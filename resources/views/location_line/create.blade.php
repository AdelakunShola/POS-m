{{-- Create Location Line --}}
@extends('layouts.app')
@section('title', __('location_line.create'))

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumb :langArray="['app.settings', 'location_line.location_lines', 'location_line.create']"/>
        <div class="row">
            <div class="col-12 col-lg-6">
                <div class="card">
                    <div class="card-header px-4 py-3">
                        <h5 class="mb-0">{{ __('location_line.details') }}</h5>
                    </div>
                    <div class="card-body p-4">
                        <form id="locationLineForm" method="POST" action="{{ route('location_line.store') }}">
                            @csrf
                            @method('POST')
                            <div class="col-md-12">
                                <x-label for="name" name="{{ __('app.name') }}" />
                                <x-input type="text" name="name" :required="true" autofocus />
                            </div>
                            <div class="col-md-12">
                                <x-button type="submit" class="primary px-4" text="{{ __('app.submit') }}" />
                                <x-anchor-tag href="{{ route('dashboard') }}" text="{{ __('app.close') }}" class="btn btn-light px-4" />
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

