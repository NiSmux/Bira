<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WorkItem;
use App\Models\Board;
use App\Models\WorkflowStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class WorkItemController extends Controller
{
    /**
     * Rodyti visas lentas arba pagrindinį puslapį
     */
    public function index()
    {
        $boards = Board::all();
        return view('pagrindinis', compact('boards'));
    }

    /**
     * Rodyti konkrečią užduotį
     */
    public function show(Board $board, WorkItem $task)
    {
        $task->load(['status', 'type', 'priority', 'creator']);
        return view('boards.tasks.showTask', compact('board', 'task'));
    }

    /**
     * Rodyti užduoties pridėjimo formą
     */
    public function create($board_id)
    {
        $board = Board::findOrFail($board_id);
        
        // Paimame duomenis pasirinkimo laukams (selects)
        $itemTypes = DB::table('item_types')->get();
        $priorities = DB::table('priorities')->get();
        
        // Svarbu: paimame statusus (stulpelius), kurie priklauso šios lentos procesui
        $statuses = WorkflowStatus::where('workflow_group_id', $board->workflow_group_id)
            ->orderBy('order_index')
            ->get();

        return view('boards.tasks.createTask', compact('board', 'itemTypes', 'priorities', 'statuses'));
    }

    /**
     * Išsaugoti naują užduotį duomenų bazėje
     */
   public function store(Request $request, Board $board)
    {
        $validated = $request->validate([
            'title' => 'required|max:200',
            'description' => 'nullable|string',
            'status_id' => 'required|exists:workflow_statuses,id',
            'item_type_id' => 'required|exists:item_types,id',
            'priority_id' => 'nullable|exists:priorities,id',
            'story_points' => 'nullable|integer|min:0|max:100',
        ]);

        $item = new WorkItem();
        $item->title = $request->title;
        $item->description = $request->description;
        $item->item_type_id = $request->item_type_id;
        $item->status_id = $request->status_id;
        $item->priority_id = $request->priority_id;
        $item->story_points = $request->story_points;
        $item->team_id = $board->team_id;

        $user = auth()->user();

        if (is_numeric($user->id)) {
            $item->created_by = $user->id;
        } else {
            $item->created_by = \DB::table('users')
                ->where('email', $user->email)
                ->value('id');
        }

        $item->save();

        $item->boards()->attach($board->id);

        return redirect()
            ->route('boards.show', $board->id)
            ->with('success', 'Task created successfully!');
    }

    public function destroy(Board $board, WorkItem $task)
    {
        // Atjungiam nuo lentos
        $task->boards()->detach($board->id);

        // Jei užduotis priklauso tik šiai lentai – galim ištrinti visiškai
        if ($task->boards()->count() === 0) {
            $task->delete();
        }

        return redirect()
            ->route('boards.show', $board->id)
            ->with('success', 'Task deleted!');
    }

    public function edit(Board $board, WorkItem $task)
    {
        $itemTypes = \DB::table('item_types')->get();
        $priorities = \DB::table('priorities')->get();

        $statuses = WorkflowStatus::where('workflow_group_id', $board->workflow_group_id)
            ->orderBy('order_index')
            ->get();

        return view('boards.tasks.editTask', compact(
            'board',
            'task',
            'itemTypes',
            'priorities',
            'statuses'
        ));
    }
    public function update(Request $request, Board $board, WorkItem $task)
    {
        $validated = $request->validate([
            'title' => 'required|max:200',
            'description' => 'nullable|string',
            'status_id' => 'required|exists:workflow_statuses,id',
            'item_type_id' => 'required|exists:item_types,id',
            'priority_id' => 'nullable|exists:priorities,id',
            'story_points' => 'nullable|integer|min:0|max:100',
        ]);

        $task->update([
            'title' => $request->title,
            'description' => $request->description,
            'status_id' => $request->status_id,
            'item_type_id' => $request->item_type_id,
            'priority_id' => $request->priority_id,
            'story_points' => $request->story_points,
        ]);

        return redirect()
            ->route('boards.show', $board->id)
            ->with('success', 'Task updated!');
    }

    public function updateStatus(Request $request, Board $board, WorkItem $task)
    {
        $validated = $request->validate([
            'status_id' => 'required|exists:workflow_statuses,id',
        ]);

        $task->update([
            'status_id' => $request->status_id,
        ]);

        return response()->json(['success' => true]);
    }
}
