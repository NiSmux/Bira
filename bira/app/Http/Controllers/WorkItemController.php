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
     * Rodyti konkrečią lentą ir jos užduotis
     */
    public function show($id)
    {
        // Užkrauname lentą su jos užduotimis (naudojant boards() ryšį modelyje)
        $board = Board::with('items.status', 'items.type')->findOrFail($id);
        
        return view('lenta.rodyti', compact('board'));
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

        return view('lenta.uzduotys.prideti_uzduoti', compact('board', 'itemTypes', 'priorities', 'statuses'));
    }

    /**
     * Išsaugoti naują užduotį duomenų bazėje
     */
    public function store(Request $request)
    {
        // 1. Validacija
        $validated = $request->validate([
            'title' => 'required|max:200',
            'description' => 'nullable|string',
            'board_id' => 'required|exists:boards,id',
            'status_id' => 'required|exists:workflow_statuses,id',
            'item_type_id' => 'required|exists:item_types,id',
            'priority_id' => 'nullable|exists:priorities,id',
        ]);

        // 2. Surandame lentą, kad gautume team_id
        $board = Board::findOrFail($request->board_id);

        // 3. Sukuriame užduotį
        $item = new WorkItem();
        $item->title = $request->title;
        $item->description = $request->description;
        $item->item_type_id = $request->item_type_id;
        $item->status_id = $request->status_id;
        $item->priority_id = $request->priority_id;
        $item->team_id = $board->team_id; 

        // --- SAUGIKLIS DĖL created_by KLAIDOS ---
        if (auth()->check()) {
            $user = auth()->user();
            
            // Jei ID yra skaičius, naudojame jį. 
            // Jei ID yra tekstas (email), surandame tikrą ID skaitmenį iš DB
            if (is_numeric($user->id)) {
                $item->created_by = $user->id;
            } else {
                // Priverstinai surandame ID pagal el. paštą
                $userId = DB::table('users')
                            ->where('email', $user->email)
                            ->value('id');
                $item->created_by = $userId;
            }
        } else {
            // Jei netyčia vartotojas nėra prisijungęs, bet pasiekė šį metodą
            $item->created_by = 1; 
        }
        // ----------------------------------------

        // Išsaugome užduotį (čia suveiks created_at, bet updated_at bus ignoruojamas, jei modelyje nustatei)
        $item->save();

        // 4. Svarbu: Pririšame užduotį prie lentos (įrašas board_items lentelėje)
        // Užtikrink, kad WorkItem modelyje yra boards() metodas su belongsToMany
        $item->boards()->attach($request->board_id);

        return redirect()->route('lenta.rodyti', $board->id)->with('success', 'Užduotis sėkmingai sukurta!');
    }
}