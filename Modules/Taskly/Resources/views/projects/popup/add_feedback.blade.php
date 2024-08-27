  {{ Form::open(['route' => ['project.feedback.store', $project->id], 'enctype' => 'multipart/form-data', 'id' => 'feedback_form']) }}

  <div class="modal-body">
      <div class="form-group">
          {{ Form::label('feedback', __('Your feedback'), ['class' => 'form-label text-lg mb-2']) }}
          {{ Form::textarea('feedback', null, ['class' => 'form-control tinyMCE', 'required' => 'required', 'rows' => 5, 'id' => 'feedbackboxes']) }}

          {{ Form::file('file', ['class' => 'form-control text-lg mt-4', 'id' => 'file']) }}
      </div>
  </div>
  <div class="modal-footer">
      <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{ __('Close') }}</button>
      <input type="submit" value="{{ __('Submit feedback') }}" class="btn  btn-primary">
  </div>

  {{ Form::close() }}
