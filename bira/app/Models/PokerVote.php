<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PokerVote extends Model
{
    protected $table = 'poker_votes';
    public $timestamps = false;

    protected $fillable = [
        'poker_session_item_id',
        'user_id',
        'points',
    ];

    protected $casts = [
        'voted_at' => 'datetime',
    ];

    public function sessionItem()
    {
        return $this->belongsTo(PokerSessionItem::class, 'poker_session_item_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
