<?php

// app/Models/WorkflowGroup.php
class WorkflowGroup extends Model
{
    protected $table = 'workflow_groups';
    public $timestamps = false;

    public function statuses()
    {
        return $this->hasMany(WorkflowStatus::class, 'workflow_group_id')->orderBy('order_index');
    }
}