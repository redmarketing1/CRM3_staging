<div class="card">
    <div class="card-header ">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">{{ __('Project Labels') }}
                </h5>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 form-group">
                @include('project::project.show.card.labels.construction_type')
            </div>
            <div class="col-md-6 form-group">
                @include('project::project.show.card.labels.propert_type')
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 form-group">
                @include('project::project.show.card.labels.project_type')
            </div>
            <div class="col-md-6 form-group">
                @include('project::project.show.card.labels.priority_type')
            </div>
        </div>
    </div>
</div>
