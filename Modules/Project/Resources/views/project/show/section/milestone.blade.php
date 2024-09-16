  <div class="card-container cc1 mt-3 mb-4">
      <div class="card milestone-card table-card">
          <div class="card-header">
              <div class="d-flex justify-content-between align-items-center">
                  <div>
                      <h5 class="mb-0">{{ __('Milestones') }} ({{ count($project->milestones) }})</h5>
                  </div>
                  <div class="float-end">
                      @permission('milestone create')
                          <p class="text-muted d-sm-flex align-items-center mb-0">
                              <a class="btn btn-sm btn-primary" data-size="lg" data-ajax-popup="true"
                                  data-title="{{ __('Create Milestone') }}"
                                  data-url="{{ route('projects.milestone', [$project->id]) }}" data-toggle="tooltip"
                                  title="{{ __('Create Milestone') }}"><i class="ti ti-plus"></i></a>
                          </p>
                      @endpermission
                  </div>
              </div>
          </div>
          <div class="card-body">

              <div class="table-responsive">
                  <table id="" class="table table-bordered px-2">
                      <thead>
                          <tr>
                              <th>{{ __('Name') }}</th>
                              <th>{{ __('Status') }}</th>
                              <th>{{ __('Start Date') }}</th>
                              <th>{{ __('End Date') }}</th>
                              <th>{{ __('Cost') }}</th>
                              <th>{{ __('Progress') }}</th>
                              <th>{{ __('Action') }}</th>
                          </tr>
                      </thead>
                      <tbody>
                          @foreach ($project->milestones as $key => $milestone)
                              <tr>
                                  <td>
                                      <a href="#" class="d-block font-weight-500 mb-0"
                                          @permission('milestone delete') data-ajax-popup="true" data-title="{{ __('Milestone Details') }}"  data-url="{{ route('projects.milestone.show', [$milestone->id]) }}" @endpermission>
                                          <h5 class="m-0"> {{ $milestone->title }} </h5>
                                      </a>
                                  </td>
                                  <td>

                                      @if ($milestone->status == 'complete')
                                          <label class="badge bg-success p-2 px-3 rounded">{{ __('Complete') }}</label>
                                      @else
                                          <label
                                              class="badge bg-warning p-2 px-3 rounded">{{ __('Incomplete') }}</label>
                                      @endif
                                  </td>
                                  <td>{{ $milestone->start_date }}</td>
                                  <td>{{ $milestone->end_date }}</td>
                                  <td>{{ company_setting('defult_currancy') }}{{ $milestone->cost }}
                                  </td>
                                  <td>
                                      <div class="progress_wrapper">
                                          <div class="progress">
                                              <div class="progress-bar" role="progressbar"
                                                  style="width: {{ $milestone->progress }}px;" aria-valuenow="55"
                                                  aria-valuemin="0" aria-valuemax="100">
                                              </div>
                                          </div>
                                          <div class="progress_labels">
                                              <div class="total_progress">

                                                  <strong> {{ $milestone->progress }}%</strong>
                                              </div>

                                          </div>
                                      </div>
                                  </td>
                                  <td class="col-auto">
                                      @permission('milestone edit')
                                          <div class="action-btn btn-primary ms-2">
                                              <a class="action-btn btn-info mx-1  btn btn-sm d-inline-flex align-items-center"
                                                  data-ajax-popup="true" data-size="lg"
                                                  data-title="{{ __('Edit Milestone') }}"
                                                  data-url="{{ route('projects.milestone.edit', [$milestone->id]) }}"
                                                  data-toggle="tooltip" title="{{ __('Edit') }}"><i
                                                      class="ti ti-pencil text-white"></i></a>
                                          </div>
                                      @endpermission
                                      @permission('milestone delete')
                                          <form id="delete-form1-{{ $milestone->id }}"
                                              action="{{ route('projects.ajax', [$milestone->id]) }}" method="POST"
                                              style="display: none;" class="d-inline-flex">
                                              <a href="#"
                                                  class="action-btn btn-danger mx-1  btn btn-sm d-inline-flex align-items-center bs-pass-para show_confirm"
                                                  data-confirm="{{ __('Are You Sure?') }}"
                                                  data-text="{{ __('This action can not be undone. Do you want to continue?') }}"
                                                  data-confirm-yes="delete-form1-{{ $milestone->id }}"
                                                  data-toggle="tooltip" title="{{ __('Delete') }}"><i
                                                      class="ti ti-trash"></i></a>
                                              @csrf
                                              @method('DELETE')

                                          </form>
                                      @endpermission

                                  </td>
                              </tr>
                          @endforeach
                      </tbody>
                  </table>
              </div>
          </div>
      </div>
  </div>
