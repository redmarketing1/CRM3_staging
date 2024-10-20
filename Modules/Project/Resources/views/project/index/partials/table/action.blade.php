<div class="actions">
    @permission('project edit')
        <div class="action-btn bg-warning ms-2">
            <a data-size="w-95" data-url="{{ route('project.quickView', [$project->id]) }}"
                class="project-edit btn btn-sm d-inline-flex align-items-center text-white " data-ajax-popup="true"
                data-bs-toggle="tooltip" data-projectId="{{ $project->id }}" data-title="{{ $project->name }}"
                title="{{ trans('Quick view') }}">
                <i class="ti ti-eye"></i>
            </a>
        </div>
    @endpermission

    @permission('project show')
        <div class="action-btn bg-primary ms-2">
            <a href="{{ $project->url() }}" data-bs-toggle="tooltip" title="View Project Details"
                data-title="View Project Details"
                class="project-show mx-3 btn btn-sm d-inline-flex align-items-center text-white ">
                <i class="ti ti-arrow-right"></i>
            </a>
        </div>
    @endpermission

    @permission('project delete')
        <div class="action-btn bg-primary ms-2">
            <button value="{{ $project->id }}" data-type="delete"
                data-text="{{ trans('This action can not be undone. Do you want to delete?') }}"
                data-title="{{ trans('Are you sure delete ?') }}" data-bs-toggle="tooltip"
                title="{{ trans('Delete Project') }}"
                class="project-delete mx-3 btn btn-sm d-inline-flex align-items-center text-white">
                <i class="ti ti-trash"></i>
            </button>
        </div>
    @endpermission

    @permission('project edit')
        <div class="action-btn bg-primary ms-2">
            <button value="{{ $project->id }}" data-type="archive"
                data-text="{{ trans('The project will move to archive. You can revert it later') }}"
                data-title="{{ trans('Are you sure archive ?') }}" data-bs-toggle="tooltip" title="{{ trans('Archive') }}"
                class="project-archive mx-3 btn btn-sm d-inline-flex align-items-center text-white">
                <i class="ti ti-file-symlink"></i>
            </button>
        </div>
    @endpermission

    @permission('project edit')
        <div class="action-btn bg-primary ms-2">
            <button value="{{ $project->id }}" data-type="duplicate"
                data-text="{{ trans('The project will be duplicate. You can delete it after created') }}"
                data-title="{{ trans('Are you sure duplicate this projects ?') }}" data-bs-toggle="tooltip"
                title="{{ trans('Duplicate') }}"
                class="project-duplicate mx-3 btn btn-sm d-inline-flex align-items-center text-white">
                <i class="ti ti-copy"></i>
            </button>
        </div>
    @endpermission
</div>
