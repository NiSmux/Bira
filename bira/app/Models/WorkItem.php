<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkItem extends Model
{
    protected $table = 'work_items';

    // 1. SVARBU: Tavo SQL lentelėje nėra 'updated_at' stulpelio.
    // Pasakome Laravel, kad naudotų tik 'created_at'.
    const UPDATED_AT = null; 

    // 2. Pridėti visi pildomi laukai
    protected $fillable = [
        'title',
        'description',
        'item_type_id',
        'priority_id',
        'story_points',
        'status_id',
        'team_id',
        'assignee_id',
        'release_id',
        'parent_item_id',
        'created_by',
        'completed_at',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    /**
     * Ryšys su lentomis (per tarpinę lentelę board_items)
     */
    public function boards()
    {
        // 'item_id' yra užsienio raktas į šią lentelę, 'board_id' - į boards lentelę
        return $this->belongsToMany(Board::class, 'board_items', 'item_id', 'board_id');
    }

    /**
     * Ryšys su statusu (stulpeliu)
     */
    public function status()
    {
        return $this->belongsTo(WorkflowStatus::class, 'status_id');
    }

    /**
     * Ryšys su užduoties tipu
     */
    public function type()
    {
        return $this->belongsTo(ItemType::class, 'item_type_id');
    }

    /**
     * Ryšys su kūrėju (Vartotoju)
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    /**
     * Ryšys su prioritetu
     */
    public function priority()
    {
        return $this->belongsTo(Priority::class, 'priority_id');
    }

    public function sprint()
    {
        return $this->belongsTo(Sprint::class, 'release_id');
    }
}
