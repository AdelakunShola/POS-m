<?php

namespace App\Models;

use App\Models\Items\Item;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryCheckin extends Model
{
    use HasFactory;
    protected $guarded = [];


    public function locationLine()
{
    return $this->belongsTo(LocationLine::class, 'location_line_id');
}


public function item()
{
    return $this->belongsTo(Item::class, 'item_id');
}

public function location()
{
    return $this->belongsTo(Location::class, 'location_id');
}





}
