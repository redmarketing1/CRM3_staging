<div class="project-tools">
    @permission('project manage')
        <a href="{{ route('projects.map') }}" class="btn btn-sm btn-primary mx-1" data-toggle="tooltip"
            title="{{ __('Project Map') }}">
            <i class="fa-solid fa-map"></i>
        </a>
    @endpermission
    @permission('project import')
        <a href="#" class="btn btn-sm btn-primary" data-ajax-popup="true" data-title="{{ __('Project Import') }}"
            data-url="{{ route('project.file.import') }}" data-toggle="tooltip" title="{{ __('Import') }}"><i
                class="ti ti-file-import"></i> </a>
    @endpermission
    <a href="{{ route('projects.grid') }}" class="btn btn-sm btn-primary"
        data-bs-toggle="tooltip"title="{{ __('Grid View') }}">
        <i class="ti ti-layout-grid text-white"></i>
    </a>

    @permission('project create')
        <a class="btn btn-sm btn-primary" data-ajax-popup="true" data-size="md" data-title="{{ __('Create New Project') }}"
            data-url="{{ route('projects.create') }}" data-toggle="tooltip" title="{{ __('Create') }}">
            <i class="ti ti-plus"></i>
        </a>
    @endpermission
</div>
