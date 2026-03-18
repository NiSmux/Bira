<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\Team;
use App\Models\WorkflowGroup;
use App\Models\WorkflowStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BoardController extends Controller
{
    /**
     * Display a listing of the boards.
     */
    public function index()
    {
        $userId = Auth::user()->id;

        $boards = Board::with('team')
            ->whereHas('team.members', function ($query) use ($userId) {
                $query->where('users.id', $userId);
            })
            ->get();

        return view('boards.index', compact('boards'));
    }

    /**
     * Show the form for creating a new board.
     */
    public function create()
    {
        $userId = Auth::user()->id;

        $teams = Team::whereHas('members', function ($query) use ($userId) {
            $query->where('users.id', $userId)
                ->where('team_members.role_in_team', 'owner');
        })->get();

        return view('boards.create', compact('teams'));
    }

    /**
     * Store a newly created board in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'team_id' => 'required|exists:teams,id',
        ]);

        $userId = Auth::user()->id;

        $team = Team::whereHas('members', function ($query) use ($userId) {
            $query->where('users.id', $userId)
                ->where('team_members.role_in_team', 'owner');
        })->where('id', $validated['team_id'])->firstOrFail();

        $workflowGroup = WorkflowGroup::create([
            'name' => $validated['name'] . ' Workflow',
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

        $board = Board::create([
            'name' => $validated['name'],
            'team_id' => $team->id,
            'workflow_group_id' => $workflowGroup->id,
        ]);

        return redirect()->route('boards.show', $board->id)
            ->with('success', 'Board created successfully!');
    }

    /**
     * Display the specified board.
     */
    public function show($id)
    {
        $userId = Auth::user()->id;

        $board = Board::with('team.members')->findOrFail($id);

        $isMember = $board->team
            && $board->team->members()
                ->where('users.id', $userId)
                ->exists();

        abort_unless($isMember, 403);

        $statuses = WorkflowStatus::where('workflow_group_id', $board->workflow_group_id)
            ->orderBy('order_index')
            ->get();

        return view('boards.show', compact('board', 'statuses'));
    }

    /**
     * Add a new column (status) to the board.
     */
    public function addColumn(Request $request, Board $board)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $userId = Auth::user()->id;

        // Check if user is a member of the team
        $isMember = $board->team
            && $board->team->members()
                ->where('users.id', $userId)
                ->exists();

        abort_unless($isMember, 403);

        // Get the last order index
        $lastOrder = WorkflowStatus::where('workflow_group_id', $board->workflow_group_id)
            ->max('order_index') ?? 0;

        WorkflowStatus::create([
            'workflow_group_id' => $board->workflow_group_id,
            'name' => $validated['name'],
            'order_index' => $lastOrder + 1,
            'is_done' => 0,
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

        $userId = Auth::user()->id;

        // Check if user is a member of the team
        $isMember = $board->team
            && $board->team->members()
                ->where('users.id', $userId)
                ->exists();

        abort_unless($isMember, 403);

        $newIndex = $validated['new_index'] + 1; // Frontend is 0-indexed, Backend is 1-indexed (order_index)
        $oldIndex = $column->order_index;

        if ($newIndex == $oldIndex) {
            return response()->json(['success' => true]);
        }

        if ($newIndex > $oldIndex) {
            // Moving down (to the right)
            WorkflowStatus::where('workflow_group_id', $board->workflow_group_id)
                ->whereBetween('order_index', [$oldIndex + 1, $newIndex])
                ->decrement('order_index');
        } else {
            // Moving up (to the left)
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

        $userId = Auth::user()->id;

        // Check if user is a member of the team
        $isMember = $board->team
            && $board->team->members()
                ->where('users.id', $userId)
                ->exists();

        abort_unless($isMember, 403);

        $column->update(['name' => $validated['name']]);

        return response()->json(['success' => true]);
    }

    /**
     * Delete a column (status) from the board.
     */
    public function deleteColumn(Request $request, Board $board, WorkflowStatus $column)
    {
        $userId = Auth::user()->id;

        $isMember = $board->team
            && $board->team->members()
                ->where('users.id', $userId)
                ->exists();

        abort_unless($isMember, 403);

        // Block deletion if the column still has tasks
        $taskCount = $column->workItems()->count();
        if ($taskCount > 0) {
            return response()->json([
                'success' => false,
                'message' => 'This column still has ' . $taskCount . ' task(s). Please move or delete all tasks before removing the column.',
                'has_tasks' => true,
            ], 422);
        }

        $column->delete();

        return response()->json(['success' => true]);
    }
}
