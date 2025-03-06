<?php

namespace App\Models;

use App\Enums\Item;
use App\Models\Purchase\PurchaseOrder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductBarcode extends Model
{
    use HasFactory;
    protected $guarded = [];

    // Relationship with Item (Each barcode belongs to an item)
    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    // Relationship with Location (Each barcode belongs to a location)
    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    // Relationship with Location Line (Each barcode may belong to a specific location line)
    public function locationLine()
    {
        return $this->belongsTo(LocationLine::class);
    }

    // Relationship with Purchase Order (Each barcode may be linked to a purchase order)
    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    // Relationship with Warehouse (Each barcode belongs to a warehouse)
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    // Relationship with User (Created by)
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Relationship with User (Updated by)
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
