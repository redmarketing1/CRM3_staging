<div class="card">
    <div class="card-header">
        <div class="float-end">
            <a href="#" data-bs-toggle="tooltip" data-bs-placement="top" title="Refferals"><i class=""></i></a>
        </div>
        <h5>{{ __('Project Status') }}</h5>
    </div>
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-xl-6 col- md-8 col-12">
                <div id="projects-chart"></div>
            </div>
            <div class="col-6">

                <div class="col-6">
                    <span class="d-flex align-items-center mb-2">
                        <i class="f-10 lh-1 fas fa-circle text-danger"></i>
                        <span class="ms-2 text-sm">{{ __('On Going') }}</span>
                    </span>
                </div>
                <div class="col-6">
                    <span class="d-flex align-items-center mb-2">
                        <i class="f-10 lh-1 fas fa-circle text-warning"></i>
                        <span class="ms-2 text-sm">{{ __('On Hold') }}</span>
                    </span>
                </div>
                <div class="col-6">
                    <span class="d-flex align-items-center mb-2">
                        <i class="f-10 lh-1 fas fa-circle text-primary"></i>
                        <span class="ms-2 text-sm">{{ __('Finished') }}</span>
                    </span>
                </div>
            </div>
            <div class="row text-center">
                @foreach ($processData['percentage'] as $index => $percentage)
                    <div class="col-4">
                        <i class="fas fa-chart"></i>
                        <h6 class="font-weight-bold">
                            <span>{{ $percentage }}%</span>
                        </h6>
                        <p class="text-muted">{{ __($processData['label'][$index]) }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
