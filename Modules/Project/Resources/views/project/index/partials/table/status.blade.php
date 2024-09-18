<span class="data-project-status"
    style="background-color: {{ $project->status->background_color ?? '#c3c3c3' }};
           color: {{ $project->status->font_color ?? '#000' }};">
    {{ $project->status->name ?? 'N/A' }}
</span>
