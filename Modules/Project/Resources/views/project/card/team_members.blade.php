<div class="card deta-card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">
                    {{ __('Team Members') }} ({{ count($project->users) }})
                </h5>
            </div>
            <div class="float-end">
                <p class="text-muted d-sm-flex align-items-center mb-0">
                    <a href="javascript:;" class="btn btn-sm btn-primary" data-ajax-popup="true" data-title="{{ __('Invite') }}"
                        data-bs-toggle="tooltip" data-bs-title="{{ __('Invite') }}"
                        data-url="{{ route('projects.invite.popup', [$project->id]) }}"><i
                            class="ti ti-brand-telegram"></i></a>
                </p>
            </div>
        </div>
    </div>

    <div class="card-body top-10-scroll">
        @foreach ($project->users as $user)
            <ul class="list-group list-group-flush" style="width: 100%;">
                <li class="list-group-item px-0">
                    <div class="row align-items-center justify-content-between">
                        <div class="col-sm-auto mb-3 mb-sm-0">
                            <div class="d-flex align-items-center px-2">
                                <a href="javascript:;" class=" text-start">
                                    <img alt="image" data-bs-toggle="tooltip" data-bs-placement="top"
                                        title="{{ $user->name }}"
                                        @if ($user->avatar) src="{{ get_file($user->avatar) }}" @else src="{{ get_file('avatar.png') }}" @endif
                                        class="rounded-circle " width="40px" height="40px">
                                </a>
                                <div class="px-2">
                                    <h5 class="m-0">{{ $user->name }}</h5>
                                    <small class="text-muted">{{ $user->email }}<span class="text-primary "> -
                                            {{ (int) count($project->user_done_tasks($user->id)) }}/{{ (int) count($project->user_tasks($user->id)) }}</span></small>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-auto text-sm-end d-flex align-items-center">
                            @auth('web')
                                @if ($user->id != Auth::id())
                                    @permission('team member remove')
                                        <form id="delete-user-{{ $user->id }}"
                                            action="{{ route('projects.user.delete', [$project->id, $user->id]) }}"
                                            method="POST" style="display: none;" class="d-inline-flex">
                                            <a href="#"
                                                class="action-btn btn-danger mx-1  btn btn-sm d-inline-flex align-items-center bs-pass-para show_confirm"
                                                data-confirm="{{ __('Are You Sure?') }}"
                                                data-text="{{ __('This action can not be undone. Do you want to continue?') }}"
                                                data-confirm-yes="delete-user-{{ $user->id }}" data-toggle="tooltip"
                                                title="{{ __('Delete') }}"><i class="ti ti-trash"></i></a>

                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    @endpermission
                                @endif
                            @endauth
                        </div>
                    </div>
                </li>
            </ul>
        @endforeach
    </div>
</div>
