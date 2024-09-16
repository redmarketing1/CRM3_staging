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
                    <a href="javascript:;" class="btn btn-sm btn-primary" data-ajax-popup="true" data-title="{{ __('Invite') }}"
                        data-bs-toggle="tooltip" data-bs-title="{{ __('Invite') }}"
                        data-url="{{ route('projects.invite.popup', [$project->id]) }}"><i
                            class="ti ti-brand-telegram"></i></a>
                </p>
            </div> --}}
        </div>
    </div>

    <div class="card-body top-10-scroll">
        <div class="select2-custom">
            <select name="project_member" class="form-control member_select2"
            onchange="save_project_member_details(this)" multiple>
            <option value="" disabled>Select</option>
            @foreach($workspace_users as $row)
                <option data-background_color="#59c2a6" data-font_color="#ffffff" 
                    value="{{ $row->id }}" 
                    @foreach($project->users as $pusr) 
                        @if($pusr->id == $row->id) 
                            selected 
                        @endif 
                    @endforeach>
                    {{ $row->name }}
                </option>
            @endforeach
        </select>
        </div>
    </div>
    
</div>


