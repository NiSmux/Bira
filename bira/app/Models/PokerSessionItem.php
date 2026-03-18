<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PokerSessionItem extends Model
{
    protected $table = 'poker_session_items';
    public $timestamps = false;

    protected $fillable = [
        'session_id',
        'work_item_id',
        'order_index',
        'final_points',
    ];

    public function session()
    {
        return $this->belongsTo(PokerSession::class, 'session_id');
    }

    public function workItem()
    {
        return $this->belongsTo(WorkItem::class, 'work_item_id');
    }

    public function votes()
    {
        return $this->hasMany(PokerVote::class, 'poker_session_item_id');
    }

    /**
     * Calculate the average of all votes (excluding "?" which are null)
     */
    public function averagePoints(): ?float
    {
        $votes = $this->votes()->whereNotNull('points')->pluck('points');
        if ($votes->isEmpty()) return null;
        return $votes->avg();
    }

    /**
     * Get the nearest Fibonacci number based on the average vote
     */
    public function consensusPoints(): ?int
    {
        $avg = $this->averagePoints();
        if ($avg === null) return null;
        return PokerSession::nearestFibonacci($avg);
    }

    /**
     * Check if a specific user has voted on this item
     */
    public function hasUserVoted(int $userId): bool
    {
        return $this->votes()->where('user_id', $userId)->exists();
    }
}
