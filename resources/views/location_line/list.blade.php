{{-- List Location Lines --}}
@extends('layouts.app')
@section('title', __('location_line.location_lines'))

@section('css')
<link href="{{ asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet">
@endsection

@section('content')
<div class="page-wrapper">
    <div class="page-content">

        <div class="card">
            <div class="card-header px-4 py-3 d-flex justify-content-between">
                <h5 class="mb-0 text-uppercase">Location Line List</h5>

                {{-- Button to create a new Location Line --}}
                <x-anchor-tag href="{{ route('location_line.create') }}" text="Create" class="btn btn-primary px-5" />
            </div>

            <div class="card-body">
                <form id="datatableForm" action="{{ route('location_line.delete') }}" method="POST">
                    @csrf
                
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered border w-100" id="datatable">
                            <thead>
                                <tr>
                                    <th class="d-none"></th>
                                    <th><input class="form-check-input row-select" type="checkbox"></th>
                                    <th>{{ __('app.name') }}</th>
                                    <th>warehouse</th>
                                    <th>{{ __('app.created_at') }}</th>
                                    <th>{{ __('app.action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($locationLines as $line)
                                <tr>
                                    <td class="d-none"></td>
                                    <td><input class="form-check-input row-select" type="checkbox"></td>
                                    <td>{{ $line->name }}</td>
                                    <td>{{ $line->warehouse->name ?? 'N/A' }}</td>
                                    <td>{{ $line->created_at->format('Y-m-d H:i') }}</td>
                                    <td>
                                        {{-- Edit Button --}}
                                        <a href="{{ route('location_line.edit', $line->id) }}" class="btn btn-warning btn-sm">Edit</a>

                                        <form action="{{ route('location_line.delete', $line->id) }}" method="POST" style="display:inline;">
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
                </form>
            </div>
        </div>

    </div>
</div>
@endsection

@section('js')
<script src="{{ versionedAsset('assets/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ versionedAsset('assets/plugins/datatable/js/dataTables.bootstrap5.min.js') }}"></script>

<script>
    $(document).ready(function() {
        $('#datatable').DataTable({
            "ordering": true,
            "paging": true,
            "searching": true,
            "info": true,
            "autoWidth": false,
            "responsive": true
        });
    });
</script>
@endsection
