@extends('layouts.app')
@section('title', __('location.locations'))

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <div class="card">
            <div class="card-header px-4 py-3 d-flex justify-content-between">
                <h5 class="mb-0 text-uppercase">Location List</h5>
                <x-anchor-tag href="{{ route('location.create') }}" text="Create Location" class="btn btn-primary px-5" />
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered border w-100" id="datatable">
                        <thead>
                            <tr>
                                <th>Location Line</th>
                                <th>{{ __('app.name') }}</th>
                                <th>Warehouse</th>
                                <th>Package Type</th>
                                <th>Storage Capacity</th>
                                <th>Current Capacity</th>
                                <th>{{ __('app.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($locations as $location)
                            <tr>
                                <td>{{ $location->locationLine->name ?? 'N/A' }}</td>
                                <td>{{ $location->name }}</td>
                                <td>{{ $location->warehouse->name ?? 'N/A' }}</td>
                                <td>{{ $location->unit->name ?? 'N/A' }}</td>
                                <td>{{ $location->storage_capacity }}</td>
                                <td>{{ $location->current_capacity }}</td>
                                <td>
                                    <a href="{{ route('location.edit', $location->id) }}" class="btn btn-warning btn-sm">Edit</a>

                                    <form action="{{ route('location.delete', $location->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm"
                                            onclick="return confirm('Are you sure you want to delete this?');">
                                            Delete
                                        </button>
                                    </form>
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
@endsection
