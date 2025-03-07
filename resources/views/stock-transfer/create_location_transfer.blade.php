@extends('layouts.app')
@section('title', __('warehouse.stock_transfer'))

        @section('content')
        <!--start page wrapper -->
        <div class="page-wrapper">
            <div class="page-content">
                <x-breadcrumb :langArray="[
                                            'warehouse.warehouse',
                                            'warehouse.stock_transfer_list',
                                            'warehouse.new_transfer',
                                        ]"/>
                <div class="row">
                <form class="g-3 needs-validation" id="stockTransferForm" action="{{ route('inventory.transfers.store') }}" method="POST">
    @csrf



                        <input type="hidden" name="row_count" value="0">
                        <input type="hidden" name="row_count_payments" value="0">
                        <input type="hidden" id="base_url" value="{{ url('/') }}">
                        <input type="hidden" id="operation" name="operation" value="save">
                        
                        <div class="row">
                            <div class="col-12 col-lg-12">
                                <div class="card">
                                    <div class="card-header px-4 py-3">
                                        <h5 class="mb-0">Location transfer</h5>
                                    </div>
                                    <div class="card-body p-4 row g-3">
                                            <div class="col-md-4">
                                                <x-label for="transfer_date" name="{{ __('app.date') }}" />
                                                <div class="input-group mb-3">
                                                    <x-input type="text" additionalClasses="datepicker" name="transfer_date" :required="true" value=""/>
                                                    <span class="input-group-text" id="input-near-focus" role="button"><i class="fadeIn animated bx bx-calendar-alt"></i></span>
                                                </div>
                                            </div>

                                        

                        

                                    </div>
                                    <div class="card-header px-4 py-3">
                                        <h5 class="mb-0">{{ __('item.items') }}</h5>
                                    </div>
                                    <div class="card-body p-4 row g-3">



                                            
                                    <div class="col-md-9">
                                            <x-label for="search_item" name="" />
                                            <div class="input-group">
                                                <span class="input-group-text" id="basic-addon1">
                                                    <i class="fadeIn animated bx bx-barcode-reader text-primary"></i>
                                                </span>
                                                <input type="text" id="search_item" class="form-control" required placeholder="Scan Barcode/Search Items" autofocus>
                                            </div>
                                        </div>





                                        <!-- Display Scanned Barcode Details -->
                                        <div id="barcodeDetails" class="mt-3"></div>

                                            <div class="col-md-12 table-responsive">
                                                <table class="table mb-0 table-striped table-bordered" id="stockTransferItemsTable">
                                                    <thead>
                                                        <tr class="text-uppercase">
                                                            
                                                          
                                                            <th scope="col" class="">Barcode</th>
                                                            <th scope="col" class="">Item ID</th>
                                                            <th scope="col">Quantity</th>
                                                            <th scope="col">Transfer From Line</th>
                                                            <th scope="col">Transfer From Location</th>
                                                            <th scope="col">Transfer To</th>


                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td colspan="8" class="text-center fw-light fst-italic default-row">
                                                                No items are added yet!!
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                  
                                                </table>
                                            </div>
                                            <div class="col-md-8">
                                                <x-label for="note" name="{{ __('app.note') }}" />
                                                <x-textarea name='note' value=''/>
                                            </div>
                                    </div>

                                    <div class="card-header px-4 py-3"></div>
                                    <div class="card-body p-4 row g-3">
                                            <div class="col-md-12">
                                                <div class="d-md-flex d-grid align-items-center gap-3">
                                                    <x-button type="button" class="primary px-4" buttonId="submit_form" text="{{ __('app.submit') }}" />
                                                    <x-anchor-tag href="{{ route('dashboard') }}" text="{{ __('app.close') }}" class="btn btn-light px-4" />
                                                </div>
                                            </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </form>
                </div>
                <!--end row-->
            </div>
        </div>
        <!-- Import Modals -->
     

        @endsection

@section('js')

