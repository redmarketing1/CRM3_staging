@permission('task manage')
    <div class="col-sm-auto">
        <a href="{{ route('projects.task.board', [$project->id]) }}"
            class="btn btn-xs btn-primary btn-icon-only width-auto "><i class="fa-solid fa-list-check" data-toggle="tooltip"
            title="{{ __('Task List') }}"></i></a>
    </div>
@endpermission
