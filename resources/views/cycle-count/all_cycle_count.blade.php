@extends('layouts.app')
@section('title', __('item.list'))

@section('css')
<link href="{{ asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet">
@endsection

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumb :langArray="['Transactions', 'Cyclecount List']" />

        <div class="card">
            <div class="card-header px-4 py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-uppercase">Cyclecount List</h5>
                <x-anchor-tag href="{{ route('cycle_count.create') }}" text="Create CycleCount" class="btn btn-primary px-5" />
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered border w-100" id="datatable">
                        <thead>
                            <tr>
                            <th>Warehouse</th>
                            <th>Product</th>
                            <th>Unit</th>
                            <th>Expected Stock</th>
                            <th>Location Expected Qty</th> <!-- NEW COLUMN -->
                            <th style="background-color: green-yellow;">Counted Qty</th>
                            <th>Value Diff</th>
                            <th>Recount</th>
                            <th>Approved</th>
                            <th>{{ __('app.action') }}</th>

                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($cyclecount as $count)
                            <tr>
                            <td>{{ $count->warehouse->name ?? 'N/A' }}</td>
                            <td>{{ $count->item->name ?? 'N/A' }}</td>
                            <td>{{ $count->item->base_unit_id ?? 'N/A' }}</td>
                            <td>{{ $count->item->current_stock ?? 'N/A' }}</td>
                            <td>
    @if ($count->item && $count->item->inventoryCheckins->count() > 0)
        <ul>
            @foreach ($count->item->inventoryCheckins as $checkin)
                <li>
                    {{ $checkin->location->locationLine->name ?? 'No Line Name' }} - 
                    {{ $checkin->location->name ?? 'No Location' }} 
                    (Qty: {{ $checkin->quantity }})
                </li>
            @endforeach
        </ul>
    @else
        N/A
    @endif
</td>



                            <td>{{ $count->counted_qty ?? '0' }}</td>
                            <td>{{ $count->value_diff ?? '0' }}</td>

                                <td>
                                    <input type="checkbox" class="recount-checkbox" data-id="{{ $count->id }}" {{ $count->recount ? 'checked' : '' }}>
                                </td>
                                <td>
                                    <input type="checkbox" class="approved-checkbox" data-id="{{ $count->id }}" {{ $count->approved ? 'checked' : '' }}>
                                </td>

                                <td>
                                    <button class="btn btn-warning btn-sm scan-btn" data-cycleid="{{ $count->id }}" data-itemid="{{ $count->item_id }}">Scan</button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scan Modal -->
<div class="modal fade" id="scanModal" tabindex="-1" aria-labelledby="scanModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="scanModalLabel">Select Location & Scan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="scanForm">
                    @csrf
                    <input type="hidden" name="cycle_count_id" id="cycle_count_id">
                    <input type="hidden" name="item_id" id="item_id">

                    <div class="mb-3">
                        <label for="location_id" class="form-label">Select Location</label>
                        <select class="form-select" id="location_id" name="location_id">
                            <option value="">Loading...</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="barcode_scan" class="form-label">Scan Barcode</label>
                        <input type="text" class="form-control" id="barcode_scan" name="barcode_scan" placeholder="Scan barcode here">
                    </div>
                    <button type="submit" class="btn btn-primary">Submit Scan</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@section('js')
<script>
$(document).ready(function() {
    let locationBarcodes = {}; // Store valid barcodes per location
    let scannedCounts = {}; // Store count of scans per location
    let scannedBarcodes = {}; // Track scanned barcodes per location

    $(".scan-btn").on("click", function() {
        let cycleCountId = $(this).data("cycleid");
        let itemId = $(this).data("itemid");

        $("#cycle_count_id").val(cycleCountId);
        $("#item_id").val(itemId);

        // Fetch locations and associated barcodes
        $.ajax({
            url: "{{ route('fetch.locations') }}",
            type: "GET",
            data: { item_id: itemId },
            success: function(response) {
                $("#location_id").empty().append('<option value="">Select Location</option>');
                locationBarcodes = {};
                scannedCounts = {};
                scannedBarcodes = {};

                response.forEach(location => {
                    let locationLineName = location.location_line ? location.location_line.name : "No Line Name";
                    let expectedQuantity = location.inventory_checkins.length > 0 ? location.inventory_checkins[0].quantity : 0;
                    let displayText = `${locationLineName} - ${location.name} (Qty: ${expectedQuantity})`;

                    $("#location_id").append(`<option value="${location.id}">${displayText}</option>`);

                    // Store valid barcodes for this location
                    locationBarcodes[location.id] = location.inventory_checkins.map(checkin => checkin.barcode);
                    
                    // Initialize count tracking
                    scannedCounts[location.id] = 0;
                    scannedBarcodes[location.id] = new Set();
                });

                console.log("Location Barcodes:", locationBarcodes); // Debugging
            },
            error: function(xhr, status, error) {
                console.error("Error fetching locations:", error);
            }
        });

        $("#scanModal").modal("show");
        updateScannedCountDisplay(); // Ensure count is updated when modal opens
    });

    $("#scanForm").on("submit", function(e) {
        e.preventDefault();
        
        let selectedLocation = $("#location_id").val();
        let scannedBarcode = $("#barcode_scan").val().trim();

        if (!selectedLocation || !scannedBarcode) {
            alert("Please select a location and scan a barcode.");
            return;
        }

        let validBarcodes = locationBarcodes[selectedLocation] || [];

        // Check if barcode belongs to this location
        if (!validBarcodes.includes(scannedBarcode)) {
            alert("Error: The scanned barcode belongs to a different location!");
            return;
        }

        // Check if barcode has already been scanned
        if (scannedBarcodes[selectedLocation].has(scannedBarcode)) {
            alert(`Error: Barcode ${scannedBarcode} has already been scanned at this location!`);
            return;
        }

        // Mark barcode as scanned
        scannedBarcodes[selectedLocation].add(scannedBarcode);

        // Increase the count for the selected location
        scannedCounts[selectedLocation] += 1;
        
        // Display updated count in the modal
        updateScannedCountDisplay();

        // Proceed with form submission if barcode is valid
        $.ajax({
            url: "{{ route('submit.scan') }}",
            type: "POST",
            data: $(this).serialize(),
            success: function(response) {
                alert(`Scan recorded successfully!\nScanned Barcode: ${scannedBarcode}`);
                $("#barcode_scan").val(""); // Clear input for next scan
            },
            error: function(xhr, status, error) {
                console.error("Error submitting scan:", error);
            }
        });
    });

    function updateScannedCountDisplay() {
        let selectedLocation = $("#location_id").val();
        if (!selectedLocation) return;

        let count = scannedCounts[selectedLocation] || 0;
        let locationName = $("#location_id option:selected").text(); // Get the selected location name
        let countDisplayId = "#scan-count-display";

        if ($(countDisplayId).length === 0) {
            // If the count display does not exist, create it
            $(".modal-body").append(`<p id="scan-count-display">Scanned Count: <strong>${count}</strong> at <strong>${locationName}</strong></p>`);
        } else {
            // Update the existing count
            $(countDisplayId).html(`Scanned Count: <strong>${count}</strong> at <strong>${locationName}</strong>`);
        }
    }
});

</script>
@endsection
