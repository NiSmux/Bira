<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Board;
use App\Models\Tag;
use App\Models\WorkItem;
use App\Http\Traits\ChecksBoardRole;

class TagController extends Controller
{
    use ChecksBoardRole;

    public function store(Request $request, Board $board)
    {
        $this->ensureBoardPermission($board, 'member');

        $validated = $request->validate([
            'name' => 'required|string|max:80',
            'color' => 'required|string|max:20'
        ]);

        $tag = $board->tags()->create([
            'name' => $validated['name'],
            'color' => $validated['color'],
            'is_custom' => true,
        ]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'tag' => $tag]);
        }

        return redirect()->back()->with('success', 'Tag created successfully!');
    }

    public function update(Request $request, Board $board, Tag $tag)
    {
        $this->ensureBoardPermission($board, 'member');
        abort_unless($tag->board_id === $board->id, 404);

        $validated = $request->validate([
            'name' => 'required|string|max:80',
            'color' => 'required|string|max:20'
        ]);

        $tag->update([
            'name' => $validated['name'],
            'color' => $validated['color']
        ]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'tag' => $tag]);
        }

        return redirect()->back()->with('success', 'Tag updated successfully!');
    }

    public function destroy(Board $board, Tag $tag)
    {
        $this->ensureBoardPermission($board, 'admin');
        abort_unless($tag->board_id === $board->id, 404);

        $tag->delete();

        return redirect()->back()->with('success', 'Tag deleted!');
    }

    public function destroyBatch(Request $request, Board $board)
    {
        $this->ensureBoardPermission($board, 'admin');
        
        $validated = $request->validate([
            'tag_ids' => 'required|array',
            'tag_ids.*' => 'exists:tags,id'
        ]);

        Tag::where('board_id', $board->id)
            ->whereIn('id', $validated['tag_ids'])
            ->delete();

        return response()->json(['success' => true, 'message' => 'Tags deleted!']);
    }

    public function attach(Request $request, Board $board, WorkItem $task)
    {
        $this->ensureBoardPermission($board, 'member');
        abort_unless($task->boards()->where('boards.id', $board->id)->exists(), 404, 'Task not on board');

        $validated = $request->validate([
            'tag_id' => 'required|exists:tags,id'
        ]);

        $tag = Tag::where('board_id', $board->id)->findOrFail($validated['tag_id']);

        $task->tags()->syncWithoutDetaching([$tag->id]);

        return response()->json(['success' => true, 'tag' => $tag]);
    }

    public function detach(Board $board, WorkItem $task, Tag $tag)
    {
        $this->ensureBoardPermission($board, 'member');
        abort_unless($task->boards()->where('boards.id', $board->id)->exists(), 404, 'Task not on board');
        abort_unless($tag->board_id === $board->id, 404, 'Tag not on board');

        $task->tags()->detach($tag->id);

        return response()->json(['success' => true]);
    }
}
