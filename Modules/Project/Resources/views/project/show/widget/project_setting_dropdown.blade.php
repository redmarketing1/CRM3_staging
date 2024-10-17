@permission('project setting')
    @if (!empty($projectLabel['project_status']))
        <div class="col-sm-auto">
            <button class="btn btn-xs btn-primary project-statusName text-white btn-icon-only width-auto dropdown-toggle"
                type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                style="background-color: {{ $project->backgroundColor }}; color:{{ $project->statusData->font_color }}!important;">
                {{ $project->statusData->name ?? 'Select Status' }}
            </button>

            <div class="dropdown-menu">
                @foreach ($projectLabel['project_status'] as $status)
                    @if ($status->id == env('PROJECT_STATUS_CLIENT'))
                        <a href="javascript:void(0)" data-ajax-popup="true" data-toggle="tooltip" data-size="md"
                            data-url="{{ route('projects.edit_form', [$project->id, 'project_status']) }}"
                            data-bs-toggle="modal" data-bs-target="#exampleModal"
                            data-bs-whatever="{{ __('Select Final Estimation') }}"
                            data-title="{{ __('Select Final Estimation') }}" class="dropdown-item"
                            data-backgroundColor="{{ $status->background_color }}"
                            data-fontColor="{{ $status->font_color }}">
                            {{ $status->name }}
                        </a>
                    @else
                        <a href="javascript:void(0)" class="dropdown-item status" data-status="{{ $status->id }}"
                            data-id="{{ $project->id }}" style="border-left: 5px solid {{ $status->background_color }};">
                            {{ $status->name }}
                        </a>
                    @endif
                @endforeach
            </div>
        </div>
    @endif
@endpermission
