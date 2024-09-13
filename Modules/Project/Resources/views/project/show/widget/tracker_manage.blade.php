@permission('project tracker manage')
    @if (module_is_active('TimeTracker'))
        <div class="col-sm-auto">
            <a href="{{ route('projecttime.tracker', [$project->id]) }}"
                class="btn btn-xs btn-primary btn-icon-only width-auto ">{{ __('Tracker') }}</a>
        </div>
    @endif
@endpermission
