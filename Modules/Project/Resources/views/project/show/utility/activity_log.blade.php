<div class="col-md-12" id="activity-card" x-data="{
    activeFilters: new Set(['file', 'milestone', 'task', 'bug', 'move', 'invoice', 'user', 'share', 'time', 'comment', 'feedback', 'mail', 'status']),
    toggleFilter(type) {
        if (this.activeFilters.has(type)) {
            this.activeFilters.delete(type);
        } else {
            this.activeFilters.add(type);
        }
    },
    isActive(type) {
        return this.activeFilters.has(type);
    },
    getActivityType(logType) {
        return logType.toLowerCase().replace('create ', '').replace('upload ', '').replace(' ', '_');
    }
}">
    <div class="card deta-card table-card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <div class="titles">
                    <h5 class="mb-0">{{ __('Activity') }}</h5>
                </div>
                <div class="header-filter">
                    <div class="status-filter">
                        @php
                            $activityTypes = [
                                'Upload File' => ['type' => 'file', 'icon' => 'fa-file', 'label' => __('File')],
                                'Create Milestone' => [
                                    'type' => 'milestone',
                                    'icon' => 'fa-cubes',
                                    'label' => __('Milestone'),
                                ],
                                'Create Task' => ['type' => 'task', 'icon' => 'fa-tasks', 'label' => __('Task')],
                                'Create Bug' => ['type' => 'bug', 'icon' => 'fa-bug', 'label' => __('Bug')],
                                'Move' => ['type' => 'move', 'icon' => 'fa-align-justify', 'label' => __('Status')],
                                'Move Bug' => [
                                    'type' => 'move',
                                    'icon' => 'fa-align-justify',
                                    'label' => __('Bug status'),
                                ],
                                'Create Invoice' => [
                                    'type' => 'invoice',
                                    'icon' => 'fa-file-invoice',
                                    'label' => __('Invoice'),
                                ],
                                'Invite User' => ['type' => 'user', 'icon' => 'fa-plus', 'label' => __('User')],
                                'Share with Client' => [
                                    'type' => 'share',
                                    'icon' => 'fa-share',
                                    'label' => __('Shared'),
                                ],
                                'Create Timesheet' => ['type' => 'time', 'icon' => 'fa-clock-o', 'label' => __('Time')],
                                'Comment Create' => [
                                    'type' => 'comment',
                                    'icon' => 'fa-comments',
                                    'label' => __('Comment'),
                                ],
                                'Feedback Create' => [
                                    'type' => 'feedback',
                                    'icon' => 'fa-message',
                                    'label' => __('Mail'),
                                ],
                                'Status Changed' => [
                                    'type' => 'status',
                                    'icon' => 'fa-exchange-alt',
                                    'label' => __('Status Changed'),
                                ],
                            ];

                            $usedTypes = $project->activities->pluck('log_type')->unique();
                        @endphp

                        @foreach ($usedTypes as $logType)
                            @if (isset($activityTypes[$logType]))
                                @php $config = $activityTypes[$logType]; @endphp
                                <button @click="toggleFilter('{{ $config['type'] }}')"
                                    class="filter-btn status-{{ $config['type'] }}"
                                    :class="{ 'active': isActive('{{ $config['type'] }}') }">
                                    <i class="fas {{ $config['icon'] }}"></i>
                                    <span>{{ $config['label'] }}</span>
                                </button>
                            @endif
                        @endforeach
                    </div>
                </div>
                <div class="d-inline-flex">
                    <button class="text-muted bg-white d-sm-flex align-items-center py-0">
                        <a class="btn btn-sm btn-primary" data-size="lg" data-ajax-popup="true"
                            data-title="Add Client Feedback"
                            data-url="{{ route('project.feedback.index', [$project->id]) }}" data-toggle="tooltip"
                            title="Add Client Feedback" data-bs-original-title="Add Client Feedback" aria-label="popup">
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
            <div class="activity-grid">
                @foreach ($project->activities as $activity)
                    @php
                        $friendlyName = match ($activity->log_type) {
                            'Upload File' => __('File'),
                            'Create Milestone' => __('Milestone'),
                            'Create Task' => __('Task'),
                            'Create Bug' => __('Bug'),
                            'Move' => __('Item'),
                            'Move Bug' => __('Bug status'),
                            'Create Invoice' => __('Invoice'),
                            'Invite User' => __('User'),
                            'Share with Client' => __('Shared'),
                            'Create Timesheet' => __('Time'),
                            'Comment Create' => __('Comment'),
                            'Feedback Create' => __('Mail'),
                            'Status Changed' => __('Status'),
                            default => $activity->log_type,
                        };

                        $type = strtolower(str_replace(['Create ', ' ', 'Upload '], '', $activity->log_type));
                    @endphp
                    <div class="activity-item card" x-show="isActive('{{ $type }}')">
                        <div class="card-header d-flex align-items-center">
                            <span
                                class="timeline-step border 
                                @if ($activity->log_type == 'Upload File') status-file
                                @elseif($activity->log_type == 'Create Milestone')
                                    status-milestone
                                @elseif($activity->log_type == 'Create Task')
                                    status-task
                                @elseif($activity->log_type == 'Create Bug')
                                    status-bug
                                @elseif($activity->log_type == 'Move' || $activity->log_type == 'Move Bug')
                                    status-move
                                @elseif($activity->log_type == 'Create Invoice')
                                    status-invoice
                                @elseif($activity->log_type == 'Invite User')
                                    status-user
                                @elseif($activity->log_type == 'Share with Client')
                                    status-share
                                @elseif($activity->log_type == 'Create Timesheet')
                                    status-time
                                @elseif($activity->log_type == 'Comment Create')
                                    status-comment
                                @elseif($activity->log_type == 'Feedback Create')
                                    status-feedback
                                @elseif($activity->log_type == 'Status Changed')
                                    status-status @endif">
                                @if ($activity->log_type == 'Upload File')
                                    <i class="fas fa-file"></i>
                                @elseif($activity->log_type == 'Create Milestone')
                                    <i class="fas fa-cubes"></i>
                                @elseif($activity->log_type == 'Create Task')
                                    <i class="fas fa-tasks"></i>
                                @elseif($activity->log_type == 'Create Bug')
                                    <i class="fas fa-bug"></i>
                                @elseif($activity->log_type == 'Move' || $activity->log_type == 'Move Bug')
                                    <i class="fas fa-align-justify"></i>
                                @elseif($activity->log_type == 'Create Invoice')
                                    <i class="fas fa-file-invoice"></i>
                                @elseif($activity->log_type == 'Invite User')
                                    <i class="fas fa-plus"></i>
                                @elseif($activity->log_type == 'Share with Client')
                                    <i class="fas fa-share"></i>
                                @elseif($activity->log_type == 'Create Timesheet')
                                    <i class="fas fa-clock-o"></i>
                                @elseif($activity->log_type == 'Comment Create')
                                    <i class="fas fa-comments"></i>
                                @elseif($activity->log_type == 'Feedback Create')
                                    <i class="fas fa-message"></i>
                                @elseif($activity->log_type == 'Status Changed')
                                    <i class="fas fa-exchange-alt"></i>
                                @elseif($activity->log_type == 'Name Changed')
                                    <i class="fas fa-pen-to-square"></i>
                                @elseif($activity->log_type == 'End Date Changed')
                                    <i class="fas fa-th"></i>
                                @elseif($activity->log_type == 'Start Date Changed')
                                    <i class="fas fa-th"></i>
                                @endif
                            </span>
                            <span class="fw-bold">{{ $friendlyName }}</span>
                            <span>{{ $activity->created_at->diffForHumans() }}</span>
                        </div>
                        <div class="activity-desc mb-2 card-body">
                            {!! $activity->getRemark() !!}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
