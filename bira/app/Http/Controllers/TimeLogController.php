<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TimeLog;
use App\Models\Board;
use App\Models\WorkItem;
use App\Http\Traits\ChecksBoardRole;
use Illuminate\Support\Facades\Auth;

class TimeLogController extends Controller
{
    use ChecksBoardRole;

    /**
     * POST /boards/{board}/tasks/{task}/time-logs
     * Log time spent on a task directly from the task detail view.
     */
    public function store(Request $request, Board $board, WorkItem $task)
    {
        $this->ensureBoardPermission($board, 'viewer');

        $request->validate([
            'hours'      => 'required_without:minutes|nullable|integer|min:0|max:999',
            'minutes'    => 'required_without:hours|nullable|integer|min:0|max:59',
            'logged_date'=> 'nullable|date',
            'note'       => 'nullable|string|max:500',
        ]);

        $totalMinutes = (((int) $request->hours) * 60) + ((int) $request->minutes);
        if ($totalMinutes <= 0) {
            return back()->withErrors(['time' => 'Please enter a time greater than 0.'])->withInput();
        }

        $loggedDate = $request->logged_date ?: now()->toDateString();

        TimeLog::create([
            'user_id'      => Auth::id(),
            'work_item_id' => $task->id,
            'logged_date'  => $loggedDate,
            'minutes'      => $totalMinutes,
            'note'         => $request->note,
        ]);

        $redirectTo = $request->query('redirect_to');
        if ($redirectTo) {
            return redirect($redirectTo)->with('success', 'Time logged successfully!');
        }

        return back()->with('success', 'Time logged successfully!');
    }

    /**
     * DELETE /boards/{board}/tasks/{task}/time-logs/{timeLog}
     * Remove a time log entry (own entries only, or admin).
     */
    public function destroy(Board $board, WorkItem $task, TimeLog $timeLog)
    {
        $permissionLevel = $this->ensureBoardPermission($board, 'viewer');

        // Only owner or admin can delete
        if ($timeLog->user_id !== Auth::id() && $permissionLevel !== 'admin') {
            abort(403);
        }

        $timeLog->delete();

        return back()->with('success', 'Time log removed.');
    }
}
