<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WorkItem;
use App\Models\Board;
use App\Models\WorkflowStatus;
use App\Http\Traits\ChecksBoardRole;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\BoardSubTeam;

class WorkItemController extends Controller
{
    use ChecksBoardRole;

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
        $this->ensureTaskBelongsToBoard($board, $task);
        $permissionLevel = $this->ensureBoardPermission($board, 'viewer');

        $task->load(['status', 'type', 'priority', 'creator', 'tags', 'comments.user']);
        return view('boards.tasks.showTask', compact('board', 'task', 'permissionLevel'));
    }

    /**
     * Rodyti užduoties pridėjimo formą
     */
    public function create($board_id)
    {
        $board = Board::with('members', 'subTeams.members')->findOrFail($board_id);

        $this->ensureBoardPermission($board, 'member');

        $itemTypes = DB::table('item_types')->get();
        $priorities = DB::table('priorities')->get();

        $statuses = WorkflowStatus::where('workflow_group_id', $board->workflow_group_id)
            ->orderBy('order_index')
            ->get();

        $boardMembers = $board->members;
        $subTeams     = $board->subTeams;

        return view('boards.tasks.createTask', compact('board', 'itemTypes', 'priorities', 'statuses', 'boardMembers', 'subTeams'));
    }

    /**
     * Išsaugoti naują užduotį duomenų bazėje
     */
    public function store(Request $request, Board $board)
    {
        $this->ensureBoardPermission($board, 'member');

        $validated = $request->validate([
            'title'        => 'required|max:200',
            'description'  => 'nullable|string',
            'status_id'    => 'required|exists:workflow_statuses,id',
            'item_type_id' => 'required|exists:item_types,id',
            'priority_id'  => 'nullable|exists:priorities,id',
            'story_points' => 'nullable|integer|min:0|max:100',
            'assignee_type' => 'nullable|in:user,sub_team',
            'assignee_id'   => 'nullable|exists:users,id',
            'sub_team_id'   => 'nullable|exists:board_sub_teams,id',
            'tags'          => 'nullable|array',
            'tags.*'        => 'exists:tags,id',
        ]);

        // Tik vienas gali būti priskirtas
        $assigneeId = null;
        $subTeamId  = null;
        if ($request->assignee_type === 'user') {
            $assigneeId = $request->assignee_id ?: null;
        } elseif ($request->assignee_type === 'sub_team') {
            $subTeamId = $request->sub_team_id ?: null;
        }

        $item = new WorkItem();
        $item->title        = $request->title;
        $item->description  = $request->description;
        $item->item_type_id = $request->item_type_id;
        $item->status_id    = $request->status_id;
        $item->priority_id  = $request->priority_id;
        $item->story_points = $request->story_points;
        $item->team_id      = $board->team_id;
        $item->assignee_id  = $assigneeId;
        $item->sub_team_id  = $subTeamId;

        $user = auth()->user();
        $item->created_by = is_numeric($user->id)
            ? $user->id
            : \DB::table('users')->where('email', $user->email)->value('id');

        $item->save();
        $item->boards()->attach($board->id);

        if ($request->has('tags')) {
            $item->tags()->sync($request->tags);
        }

        return redirect()
            ->route('boards.show', $board->id)
            ->with('success', 'Task created successfully!');
    }

    public function destroy(Board $board, WorkItem $task)
    {
        $this->ensureTaskBelongsToBoard($board, $task);
        $this->ensureBoardPermission($board, 'admin');

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
        $this->ensureTaskBelongsToBoard($board, $task);
        $this->ensureBoardPermission($board, 'member');

        $board->load('members', 'subTeams.members');
        $task->load('assignee', 'subTeam');

        $itemTypes  = \DB::table('item_types')->get();
        $priorities = \DB::table('priorities')->get();

        $statuses = WorkflowStatus::where('workflow_group_id', $board->workflow_group_id)
            ->orderBy('order_index')
            ->get();

        $boardMembers = $board->members;
        $subTeams     = $board->subTeams;

        return view('boards.tasks.editTask', compact(
            'board',
            'task',
            'itemTypes',
            'priorities',
            'statuses',
            'boardMembers',
            'subTeams'
        ));
    }

    public function update(Request $request, Board $board, WorkItem $task)
    {
        $this->ensureTaskBelongsToBoard($board, $task);
        $this->ensureBoardPermission($board, 'member');

        $validated = $request->validate([
            'title'        => 'required|max:200',
            'description'  => 'nullable|string',
            'status_id'    => 'required|exists:workflow_statuses,id',
            'item_type_id' => 'required|exists:item_types,id',
            'priority_id'  => 'nullable|exists:priorities,id',
            'story_points' => 'nullable|integer|min:0|max:100',
            'assignee_type' => 'nullable|in:user,sub_team',
            'assignee_id'   => 'nullable|exists:users,id',
            'sub_team_id'   => 'nullable|exists:board_sub_teams,id',
            'tags'          => 'nullable|array',
            'tags.*'        => 'exists:tags,id',
        ]);

        // Tik vienas gali būti priskirtas
        $assigneeId = null;
        $subTeamId  = null;
        if ($request->assignee_type === 'user') {
            $assigneeId = $request->assignee_id ?: null;
        } elseif ($request->assignee_type === 'sub_team') {
            $subTeamId = $request->sub_team_id ?: null;
        }

        $newStatus   = WorkflowStatus::find($request->status_id);
        $completedAt = ($newStatus && $newStatus->is_done)
            ? ($task->completed_at ?? now())
            : null;

        $task->update([
            'title'        => $request->title,
            'description'  => $request->description,
            'status_id'    => $request->status_id,
            'item_type_id' => $request->item_type_id,
            'priority_id'  => $request->priority_id,
            'story_points' => $request->story_points,
            'completed_at' => $completedAt,
            'assignee_id'  => $assigneeId,
            'sub_team_id'  => $subTeamId,
        ]);

        if ($request->has('tags')) {
            $task->tags()->sync($request->tags);
        } else {
            $task->tags()->sync([]);
        }

        return redirect()
            ->route('boards.show', $board->id)
            ->with('success', 'Task updated!');
    }

    public function updateStatus(Request $request, Board $board, WorkItem $task)
    {
        $this->ensureTaskBelongsToBoard($board, $task);
        $this->ensureBoardPermission($board, 'member');

        $validated = $request->validate([
            'status_id' => 'required|exists:workflow_statuses,id',
        ]);

        $newStatus = WorkflowStatus::find($request->status_id);
        // Preserve the original completed_at if moving to done again; clear it when leaving done
        $completedAt = ($newStatus && $newStatus->is_done)
            ? ($task->completed_at ?? now())
            : null;

        $task->update([
            'status_id'    => $request->status_id,
            'completed_at' => $completedAt,
        ]);

        return response()->json(['success' => true]);
    }

    private function ensureTaskBelongsToBoard(Board $board, WorkItem $task)
    {
        abort_unless($task->boards()->where('boards.id', $board->id)->exists(), 404, 'Task not found on this board.');
    }
}
