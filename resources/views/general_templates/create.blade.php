{{ Form::open(['route' => 'general-templates.create', 'id' => 'general_template_message_form', 'method' => 'POST']) }}
{{ Form::hidden('lang', app()->getLocale()) }}
<div class="modal-body">
    <div class="form-group mb-5">
        {{ form::label('name', __('Template Name'), ['class' => 'form-label text-lg mb-2']) }}
        {{ form::text('name', '', ['class' => 'form-control', 'required' => 'required']) }}
    </div>

    <div class="form-group">
        {{ Form::label('content', __('Message Template'), ['class' => 'form-label text-lg mb-2']) }}
        {{ Form::textarea('content', null, ['class' => 'form-control tinyMCES', 'required' => 'required', 'rows' => 5, 'id' => 'premsg']) }}
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{ __('Close') }}</button>
    <input type="submit" value="{{ __('Created Template') }}" class="btn  btn-primary">
</div>
{{ Form::close() }}
