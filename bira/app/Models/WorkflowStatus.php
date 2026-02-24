<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkflowStatus extends Model
{
    protected $table = 'workflow_statuses'; 
    public $timestamps = false; // Kadangi SQL schemoje nėra created_at/updated_at laukų šioje lentelėje

    protected $fillable = ['workflow_group_id', 'name', 'order_index', 'is_done'];

    /**
     * Ryšys su grupėmis (kiekvienas statusas priklauso kažkokiai grupei)
     */
    public function group()
    {
        return $this->belongsTo(WorkflowGroup::class, 'workflow_group_id');
    }

    /**
     * Ryšys su užduotimis.
     * Tai leis tau pasiekti visas užduotis, kurios šiuo metu yra šiame stulpelyje.
     * Pvz.: $status->workItems
     */
    public function workItems()
    {
        return $this->hasMany(WorkItem::class, 'status_id');
    }
}