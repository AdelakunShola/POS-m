@extends('layouts.app')
@section('title', __('purchase.bills'))

@section('css')
<link href="{{ asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet">
@endsection
        @section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

        <!--start page wrapper -->
        <div class="page-wrapper">
            <div class="page-content">
                    <x-breadcrumb :langArray="[
                                            'Transaction',
                                            'Pickup',
                                        ]"/>

                    <div class="card">

                    <div class="card-header px-4 py-3 d-flex justify-content-between">
                        <!-- Other content on the left side -->
                        <div>
                            <h5 class="mb-0 text-uppercase">Sale Order Ready For Pickup</h5>
                        </div>

                         @can('purchase.bill.create')
                        <!-- Button pushed to the right side -->
                       <!-- <x-anchor-tag href="{{ route('purchase.bill.create') }}" text="{{ __('purchase.create') }}" class="btn btn-primary px-5" />-->
                        @endcan
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                    
                            <form method="GET" action="{{ route('pickup.item') }}">
    <div class="row g-3">
        <div class="col-md-3">
            <x-label for="from_date" name="{{ __('app.from_date') }}" />
            <div class="input-group mb-3">
                <x-input type="date" name="from_date" value="{{ request('from_date') }}" />
                <span class="input-group-text"><i class="fadeIn animated bx bx-calendar-alt"></i></span>
            </div>
        </div>
        <div class="col-md-3">
            <x-label for="to_date" name="{{ __('app.to_date') }}" />
            <div class="input-group mb-3">
                <x-input type="date" name="to_date" value="{{ request('to_date') }}" />
                <span class="input-group-text"><i class="fadeIn animated bx bx-calendar-alt"></i></span>
            </div>
        </div>
        <div class="col-md-3">
            <label>&nbsp;</label> <!-- To align the button -->
            <button type="submit" class="btn btn-primary d-block">Filter</button>
        </div>
    </div>
</form>

                        </div>

                        <div class="mb-3">
    <label for="statusFilter" class="form-label">Filter by Status:</label>
    <select id="statusFilter" class="form-select">
        <option value="all">All</option>
        <option value="pending">Pending</option>
        <option value="partial">Partially Picked</option>
        <option value="completed">Completed</option>
    </select>
</div>


                       
                        <div class="table-responsive">
    <table class="table table-striped table-bordered border w-100" id="datatable">
    <thead>
    <tr>
        <th class="d-none"><!-- Stores ID for sorting --></th>
        <th><input class="form-check-input row-select" type="checkbox"></th>
        <th>{{ __('sale.code') }}</th>
        <th>{{ __('app.date') }}</th>
        <th>{{ __('Product Name') }}</th>
        <th>{{ __('Quantity Sold') }}</th>
        <th>Quantity Picked</th>
        <th>Scanned By</th> <!-- Add this column -->
        <th>{{ __('app.action') }}</th>
    </tr>
</thead>
<tbody>
@foreach($pickup as $sale)
    @php
        $pickedQty = $sale->totalQuantityPicked();
        $isCompleted = $sale->quantity == $pickedQty;
        $isPending = $pickedQty == 0;
        $statusClass = $isCompleted ? 'table-success' : ($isPending ? 'table-danger' : 'table-warning');
        $statusText = $isCompleted ? 'completed' : ($isPending ? 'pending' : 'partial');

     
    @endphp
    <tr class="{{ $statusClass }}" data-status="{{ $statusText }}">
        <td><input class="form-check-input row-select" type="checkbox"></td>
        <td>{{ $sale->transaction_id }}</td>
        <td>{{ $sale->transaction_date }}</td>
        <td>{{ $sale->product->name }}</td>
        <td>{{ $sale->quantity }}</td>
        <td>{{ $pickedQty }}</td>
        <td>{{ optional(optional($sale->inventoryPickups->first())->scanner)->username ?? 'N/A' }}</td>


        <td>
            <button class="btn btn-primary pickup-btn {{ $isCompleted ? 'btn-success disabled' : '' }}" 
                data-item-id="{{ $sale->item_id }}" 
                data-sale-code="{{ $sale->sale_code }}" 
                data-bs-toggle="modal" 
                data-bs-target="#pickupModal"
                {{ $isCompleted ? 'disabled' : '' }}>
                Pick-up
            </button>
        </td>
    </tr>
@endforeach
</tbody>


    </table>
</div>


<!-- Pick-up Modal -->
<!-- Pick-up Modal -->
<div class="modal fade" id="pickupModal" tabindex="-1" aria-labelledby="pickupModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="pickupModalLabel">Item Pickup Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
            <input type="hidden" id="saleCodeInput">
            <div class="mb-3">
                    <label for="locationDropdown" class="form-label">Select Location:</label>
                    <select id="locationDropdown" class="form-select">
                        <option value="">Select a location...</option>
                    </select>
                </div>


                <div class="mb-3">
                    <label for="barcodeInput" class="form-label">Scan Barcode:</label>
                    <input type="text" id="barcodeInput" class="form-control" placeholder="Scan barcode here...">
                </div>

                <div class="mb-3">
    <label>Successful Scans: <span id="scanCount">0</span></label>
