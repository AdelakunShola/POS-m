@extends('layouts.app')
@section('title', __('location.edit'))

@section('content')
<!--start page wrapper -->
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumb :langArray="['item.location', 'location.list', 'location.edit']" />
        <div class="row">
            <div class="col-12 col-lg-12">
                <div class="card">
                    <div class="card-header px-4 py-3 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Edit Location</h5>
                    </div>
                    <div class="card-body p-4">
                        <form class="row g-3" method="POST" id="editLocationForm" action="{{ route('location.update', $location->id) }}" enctype="multipart/form-data">
                            @csrf
                      

                            <input type="hidden" id="operation" name="operation" value="update">
                            <input type="hidden" id="base_url" value="{{ url('/') }}">

                            <div class="col-md-4">
                                <x-label for="name" name="Name" />
                                <x-input type="text" name="name" id="name" value="{{ $location->name }}" required />
                            </div>

                            <div class="col-md-4">
                                <x-label for="storage_capacity" name="Storage Capacity" />
                                <x-input type="number" name="storage_capacity" value="{{ $location->storage_capacity }}" required />
                            </div>

                            <div class="col-md-4">
                                <x-label for="secondary_unit_id" name="Package Type" />
                                <div class="input-group">
                                <select name="secondary_unit_id">
                                    <option value="">Select Package Type</option>
                                    @foreach($units as $unit)
                                        <option value="{{ $unit->id }}" {{ $location->secondary_unit_id == $unit->id ? 'selected' : '' }}>{{ $unit->name }}</option>
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
                                        <option value="{{ $line->id }}" {{ $location->location_line_id == $line->id ? 'selected' : '' }}>{{ $line->name }}</option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target=""><i class="bx me-0"></i>
                                </div>
                            </div>



                           <!-- <div class="col-md-4">
                                <x-label for="warehouse_id" name="Warehouse" />
                                <div class="input-group">
                                <select name="warehouse_id">
                                    <option value="">Select Warehouse</option>
                                    @foreach($warehouses as $warehouse)
                                        <option value="{{ $warehouse->id }}" {{ $location->warehouse_id == $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }}</option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target=""><i class="bx me-0"></i>
                              </div>
                            </div>-->


                            <div class="col-md-12">
                                <div class="d-md-flex d-grid align-items-center gap-3">
                                    <x-button type="submit" class="primary px-4" text="Update" />
                                    <x-anchor-tag href="{{ route('location.list') }}" text="{{ __('app.close') }}" class="btn btn-light px-4" />
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')

@endsection
