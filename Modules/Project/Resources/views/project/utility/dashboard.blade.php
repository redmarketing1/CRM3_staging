<div class="row mt-5 mb-5">
    @if ($project->type == 'project')
        <div class="col-lg-3 col-6">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="theme-avtar bg-primary">
                            <i class="fas fas fa-calendar-day"></i>
                        </div>
                        <div class="col text-end">
                            <h6 class="text-muted">{{ __('Days left') }}</h6>
                            <span class="h6 font-weight-bold mb-0 ">
                                {{ $project->expired_date }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="theme-avtar bg-info">
                            <i class="fas fa-money-bill-alt"></i>
                        </div>
                        <div class="col text-end">
                            <h6 class="text-muted">{{ __('Budget') }}</h6>
                            <span class="h6 font-weight-bold mb-0 ">{{ company_setting('defult_currancy') }}
                                {{ number_format($project->budget) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
    @php
        $class = $project->type == 'template' ? 'col-lg-6 col-6' : 'col-lg-3 col-6';
    @endphp
    <div class="{{ $class }}">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="theme-avtar bg-danger">
                        <i class="fas fa-check-double"></i>
                    </div>
                    <div class="col text-end">
                        <h6 class="text-muted">{{ __('Total Task') }}</h6>
                        <span class="h6 font-weight-bold mb-0 ">{{ $project->countTask() }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="{{ $class }}">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="theme-avtar bg-success">
                        <i class="fas fa-comments"></i>
                    </div>
                    <div class="col text-end">
                        <h6 class="text-muted">{{ __('Comment') }}</h6>
                        <span class="h6 font-weight-bold mb-0 ">{{ $project->countTaskComments() }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
