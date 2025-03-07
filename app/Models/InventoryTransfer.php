<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryTransfer extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'user_id'); // Assuming `created_by` is the foreign key
    }

    public function fromLocation()
{
    return $this->belongsTo(Location::class, 'from_location_id');
}

public function fromLocationLine()
{
    return $this->belongsTo(LocationLine::class, 'from_location_line_id');
}

public function toLocation()
{
    return $this->belongsTo(Location::class, 'to_location_id');
}

public function toLocationLine()
{
    return $this->belongsTo(LocationLine::class, 'to_location_line_id');
}

}
