 <!-- Warehouse Modal: start -->
 <div class="modal fade" id="locationlineModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" >Create Location Line</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
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
<!-- Warehouse Modal: end -->
