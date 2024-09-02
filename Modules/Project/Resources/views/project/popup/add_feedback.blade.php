  {{ Form::open(['route' => ['project.feedback.store', $project->id], 'enctype' => 'multipart/form-data', 'id' => 'feedback_form']) }}

  <div class="modal-body">
      <div class="form-group dropdown-premsg">

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
                      @foreach ($templateItems as $item)
                          @foreach ($item->contentTemplate as $templateLang)
                              <li>
                                  <a class="dropdown-item text-primary" href="#" data-id="{{ $item->id }}"
                                      data-name="{{ $item->name }}" data-content="{{ $templateLang->content }}">
                                      {{ $item->name }}
                                  </a>
                              </li>
                          @endforeach
                      @endforeach
                  </ul>
              </div>
          </div>

          {{ Form::label('feedback', __('Your feedback'), ['class' => 'form-label text-lg mb-2']) }}
          {{ Form::textarea('feedback', null, ['class' => 'form-control tinyMCES', 'required' => 'required', 'rows' => 5, 'id' => 'premsg']) }}

          {{ Form::file('file', ['class' => 'form-control text-lg mt-4', 'id' => 'file']) }}
      </div>
  </div>
  <div class="modal-footer">
      <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{ __('Close') }}</button>
      <input type="submit" value="{{ __('Submit feedback') }}" class="btn  btn-primary">
  </div>

  {{ Form::close() }}
