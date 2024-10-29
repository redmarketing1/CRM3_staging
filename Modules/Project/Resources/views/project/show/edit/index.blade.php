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
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{ __('Close') }}</button>
    <input type="submit" value="{{ __('Save Changes') }}" class="btn  btn-primary">
</div>
{{ Form::close() }}