</div>


                

                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Location Line</th>
                            <th>Location Name</th>
                            <th>Quantity Available</th>
                        </tr>
                    </thead>
                    <tbody id="pickupDetails">
                        <!-- Data will be inserted here -->
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="scanOutBtn">Scan Out</button>
            </div>
        </div>
    </div>
</div>



                       
                    </div>
                </div>
                    </div>
                </div>
                <!--end row-->
            </div>
        </div>

        @include("modals.payment.invoice-payment", ['payment_for' => 'purchase-bill'])
        @include("modals.payment.invoice-payment-history")
        @include("modals.email.send")
        @include("modals.sms.send")

        @endsection
@section('js')
<script>
$(document).ready(function () {
    let scanCount = 0; // Counter for successful scans

    $(".pickup-btn").on("click", function () {
        let itemId = $(this).data("item-id");
        let saleCode = $(this).closest("tr").find("td:eq(1)").text().trim(); // Get sale code (transaction_id)

        $("#pickupDetails").html('<tr><td colspan="3">Loading...</td></tr>');
        $("#locationDropdown").html('<option value="">Select a location...</option>');
        $("#scanCount").text(scanCount); // Initialize scan count
        $("#saleCodeInput").val(saleCode); // Store sale code in hidden input

        $.ajax({
            url: "/get-inventory-details/" + itemId,
            type: "GET",
            success: function (response) {
                $("#pickupDetails").empty();
                $("#locationDropdown").empty().append('<option value="">Select a location...</option>');

                let hasValidLocations = false;

                response.forEach(function (item) {
                    if (item.total_quantity > 0) { // Only include locations with available stock
                        hasValidLocations = true;

                        $("#pickupDetails").append(`
                            <tr>
                                <td>${item.location_line_name}</td>  
                                <td>${item.location_name}</td>
                                <td>${item.total_quantity}</td>
                            </tr>
                        `);

                        $("#locationDropdown").append(`
                            <option value="${item.location_line_name}-${item.location_name}">
                                ${item.location_line_name} - ${item.location_name} (Qty: ${item.total_quantity})
                            </option>
                        `);
                    }
                });

                // If no valid locations, show message
                if (!hasValidLocations) {
                    $("#pickupDetails").html('<tr><td colspan="3">No available stock.</td></tr>');
                }
            },
            error: function () {
                $("#pickupDetails").html('<tr><td colspan="3">Error fetching data.</td></tr>');
            },
        });
    });

    // Auto-submit on barcode scan
    $("#barcodeInput").on("input", function () {
        let barcode = $(this).val().trim();
        let selectedLocation = $("#locationDropdown").val();
        let saleCode = $("#saleCodeInput").val(); // Get transaction_id

        if (barcode.length < 6) return; // Adjust based on barcode length

        if (!selectedLocation) {
            alert("Please select a location.");
            return;
        }

        $.ajax({
            url: "/scan-out",
            type: "POST",
            data: {
                barcode: barcode,
                location: selectedLocation,
                transaction_id: saleCode, // Include transaction ID
                _token: $('meta[name="csrf-token"]').attr("content"),
            },
            success: function () {
                scanCount++; // Increment scan count
                $("#scanCount").text(scanCount); // Update displayed count
                $("#barcodeInput").val(""); // Clear input for next scan
            },
            error: function (xhr) {
                if (xhr.status === 400 && xhr.responseJSON.message === "Barcode already scanned out by you.") {
                    alert(`Error: ${xhr.responseJSON.message}\nScanned At: ${xhr.responseJSON.scanned_at}`);
                } else {
                    alert("Error scanning out: " + xhr.responseJSON.message);
                }
                $("#barcodeInput").val(""); // Clear input on error
            },
        });
    });

    // Handle Scan Out button click
    $("#scanOutBtn").on("click", function () {
        let barcode = $("#barcodeInput").val().trim();
        let selectedLocation = $("#locationDropdown").val();
        let saleCode = $("#saleCodeInput").val(); // Get transaction_id

        if (!barcode || barcode.length < 6) {
            alert("Please scan a valid barcode.");
            return;
        }

        if (!selectedLocation) {
            alert("Please select a location.");
            return;
        }

        $.ajax({
            url: "/scan-out",
            type: "POST",
            data: {
                barcode: barcode,
                location: selectedLocation,
                transaction_id: saleCode, // Include transaction ID
                _token: $('meta[name="csrf-token"]').attr("content"),
            },
            success: function () {
                scanCount++; // Increment scan count
                $("#scanCount").text(scanCount); // Update displayed count
                $("#barcodeInput").val(""); // Clear input for next scan
                alert("Item scanned out successfully!");
            },
            error: function (xhr) {
                if (xhr.status === 400 && xhr.responseJSON.message === "Barcode already scanned out by you.") {
                    alert(`Error: ${xhr.responseJSON.message}\nScanned At: ${xhr.responseJSON.scanned_at}`);
                } else {
                    alert("Error scanning out: " + xhr.responseJSON.message);
                }
                $("#barcodeInput").val(""); // Clear input on error
            },
        });
    });

});

</script>
<script>
$(document).ready(function () {
    $("#statusFilter").on("change", function () {
        let selectedStatus = $(this).val();
        $("tbody tr").each(function () {
            let rowStatus = $(this).data("status");
            if (selectedStatus === "all" || rowStatus === selectedStatus) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
});
</script>

@endsection
