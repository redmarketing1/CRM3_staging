{{ Form::open(['route' => ['project.delay.store', $id], 'method' => 'POST', 'enctype' => 'multipart/form-data']) }}
<div class="row">
    <div class="form-group col-md-12">
        {{ Form::label('new_deadline', __('New Deadline'), ['class' => 'col-form-label']) }}
        {!! Form::date('new_deadline', '', ['class' => 'form-control', 'required' => 'required']) !!}
    </div>
    <div class="form-group col-md-12">
        {{ Form::label('delay_in_weeks', __('Delay in Weeks'), ['class' => 'col-form-label']) }}
        {!! Form::text('delay_in_weeks', '', ['class' => 'form-control', 'required' => 'required']) !!}
    </div>
    <div class="form-group col-md-12">
        {{ Form::label('reason', __('Reason for Delay'), ['class' => 'col-form-label']) }}
        {!! Form::textarea('reason', '', ['class' => 'form-control', 'rows' => 2, 'required' => 'required']) !!}
    </div>
    <div class="form-group col-md-12">
        {{ Form::label('internal_comment', __('Internal Comment'), ['class' => 'col-form-label']) }}
        {!! Form::textarea('internal_comment', '', ['class' => 'form-control', 'rows' => 2, 'required' => 'required']) !!}
    </div>
    <div class="form-group col-md-12">
        {{ Form::label('media', __('Media'), ['class' => 'col-form-label']) }}
        {!! Form::file('media[]', ['class' => 'form-control', 'multiple', 'required' => 'required']) !!}
    </div>
    <div class="modal-footer pr-0">
        <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{ __('Close') }}</button>
        {{ Form::submit(__('Add'), ['class' => 'btn  btn-primary']) }}
    </div>
</div>

{{ Form::close() }}
