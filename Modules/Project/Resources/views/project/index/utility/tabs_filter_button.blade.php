 <div class="nav nav-tabs project_tabs mb-4" id="status-tabs" role="tablist">
     <a class="nav-item nav-link active project_status_link" id="all-tab" data-toggle="tab" href="#all" role="tab"
         aria-controls="all" data-status-name="all" aria-selected="true">
         {{ __('All') }}
     </a>

     @foreach ($statusLists as $item)
         <a class="nav-item nav-link project_status_link" data-toggle="tab" role="tab"
             data-status-name="{{ $item->name }}" href="javascript:void()"
             style="background-color: {{ $item->background_color }};color: {{ $item->font_color }};">
             {{ trans($item->name) }}
             <span class="project_item_count">
                 {{ $item->total }}
             </span>
         </a>
     @endforeach
 </div>
