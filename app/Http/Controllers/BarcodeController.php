<?php

namespace App\Http\Controllers;

use App\Models\InventoryCheckin;
use App\Models\InventoryTransfer;
use App\Models\Items\Item;
use App\Models\Items\ItemTransaction;
use App\Models\Location;
use App\Models\LocationLine;
use App\Models\ProductBarcode;
use App\Models\Sale\Sale;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
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
    



   



    public function billList(Request $request)
    {
        $query = ItemTransaction::with(['purchase.party', 'creator', 'item'])
            ->where('transaction_type', 'Purchase');
    
        // Filter by transaction date if selected
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('transaction_date', [$request->start_date, $request->end_date]);
        }
    
        $bills = $query->latest()->get();
    
        // Fetch purchases with username
        $purchases = DB::table('inventory_checkins')
            ->join('users', 'inventory_checkins.user_id', '=', 'users.id')
            ->select(
                'inventory_checkins.purchase_code',
                DB::raw('SUM(quantity) as total_quantity'),
                'users.username as user_name'
            )
            ->groupBy('inventory_checkins.purchase_code', 'users.username')
            ->get();
    
        return view('transaction.checkin', compact('bills', 'purchases'));
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

            // Fetch product name
            $product = DB::table('items')->where('id', $itemId)->first();
            if (!$product) {
                return response()->json([
                    'valid' => false,
                    'message' => 'Invalid product!'
                ]);
            }

            Log::info("Validating barcode: $barcode for item: $itemId (Product: $product->name)");

            // Check if barcode exists for the correct product
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

            // Check if barcode was already checked in
            $existingCheckin = DB::table('inventory_checkins')
                ->where('barcode', $barcode)
                ->first();

            if ($existingCheckin) {
                $location = DB::table('locations')->where('id', $existingCheckin->location_id)->first();

                return response()->json([
                    'valid' => false,
                    'message' => "Barcode already checked in! Location: {$location->name}, Line: {$location->location_line_id}"
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
    // Log incoming request data
    Log::info('Incoming Request Data:', $request->all());

    try {
        // Validate input fields
        $validatedData = $request->validate([
            'barcode' => 'required|string|unique:inventory_checkins,barcode',
            'item_id' => 'required|exists:items,id',
            'location_id' => 'required|exists:locations,id',
            'location_name' => 'required|string',
            'location_line_id' => 'required|string',
            'storage_capacity' => 'required|integer',
            'purchase_code' => 'required|string',
            'user_id' => 'required|exists:users,id',
        ]);

        // Check if barcode was already checked in
        $existingCheckin = InventoryCheckin::where('barcode', $validatedData['barcode'])->first();
        if ($existingCheckin) {
            $existingLocation = Location::find($existingCheckin->location_id);
            return response()->json([
                'success' => false,
                'message' => "Barcode already checked in! Location: {$existingLocation->name}, Line: {$existingLocation->location_line_id}"
            ], 400);
        }

        // Begin transaction
        DB::beginTransaction();

        // Save scanned barcode data
        $checkin = InventoryCheckin::create([
            'barcode' => $validatedData['barcode'],
            'item_id' => $validatedData['item_id'],
            'location_id' => $validatedData['location_id'],
            'location_name' => $validatedData['location_name'],
            'location_line_id' => $validatedData['location_line_id'],
            'storage_capacity' => $validatedData['storage_capacity'],
            'current_capacity' => 0, // Temporarily set to 0, will update below
            'quantity' => 1, // Assuming each barcode represents a single item
            'purchase_code' => $validatedData['purchase_code'],
            'user_id' => $validatedData['user_id']
        ]);

        // 🔄 Step 1: Recalculate total quantity in this location
        $totalQuantity = InventoryCheckin::where('location_id', $validatedData['location_id'])->sum('quantity');

        // 🔄 Step 2: Update `current_capacity` in `locations` table
        $updateStatus = Location::where('id', $validatedData['location_id'])
            ->update(['current_capacity' => $totalQuantity]);

        // Debug log to check if update was successful
        if ($updateStatus) {
            Log::info("Updated current_capacity successfully for Location ID: " . $validatedData['location_id']);
        } else {
            Log::error("Failed to update current_capacity for Location ID: " . $validatedData['location_id']);
        }

        // 🔄 Step 3: Update `current_capacity` for the newly inserted check-in record
        $checkin->update(['current_capacity' => $totalQuantity]);

        // Commit transaction
        DB::commit();

        // Log success
        Log::info('Barcode successfully saved and location updated:', [
            'barcode' => $validatedData['barcode'],
            'new_current_capacity' => $totalQuantity
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Barcode saved successfully!',
            'new_capacity' => $totalQuantity
        ]);

    } catch (ValidationException $e) {
        // Log validation errors
        Log::error('Validation Error:', $e->errors());
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $e->errors()
        ], 422);
    } catch (\Exception $e) {
        // Rollback transaction on failure
        DB::rollBack();
        Log::error('Database Insert Error:', ['message' => $e->getMessage()]);
        return response()->json([
            'success' => false,
            'message' => 'Error saving barcode!',
            'error' => $e->getMessage()
        ], 500);
    }
}

    
    

    




















