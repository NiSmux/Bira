<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CalendarNote extends Model
{
    protected $table = 'calendar_notes';

    protected $fillable = [
        'user_id',
        'note_date',
        'content',
    ];

    protected $casts = [
        'note_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
