<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\PokerSession;
use App\Models\PokerSessionItem;
use App\Models\PokerVote;
use App\Models\Team;
use App\Models\WorkItem;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PlanningPokerController extends Controller
{
    /**
     * List all poker sessions for the current board
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $boardId = $request->query('board_id');

        if ($boardId) {
            // Show sessions for the specific board
            $board = Board::findOrFail($boardId);

            // Verify the user belongs to the board's team
            if (!$board->team->members()->where('users.id', $user->id)->exists()) {
                abort(403);
            }

            $sessions = PokerSession::where('board_id', $boardId)
                ->with(['team', 'creator', 'items'])
                ->orderByDesc('created_at')
                ->get();
        } else {
            // Fallback: show sessions across all user teams
            $teamIds = $user->teams()->pluck('teams.id');
            $sessions = PokerSession::whereIn('team_id', $teamIds)
                ->with(['team', 'creator', 'items'])
                ->orderByDesc('created_at')
                ->get();
        }

        return view('poker.index', compact('sessions', 'boardId'));
    }

    /**
     * Show form to create a new poker session
     */
    public function create(Request $request)
    {
        $user = Auth::user();
        $teams = $user->teams()->with('boards')->get();
        $selectedTeamId = $request->query('team_id');
        $boardId = $request->query('board_id');

        return view('poker.create', compact('teams', 'selectedTeamId', 'boardId'));
    }

    /**
     * Get work items for a board (AJAX endpoint for dynamic form)
     */
    public function boardItems(Board $board)
    {
        $user = Auth::user();

        // Check user is member of the board's team
        if (!$board->team->members()->where('users.id', $user->id)->exists()) {
            return response()->json([], 403);
        }

        // Only show backlog items (not assigned to active/completed sprints)
        $backlogStatusIds = \App\Models\WorkflowStatus::where('workflow_group_id', $board->workflow_group_id)
            ->where('is_backlog', 1)
            ->pluck('id');

        $items = $board->items()
            ->where('is_deleted', 0)
            ->whereIn('work_items.status_id', $backlogStatusIds)
            ->select('work_items.id', 'work_items.title', 'work_items.story_points')
            ->orderBy('work_items.title')
            ->get();

        return response()->json($items);
    }

    /**
     * Store a new poker session
     */
    public function store(Request $request)
    {
        $request->validate([
            'team_id'    => 'required|integer|exists:teams,id',
            'board_id'   => 'nullable|integer|exists:boards,id',
            'title'      => 'required|string|max:200',
            'time_limit' => 'required|integer|min:1|max:120',
            'work_items' => 'required|array|min:1',
            'work_items.*' => 'integer|exists:work_items,id',
        ]);

        $user = Auth::user();
        $team = Team::findOrFail($request->team_id);

        // Check user is member of the team
        if (!$team->members()->where('users.id', $user->id)->exists()) {
            return redirect()->back()->withErrors(['team_id' => 'You are not a member of this team.']);
        }

        // Create the session (time_limit stored in seconds)
        $session = PokerSession::create([
            'team_id'  => $request->team_id,
            'board_id' => $request->board_id ?: null,
            'title'    => $request->title,
            'time_limit' => $request->time_limit * 60, // convert minutes to seconds
            'status'     => 'active',
            'created_by' => $user->id,
            'created_at' => now(),
        ]);

        // Attach work items
        foreach ($request->work_items as $index => $itemId) {
            PokerSessionItem::create([
                'session_id'   => $session->id,
                'work_item_id' => $itemId,
                'order_index'  => $index,
            ]);
        }

        // Notify team members about the new poker session
        $notifyIds = $team->members()->pluck('users.id')->toArray();

        if (!empty($notifyIds)) {
            $boardName = $request->board_id ? (Board::find($request->board_id)?->name ?? 'N/A') : 'N/A';
            NotificationService::notify(
                $notifyIds,
                'poker_started',
                'Planning Poker Started',
                "Session \"{$request->title}\" started on board \"{$boardName}\"",
                route('poker.show', $session->id)
            );
        }

        return redirect()->route('poker.show', $session->id)
            ->with('success', 'Planning Poker session created!');
    }

    /**
     * Show the voting room
     */
    public function show(PokerSession $session)
    {
        $user = Auth::user();
        $team = $session->team;

        // Check user is a team member
        if (!$team->members()->where('users.id', $user->id)->exists()) {
            abort(403, 'You are not a member of this team.');
        }

        // Auto-complete if timer expired
        if ($session->status === 'active' && $session->isExpired()) {
            $this->finishSession($session);
            $session->refresh();
        }

        // If completed, redirect to results
        if ($session->status === 'completed') {
            return redirect()->route('poker.results', $session->id);
        }

        $session->load(['items.workItem', 'items.votes', 'creator']);
        $members = $team->members;

        // Find the current item to vote on (first item without user's vote)
        $currentItem = null;
        foreach ($session->items as $item) {
            if (!$item->hasUserVoted($user->id)) {
                $currentItem = $item;
                break;
            }
        }

        // If user voted on all items, show the last item
        if (!$currentItem && $session->items->count() > 0) {
            $currentItem = $session->items->last();
        }

        $fibonacciCards = [0, 1, 2, 3, 5, 8, 13, 21, '?'];

        return view('poker.show', compact('session', 'members', 'currentItem', 'fibonacciCards', 'user'));
    }

    /**
     * Record a vote
     */
    public function vote(Request $request, PokerSession $session, PokerSessionItem $item)
    {
        $user = Auth::user();
        $team = $session->team;

        // Validate
        if (!$team->members()->where('users.id', $user->id)->exists()) {
            abort(403);
        }

        if ($session->status !== 'active') {
            return redirect()->route('poker.results', $session->id)
                ->withErrors(['session' => 'This session is already completed.']);
        }

        if ($session->isExpired()) {
            $this->finishSession($session);
            return redirect()->route('poker.results', [
                $session->id,
                'board_id' => $request->query('board_id'),
                'team_id'  => $request->query('team_id'),
            ]);
        }

        $request->validate([
            'points' => 'nullable|integer|in:0,1,2,3,5,8,13,21',
        ]);

        // Upsert vote (update if exists, create if not)
        PokerVote::updateOrCreate(
            [
                'poker_session_item_id' => $item->id,
                'user_id' => $user->id,
            ],
            [
                'points' => $request->points, // null means "?"
                'voted_at' => now(),
            ]
        );

        // Check if all members voted for ALL items → auto-complete
        $allDone = true;
        foreach ($session->items as $sessionItem) {
            if (!$session->allVotedForItem($sessionItem)) {
                $allDone = false;
                break;
            }
        }

        if ($allDone) {
            $this->finishSession($session);
            return redirect()->route('poker.results', [
                $session->id,
                'board_id' => $request->query('board_id'),
                'team_id'  => $request->query('team_id'),
            ])->with('success', 'All votes are in! Here are the results.');
        }

        return redirect()->route('poker.show', [
            $session->id,
            'board_id' => $request->query('board_id'),
            'team_id'  => $request->query('team_id'),
        ])->with('success', 'Vote recorded!');
    }

    /**
     * Manually complete a session (only creator)
     */
    public function complete(PokerSession $session)
    {
        $user = Auth::user();

        if ($session->created_by !== $user->id) {
            abort(403, 'Only the session creator can finish the session.');
        }

        $this->finishSession($session);

        return redirect()->route('poker.results', [
            $session->id,
            'board_id' => request()->query('board_id'),
            'team_id'  => request()->query('team_id'),
        ])->with('success', 'Session completed! Here are the results.');
    }

    /**
     * Show results
     */
    public function results(PokerSession $session)
    {
        $user = Auth::user();
        $team = $session->team;

        if (!$team->members()->where('users.id', $user->id)->exists()) {
            abort(403);
        }

        $session->load(['items.workItem', 'items.votes', 'team', 'creator']);

        // Calculate results per item
        $results = [];
        foreach ($session->items as $item) {
            $results[] = [
                'item' => $item,
                'workItem' => $item->workItem,
                'consensus' => $item->final_points ?? $item->consensusPoints(),
                'voteCount' => $item->votes->count(),
                'totalMembers' => $team->members->count(),
            ];
        }

        return view('poker.results', compact('session', 'results', 'user'));
    }

    /**
     * Save final points to work items
     */
    public function savePoints(Request $request, PokerSession $session)
    {
        $user = Auth::user();

        if ($session->created_by !== $user->id) {
            abort(403, 'Only the session creator can save points.');
        }

        $session->load('items.workItem');

        foreach ($session->items as $item) {
            $consensus = $item->final_points ?? $item->consensusPoints();
            if ($consensus !== null && $item->workItem) {
                $item->workItem->update(['story_points' => $consensus]);
                $item->update(['final_points' => $consensus]);
            }
        }

        return redirect()->route('poker.results', [
            $session->id,
            'board_id' => request()->query('board_id'),
            'team_id'  => request()->query('team_id'),
        ])->with('success', 'Story points saved to all tasks!');
    }

    /**
     * Helper: finish a session and calculate final points
     */
    private function finishSession(PokerSession $session): void
    {
        foreach ($session->items as $item) {
            $consensus = $item->consensusPoints();
            if ($consensus !== null) {
                $item->update(['final_points' => $consensus]);
            }
        }

        $session->update([
            'status' => 'completed',
            'finished_at' => now(),
        ]);

        // Notify team members about completed session
        $session->loadMissing('team.members', 'board');
        $notifyIds = $session->team->members()->pluck('users.id')->toArray();

        if (!empty($notifyIds)) {
            $boardName = $session->board ? $session->board->name : 'N/A';
            NotificationService::notify(
                $notifyIds,
                'poker_completed',
                'Planning Poker Completed',
                "Session \"{$session->title}\" completed on board \"{$boardName}\" — view results",
                route('poker.results', $session->id)
            );
        }
    }
}
