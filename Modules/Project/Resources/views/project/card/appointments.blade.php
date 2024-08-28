<div class="card">
    <div class="card-header ">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">{{ __('Appointments') }}
                </h5>
            </div>
            @if (\Auth::user()->hasRole('company'))
                <div class="float-end">
                    <p class="text-muted d-sm-flex align-items-center mb-0">
                        <a class="btn btn-sm btn-primary" data-size="lg" data-ajax-popup="true"
                            data-title="{{ __('Create') }}" data-url="" data-toggle="tooltip"
                            title="{{ __('Create') }}"><i class="ti ti-plus"></i></a>
                    </p>
                </div>
            @endif
        </div>
    </div>
    <div class="card-body">

    </div>
</div>
