<?php

namespace App\Models;

use App\Models\Items\Item;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CyclecountDetail extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function item()
{
    return $this->belongsTo(Item::class, 'item_id');
}

public function warehouse()
{
    return $this->belongsTo(Warehouse::class, 'warehouse_id');
}

}
