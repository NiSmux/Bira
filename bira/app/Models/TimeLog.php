<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimeLog extends Model
{
    protected $table = 'time_logs';

    protected $fillable = [
        'user_id',
        'work_item_id',
        'logged_date',
        'minutes',
        'note',
    ];

    protected $casts = [
        'logged_date' => 'date',
        'minutes'     => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function workItem()
    {
        return $this->belongsTo(WorkItem::class, 'work_item_id');
    }

    /**
     * Return a human-readable duration string, e.g. "1h 30m".
     */
    public function getDurationAttribute(): string
    {
        $h = intdiv($this->minutes, 60);
        $m = $this->minutes % 60;
        if ($h > 0 && $m > 0) return "{$h}h {$m}m";
        if ($h > 0) return "{$h}h";
        return "{$m}m";
    }
}
