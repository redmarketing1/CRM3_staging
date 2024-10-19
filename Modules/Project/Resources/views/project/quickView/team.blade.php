<div class="card deta-card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">
                    {{ __('Team Members') }} (<span class="projectteamcount">{{ count($project->users) }}</span>)
                </h5>
            </div>
            {{-- <div class="float-end">
                <p class="text-muted d-sm-flex align-items-center mb-0">
                    <a href="javascript:;" class="btn btn-sm btn-primary" data-ajax-popup="true"
                        data-title="{{ __('Invite') }}" data-bs-toggle="tooltip" data-bs-title="{{ __('Invite') }}"
                        data-url="{{ route('projects.invite.popup', [$project->id]) }}"><i
                            class="ti ti-brand-telegram"></i></a>
                </p>
            </div> --}}
        </div>
    </div>

    <div class="card-body top-10-scroll">
        <div class="select2-custom">
            @foreach ($project->users as $team)
                <div class="font-semibold mb-1 text-base">
                    {{ $team->name }}
                </div>
            @endforeach
        </div>
    </div>

</div>
