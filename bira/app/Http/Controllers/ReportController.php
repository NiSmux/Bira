<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\Sprint;
use App\Models\WorkflowStatus;
use App\Http\Traits\ChecksBoardRole;
use Carbon\Carbon;

class ReportController extends Controller
{
    use ChecksBoardRole;

    public function index(Board $board)
    {
        $this->ensureBoardPermission($board, 'viewer');
        return redirect()->route('reports.burndown', $board->id);
    }

    public function burndown(Board $board, Sprint $sprint = null)
    {
        $this->ensureBoardPermission($board, 'viewer');

        if ($sprint) {
            abort_unless($sprint->board_id === $board->id, 404);
        }

        // Sprints that have data worth showing (started or completed)
        $sprints = Sprint::where('board_id', $board->id)
            ->whereIn('status', ['in_progress', 'to_be_released', 'delivered'])
            ->orderByRaw("FIELD(status, 'in_progress', 'to_be_released', 'delivered')")
            ->orderBy('created_at', 'desc')
            ->get();

        // Default to active sprint, then most recent completed
        if (!$sprint) {
            $sprint = $sprints->firstWhere('status', 'in_progress') ?? $sprints->first();
        }

        $chartData = null;

        if ($sprint && $sprint->start_date) {
            $items = $sprint->items()->get();
            $totalPoints = (int) $items->sum('story_points');

            $startDate = $sprint->start_date->copy()->startOfDay();
            $endDate   = ($sprint->end_date ?? $sprint->start_date->copy()->addDays(13))->copy()->endOfDay();
            $today     = now()->startOfDay();
            $totalDays = max((int) $startDate->diffInDays($endDate), 1);

            $labels     = [];
            $actualLine = [];
            $idealLine  = [];
            $dayIndex   = 0;
            $current    = $startDate->copy();

            while ($current->lte($endDate)) {
                $labels[] = $current->format('M d');

                // Ideal burndown: linear from totalPoints on day 0 to 0 on the last day
                $idealLine[] = (int) round($totalPoints * (1 - $dayIndex / $totalDays));

                // Actual: only fill in days that have passed (including today)
                if ($current->lte($today)) {
                    $completedPoints = $items->filter(function ($item) use ($current) {
                        return $item->completed_at
                            && $item->completed_at->startOfDay()->lte($current);
                    })->sum('story_points');

                    $actualLine[] = max(0, $totalPoints - (int) $completedPoints);
                } else {
                    $actualLine[] = null; // future dates show gap on chart
                }

                $current->addDay();
                $dayIndex++;
            }

            // Last known actual value (latest non-null entry)
            $lastActual = collect($actualLine)->filter(fn($v) => $v !== null)->last() ?? $totalPoints;

            $chartData = [
                'labels'           => $labels,
                'actual'           => $actualLine,
                'ideal'            => $idealLine,
                'totalPoints'      => $totalPoints,
                'completedPoints'  => $totalPoints - $lastActual,
                'remainingPoints'  => $lastActual,
            ];
        }

        $sprintItems = $sprint ? $sprint->items()->with(['assignee', 'subTeam', 'priority', 'status', 'type'])->get() : collect();

        return view('reports.burndown', compact('board', 'sprints', 'sprint', 'chartData', 'sprintItems'));
    }

    public function velocity(Board $board)
    {
        $this->ensureBoardPermission($board, 'viewer');

        $sprints = Sprint::where('board_id', $board->id)
            ->whereIn('status', ['to_be_released', 'delivered'])
            ->orderBy('created_at', 'asc')
            ->get();

        $labels    = $sprints->pluck('name')->toArray();
        $committed = $sprints->map(fn($s) => $s->total_points     ?? 0)->toArray();
        $completed = $sprints->map(fn($s) => $s->completed_points ?? 0)->toArray();

        $avgVelocity = count($completed) > 0
            ? round(array_sum($completed) / count($completed), 1)
            : 0;

        return view('reports.velocity', compact('board', 'sprints', 'labels', 'committed', 'completed', 'avgVelocity'));
    }
}
