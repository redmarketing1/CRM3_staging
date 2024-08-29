@permission('bug manage')
    <div class="col-sm-auto">
        <a href="{{ route('projects.bug.report', [$project->id]) }}"
            class="btn btn-xs btn-primary btn-icon-only width-auto">{{ __('Bug Report') }}</a>
    </div>
@endpermission
