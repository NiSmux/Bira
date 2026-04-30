<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WorkItem;
use App\Models\Board;
use App\Models\Sprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        if (!Auth::check()) {
            return view('pagrindinis');
        }

        $user = Auth::user();

        // 1. User Statistics
        $totalTasks = WorkItem::where('assignee_id', $user->id)
            ->whereHas('status', function($q) {
                $q->where('is_done', false);
            })->count();
            
        $totalDoneTasks = WorkItem::where('assignee_id', $user->id)
            ->whereHas('status', function($q) {
                $q->where('is_done', true);
            })->count();

        // Story points & Time in active sprints
        $activeSprints = Sprint::where('status', 'in_progress')
            ->whereHas('board.members', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->with('board')
            ->get();

        $storyPointsDone = 0;
        $storyPointsLeft = 0;
        $hoursDone = 0;
        $hoursLeft = 0;

        foreach ($activeSprints as $sprint) {
            $mode = $sprint->board->estimation_mode ?? 'points';

            $tasks = WorkItem::where('release_id', $sprint->id)
                ->where('assignee_id', $user->id)
                ->with('status')
                ->get();
                
            foreach ($tasks as $task) {
                if ($task->status && $task->status->is_done) {
                    if ($mode === 'points') {
                        $storyPointsDone += $task->story_points ?? 0;
                    } else {
                        $hoursDone += $task->estimated_hours ?? 0;
                    }
                } else {
                    if ($mode === 'points') {
                        $storyPointsLeft += $task->story_points ?? 0;
                    } else {
                        $hoursLeft += $task->estimated_hours ?? 0;
                    }
                }
            }
        }

        // 2. Notifications
        $notifications = DB::table('notifications')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(8)
            ->get();

        // 3. Last boards user was in
        $recentBoards = Board::whereHas('members', function($q) use ($user) {
                $q->where('users.id', $user->id);
            })
            ->join('board_members', function($join) use ($user) {
                $join->on('boards.id', '=', 'board_members.board_id')
                     ->where('board_members.user_id', '=', $user->id);
            })
            ->select('boards.*', 'board_members.last_accessed_at')
            ->orderBy('last_accessed_at', 'desc')
            ->take(2)
            ->get();

        // 4. Latest changes related to user (recent tasks assigned, updated, etc)
        $recentTasks = WorkItem::where(function($q) use ($user) {
                $q->where('assignee_id', $user->id)
                  ->orWhere('created_by', $user->id);
            })
            ->with(['status', 'type', 'priority'])
            ->orderBy('updated_at', 'desc')
            ->take(6)
            ->get();

        $activeSprintsCount = $activeSprints->count();

        return view('pagrindinis', compact(
            'totalTasks',
            'totalDoneTasks',
            'storyPointsDone',
            'storyPointsLeft',
            'hoursDone',
            'hoursLeft',
            'notifications',
            'recentBoards',
            'recentTasks',
            'activeSprintsCount'
        ));
    }
}
