<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\View\View;
use App\Models\Location;
use App\Models\LocationLine;
use App\Models\Warehouse;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;

class LocationLineController extends Controller
{

    public function create(): View
    {
        $warehouses = Warehouse::all();
        return view('location_line.create', compact('warehouses'));
    }

    public function edit($id): View
    {
        $locationLine = LocationLine::findOrFail($id);
        $warehouses = Warehouse::all();
        return view('location_line.edit', compact('locationLine', 'warehouses'));
    }
    

    public function store(Request $request)
{
    $validatedData = $request->validate([
        'name' => 'required|unique:location_lines,name',
        'warehouse_id' => 'required|exists:warehouses,id',
    ]);

    $validatedData['created_by'] = Auth::id();
    $validatedData['updated_by'] = Auth::id();

    LocationLine::create($validatedData);

    return redirect()->route('location_line.list')->with('success', __('app.record_saved_successfully'));
}

    public function update(Request $request)
    {
        $validatedData = $request->validate([
            'id' => 'required|exists:location_lines,id',
            'name' => 'required|unique:location_lines,name,' . $request->id,
            'warehouse_id' => 'required|exists:warehouses,id',
        ]);
        

        LocationLine::where('id', $request->id)->update($validatedData);

        return redirect()->route('location_line.list')->with('success', __('app.record_saved_successfully'));
    }

    public function list(): View
    {
        $locationLines = LocationLine::all(); // Fetch all location lines
    return view('location_line.list', compact('locationLines'));
    }
    

    public function datatableList(Request $request)
    {
        $data = LocationLine::query();

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('created_at', fn($row) => $row->created_at->format('Y-m-d'))
            ->addColumn('action', fn($row) => '<a href="'.route('location_line.edit', $row->id).'">Edit</a>')
            ->rawColumns(['action'])
            ->make(true);
    }

    public function delete($id)
    {
        try {
            $locationLine = LocationLine::findOrFail($id);
            $locationLine->delete();
    
            return redirect()->route('location_line.list')->with('success', __('app.record_deleted_successfully'));
        } catch (QueryException $e) {
            return redirect()->route('location_line.list')->with('error', __('app.cannot_delete_records'));
        }
    }
    
    
}










