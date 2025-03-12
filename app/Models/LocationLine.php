<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LocationLine extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function barcodes()
{
    return $this->hasMany(ProductBarcode::class);
}

public function location()
{
    return $this->belongsTo(location::class);
}


public function warehouse()
{
    return $this->belongsTo(Warehouse::class);
}

}
