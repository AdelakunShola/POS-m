@extends('layouts.app')
@section('title', __('purchase.order.order'))

        @section('content')
        <!--start page wrapper -->
        <div class="page-wrapper">
            <div class="page-content">
                <x-breadcrumb :langArray="[
                                            'Transaction',
                                            'Cycle Count',
                                        ]"/>
                <div class="row">
                <form class="g-3 needs-validation" id="cycleCountForm" action="{{ route('cycle-count.store') }}" method="POST">
                @csrf

                   
                        <div class="row">
                            <div class="col-12 col-lg-12">
                                <div class="card">
                                    <div class="card-header px-4 py-3">
                                        <h5 class="mb-0">Cycle Count Order Details</h5>
                                    </div>
                                    <div class="card-body p-4 row g-3">
                                            

                                            <div class="col-md-4">
                                                <x-label for="item_id" name="Items To Cycle Count" />
                                                <div class="input-group">
                                                    <select class="form-select" id="item_id" name="item_id">
                                                        <option value="">Select Item</option>
                                                        @foreach($items as $item)
                                                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                                                        @endforeach
                                                    </select>
                                                   
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <x-label for="order_date" name="Schedule Date" />
                                                <div class="input-group mb-3">
                                                    <x-input type="text" additionalClasses="datepicker" name="order_date" :required="true" value=""/>
                                                    <span class="input-group-text" id="input-near-focus" role="button"><i class="fadeIn animated bx bx-calendar-alt"></i></span>
                                                </div>
                                            </div>
                                            


                                         

                            <div class="col-md-3">
                                <x-label for="item_id" name="Assign User" />
                                <x-dropdown-user selected="" :showOnlyUsername='true' />
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
