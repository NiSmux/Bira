<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class ProfilisController extends Controller
{
    /**
     * Rodyti prisijungusio vartotojo profilio puslapį
     */
    public function show()
    {
        $user = Auth::user();

        // Rolės pavadinimas
        $role = DB::table('roles')->where('id', $user->role_id)->first();

        // Komandos, kuriose dalyvauja vartotojas
        $teams = DB::table('team_members')
            ->join('teams', 'team_members.team_id', '=', 'teams.id')
            ->where('team_members.user_id', $user->id)
            ->select('teams.id', 'teams.name', 'teams.description', 'team_members.role_in_team', 'team_members.joined_at')
            ->get();

        // Statistika: sukurtų užduočių skaičius
        $sukurtuUzduociu = DB::table('work_items')
            ->where('created_by', $user->id)
            ->where('is_deleted', 0)
            ->count();

        // Statistika: priskirtų užduočių skaičius
        $priskirtuUzduociu = DB::table('work_items')
            ->where('assignee_id', $user->id)
            ->where('is_deleted', 0)
            ->count();

        // Statistika: atliktų užduočių skaičius (statusas is_done = 1)
        // Skaičiuojame užduotis, kurias vartotojas sukūrė ARBA jam priskirtas
        $atliktaUzduociu = DB::table('work_items')
            ->join('workflow_statuses', 'work_items.status_id', '=', 'workflow_statuses.id')
            ->where(function ($q) use ($user) {
                $q->where('work_items.created_by', $user->id)
                  ->orWhere('work_items.assignee_id', $user->id);
            })
            ->where('work_items.is_deleted', 0)
            ->where('workflow_statuses.is_done', 1)
            ->count();

        // Paskutinės 5 sukurtos / priskirtos užduotys
        $paskutinesUzduotys = DB::table('work_items')
            ->join('workflow_statuses', 'work_items.status_id', '=', 'workflow_statuses.id')
            ->join('item_types', 'work_items.item_type_id', '=', 'item_types.id')
            ->leftJoin('priorities', 'work_items.priority_id', '=', 'priorities.id')
            ->where(function ($q) use ($user) {
                $q->where('work_items.created_by', $user->id)
                  ->orWhere('work_items.assignee_id', $user->id);
            })
            ->where('work_items.is_deleted', 0)
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
                'priorities.name as prioritetas'
            )
            ->get();

        return view('profilis.rodyti', compact(
            'user',
            'role',
            'teams',
            'sukurtuUzduociu',
            'priskirtuUzduociu',
            'atliktaUzduociu',
            'paskutinesUzduotys'
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

        return redirect()->route('profilis.rodyti')->with('success', 'Profilis sėkmingai atnaujintas!');
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
                'dabartinis_slaptazodis' => 'Dabartinis slaptažodis neteisingas.',
            ]);
        }

        // 2. Drausti keisti į tą patį slaptažodį
        if (Hash::check($request->naujas_slaptazodis, $user->password_hash)) {
            return back()->withErrors([
                'naujas_slaptazodis' => 'Naujas slaptažodis negali būti toks pats kaip dabartinis.',
            ]);
        }

        DB::table('users')
            ->where('id', $user->id)
            ->update(['password_hash' => Hash::make($request->naujas_slaptazodis)]);

        return redirect()->route('profilis.rodyti')->with('success', 'Slaptažodis sėkmingai pakeistas!');
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

        return redirect()->route('login')->with('success', 'Profilis ir jūsų valdomos komandos sėkmingai pašalintos.');
    }
}
