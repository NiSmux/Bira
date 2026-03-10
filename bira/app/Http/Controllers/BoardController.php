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
        'name' => 'Laukia',
        'order_index' => 1,
        'is_done' => 0,
    ]);

    WorkflowStatus::create([
        'workflow_group_id' => $workflowGroup->id,
        'name' => 'Daroma',
        'order_index' => 2,
        'is_done' => 0,
    ]);

    WorkflowStatus::create([
        'workflow_group_id' => $workflowGroup->id,
        'name' => 'Atlikta',
        'order_index' => 3,
        'is_done' => 1,
    ]);

    $board = Board::create([
        'name' => $validated['name'],
        'team_id' => $team->id,
        'workflow_group_id' => $workflowGroup->id,
    ]);

    return redirect()->route('boards.show', $board->id)
        ->with('success', 'Lenta sėkmingai sukurta!');
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
}