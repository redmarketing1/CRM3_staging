{{ Form::model($project, ['route' => ['project.update', $project->id], 'method' => 'PUT', 'enctype' => 'multipart/form-data']) }}
{{ Form::hidden('type', 'updateAll') }}
<div class="project-edits">
    <div class="row">
        <div class="form-group col-md-12">
            {{ Form::label('projectname', __('Name'), ['class' => 'form-label']) }}
            {{ Form::text('name', null, ['class' => 'form-control', 'required' => 'required', 'id' => 'projectname', 'placeholder' => __('Project Name')]) }}
        </div>
        <div class="form-group col-md-12">
            {{ Form::label('description', __('Comment'), ['class' => 'form-label']) }}
            {{ Form::textarea('description', null, ['class' => 'form-control tinyMCE', 'rows' => 3, 'id' => 'description', 'placeholder' => __('Add Description')]) }}
        </div>

        <div class="form-group col-md-4">
            {{ Form::label('budget', __('Budget'), ['class' => 'form-label']) }}
            <div class="input-group mb-3">
                <span class="input-group-text">{{ company_setting('defult_currancy') }}</span>
                {{ Form::number('budget', null, ['class' => 'form-control currency_input', 'required' => 'required', 'id' => 'budget', 'placeholder' => __('Project Budget')]) }}
            </div>
        </div>

        <div class="form-group col-md-4">
            {{ Form::label('start_date', __('Start Date'), ['class' => 'form-label']) }}
            <div class="input-group date ">
                {{ Form::date('start_date', null, ['class' => 'form-control', 'required' => 'required', 'id' => 'start_date']) }}
            </div>
        </div>

        <div class="form-group col-md-4">
            {{ Form::label('end_date', __('End Date'), ['class' => 'form-label']) }}
            <div class="input-group date ">
                {{ Form::date('end_date', null, ['class' => 'form-control', 'required' => 'required', 'id' => 'end_date']) }}
            </div>
        </div>

        @if (module_is_active('CustomField') && !$customFields->isEmpty())
            <div class="col-md-12">
                <div class="tab-pane fade show" id="tab-2" role="tabpanel">
                    @include('customfield::formBuilder', ['fildedata' => $project->customField])
                </div>
            </div>
        @endif

        <div class="form-group col-md-4">
            {{ Form::label('status', __('Project Status'), ['class' => 'form-label']) }}
            <div class="input-group date ">
                <select name="status" id="status">
                    @foreach ($projectLabel['project_status'] as $status)
                        <option value="{{ $status->id }}" {{ $project->status == $status->id ? 'selected' : '' }}>
                            {{ $status->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="form-group col-md-4">
            {{ Form::label('construction_type', __('Construction Type'), ['class' => 'form-label']) }}
            <div class="input-group date ">
                <select name="construction_type" id="construction_type">
                    @foreach ($projectLabel['construction_type'] as $construction)
                        <option value="{{ $construction->id }}"
                            {{ $project->construction_type == $construction->id ? 'selected' : '' }}>
                            {{ $construction->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="form-group col-md-4">
            {{ Form::label('property_type', __('Property Type'), ['class' => 'form-label']) }}
            <div class="input-group date ">
                <select name="property_type" id="property_type">
                    @foreach ($projectLabel['property'] as $property)
                        <option value="{{ $property->id }}"
                            {{ $project->property_type == $property->id ? 'selected' : '' }}>
                            {{ $property->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="form-group col-md-4">
            {{ Form::label('project_label', __('Project Label'), ['class' => 'form-label']) }}
            <div class="input-group date ">
                <select name="label" id="label">
                    @foreach ($projectLabel['project_label'] as $label)
                        <option value="{{ $label->id }}" {{ $project->label == $label->id ? 'selected' : '' }}>
                            {{ $label->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="form-group col-md-4">
            {{ Form::label('priority', __('Priority Type'), ['class' => 'form-label']) }}
            <div class="input-group date ">
                <select name="priority" id="priority">
                    @foreach ($projectLabel['priority'] as $priority)
                        <option value="{{ $priority->id }}"
                            {{ $project->priority == $priority->id ? 'selected' : '' }}>
                            {{ $priority->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{ __('Close') }}</button>
    <input type="submit" value="{{ __('Save Changes') }}" class="btn  btn-primary">
</div>
{{ Form::close() }}
