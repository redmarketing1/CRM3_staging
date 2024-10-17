@permission('project setting')
    @if (!empty($projectLabel['project_status']))
        <div class="col-sm-auto">
            <button class="btn btn-xs btn-primary project-statusName text-white btn-icon-only width-auto dropdown-toggle"
                type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                style="background-color: {{ $project->backgroundColor }}; color:{{ $project->fontColor }}!important;">
                {{ $project->statusData->name ?? 'Select Status' }}
            </button>

            <div class="dropdown-menu">
                @foreach ($projectLabel['project_status'] as $status)
                    <a href="javascript:void(0)" class="dropdown-item status" data-status="{{ $status->id }}"
                        data-id="{{ $project->id }}" data-font="{{ $status->font_color }}"
                        data-background="{{ $status->background_color }}"
                        style="border-left: 5px solid {{ $status->background_color }};">
                        {{ $status->name }}
                    </a>
                @endforeach
            </div>
        </div>
    @endif
@endpermission
