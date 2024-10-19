<div class="card">
    <div class="card-header ">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">{{ __('Project Labels') }}</h5>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="row mt-2">
            <div class="col-md-6 form-group">
                <h6 class="form-label">{{ trans('Construction Type') }}</h6>
                <div class="select2-custom construction-label">
                    @foreach ($project->constructionTypeData ?? [] as $type)
                        <div class="font-semibold construction-type"
                            style="background: {{ $type->background_color }};color:{{ $type->font_color }}">
                            {{ $type->name }}
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="col-md-6 form-group">
                <h6 class="form-label">{{ trans('Property Type') }}</h6>
                <div class="select2-custom property-label">
                    @foreach ($project->PropertyTypeData ?? [] as $type)
                        <div class="font-semibold property-type"
                            style="background: {{ $type->background_color }};color:{{ $type->font_color }}">
                            {{ $type->name }}
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 form-group">
                <h6 class="form-label">{{ trans('Project Label') }}</h6>
                <div class="select2-custom project-labels">
                    @foreach ($project->ProjectLabelData ?? [] as $type)
                        <div class="font-semibold project-label-type"
                            style="background: {{ $type->background_color }};color:{{ $type->font_color }}">
                            {{ $type->name }}
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="col-md-6 form-group">
                <h6 class="form-label">{{ trans('Property Type') }}</h6>
                <div class="select2-custom property-label">
                    @foreach ($project->ProjectPriorityData ?? [] as $type)
                        <div class="font-semibold property-type"
                            style="background: {{ $type->background_color }};color:{{ $type->font_color }}">
                            {{ $type->name }}
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
