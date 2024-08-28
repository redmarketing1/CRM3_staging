<div class="card-container cc2">
    <div class="card table-card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">{{ __('Project Progress') }}</h5>
            <div class="">
                <a href="{{ route('projects.project_progress', [\Crypt::encrypt($project->id), 'display' => 'all']) }}"
                    class="btn btn-sm btn-primary btn-icon m-1" target="_blank">
                    <i class="ti ti-plus"></i>
                    {{ __('Create Internal Progress') }}
                </a>
                <a href="{{ route('projects.project_progress', [\Crypt::encrypt($project->id)]) }}"
                    class="btn btn-sm btn-primary btn-icon m-1" target="_blank">
                    <i class="ti ti-plus"></i>
                    {{ __('Client Progress') }}
                </a>
            </div>
        </div>
        <div class="card-body p-3" id="progress-div">
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
</div>
