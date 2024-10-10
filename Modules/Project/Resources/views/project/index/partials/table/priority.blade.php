<span class="data-project-priority"
    style="background-color: {{ $project->priorityData->background_color ?? '#ffffff' }};color: {{ $project->priorityData->font_color ?? '#555555' }};">
    {!! $project->priorityData->name ?? '<span class="no-data">-</span>' !!}
</span>
