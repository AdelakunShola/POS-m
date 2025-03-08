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
                                            'CheckIn',
                                        ]"/>

                    <div class="card">

                    <div class="card-header px-4 py-3 d-flex justify-content-between">
                        <!-- Other content on the left side -->
                        <div>
                            <h5 class="mb-0 text-uppercase"> Purchase Order Ready For Check-In</h5>
                        </div>

                         @can('purchase.bill.create')
                        <!-- Button pushed to the right side -->
                       <!-- <x-anchor-tag href="{{ route('purchase.bill.create') }}" text="{{ __('purchase.create') }}" class="btn btn-primary px-5" />-->
                        @endcan
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                           
                            <div class="row g-3">
    <div class="col-md-3">
        <x-label for="start_date" name="Start Date" />
        <input type="date" id="start_date" name="start_date" class="form-control" value="{{ request('start_date') }}">
    </div>

    <div class="col-md-3">
        <x-label for="end_date" name="End Date" />
        <input type="date" id="end_date" name="end_date" class="form-control" value="{{ request('end_date') }}">
    </div>

  
    <div class="col-md-3 align-self-end">
        <button type="submit" class="btn btn-primary" onclick="filterTransactions()">Filter</button>
    </div>

    <form method="GET">
    <div class="row g-3 mt-3">
        <!-- User Filter (Independent) -->
        <div class="col-md-3">
            <x-label for="user_id" name="User" />
            <select id="user_id" name="user_id" class="form-control" onchange="this.form.submit()">
                <option value="">All Users</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                        {{ $user->username }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
</form>

</div>



                        </div>

                       
                        <div class="table-responsive">
    <table class="table table-striped table-bordered border w-100" id="datatable">
    <thead>
    <tr>
        <th class="d-none"><!-- Hidden for sorting --></th>
        <th><input class="form-check-input row-select" type="checkbox"></th>
        <th>{{ __('purchase.code') }}</th>
        <th>{{ __('app.date') }}</th>
        <th>Supplier</th>
        <th>{{ __('Product Name') }}</th> <!-- Product Name -->
        <th>{{ __('Quantity Purchased') }}</th>
        <th>Quantity Checked-In</th>
        <th>Check-In By</th>
        <th>Status</th> <!-- Status Column -->
        <th>{{ __('app.action') }}</th>
    </tr>
</thead>
<tbody>
    @foreach($bills as $bill)
        <tr>
            <td class="d-none">{{ $bill->id }}</td>
            <td><input class="form-check-input row-select" type="checkbox"></td>
            <td>{{ $bill->transaction_id }}</td>
            <td>{{ $bill->transaction_date }}</td>
            <td>{{ $bill->purchases->party->first_name ?? 'N/A' }}</td> <!-- Supplier -->
            <td>{{ $bill->product->name }}</td> <!-- Product Name -->
            <td>{{ $bill->quantity }}</td> 

            <!-- Quantity Checked-In -->
            <td>
                {{ $purchases->where('purchase_code', $bill->transaction_id)->sum('total_quantity') }}
            </td>

            <!-- Checked-In By -->
            <td>
                {{ $purchases->where('purchase_code', $bill->transaction_id)->first()->user_name ?? 'N/A' }}
            </td>

            <!-- Status Calculation -->
            <td>
                @php
                    $quantityPurchased = $bill->quantity;
                    $quantityCheckedIn = $purchases->where('purchase_code', $bill->transaction_id)->sum('total_quantity');

                    if ($quantityCheckedIn == 0) {
                        $status = 'Pending';
                        $color = 'badge bg-danger'; // Red
                    } elseif ($quantityCheckedIn == $quantityPurchased) {
                        $status = 'Completely Picked';
                        $color = 'badge bg-success'; // Green
                    } else {
                        $status = 'Partially Picked';
                        $color = 'badge bg-warning'; // Yellow
                    }
                @endphp
                <span class="{{ $color }}">{{ $status }}</span>
            </td>

            <!-- Check-in Button with Modal -->
            <td>
                <button class="btn btn-primary checkin-btn" data-item-id="{{ $bill->item_id }}">
                    Check-in
                </button>

                <div class="modal fade" id="checkinModal" tabindex="-1">
                    <input type="hidden" id="purchaseCode" value="{{ $bill->transaction_id }}">
                    <meta name="user-id" content="{{ auth()->id() }}">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Select Location</h5>
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                            </div>
                            <div class="modal-body">
                                <label for="locationSelect">Choose a Location:</label>
                                <select id="locationSelect" class="form-control"></select>

                                <label for="barcodeScanner">Scan Barcode In:</label>
                                <input type="text" id="barcodeScanner" class="form-control" autofocus>

                                <p>Scanned Count: <span id="barcodeCount">0</span></p>
                                <ul id="scannedBarcodes"></ul>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-success">Check-in</button>
                            </div>
                        </div>
                    </div>
                </div>
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
                <!--end row-->
            </div>
        </div>

        @include("modals.payment.invoice-payment", ['payment_for' => 'purchase-bill'])
        @include("modals.payment.invoice-payment-history")
        @include("modals.email.send")
        @include("modals.sms.send")

        @endsection
@section('js')
<script src="{{ versionedAsset('assets/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ versionedAsset('assets/plugins/datatable/js/dataTables.bootstrap5.min.js') }}"></script>

<script>
$(document).ready(function () {
    $(".checkin-btn").click(function () {
        var itemId = $(this).data("item-id");

        $.ajax({
            url: "/get-locations/" + itemId,
            type: "GET",
            success: function (data) {
                let options = "";
                data.forEach(location => {
                    options += `<option value="${location.id}" 
                                    data-current-capacity="${location.current_capacity}"
                                    data-storage-capacity="${location.storage_capacity}">
                                    ${location.name} - Line: ${location.location_line_id} 
                                    (Capacity: <span class="capacity-display">${location.current_capacity}</span>/${location.storage_capacity})
                                </option>`;
                });

                $("#locationSelect").html(options);
                $("#checkinModal").modal("show");
            },
            error: function () {
                alert("Failed to load locations.");
            }
        });
    });

    // Function to update the current capacity after scanning
    function updateCurrentCapacity(locationId) {
    fetch("/recalculate-location-capacity", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
        },
        body: JSON.stringify({ location_id: locationId }),
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Find the selected location in the dropdown and update capacity
            $("#locationSelect option:selected").attr("data-current-capacity", data.new_capacity);
            $("#locationSelect option:selected").html(
                `${data.location_name} - Line: ${data.location_line_id} 
                 (Capacity: <span class="capacity-display">${data.new_capacity}</span>/${data.storage_capacity})`
            );
        }
    })
    .catch(error => {
        console.error("Error updating capacity:", error);
    });
}

