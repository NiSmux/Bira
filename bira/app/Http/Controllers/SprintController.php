<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\Sprint;
use App\Models\WorkItem;
use App\Models\WorkflowStatus;
use App\Http\Traits\ChecksBoardRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SprintController extends Controller
{
    use ChecksBoardRole;

    public function store(Request $request, Board $board)
    {
        $this->ensureBoardPermission($board, 'admin');

        $validated = $request->validate([
            'name'       => 'required|string|max:120',
            'goal'       => 'nullable|string|max:1000',
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
        ]);

        Sprint::create([
            'board_id'   => $board->id,
            'name'       => $validated['name'],
            'goal'       => $validated['goal'] ?? null,
            'start_date' => $validated['start_date'] ?? null,
            'end_date'   => $validated['end_date'] ?? null,
            'status'     => 'new',
            'created_by' => Auth::user()->id,
        ]);

        return redirect($request->input('redirect_to', route('boards.show', $board->id)))
            ->with('success', 'Sprint created.');
    }

    public function update(Request $request, Board $board, Sprint $sprint)
    {
        $this->ensureBoardPermission($board, 'admin');
        abort_unless($sprint->board_id === $board->id, 404);

        $validated = $request->validate([
            'name'       => 'required|string|max:120',
            'goal'       => 'nullable|string|max:1000',
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
        ]);

        $sprint->update($validated);

        return redirect($request->input('redirect_to', route('boards.show', $board->id)))
            ->with('success', 'Sprint updated.');
    }

    public function destroy(Board $board, Sprint $sprint)
    {
        $this->ensureBoardPermission($board, 'admin');
        abort_unless($sprint->board_id === $board->id, 404);

        if ($sprint->status === 'in_progress') {
            return redirect(request('redirect_to', route('boards.show', $board->id)))
                ->withErrors(['sprint' => 'Cannot delete an in-progress sprint. Complete it first.']);
        }

        $backlogStatus = WorkflowStatus::where('workflow_group_id', $board->workflow_group_id)
            ->where('is_backlog', 1)->first();

        if ($backlogStatus) {
            $sprint->items()->update(['release_id' => null, 'status_id' => $backlogStatus->id]);
        } else {
            $sprint->items()->update(['release_id' => null]);
        }

        $sprint->delete();

        return redirect(request('redirect_to', route('boards.show', $board->id)))
            ->with('success', 'Sprint deleted. Items returned to backlog.');
    }

    /**
     * Transition a new sprint to planned status.
     */
    public function plan(Board $board, Sprint $sprint)
    {
        $this->ensureBoardPermission($board, 'admin');
        abort_unless($sprint->board_id === $board->id, 404);

        if ($sprint->status !== 'new') {
            return redirect(request('redirect_to', route('boards.show', $board->id)))
                ->withErrors(['sprint' => 'Only new sprints can be marked as planned.']);
        }

        $sprint->update(['status' => 'planned']);

        return redirect(request('redirect_to', route('boards.show', $board->id)))
            ->with('success', "Sprint \"{$sprint->name}\" marked as planned.");
    }

    /**
     * Start a sprint (new or planned → in_progress).
     */
    public function start(Board $board, Sprint $sprint)
    {
        $this->ensureBoardPermission($board, 'admin');
        abort_unless($sprint->board_id === $board->id, 404);

        if (!in_array($sprint->status, ['new', 'planned'])) {
            return redirect(request('redirect_to', route('boards.show', $board->id)))
                ->withErrors(['sprint' => 'Sprint cannot be started.']);
        }

        $hasActive = Sprint::where('board_id', $board->id)->where('status', 'in_progress')->exists();
        if ($hasActive) {
            return redirect(request('redirect_to', route('boards.show', $board->id)))
                ->withErrors(['sprint' => 'There is already an active sprint on this board.']);
        }

        $sprint->update([
            'status'     => 'in_progress',
            'start_date' => $sprint->start_date ?? now()->toDateString(),
        ]);

        return redirect(request('redirect_to', route('boards.show', $board->id)))
            ->with('success', "Sprint \"{$sprint->name}\" started.");
    }

    /**
     * Complete a sprint (in_progress → to_be_released).
     * Snapshots points. Unfinished items return to backlog; done items stay in place.
     */
    public function complete(Board $board, Sprint $sprint)
    {
        $this->ensureBoardPermission($board, 'admin');
        abort_unless($sprint->board_id === $board->id, 404);

        if ($sprint->status !== 'in_progress') {
            return redirect(request('redirect_to', route('boards.show', $board->id)))
                ->withErrors(['sprint' => 'Sprint is not in progress.']);
        }

        // Snapshot point totals BEFORE moving items to backlog so velocity data is accurate
        $doneStatusIds = WorkflowStatus::where('workflow_group_id', $board->workflow_group_id)
            ->where('is_done', 1)->pluck('id');

        $metricField     = $board->estimation_mode === 'hours' ? 'estimated_hours' : 'story_points';
        $totalPoints     = (float) $sprint->items()->sum($metricField);
        $completedPoints = (float) $sprint->items()->whereIn('status_id', $doneStatusIds)->sum($metricField);

        $backlogStatus = WorkflowStatus::where('workflow_group_id', $board->workflow_group_id)
            ->where('is_backlog', 1)->first();

        // Only incomplete items return to backlog and are removed from the sprint.
        // Done items stay in the completed sprint history.
        if ($backlogStatus && $doneStatusIds->isNotEmpty()) {
            $sprint->items()
                ->whereNotIn('status_id', $doneStatusIds)
                ->update([
                    'status_id' => $backlogStatus->id,
                    'release_id' => null
                ]);
        } elseif ($backlogStatus) {
            $sprint->items()->update([
                'status_id' => $backlogStatus->id,
                'release_id' => null
            ]);
        }

        $sprint->update([
            'status'           => 'to_be_released',
            'end_date'         => $sprint->end_date ?? now()->toDateString(),
            'total_points'     => $totalPoints,
            'completed_points' => $completedPoints,
        ]);

        return redirect(request('redirect_to', route('boards.show', $board->id)))
            ->with('success', "Sprint \"{$sprint->name}\" completed. Ready for release.");
    }

    /**
     * Mark a sprint as delivered (to_be_released → delivered).
     */
    public function deliver(Board $board, Sprint $sprint)
    {
        $this->ensureBoardPermission($board, 'admin');
        abort_unless($sprint->board_id === $board->id, 404);

        if ($sprint->status !== 'to_be_released') {
            return redirect(request('redirect_to', route('boards.show', $board->id)))
                ->withErrors(['sprint' => 'Sprint is not in "To be Released" state.']);
        }

        $sprint->update(['status' => 'delivered']);

        return redirect(request('redirect_to', route('boards.show', $board->id)))
            ->with('success', "Sprint \"{$sprint->name}\" marked as delivered.");
    }

    public function addItem(Request $request, Board $board, Sprint $sprint)
    {
        $this->ensureBoardPermission($board, 'member');
        abort_unless($sprint->board_id === $board->id, 404);
        abort_unless(in_array($sprint->status, ['new', 'planned', 'in_progress']), 422);

        $validated = $request->validate([
            'item_id' => 'required|exists:work_items,id',
        ]);

        $item = WorkItem::findOrFail($validated['item_id']);
        abort_unless($item->boards()->where('boards.id', $board->id)->exists(), 403);

        $toDoStatus = WorkflowStatus::where('workflow_group_id', $board->workflow_group_id)
            ->where('is_backlog', 0)
            ->where('is_done', 0)
            ->orderBy('order_index')
            ->first();

        $item->update([
            'release_id' => $sprint->id,
            'status_id'  => $toDoStatus ? $toDoStatus->id : $item->status_id,
        ]);

        return response()->json(['success' => true]);
    }

    public function removeItem(Board $board, Sprint $sprint, WorkItem $item)
    {
        $this->ensureBoardPermission($board, 'member');
        abort_unless($sprint->board_id === $board->id, 404);
        abort_unless((int) $item->release_id === $sprint->id, 403);

        $backlogStatus = WorkflowStatus::where('workflow_group_id', $board->workflow_group_id)
            ->where('is_backlog', 1)->first();

        $item->update([
            'release_id' => null,
            'status_id'  => $backlogStatus ? $backlogStatus->id : $item->status_id,
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Sprint history page — all sprints for a board with expandable task lists.
     */
    public function history(Board $board)
    {
        $permissionLevel = $this->ensureBoardPermission($board, 'viewer');

        $sprints = Sprint::where('board_id', $board->id)
            ->with(['items' => fn($q) => $q->with(['type', 'priority', 'status', 'assignee'])])
            ->orderByRaw("FIELD(status, 'in_progress', 'to_be_released', 'planned', 'new', 'delivered')")
            ->orderBy('created_at', 'desc')
            ->get();

        return view('boards.sprints.history', compact('board', 'sprints', 'permissionLevel'));
    }
}
