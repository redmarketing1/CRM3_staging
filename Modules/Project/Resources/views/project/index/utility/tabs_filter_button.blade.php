 <div class="nav nav-tabs project_tabs mb-4" id="status-tabs" role="tablist">
     <a class="nav-item nav-link active project_status_link" id="all-tab" data-toggle="tab" href="#all" role="tab"
         aria-controls="all" aria-selected="true">{{ __('All') }}</a>
     @if (isset($project_dropdown['project_status']) && !empty($project_dropdown['project_status']))
         @foreach ($project_dropdown['project_status'] as $status)
             @php
                 $project_count = '';
                 $project_count = $all_projects->where('status_data.name', $status->name)->count();
             @endphp
             <a class="nav-item nav-link project_status_link" id="status-tab-{{ $status->id }}"
                 data-status-id="{{ $status->id }}" data-toggle="tab" href="#status-{{ $status->id }}"
                 role="tab" aria-controls="status-{{ $status->id }}" aria-selected="false"
                 style="background-color:{{ isset($status->background_color) ? $status->background_color : '' }}; color:{{ isset($status->font_color) ? $status->font_color : '' }};">{{ __($status->name) }}
                 <span class="project_item_count"
                     style="background-color:{{ isset($status->background_color) ? $status->background_color : '' }}; color:{{ isset($status->font_color) ? $status->font_color : '' }};">{{ $project_count }}</span>
             </a>
         @endforeach
     @endif
 </div>
