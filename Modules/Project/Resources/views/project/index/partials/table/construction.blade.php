 <span class="data-project-construction"
     style="background-color: {{ $project->constructionData->background_color ?? '#ffffff' }};color: {{ $project->constructionData->font_color ?? '#555555' }};">
     {{ $project->constructionData->name ?? 'N/A' }}
 </span>

 @foreach ($project->property as $property)
     <span class="data-project-property"
         style="background-color: {{ $property->background_color ?? '#ffffff' }};color: {{ $property->font_color ?? '#555555' }};">
         {{ $property->name ?? 'N/A' }}
     </span>
 @endforeach
