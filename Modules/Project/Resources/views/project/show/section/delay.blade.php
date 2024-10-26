<div class="col-md-12" id="delay-card">
    <div id="useradd-15" class="delay-announcements">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ __('Project Delays Announcement') }}</h5>
                    @if (\Auth::user()->type == 'company' || \Auth::user()->type == 'employee' || \Auth::user()->type == 'subcontractor')
                        <div class="float-end">
                            <p class="text-muted d-none d-sm-flex align-items-center mb-0">
                                <a href="javascript:;" class="btn btn-sm btn-primary" data-ajax-popup="true"
                                    data-title="{{ __('Add Project Delay Announcement') }}"
                                    title="{{ __('Add Project Delay Announcement') }}" data-toggle="tooltip"
                                    data-size="lg" data-url="{{ route('project.delay.index', $project->id) }}">
                                    <i class="ti ti-plus"></i>
                                </a>
                            </p>
                        </div>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table" id="pc-dt-simple">
                        <thead>
                            <tr>
                                <th> {{ __('Date') }}</th>
                                <th> {{ __('Reason for delay') }}</th>
                                <th> {{ __('Delay in weeks') }}</th>
                                <th> {{ __('New Deadline') }}</th>
                                <th> {{ __('Internal Comment') }}</th>
                                <th> {{ __('Proof / Upload') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($project->delays as $delay)
                                <tr class="font-style">
                                    <td>{{ Auth::user()->dateFormat($delay->created_at) }}</td>
                                    <td>{{ $delay->reason }}</td>
                                    <td>{{ $delay->delay_in_weeks }}</td>
                                    <td>{{ Auth::user()->dateFormat($delay->new_deadline) }}</td>
                                    <td>{{ $delay->internal_comment }}</td>
                                    <td>
                                        @if ($delay->media)
                                            @foreach (json_decode($delay->media) as $image)
                                                <a class="lightbox-link" href="{{ get_file($image) }}"
                                                    data-lightbox="gallery"
                                                    data-title="image from {{ $project->title }}">
                                                    <img alt="image from {{ $project->title }}" width="40"
                                                        height="40" src="{{ get_file($image) }}"
                                                        class="img-thumbnail">
                                                </a>
                                            @endforeach
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
