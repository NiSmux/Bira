<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class ProfilisController extends Controller
{
    /**
     * Rodyti vartotojo profilio puslapį
     */
    use \App\Http\Traits\ChecksBoardRole;
    /**
     * Rodyti vartotojo profilio puslapį
     */
    public function show(Request $request, $id = null)
    {
        $boardId = $request->query('board_id');
        $teamId  = $request->query('team_id');

        $board = $boardId ? \App\Models\Board::with('team')->find($boardId) : null;
        $team  = $teamId ? \App\Models\Team::find($teamId) : ($board ? $board->team : null);

        // Jei ID nepateiktas, žiūrime savo profilį, jei pateiktas - konkretų vartotoją
        if ($id) {
            $user = \App\Models\User::find($id);
            if (!$user) {
                return redirect()->route('pagrindinis')->with('error', 'User not found');
            }
        } else {
            $user = Auth::user();
        }

        $isOwnProfile = Auth::check() && Auth::user()->id == $user->id;

        // Access control: only admins or owners can view other members' profiles
        if (!$isOwnProfile) {
            $isAuthorized = false;

            // 1. Check if user is a team owner of any team the target user is in
            // If a specific team context is provided, check ownership of that team first
            if ($team) {
                $isRequesterOwnerOfTeam = \DB::table('team_members')
                    ->where('team_id', $team->id)
                    ->where('user_id', Auth::user()->id)
                    ->whereIn('role_in_team', ['owner', 'Admin', 'Owner', 'team_owner'])
                    ->exists();
                
                $isTargetInTeam = \DB::table('team_members')
                    ->where('team_id', $team->id)
                    ->where('user_id', $user->id)
                    ->exists();

                if ($isRequesterOwnerOfTeam && $isTargetInTeam) {
                    $isAuthorized = true;
                }
            }

            // General fallback: check if requester is owner of ANY team the target is in
            if (!$isAuthorized) {
                $isTeamOwnerAny = \DB::table('team_members as tm1')
                    ->join('team_members as tm2', 'tm1.team_id', '=', 'tm2.team_id')
                    ->where('tm1.user_id', Auth::user()->id)
                    ->whereIn('tm1.role_in_team', ['owner', 'Admin', 'Owner', 'team_owner'])
                    ->where('tm2.user_id', $user->id)
                    ->exists();

                if ($isTeamOwnerAny) {
                    $isAuthorized = true;
                }
            }

            // 2. Check if user is a board admin on the provided board
            if (!$isAuthorized && $board) {
                if ($this->isBoardAdmin($board)) {
                    $isTargetOnBoard = \DB::table('board_members')
                        ->where('board_id', $board->id)
                        ->where('user_id', $user->id)
                        ->exists();
                    if ($isTargetOnBoard) {
                        $isAuthorized = true;
                    }
                }
            }

            if (!$isAuthorized) {
                if (!$board && !$team) {
                    return redirect()->route('pagrindinis')->with('error', 'Viewing member profiles is only allowed within a board/team context or for team owners.');
                }
                return redirect()->route('pagrindinis')->with('error', 'Only board admins or team owners can view member profiles.');
            }
        }

        // Rolės pavadinimas (Global role)
        $role = DB::table('roles')->where('id', $user->role_id)->first();

        // Board-specific role if board context is present
        $boardRole = null;
        if ($board) {
            $boardMember = \DB::table('board_members')
                ->where('board_id', $board->id)
                ->where('user_id', $user->id)
                ->first();
            if ($boardMember) {
                $boardRoleLabel = self::boardRoles()[$boardMember->role] ?? $boardMember->role;
                $boardRole = $boardRoleLabel;
            }
        }

        // Base query for tasks, optionally scoped to board or team
        $itemsQuery = DB::table('work_items')->where('work_items.is_deleted', 0);
        if ($board) {
            $itemsQuery->join('board_items', 'work_items.id', '=', 'board_items.item_id')
                       ->where('board_items.board_id', $board->id);
        } elseif ($team) {
            $itemsQuery->where('work_items.team_id', $team->id);
        }

        // Statistika: sukurtų užduočių skaičius
        $sukurtuUzduociu = (clone $itemsQuery)->where('work_items.created_by', $user->id)->count();

        // Statistika: priskirtų užduočių skaičius
        $priskirtuUzduociu = (clone $itemsQuery)->where('work_items.assignee_id', $user->id)->count();

        // Statistika: atliktų užduočių skaičius (statusas is_done = 1)
        $atliktaUzduociu = (clone $itemsQuery)
            ->join('workflow_statuses', 'work_items.status_id', '=', 'workflow_statuses.id')
            ->where(function ($q) use ($user) {
                $q->where('work_items.created_by', $user->id)
                  ->orWhere('work_items.assignee_id', $user->id);
            })
            ->where('workflow_statuses.is_done', 1)
            ->count();

        // Paskutinės 5 sukurtos / priskirtos užduotys
        $paskutinesUzduotys = (clone $itemsQuery)
            ->join('workflow_statuses', 'work_items.status_id', '=', 'workflow_statuses.id')
            ->join('item_types', 'work_items.item_type_id', '=', 'item_types.id')
            ->leftJoin('priorities', 'work_items.priority_id', '=', 'priorities.id')
            ->where(function ($q) use ($user) {
                $q->where('work_items.created_by', $user->id)
                  ->orWhere('work_items.assignee_id', $user->id);
            })
            ->orderByDesc('work_items.updated_at')
            ->limit(5)
            ->select(
                'work_items.id',
                'work_items.title',
                'work_items.story_points',
                'work_items.updated_at',
                'workflow_statuses.name as statusas',
                'workflow_statuses.is_done',
                'item_types.name as tipas',
                'priorities.name as prioritetas',
                'work_items.team_id'
            )
            ->get();

        // Komandos, kuriose dalyvauja vartotojas
        $teams = DB::table('team_members')
            ->join('teams', 'team_members.team_id', '=', 'teams.id')
            ->where('team_members.user_id', $user->id)
            ->select('teams.id', 'teams.name', 'teams.description', 'team_members.role_in_team', 'team_members.joined_at')
            ->get();

        return view('profilis.rodyti', compact(
            'user',
            'role',
            'teams',
            'isOwnProfile',
            'sukurtuUzduociu',
            'priskirtuUzduociu',
            'atliktaUzduociu',
            'paskutinesUzduotys',
            'board',
            'boardRole',
            'team'
        ));
    }

    /**
     * Rodyti profilio redagavimo formą
     */
    public function edit()
    {
        $user = Auth::user();
        return view('profilis.redaguoti', compact('user'));
    }

    /**
     * Atnaujinti profilio informaciją
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name'  => 'required|string|min:2|max:120',
            'email' => 'required|email:rfc,dns|max:190|unique:users,email,' . $user->id,
        ]);

        DB::table('users')
            ->where('id', $user->id)
            ->update([
                'name'  => $validated['name'],
                'email' => $validated['email'],
            ]);

        return redirect()->route('profilis.rodyti')->with('success', 'Profile updated successfully!');
    }

    /**
     * Pakeisti slaptažodį
     */
    public function keistiSlaptazodi(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'dabartinis_slaptazodis' => ['required', 'string'],
            'naujas_slaptazodis'     => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        // 1. Patikrinti ar dabartinis slaptažodis teisingas
        if (!Hash::check($request->dabartinis_slaptazodis, $user->password_hash)) {
            return back()->withErrors([
                'dabartinis_slaptazodis' => 'Current password is incorrect.',
            ]);
        }

        // 2. Drausti keisti į tą patį slaptažodį
        if (Hash::check($request->naujas_slaptazodis, $user->password_hash)) {
            return back()->withErrors([
                'naujas_slaptazodis' => 'New password cannot be the same as current password.',
            ]);
        }

        DB::table('users')
            ->where('id', $user->id)
            ->update(['password_hash' => Hash::make($request->naujas_slaptazodis)]);

        return redirect()->route('profilis.rodyti')->with('success', 'Password changed successfully!');
    }

    /**
     * Visiškai ištrinti vartotojo profilį
     */
    public function destroy(Request $request)
    {
        $user = Auth::user();

        // 1. Surandame visas komandas, kurioms priklauso šis vartotojas
        $userTeams = DB::table('team_members')
            ->where('user_id', $user->id)
            ->pluck('team_id');

        foreach ($userTeams as $teamId) {
            // Patikriname, kiek narių iš viso turi ši komanda
            $membersCount = DB::table('team_members')
                ->where('team_id', $teamId)
                ->count();

            // Jei vartotojas yra paskutinis narys šiame teame
            if ($membersCount <= 1) {
                // Ištriname visas komandos užduotis (work_items)
                // Kadangi 'work_items' turi FK į 'teams', o 'board_items' priklauso 'work_items',
                // turime išvalyti viską, kas susiję su šia komanda.
                
                // Pirmiausia surandame visus komandos work_items_id
                $itemIds = DB::table('work_items')->where('team_id', $teamId)->pluck('id');
                
                // Išvalome sąsajas su lentomis, kurios galbūt nepriklauso šiai tarnybai (saugumo dėlei)
                DB::table('board_items')->whereIn('item_id', $itemIds)->delete();
                
                // Ištriname pačias užduotis
                DB::table('work_items')->where('team_id', $teamId)->delete();
                
                // Ištriname lentas (boards turi fk į teams su cascade, bet užduotis prieš tai išvalėme rankiniu būdu)
                DB::table('boards')->where('team_id', $teamId)->delete();
                
                // Ištriname komandą (prieš tai išsitrina team_members per cascade)
                DB::table('teams')->where('id', $teamId)->delete();
            }
        }

        // 2. Jei liko užduočių kituose team'uose, kuriuos šis vartotojas SUKŪRĖ, 
        // priskiriame jas kitam vartotojui, kad DB neleistų klaidų dėl 'created_by' FK.
        $kitaVartotojoId = DB::table('users')
            ->where('id', '!=', $user->id)
            ->value('id');

        if ($kitaVartotojoId) {
            DB::table('work_items')
                ->where('created_by', $user->id)
                ->update(['created_by' => $kitaVartotojoId]);
        }

        // 3. Atsijungiame ir ištriname vartotoją
        Auth::logout();
        DB::table('users')->where('id', $user->id)->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Profile and your managed teams have been successfully removed.');
    }
}
