 <div class="card bg-primary project-detail-dashboard">
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
                     {{--
                        <div class="px-3">
                         <span class="text-white text-sm">{{ __('Total Members') }}:</span>
                         <h5 class="text-white text-nowrap">
                             {{ (int) $project->users->count() + (int) $project->clients->count() }}
                         </h5>
                     </div>
                     --}}
                 @endif
             </div>

             @include('project::project.show.widget.gantt_chart')
             @include('project::project.show.widget.task_board')
             @include('project::project.show.widget.bug_report')
             @include('project::project.show.widget.tracker_manage')

             @if (!$project->is_active)
                 <button class="btn btn-light d">
                     <a href="#" class="" title="{{ __('Locked') }}">
                         <i class="ti ti-lock"> </i></a>
                 </button>
             @else
                 <!-- <div class="d-flex align-items-center "> -->
                 @permission('project edit')
                     <div class="btn btn-light d-flex align-items-between">
                         <a href="javascript:void(0)" class="" data-size="xl"
                             data-url="{{ route('project.edit', [$project->id]) }}" data-ajax-popup="true"
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

                 <!-- </div> -->
             @endif


             @if ($project->is_archive)
                 <div class="col-md-auto col-sm-4">
                     <a href="javascript:void" class="btn btn-xs btn-primary btn-icon-only col-12 change-archive"
                         data-toggle="tooltip" data-bs-toggle="tooltip" data-id="{{ $project->id }}"
                         data-type="unarchive"
                         data-text="{{ __('The project will move to unrchive. You can revert it later') }}"
                         data-title="{{ __('Are you sure unrchive ?') }}"
                         data-bs-original-title="{{ __('Unrchive') }}">
                         <span class="btn-inner--text text-white">
                             <i class="ti ti-file-symlink"></i>
                         </span>
                     </a>
                 </div>
             @else
                 <div class="col-md-auto col-sm-4">
                     <a href="javascript:void" class="btn btn-xs btn-primary btn-icon-only col-12 change-archive"
                         data-toggle="tooltip" data-bs-toggle="tooltip" data-id="{{ $project->id }}"
                         data-type="archive"
                         data-text="{{ __('The project will move to archive. You can revert it later') }}"
                         data-title="{{ __('Are you sure archive ?') }}"
                         data-bs-original-title="{{ __('Move to archive') }}">
                         <span class="btn-inner--text text-white">
                             <i class="ti ti-files-off"></i>
                         </span>
                     </a>
                 </div>
             @endif


             @include('project::project.show.widget.project_share_settings')
             @include('project::project.show.widget.project_setting_dropdown')

         </div>
     </div>
 </div>
