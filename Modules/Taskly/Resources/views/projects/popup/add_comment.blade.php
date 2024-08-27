 {{ Form::open(['route' => ['project.comment.store', $project->id], 'enctype' => 'multipart/form-data', 'id' => 'comment_form']) }}

 <div class="modal-body">
     <div class="form-group">
         {{ Form::label('comment', __('Your comment'), ['class' => 'form-label text-lg mb-2']) }}
         {{ Form::textarea('comment', null, ['class' => 'form-control tinyMCE', 'required' => 'required', 'rows' => 5, 'id' => 'commentboxes']) }}

         {{ Form::file('file', ['class' => 'form-control text-lg mt-4', 'id' => 'file']) }}
     </div>
 </div>
 <div class="modal-footer">
     <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{ __('Close') }}</button>
     <input type="submit" value="{{ __('Submit Comment') }}" class="btn  btn-primary">
 </div>

 {{ Form::close() }}

 @push('scripts')
     <script type="javascript">
    alert(3);
     init_tiny_mce('.tinyMCE');
 </script>
 @endpush
