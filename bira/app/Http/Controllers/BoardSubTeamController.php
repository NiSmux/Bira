<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\BoardSubTeam;
use App\Models\User;
use App\Http\Traits\ChecksBoardRole;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BoardSubTeamController extends Controller
{
    use ChecksBoardRole;

    /**
     * Tik admin rolės gali valdyti sub-teams.
     */
    private function ensureAdmin(Board $board): void
    {
        $this->ensureBoardPermission($board, 'admin');
    }

    /**
     * Sukurti naują sub-komandą.
     */
    public function store(Request $request, Board $board)
    {
        $this->ensureAdmin($board);

        $validated = $request->validate([
            'name' => [
                'required', 'string', 'max:120',
                \Illuminate\Validation\Rule::unique('board_sub_teams')
                    ->where(fn($q) => $q->where('board_id', $board->id)),
            ],
        ], [
            'name.unique' => 'A sub-team with this name already exists on this board.',
        ]);

        $user = auth()->user();
        $userId = is_numeric($user->id)
            ? $user->id
            : \DB::table('users')->where('email', $user->email)->value('id');

        BoardSubTeam::create([
            'board_id'   => $board->id,
            'name'       => $validated['name'],
            'created_by' => $userId,
        ]);

        return redirect()->route('boards.settings', $board->id)
            ->with('success', 'Sub-team created successfully.');
    }

    /**
     * Atnaujinti sub-komandos pavadinimą.
     */
    public function update(Request $request, Board $board, BoardSubTeam $subTeam)
    {
        $this->ensureAdmin($board);
        abort_unless($subTeam->board_id === $board->id, 404);

        $validated = $request->validate([
            'name' => [
                'required', 'string', 'max:120',
                \Illuminate\Validation\Rule::unique('board_sub_teams')
                    ->where(fn($q) => $q->where('board_id', $board->id))
                    ->ignore($subTeam->id),
            ],
        ], [
            'name.unique' => 'A sub-team with this name already exists on this board.',
        ]);

        $subTeam->update(['name' => $validated['name']]);

        return redirect()->route('boards.settings', $board->id)
            ->with('success', 'Sub-team updated.');
    }

    /**
     * Ištrinti sub-komandą.
     */
    public function destroy(Board $board, BoardSubTeam $subTeam)
    {
        $this->ensureAdmin($board);
        abort_unless($subTeam->board_id === $board->id, 404);

        $subTeam->delete();

        return redirect()->route('boards.settings', $board->id)
            ->with('success', 'Sub-team deleted.');
    }

    /**
     * Pridėti narį į sub-komandą.
     */
    public function addMember(Request $request, Board $board, BoardSubTeam $subTeam)
    {
        $this->ensureAdmin($board);
        abort_unless($subTeam->board_id === $board->id, 404);

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        // Narys turi būti board'o narys
        $isBoardMember = $board->members()->where('users.id', $validated['user_id'])->exists();
        abort_unless($isBoardMember, 422, 'User must be a board member first.');

        // Patikrinti ar jau nėra šiame sub-teame
        $alreadyIn = $subTeam->members()->where('users.id', $validated['user_id'])->exists();
        if ($alreadyIn) {
            return back()->withErrors(['user_id' => 'This user is already in the sub-team.']);
        }

        $subTeam->members()->attach($validated['user_id']);

        // Notify the added user
        NotificationService::notify(
            [$validated['user_id']],
            'subteam_added',
            'Added to Sub-Team',
            "You were added to sub-team \"{$subTeam->name}\" on board \"{$board->name}\"",
            route('boards.settings', $board->id)
        );

        return redirect()->route('boards.settings', $board->id)
            ->with('success', 'Member added to sub-team.');
    }

    /**
     * Pašalinti narį iš sub-komandos.
     */
    public function removeMember(Board $board, BoardSubTeam $subTeam, User $user)
    {
        $this->ensureAdmin($board);
        abort_unless($subTeam->board_id === $board->id, 404);

        $subTeam->members()->detach($user->id);

        return redirect()->route('boards.settings', $board->id)
            ->with('success', 'Member removed from sub-team.');
    }
}
