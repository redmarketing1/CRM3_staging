<div class="col-md-12" id="activity-card">
    <div class="card deta-card table-card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <div class="titles">
                    <h5 class="mb-0">{{ __('Activity') }}</h5>
                </div>
                <div class="header-filter">
                    <div class="status-filter">
                        @php
                            $types = $project->activities
                                ->unique('LogTypes.type')
                                ->map(fn($activities) => $activities->LogTypes);
                        @endphp

                        @foreach ($types as $log)
                            <button class="filter-btn status-{{ $log['type'] }}">
                                <i class="fas {{ $log['icon'] }}"></i>
                                <span>{{ $log['label'] }}</span>
                            </button>
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
                    <div class="activity-item card {{ $activity->LogTypes['type'] }}">
                        <div class="card-header d-flex align-items-center">
                            <span class="timeline-step border {{ $activity->LogTypes['type'] }}">
                                <i class="{{ $activity->LogTypes['icon'] }}"></i>
                            </span>
                            <span class="fw-bold">{{ $activity->LogTypes['label'] }}</span>
                            <span class="time" title="{{ $activity->created_at }}">
                                {{ $activity->created_at->diffForHumans() }}
                            </span>
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
