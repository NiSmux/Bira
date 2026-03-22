<?php

namespace App\Http\Traits;

use App\Models\Board;
use Illuminate\Support\Facades\Auth;

trait ChecksBoardRole
{
    /**
     * All available functional roles with display labels.
     */
    public static function boardRoles(): array
    {
        return [
            'product_owner' => 'Product Owner',
            'techlead'      => 'Tech Lead',
            'teamlead'      => 'Team Lead',
            'fe_dev'        => 'FE Dev',
            'be_dev'        => 'BE Dev',
            'fullstack'     => 'Fullstack',
            'qa'            => 'QA',
            'viewer'        => 'Viewer',
        ];
    }

    /**
     * Map functional role → permission tier.
     */
    public function getPermissionLevel(string $role): string
    {
        return match ($role) {
            'product_owner', 'techlead', 'teamlead' => 'admin',
            'fe_dev', 'be_dev', 'fullstack', 'qa'   => 'member',
            'viewer'                                  => 'viewer',
            default                                   => 'viewer',
        };
    }

    /**
     * Get the current user's functional role on a board (or null).
     */
    public function getBoardRole(Board $board): ?string
    {
        $userId = Auth::user()->id;

        $member = $board->members()->where('users.id', $userId)->first();

        return $member ? $member->pivot->role : null;
    }

    /**
     * Abort 403 if the current user doesn't have at least $minLevel on $board.
     * Levels: admin > member > viewer
     */
    public function ensureBoardPermission(Board $board, string $minLevel): string
    {
        $userId = Auth::user()->id;

        // Team owners get automatic super-admin rights to all boards in their team
        $isTeamOwner = \App\Models\Team::where('id', $board->team_id)
            ->whereHas('members', function ($query) use ($userId) {
                $query->where('users.id', $userId)
                    ->whereIn('team_members.role_in_team', ['owner', 'Admin', 'Owner']);
            })->exists();

        if ($isTeamOwner) {
            return 'admin';
        }

        $role = $this->getBoardRole($board);

        abort_unless($role !== null, 403, 'You are not a member of this board.');

        $level = $this->getPermissionLevel($role);

        $hierarchy = ['viewer' => 0, 'member' => 1, 'admin' => 2];

        abort_unless(
            ($hierarchy[$level] ?? -1) >= ($hierarchy[$minLevel] ?? 99),
            403,
            'You do not have permission to perform this action.'
        );

        return $level;
    }

    /**
     * Quick check: is user at least admin on this board?
     */
    public function isBoardAdmin(Board $board): bool
    {
        $userId = Auth::user()->id;

        $isTeamOwner = \App\Models\Team::where('id', $board->team_id)
            ->whereHas('members', function ($query) use ($userId) {
                $query->where('users.id', $userId)
                    ->whereIn('team_members.role_in_team', ['owner', 'Admin', 'Owner']);
            })->exists();

        if ($isTeamOwner) {
            return true;
        }

        $role = $this->getBoardRole($board);
        return $role && $this->getPermissionLevel($role) === 'admin';
    }
}
