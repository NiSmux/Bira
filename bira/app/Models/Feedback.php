<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    protected $table = 'feedback';
    public $timestamps = false;

    protected $fillable = ['user_id', 'type', 'title', 'description'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
