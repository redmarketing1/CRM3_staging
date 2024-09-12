<section class="col-md-12">
    <div class="card table-card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">{{ __('Project Progress') }}</h5>
            <div class="">
                <a class="btn btn-sm btn-primary text-white" data-size="xl" data-ajax-popup="true"
                    data-url="{{ route('project.progress', [$project->id]) }}" data-toggle="tooltip" aria-label="popup"
                    title="Create Internal Progress" data-bs-original-title="Create Internal Progress"
                    data-title="Create Internal Progress">
                    <i class="ti ti-box-multiple-9"></i>
                    {{ __('Internal Progress') }}
                </a>
            </div>
        </div>
        <div class="card-body p-3" id="progressContainer">
            <table class="table w-100 table-hover table-bordered" id="progress-table">
                <thead>
                    <th>{{ __('ID') }}</th>
                    <th>{{ __('Client Name') }}</th>
                    <th>{{ __('Comment') }}</th>
                    <th>{{ __('User') }}</th>
                    <th>{{ __('Date') }}</th>
                    <th>{{ __('Action') }}</th>
                </thead>

            </table>
        </div>
    </div>
</section>
