 <div class="card">
     <div class="card-header ">
         <div class="d-flex justify-content-between align-items-center">
             <div>
                 <h5 class="mb-0">{{ __('Desciption') }}</h5>
             </div>
             <div class="float-end">
                 <p class="text-muted d-none d-sm-flex align-items-center mb-0">
                     <a href="javascript:void(0)" class="btn btn-sm btn-primary" data-size="w-80"
                         data-url="{{ route('project.edit', [$project->id]) }}" data-ajax-popup="true"
                         data-toggle="tooltip" title="{{ __('Edit Project') }}">
                         <i class="ti ti-edit"></i>
                     </a>
                 </p>
             </div>
         </div>
     </div>
     <div class="card-body">
         {!! $project->description !!}
     </div>
 </div>
