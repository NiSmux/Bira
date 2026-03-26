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
            'status'     => 'planned',
            'created_by' => Auth::user()->id,
        ]);

        return redirect()->route('boards.show', $board->id)
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

        return redirect()->route('boards.show', $board->id)
            ->with('success', 'Sprint updated.');
    }

    public function destroy(Board $board, Sprint $sprint)
    {
        $this->ensureBoardPermission($board, 'admin');
        abort_unless($sprint->board_id === $board->id, 404);

        if ($sprint->status === 'active') {
            return redirect()->route('boards.show', $board->id)
                ->withErrors(['sprint' => 'Cannot delete an active sprint. Complete it first.']);
        }

        $backlogStatus = WorkflowStatus::where('workflow_group_id', $board->workflow_group_id)
            ->where('is_backlog', 1)->first();

        if ($backlogStatus) {
            $sprint->items()->update(['release_id' => null, 'status_id' => $backlogStatus->id]);
        } else {
            $sprint->items()->update(['release_id' => null]);
        }

        $sprint->delete();

        return redirect()->route('boards.show', $board->id)
            ->with('success', 'Sprint deleted. Items returned to backlog.');
    }

    public function start(Board $board, Sprint $sprint)
    {
        $this->ensureBoardPermission($board, 'admin');
        abort_unless($sprint->board_id === $board->id, 404);

        if ($sprint->status !== 'planned') {
            return redirect()->route('boards.show', $board->id)
                ->withErrors(['sprint' => 'Sprint cannot be started.']);
        }

        $hasActive = Sprint::where('board_id', $board->id)->where('status', 'active')->exists();
        if ($hasActive) {
            return redirect()->route('boards.show', $board->id)
                ->withErrors(['sprint' => 'There is already an active sprint on this board.']);
        }

        $sprint->update([
            'status'     => 'active',
            'start_date' => $sprint->start_date ?? now()->toDateString(),
        ]);

        return redirect()->route('boards.show', $board->id)
            ->with('success', "Sprint \"{$sprint->name}\" started.");
    }

    public function complete(Board $board, Sprint $sprint)
    {
        $this->ensureBoardPermission($board, 'admin');
        abort_unless($sprint->board_id === $board->id, 404);

        if ($sprint->status !== 'active') {
            return redirect()->route('boards.show', $board->id)
                ->withErrors(['sprint' => 'Sprint is not active.']);
        }

        $backlogStatus = WorkflowStatus::where('workflow_group_id', $board->workflow_group_id)
            ->where('is_backlog', 1)->first();

        // All items move to backlog (clears the kanban board).
        // release_id is kept so the completed sprint still shows its item history.
        if ($backlogStatus) {
            $sprint->items()->update(['status_id' => $backlogStatus->id]);
        }

        $sprint->update([
            'status'   => 'completed',
            'end_date' => $sprint->end_date ?? now()->toDateString(),
        ]);

        return redirect()->route('boards.show', $board->id)
            ->with('success', "Sprint \"{$sprint->name}\" completed. Unfinished items returned to backlog.");
    }

    public function addItem(Request $request, Board $board, Sprint $sprint)
    {
        $this->ensureBoardPermission($board, 'member');
        abort_unless($sprint->board_id === $board->id, 404);
        abort_unless(in_array($sprint->status, ['planned', 'active']), 422);

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
}
