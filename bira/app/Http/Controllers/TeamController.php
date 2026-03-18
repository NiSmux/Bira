<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\User;
use App\Models\WorkflowGroup;
use App\Models\WorkflowStatus;
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

    public function create()
    {
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
        $this->ensureOwner($team);

        $team->load('members', 'boards');

        $availableUsers = User::whereNotIn('id', $team->members->pluck('id'))->get();

        return view('teams.show', compact('team', 'availableUsers'));
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

        return redirect()->route('teams.show', $team->id)
            ->with('success', 'Member added successfully.');
    }

    public function removeMember(Team $team, User $user)
    {
        $this->ensureOwner($team);

        $team->members()->detach($user->id);

        return redirect()->route('teams.show', $team->id)
            ->with('success', 'Member removed from the team.');
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
