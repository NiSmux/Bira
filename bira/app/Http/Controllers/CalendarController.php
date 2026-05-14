<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TimeLog;
use App\Models\CalendarNote;
use App\Models\WorkItem;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CalendarController extends Controller
{
    /**
     * Full calendar page.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $date = $request->query('date') ? Carbon::parse($request->query('date')) : Carbon::now();
        $year  = (int) $date->format('Y');
        $month = (int) $date->format('n');

        // Days in month that have any log/note for this user
        $activeDays = $this->getActiveDays($user->id, $year, $month);

        $userId = $user->id;
        $myBoards = \App\Models\Board::where(function ($query) use ($userId) {
            $query->whereHas('members', function ($q) use ($userId) {
                $q->where('users.id', $userId);
            })->orWhereHas('team.members', function ($q) use ($userId) {
                $q->where('users.id', $userId)
                  ->whereIn('team_members.role_in_team', ['owner', 'Admin', 'Owner']);
            });
        })->get(['id', 'name']);

        $focusDate = $request->query('date') ? $date->format('Y-m-d') : null;

        return view('calendar.index', compact('year', 'month', 'activeDays', 'myBoards', 'focusDate'));
    }

    /**
     * AJAX: return which days in a month have entries (for dot indicators).
     */
    public function monthData(Request $request)
    {
        $user  = Auth::user();
        $year  = (int) $request->query('year', now()->year);
        $month = (int) $request->query('month', now()->month);

        return response()->json([
            'active_days' => $this->getActiveDays($user->id, $year, $month),
        ]);
    }

    /**
     * AJAX: return data for a single day.
     */
    public function dayData(Request $request, string $date)
    {
        $user  = Auth::user();
        $carbon = Carbon::parse($date);

        // Note for this day
        $note = CalendarNote::where('user_id', $user->id)
            ->where('note_date', $carbon->toDateString())
            ->first();

        // Time logs for this day
        $logs = TimeLog::where('user_id', $user->id)
            ->where('logged_date', $carbon->toDateString())
            ->with(['workItem.boards'])
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(fn($log) => [
                'id'           => $log->id,
                'task_title'   => $log->workItem ? '[' . ($log->workItem->boards->first()->name ?? 'No Board') . '] ' . $log->workItem->title : 'General',
                'work_item_id' => $log->work_item_id,
                'minutes'      => $log->minutes,
                'duration'     => $log->duration,
                'note'         => $log->note,
                'created_at'   => $log->created_at->format('H:i'),
            ]);

        // Total minutes for the day
        $totalMinutes = $logs->sum('minutes');
        $totalH = intdiv($totalMinutes, 60);
        $totalM = $totalMinutes % 60;
        $totalDuration = $totalH > 0 ? ($totalM > 0 ? "{$totalH}h {$totalM}m" : "{$totalH}h") : "{$totalM}m";

        return response()->json([
            'date'           => $carbon->toDateString(),
            'day_label'      => $carbon->format('l, F j, Y'),
            'note'           => $note?->content ?? '',
            'logs'           => $logs,
            'total_duration' => $totalDuration,
            'total_minutes'  => $totalMinutes,
        ]);
    }

    /**
     * POST: create or update the daily note.
     */
    public function storeNote(Request $request)
    {
        $request->validate([
            'note_date' => 'required|date',
            'content'   => 'nullable|string|max:5000',
        ]);

        $user = Auth::user();

        CalendarNote::updateOrCreate(
            ['user_id' => $user->id, 'note_date' => $request->note_date],
            ['content' => $request->content ?? '']
        );

        return response()->json(['success' => true]);
    }

    /**
     * POST: log time from the calendar panel.
     */
    public function storeTimeLog(Request $request)
    {
        $request->validate([
            'logged_date'  => 'required|date',
            'hours'        => 'required_without:minutes|nullable|integer|min:0|max:999',
            'minutes'      => 'required_without:hours|nullable|integer|min:0|max:59',
            'work_item_id' => 'nullable|exists:work_items,id',
            'note'         => 'nullable|string|max:500',
        ]);

        $totalMinutes = (((int) $request->hours) * 60) + ((int) $request->minutes);
        if ($totalMinutes <= 0) {
            return response()->json(['error' => 'Time must be greater than 0.'], 422);
        }

        $log = TimeLog::create([
            'user_id'      => Auth::id(),
            'work_item_id' => $request->work_item_id ?: null,
            'logged_date'  => $request->logged_date,
            'minutes'      => $totalMinutes,
            'note'         => $request->note,
        ]);

        $log->load('workItem.boards');

        return response()->json([
            'success'  => true,
            'log'      => [
                'id'           => $log->id,
                'task_title'   => $log->workItem ? '[' . ($log->workItem->boards->first()->name ?? 'No Board') . '] ' . $log->workItem->title : 'General',
                'work_item_id' => $log->work_item_id,
                'minutes'      => $log->minutes,
                'duration'     => $log->duration,
                'note'         => $log->note,
                'created_at'   => $log->created_at->format('H:i'),
            ],
        ]);
    }

    /**
     * DELETE: remove a time log entry.
     */
    public function destroyTimeLog(TimeLog $timeLog)
    {
        abort_unless($timeLog->user_id === Auth::id(), 403);
        $timeLog->delete();
        return response()->json(['success' => true]);
    }

    /**
     * AJAX: Get tasks for a specific board
     */
    public function getBoardTasks($boardId)
    {
        $userId = Auth::id();
        
        // Ensure user has access to this board
        $hasAccess = \App\Models\Board::where('id', $boardId)
            ->where(function ($query) use ($userId) {
                $query->whereHas('members', function ($q) use ($userId) {
                    $q->where('users.id', $userId);
                })->orWhereHas('team.members', function ($q) use ($userId) {
                    $q->where('users.id', $userId)
                      ->whereIn('team_members.role_in_team', ['owner', 'Admin', 'Owner']);
                });
            })->exists();
            
        if (!$hasAccess) {
            return response()->json([]);
        }

        $tasks = WorkItem::whereHas('boards', function($q) use ($boardId) {
                $q->where('boards.id', $boardId);
            })
            ->whereHas('status', function($q) {
                $q->where('is_done', false)->where('is_backlog', false);
            })
            ->select('id', 'title')
            ->orderBy('updated_at', 'desc')
            ->get();

        return response()->json($tasks);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function getActiveDays(int $userId, int $year, int $month): array
    {
        $start = \Carbon\Carbon::create($year, $month, 1)->startOfMonth()->toDateString();
        $end   = \Carbon\Carbon::create($year, $month, 1)->endOfMonth()->toDateString();

        $logs = TimeLog::where('user_id', $userId)
            ->whereBetween('logged_date', [$start, $end])
            ->get();

        $notes = CalendarNote::where('user_id', $userId)
            ->whereBetween('note_date', [$start, $end])
            ->where('content', '!=', '')
            ->get();

        $activeDays = [];

        foreach ($notes as $note) {
            $day = \Carbon\Carbon::parse($note->note_date)->day;
            if (!isset($activeDays[$day])) $activeDays[$day] = ['note' => '', 'minutes' => 0];
            $activeDays[$day]['note'] = $note->content;
        }

        foreach ($logs as $log) {
            $day = \Carbon\Carbon::parse($log->logged_date)->day;
            if (!isset($activeDays[$day])) $activeDays[$day] = ['note' => '', 'minutes' => 0];
            $activeDays[$day]['minutes'] += $log->minutes;
        }

        foreach ($activeDays as $day => &$data) {
            $m = $data['minutes'];
            if ($m > 0) {
                $h = intdiv($m, 60);
                $rem = $m % 60;
                $data['duration'] = $h > 0 ? ($rem > 0 ? "{$h}h {$rem}m" : "{$h}h") : "{$rem}m";
            } else {
                $data['duration'] = null;
            }
        }

        return $activeDays;
    }
}
