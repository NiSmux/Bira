<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sprint extends Model
{
    protected $table = 'releases';

    const UPDATED_AT = null;

    protected $fillable = [
        'board_id', 'name', 'goal', 'start_date', 'end_date', 'status', 'created_by',
        'completed_points', 'total_points',
    ];

    protected $casts = [
        'start_date'       => 'date',
        'end_date'         => 'date',
        'completed_points' => 'integer',
        'total_points'     => 'integer',
    ];

    public function board()
    {
        return $this->belongsTo(Board::class);
    }

    public function items()
    {
        return $this->hasMany(WorkItem::class, 'release_id');
    }

    /**
     * Historical record of all items that were in this sprint.
     */
    public function historicalItems()
    {
        return $this->belongsToMany(WorkItem::class, 'sprint_work_items', 'sprint_id', 'work_item_id')
            ->withPivot('status_id')
            ->withTimestamps();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