///////STOCK TRANSFER////////////

public function transferLocation()
{
    $transfers = InventoryTransfer::with(['createdBy', 'item'])->orderBy('created_at', 'desc')->get();
    return view('stock-transfer.list_location_transfer', compact('transfers'));
}



public function createTransfer()
{
    // Fetch all locations with their associated location lines
    $locations = Location::with('locationLines')->get(); 

    return view('stock-transfer.create_location_transfer', compact('locations'));
}





public function validateBarcodeTransfer($barcode)
{
    try {
        // Fetch barcode and inventory details
        $barcodeRecord = ProductBarcode::where('barcode', $barcode)->first();

        if (!$barcodeRecord) {
            return response()->json([
                'success' => false,
                'message' => 'Barcode not found!'
            ]);
        }

        $inventory = InventoryCheckin::with(['locationLine', 'location', 'item'])
            ->where('barcode', $barcode)
            ->first();

        if (!$inventory || !$inventory->item) {
            return response()->json([
                'success' => false,
                'message' => 'Barcode found but no inventory data available.'
            ]);
        }

        $item = $inventory->item;
        $secondaryUnitId = $item->secondary_unit_id; // Get item's secondary_unit_id

        // Find locations that match the item's secondary_unit_id
        $matchingLocations = Location::where('secondary_unit_id', $secondaryUnitId)
            ->with('locationLine')
            ->get();

        // Prepare dropdown data for transfer locations
        $locationsDropdown = $matchingLocations->map(function ($location) {
            return [
                'id' => $location->id,
                'name' => $location->name,
                'location_line_id' => $location->locationLine ? $location->locationLine->id : null,
                'location_line_name' => $location->locationLine ? $location->locationLine->name : null,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'item_id' => $item->name,
                'item_id' => $inventory->item ? $inventory->item->id : null,
                'barcode' => $inventory->barcode,
                'location_name' => $inventory->location ? $inventory->location->name : 'N/A',
                'location_id' => $inventory->location ? $inventory->location->id : null,
                'location_line_name' => $inventory->locationLine ? $inventory->locationLine->name : 'N/A', 
                'location_line_id' => $inventory->locationLine ? $inventory->locationLine->id : null,
                'quantity' => $inventory->quantity,
                'locations_dropdown' => $locationsDropdown, // Send dropdown data
            ]
        ]);
    } catch (\Exception $e) {
        Log::error('Barcode validation error: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Error fetching barcode details: ' . $e->getMessage()
        ]);
    }
}





public function storeTransfer(Request $request)
{
    Log::info('Incoming Transfer Request', ['data' => $request->all()]);

    try {
        foreach ($request->input('transfers', []) as $transfer) {
            // Create Inventory Transfer Record
            InventoryTransfer::create([
                'barcode' => $transfer['barcode'] ?? null,
                'item_id' => $transfer['item_id'] ?? null,
                'to_location_id' => $transfer['to_location_id'] ?? null,
                'to_location_line_id' => $transfer['to_location_line_id'] ?? null,
                'from_location_id' => $transfer['from_location_id'] ?? null,
                'from_location_line_id' => $transfer['from_location_line_id'] ?? null,
                'quantity' => $transfer['quantity'] ?? 0,
                'transfer_date' => $request->input('transfer_date') ?? now(),
                'user_id' => auth()->id(),
                'status' => 'pending'
            ]);

            // ✅ Update inventory_checkins table
            $inventoryCheckin = InventoryCheckin::where('barcode', $transfer['barcode'])->first();
            if ($inventoryCheckin) {
                $inventoryCheckin->update([
                    'location_id' => $transfer['to_location_id'],
                    'location_name' => Location::find($transfer['to_location_id'])->name ?? null,
                    'location_line_id' => $transfer['to_location_line_id']
                ]);
            }

            // ✅ Update locations table
            // Reduce capacity of old location
            if (!empty($transfer['from_location_id'])) {
                Location::where('id', $transfer['from_location_id'])->decrement('current_capacity', 1);
            }

            // Increase capacity of new location
            if (!empty($transfer['to_location_id'])) {
                Location::where('id', $transfer['to_location_id'])->increment('current_capacity', 1);
            }
        }

        return response()->json(['success' => true, 'message' => 'Stock transfer saved successfully!']);
    } catch (\Exception $e) {
        Log::error('Error Saving Transfer: ' . $e->getMessage());
        return response()->json(['success' => false, 'message' => 'Error saving stock transfer.', 'error' => $e->getMessage()], 500);
    }
}


























