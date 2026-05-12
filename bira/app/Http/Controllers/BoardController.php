<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\Sprint;
use App\Models\Team;
use App\Models\User;
use App\Models\WorkflowGroup;
use App\Models\WorkflowStatus;
use App\Http\Traits\ChecksBoardRole;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BoardController extends Controller
{
    use ChecksBoardRole;

    /**
     * Display a listing of the boards the user belongs to.
     */
    public function index()
    {
        $userId = Auth::user()->id;

        $boards = Board::with('team')
            ->where(function ($query) use ($userId) {
                $query->whereHas('members', function ($q) use ($userId) {
                    $q->where('users.id', $userId);
                })->orWhereHas('team.members', function ($q) use ($userId) {
                    $q->where('users.id', $userId)
                      ->whereIn('team_members.role_in_team', ['owner', 'Admin', 'Owner']);
                });
            })
            ->get();

        $groupedBoards = $boards->groupBy(function ($board) {
            return $board->team ? $board->team->name : 'Personal Boards';
        });

        return view('boards.index', compact('groupedBoards'));
    }

    /**
     * Show the form for creating a new board.
     */
    public function create(Request $request)
    {
        $userId = Auth::user()->id;

        $teams = Team::whereHas('members', function ($query) use ($userId) {
            $query->where('users.id', $userId)
                ->whereIn('team_members.role_in_team', ['owner', 'Admin', 'Owner']);
        })->with('members')->get();

        if ($request->has('debug')) {
            dd([
                'user_id' => $userId,
                'teams_count' => $teams->count(),
                'teams_sql' => Team::whereHas('members', function ($query) use ($userId) {
                    $query->where('users.id', $userId)
                        ->whereIn('team_members.role_in_team', ['owner', 'Admin', 'Owner']);
                })->toSql(),
                'teams_bindings' => Team::whereHas('members', function ($query) use ($userId) {
                    $query->where('users.id', $userId)
                        ->whereIn('team_members.role_in_team', ['owner', 'Admin', 'Owner']);
                })->getBindings()
            ]);
        }

        $roles = self::boardRoles();
        $preselectedTeamId = $request->query('team_id');

        if ($request->ajax()) {
            return view('boards.partials.create_board_form', compact('teams', 'roles', 'preselectedTeamId'));
        }

        return view('boards.create', compact('teams', 'roles', 'preselectedTeamId'));
    }

    /**
     * AJAX endpoint: get team members for dynamic board creation form.
     */
    public function getTeamMembers(Team $team)
    {
        $userId = Auth::user()->id;

        // Ensure requesting user is a team owner
        $isOwner = $team->members()
            ->where('users.id', $userId)
            ->wherePivotIn('role_in_team', ['owner', 'Admin', 'Owner'])
            ->exists();

        abort_unless($isOwner, 403);

        $members = $team->members->map(function ($member) use ($userId) {
            return [
                'id'         => $member->id,
                'name'       => $member->name,
                'email'      => $member->email,
                'is_current' => $member->id === $userId,
            ];
        });

        return response()->json($members);
    }

    /**
     * Store a newly created board in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'              => [
                'required', 'string', 'max:255',
                \Illuminate\Validation\Rule::unique('boards')->where(fn ($query) =>
                    $query->where('team_id', $request->input('team_id'))
                ),
            ],
            'team_id'           => 'required|exists:teams,id',
            'members'           => 'required|array|min:1',
            'members.*.user_id' => 'required|exists:users,id',
            'members.*.role'    => 'required|string|in:' . implode(',', array_keys(self::boardRoles())),
            'estimation_mode'   => 'sometimes|required|in:points,hours',
        ], [
            'name.unique' => 'A board with this name already exists in this team.',
        ]);

        $userId = Auth::user()->id;

        $team = Team::whereHas('members', function ($query) use ($userId) {
            $query->where('users.id', $userId)
                ->whereIn('team_members.role_in_team', ['owner', 'Admin', 'Owner']);
        })->where('id', $validated['team_id'])->firstOrFail();

        $board = null;

        DB::transaction(function () use ($validated, $team, $userId, &$board) {
            $workflowGroup = WorkflowGroup::create([
                'name'    => $validated['name'] . ' Workflow',
                'team_id' => $team->id,
            ]);

            WorkflowStatus::create([
                'workflow_group_id' => $workflowGroup->id,
                'name'              => 'Backlog',
                'order_index'       => 0,
                'is_done'           => 0,
                'is_backlog'        => 1,
            ]);

            WorkflowStatus::create([
                'workflow_group_id' => $workflowGroup->id,
                'name'              => 'To Do',
                'order_index'       => 1,
                'is_done'           => 0,
            ]);

            WorkflowStatus::create([
                'workflow_group_id' => $workflowGroup->id,
                'name'              => 'In Progress',
                'order_index'       => 2,
                'is_done'           => 0,
            ]);

            WorkflowStatus::create([
                'workflow_group_id' => $workflowGroup->id,
                'name'              => 'Done',
                'order_index'       => 3,
                'is_done'           => 1,
            ]);

            $board = Board::create([
                'name'              => $validated['name'],
                'team_id'           => $team->id,
                'workflow_group_id' => $workflowGroup->id,
                'estimation_mode'   => $validated['estimation_mode'] ?? 'points',
            ]);

            // Add selected members with their roles
            $creatorIncluded = false;
            foreach ($validated['members'] as $memberData) {
                $board->members()->attach($memberData['user_id'], [
                    'role' => $memberData['role'],
                ]);
                if ((int) $memberData['user_id'] === $userId) {
                    $creatorIncluded = true;
                }
            }

            // Ensure creator is always on the board as product_owner
            if (!$creatorIncluded) {
                $board->members()->attach($userId, [
                    'role' => 'product_owner',
                ]);
            }
        });

        // Notify added members (except the creator)
        $notifyIds = collect($validated['members'])
            ->pluck('user_id')
            ->map(fn($id) => (int) $id)
            ->reject(fn($id) => $id === $userId)
            ->values()
            ->toArray();

        if (!empty($notifyIds)) {
            NotificationService::notify(
                $notifyIds,
                'board_added',
                'Added to Board',
                "You were added to new board \"{$validated['name']}\"",
                route('boards.show', $board->id)
            );
        }

        return redirect()->route('boards.show', $board->id)
            ->with('success', 'Board created successfully!');
    }

    /**
     * Display the specified board.
     */
    public function show($id)
    {
        $board = Board::with('team.members')->findOrFail($id);

        $permissionLevel = $this->ensureBoardPermission($board, 'viewer');
        $userRole = $this->getBoardRole($board);

        // Track when the user last accessed this board
        $userId = Auth::user()->id;
        DB::table('board_members')
            ->where('board_id', $board->id)
            ->where('user_id', $userId)
            ->update(['last_accessed_at' => now()]);

        if (!$userRole && $permissionLevel === 'admin') {
            $userRole = 'team_owner';
        }

        $statuses = WorkflowStatus::where('workflow_group_id', $board->workflow_group_id)
            ->where('is_backlog', 0)
            ->orderBy('order_index')
            ->get();

        $backlogStatus = WorkflowStatus::where('workflow_group_id', $board->workflow_group_id)
            ->where('is_backlog', 1)
            ->first();

        // Only show active/upcoming sprints on the board page.
        // Completed sprints (to_be_released, delivered) are in the history page.
        $sprints = Sprint::where('board_id', $board->id)
            ->whereIn('status', ['new', 'planned', 'in_progress'])
            ->with(['items' => fn($q) => $q->with(['type', 'priority', 'status'])])
            ->orderByRaw("FIELD(status, 'in_progress', 'planned', 'new')")
            ->orderBy('created_at', 'desc')
            ->get();

        $activeSprint   = $sprints->firstWhere('status', 'in_progress');
        $plannedSprints = $sprints->where('status', 'planned');
        $newSprints     = $sprints->where('status', 'new');

        return view('boards.show', compact(
            'board', 'statuses', 'permissionLevel', 'userRole',
            'backlogStatus', 'activeSprint', 'plannedSprints', 'newSprints'
        ));
    }

    /**
     * Board settings page — manage members, roles, and sub-teams.
     */
    public function settings(Board $board)
    {
        $this->ensureBoardPermission($board, 'admin');

        $board->load('team.members', 'members', 'subTeams.members');

        $roles = self::boardRoles();
        $isBoardAdmin = true; // ensureBoardPermission above already guarantees admin

        // Team members not yet on this board
        $boardMemberIds = $board->members->pluck('id')->toArray();
        $availableMembers = $board->team->members->filter(function ($member) use ($boardMemberIds) {
            return !in_array($member->id, $boardMemberIds);
        });

        return view('boards.settings', compact('board', 'roles', 'availableMembers', 'isBoardAdmin'));
    }

    /**
     * Update the board's estimation mode.
     */
    public function updateMode(Request $request, Board $board)
    {
        $this->ensureBoardPermission($board, 'admin');

        $validated = $request->validate([
            'estimation_mode' => 'required|in:points,hours',
        ]);

        $oldMode = $board->estimation_mode;
        $newMode = $validated['estimation_mode'];

        if ($oldMode !== $newMode) {
            $board->update(['estimation_mode' => $newMode]);
            
            $itemIds = $board->items()->pluck('work_items.id');
            
            if ($itemIds->isNotEmpty()) {
                if ($newMode === 'hours') {
                    // Changing to hours, convert existing story_points over natively 1:1
                    \App\Models\WorkItem::whereIn('id', $itemIds)
                        ->update(['estimated_hours' => \DB::raw('story_points')]);
                } else {
                    // Changing to points, convert estimated_hours back to points (rounding) 1:1
                    \App\Models\WorkItem::whereIn('id', $itemIds)
                        ->update(['story_points' => \DB::raw('ROUND(estimated_hours)')]);
                }
            }
        }

        return back()->with('success', 'Estimation mode updated successfully and metrics converted!');
    }

    /**
     * Update the board's SP-to-hours conversion rate.
     */
    public function updateSpRate(Request $request, Board $board)
    {
        $this->ensureBoardPermission($board, 'admin');

        $validated = $request->validate([
            'sp_to_hours_rate' => 'nullable|numeric|min:0.1|max:100',
        ]);

        $board->update(['sp_to_hours_rate' => $validated['sp_to_hours_rate'] ?: null]);

        return back()->with('success', 'SP to hours conversion rate updated.');
    }

    /**
     * Add a team member to the board.
     */
    public function addBoardMember(Request $request, Board $board)
    {
        $this->ensureBoardPermission($board, 'admin');

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'role'    => 'required|string|in:' . implode(',', array_keys(self::boardRoles())),
        ]);

        // Ensure user is a member of the team
        $isTeamMember = $board->team->members()->where('users.id', $validated['user_id'])->exists();
        abort_unless($isTeamMember, 422, 'User must be a member of the team first.');

        // Check not already on board
        $alreadyOnBoard = $board->members()->where('users.id', $validated['user_id'])->exists();
        if ($alreadyOnBoard) {
            return back()->withErrors(['user_id' => 'This user is already on the board.']);
        }

        $board->members()->attach($validated['user_id'], [
            'role' => $validated['role'],
        ]);

        // Notify the added user
        NotificationService::notify(
            [$validated['user_id']],
            'board_added',
            'Added to Board',
            "You were added to board \"{$board->name}\"",
            route('boards.show', $board->id)
        );

        return redirect()->route('boards.settings', $board->id)
            ->with('success', 'Member added to board.');
    }

    /**
     * Update a board member's role.
     */
    public function updateBoardMemberRole(Request $request, Board $board, User $user)
    {
        $this->ensureBoardPermission($board, 'admin');

        $validated = $request->validate([
            'role' => 'required|string|in:' . implode(',', array_keys(self::boardRoles())),
        ]);

        $board->members()->updateExistingPivot($user->id, [
            'role' => $validated['role'],
        ]);

        return redirect()->route('boards.settings', $board->id)
            ->with('success', 'Role updated.');
    }

    /**
     * Remove a member from the board.
     */
    public function removeBoardMember(Board $board, User $user)
    {
        $this->ensureBoardPermission($board, 'admin');

        // Prevent removing yourself if you're the only admin
        $adminCount = $board->members->filter(function ($m) {
            return in_array($m->pivot->role, ['product_owner', 'techlead', 'teamlead']);
        })->count();

        if ($user->id === Auth::user()->id && $adminCount <= 1) {
            return back()->withErrors(['user_id' => 'Cannot remove the last admin from the board.']);
        }

        $board->members()->detach($user->id);

        return redirect()->route('boards.settings', $board->id)
            ->with('success', 'Member removed from board.');
    }

    /**
     * Add a new column (status) to the board.
     */
    public function addColumn(Request $request, Board $board)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $this->ensureBoardPermission($board, 'admin');

        $lastOrder = WorkflowStatus::where('workflow_group_id', $board->workflow_group_id)
            ->max('order_index') ?? 0;

        WorkflowStatus::create([
            'workflow_group_id' => $board->workflow_group_id,
            'name'              => $validated['name'],
            'order_index'       => $lastOrder + 1,
            'is_done'           => 0,
        ]);

        return redirect()->route('boards.show', $board->id)
            ->with('success', 'Column added successfully!');
    }

    /**
     * Reorder columns (statuses) in the board.
     */
    public function reorderColumn(Request $request, Board $board, WorkflowStatus $column)
    {
        $validated = $request->validate([
            'new_index' => 'required|integer|min:0',
        ]);

        $this->ensureBoardPermission($board, 'admin');

        $newIndex = $validated['new_index'] + 1;
        $oldIndex = $column->order_index;

        if ($newIndex == $oldIndex) {
            return response()->json(['success' => true]);
        }

        if ($newIndex > $oldIndex) {
            WorkflowStatus::where('workflow_group_id', $board->workflow_group_id)
                ->whereBetween('order_index', [$oldIndex + 1, $newIndex])
                ->decrement('order_index');
        } else {
            WorkflowStatus::where('workflow_group_id', $board->workflow_group_id)
                ->whereBetween('order_index', [$newIndex, $oldIndex - 1])
                ->increment('order_index');
        }

        $column->update(['order_index' => $newIndex]);

        return response()->json(['success' => true]);
    }

    /**
     * Update column (status) details.
     */
    public function updateColumn(Request $request, Board $board, WorkflowStatus $column)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $this->ensureBoardPermission($board, 'admin');

        abort_unless($column->workflow_group_id === $board->workflow_group_id, 404);

        if ($column->is_backlog) {
            return response()->json(['success' => false, 'message' => 'Backlog column cannot be renamed.'], 403);
        }

        $column->update(['name' => $validated['name']]);

        return response()->json(['success' => true]);
    }

    /**
     * Delete a column (status) from the board.
     */
    public function deleteColumn(Request $request, Board $board, WorkflowStatus $column)
    {
        $this->ensureBoardPermission($board, 'admin');

        abort_unless($column->workflow_group_id === $board->workflow_group_id, 404);

        if ($column->is_backlog) {
            return response()->json(['success' => false, 'message' => 'Backlog column cannot be deleted.'], 403);
        }

        $taskCount = $column->workItems()->count();
        if ($taskCount > 0) {
            return response()->json([
                'success'   => false,
                'message'   => 'This column still has ' . $taskCount . ' task(s). Please move or delete all tasks before removing the column.',
                'has_tasks' => true,
            ], 422);
        }

        $column->delete();

        return response()->json(['success' => true]);
    }

    /**
     * AJAX: Return metrics data for the board metrics sidebar (4 tabs).
     */
    public function metricsData(Board $board)
    {
        $this->ensureBoardPermission($board, 'viewer');
        $user = Auth::user();

        $board->load(['members', 'sprints']);
        $estimationMode = $board->estimation_mode ?? 'points';

        // ── Active sprint ──────────────────────────────────────────────────
        $activeSprint = \App\Models\Sprint::where('board_id', $board->id)
            ->where('status', 'in_progress')
            ->with(['items.status', 'items.type', 'items.assignee'])
            ->first();

        $sprintData = null;
        if ($activeSprint) {
            $items = $activeSprint->items->where('is_deleted', '!=', 1);
            $totalItems = $items->count();
            $doneItems  = $items->filter(fn($i) => $i->status && $i->status->is_done)->count();

            $totalPts  = $items->sum('story_points');
            $donePts   = $items->filter(fn($i) => $i->status && $i->status->is_done)->sum('story_points');
            $totalHrs  = $items->sum('estimated_hours');
            $doneHrs   = $items->filter(fn($i) => $i->status && $i->status->is_done)->sum('estimated_hours');

            $byType = $items->groupBy(fn($i) => $i->type?->name ?? 'Untyped')
                ->map(fn($g) => ['total' => $g->count(), 'done' => $g->filter(fn($i) => $i->status?->is_done)->count()])
                ->toArray();

            $byStatus = $items->groupBy(fn($i) => $i->status?->name ?? 'No Status')
                ->map->count()->toArray();

            $daysLeft = $activeSprint->end_date
                ? max(0, now()->startOfDay()->diffInDays($activeSprint->end_date->startOfDay(), false))
                : null;
            $overdue = $activeSprint->end_date && now()->startOfDay()->gt($activeSprint->end_date->startOfDay());

            $sprintData = [
                'name'         => $activeSprint->name,
                'goal'         => $activeSprint->goal,
                'start_date'   => $activeSprint->start_date?->format('M j, Y'),
                'end_date'     => $activeSprint->end_date?->format('M j, Y'),
                'days_left'    => $daysLeft,
                'overdue'      => $overdue,
                'total_items'  => $totalItems,
                'done_items'   => $doneItems,
                'total_points' => $totalPts,
                'done_points'  => $donePts,
                'total_hours'  => round($totalHrs, 1),
                'done_hours'   => round($doneHrs, 1),
                'by_type'      => $byType,
                'by_status'    => $byStatus,
                'estimation_mode' => $estimationMode,
            ];
        }

        // ── Release (historical sprints) ────────────────────────────────────
        $completedSprints = \App\Models\Sprint::where('board_id', $board->id)
            ->whereIn('status', ['to_be_released', 'delivered'])
            ->orderBy('created_at', 'desc')
            ->get();

        $allSprints = \App\Models\Sprint::where('board_id', $board->id)
            ->orderBy('created_at', 'desc')
            ->take(6)
            ->get();

        $avgVelocity = $completedSprints->isNotEmpty()
            ? round($completedSprints->avg('completed_points'), 1)
            : 0;

        $sprintBars = $allSprints->map(fn($s) => [
            'name'      => $s->name,
            'status'    => $s->status,
            'completed' => $s->completed_points ?? 0,
            'total'     => $s->total_points ?? 0,
        ])->reverse()->values()->toArray();

        $releaseData = [
            'total_sprints'      => $board->sprints->count(),
            'completed_sprints'  => $completedSprints->count(),
            'avg_velocity'       => $avgVelocity,
            'last_sprint'        => $completedSprints->first() ? [
                'name'   => $completedSprints->first()->name,
                'points' => $completedSprints->first()->completed_points ?? 0,
            ] : null,
            'sprint_bars'        => $sprintBars,
            'estimation_mode'    => $estimationMode,
        ];

        // ── Team ────────────────────────────────────────────────────────────
        $board->load('members');
        $boardItemIds = \DB::table('board_items')
            ->where('board_id', $board->id)
            ->pluck('item_id');

        $itemsByMember = \App\Models\WorkItem::whereIn('id', $boardItemIds)
            ->where('is_deleted', '!=', 1)
            ->whereNotNull('assignee_id')
            ->selectRaw('assignee_id, count(*) as total, sum(CASE WHEN story_points IS NOT NULL THEN story_points ELSE 0 END) as pts')
            ->groupBy('assignee_id')
            ->get()
            ->keyBy('assignee_id');

        $unassignedCount = $activeSprint
            ? $activeSprint->items->where('is_deleted', '!=', 1)->whereNull('assignee_id')->count()
            : 0;

        $teamMembers = $board->members->map(function($member) use ($itemsByMember) {
            $stats = $itemsByMember->get($member->id);
            return [
                'id'    => $member->id,
                'name'  => $member->name,
                'role'  => $member->pivot->role,
                'items' => $stats?->total ?? 0,
                'pts'   => $stats?->pts ?? 0,
                'initials' => strtoupper(substr($member->name, 0, 1) . (strpos($member->name, ' ') !== false ? substr($member->name, strpos($member->name, ' ') + 1, 1) : '')),
            ];
        })->sortByDesc('items')->values()->toArray();

        $teamData = [
            'members'           => $teamMembers,
            'total_members'     => count($teamMembers),
            'unassigned_active' => $unassignedCount,
        ];

        // ── User (personal, scoped to this board) ───────────────────────────
        $myItems = $activeSprint
            ? $activeSprint->items->where('is_deleted', '!=', 1)->where('assignee_id', $user->id)
            : collect();

        $myDone     = $myItems->filter(fn($i) => $i->status?->is_done);
        $myByType   = $myItems->groupBy(fn($i) => $i->type?->name ?? 'Untyped')
            ->map(fn($g) => ['total' => $g->count(), 'done' => $g->filter(fn($i) => $i->status?->is_done)->count()])
            ->toArray();

        $userData = [
            'my_total'  => $myItems->count(),
            'my_done'   => $myDone->count(),
            'my_pts_done'  => $myDone->sum('story_points'),
            'my_pts_total' => $myItems->sum('story_points'),
            'my_hrs_done'  => round($myDone->sum('estimated_hours'), 1),
            'my_hrs_total' => round($myItems->sum('estimated_hours'), 1),
            'my_by_type'   => $myByType,
            'estimation_mode' => $estimationMode,
        ];

        return response()->json([
            'sprint'  => $sprintData,
            'release' => $releaseData,
            'team'    => $teamData,
            'user'    => $userData,
        ]);
    }

    /**
     * Delete the specified board.
     */
    public function destroy($id)
    {
        $board = Board::with('members')->findOrFail($id);

        $this->ensureBoardPermission($board, 'admin');

        $board->delete();

        return redirect()->back()->with('success', 'Board deleted successfully!');
    }
}
