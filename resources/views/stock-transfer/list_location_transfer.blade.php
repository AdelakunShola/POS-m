@extends('layouts.app')
@section('title', __('warehouse.stock_transfer_list'))

@section('css')
<link href="{{ asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet">
@endsection
        @section('content')

        <!--start page wrapper -->
        <div class="page-wrapper">
            <div class="page-content">
                    <x-breadcrumb :langArray="[
                                            'warehouse.warehouse',
                                            'warehouse.stock_transfer_list',
                                        ]"/>

                    <div class="card">

                    <div class="card-header px-4 py-3 d-flex justify-content-between">
                        <!-- Other content on the left side -->
                        <div>
                            <h5 class="mb-0 text-uppercase">{{ __('warehouse.stock_transfer_list') }}</h5>
                        </div>

                        @can('stock_transfer.create')
                        <!-- Button pushed to the right side -->
                        <x-anchor-tag href="{{ route('location_transfer.create') }}" text="Product Transfer" class="btn btn-primary px-5" />
                        @endcan
                    </div>
                    <div class="card-body">
                        <div class="row g-3">

                            <div class="col-md-3">
                                <x-label for="from_date" name="{{ __('app.from_date') }}" />
                                <a tabindex="0" class="text-primary" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-content="Filter by Sale Date"><i class="fadeIn animated bx bx-info-circle"></i></a>
                                <div class="input-group mb-3">
                                    <x-input type="text" additionalClasses="datepicker-edit" name="from_date" :required="true" value=""/>
                                    <span class="input-group-text" id="input-near-focus" role="button"><i class="fadeIn animated bx bx-calendar-alt"></i></span>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <x-label for="to_date" name="{{ __('app.to_date') }}" />
                                <a tabindex="0" class="text-primary" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-content="Filter by Sale Date"><i class="fadeIn animated bx bx-info-circle"></i></a>
                                <div class="input-group mb-3">
                                    <x-input type="text" additionalClasses="datepicker-edit" name="to_date" :required="true" value=""/>
                                    <span class="input-group-text" id="input-near-focus" role="button"><i class="fadeIn animated bx bx-calendar-alt"></i></span>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <x-label for="user_id" name="{{ __('user.user') }}" />
                                <x-dropdown-user selected="" :showOnlyUsername='true' :canViewAllUsers="auth()->user()->can('stock_transfer.can.view.other.users.stock.transfers')" />
                            </div>
                        </div>
                       

                            <div class="table-responsive">
                                <table class="table table-striped table-bordered border w-100" id="datatable">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <!--<th>Transfer Code</th>-->
                                    <th>Barcode</th>
                                    <th>Date</th>
                                    <th>Tranfer From</th>
                                    <th>Transfer To</th>
                                    <th>Transfer By</th>
                                    <th>Created At</th>
                                    <th>{{ __('app.action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($transfers as $index => $transfer)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <!--<td>{{ $transfer->transfer_code }}</td>-->
                                        <td>{{ $transfer->barcode }}</td>
                                        <td>{{ $transfer->transfer_date }}</td>
                                        <td>{{ $transfer->fromLocationLine->name ?? 'N/A' }} - {{ $transfer->fromLocation->name ?? 'N/A' }} </td>
                                        <td>{{ $transfer->toLocationLine->name ?? 'N/A' }} - {{ $transfer->toLocation->name ?? 'N/A' }} </td>
                                        <td>{{ $transfer->createdBy->username ?? 'N/A' }}</td>
                                        <td>{{ $transfer->created_at->format('Y-m-d H:i') }}</td>
                                        <td>
                                            <a href="" class="btn btn-info btn-sm">{{ __('View') }}</a>
                                            <a href="" class="btn btn-warning btn-sm">{{ __('Edit') }}</a>
                                            <form action="" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">{{ __('Delete') }}</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach

                                @if($transfers->isEmpty())
                                    <tr>
                                        <td colspan="6" class="text-center">{{ __('No transfers found') }}</td>
                                    </tr>
                                @endif
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
        @endsection
@section('js')
<script src="{{ versionedAsset('assets/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ versionedAsset('assets/plugins/datatable/js/dataTables.bootstrap5.min.js') }}"></script>
<script src="{{ versionedAsset('custom/js/common/common.js') }}"></script>
<script src="{{ versionedAsset('custom/js/stock-transfer/stock-transfer-list.js') }}"></script>
@endsection
