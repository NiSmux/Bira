<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemType extends Model
{
    // Nurodome lentelės pavadinimą iš tavo SQL
    protected $table = 'item_types';

    // Kadangi tavo SQL schemoje ši lentelė neturi laiko žymių
    public $timestamps = false;

    protected $fillable = ['name', 'order_index'];

    /**
     * Ryšys su užduotimis
     */
    public function workItems()
    {
        return $this->hasMany(WorkItem::class, 'item_type_id');
    }
}
