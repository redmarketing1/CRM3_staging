@permission('task manage')
    <div class="col-sm-auto">
        <a href="{{ route('projects.task.board', [$project->id]) }}"
            class="btn btn-xs btn-primary btn-icon-only width-auto ">{{ __('Task Board') }}</a>
    </div>
@endpermission
