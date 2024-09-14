 <div class="card bg-primary">
     <div class="card-body">
         <div class="d-block d-sm-flex align-items-center justify-content-between">
             <h4 class="text-white"> {{ $project->name }}</h4>
             <div class="d-flex  align-items-center row1">
                 @if ($project->type == 'project')
                     <div class="px-3">
                         <span class="text-white text-sm">{{ __('Start Date') }}:</span>
                         <h5 class="text-white text-nowrap">
                             {{ company_date_formate($project->start_date) }}
                         </h5>
                     </div>
                     <div class="px-3">
                         <span class="text-white text-sm">{{ __('Due Date') }}:</span>
                         <h5 class="text-white text-nowrap">
                             {{ company_date_formate($project->end_date) }}
                         </h5>
                     </div>
                     <div class="px-3">
                         <span class="text-white text-sm">{{ __('Total Members') }}:</span>
                         <h5 class="text-white text-nowrap">
                             {{ (int) $project->users->count() + (int) $project->clients->count() }}
                         </h5>
                     </div>
                 @endif
             </div>

             @if (!$project->is_active)
                 <button class="btn btn-light d">
                     <a href="#" class="" title="{{ __('Locked') }}">
                         <i class="ti ti-lock"> </i></a>
                 </button>
             @else
                 <div class="d-flex align-items-center ">
                     @permission('project edit')
                         <div class="btn btn-light d-flex align-items-between me-3">
                             <a href="javascript:void(0)" class="" data-size="lg"
                                 data-url="{{ route('projects.edit', [$project->id]) }}" data-ajax-popup="true"
                                 data-toggle="tooltip" title="{{ __('Edit Project') }}">
                                 <i class="ti ti-pencil"></i>
                             </a>
                         </div>
                     @endpermission
                     @permission('project delete')
                         {!! Form::open([
                             'method' => 'DELETE',
                             'route' => ['projects.destroy', $project->id],
                             'id' => 'delete-form-' . $project->id,
                         ]) !!}
                         <button class="btn btn-light d" type="button"><a href="#" data-toggle="tooltip"
                                 title="{{ __('Delete') }}" class="bs-pass-para show_confirm"><i class="ti ti-trash">
                                 </i></a></button>
                         {!! Form::close() !!}
                     @endpermission

                 </div>
             @endif
         </div>
     </div>
 </div>
