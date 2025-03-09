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
    <tr class="" data-status="{{ $statusText }}">
        <td><input class="form-check-input row-select" type="checkbox"></td>
        <td class="{{ $statusClass }}" >{{ $sale->transaction_id }}</td>
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
    <input type="text" id="barcodeInput" class="form-control" placeholder="Scan barcode here..." disabled>
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


        <!-- Error Modal -->
<div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="errorModalLabel">Error</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="errorModalMessage">
                <!-- Error message will be inserted here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
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
    let scanCount = 0;

    $(".pickup-btn").on("click", function () {
        let itemId = $(this).data("item-id");
        let saleCode = $(this).closest("tr").find("td:eq(1)").text().trim();

        $("#pickupDetails").html('<tr><td colspan="3">Loading...</td></tr>');
        $("#locationDropdown").html('<option value="">Select a location...</option>');
        $("#barcodeInput").prop("disabled", true); // Disable initially
        $("#scanCount").text(scanCount);
        $("#saleCodeInput").val(saleCode);

        $.ajax({
            url: "/get-inventory-details/" + itemId,
            type: "GET",
            success: function (response) {
                $("#pickupDetails").empty();
                $("#locationDropdown").empty().append('<option value="">Select a location...</option>');

                let hasValidLocations = false;

                response.forEach(function (item) {
                    if (item.total_quantity > 0) {
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

                if (!hasValidLocations) {
                    $("#pickupDetails").html('<tr><td colspan="3">No available stock.</td></tr>');
                }
            },
            error: function () {
                $("#pickupDetails").html('<tr><td colspan="3">Error fetching data.</td></tr>');
            },
        });
    });

    // Enable or disable barcode input based on location selection
    $("#locationDropdown").on("change", function () {
        let selectedLocation = $(this).val();
        $("#barcodeInput").prop("disabled", selectedLocation === "");
    });

    // Prevent scanning if no location is selected
    $("#barcodeInput").on("input", function () {
        if ($("#barcodeInput").prop("disabled")) {
            alert("Please select a location first.");
            $(this).val(""); // Clear input
            return;
        }

        let barcode = $(this).val().trim();
        let selectedLocation = $("#locationDropdown").val();
        let saleCode = $("#saleCodeInput").val();

        if (barcode.length < 6) return;

        $.ajax({
            url: "/scan-out",
            type: "POST",
            data: {
                barcode: barcode,
                location: selectedLocation,
                transaction_id: saleCode,
                _token: $('meta[name="csrf-token"]').attr("content"),
            },
            success: function () {
                scanCount++;
                $("#scanCount").text(scanCount);
                $("#barcodeInput").val("");
            },
            error: function (xhr) {
                alert("Error scanning out: " + xhr.responseJSON.message);
                $("#barcodeInput").val("");
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
