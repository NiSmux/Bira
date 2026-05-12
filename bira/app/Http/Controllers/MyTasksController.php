<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\WorkItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MyTasksController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::user()->id;

        $query = WorkItem::where('assignee_id', $userId)
            ->whereHas('status', function ($q) {
                $q->where('is_done', false)->where('is_backlog', false);
            })
            ->with(['status', 'type', 'priority', 'boards.team', 'sprint']);

        if ($request->filled('priority_id')) {
            $query->where('priority_id', $request->priority_id);
        }

        if ($request->filled('board_id')) {
            $query->whereHas('boards', fn($q) => $q->where('boards.id', $request->board_id));
        }

        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $query->where('title', 'like', $search);
        }

        $allowedSorts = ['updated_at', 'created_at', 'story_points', 'estimated_hours'];
        $sortBy  = in_array($request->get('sort'), $allowedSorts) ? $request->get('sort') : 'updated_at';
        $sortDir = $request->get('dir') === 'asc' ? 'asc' : 'desc';

        $tasks = $query->orderBy($sortBy, $sortDir)->paginate(20)->withQueryString();

        $userBoards = Board::whereHas('members', fn($q) => $q->where('users.id', $userId))
            ->with('team')
            ->get();

        $priorities = DB::table('priorities')->orderBy('id')->get();

        return view('my-tasks.index', compact('tasks', 'userBoards', 'priorities'));
    }
}
