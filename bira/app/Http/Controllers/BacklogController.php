<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\Team;
use App\Models\WorkflowStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Traits\ChecksBoardRole;

class BacklogController extends Controller
{
    use ChecksBoardRole;

    /**
     * Display a listing of all backlogs across all boards the user has access to.
     */
    public function index(Request $request)
    {
        $userId = Auth::user()->id;
        $teamId = $request->query('team_id');
        $boardIdQuery = $request->query('board_id');

        // Fetch all boards the user belongs to
        $boardsQuery = Board::with(['team', 'items' => function ($query) {
                // Pre-load items with their related models
                $query->with(['priority', 'type', 'creator', 'status']);
            }, 'sprints' => function ($query) {
                // Pre-load items in sprints
                $query->with(['items' => function ($q) {
                    $q->with(['priority', 'type', 'status']);
                }]);
            }])
            ->where(function ($query) use ($userId) {
                $query->whereHas('members', function ($q) use ($userId) {
                    $q->where('users.id', $userId);
                })->orWhereHas('team.members', function ($q) use ($userId) {
                    $q->where('users.id', $userId)
                      ->whereIn('team_members.role_in_team', ['owner', 'Admin', 'Owner']);
                });
            });

        if ($teamId) {
            $boardsQuery->where('team_id', $teamId);
        }

        if ($boardIdQuery) {
            $boardsQuery->where('id', $boardIdQuery);
        }

        $boards = $boardsQuery->get();

        // Attach permission level for each board for UI toggles
        foreach ($boards as $boardItem) {
            $boardItem->permissionLevel = $this->getBoardPermissionLevel($boardItem);
            
            // Organize sprints/items into categories Jira-style
            $boardItem->activeSprint = $boardItem->sprints->firstWhere('status', 'in_progress');
            $boardItem->plannedSprints = $boardItem->sprints->where('status', 'planned');
            $boardItem->newSprints = $boardItem->sprints->where('status', 'new');
            
            // Backlog items = items NOT in an active/planned/new sprint AND in a backlog status
            // Wait, Jira also shows items in the backlog status that ARE in a sprint if they are just "not started".
            // Actually, in Jira:
            // Sprints contain items assigned to them.
            // "Backlog" section contains items NOT assigned to any sprint.
            
            $sprintItemIds = $boardItem->sprints
                ->whereIn('status', ['in_progress', 'planned', 'new'])
                ->pluck('items')
                ->flatten()
                ->pluck('id')
                ->toArray();

            $boardItem->backlogTasks = $boardItem->items
                ->filter(function($item) use ($sprintItemIds) {
                    return !in_array($item->id, $sprintItemIds) && optional($item->status)->is_backlog;
                });
                
            $boardItem->backlogStatus = WorkflowStatus::where('workflow_group_id', $boardItem->workflow_group_id)
                ->where('is_backlog', 1)
                ->first();
        }

        $team = $teamId ? Team::find($teamId) : null;
        $board = $boardIdQuery ? $boards->firstWhere('id', $boardIdQuery) : null;

        return view('backlog.index', compact('boards', 'team', 'board'));
    }

    /**
     * Helper to get permission level for a board without throwing abort.
     */
    protected function getBoardPermissionLevel(Board $board)
    {
        $userId = Auth::user()->id;

        // Check team owner/admin
        $isTeamAdmin = $board->team->members()
            ->where('users.id', $userId)
            ->wherePivotIn('role_in_team', ['owner', 'Admin', 'Owner'])
            ->exists();

        if ($isTeamAdmin) return 'admin';

        // Check board role
        $role = $this->getBoardRole($board);
        if (in_array($role, ['product_owner', 'techlead', 'teamlead'])) return 'admin';
        if (in_array($role, ['developer', 'stakeholder'])) return 'member';

        return 'viewer';
    }
}
