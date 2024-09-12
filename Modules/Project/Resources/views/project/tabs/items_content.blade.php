<div class="tab-content" id="projectContent">

    <div class="tab-pane fade show active" id="allprojects" role="tabpanel">
        <ul class="tab-submenu">
            @foreach ($allProjects as $project)
                <li class="tab-item">
                    <a class="tab-link" href="{{ $project->url() }}">
                        <span style="background-color: {{ $project->backgroundColor }};">
                            {{ $project->shortName }}
                        </span>
                        {{ $project->name }}
                    </a>
                </li>
            @endforeach
        </ul>
    </div>

    @foreach ($groupedProjects as $statusName => $projects)
        @php
            $statusCssName = preg_replace('/[^a-zA-Z0-9_]/', '', strtolower(str_replace(' ', '_', $statusName)));
        @endphp
        <div class="tab-pane fade" id="{{ strtolower($statusCssName) }}" role="tabpanel">
            <ul class="tab-submenu">
                @foreach ($projects as $project)
                    <li class="tab-item">
                        <a class="tab-link" href="{{ $project->url() }}">
                            <span style="background-color: {{ $project->backgroundColor }};">
                                {{ $project->shortName }}
                            </span>
                            {{ $project->name }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    @endforeach
</div>
