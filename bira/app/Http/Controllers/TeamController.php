<?php

namespace App\Http\Controllers;

use App\Models\ItemType;
use App\Models\Team;
use App\Models\User;
use App\Models\WorkflowGroup;
use App\Models\WorkflowStatus;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class TeamController extends Controller
{
    public function index()
    {
        $userId = Auth::user()->id;

        $ownedTeams = Team::whereHas('members', function ($query) use ($userId) {
            $query->where('users.id', $userId)
                ->where('team_members.role_in_team', 'owner');
        })->with('members')->get();

        $memberTeams = Team::whereHas('members', function ($query) use ($userId) {
            $query->where('users.id', $userId);
        })->whereDoesntHave('members', function ($query) use ($userId) {
            $query->where('users.id', $userId)
                ->where('team_members.role_in_team', 'owner');
        })->with('members')->get();

        return view('teams.index', compact('ownedTeams', 'memberTeams'));
    }

    public function create(Request $request)
    {
        if ($request->ajax()) {
            return view('teams.partials.create_form');
        }
        return view('teams.create');
    }

    public function store(Request $request)
    {
    $validated = $request->validate([
        'name' => 'required|string|max:255|unique:teams,name',
        'description' => 'nullable|string|max:255',
    ]);

    $userId = Auth::user()->id;
    $team = null;

    DB::transaction(function () use ($validated, $userId, &$team) {
        $team = Team::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        $team->members()->attach($userId, [
            'role_in_team' => 'owner',
        ]);

        $workflowGroup = WorkflowGroup::create([
            'name' => 'Default Workflow',
            'team_id' => $team->id,
        ]);

        WorkflowStatus::create([
            'workflow_group_id' => $workflowGroup->id,
            'name' => 'Backlog',
            'order_index' => 0,
            'is_done' => 0,
            'is_backlog' => 1,
        ]);

        WorkflowStatus::create([
            'workflow_group_id' => $workflowGroup->id,
            'name' => 'To Do',
            'order_index' => 1,
            'is_done' => 0,
        ]);

        WorkflowStatus::create([
            'workflow_group_id' => $workflowGroup->id,
            'name' => 'In Progress',
            'order_index' => 2,
            'is_done' => 0,
        ]);

        WorkflowStatus::create([
            'workflow_group_id' => $workflowGroup->id,
            'name' => 'Done',
            'order_index' => 3,
            'is_done' => 1,
        ]);
    });

    return redirect()->route('teams.show', $team->id)
        ->with('success', 'Team created successfully.');
    }

    public function show(Team $team)
    {
        $this->ensureMember($team);

        $team->load('members', 'boards', 'itemTypes');

        $userId = Auth::user()->id;
        $isOwner = $team->members()
            ->where('users.id', $userId)
            ->wherePivot('role_in_team', 'owner')
            ->exists();

        $availableUsers = $isOwner
            ? User::whereNotIn('id', $team->members->pluck('id'))->get()
            : collect();

        $globalItemTypes = DB::table('item_types')->whereNull('team_id')->orderBy('order_index')->get();

        return view('teams.show', compact('team', 'availableUsers', 'isOwner', 'globalItemTypes'));
    }

    public function updateDefaultType(Request $request, Team $team)
    {
        $this->ensureOwner($team);

        $validated = $request->validate([
            'default_item_type_id' => 'nullable|exists:item_types,id',
        ]);

        $team->update(['default_item_type_id' => $validated['default_item_type_id'] ?: null]);

        return redirect()->route('teams.show', $team->id)
            ->with('success', 'Default task type updated.');
    }

    public function storeItemType(Request $request, Team $team)
    {
        $this->ensureOwner($team);

        $validated = $request->validate([
            'name'        => 'required|string|max:80',
            'icon'        => 'nullable|string|max:10',
            'color'       => 'nullable|string|max:20|regex:/^#[0-9a-fA-F]{3,8}$/',
            'description' => 'nullable|string|max:255',
        ]);

        $maxOrder = DB::table('item_types')->max('order_index') ?? 0;

        ItemType::create([
            'name'        => $validated['name'],
            'icon'        => $validated['icon'] ?? null,
            'color'       => $validated['color'] ?? null,
            'description' => $validated['description'] ?? null,
            'order_index' => $maxOrder + 1,
            'team_id'     => $team->id,
        ]);

        return redirect()->route('teams.show', $team->id)
            ->with('success', 'Task type "' . $validated['name'] . '" added.');
    }

    public function updateItemType(Request $request, Team $team, ItemType $itemType)
    {
        $this->ensureOwner($team);

        abort_unless($itemType->team_id === $team->id, 403);

        $validated = $request->validate([
            'name'        => 'required|string|max:80',
            'icon'        => 'nullable|string|max:10',
            'color'       => 'nullable|string|max:20|regex:/^#[0-9a-fA-F]{3,8}$/',
            'description' => 'nullable|string|max:255',
        ]);

        $itemType->update([
            'name'        => $validated['name'],
            'icon'        => $validated['icon'] ?? null,
            'color'       => $validated['color'] ?? null,
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()->route('teams.show', $team->id)
            ->with('success', 'Task type "' . $validated['name'] . '" updated.');
    }

    public function destroyItemType(Team $team, ItemType $itemType)
    {
        $this->ensureOwner($team);

        abort_unless($itemType->team_id === $team->id, 403);

        if ($team->default_item_type_id === $itemType->id) {
            $team->update(['default_item_type_id' => null]);
        }

        $itemType->delete();

        return redirect()->route('teams.show', $team->id)
            ->with('success', 'Task type removed.');
    }

    public function addMember(Request $request, Team $team)
    {
        $this->ensureOwner($team);

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $alreadyInTeam = $team->members()->where('users.id', $validated['user_id'])->exists();

        if ($alreadyInTeam) {
            return back()->withErrors([
                'user_id' => 'This user is already in the team.',
            ]);
        }

        $team->members()->attach($validated['user_id'], [
            'role_in_team' => 'member',
        ]);

        // Notify the added user
        NotificationService::notify(
            [$validated['user_id']],
            'team_added',
            'Added to Team',
            "You were added to team \"{$team->name}\"",
            route('teams.show', $team->id)
        );

        return redirect()->route('teams.show', $team->id)
            ->with('success', 'Member added successfully.');
    }

    public function removeMember(Team $team, User $user)
    {
        $this->ensureOwner($team);

        $team->members()->detach($user->id);

        // Also remove the user from all boards belonging to this team
        $boardIds = $team->boards()->pluck('id');
        if ($boardIds->isNotEmpty()) {
            DB::table('board_members')
                ->whereIn('board_id', $boardIds)
                ->where('user_id', $user->id)
                ->delete();
        }

        return redirect()->route('teams.show', $team->id)
            ->with('success', 'Member removed from the team and all associated boards.');
    }

    private function ensureMember(Team $team)
    {
        $userId = Auth::user()->id;

        $isMember = $team->members()
            ->where('users.id', $userId)
            ->exists();

        abort_unless($isMember, 403);
    }

    private function ensureOwner(Team $team)
    {
        $userId = Auth::user()->id;

        $isOwner = $team->members()
            ->where('users.id', $userId)
            ->wherePivot('role_in_team', 'owner')
            ->exists();

        abort_unless($isOwner, 403);
    }
}
