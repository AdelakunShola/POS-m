@extends('layouts.app')
@section('title', __('location.create'))

@section('content')



		<!--start page wrapper -->
		<div class="page-wrapper">
			<div class="page-content">
				<x-breadcrumb :langArray="[
											'item.location',
											'location.list',
											'location.create',
										]"/>
				<div class="row">
					<div class="col-12 col-lg-12">
                        <div class="card">
                            <div class="card-header px-4 py-3 d-flex justify-content-between align-items-center">
                              <h5 class="mb-0">Location Details</h5>
                             
                            </div>
                            <div class="card-body p-4">
                                <form class="row g-3 needs-validation" method="POST" id="itemForm yourFormId" action="{{ route('location.store') }}" enctype="multipart/form-data">
                                    {{-- CSRF Protection --}}
                                    @csrf
                                   

                                    {{-- Units Modal --}}
                                    @include("modals.unit.create")

                                    <input type="hidden" id="operation" name="operation" value="save">
                                    <input type="hidden" id="base_url" value="{{ url('/') }}">
                           

                                 


                                    <div class="col-md-4">
                                        <x-label for="hsn" name="Name" />
                                        <div class="input-group mb-3">
                                            <x-input type="text" name="name" id="generatedCode" required />
                                            <button class="btn btn-outline-secondary auto-generate-code" type="button" id="button-addon2">auto</button>
                                        </div>
                                    </div>


                                    

                              
                                    <div class="col-md-4">
                                        <x-label for="storage_capacity" name="Storage Capacity" />
                                        <x-input type="number" name="storage_capacity" :required="true" />
                                    </div>
                                   
                                  
                                    

                                    <div class="col-md-4">
                                        <x-label for="secondary_unit_id" name="Package Type" />
                                        <div class="input-group">
                                        <select name="secondary_unit_id">
                                    <option value="">Select Package Type</option>
                                    @foreach($units as $units)
                                        <option value="{{ $units->id }}">{{ $units->name }}</option>
                                    @endforeach
                                </select>
                                            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target=""><i class="bx me-0"></i>
                                        </div>
                                    </div>


                                    <div class="col-md-4">
                                        <x-label for="location_line_id" name="Location Line" />
                                        <div class="input-group">
                                        <select name="location_line_id">
                                    <option value="">Select Location Line</option>
                                    @foreach($locationLines as $line)
                                        <option value="{{ $line->id }}">{{ $line->name }}</option>
                                    @endforeach
                                </select>
                                            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#locationlineModal"><i class="bx bx-plus-circle me-0"></i>
                                        </div>
                                    </div>



                                    <div class="col-md-4">
                                        <x-label for="warehouse_id" name="Warehouse" />
                                        <div class="input-group">
                                        <select name="warehouse_id">
                                    <option value="">Select Warehouse</option>
                                    @foreach($warehouses as $warehouse)
                                        <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                    @endforeach
                                </select>
                                            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#warehouseModal"><i class="bx bx-plus-circle me-0"></i>
                                        </div>
                                    </div>



                                   
                                    <div class="col-md-12">
                                        <div class="d-md-flex d-grid align-items-center gap-3">
                                            <x-button type="submit" class="primary px-4" text="submit" />
                                            <x-anchor-tag href="{{ route('dashboard') }}" text="{{ __('app.close') }}" class="btn btn-light px-4" />
                                        </div>
                                    </div>
                                </form>

                            </div>

                        </div>
					</div>
				</div>
				<!--end row-->
			</div>
		</div>
        <!-- Import Modals -->
        @include("modals.tax.create")
        @include("modals.item.brand.create")
        @include("modals.item.warehouse.create")
        @include("modals.item.location_line.create")
        @include("modals.item.category.create")
        @include("modals.item.serial-tracking")
        @include("modals.item.batch-tracking")

		@endsection

@section('js')
<script src="{{ versionedAsset('custom/js/items/item.js') }}"></script>
<script src="{{ versionedAsset('custom/js/items/serial-tracking.js') }}"></script>
<script src="{{ versionedAsset('custom/js/items/batch-tracking.js') }}"></script>
<script src="{{ versionedAsset('custom/js/modals/tax/tax.js') }}"></script>
<script src="{{ versionedAsset('custom/js/modals/item/brand/brand.js') }}"></script>
<script src="{{ versionedAsset('custom/js/modals/item/category/category.js') }}"></script>
<script src="{{ versionedAsset('custom/js/modals/unit/unit.js') }}"></script>

<script>
document.getElementById('warehouseForm').addEventListener('submit', function(event) {
    event.preventDefault(); // Prevent default form submission

    let formData = new FormData(this);

    fetch(this.action, {
        method: "POST",
        body: formData,
        headers: {
            "X-CSRF-TOKEN": document.querySelector('input[name=_token]').value
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Warehouse created successfully!');
            location.reload(); // Reload the page after success
        } else {
            alert('Warehouse created successfully!');
            location.reload(); // Reload the page after success
        }
    })
    .catch(error => console.error('Error:', error));
});
</script>

<script>
document.getElementById('button-addon2').addEventListener('click', function() {
    let randomCode = generateRandomCode(5);
    document.getElementById('generatedCode').value = randomCode;
});

function generateRandomCode(length) {
    let characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    let result = '';
    for (let i = 0; i < length; i++) {
        result += characters.charAt(Math.floor(Math.random() * characters.length));
    }
    return result;
}
</script>
<script>



fetch('/location/store', {
    method: 'POST',
    body: new FormData(document.getElementById('yourFormId')),
    headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    }
})
.then(response => response.json())
.then(data => {
    if (data.success) {
        window.location.href = data.redirect; // Redirect using provided URL
    } else {
        alert(data.message);
    }
})
.catch(error => console.error('Error:', error));

</script>




@endsection










