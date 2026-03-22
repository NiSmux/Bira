<?php

namespace App\Http\Controllers;

use App\Models\Board;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Traits\ChecksBoardRole;

class BacklogController extends Controller
{
    use ChecksBoardRole;

    /**
     * Display a listing of all backlogs across all boards the user has access to.
     */
    public function index()
    {
        $userId = Auth::user()->id;

        // Fetch all boards the user belongs to (directly or via team owner override)
        $boards = Board::with(['team', 'items' => function ($query) {
                // Pre-load only items that belong to a backlog status
                $query->whereHas('status', function ($q) {
                    $q->where('is_backlog', 1);
                })->with(['priority', 'type', 'creator']);
            }])
            ->where(function ($query) use ($userId) {
                $query->whereHas('members', function ($q) use ($userId) {
                    $q->where('users.id', $userId);
                })->orWhereHas('team.members', function ($q) use ($userId) {
                    $q->where('users.id', $userId)
                      ->whereIn('team_members.role_in_team', ['owner', 'Admin', 'Owner']);
                });
            })
            ->get();

        return view('backlog.index', compact('boards'));
    }
}