// Trigger update when barcode is scanned
$("#barcodeScanner").on("change", function () {
    var locationId = $("#locationSelect").val();
    if (locationId) {
        updateCurrentCapacity(locationId);
    }
});

  
});

</script>

<script>
$("#barcodeScanner").css("pointer-events", "auto").prop("disabled", false);


$(document).ready(function () {
    let scannedCount = 0;
    let scannedBarcodes = new Set();

    $(".checkin-btn").on("click", function () {
        let itemId = $(this).data("item-id");
        let purchaseCode = $(this).closest("tr").find("td:eq(2)").text().trim(); // Get correct purchase code

        // **Set the correct purchaseCode in the modal**
        $("#purchaseCode").val(purchaseCode); 

        // Store the itemId in modal for later use
        $("#checkinModal").data("item-id", itemId);

        // Reset scanned barcode list
        scannedCount = 0;
        scannedBarcodes.clear();
        $("#barcodeCount").text(scannedCount);
        $("#scannedBarcodes").empty();

        // Load available locations
        $.ajax({
            url: "/get-locations/" + itemId,
            type: "GET",
            success: function (data) {
                let options = "";
                data.forEach(location => {
                    options += `<option value="${location.id}" 
                                    data-capacity="${location.storage_capacity}" 
                                    data-current="${location.current_capacity}" 
                                    data-line="${location.location_line_id}" 
                                    data-name="${location.name}">
                                    ${location.name} - Line: ${location.location_line_id} 
                                    (Capacity: ${location.current_capacity}/${location.storage_capacity})
                                </option>`;
                });

                $("#locationSelect").html(options);
                $("#checkinModal").modal("show");
            },
            error: function () {
                alert("Failed to load locations.");
            }
        });
    });

    // **Handle barcode scanning input**
    $("#barcodeScanner").on("input", function () {
        let barcode = $(this).val().trim();
        if (barcode.length === 0) return;

        let itemId = $("#checkinModal").data("item-id");
        let purchaseCode = $("#purchaseCode").val(); // Ensure correct purchaseCode is used

        if (scannedBarcodes.has(barcode)) {
            showError("Barcode already scanned!");
            return;
        }

        // **Step 1: Validate Barcode**
        $.ajax({
            url: "/validate-barcode",
            method: "POST",
            data: {
                barcode: barcode,
                item_id: itemId,
                purchase_code: purchaseCode, // Pass the correct purchaseCode
                _token: $('meta[name="csrf-token"]').attr("content")
            },
            success: function (response) {
                if (response.valid) {
                    storeBarcode(barcode);
                } else {
                    showError(response.message);
                }
            },
            error: function () {
                showError("Error validating barcode!");
            }
        });
    });

    function storeBarcode(barcode) {
        let itemId = $("#checkinModal").data("item-id");
        let purchaseCode = $("#purchaseCode").val();
        let selectedLocation = $("#locationSelect option:selected");
        let locationId = selectedLocation.val();
        let locationName = selectedLocation.data("name");
        let locationLine = selectedLocation.data("line");
        let storageCapacity = parseInt(selectedLocation.data("capacity"));
        let currentCapacity = parseInt(selectedLocation.data("current"));
        let userId = $("meta[name='user-id']").attr("content");

        if (currentCapacity + 1 > storageCapacity) {
            showError("Location capacity reached!");
            return;
        }

        // **Step 2: Store Barcode**
        $.ajax({
            url: "/store-scanned-barcode",
            method: "POST",
            data: {
                barcode: barcode,
                item_id: itemId,
                location_id: locationId,
                location_name: locationName,
                location_line_id: locationLine,
                storage_capacity: storageCapacity,
                current_capacity: currentCapacity + 1,
                purchase_code: purchaseCode, // Ensure correct purchaseCode is stored
                user_id: userId,
                _token: $('meta[name="csrf-token"]').attr("content")
            },
            success: function (response) {
                if (response.success) {
                    scannedBarcodes.add(barcode);
                    scannedCount++;
                    $("#barcodeCount").text(scannedCount);
                    $("#scannedBarcodes").append(`<li>${barcode}</li>`);

                    // Update capacity display
                    selectedLocation.data("current", currentCapacity + 1);
                    selectedLocation.text(`${locationName} - Line: ${locationLine} (Capacity: ${currentCapacity + 1}/${storageCapacity})`);
                } else {
                    showError(response.message);
                }
                $("#barcodeScanner").val("").focus();
            },
            error: function () {
                showError("Error saving barcode!");
            }
        });
    }

    function showError(message) {
        $("#barcodeScanner").val("").focus();
        alert(message);
    }
});



</script>

<script>
function filterTransactions() {
    let startDate = document.getElementById("start_date").value;
    let endDate = document.getElementById("end_date").value;
    let url = `?start_date=${startDate}&end_date=${endDate}`;
    window.location.href = url;
}
</script>



@endsection
