<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryPickup extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function scanner()
    {
        return $this->belongsTo(User::class, 'scanned_by');
    }
    

}
