<div class="projectTabs">
    <ul class="nav dash-item-tabs" id="project" role="tablist">
        <li class="nav-item" role="presentation">
            <a class="nav-link" id="tab-allprojects" href="#allprojects" role="tab" style="background-color:#eee;"
                data-bs-toggle="tab" data-bs-placement="top" title="all project">
                <i class="fa-solid fa-list"></i>
            </a>
        </li>
        @foreach ($projectTabs as $project)
            @if (isset($project->statusData->name))
                @php

                    $statusCssName = preg_replace(
                        '/[^a-zA-Z0-9_]/',
                        '',
                        strtolower(str_replace(' ', '_', $project->statusData->name)),
                    );

                @endphp
                <li class="nav-item" role="presentation">
                    <a class="nav-link" id="tab-{{ $statusCssName }}" href="#{{ $statusCssName }}" role="tab"
                        data-bs-toggle="tab" data-bs-placement="top" title="{{ $project->statusData->name }}"
                        style="background-color: {{ $project->backgroundColor }}; color:{{ $project->statusData->font_color }}!important;">
                        {{ $project->shortName }} <span>{{ $project->ProjectCountByconstruction }}</span>
                    </a>
                </li>
            @endif
        @endforeach
    </ul>
</div>
