@if (isset($project->statusData->name))
    <span class="data-project-status"
        style="background-color: {{ $project->statusData->background_color ?? '#ffffff' }};color: {{ $project->statusData->font_color ?? '#555555' }};">
        {{ $project->statusData->name }}
    </span>
@else
    @include('project::project.index.partials.table.empty')
@endif
