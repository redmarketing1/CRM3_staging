@php
    use Carbon\Carbon;
@endphp
 <div class="card bg-primary project-detail-dashboard">
     <div class="card-body">
         <div class="d-block d-sm-flex align-items-center justify-content-between">
             <h4 class="text-white inline-edit" contenteditable="true" data-field="name"
                 data-message="{{ trans('Project title has been updated.') }}" data-toggle="tooltip"
                 title="{{ __('Edit Project Title') }}">
                 {{ $project->name }}
             </h4>
             <div class="d-flex  align-items-center row1">
                <div class="px-3">
                    <span class="text-white text-sm">{{ __('Created Date') }}:</span>
                    <input type="text" class="text-white text-nowrap inline-edit edit-dateField"
                        contenteditable="true" data-field="start_date"
                        data-message="{{ trans('Project start date has been updated.') }}" data-toggle="tooltip"
                        title="{{ $project->created_at }}" data-original-text="{{ $project->created_at }}"
                        value="{{ $project->created_at->diffForHumans() }}">
                </div>
                 <div class="px-3">
                     <span class="text-white text-sm">{{ __('Start Date') }}:</span>
                     <input type="text" class="text-white text-nowrap inline-edit edit-dateField"
                         contenteditable="true" data-field="start_date"
                         data-message="{{ trans('Project start date has been updated.') }}" data-toggle="tooltip"
                         title="{{ __('Edit Project Start Date') }}" data-original-text="{{ $project->start_date }}"
                         value="{{ $project->start_date }}">
                 </div>
                 <div class="px-3">
                     <span class="text-white text-sm">{{ __('Due Date') }}:</span>
                     <input type="text" class="text-white text-nowrap inline-edit edit-dateField"
                         contenteditable="true" data-field="end_date"
                         data-message="{{ trans('Project edning date has been updated.') }}" data-toggle="tooltip"
                         title="{{ __('Edit Project Ending Date') }}" data-original-text="{{ $project->end_date }}"
                         value="{{ $project->end_date }}">
                 </div>
                 {{-- <div class="px-3">
                         <span class="text-white text-sm">{{ __('Total Members') }}:</span>
                         <h5 class="text-white text-nowrap">
                             {{ (int) $project->users->count() + (int) $project->clients->count() }}
                         </h5>
                     </div> --}}
             </div>

             <div class="button-container">
                 @include('project::project.show.widget.gantt_chart')
                 @include('project::project.show.widget.task_board')
                 @include('project::project.show.widget.bug_report')
                 @include('project::project.show.widget.tracker_manage')

                 @if (!$project->is_active)
                     <button class="btn btn-light">
                         <a href="#" class="" title="{{ __('Locked') }}">
                             <i class="ti ti-lock"> </i></a>
                     </button>
                 @else
                     @permission('project edit')
                         <div class="btn btn-light">
                             <a href="javascript:void(0)" class="" data-size="w-80"
                                 data-url="{{ route('project.edit', [$project->id]) }}" data-ajax-popup="true"
                                 data-toggle="tooltip" title="{{ __('Edit Project') }}">
                                 <i class="ti ti-pencil"></i>
                             </a>
                         </div>
                     @endpermission
                     @permission('project delete')
                         <div class="">
                             {!! Form::open([
                                 'method' => 'DELETE',
                                 'route' => ['projects.destroy', $project->id],
                                 'id' => 'delete-form-' . $project->id,
                             ]) !!}
                             <button class="btn btn-light" type="button"><a href="#" data-toggle="tooltip"
                                     title="{{ __('Delete') }}" class="bs-pass-para show_confirm"><i class="ti ti-trash">
                                     </i></a></button>
                             {!! Form::close() !!}
                         </div>
                     @endpermission
                 @endif


                 @if ($project->is_archive)
                     <div class="">
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
                     <div class="">
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
 </div>
