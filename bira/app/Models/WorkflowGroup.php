<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// app/Models/WorkflowGroup.php
class WorkflowGroup extends Model
{
    protected $table = 'workflow_groups';
    public $timestamps = false;

    protected $fillable = [
        'name',
        'team_id',
    ];

    public function statuses()
    {
        return $this->hasMany(WorkflowStatus::class, 'workflow_group_id')->orderBy('order_index');
    }
}