 <div class="col-md-12">
     <div class="card deta-card table-card">
         <div class="card-header">
             <div class="d-flex justify-content-between align-items-center">
                 <div class="titles">
                     <h5 class="mb-0">{{ __('Activity') }}</h5>
                 </div>
                 <div class="d-inline-flex">
                     <button class="text-muted bg-white d-sm-flex align-items-center py-0">
                         <a class="btn btn-sm btn-primary" data-size="lg" data-ajax-popup="true"
                             data-title="Add Client Feedback"
                             data-url="{{ route('project.feedback.index', [$project->id]) }}" data-toggle="tooltip"
                             title="Add Client Feedback" data-bs-original-title="Add Client Feedback"
                             aria-label="popup">
                             <i class="ti ti-send"></i>
                         </a>
                     </button>
                     <button class="text-muted bg-white d-sm-flex align-items-center py-0">
                         <a class="btn btn-sm btn-primary" data-size="lg" data-ajax-popup="true"
                             data-title="Add Comments" data-url="{{ route('project.comment.index', [$project->id]) }}"
                             data-toggle="tooltip" title="Add Comments" data-bs-original-title="Add Comments"
                             aria-label="popup">
                             <i class="ti ti-messages"></i>
                         </a>
                     </button>
                 </div>
             </div>
         </div>
         <div class="card-body p-3">
             <table class="timeline timeline-one-side" data-timeline-content="axis" data-timeline-axis-style="dashed">
                 <thead>
                     <tr>
                         <th class="text-center">{{ __('Action') }}</th>
                         <th class="text-center" style="width: 16%;">{{ __('Type') }}</th>
                         <th>{{ __('Description') }}</th>
                         <th class="text-right">{{ __('Time') }}</th>
                     </tr>
                 </thead>
                 <tbody>
                     @foreach ($project->activities as $activity)
                         <tr class="timeline-block px-2 pt-3">
                             <td class="timeline-step-cell text-center" style="width: 20px;">
                                 @if ($activity->log_type == 'Upload File')
                                     <span class="timeline-step timeline-step-sm border text-white">
                                         <i class="fas fa-file text-dark"></i>
                                     </span>
                                 @elseif($activity->log_type == 'Create Milestone')
                                     <span class="timeline-step timeline-step-sm border text-white">
                                         <i class="fas fa-cubes text-dark"></i>
                                     </span>
                                 @elseif($activity->log_type == 'Create Task')
                                     <span class="timeline-step timeline-step-sm border text-white">
                                         <i class="fas fa-tasks text-dark"></i>
                                     </span>
                                 @elseif($activity->log_type == 'Create Bug')
                                     <span class="timeline-step timeline-step-sm border text-white">
                                         <i class="fas fa-bug text-dark"></i>
                                     </span>
                                 @elseif($activity->log_type == 'Move' || $activity->log_type == 'Move Bug')
                                     <span class="timeline-step timeline-step-sm border round text-white">
                                         <i class="fas fa-align-justify text-dark"></i>
                                     </span>
                                 @elseif($activity->log_type == 'Create Invoice')
                                     <span class="timeline-step timeline-step-sm border  text-white">
                                         <i class="fas fa-file-invoice text-dark"></i>
                                     </span>
                                 @elseif($activity->log_type == 'Invite User')
                                     <span class="timeline-step timeline-step-sm border  ">
                                         <i class="fas fa-plus text-dark"></i>
                                     </span>
                                 @elseif($activity->log_type == 'Share with Client')
                                     <span class="timeline-step timeline-step-sm border  text-white">
                                         <i class="fas fa-share text-dark"></i>
                                     </span>
                                 @elseif($activity->log_type == 'Create Timesheet')
                                     <span class="timeline-step timeline-step-sm border   text-white">
                                         <i class="fas fa-clock-o text-dark"></i>
                                     </span>
                                 @elseif($activity->log_type == 'Comment Create')
                                     <span class="border round text-white timeline-step">
                                         <i class="fas fa-comments text-dark"></i>
                                     </span>
                                 @elseif($activity->log_type == 'Feedback Create')
                                     <span class="border round text-white timeline-step">
                                         <i class="fas fa-message text-dark"></i>
                                     </span>
                                 @endif
                             </td>
                             <td class="col-1 m-0 text-nowrap text-center" style="width: 16%;">
                                 <span>{{ $activity->log_type }}</span>
                             </td>
                             <td class="col text-start activity-desc">
                                 {!! $activity->getRemark() !!}
                             </td>
                             <td class="text-end notification_time_main text-right">
                                 <p class="">{{ $activity->created_at->diffForHumans() }}</p>
                             </td>
                         </tr>
                     @endforeach
                 </tbody>
             </table>
         </div>
     </div>
 </div>
