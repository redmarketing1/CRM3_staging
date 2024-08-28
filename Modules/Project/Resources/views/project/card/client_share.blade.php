  <div class="card deta-card">
      <div class="card-header ">
          <div class="d-flex justify-content-between align-items-center">
              <div>
                  <h5 class="mb-0">{{ __('Clients') }} ({{ count($project->clients) }})
                  </h5>
              </div>
              <div class="float-end">
                  <p class="text-muted d-none d-sm-flex align-items-center mb-0">
                      <a href="#" class="btn btn-sm btn-primary" data-ajax-popup="true"
                          data-title="{{ __('Share to Client') }}" data-toggle="tooltip"
                          title="{{ __('Share to Client') }}"
                          data-url="{{ route('projects.share.popup', [$project->id]) }}"><i class="ti ti-share"></i></a>
                  </p>
              </div>
          </div>
      </div>
      <div class="card-body top-10-scroll">
          @foreach ($project->clients as $client)
              <ul class="list-group list-group-flush" style="width: 100%;">
                  <li class="list-group-item px-0">
                      <div class="row align-items-center justify-content-between">
                          <div class="col-sm-auto mb-3 mb-sm-0">
                              <div class="d-flex align-items-center px-2">
                                  <a href="#" class=" text-start">
                                      <img alt="image" data-bs-toggle="tooltip" data-bs-placement="top"
                                          title="{{ $client->name }}"
                                          @if ($client->avatar) src="{{ get_file($client->avatar) }}" @else src="{{ get_file('avatar.png') }}" @endif
                                          class="rounded-circle " width="40px" height="40px">
                                  </a>
                                  <div class="px-2">
                                      <h5 class="m-0">{{ $client->name }}</h5>
                                      <small class="text-muted">{{ $client->email }}</small>
                                  </div>
                              </div>
                          </div>
                          <div class="col-sm-auto text-sm-end d-flex align-items-center">
                              @if (\Auth::user()->hasRole('company'))
                                  @permission('team client remove')
                                      <form id="delete-client-{{ $client->id }}"
                                          action="{{ route('project.client.delete', [$project->id, $client->id]) }}"
                                          method="POST" style="display: none;" class="d-inline-flex">
                                          <a href="#"
                                              class="action-btn btn-danger mx-1  btn btn-sm d-inline-flex align-items-center bs-pass-para show_confirm"
                                              data-confirm="{{ __('Are You Sure?') }}"
                                              data-text="{{ __('This action can not be undone. Do you want to continue?') }}"
                                              data-confirm-yes="delete-client-{{ $client->id }}" data-toggle="tooltip"
                                              title="{{ __('Delete') }}"><i class="ti ti-trash"></i></a>
                                          @csrf
                                          @method('DELETE')

                                      </form>
                                  @endpermission
                              @endif
                          </div>
                      </div>
                  </li>
              </ul>
          @endforeach
      </div>
  </div>
