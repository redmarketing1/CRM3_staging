@permission('project setting')
    @php
        $title =
            module_is_active('ProjectTemplate') && $project->type == 'template'
                ? __('Shared Project Template Settings')
                : __('Shared Project Settings');
    @endphp
    <div class="col-sm-auto">
        <a href="#" class="btn btn-xs btn-primary btn-icon-only col-12" data-title="{{ $title }}"
            data-ajax-popup="true" data-size="lg" data-bs-toggle="tooltip"
            data-bs-original-title="{{ __('Shared Project Setting') }}"
            data-url="{{ route('project.setting', [$project->id]) }}">
            <i class="ti ti-share"></i>
        </a>
    </div>
@endpermission
