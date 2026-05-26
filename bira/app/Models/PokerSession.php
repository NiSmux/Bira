<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PokerSession extends Model
{
    protected $table = 'poker_sessions';
    public $timestamps = false;

    protected $fillable = [
        'team_id',
        'board_id',
        'title',
        'time_limit',
        'status',
        'created_by',
        'finished_at',
        'participants',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'finished_at' => 'datetime',
        'participants' => 'array',
    ];

    public function team()
    {
        return $this->belongsTo(Team::class, 'team_id');
    }

    public function board()
    {
        return $this->belongsTo(Board::class, 'board_id');
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
     * Check if this is a live session (no time limit, synchronous)
     */
    public function isLive(): bool
    {
        return $this->time_limit === 0;
    }

    /**
     * Check if the session timer has expired
     */
    public function isExpired(): bool
    {
        if ($this->isLive()) return false;
        if (!$this->created_at) return false;
        return now()->greaterThan($this->created_at->addSeconds($this->time_limit));
    }

    /**
     * Get the users who are participating in this session.
     * If no participants are explicitly set, defaults to all team members.
     */
    public function getActiveParticipants()
    {
        if (empty($this->participants)) {
            return $this->team->members;
        }

        // Return members filtered by the stored participants array
        return $this->team->members()->whereIn('users.id', $this->participants)->get();
    }

    /**
     * Check if all participants have voted for a specific item
     */
    public function allVotedForItem(PokerSessionItem $item): bool
    {
        $participants = $this->getActiveParticipants();
        $memberCount = $participants->count();
        
        // Count votes only from active participants
        $voteCount = $item->votes()->whereIn('user_id', $participants->pluck('id'))->count();
        
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
