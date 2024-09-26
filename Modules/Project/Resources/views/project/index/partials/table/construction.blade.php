@foreach ($project->constructionData as $construction)
    <span class="data-project-construction"
        style="background-color: {{ $construction->background_color ?? '#ffffff' }};color: {{ $construction->font_color ?? '#555555' }};">
        {{ $construction->name ?? 'N/A' }}
    </span>
@endforeach

@foreach ($project->property as $property)
    <span class="data-project-property"
        style="background-color: {{ $property->background_color ?? '#ffffff' }};color: {{ $property->font_color ?? '#555555' }};">
        {{ $property->name ?? 'N/A' }}
    </span>
@endforeach
