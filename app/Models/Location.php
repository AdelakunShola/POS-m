<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function barcodes()
{
    return $this->hasMany(ProductBarcode::class);
}


public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }


    public function unit()
    {
        return $this->belongsTo(Unit::class, 'secondary_unit_id'); // Specify foreign key
    }
 
    public function locationLine()
    {
        return $this->belongsTo(LocationLine::class); // Adjust if needed
    }
}
