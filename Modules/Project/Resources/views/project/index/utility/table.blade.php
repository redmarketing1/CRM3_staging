<table id="projectsTable">
    <thead>
        <tr>
            <th colspan="2" data-orderable="true">Project Name</th>
            <th data-orderable="true">Status</th>
            <th data-orderable="false">Comments</th>
            <th data-orderable="true">Priority</th>
            <th data-orderable="false">Construction</th>
            <th data-orderable="true">Project Net</th>
            <th data-orderable="true">Date</th>
            <th data-orderable="false">Action</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($projects as $project)
            <tr id="project-items" data-project-backgroundColor="{{ $project->status->background_color ?? '#c3c3c3' }}"
                data-project-fontColor="{{ $project->status->font_color ?? '#000' }}">
                <td>
                    <div class="data-thubmnail">
                        <img src="{{ asset('assets/images/default_thumbnail3.png') }}">
                    </div>
                </td>
                <td>
                    <div class="d-flex flex-column text-left">
                        <a href="{{ route('project.show', $project->id) }}" target="__blank">
                            <h2 class="data-name font-medium text-xl">{{ $project->name }}</h2>
                        </a>
                        {{-- <div class="d-flex data-sub-name flex-column font-normal">
                            <span class="construction-client-name">
                                <a href="#" class="text-sm text-black">
                                    Markus Hartwig
                                </a>
                            </span>
                            <span class="text-sm text-black">
                                Steinfurter Allee, 44
                            </span>
                        </div> --}}
                    </div>
                </td>
                <td>
                    <span class="data-project-status"
                        data-backgroundColor="{{ $project->status->background_color ?? '#c3c3c3' }}"
                        data-fontColor="{{ $project->status->font_color ?? '#000' }}">{{ $project->status->name ?? 'N/A' }}</span>
                </td>
                <td>{{ $project->comments ?? 'N/A' }}</td>
                <td data-fontColor="{{ $project->priority->background_color ?? '#c3c3c3' }}">
                    {{ $project->priority->name ?? 'N/A' }}
                </td>
                <td>Building A</td>
                <td>{{ currency_format_with_sym($project->budget) }}</td>
                <td>{{ company_datetime_formate($project->created_at) }}</td>
                <td>
                    <div class="actions">
                        <div class="action-btn bg-primary ms-2">
                            <a data-size="lg" data-url="{{ route('projects.edit', [$project->id]) }}"
                                class="btn btn-sm d-inline-flex align-items-center text-white " data-ajax-popup="true"
                                data-bs-toggle="tooltip" data-title="{{ trans('Edit Project') }}"
                                title="{{ trans('Edit Project') }}">
                                <i class="ti ti-pencil"></i>
                            </a>
                        </div>
                        <div class="action-btn bg-warning ms-2">
                            <a href="{{ route('project.show', $project->id) }}" target="__blank"
                                data-bs-toggle="tooltip" title="View Project Details" data-title="View Project Details"
                                class="mx-3 btn btn-sm d-inline-flex align-items-center text-white ">
                                <i class="ti ti-eye"></i>
                            </a>
                        </div>
                    </div>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
