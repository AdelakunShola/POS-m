<?php

namespace App\Http\Controllers;

use App\Models\InventoryCheckin;
use App\Models\Items\Item;
use App\Models\Items\ItemTransaction;
use App\Models\Location;
use App\Models\ProductBarcode;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;


class BarcodeController extends Controller
{
    function generateUniqueBarcode() {
        do {
            $barcode = rand(100000000000, 999999999999); // 12-digit random number
        } while (ProductBarcode::where('barcode', $barcode)->exists());
    
        return $barcode;
    }
    
    public function generateBarcode(Request $request) {
        return response()->json(['barcode' => $this->generateUniqueBarcode()]);
    }



    public function store(Request $request) {
        // Validate input
        if (!$request->has('barcodes')) {
            return response()->json(['success' => false, 'message' => 'No barcode data received'], 400);
        }
    
        foreach ($request->barcodes as $barcodeData) {
            // Find the item in the items table
            $item = \App\Models\Items\Item::where('name', $barcodeData['itemName'])->first();
    
            if (!$item) {
                return response()->json([
                    'success' => false,
                    'message' => "Item not found: " . $barcodeData['itemName']
                ], 404);
            }
    
            // Save barcode with item_id and item_name
            \App\Models\ProductBarcode::create([
                'barcode' => $barcodeData['barcode'],
                'item_id' => $item->id,
                'item_name' => $barcodeData['itemName'],
            ]);
        }
    
        return response()->json(['success' => true, 'message' => 'Barcodes saved successfully']);
    }
    



   



    public function billList()
{
    $bills = ItemTransaction::with(['purchase.party', 'creator', 'item'])
        ->where('transaction_type', 'Purchase')
        ->latest()
        ->get();

    return view('transaction.checkin', compact('bills'));
}





    public function process(Request $request, $id)
{
    $request->validate([
        'location_id' => 'required|exists:locations,id',
    ]);

    $bill = ItemTransaction::findOrFail($id);

    // Update the transaction with the selected location
    $bill->update([
        'location_id' => $request->location_id,
        'status' => 'checked_in',
    ]);

    return redirect()->back()->with('success', 'Item checked in successfully to the selected location.');
}

    




public function getLocations($item_id)
{
    $item = Item::find($item_id);

    if (!$item) {
        return response()->json(['error' => 'Item not found'], 404);
    }

    // Fetch locations where secondary_unit_id matches the item's base_unit_id
    $locations = Location::where('secondary_unit_id', $item->base_unit_id)
        ->with('locationLine') // Include additional details
        ->get();

    return response()->json($locations);
}



public function validateBarcode(Request $request)
{
    try {
        $barcode = $request->barcode;
        $itemId = $request->item_id;

        // Fetch product name from the database
        $product = DB::table('items')->where('id', $itemId)->first();

        if (!$product) {
            return response()->json([
                'valid' => false,
                'message' => 'Invalid product!'
            ]);
        }

        Log::info("Validating barcode: $barcode for item: $itemId (Product: $product->name)");

        // Check if the barcode exists and belongs to the correct product
        $barcodeRecord = DB::table('product_barcodes')
            ->where('barcode', $barcode)
            ->where('item_id', $itemId)
            ->first();

        if (!$barcodeRecord) {
            return response()->json([
                'valid' => false,
                'message' => 'Invalid barcode or does not belong to this product!'
            ]);
        }

        return response()->json(['valid' => true]);

    } catch (\Exception $e) {
        Log::error("Barcode validation failed: " . $e->getMessage());
        return response()->json([
            'valid' => false,
            'message' => 'Server error: ' . $e->getMessage()
        ], 500);
    }
}




public function storeScannedBarcode(Request $request)
    {
        // Log Incoming Request Data
        Log::info('Incoming Request Data:', $request->all());

        try {
            // Validate input fields
            $validatedData = $request->validate([
                'barcode' => 'required|unique:inventory_checkins,barcode',
                'item_id' => 'required|exists:items,id',
                'location_id' => 'required|exists:locations,id',
                'location_name' => 'required|string',
                'location_line_id' => 'required|string',
                'storage_capacity' => 'required|integer',
                'current_capacity' => 'required|integer',
                'purchase_code' => 'required|string',
                'user_id' => 'required|exists:users,id'
            ]);

            // Save scanned barcode data
            $checkin = InventoryCheckin::create($validatedData);
            
            // Log success
            Log::info('Barcode successfully saved:', $checkin->toArray());

            return response()->json(['success' => true, 'message' => 'Barcode saved successfully!']);

        } catch (ValidationException $e) {
            // Log validation errors
            Log::error('Validation Error:', $e->errors());
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            // Log unexpected errors
            Log::error('Database Insert Error:', ['message' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Error saving barcode!', 'error' => $e->getMessage()], 500);
        }
    }
    
}
