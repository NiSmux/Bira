<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Board;
use App\Models\WorkItem;
use App\Models\WorkItemComment;
use App\Http\Traits\ChecksBoardRole;
use Illuminate\Support\Facades\Auth;

class WorkItemCommentController extends Controller
{
    use ChecksBoardRole;

    public function store(Request $request, Board $board, WorkItem $task)
    {
        $this->ensureBoardPermission($board, 'viewer');
        abort_unless($task->boards()->where('boards.id', $board->id)->exists(), 404, 'Task not on board');

        $validated = $request->validate([
            'body' => 'required|string'
        ]);

        $user = Auth::user();
        $userId = is_numeric($user->id) 
            ? $user->id 
            : \Illuminate\Support\Facades\DB::table('users')->where('email', $user->email ?? $user->id)->value('id');

        $task->comments()->create([
            'user_id' => $userId,
            'body' => $validated['body']
        ]);

        return redirect()->back()->with('success', 'Comment posted!');
    }

    public function destroy(Board $board, WorkItem $task, WorkItemComment $comment)
    {
        abort_unless($task->boards()->where('boards.id', $board->id)->exists(), 404, 'Task not on board');
        abort_unless($comment->work_item_id === $task->id, 404, 'Comment does not belong to task');

        $user = Auth::user();
        $userId = is_numeric($user->id) 
            ? $user->id 
            : \Illuminate\Support\Facades\DB::table('users')->where('email', $user->email ?? $user->id)->value('id');

        if ($comment->user_id != $userId) {
            $this->ensureBoardPermission($board, 'admin');
        }

        $comment->delete();

        return redirect()->back()->with('success', 'Comment deleted!');
    }
}
