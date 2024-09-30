<div class="actions">
    <div class="action-btn bg-primary ms-2">
        <a data-size="lg" data-url="{{ route('project.edit', [$project->id]) }}"
            class="btn btn-sm d-inline-flex align-items-center text-white " data-ajax-popup="true" data-bs-toggle="tooltip"
            data-title="{{ trans('Edit Project') }}" title="{{ trans('Edit Project') }}">
            <i class="ti ti-pencil"></i>
        </a>
    </div>
    <div class="action-btn bg-warning ms-2">
        <a href="{{ $project->url() }}" target="__blank" data-bs-toggle="tooltip" title="View Project Details"
            data-title="View Project Details" class="mx-3 btn btn-sm d-inline-flex align-items-center text-white ">
            <i class="ti ti-eye"></i>
        </a>
    </div>
</div>
