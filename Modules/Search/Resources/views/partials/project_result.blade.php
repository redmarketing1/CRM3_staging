 <div class="search-result-item projects">
     <div class="card">
         <a href="{{ $project->url() }}">
             <div class="align-items-center d-flex grid-cols-2">
                 <div class="image">
                     <img src="{{ $project->thumbnailOrDefault }}" class="card-img">
                 </div>
                 <div class="content">
                     <div class="cardbody">
                         <h5 class="card-title">{{ $project->name }}</h5>
                         @if (isset($project->statusData->name))
                             <p class="data-project-status xlabel"
                                 style="background-color: {{ $project->statusData->background_color ?? '#ffffff' }};color: {{ $project->statusData->font_color ?? '#555555' }}">
                                 {{ $project->statusData->name }}
                             </p>
                         @endif

                         <div class="data-sub-name">
                             @if (isset($project->contactDetail->name))
                                 <div class="construction-client-name">
                                     <a href="{{ route('user', $project->contactDetail->id) }}" target="__blank"
                                         class="text-sm text-black">
                                         {{ $project->contactDetail->name }}
                                     </a>
                                 </div>
                             @endif
                             @if (isset($project->contactDetail->address_1))
                                 <div class="text-sm text-black">
                                     {{ $project->contactDetail->address_1 }}
                                 </div>
                             @endif
                             @if (isset($project->contactDetail->mobile))
                                 <div class="text-sm text-black">
                                     {{ $project->contactDetail->mobile }}
                                 </div>
                             @endif
                             @if (isset($project->contactDetail->phone))
                                 <div class="text-sm text-black">
                                     {{ $project->contactDetail->phone }}
                                 </div>
                             @endif
                         </div>

                     </div>
                 </div>
             </div>
         </a>
     </div>
 </div>
