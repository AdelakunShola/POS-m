<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\View\View;
use App\Models\Location;
use App\Models\LocationLine;
use App\Models\Unit;
use App\Models\Warehouse;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LocationController extends Controller
{
    public function locationList(): View
    {
        $locations = Location::with(['warehouse', 'unit', 'locationLine'])->get();
        return view('location.list', compact('locations'));
    }

    public function locationCreate(): View
    {
        $warehouses = Warehouse::all();
        $locationLines = LocationLine::all();
        $units = Unit::all();

        return view('location.create', compact('warehouses', 'locationLines', 'units'));
    }



    public function locationStore(Request $request)
    {
       
            $validatedData = $request->validate([
                'name' => 'required|unique:locations,name',
                'secondary_unit_id' => 'nullable|exists:units,id',
                'location_line_id' => 'nullable|exists:location_lines,id',
                'storage_capacity' => 'required|integer|min:0',
                'warehouse_id' => 'nullable|exists:warehouses,id',
            ]);
    
            $validatedData['current_capacity'] = 0;
            $validatedData['created_by'] = Auth::id();
            $validatedData['updated_by'] = Auth::id();
    
            Location::create($validatedData);
    

            return redirect()->route('location.list')->with('success', __('app.record_saved_successfully'));
        
    }
    
    


    public function locationEdit($id): View
    {
        $location = Location::findOrFail($id);
        $warehouses = Warehouse::all();
        $locationLines = LocationLine::all();
        $units = Unit::all();

        return view('location.edit', compact('location', 'warehouses', 'locationLines', 'units'));
    }

    public function locationUpdate(Request $request, $id)
    {
        $validatedData = $request->validate([
            'name' => 'required|unique:locations,name,' . $id,
            'secondary_unit_id' => 'nullable|exists:units,id',
            'location_line_id' => 'nullable|exists:location_lines,id',
            'storage_capacity' => 'required|integer|min:0',
            'warehouse_id' => 'nullable|exists:warehouses,id',
        ]);

        $validatedData['updated_by'] = Auth::id();

        Location::where('id', $request->id)->update($validatedData);

        return redirect()->route('location.list')->with('success', __('app.record_updated_successfully'));
    }


    public function locationDelete($id)
    {
        try {
            $location = Location::findOrFail($id);
            $location->delete();

            return redirect()->route('location.list')->with('success', __('app.record_deleted_successfully'));
        } catch (QueryException $e) {
            return redirect()->route('location.list')->with('error', __('app.cannot_delete_records'));
        }
    }


}

