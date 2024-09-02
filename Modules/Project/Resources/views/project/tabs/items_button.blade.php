<ul class="nav dash-item-tabs" id="project" role="tablist">
    <li class="nav-item" role="presentation">
        <a class="nav-link" id="tab-allprojects" data-tabs-id="all" href="#allprojects" role="tab"
            style="background-color:#eee;" data-bs-toggle="tab" data-bs-placement="top" title="all project">
            <i class="fa-solid fa-list"></i>
        </a>
    </li>
    @foreach ($groupedProjects as $project)
        @if (isset($project->status_data->name))
            @php

                $statusCssName = preg_replace(
                    '/[^a-zA-Z0-9_]/',
                    '',
                    strtolower(str_replace(' ', '_', $project->status_data->name)),
                );

            @endphp
            <li class="nav-item" role="presentation">
                <a class="nav-link" id="tab-{{ $statusCssName }}" href="#{{ $statusCssName }}" role="tab"
                    data-bs-toggle="tab" data-tabs-id="{{ $project->status }}" data-bs-placement="top"
                    title="{{ $project->status_data->name }}"
                    style="background-color: {{ $project->backgroundColor }};">
                    {{ $project->shortName }} <span>{{ $project->projectCount }}</span>
                </a>
            </li>
        @endif
    @endforeach
</ul>
