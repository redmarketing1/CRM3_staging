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
        <div class="row mt-2">
            <div class="col-md-6 form-group">
                <label for="construction_type" class="form-label">Construction Type</label>
                <div class="selct2-custom">
                    <select name="construction_type" class="form-control filter_select2"
                        onchange="store_to_project_data('construction_type',this)" multiple>
                        <option value="" disabled>Select</option>
                        @if (isset($construction_types) && count($construction_types) > 0)
                            @foreach ($construction_types as $construction_type)
                                @php
                                    $selected_construction_type = '';
                                    if (isset($project->construction_type)) {
                                        $selected_construction_types = explode(',', $project->construction_type);
                                        if (
                                            count($selected_construction_types) > 0 &&
                                            in_array($construction_type->id, $selected_construction_types)
                                        ) {
                                            $selected_construction_type = 'selected';
                                        }
                                    }
                                @endphp
                                <option value="{{ $construction_type->id }}"
                                    data-background_color="{{ $construction_type->background_color }}"
                                    data-font_color="{{ $construction_type->font_color ? $construction_type->font_color : '#fff' }}"
                                    {{ $selected_construction_type }}>
                                    {{ $construction_type->name }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
            </div>
            <div class="col-md-6 form-group">
                <label for="property" class="form-label">Property</label>
                <div class="selct2-custom">
                    <select name="property" class="form-control filter_select2"
                        onchange="store_to_project_data('property_type',this)" multiple>
                        <option value="" disabled>Select</option>
                        @if (isset($properties) && count($properties) > 0)
                            @foreach ($properties as $property)
                                @php
                                    $selected_property_type = '';
                                    if (isset($project->property_type)) {
                                        $selected_property_types = explode(',', $project->property_type);
                                        if (
                                            count($selected_property_types) > 0 &&
                                            in_array($property->id, $selected_property_types)
                                        ) {
                                            $selected_property_type = 'selected';
                                        }
                                    }
                                @endphp
                                <option value="{{ $property->id }}"
                                    data-background_color="{{ $property->background_color }}"
                                    data-font_color="{{ $property->font_color ? $property->font_color : '#fff' }}"
                                    {{ $selected_property_type }}>{{ $property->name }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 form-group">
                <label for="status_label" class="form-label">Project Label</label>
                <select name="status_label" class="form-control filter_select2"
                    onchange="store_to_project_data('label',this)" multiple>
                    <option value="">Select</option>
                    @if (isset($status_labels) && count($status_labels) > 0)
                        @foreach ($status_labels as $status_label)
                            @php
                                $selected_status_label = '';
                                if (isset($project->label)) {
                                    $selected_status_array = explode(',', $project->label);
                                    if (
                                        count($selected_status_array) > 0 &&
                                        in_array($status_label->id, $selected_status_array)
                                    ) {
                                        $selected_status_label = 'selected';
                                    }
                                }
                            @endphp
                            <option value="{{ $status_label->id }}"
                                data-background_color="{{ $status_label->background_color }}"
                                data-font_color="{{ $status_label->font_color ? $status_label->font_color : '#fff' }}"
                                {{ $selected_status_label }}>{{ $status_label->name }}</option>
                        @endforeach
                    @endif
                </select>
            </div>
            <div class="col-md-6 form-group">
                <label for="priority" class="form-label">{{ __('Priority') }}</label>
                <select name="priority" id="priority" class="form-control filter_select2"
                    onchange="store_to_project_data('priority',this)" multiple>
                    <option value="">Select</option>
                    @php
                        //	$project->priority = ($project->priority > 0) ? $project->priority : env('DEFAULT_PRIORITY');
                    @endphp
                    @if (isset($priorities) && count($priorities) > 0)
                        @foreach ($priorities as $priority)
                            @php
                                //	$selected_priority = (isset($project->priority) && $priority->id == $project->priority) ? 'selected' : '';
                                $selected_priority = '';
                                if (isset($project->priority)) {
                                    $selected_status_array = explode(',', $project->priority);
                                    if (
                                        count($selected_status_array) > 0 &&
                                        in_array($priority->id, $selected_status_array)
                                    ) {
                                        $selected_priority = 'selected';
                                    }
                                }
                            @endphp
                            <option value="{{ $priority->id }}"
                                data-background_color="{{ $priority->background_color }}"
                                data-font_color="{{ $priority->font_color ? $priority->font_color : '#fff' }}"
                                {{ $selected_priority }}>{{ $priority->name }}</option>
                        @endforeach
                    @endif
                </select>
            </div>
        </div>
    </div>
</div>