///////////////PICKUP////////////

public function saleList(Request $request) 
{
    $query = ItemTransaction::where('transaction_type', 'sale')
        ->with(['inventoryPickups.scanner']); // Ensure pickups are loaded

    // Apply date filtering if values are provided
    if ($request->filled('from_date') && $request->filled('to_date')) {
        $fromDate = Carbon::parse($request->from_date)->startOfDay();
        $toDate = Carbon::parse($request->to_date)->endOfDay();
        $query->whereBetween('transaction_date', [$fromDate, $toDate]);
    }

    $pickup = $query->latest()->get();

    Log::info('Sale Transactions with Pickups:', ['pickup' => $pickup->toArray()]);

    return view('transaction.pickup', compact('pickup'));
}





public function getInventoryDetails($itemId)
{
    $inventory = DB::table('inventory_checkins')
        ->join('locations', 'inventory_checkins.location_id', '=', 'locations.id')
        ->join('location_lines', 'inventory_checkins.location_line_id', '=', 'location_lines.id')
        ->where('inventory_checkins.item_id', $itemId)
        ->select(
            'locations.name as location_name',
            'location_lines.name as location_line_name',
            DB::raw('SUM(inventory_checkins.quantity) as total_quantity')
        )
        ->groupBy('inventory_checkins.location_line_id', 'inventory_checkins.location_id', 'locations.name', 'location_lines.name')
        ->get();

    return response()->json($inventory);
}



public function scanOut(Request $request)
{
    $barcode = $request->barcode;
    $location = $request->location;
    $transactionId = $request->transaction_id; // Get transaction ID from request
    $userId = auth()->id(); // Get the logged-in user ID

    if (!$barcode || !$location) {
        return response()->json(['message' => 'Barcode and location are required.'], 400);
    }

    // Extract location details
    [$locationLineName, $locationName] = explode('-', $location);

    // Check if barcode already exists in inventory_pickups for this user
    $existingPickup = DB::table('inventory_pickups')
        ->where('barcode', $barcode)
        ->where('scanned_by', $userId)
        ->first();

    if ($existingPickup) {
        return response()->json([
            'message' => 'Barcode already scanned out already.',
            'scanned_by' => $existingPickup->scanned_by,
            'scanned_at' => $existingPickup->created_at
        ], 400);
    }

    // Verify if the barcode exists in the selected location and location line
    $inventory = DB::table('inventory_checkins')
        ->join('locations', 'inventory_checkins.location_id', '=', 'locations.id')
        ->join('location_lines', 'inventory_checkins.location_line_id', '=', 'location_lines.id')
        ->where('inventory_checkins.barcode', $barcode)
        ->where('location_lines.name', $locationLineName)
        ->where('locations.name', $locationName)
        ->select(
            'inventory_checkins.id',
            'inventory_checkins.item_id',
            'inventory_checkins.quantity',
            'inventory_checkins.location_id',
            'inventory_checkins.location_line_id',
            'locations.id as location_id',
            'locations.current_capacity'
        )
        ->first();

    if (!$inventory) {
        return response()->json(['message' => 'Barcode not found in the selected location and location line.'], 404);
    }

    // Ensure the quantity is greater than zero before decrementing
    if ($inventory->quantity <= 0) {
        return response()->json(['message' => 'No available quantity for this item in the selected location.'], 400);
    }

    DB::transaction(function () use ($inventory, $barcode, $transactionId, $userId, $locationName, $locationLineName) {
        // Update inventory (reduce quantity by 1)
        DB::table('inventory_checkins')
            ->where('id', $inventory->id)
            ->update([
                'quantity' => DB::raw('quantity - 1'),
                'current_capacity' => DB::raw('current_capacity - 1')
    ]);

        // Reduce current capacity in the locations table
        DB::table('locations')
            ->where('id', $inventory->location_id)
            ->decrement('current_capacity', 1);

        // Insert scanned item into inventory_pickups
        DB::table('inventory_pickups')->insert([
            'transaction_id' => $transactionId, // Save transaction ID
            'item_id' => $inventory->item_id,
            'barcode' => $barcode,
            'location_id' => $inventory->location_id,
            'location_name' => $locationName,
            'location_line_id' => $inventory->location_line_id,
            'scanned_by' => $userId,
            'quantity' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    });

    return response()->json(['message' => 'Item scanned out successfully!']);
}





}