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
                            <h5 class="mb-0 text-uppercase">Received Purchase Order List</h5>
                        </div>

                         @can('purchase.bill.create')
                        <!-- Button pushed to the right side -->
                       <!-- <x-anchor-tag href="{{ route('purchase.bill.create') }}" text="{{ __('purchase.create') }}" class="btn btn-primary px-5" />-->
                        @endcan
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <x-label for="party_id" name="{{ __('supplier.suppliers') }}" />

                                <a tabindex="0" class="text-primary" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-content="Search by name, mobile, phone, whatsApp, email"><i class="fadeIn animated bx bx-info-circle"></i></a>

                                <select class="party-ajax form-select" data-party-type='supplier' data-placeholder="Select Supplier" id="party_id" name="party_id"></select>
                            </div>
                            <div class="col-md-3">
                                <x-label for="user_id" name="{{ __('user.user') }}" />
                                <x-dropdown-user selected="" :showOnlyUsername='true' :canViewAllUsers="auth()->user()->can('purchase.bill.can.view.other.users.purchase.bills')" />
                            </div>
                            <div class="col-md-3">
                                <x-label for="from_date" name="{{ __('app.from_date') }}" />
                                <a tabindex="0" class="text-primary" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-content="Filter by Purchase Date"><i class="fadeIn animated bx bx-info-circle"></i></a>
                                <div class="input-group mb-3">
                                    <x-input type="text" additionalClasses="datepicker-edit" name="from_date" :required="true" value=""/>
                                    <span class="input-group-text" id="input-near-focus" role="button"><i class="fadeIn animated bx bx-calendar-alt"></i></span>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <x-label for="to_date" name="{{ __('app.to_date') }}" />
                                <a tabindex="0" class="text-primary" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-content="Filter by Purchase Date"><i class="fadeIn animated bx bx-info-circle"></i></a>
                                <div class="input-group mb-3">
                                    <x-input type="text" additionalClasses="datepicker-edit" name="to_date" :required="true" value=""/>
                                    <span class="input-group-text" id="input-near-focus" role="button"><i class="fadeIn animated bx bx-calendar-alt"></i></span>
                                </div>
                            </div>
                        </div>

                       
                        <div class="table-responsive">
    <table class="table table-striped table-bordered border w-100" id="datatable">
        <thead>
            <tr>
                <th class="d-none"><!-- Which Stores ID & it is used for sorting --></th>
                <th><input class="form-check-input row-select" type="checkbox"></th>
                <th>{{ __('purchase.code') }}</th>
                <th>{{ __('app.date') }}</th>
                <th>supplier</th>
                <th>{{ __('Product Name') }}</th> <!-- Added Product Name Column -->
                <th>{{ __('Quantity Purchased') }}</th>
                <th>Quantity Received</th>
                <th>{{ __('app.total') }}</th>
                <th>{{ __('payment.balance') }}</th>
                <th>{{ __('app.created_by') }}</th>
                <th>{{ __('app.created_at') }}</th>
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
                    <td>{{ $bill->purchases->party->first_name ?? 'N/A' }}</td> <!-- Adjust this to display supplier name if needed -->
                    <td>{{ $bill->product->name }}</td> <!-- Display Product Name -->
                    <td>{{ $bill->quantity }}</td> 
                    <td>
    {{ $purchases->where('purchase_code', $bill->transaction_id)->sum('total_quantity') }}
</td>

                    <td>{{ number_format($bill->total, 2) }}</td>
                    <td>{{ number_format($bill->balance, 2) }}</td>
                    <td>{{ $bill->creator->username }}</td>
                    <td>{{ $bill->created_at }}</td>
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

              
    <label for="barcodeScanner">Scan Barcode:</label>
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
<script src="{{ versionedAsset('custom/js/common/common.js') }}"></script>
<script src="{{ versionedAsset('custom/js/purchase/purchase-bill-list.js') }}"></script>
<script src="{{ versionedAsset('custom/js/modals/payment/invoice-payment.js') }}"></script>
<script src="{{ versionedAsset('custom/js/modals/email/send.js') }}"></script>
<script src="{{ versionedAsset('custom/js/sms/sms.js') }}"></script>

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

@endsection