<script>
$(document).ready(function () {
    let typingTimer;
    let doneTypingInterval = 200; // Delay to allow scanning completion

    $("#search_item").on("input", function () {
        clearTimeout(typingTimer);

        let barcode = $(this).val().trim();
        if (barcode.length > 0) {
            typingTimer = setTimeout(function () {
                validateBarcode(barcode);
            }, doneTypingInterval);
        }
    });

    function validateBarcode(barcode) {
        if (barcode === "") return;

        $.ajax({
            url: "/validate-barcode/" + encodeURIComponent(barcode),
            type: "GET",
            success: function (response) {
                if (response.success) {
                    let item = response.data;
                    addToTable(item);
                } else {
                    alert(response.message);
                }
            },
            error: function () {
                alert("Error fetching barcode details.");
            }
        });
        $("#search_item").val("");
    }

    function addToTable(item) {
        let tableBody = $("#stockTransferItemsTable tbody");
        let defaultRow = tableBody.find(".default-row");

        if (defaultRow.length) {
            defaultRow.remove();
        }

        let newRow = `
        <tr data-from-location-id="${item.location_id}" data-from-location-line-id="${item.location_line_id}">
            <td>${item.barcode}</td>
            <td>${item.item_id}</td>
            <td class="quantity">${item.quantity}</td>
            <td>${item.location_line_name ?? "N/A"}</td>
            <td>${item.location_name ?? "N/A"}</td>
            <td>
                <select name="transfer_to_line[]" class="form-control transfer-to-line-dropdown">
                    <option value="">Select Line</option>
                </select>
            </td>
            <td>
                <select name="transfer_to_location[]" class="form-control transfer-to-location-dropdown">
                    <option value="">Select Location</option>
                </select>
            </td>
        </tr>`;

        tableBody.append(newRow);
        updateTransferToDropdowns(item.locations_dropdown);
        updateTotalQuantity();
    }

    function updateTransferToDropdowns(locations) {
        let lastRow = $("#stockTransferItemsTable tbody tr").last();
        let lineDropdown = lastRow.find(".transfer-to-line-dropdown");
        let locationDropdown = lastRow.find(".transfer-to-location-dropdown");

        lineDropdown.empty().append('<option value="">Select Line</option>');
        locationDropdown.empty().append('<option value="">Select Location</option>');

        let lines = {};
        locations.forEach(location => {
            if (!lines[location.location_line_id]) {
                lines[location.location_line_id] = location.location_line_name;
                lineDropdown.append(`<option value="${location.location_line_id}">${location.location_line_name}</option>`);
            }
        });

        lineDropdown.change(function () {
            let selectedLineId = $(this).val();
            locationDropdown.empty().append('<option value="">Select Location</option>');
            locations.forEach(location => {
                if (location.location_line_id == selectedLineId) {
                    locationDropdown.append(`<option value="${location.id}">${location.name}</option>`);
                }
            });
        });
    }

    function updateTotalQuantity() {
        let total = 0;
        $("#stockTransferItemsTable tbody .quantity").each(function () {
            total += parseInt($(this).text()) || 0;
        });
        $(".sum_of_quantity").text(total);
    }

    $("#submit_form").click(function (e) {
        e.preventDefault();

        let transferData = [];
        $("#stockTransferItemsTable tbody tr").each(function () {
    let barcode = $(this).find("td:eq(0)").text().trim();
    let item_id = $(this).find("td:eq(1)").text().trim();
    let quantity = $(this).find("td:eq(2)").text().trim();
    let from_location_id = $(this).data("from-location-id");
    let from_location_line_id = $(this).data("from-location-line-id");
    let to_location_id = $(this).find(".transfer-to-location-dropdown").val();
    let to_location_line_id = $(this).find(".transfer-to-line-dropdown").val();

    if (barcode && item_id && quantity && to_location_id && to_location_line_id) {
        transferData.push({
    barcode,
    item_id: item_id ? parseInt(item_id) : null,  
    to_location_id: to_location_id ? parseInt(to_location_id) : null, 
    to_location_line_id: to_location_line_id ? parseInt(to_location_line_id) : null,
    from_location_id: from_location_id ? parseInt(from_location_id) : null, 
    from_location_line_id: from_location_line_id ? parseInt(from_location_line_id) : null,
    quantity: parseInt(quantity)
});
    }
});


        if (transferData.length === 0) {
            alert("No items to transfer.");
            return;
        }

        $.ajax({
            url: "{{ route('inventory.transfers.store') }}",
            type: "POST",
            data: JSON.stringify({
                _token: "{{ csrf_token() }}",
                transfer_date: $("input[name='transfer_date']").val(),
                transfers: transferData
            }),
            contentType: "application/json",
            success: function (response) {
                alert("Stock transfer saved successfully!");
                location.reload();
            },
            error: function (xhr) {
                alert("Error saving stock transfer.");
                console.log(xhr.responseText);
            }
        });
    });
});

</script>

@endsection
