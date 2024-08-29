 {{ Form::open(['route' => ['project.comment.store', $project->id], 'enctype' => 'multipart/form-data', 'id' => 'comment_form']) }}

 <div class="modal-body">
     <div class="form-group">

         <div class="mb-4">
             <div class="dropdown dash-h-item drp-language">
                 <a class="dash-head-link w-100 dropdown-toggle arrow-none m-0" data-bs-toggle="dropdown" href="#"
                     role="button" aria-haspopup="false" aria-expanded="false" id="dropdownLanguage">
                     <span class="drp-text hide-mob text-dark text-lg">
                         {{ __('Select Message Template') }}
                     </span>
                     <i class="ti ti-chevron-down drp-arrow nocolor"></i>
                 </a>
                 <ul class="dropdown-menu w-100 dash-h-dropdown dropdown-menu-end" aria-labelledby="dropdownLanguage">
                     @foreach ($templateItems as $notification_template)
                         <li>
                             <a class="dropdown-item text-primary" href="#"
                                 data-id="{{ $notification_template->id }}">
                                 {{ $notification_template->name }}
                             </a>
                         </li>
                     @endforeach
                 </ul>
             </div>
         </div>


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
