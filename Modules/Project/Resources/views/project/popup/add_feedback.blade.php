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
                      <li>
                          <a class="dropdown-item text-primary" href="#"
                              data-content="Thank you for your feedback.">
                              Thank You Message
                          </a>
                      </li>
                      <li>
                          <a class="dropdown-item text-primary" href="#"
                              data-content="We appreciate your business.">
                              Appreciation Message
                          </a>
                      </li>
                      <li>
                          <a class="dropdown-item text-primary" href="#"
                              data-content="Your request has been received.">
                              Request Received
                          </a>
                      </li>
                      <li>
                          <a class="dropdown-item text-primary" href="#"
                              data-content="We will get back to you shortly.">
                              Follow-Up Message
                          </a>
                      </li>
                      <li>
                          <a class="dropdown-item text-primary" href="#"
                              data-content="Please provide more details.">
                              Request for More Information
                          </a>
                      </li>
                      <li>
                          <a class="dropdown-item text-primary" href="#"
                              data-content="Your order is being processed.">
                              Order Processing
                          </a>
                      </li>
                      <li>
                          <a class="dropdown-item text-primary" href="#"
                              data-content="Your appointment is confirmed.">
                              Appointment Confirmation
                          </a>
                      </li>
                      <li>
                          <a class="dropdown-item text-primary" href="#"
                              data-content="We are sorry for the inconvenience.">
                              Apology Message
                          </a>
                      </li>
                      <li>
                          <a class="dropdown-item text-primary" href="#"
                              data-content="Your account has been updated.">
                              Account Update
                          </a>
                      </li>
                      <li>
                          <a class="dropdown-item text-primary" href="#"
                              data-content="Your subscription has been renewed.">
                              Subscription Renewal
                          </a>
                      </li>
                  </ul>
              </div>
          </div>

          {{ Form::label('feedback', __('Your feedback'), ['class' => 'form-label text-lg mb-2']) }}
          {{ Form::textarea('feedback', null, ['class' => 'form-control tinyMCE', 'required' => 'required', 'rows' => 5, 'id' => 'premsg']) }}

          {{ Form::file('file', ['class' => 'form-control text-lg mt-4', 'id' => 'file']) }}
      </div>
  </div>
  <div class="modal-footer">
      <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{ __('Close') }}</button>
      <input type="submit" value="{{ __('Submit feedback') }}" class="btn  btn-primary">
  </div>

  {{ Form::close() }}
