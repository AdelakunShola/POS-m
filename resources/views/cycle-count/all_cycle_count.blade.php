@extends('layouts.app')
@section('title', __('item.list'))

@section('css')
<link href="{{ asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet">
@endsection
		@section('content')
		<!--start page wrapper -->
		<div class="page-wrapper">
			<div class="page-content">
					<x-breadcrumb :langArray="[
											'Transactions',
											'Cyclecount List',
										]"/>

                    <div class="card">

					<div class="card-header px-4 py-3 d-flex justify-content-between align-items-center">
					    <!-- Other content on the left side -->
					    <div>
					    	<h5 class="mb-0 text-uppercase">Cyclecount List</h5>
					    </div>
					    <div class="d-flex gap-2">
						    

						    <!-- Button pushed to the right side -->
						    <x-anchor-tag href="{{ route('cycle_count.create') }}" text="Create CycleCount" class="btn btn-primary px-5" />
						
						</div>
					</div>

					<div class="card-body">
						<div class="row g-3">
							
                           
                         
                            <div class="col-md-4">
                                <x-label for="user_id" name="{{ __('user.user') }}" />
                                <x-dropdown-user selected="" :showOnlyUsername='true' />
                            </div>
                            
                        </div>
                        
							<div class="table-responsive">
								<table class="table table-striped table-bordered border w-100" id="datatable">
									<thead>
										<tr>
											<th class="d-none"><!-- Which Stores ID & it is used for sorting --></th>
											<th><input class="form-check-input row-select" type="checkbox"></th>
											<th>Warehouse</th>
                                            <th>Product</th>
											<th>Unit</th>
											<th>Expected Stock</th>
											<th style="background-color: green-yellow;">Counted Qty</th>
                                            <th>Value Diff</th>
                                            <th>Recount</th>
											<th>Approved</th>
											<th>{{ __('app.action') }}</th>
										</tr>
									</thead>
                                    <tbody>
                            @foreach ($cyclecount as $count)
                            <tr>
                            <td><input class="form-check-input row-select" type="checkbox"></td>
                                <td>{{ $count->warehouse->name ?? 'N/A' }}</td>
                                <td>{{ $count->item_id ?? 'N/A'  }}</td>
                                <td>{{ $count->unit->name ?? 'N/A' }}</td>
                                <td>{{ $count->product->stock ?? 'N/A'  }}</td>
                                <td>{{ $count->counted_qty ?? 'N/A'  }}</td>
                                <td>{{ $count->value_diff ?? 'N/A'  }}</td>
                                <td>{{ $count->recount ?? 'N/A'  }}</td>
                                <td>{{ $count->approved ?? 'N/A'  }}</td>
                                <td>
                                    <a href="" class="btn btn-warning btn-sm">Count</a>

                                  
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
	

@endsection
