<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\WorkflowStatus;
use Illuminate\Http\Request;

class BoardController extends Controller
{
    /**
     * Display a listing of the boards.
     */
    public function index()
    {
        $boards = Board::all();
        return view('boards.index', compact('boards'));
    }

    /**
     * Show the form for creating a new board.
     */
    public function create()
    {
        return view('boards.create');
    }

    /**
     * Store a newly created board in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $board = Board::create([
            'name' => $validated['name'],
            'team_id' => 1,
            'workflow_group_id' => 1,
        ]);

        return redirect()->route('boards.show', $board->id)->with('success', 'Lenta sėkmingai sukurta!');
    }

    /**
     * Display the specified board.
     */
    public function show($id)
    {
        $board = Board::findOrFail($id);

        $statuses = WorkflowStatus::where('workflow_group_id', $board->workflow_group_id)
            ->orderBy('order_index')
            ->get();

        return view('boards.show', compact('board', 'statuses'));
    }
}
