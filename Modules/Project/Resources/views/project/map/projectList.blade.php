<div class="projectList">

    <div class="tab-pane fade show active" id="allprojects" role="tabpanel">
        <ul class="tab-submenu">
            @foreach ($groupedProjectLists as $projects)
                @foreach ($projects as $project)
                    <li class="tab-item">
                        <a class="tab-link" id="{{ $project->id }}" data-lat="{{ $project->lat }}"
                            data-long="{{ $project->lng }}" href="{{ $project->url }}">
                            <span
                                style="background-color: {{ $project->backgrounColor }};color: {{ $project->fontColor }};">
                                {{ $project->shortName }}
                            </span>
                            {{ $project->name }}
                        </a>
                    </li>
                @endforeach
            @endforeach
        </ul>
    </div>

    @foreach ($groupedProjectLists as $statusName => $projects)
        @php
            $statusCssName = preg_replace('/[^a-zA-Z0-9_]/', '', strtolower(str_replace(' ', '_', $statusName)));
        @endphp
        <div class="tab-pane fade" id="{{ strtolower($statusCssName) }}" role="tabpanel">
            <ul class="tab-submenu">
                @foreach ($projects as $project)
                    <li class="tab-item">
                        <a class="tab-link" id="{{ $project->id }}" data-lat="{{ $project->lat }}"
                            data-long="{{ $project->lng }}" href="{{ $project->url }}">
                            <span
                                style="background-color: {{ $project->backgrounColor }};color: {{ $project->fontColor }}">
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
