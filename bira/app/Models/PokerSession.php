<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PokerSession extends Model
{
    protected $table = 'poker_sessions';
    public $timestamps = false;

    protected $fillable = [
        'team_id',
        'title',
        'time_limit',
        'status',
        'created_by',
        'finished_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function team()
    {
        return $this->belongsTo(Team::class, 'team_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items()
    {
        return $this->hasMany(PokerSessionItem::class, 'session_id')->orderBy('order_index');
    }

    /**
     * Check if the session timer has expired
     */
    public function isExpired(): bool
    {
        if (!$this->created_at) return false;
        return now()->greaterThan($this->created_at->addSeconds($this->time_limit));
    }

    /**
     * Check if all team members have voted for a specific item
     */
    public function allVotedForItem(PokerSessionItem $item): bool
    {
        $memberCount = $this->team->members()->count();
        $voteCount = $item->votes()->count();
        return $voteCount >= $memberCount;
    }

    /**
     * Get remaining seconds on the timer using database clock to avoid timezone issues
     */
    public function remainingSeconds(): int
    {
        if (!$this->exists || !$this->created_at) return $this->time_limit;

        // Ask the database for the difference based on its own internal clock
        $elapsed = \Illuminate\Support\Facades\DB::selectOne(
            "SELECT TIMESTAMPDIFF(SECOND, created_at, NOW()) as elapsed FROM poker_sessions WHERE id = ?", 
            [$this->id]
        )->elapsed ?? 0;

        return max(0, $this->time_limit - (int)$elapsed);
    }

    /**
     * Find the nearest Fibonacci number to a given value
     */
    public static function nearestFibonacci(float $value): int
    {
        $fibonacci = [0, 1, 2, 3, 5, 8, 13, 21];
        $closest = 0;
        $minDiff = PHP_INT_MAX;

        foreach ($fibonacci as $fib) {
            $diff = abs($value - $fib);
            if ($diff < $minDiff) {
                $minDiff = $diff;
                $closest = $fib;
            }
        }

        return $closest;
    }
}
