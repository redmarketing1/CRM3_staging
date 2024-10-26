@permission('bug manage')
    <div class="col-sm-auto">
        <a href="{{ route('projects.bug.report', [$project->id]) }}"
            class="btn btn-xs btn-primary btn-icon-only width-auto"><i class="fa-solid fa-bug" data-toggle="tooltip"
            title="{{ __('Bug Report') }}"></i></a>
    </div>
@endpermission
