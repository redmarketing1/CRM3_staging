{{-- 
 
 𝗧𝗛𝗜𝗦 𝗜𝗦 𝗗𝗲𝗽𝗿𝗲𝗰𝗮𝘁𝗲𝗱 𝘃𝗲𝗿𝘀𝗶𝗼𝗻. 
 𝘁𝗵𝗶𝘀 𝗼𝗻𝗲 𝗺𝗼𝘃𝗲𝗱 𝘁𝗼 
    ❞𝗠𝗼𝗱𝘂𝗹𝗲𝘀/𝗣𝗿𝗼𝗷𝗲𝗰𝘁/𝗥𝗲𝘀𝗼𝘂𝗿𝗰𝗲𝘀/𝘃𝗶𝗲𝘄𝘀/𝗽𝗿𝗼𝗷𝗲𝗰𝘁/𝘀𝗵𝗼𝘄.𝗯𝗹𝗮𝗱𝗲.𝗽𝗵𝗽❞

--}}

@extends('layouts.main')
@php
    if ($project->type == 'project') {
        $name = 'Project';
    } else {
        $name = 'Project Template';
    }
@endphp
@section('page-title')
    {{ __($name . ' Detail') }}
@endsection
@push('css')
    <link rel="stylesheet" href="{{ asset('assets/css/plugins/dropzone.css') }}" type="text/css" />
    {{-- <link rel="stylesheet" href="{{ asset('Modules/Taskly/Resources/assets/libs/select2/dist/css/select2.min.css') }}" type="text/css" /> --}}
    <link href="{{ asset('assets/css/plugins/select2.min.css') }}" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('assets/css/plugins/datatable/dataTables.dataTables.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
@endpush
@section('page-breadcrumb')
    {{ __($name . ' Detail') }}
@endsection

@section('page-action')
    @if ($project->type == 'project')
        @stack('addButtonHook')
    @else
        @stack('projectConvertButton')
    @endif
    <div class="col-md-auto col-sm-4 pb-3">
        <a href="#" class="btn btn-xs btn-primary btn-icon-only col-12 cp_link"
            data-link="{{ route('project.shared.link', [\Illuminate\Support\Facades\Crypt::encrypt($project->id)]) }}"
            data-toggle="tooltip" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Copy') }}">
            <span class="btn-inner--text text-white">
                <i class="ti ti-copy"></i></span>
        </a>
    </div>
    @permission('project setting')
        @php
            $title =
                module_is_active('ProjectTemplate') && $project->type == 'template'
                    ? __('Shared Project Template Settings')
                    : __('Shared Project Settings');
        @endphp
        <div class="col-sm-auto">
            <a href="#" class="btn btn-xs btn-primary btn-icon-only col-12" data-title="{{ $title }}"
                data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip"
                data-bs-original-title="{{ __('Shared Project Setting') }}"
                data-url="{{ route('project.setting', [$project->id]) }}">
                <i class="ti ti-settings"></i>
            </a>
        </div>
    @endpermission
    <div class="col-sm-auto">
        <a href="{{ route('projects.gantt', [$project->id]) }}"
            class="btn btn-xs btn-primary btn-icon-only width-auto ">{{ __('Gantt Chart') }}</a>
    </div>
    @permission('task manage')
        <div class="col-sm-auto">
            <a href="{{ route('projects.task.board', [$project->id]) }}"
                class="btn btn-xs btn-primary btn-icon-only width-auto ">{{ __('Task Board') }}</a>
        </div>
    @endpermission
    @permission('bug manage')
        <div class="col-sm-auto">
            <a href="{{ route('projects.bug.report', [$project->id]) }}"
                class="btn btn-xs btn-primary btn-icon-only width-auto">{{ __('Bug Report') }}</a>
        </div>
    @endpermission
    @permission('project tracker manage')
        @if (module_is_active('TimeTracker'))
            <div class="col-sm-auto">
                <a href="{{ route('projecttime.tracker', [$project->id]) }}"
                    class="btn btn-xs btn-primary btn-icon-only width-auto ">{{ __('Tracker') }}</a>
            </div>
        @endif
    @endpermission
    @permission('project setting')
        @if ($projectStatus)
            <div class="col-sm-auto btn-group">
                <button class="btn btn-xs btn-primary text-white btn-icon-only width-auto dropdown-toggle rounded-pill"
                    type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    @php
                        $selected_project_status = isset($project->status_data->name)
                            ? $project->status_data->name
                            : '';
                    @endphp
                    {{ $selected_project_status }}
                </button>
                <div class="dropdown-menu">
                    @foreach ($projectStatus as $k => $status)
                        @if ($status->id == env('PROJECT_STATUS_CLIENT'))
                            <a href="javascript:void(0)" data-ajax-popup="true" data-toggle="tooltip" data-size="md"
                                data-url="{{ route('projects.edit_form', [$project->id, 'project_status']) }}"
                                data-bs-toggle="modal" data-bs-target="#exampleModal"
                                data-bs-whatever="{{ __('Select Final Estimation') }}"
                                data-title="{{ __('Select Final Estimation') }}" class="dropdown-item"
                                data-bs-toggle="tooltip">{{ $status->name }}</a>
                        @else
                            <a class="dropdown-item status" data-id="{{ $status->id }}"
                                data-url="{{ route('project.status', $project->id) }}" href="#">{{ $status->name }}</a>
                        @endif
                    @endforeach
                </div>
            </div>
        @endif
    @endpermission
@endsection
@push('css')
    <link rel="stylesheet" href="{{ asset('assets/css/plugins/dropzone.min.css') }}">
    <style>
        @media (max-width: 1300px) {
            .row1 {
                display: flex;
                flex-wrap: wrap;
            }
        }

        .table-responsive {
            overflow-x: visible !important;
        }

        #progress-table tr.group,
        #progress-table tr.group:hover {
            background-color: rgba(0, 0, 0, 0.1) !important;
        }

        #card2 {
            right: -61px;
            width: 48%;
        }

        .dropdown-toggle {
            height: max-content;
        }
    </style>
@endpush
@section('content')
    @php
        $display_other_tabs = false;

        if (\Auth::user()->hasRole('company') && $project->status == env('PROJECT_STATUS_CLIENT')) {
            $display_other_tabs = true;
        }
        if ($project->type == 'project') {
            $name = 'Project';
        } else {
            $name = 'Project Template';
        }
    @endphp
    <div class="row">
        <!-- [ sample-page ] start -->
        <div class="col-sm-12">
            <div class="row">
                <div class="col-xxl-12">
                    <div class="row">
                        <div class="col-xxl-8">
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
                                                        <a href="#" class="" data-size="lg"
                                                            data-url="{{ route('projects.edit', [$project->id]) }}"
                                                            data-="" data-ajax-popup="true"
                                                            data-title="{{ __('Edit ') . $name }}" data-toggle="tooltip"
                                                            title="{{ __('Edit') }}">
                                                            <i class="ti ti-pencil"> </i>
                                                        </a>
                                                    </div>
                                                @endpermission
                                                @permission('project delete')
                                                    {!! Form::open([
                                                        'method' => 'DELETE',
                                                        'route' => ['projects.destroy', $project->id],
                                                        'id' => 'delete-form-' . $project->id,
                                                    ]) !!}
                                                    <button class="btn btn-light d" type="button"><a href="#"
                                                            data-toggle="tooltip" title="{{ __('Delete') }}"
                                                            class="bs-pass-para show_confirm"><i class="ti ti-trash">
                                                            </i></a></button>
                                                    {!! Form::close() !!}
                                                @endpermission

                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                @if ($project->type == 'project')
                                    <div class="col-lg-3 col-6">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="theme-avtar bg-primary">
                                                        <i class="fas fas fa-calendar-day"></i>
                                                    </div>
                                                    <div class="col text-end">
                                                        <h6 class="text-muted">{{ __('Days left') }}</h6>
                                                        <span class="h6 font-weight-bold mb-0 ">{{ $daysleft }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-6">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="theme-avtar bg-info">
                                                        <i class="fas fa-money-bill-alt"></i>
                                                    </div>
                                                    <div class="col text-end">
                                                        <h6 class="text-muted">{{ __('Budget') }}</h6>
                                                        <span
                                                            class="h6 font-weight-bold mb-0 ">{{ company_setting('defult_currancy') }}
                                                            {{ number_format($project->budget) }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                @php
                                    $class = $project->type == 'template' ? 'col-lg-6 col-6' : 'col-lg-3 col-6';
                                @endphp
                                <div class="{{ $class }}">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="theme-avtar bg-danger">
                                                    <i class="fas fa-check-double"></i>
                                                </div>
                                                <div class="col text-end">
                                                    <h6 class="text-muted">{{ __('Total Task') }}</h6>
                                                    <span
                                                        class="h6 font-weight-bold mb-0 ">{{ $project->countTask() }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="{{ $class }}">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="theme-avtar bg-success">
                                                    <i class="fas fa-comments"></i>
                                                </div>
                                                <div class="col text-end">
                                                    <h6 class="text-muted">{{ __('Comment') }}</h6>
                                                    <span
                                                        class="h6 font-weight-bold mb-0 ">{{ $project->countTaskComments() }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <div class="col-xxl-4">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="card" style="height: 239px">
                                        <div class="card-header">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h5 class="mb-0">{{ __('Progress') }}<span class="text-end">
                                                            ({{ __('Last Week Tasks') }}) </span></h5>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body p-2">
                                            <div id="task-chart"></div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @if ($project->type == 'project')
                    <div class="col-xxl-12 card-container">

                        <div class="card deta-card">
                            <div class="card-header">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="mb-0">{{ __('Team Members') }}
                                            ({{ count($project->users) }})
                                        </h5>
                                    </div>
                                    <div class="float-end">
                                        <p class="text-muted d-sm-flex align-items-center mb-0">

                                            <a href="#" class="btn btn-sm btn-primary" data-ajax-popup="true"
                                                data-title="{{ __('Invite') }}" data-bs-toggle="tooltip"
                                                data-bs-title="{{ __('Invite') }}"
                                                data-url="{{ route('projects.invite.popup', [$project->id]) }}"><i
                                                    class="ti ti-brand-telegram"></i></a>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body top-10-scroll">
                                @foreach ($project->users as $user)
                                    <ul class="list-group list-group-flush" style="width: 100%;">
                                        <li class="list-group-item px-0">
                                            <div class="row align-items-center justify-content-between">
                                                <div class="col-sm-auto mb-3 mb-sm-0">
                                                    <div class="d-flex align-items-center px-2">
                                                        <a href="#" class=" text-start">
                                                            <img alt="image" data-bs-toggle="tooltip"
                                                                data-bs-placement="top" title="{{ $user->name }}"
                                                                @if ($user->avatar) src="{{ get_file($user->avatar) }}" @else src="{{ get_file('avatar.png') }}" @endif
                                                                class="rounded-circle " width="40px" height="40px">
                                                        </a>
                                                        <div class="px-2">
                                                            <h5 class="m-0">{{ $user->name }}</h5>
                                                            <small class="text-muted">{{ $user->email }}<span
                                                                    class="text-primary "> -
                                                                    {{ (int) count($project->user_done_tasks($user->id)) }}/{{ (int) count($project->user_tasks($user->id)) }}</span></small>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-sm-auto text-sm-end d-flex align-items-center">
                                                    @auth('web')
                                                        @if ($user->id != Auth::id())
                                                            @permission('team member remove')
                                                                <form id="delete-user-{{ $user->id }}"
                                                                    action="{{ route('projects.user.delete', [$project->id, $user->id]) }}"
                                                                    method="POST" style="display: none;" class="d-inline-flex">
                                                                    <a href="#"
                                                                        class="action-btn btn-danger mx-1  btn btn-sm d-inline-flex align-items-center bs-pass-para show_confirm"
                                                                        data-confirm="{{ __('Are You Sure?') }}"
                                                                        data-text="{{ __('This action can not be undone. Do you want to continue?') }}"
                                                                        data-confirm-yes="delete-user-{{ $user->id }}"
                                                                        data-toggle="tooltip" title="{{ __('Delete') }}"><i
                                                                            class="ti ti-trash"></i></a>

                                                                    @csrf
                                                                    @method('DELETE')
                                                                </form>
                                                            @endpermission
                                                        @endif
                                                    @endauth
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                @endforeach
                            </div>
                        </div>

                        <div class="card deta-card">
                            <div class="card-header ">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="mb-0">{{ __('Clients') }} ({{ count($project->clients) }})
                                        </h5>
                                    </div>
                                    <div class="float-end">
                                        <p class="text-muted d-none d-sm-flex align-items-center mb-0">
                                            <a href="#" class="btn btn-sm btn-primary" data-ajax-popup="true"
                                                data-title="{{ __('Share to Client') }}" data-toggle="tooltip"
                                                title="{{ __('Share to Client') }}"
                                                data-url="{{ route('projects.share.popup', [$project->id]) }}"><i
                                                    class="ti ti-share"></i></a>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body top-10-scroll">
                                @foreach ($project->clients as $client)
                                    <ul class="list-group list-group-flush" style="width: 100%;">
                                        <li class="list-group-item px-0">
                                            <div class="row align-items-center justify-content-between">
                                                <div class="col-sm-auto mb-3 mb-sm-0">
                                                    <div class="d-flex align-items-center px-2">
                                                        <a href="#" class=" text-start">
                                                            <img alt="image" data-bs-toggle="tooltip"
                                                                data-bs-placement="top" title="{{ $client->name }}"
                                                                @if ($client->avatar) src="{{ get_file($client->avatar) }}" @else src="{{ get_file('avatar.png') }}" @endif
                                                                class="rounded-circle " width="40px" height="40px">
                                                        </a>
                                                        <div class="px-2">
                                                            <h5 class="m-0">{{ $client->name }}</h5>
                                                            <small class="text-muted">{{ $client->email }}</small>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-sm-auto text-sm-end d-flex align-items-center">
                                                    @if (\Auth::user()->hasRole('company'))
                                                        @permission('team client remove')
                                                            <form id="delete-client-{{ $client->id }}"
                                                                action="{{ route('projects.client.delete', [$project->id, $client->id]) }}"
                                                                method="POST" style="display: none;" class="d-inline-flex">
                                                                <a href="#"
                                                                    class="action-btn btn-danger mx-1  btn btn-sm d-inline-flex align-items-center bs-pass-para show_confirm"
                                                                    data-confirm="{{ __('Are You Sure?') }}"
                                                                    data-text="{{ __('This action can not be undone. Do you want to continue?') }}"
                                                                    data-confirm-yes="delete-client-{{ $client->id }}"
                                                                    data-toggle="tooltip" title="{{ __('Delete') }}"><i
                                                                        class="ti ti-trash"></i></a>
                                                                @csrf
                                                                @method('DELETE')

                                                            </form>
                                                        @endpermission
                                                    @endif
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                @endforeach
                            </div>
                        </div>


                        <div class="card">
                            <div class="card-header ">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="mb-0">{{ __('Appointments') }}
                                        </h5>
                                    </div>
                                    @if (\Auth::user()->hasRole('company'))
                                        <div class="float-end">
                                            <p class="text-muted d-sm-flex align-items-center mb-0">
                                                <a class="btn btn-sm btn-primary" data-size="lg" data-ajax-popup="true"
                                                    data-title="{{ __('Create') }}" data-url=""
                                                    data-toggle="tooltip" title="{{ __('Create') }}"><i
                                                        class="ti ti-plus"></i></a>
                                            </p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="card-body">

                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header ">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="mb-0">{{ __('Project Labels') }}
                                        </h5>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row mt-2">
                                    <div class="col-md-6 form-group">
                                        <label for="construction_type" class="form-label">Construction Type</label>
                                        <div class="selct2-custom">
                                            <select name="construction_type" class="form-control filter_select2"
                                                onchange="store_to_project_data('construction_type',this)" multiple>
                                                <option value="" disabled>Select</option>
                                                @if (isset($construction_types) && count($construction_types) > 0)
                                                    @foreach ($construction_types as $construction_type)
                                                        @php
                                                            $selected_construction_type = '';
                                                            if (isset($project->construction_type)) {
                                                                $selected_construction_types = explode(
                                                                    ',',
                                                                    $project->construction_type,
                                                                );
                                                                if (
                                                                    count($selected_construction_types) > 0 &&
                                                                    in_array(
                                                                        $construction_type->id,
                                                                        $selected_construction_types,
                                                                    )
                                                                ) {
                                                                    $selected_construction_type = 'selected';
                                                                }
                                                            }
                                                        @endphp
                                                        <option value="{{ $construction_type->id }}"
                                                            data-background_color="{{ $construction_type->background_color }}"
                                                            data-font_color="{{ $construction_type->font_color ? $construction_type->font_color : '#fff' }}"
                                                            {{ $selected_construction_type }}>
                                                            {{ $construction_type->name }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label for="property" class="form-label">Property</label>
                                        <div class="selct2-custom">
                                            <select name="property" class="form-control filter_select2"
                                                onchange="store_to_project_data('property_type',this)" multiple>
                                                <option value="" disabled>Select</option>
                                                @if (isset($properties) && count($properties) > 0)
                                                    @foreach ($properties as $property)
                                                        @php
                                                            $selected_property_type = '';
                                                            if (isset($project->property_type)) {
                                                                $selected_property_types = explode(
                                                                    ',',
                                                                    $project->property_type,
                                                                );
                                                                if (
                                                                    count($selected_property_types) > 0 &&
                                                                    in_array($property->id, $selected_property_types)
                                                                ) {
                                                                    $selected_property_type = 'selected';
                                                                }
                                                            }
                                                        @endphp
                                                        <option value="{{ $property->id }}"
                                                            data-background_color="{{ $property->background_color }}"
                                                            data-font_color="{{ $property->font_color ? $property->font_color : '#fff' }}"
                                                            {{ $selected_property_type }}>{{ $property->name }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <label for="status_label" class="form-label">Project Label</label>
                                        <select name="status_label" class="form-control filter_select2"
                                            onchange="store_to_project_data('label',this)" multiple>
                                            <option value="">Select</option>
                                            @if (isset($status_labels) && count($status_labels) > 0)
                                                @foreach ($status_labels as $status_label)
                                                    @php
                                                        $selected_status_label = '';
                                                        if (isset($project->label)) {
                                                            $selected_status_array = explode(',', $project->label);
                                                            if (
                                                                count($selected_status_array) > 0 &&
                                                                in_array($status_label->id, $selected_status_array)
                                                            ) {
                                                                $selected_status_label = 'selected';
                                                            }
                                                        }
                                                    @endphp
                                                    <option value="{{ $status_label->id }}"
                                                        data-background_color="{{ $status_label->background_color }}"
                                                        data-font_color="{{ $status_label->font_color ? $status_label->font_color : '#fff' }}"
                                                        {{ $selected_status_label }}>{{ $status_label->name }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label for="priority" class="form-label">{{ __('Priority') }}</label>
                                        <select name="priority" id="priority" class="form-control filter_select2"
                                            onchange="store_to_project_data('priority',this)" multiple>
                                            <option value="">Select</option>
                                            @php
                                                //	$project->priority = ($project->priority > 0) ? $project->priority : env('DEFAULT_PRIORITY');
                                            @endphp
                                            @if (isset($priorities) && count($priorities) > 0)
                                                @foreach ($priorities as $priority)
                                                    @php
                                                        //	$selected_priority = (isset($project->priority) && $priority->id == $project->priority) ? 'selected' : '';
                                                        $selected_priority = '';
                                                        if (isset($project->priority)) {
                                                            $selected_status_array = explode(',', $project->priority);
                                                            if (
                                                                count($selected_status_array) > 0 &&
                                                                in_array($priority->id, $selected_status_array)
                                                            ) {
                                                                $selected_priority = 'selected';
                                                            }
                                                        }
                                                    @endphp
                                                    <option value="{{ $priority->id }}"
                                                        data-background_color="{{ $priority->background_color }}"
                                                        data-font_color="{{ $priority->font_color ? $priority->font_color : '#fff' }}"
                                                        {{ $selected_priority }}>{{ $priority->name }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="card">
                            <div class="card-header ">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="mb-0">{{ __('Contact') }}
                                        </h5>
                                    </div>
                                    @if (\Auth::user()->hasRole('company'))
                                        <div class="float-end">
                                            <p class="text-muted d-none d-sm-flex align-items-center mb-0">
                                                <a href="#" class="btn btn-sm btn-primary" data-ajax-popup="true"
                                                    data-title="{{ __('Contact Details') }}" data-toggle="tooltip"
                                                    title="{{ __('Contact Details') }}" data-size="lg"
                                                    data-url="{{ route('projects.edit_form', [$project->id, 'ConstructionDetails']) }}"><i
                                                        class="ti ti-edit"></i></a>
                                            </p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="card-body top-10-scroll project_all_address">

                            </div>
                        </div>

                    </div>
                @endif


                <div class="card-container cc1" id="milestones-card">
                    <div class="card milestone-card table-card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-0">{{ __('Milestones') }} ({{ count($project->milestones) }})</h5>
                                </div>
                                <div class="float-end">
                                    @permission('milestone create')
                                        <p class="text-muted d-sm-flex align-items-center mb-0">
                                            <a class="btn btn-sm btn-primary" data-size="lg" data-ajax-popup="true"
                                                data-title="{{ __('Create Milestone') }}"
                                                data-url="{{ route('projects.milestone', [$project->id]) }}"
                                                data-toggle="tooltip" title="{{ __('Create Milestone') }}"><i
                                                    class="ti ti-plus"></i></a>
                                        </p>
                                    @endpermission
                                </div>
                            </div>
                        </div>
                        <div class="card-body">

                            <div class="table-responsive">
                                <table id="" class="table table-bordered px-2">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Name') }}</th>
                                            <th>{{ __('Status') }}</th>
                                            <th>{{ __('Start Date') }}</th>
                                            <th>{{ __('End Date') }}</th>
                                            <th>{{ __('Cost') }}</th>
                                            <th>{{ __('Progress') }}</th>
                                            <th>{{ __('Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($project->milestones as $key => $milestone)
                                            <tr>
                                                <td>
                                                    <a href="#" class="d-block font-weight-500 mb-0"
                                                        @permission('milestone delete') data-ajax-popup="true" data-title="{{ __('Milestone Details') }}"  data-url="{{ route('projects.milestone.show', [$milestone->id]) }}" @endpermission>
                                                        <h5 class="m-0"> {{ $milestone->title }} </h5>
                                                    </a>
                                                </td>
                                                <td>

                                                    @if ($milestone->status == 'complete')
                                                        <label
                                                            class="badge bg-success p-2 px-3 rounded">{{ __('Complete') }}</label>
                                                    @else
                                                        <label
                                                            class="badge bg-warning p-2 px-3 rounded">{{ __('Incomplete') }}</label>
                                                    @endif
                                                </td>
                                                <td>{{ $milestone->start_date }}</td>
                                                <td>{{ $milestone->end_date }}</td>
                                                <td>{{ company_setting('defult_currancy') }}{{ $milestone->cost }}
                                                </td>
                                                <td>
                                                    <div class="progress_wrapper">
                                                        <div class="progress">
                                                            <div class="progress-bar" role="progressbar"
                                                                style="width: {{ $milestone->progress }}px;"
                                                                aria-valuenow="55" aria-valuemin="0" aria-valuemax="100">
                                                            </div>
                                                        </div>
                                                        <div class="progress_labels">
                                                            <div class="total_progress">

                                                                <strong> {{ $milestone->progress }}%</strong>
                                                            </div>

                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="col-auto">
                                                    @permission('milestone edit')
                                                        <div class="action-btn btn-primary ms-2">
                                                            <a class="action-btn btn-info mx-1  btn btn-sm d-inline-flex align-items-center"
                                                                data-ajax-popup="true" data-size="lg"
                                                                data-title="{{ __('Edit Milestone') }}"
                                                                data-url="{{ route('projects.milestone.edit', [$milestone->id]) }}"
                                                                data-toggle="tooltip" title="{{ __('Edit') }}"><i
                                                                    class="ti ti-pencil text-white"></i></a>
                                                        </div>
                                                    @endpermission
                                                    @permission('milestone delete')
                                                        <form id="delete-form1-{{ $milestone->id }}"
                                                            action="{{ route('projects.milestone.destroy', [$milestone->id]) }}"
                                                            method="POST" style="display: none;" class="d-inline-flex">
                                                            <a href="#"
                                                                class="action-btn btn-danger mx-1  btn btn-sm d-inline-flex align-items-center bs-pass-para show_confirm"
                                                                data-confirm="{{ __('Are You Sure?') }}"
                                                                data-text="{{ __('This action can not be undone. Do you want to continue?') }}"
                                                                data-confirm-yes="delete-form1-{{ $milestone->id }}"
                                                                data-toggle="tooltip" title="{{ __('Delete') }}"><i
                                                                    class="ti ti-trash"></i></a>
                                                            @csrf
                                                            @method('DELETE')

                                                        </form>
                                                    @endpermission

                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>


                    <div class="card files-card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-0">{{ __('Files') }}</h5>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-4">

                            <!-- <div class="author-box-name form-control-label mb-4">
                                                                                                                                                                    </div> -->
                            <div class="col-md-12 dropzone browse-file" id="dropzonewidget">
                                <div class="dz-message" data-dz-message>
                                    <span>{{ __('Drop files here to upload') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @permission('estimation manage')
                    <div class="col-md-12">
                        <div class="card estimation-card table-card">
                            <div class="card-header">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="mb-0">{{ __('Estimations') }}</h5>
                                    </div>
                                    <div class="float-end">
                                        <div class="d-flex">
                                            @permission('estimation delete')
                                                {!! Form::open([
                                                    'method' => 'POST',
                                                    'route' => ['estimations.bulk_delete'],
                                                    'class' => 'delete_estimation_form d-none',
                                                ]) !!}
                                                <input type="hidden" value="" name="remove_estimation_ids"
                                                    id="remove_estimation_ids">
                                                <button type="button"
                                                    class="btn btn-sm btn-primary btn-icon m-1 btn_bulk_delete_estimations show_error_toaster"
                                                    data-bs-whatever="{{ __('Create New Estimation') }}">
                                                    <i class="ti ti-trash text-white" data-bs-toggle="tooltip"
                                                        data-bs-original-title="{{ __('Delete Estimations') }}"></i>
                                                    {{ __('Delete Estimations') }}
                                                </button>
                                                {{ Form::close() }}
                                            @endpermission
                                            @permission('estimation create')
                                                <p class="text-muted d-sm-flex align-items-center mb-0">
                                                    <a href="{{ route('estimations.create.page', $project->id) }}"
                                                        class="btn btn-sm btn-primary" data-size="lg"
                                                        data-bs-whatever="{{ __('Create New Estimation') }}">
                                                        <i class="ti ti-plus"></i>
                                                    </a>
                                                </p>
                                            @endpermission
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body p-3 top-10-scroll">
                                <div class="table-responsive">
                                    @if (\Auth::user()->type != 'company')
                                        <table class="table table-bordered px-2" id="{{-- estimation-table --}}">
                                            <thead>
                                                <th>{{ __('Status') }}</th>
                                                <th>{{ __('Title') }}</th>
                                                <th>{{ __('Issue Date') }}</th>
                                                <th>{{ __('Action') }}</th>
                                            </thead>
                                            <tbody>
                                                @foreach ($project_estimations as $estimation)
                                                    <tr>
                                                        @php
                                                            $setup_url = route(
                                                                'estimations.setup.estimate',
                                                                \Crypt::encrypt($estimation->id),
                                                            );
                                                            if (!\Auth::user()->isAbleTo('estimation edit')) {
                                                                $setup_url = 'javascript:void(0)';
                                                            }
                                                        @endphp
                                                        <td>
                                                            <span
                                                                class="badge fix_badges bg-{{ $statuesColor[$estimationStatus[$estimation->status]] }} p-2 px-3 rounded">{{ $estimationStatus[$estimation->status] }}</span>
                                                        </td>
                                                        <td style="font-weight:600;">
                                                            @if ($estimation->status > 1)
                                                                <a
                                                                    href="{{ route('estimations.finalize.estimate', encrypt($estimation->id)) }}">
                                                                    {{ $estimation->title }}
                                                                </a>
                                                            @else
                                                                <a href="{{ $setup_url }}">
                                                                    {{ $estimation->title }}
                                                                </a>
                                                            @endif
                                                        </td>
                                                        <td class="text-right">
                                                            {{ company_date_formate($estimation->issue_date) }}</td>
                                                        <td>
                                                            @php
                                                                $quote_status = '';
                                                                $est_status = $estimation->estimationStatus()
                                                                    ->is_display;
                                                                if ($est_status == 0) {
                                                                    $quote_status = 'invited';
                                                                } elseif ($est_status == 1) {
                                                                    $quote_status = 'quote_submitted';
                                                                }
                                                            @endphp
                                                            @if ($quote_status == 'invited')
                                                                <a href="{{ $setup_url }}" class="dropdown-item">
                                                                    <i class="ti ti-add">{{ __('Create Quote') }}
                                                                </a>
                                                            @elseif($quote_status == 'quote_submitted')
                                                                <a href="{{ $setup_url }}" class="dropdown-item">
                                                                    <i class="ti ti-eye"></i>{{ __('View Quote') }}
                                                                </a>
                                                            @else
                                                                <a href="{{ $setup_url }}" class="dropdown-item">
                                                                    <i class="ti ti-eye"></i>{{ __('View Quote') }}
                                                                </a>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    @else
                                        @php
                                            $client_final_estimation_id = isset(
                                                $project->client_final_quote->project_estimation_id,
                                            )
                                                ? $project->client_final_quote->project_estimation_id
                                                : '';
                                            $sub_contractor_final_estimation_id = isset(
                                                $project->sub_contractor_final_quote->project_estimation_id,
                                            )
                                                ? $project->sub_contractor_final_quote->project_estimation_id
                                                : '';
                                            $same_final_estimations = 0;
                                            if ($client_final_estimation_id == $sub_contractor_final_estimation_id) {
                                                $same_final_estimations = 1;
                                            }
                                        @endphp
                                        @if (isset($project->client_final_quote->project_estimation_id))
                                            <div class="row mb-2">
                                                <div class="col-md-12">
                                                    <h6>{{ __('Final Estimations') }}</h6>
                                                </div>
                                            </div>
                                        @endif
                                        <table class="table table-bordered estimation-list px-2" id="{{-- estimation-table --}}">
                                            <thead>
                                                <th class="select"></th>
                                                <th class="status">{{ __('Status') }}</th>
                                                <th class="title">{{ __('Title') }}</th>
                                                <th class="net-inc-discount">{{ __('Net incl. Discount') }}</th>
                                                <th class="gross-inc-discount">{{ __('Gross incl. Discount') }}</th>
                                                <th class="discount">{{ __('Discount %') }}</th>
                                                <th class="issue-date">{{ __('Issue Date') }}</th>
                                                <th class="action">{{ __('Action') }}</th>
                                            </thead>
                                            @php
                                                $only_final_display = 'd-none';
                                                if (
                                                    isset($project->client_final_quote->estimation) ||
                                                    isset($project->sub_contractor_final_quote->estimation)
                                                ) {
                                                    $only_final_display = '';
                                                }
                                            @endphp
                                            <tbody class="only_final_quotes {{ $only_final_display }}">
                                                @php
                                                    $final_quote_list = [];
                                                    $profit_gross = 0;
                                                    $profit_gross_with_discount = 0;
                                                    $profit_net = 0;
                                                    $profit_net_with_discount = 0;
                                                    $profit_discount = 0;

                                                    $client_gross = 0;
                                                    $client_gross_with_discount = 0;
                                                    $client_net = 0;
                                                    $client_net_with_discount = 0;
                                                    $client_discount = 0;

                                                    $sub_contractor_gross = 0;
                                                    $sub_contractor_gross_with_discount = 0;
                                                    $sub_contractor_net = 0;
                                                    $sub_contractor_net_with_discount = 0;
                                                    $sub_contractor_discount = 0;
                                                @endphp
                                                @if (isset($project->client_final_quote->id) && isset($project->client_final_quote->estimation))
                                                    @php
                                                        $client_final_quote = $project->client_final_quote;
                                                        $estimation = $project->client_final_quote->estimation;
                                                        array_push($final_quote_list, $estimation->id);
                                                        //	$queues_result = $estimation->getQueuesProgress();
                                                        $queues_result = [];
                                                        $client_gross = isset($client_final_quote->gross)
                                                            ? $client_final_quote->gross
                                                            : 0;
                                                        $client_gross_with_discount = isset(
                                                            $client_final_quote->gross_with_discount,
                                                        )
                                                            ? $client_final_quote->gross_with_discount
                                                            : 0;
                                                        $client_net = isset($client_final_quote->net)
                                                            ? $client_final_quote->net
                                                            : 0;
                                                        $client_net_with_discount = isset(
                                                            $client_final_quote->net_with_discount,
                                                        )
                                                            ? $client_final_quote->net_with_discount
                                                            : 0;
                                                        $client_discount = isset($client_final_quote->discount)
                                                            ? $client_final_quote->discount
                                                            : 0;
                                                    @endphp
                                                    <tr class="client_final_quote">
                                                        <td>
                                                            <div class="form-check form-switch">
                                                                <input type="checkbox" class="estimation_selection"
                                                                    id="estimation_check_0"
                                                                    value="{{ Crypt::encrypt($estimation->id) }}"
                                                                    onchange="selected_estimations()">
                                                                <label class="custom-control-label"
                                                                    for="estimation_check_0"></label>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <span
                                                                class="badge fix_badges client-final-badge rounded">{{ __('Client Final Quote') }}</span>
                                                            <span
                                                                class="badge fix_badges bg-{{ $statuesColor[$estimationStatus[$estimation->status]] }} p-2 px-3 rounded">{{ $estimationStatus[$estimation->status] }}</span>
                                                        </td>
                                                        <td style="font-weight:600;">
                                                            @if ($estimation->status > 1)
                                                                <a
                                                                    href="{{ route('estimations.finalize.estimate', encrypt($estimation->id)) }}">
                                                                    {{ $estimation->title }}
                                                                </a>
                                                            @else
                                                                <a
                                                                    href="{{ route('estimations.setup.estimate', \Crypt::encrypt($estimation->id)) }}">
                                                                    {{ $estimation->title }}
                                                                </a>
                                                            @endif
                                                            @if (!empty($queues_result))
                                                                @foreach ($queues_result['estimation_queues_list'] as $qrow)
                                                                    @if ($qrow['completed_percentage'] >= 0)
                                                                        <div class="progress">
                                                                            <div class="progress-bar bg-success"
                                                                                role="progressbar"
                                                                                style="width: {{ $qrow['completed_percentage'] }}%"
                                                                                aria-valuenow="{{ $qrow['completed_percentage'] }}"
                                                                                aria-valuemin="0" aria-valuemax="100">
                                                                                {{ $qrow['completed_percentage'] }}%</div>
                                                                        </div>
                                                                    @endif
                                                                @endforeach
                                                            @endif
                                                        </td>
                                                        <td class="text-right">
                                                            {{ currency_format_with_sym($client_final_quote->net_with_discount) }}
                                                        </td>
                                                        <td class="text-right">
                                                            {{ currency_format_with_sym($client_final_quote->gross_with_discount) }}
                                                        </td>
                                                        <td class="text-right">
                                                            {{ number_format($client_final_quote->discount, 2, ',', '.') }} %
                                                        </td>
                                                        <td class="text-right">
                                                            {{ company_date_formate($estimation->issue_date) }}</td>
                                                        <td class="">
                                                            <div class="icons-div">
                                                                @if (\Auth::user()->type == 'company')
                                                                    @permission('estimation copy')
                                                                        <div class="action-btn btn-primary ms-2">
                                                                            <a class="action-btn btn-info mx-1  btn btn-sm d-inline-flex align-items-center"
                                                                                data-ajax-popup="true" data-size="lg"
                                                                                data-title="{{ __('Create New Item') }}"
                                                                                data-url="{{ route('estimations.copy', $estimation->id) }}"
                                                                                data-toggle="tooltip"
                                                                                title="{{ __('Duplicate Estimation') }}"><i
                                                                                    class="ti ti-copy text-white"></i></a>
                                                                        </div>
                                                                    @endpermission
                                                                    @permission('estimation delete')
                                                                        <form id="delete-form2-{{ $estimation->id }}"
                                                                            action="{{ route('estimations.deleteEstimation', [$estimation->id]) }}"
                                                                            method="POST" style="display: none;"
                                                                            class="d-inline-flex">
                                                                            <a href="#"
                                                                                class="action-btn btn-danger mx-1  btn btn-sm d-inline-flex align-items-center bs-pass-para show_confirm"
                                                                                data-confirm="{{ __('Are You Sure?') }}"
                                                                                data-text="{{ __('This action can not be undone. Do you want to continue?') }}"
                                                                                data-confirm-yes="delete-form2-{{ $estimation->id }}"
                                                                                data-toggle="tooltip"
                                                                                title="{{ __('Delete') }}"><i
                                                                                    class="ti ti-trash"></i></a>
                                                                            @csrf
                                                                            @method('DELETE')
                                                                        </form>
                                                                    @endpermission
                                                                    @permission('estimation invite user')
                                                                        <div class="action-btn btn-primary ms-2">
                                                                            <a class="action-btn btn-primary mx-1  btn btn-sm d-inline-flex align-items-center"
                                                                                data-ajax-popup="true" data-size="md"
                                                                                data-title="{{ __('Add User') }}"
                                                                                data-url="{{ route('estimation.allowedUsers', ['estimation_id' => $estimation->id]) }}"
                                                                                data-toggle="tooltip"
                                                                                title="{{ __('Invite User') }}"><i
                                                                                    class="ti ti-plus text-white"></i></a>
                                                                        </div>
                                                                    @endpermission
                                                                @endif
                                                                <div>
                                                                    <div class="user-group projectusers">
                                                                        @foreach ($estimation->all_quotes_list as $row)
                                                                            @php
                                                                                $quote_status = '';
                                                                                if ($row->is_display == 1) {
                                                                                    $border_color = '#6FD943';
                                                                                    $quote_status = __(
                                                                                        'Quote Submitted',
                                                                                    );
                                                                                } else {
                                                                                    $border_color = '';
                                                                                    $quote_status = __('Invited');
                                                                                }
                                                                            @endphp
                                                                            <img @if (!empty($row->user->avatar)) src="{{ get_file($row->user->avatar) }}" @else avatar="{{ $row->user->name }}" @endif
                                                                                class="subc"
                                                                                style="border:4px solid {{ $border_color }} !important"
                                                                                data-bs-toggle="tooltip"
                                                                                title="{{ $row->user->name . ' - ' . ucfirst($quote_status) }}"
                                                                                data-user_id="{{ $row->user->id }}"
                                                                                data-estimation_id="{{ $estimation->id }}">
                                                                        @endforeach
                                                                    </div>
                                                        </td>
                                                    </tr>
                                                @endif
                                                @if (isset($project->sub_contractor_final_quote->id) && isset($project->sub_contractor_final_quote->estimation))
                                                    @php
                                                        $sub_contractor_final_quote =
                                                            $project->sub_contractor_final_quote;
                                                        $estimation = $project->sub_contractor_final_quote->estimation;
                                                        array_push($final_quote_list, $estimation->id);
                                                        $sub_contractor_gross = isset(
                                                            $sub_contractor_final_quote->gross,
                                                        )
                                                            ? $sub_contractor_final_quote->gross
                                                            : 0;
                                                        $sub_contractor_gross_with_discount = isset(
                                                            $sub_contractor_final_quote->gross_with_discount,
                                                        )
                                                            ? $sub_contractor_final_quote->gross_with_discount
                                                            : 0;
                                                        $sub_contractor_net = isset($sub_contractor_final_quote->net)
                                                            ? $sub_contractor_final_quote->net
                                                            : 0;
                                                        $sub_contractor_net_with_discount = isset(
                                                            $sub_contractor_final_quote->net_with_discount,
                                                        )
                                                            ? $sub_contractor_final_quote->net_with_discount
                                                            : 0;
                                                        $sub_contractor_discount = isset(
                                                            $sub_contractor_final_quote->discount,
                                                        )
                                                            ? $sub_contractor_final_quote->discount
                                                            : 0;
                                                    @endphp
                                                    <tr class="subcontractor_final_quote">
                                                        <td>
                                                            <div class="form-check form-switch">
                                                                <input type="checkbox" class="estimation_selection"
                                                                    id="estimation_check_0"
                                                                    value="{{ Crypt::encrypt($estimation->id) }}"
                                                                    onchange="selected_estimations()">
                                                                <label class="custom-control-label"
                                                                    for="estimation_check_0"></label>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <span
                                                                class="badge fix_badges sc-final-badge rounded">{{ __('Subcontractor Final Quote') }}</span>
                                                            <span
                                                                class="badge fix_badges bg-{{ $statuesColor[$estimationStatus[$estimation->status]] }} p-2 px-3 rounded">{{ $estimationStatus[$estimation->status] }}</span>
                                                        </td>
                                                        <td style="font-weight:600;">
                                                            @if ($estimation->status > 1)
                                                                <a
                                                                    href="{{ route('estimations.finalize.estimate', encrypt($estimation->id)) }}">
                                                                    {{ $estimation->title }}
                                                                </a>
                                                            @else
                                                                <a
                                                                    href="{{ route('estimations.setup.estimate', \Crypt::encrypt($estimation->id)) }}">
                                                                    {{ $estimation->title }}
                                                                </a>
                                                            @endif
                                                        </td>
                                                        <td class="text-right">
                                                            {{ currency_format_with_sym($sub_contractor_final_quote->net_with_discount) }}
                                                        </td>
                                                        <td class="text-right">
                                                            {{ currency_format_with_sym($sub_contractor_final_quote->gross_with_discount) }}
                                                        </td>
                                                        <td class="text-right">
                                                            {{ number_format($sub_contractor_final_quote->discount, 2, ',', '.') }}
                                                            %</td>
                                                        <td class="text-right">
                                                            {{ company_date_formate($estimation->issue_date) }}</td>
                                                        <td class="">
                                                            <div class="icons-div">
                                                                @if (\Auth::user()->type == 'company')
                                                                    @permission('estimation copy')
                                                                        <div class="action-btn btn-primary ms-2">
                                                                            <a class="action-btn btn-info mx-1  btn btn-sm d-inline-flex align-items-center"
                                                                                data-ajax-popup="true" data-size="lg"
                                                                                data-title="{{ __('Create New Item') }}"
                                                                                data-url="{{ route('estimations.copy', $estimation->id) }}"
                                                                                data-toggle="tooltip"
                                                                                title="{{ __('Duplicate Estimation') }}"><i
                                                                                    class="ti ti-copy text-white"></i></a>
                                                                        </div>
                                                                    @endpermission
                                                                    @permission('estimation delete')
                                                                        <form id="delete-form2-{{ $estimation->id }}"
                                                                            action="{{ route('estimations.deleteEstimation', [$estimation->id]) }}"
                                                                            method="POST" style="display: none;"
                                                                            class="d-inline-flex">
                                                                            <a href="#"
                                                                                class="action-btn btn-danger mx-1  btn btn-sm d-inline-flex align-items-center bs-pass-para show_confirm"
                                                                                data-confirm="{{ __('Are You Sure?') }}"
                                                                                data-text="{{ __('This action can not be undone. Do you want to continue?') }}"
                                                                                data-confirm-yes="delete-form2-{{ $estimation->id }}"
                                                                                data-toggle="tooltip"
                                                                                title="{{ __('Delete') }}"><i
                                                                                    class="ti ti-trash"></i></a>
                                                                            @csrf
                                                                            @method('DELETE')
                                                                        </form>
                                                                    @endpermission
                                                                    @permission('estimation invite user')
                                                                        <div class="action-btn btn-primary ms-2">
                                                                            <a class="action-btn btn-primary mx-1  btn btn-sm d-inline-flex align-items-center"
                                                                                data-ajax-popup="true" data-size="md"
                                                                                data-title="{{ __('Add User') }}"
                                                                                data-url="{{ route('estimation.allowedUsers', ['estimation_id' => $estimation->id]) }}"
                                                                                data-toggle="tooltip"
                                                                                title="{{ __('Invite User') }}"><i
                                                                                    class="ti ti-plus text-white"></i></a>
                                                                        </div>
                                                                    @endpermission
                                                                @endif
                                                            </div>
                                                            <div class="user-group projectusers">
                                                                @foreach ($estimation->all_quotes_list as $row)
                                                                    @php
                                                                        $quote_status = '';
                                                                        if ($row->is_display == 1) {
                                                                            $border_color = '#6FD943';
                                                                            $quote_status = __('Quote Submitted');
                                                                        } else {
                                                                            $border_color = '';
                                                                            $quote_status = __('Invited');
                                                                        }
                                                                    @endphp
                                                                    <img @if (!empty($row->user->avatar)) src="{{ get_file($row->user->avatar) }}" @else avatar="{{ $row->user->name }}" @endif
                                                                        class="subc"
                                                                        style="border:4px solid {{ $border_color }} !important"
                                                                        data-bs-toggle="tooltip"
                                                                        title="{{ $row->user->name . ' - ' . ucfirst($quote_status) }}"
                                                                        data-user_id="{{ $row->user->id }}"
                                                                        data-estimation_id="{{ $estimation->id }}">
                                                                @endforeach
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endif
                                                <tr class="only_final_quotes_profit">
                                                    @php
                                                        $profit_gross =
                                                            floatval($client_gross) - floatval($sub_contractor_gross);
                                                        $profit_gross_with_discount =
                                                            floatval($client_gross_with_discount) -
                                                            floatval($sub_contractor_gross_with_discount);
                                                        $profit_net =
                                                            floatval($client_net) - floatval($sub_contractor_net);
                                                        $profit_net_with_discount =
                                                            floatval($client_net_with_discount) -
                                                            floatval($sub_contractor_net_with_discount);
                                                        $profit_discount =
                                                            floatval($client_discount) -
                                                            floatval($sub_contractor_discount);
                                                    @endphp
                                                    <th colspan="2"></th>
                                                    <th><b>{{ __('Profit') }}:</b></th>
                                                    <th class="text-right net-discount">
                                                        {{ currency_format_with_sym($profit_net_with_discount) }}</th>
                                                    <th class="text-right gross-discount">
                                                        {{ currency_format_with_sym($profit_gross_with_discount) }}</th>
                                                    <th class="text-right discount">
                                                        <!-- {{ number_format($profit_discount, 2, ',', '.') }} % -->
                                                    </th>
                                                    <th></th>
                                                    <th colspan="2"></th>
                                                </tr>
                                                <tr class="other-estimations-row">
                                                    <td colspan="8">
                                                        <h6>{{ __('Other Estimations') }}</h6>
                                                    </td>
                                                </tr>
                                            </tbody>
                                            <tbody class="all_estimations">
                                                @foreach ($project_estimations as $e_key => $estimation)
                                                    @if (isset($estimation->id))
                                                        @if (!in_array($estimation->id, $final_quote_list))
                                                            @php
                                                                $bg_color = '';
                                                                $is_selected_quote = 0;
                                                                if (
                                                                    $same_final_estimations > 0 &&
                                                                    $client_final_estimation_id == $estimation->id
                                                                ) {
                                                                    $bg_color = 'bg-success';
                                                                } else {
                                                                    if (
                                                                        $client_final_estimation_id == $estimation->id
                                                                    ) {
                                                                        $bg_color = 'bg-info';
                                                                    }
                                                                    if (
                                                                        $sub_contractor_final_estimation_id ==
                                                                        $estimation->id
                                                                    ) {
                                                                        $bg_color = 'bg-warning';
                                                                    }
                                                                }
                                                                if ($bg_color != '') {
                                                                    $is_selected_quote = 1;
                                                                }
                                                                //	$queues_result = $estimation->getQueuesProgress();
                                                                $queues_result = [];
                                                            @endphp
                                                            <tr class="{{ $bg_color }}">
                                                                <td>
                                                                    <div class="form-check form-switch">
                                                                        <input type="checkbox" class="estimation_selection"
                                                                            id="estimation_check_{{ $e_key }}"
                                                                            value="{{ Crypt::encrypt($estimation->id) }}"
                                                                            onchange="selected_estimations()">
                                                                        <label class="custom-control-label"
                                                                            for="estimation_check_{{ $e_key }}	"></label>
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <span
                                                                        class="badge fix_badges bg-{{ $statuesColor[$estimationStatus[$estimation->status]] }} p-2 px-3 rounded">{{ $estimationStatus[$estimation->status] }}</span>
                                                                    @if (!empty($queues_result))
                                                                        @foreach ($queues_result['estimation_queues_list'] as $qrow)
                                                                            @if ($qrow['completed_percentage'] >= 0)
                                                                                <div class="progress">
                                                                                    <div class="progress-bar bg-success"
                                                                                        role="progressbar"
                                                                                        style="width: {{ $qrow['completed_percentage'] }}%"
                                                                                        aria-valuenow="{{ $qrow['completed_percentage'] }}"
                                                                                        aria-valuemin="0" aria-valuemax="100">
                                                                                        {{ $qrow['completed_percentage'] }}%
                                                                                    </div>
                                                                                </div>
                                                                            @endif
                                                                        @endforeach
                                                                    @endif
                                                                </td>
                                                                <td class="est-title">
                                                                    @if ($estimation->status > 1)
                                                                        <a
                                                                            href="{{ route('estimations.finalize.estimate', encrypt($estimation->id)) }}">
                                                                            {{ $estimation->title }}
                                                                        </a>
                                                                    @else
                                                                        <a
                                                                            href="{{ route('estimations.setup.estimate', \Crypt::encrypt($estimation->id)) }}">
                                                                            {{ $estimation->title }}
                                                                        </a>
                                                                    @endif
                                                                </td>
                                                                @if ($is_selected_quote > 0 && isset($estimation->final_quote->id))
                                                                    @if ($same_final_estimations > 0)
                                                                        <td class="text-right">
                                                                            {{ currency_format_with_sym($client_final_quote->net_with_discount) }}
                                                                        </td>
                                                                        <td class="text-right">
                                                                            {{ currency_format_with_sym($client_final_quote->gross_with_discount) }}
                                                                        </td>
                                                                        <td class="text-right">
                                                                            {{ number_format($client_final_quote->discount, 2, ',', '.') }}
                                                                            %</td>
                                                                    @else
                                                                        <td class="text-right">
                                                                            {{ currency_format_with_sym($estimation->final_quote->net_with_discount) }}
                                                                        </td>
                                                                        <td class="text-right">
                                                                            {{ currency_format_with_sym($estimation->final_quote->gross_with_discount) }}
                                                                        </td>
                                                                        <td class="text-right">
                                                                            {{ number_format($estimation->final_quote->discount, 2, ',', '.') }}
                                                                            %</td>
                                                                    @endif
                                                                @else
                                                                    <td class="text-right">
                                                                        {{ currency_format_with_sym(isset($estimation->final_quote->net_with_discount) ? $estimation->final_quote->net_with_discount : 0) }}
                                                                    </td>
                                                                    <td class="text-right">
                                                                        {{ currency_format_with_sym(isset($estimation->final_quote->gross_with_discount) ? $estimation->final_quote->gross_with_discount : 0) }}
                                                                    </td>
                                                                    <td class="text-right">
                                                                        {{ number_format(isset($estimation->final_quote->discount) ? $estimation->final_quote->discount : 0, 2, ',', '.') }}
                                                                        %</td>
                                                                @endif
                                                                <td class="text-right">
                                                                    {{ company_date_formate($estimation->issue_date) }}</td>
                                                                <td class="actions">
                                                                    @if (\Auth::user()->type == 'company')
                                                                        <div class="icons-div">
                                                                            @permission('estimation copy')
                                                                                <div class="action-btn btn-primary ms-2">
                                                                                    <a class="action-btn btn-info mx-1  btn btn-sm d-inline-flex align-items-center"
                                                                                        data-ajax-popup="true" data-size="lg"
                                                                                        data-title="{{ __('Create New Item') }}"
                                                                                        data-url="{{ route('estimations.copy', $estimation->id) }}"
                                                                                        data-toggle="tooltip"
                                                                                        title="{{ __('Duplicate Estimation') }}"><i
                                                                                            class="ti ti-copy text-white"></i></a>
                                                                                </div>
                                                                            @endpermission
                                                                            @permission('estimation delete')
                                                                                <form id="delete-form2-{{ $estimation->id }}"
                                                                                    action="{{ route('estimations.deleteEstimation', [$estimation->id]) }}"
                                                                                    method="POST" style="display: none;"
                                                                                    class="d-inline-flex">
                                                                                    <a href="#"
                                                                                        class="action-btn btn-danger mx-1  btn btn-sm d-inline-flex align-items-center bs-pass-para show_confirm"
                                                                                        data-confirm="{{ __('Are You Sure?') }}"
                                                                                        data-text="{{ __('This action can not be undone. Do you want to continue?') }}"
                                                                                        data-confirm-yes="delete-form2-{{ $estimation->id }}"
                                                                                        data-toggle="tooltip"
                                                                                        title="{{ __('Delete') }}"><i
                                                                                            class="ti ti-trash"></i></a>
                                                                                    @csrf
                                                                                    @method('DELETE')
                                                                                </form>
                                                                            @endpermission
                                                                            @permission('estimation invite user')
                                                                                <div class="action-btn btn-primary ms-2">
                                                                                    <a class="action-btn btn-primary mx-1  btn btn-sm d-inline-flex align-items-center"
                                                                                        data-ajax-popup="true" data-size="md"
                                                                                        data-title="{{ __('Add User') }}"
                                                                                        data-url="{{ route('estimation.allowedUsers', ['estimation_id' => $estimation->id]) }}"
                                                                                        data-toggle="tooltip"
                                                                                        title="{{ __('Invite User') }}"><i
                                                                                            class="ti ti-plus text-white"></i></a>
                                                                                </div>
                                                                            @endpermission
                                                                        </div>
                                                                        <div class="user-group projectusers">
                                                                            @foreach ($estimation->all_quotes_list as $row)
                                                                                @php
                                                                                    $quote_status = '';
                                                                                    if ($row->is_display == 1) {
                                                                                        $border_color = '#6FD943';
                                                                                        $quote_status = __(
                                                                                            'Quote Submitted',
                                                                                        );
                                                                                    } else {
                                                                                        $border_color = '';
                                                                                        $quote_status = __('Invited');
                                                                                    }
                                                                                @endphp
                                                                                <img @if (!empty($row->user->avatar)) src="{{ get_file($row->user->avatar) }}" @else avatar="{{ $row->user->name }}" @endif
                                                                                    class="subc"
                                                                                    style="border:4px solid {{ $border_color }} !important"
                                                                                    data-bs-toggle="tooltip"
                                                                                    title="{{ $row->user->name . ' - ' . ucfirst($quote_status) }}"
                                                                                    data-user_id="{{ $row->user->id }}"
                                                                                    data-estimation_id="{{ $estimation->id }}">
                                                                            @endforeach
                                                                        </div>
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        @endif
                                                    @endif
                                                @endforeach
                                            </tbody>
                                        </table>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endpermission


                @if ($display_other_tabs == true)
                    <div class="card-container cc2" id="progress-card">
                        <div class="card table-card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">{{ __('Project Progress') }}</h5>
                                <div class="">
                                    <a href="{{ route('project.project_progress', [\Crypt::encrypt($project->id), 'display' => 'all']) }}"
                                        class="btn btn-sm btn-primary btn-icon m-1" target="_blank">
                                        <i class="ti ti-plus"></i>
                                        {{ __('Create Internal Progress') }}
                                    </a>
                                    <a href="{{ route('project.project_progress', [\Crypt::encrypt($project->id)]) }}"
                                        class="btn btn-sm btn-primary btn-icon m-1" target="_blank">
                                        <i class="ti ti-plus"></i>
                                        {{ __('Client Progress') }}
                                    </a>
                                </div>
                            </div>
                            <div class="card-body p-3" id="progress-div">
                                <table class="table w-100 table-hover table-bordered" id="progress-table">
                                    <thead>
                                        <th>{{ __('ID') }}</th>
                                        <th>{{ __('Client Name') }}</th>
                                        <th>{{ __('Comment') }}</th>
                                        <th>{{ __('User') }}</th>
                                        <th>{{ __('Date') }}</th>
                                        <th>{{ __('Action') }}</th>
                                    </thead>

                                </table>
                            </div>
                        </div>
                    </div>
                @endif


                @include('project::project.show.utility.activity_log')


                <div class="col-md-12">
                    @stack('DocumentSection')
                </div>

            </div>
            <!-- [ sample-page ] end -->
        </div>
        <!-- [ Main Content ] end -->
    </div>
@endsection
@push('scripts')
    <script src="{{ asset('assets/js/plugins/apexcharts.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/tinymce/tinymce.min.js') }}"></script>
    <script src="{{ asset('js/letter.avatar.js') }}"></script>
    <script src="{{ asset('Modules/Taskly/Resources/assets/js/dropzone.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/select2.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/datatable/dataTables.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/datatable/intl.js') }}"></script>
    <script type="text/javascript"
        src="https://maps.google.com/maps/api/js?key=AIzaSyBbTqlUNbqPssvetzvRl4n65HB2g_-o9tE&libraries=places"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/1.6.2/axios.min.js"
        integrity="sha512-b94Z6431JyXY14iSXwgzeZurHHRNkLt9d6bAHt7BZT38eqV+GyngIi/tVye4jBKPYQ2lBdRs0glww4fmpuLRwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="{{ asset('js/jquery.validate.min.js') }}"></script>
    <script>
        var active_estimation_id = '{{ isset($active_estimation->id) ? $active_estimation->id : 0 }}';
        let moneyFormat = '{{ $site_money_format }}';
        var project_id = '{{ \Crypt::encrypt($project->id) }}';

        $(document).ready(function() {

            /** call ajaxComplete after open data-popup **/
            $(document).ajaxComplete(function() {
                tinymce.remove();
                document.querySelectorAll('.tinyMCE').forEach(function(editor) {
                    init_tiny_mce('#' + editor.id);
                });
            });

            var type = '{{ $project->type }}';
            if (type == 'template') {
                $('.pro_type').addClass('d-none');
            } else {
                $('.pro_type').removeClass('d-none');
            }

            init_tiny_mce('.tinyMCE');
            set_construction_address();
            getItems(active_estimation_id);

            $(document).on("click", ".projectusers img", function() {
                var csrfToken = $('meta[name="csrf-token"]').attr('content');
                var user_id = $(this).data('user_id');
                var estimation_id = $(this).data('estimation_id');

                const swalWithBootstrapButtons = Swal.mixin({
                    customClass: {
                        confirmButton: 'btn btn-success',
                        cancelButton: 'btn btn-danger'
                    },
                    buttonsStyling: false
                })
                swalWithBootstrapButtons.fire({
                    title: 'Are you sure to Remove this User from this Estimation?',
                    text: "This action can not be undone. Do you want to continue?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes',
                    cancelButtonText: 'No',
                    reverseButtons: true,
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '{{ route('estimation.remove_estimation_user') }}',
                            type: "POST",
                            data: {
                                estimation_id: estimation_id,
                                user_id: user_id,
                                _token: csrfToken
                            },
                            beforeSend: function() {
                                showHideLoader('visible');
                            },
                            success: function(response) {
                                if (response.status == true) {
                                    showHideLoader('hidden');
                                    toastrs('Success', response.message, 'success');
                                    setTimeout(function() {
                                        location.reload();
                                    }, 1000)
                                } else {
                                    toastrs('Error', response.message)
                                }
                            }
                        });
                    }
                })
            });

            $(document).on("change", "#same_invoice_address", function() {
                $('.different-invoice-address-block').toggleClass('d-none');
            });

            $(document).on('change', '#construction-select', function() {
                var selectedOption = $('#construction-select option:selected');
                var selectedType = selectedOption.data('type');
                var clientTypeInput = document.getElementById('client_type1');

                if (selectedType !== undefined && selectedType !== null) {
                    clientTypeInput.value = selectedType;
                } else {
                    clientTypeInput.value = 'new';
                }

                var url = '{{ route('users.get_user') }}';
                var user_id = this.value;

                // Get the selected values
                if (user_id) {
                    axios.post(url, {
                        'user_id': user_id,
                        'from': 'construction'
                    }).then((response) => {

                        var clientDetailsElement = document.getElementById('construction-details');

                        $('#construction-details').html(response.data.html_data);
                        $('#construction_detail_id').val(response.data.user_id);
                        initialize_construction();
                        if ($('#construction_detail-company_notes').length > 0) {
                            init_tiny_mce('#construction_detail-company_notes');
                        }




                        // Remove the d-none class if the element is found
                        if (clientDetailsElement) {
                            clientDetailsElement.classList.remove('d-none');
                        }
                    })
                } else {
                    var clientDetailsElement = document.getElementById('construction-details');
                    // Remove the d-none class if the element is found
                    if (clientDetailsElement) {
                        clientDetailsElement.classList.add('d-none');
                    }
                }
            });

            $(document).on('change', '#client-select', function() {
                var selectedOption = $('#client-select option:selected');
                var selectedType = selectedOption.data('type');
                var clientTypeInput = document.getElementById('client_type');

                if (selectedType !== undefined && selectedType !== null) {
                    clientTypeInput.value = selectedType;
                } else {
                    clientTypeInput.value = 'new';
                }
                var url;

                var url = '{{ route('users.get_user') }}';

                // Get the selected values
                if (this.value) {
                    axios.post(url, {
                        'user_id': this.value,
                        'from': 'client'
                    }).then((response) => {
                        var clientDetailsElement = document.getElementById('client-details');

                        $('#client-details').html(response.data.html_data);
                        $('#client_id').val(response.data.user_id);
                        initialize();

                        if ($('#client-company_notes').length > 0) {
                            init_tiny_mce('#client-company_notes');
                        }

                        // Remove the d-none class if the element is found
                        if (clientDetailsElement) {
                            clientDetailsElement.classList.remove('d-none');
                        }
                    })
                } else {
                    var clientDetailsElement = document.getElementById('client-details');
                    // Remove the d-none class if the element is found
                    if (clientDetailsElement) {
                        clientDetailsElement.classList.add('d-none');
                    }
                }
            });

            /*** edit feedback ***/
            $(document).on("click", ".client_feedback_edit", function(e) {
                e.preventDefault();
                var feedback_id = $(this).data('id');
                if (feedback_id != '') {
                    $.ajax({
                        url: "{{ route('get.project.client.feedback', $project->id) }}",
                        type: "POST",
                        data: {
                            feedback_id: feedback_id,
                            // _token : csrfToken
                        },
                        beforeSend: function() {
                            showHideLoader('visible');
                        },
                        success: function(response) {
                            if (response.status == true) {
                                showHideLoader('hidden');
                                if (response.data.feedback != null) {
                                    tinymce.get('feedbackEditor').setContent(response.data
                                        .feedback);
                                }
                                $('#feedback_id').val(response.data.id);
                                if (response.data.file != null) {
                                    $('#feedback_old_file').val(response.data.file);
                                }
                                $('.feedback_old_file_link').html(response.file_link);
                                $('.feedback_collapse' + feedback_id).collapse('hide');
                                $("#collapseFeedback").collapse('show');
                                $('html, body').animate({
                                    scrollTop: $("#feedbackAccordion").offset().top
                                }, 200);
                            } else {
                                toastrs('Error', response.message);
                            }
                        }
                    });
                }
            });

            /*** delete feedback ***/
            $(document).on("click", ".client_feedback_delete", function(e) {
                e.preventDefault();
                var feedback_id = $(this).data('id');
                const swalWithBootstrapButtons = Swal.mixin({
                    customClass: {
                        confirmButton: 'btn btn-success',
                        cancelButton: 'btn btn-danger'
                    },
                    buttonsStyling: false
                })
                swalWithBootstrapButtons.fire({
                    title: "{{ __('Are you sure to remove this client message?') }}",
                    text: "{{ __('This action can not be undone. Do you want to continue?') }}",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: "{{ __('Yes') }}",
                    cancelButtonText: "{{ __('No') }}",
                    reverseButtons: true,
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('project.client.feedback.delete', $project->id) }}",
                            type: "POST",
                            data: {
                                feedback_id: feedback_id,
                                //	_token : csrfToken
                            },
                            beforeSend: function() {
                                showHideLoader('visible');
                            },
                            success: function(response) {
                                if (response.status == true) {
                                    showHideLoader('hidden');
                                    $('.feedback_heading' + feedback_id).remove();
                                    $('.feedback_collapse' + feedback_id).remove();
                                    toastrs('Success', response.message, 'success');
                                    setTimeout(function() {
                                        window.location.reload();
                                    }, 1000);
                                } else {
                                    toastrs('Error', response.message);
                                }
                            }
                        });
                    }
                });
            });

            /*** edit project comments ***/
            $(document).on("click", ".project_comments_edit", function(e) {
                e.preventDefault();
                var comment_id = $(this).data('id');
                if (comment_id != '') {
                    $.ajax({
                        url: "{{ route('get.project.comment', $project->id) }}",
                        type: "POST",
                        data: {
                            comment_id: comment_id,
                        },
                        beforeSend: function() {
                            showHideLoader('visible');
                        },
                        success: function(response) {
                            if (response.status == true) {
                                showHideLoader('hidden');
                                if (response.data.comment != null) {
                                    tinymce.get('commentEditor').setContent(response.data
                                        .comment);
                                }
                                $('#project_comment_id').val(response.data.id);
                                $('#project_comment_old_file').val(response.data.file);
                                $('.project_comment_old_file_link').html(response.file_link);
                                $('.comment_collapse' + comment_id).collapse('hide');
                                $("#collapseComment").collapse('show');
                                $('html, body').animate({
                                    scrollTop: $("#commentAccordion").offset().top
                                }, 200);
                            } else {
                                toastrs('Error', response.message);
                            }
                        }
                    });
                }
            });

            /*** delete project comments ***/
            $(document).on("click", ".project_comments_delete", function(e) {
                e.preventDefault();
                var comment_id = $(this).data('id');
                const swalWithBootstrapButtons = Swal.mixin({
                    customClass: {
                        confirmButton: 'btn btn-success',
                        cancelButton: 'btn btn-danger'
                    },
                    buttonsStyling: false
                })
                swalWithBootstrapButtons.fire({
                    title: "{{ __('Are you sure to remove this comment?') }}",
                    text: "{{ __('This action can not be undone. Do you want to continue?') }}",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: "{{ __('Yes') }}",
                    cancelButtonText: "{{ __('No') }}",
                    reverseButtons: true,
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('project.comment.delete', $project->id) }}",
                            type: "POST",
                            data: {
                                comment_id: comment_id,
                            },
                            beforeSend: function() {
                                showHideLoader('visible');
                            },
                            success: function(response) {
                                if (response.status == true) {
                                    showHideLoader('hidden');
                                    $('.comment_heading' + comment_id).remove();
                                    $('.comment_collapse' + comment_id).remove();
                                    toastrs('Success', response.message, 'success');
                                    setTimeout(function() {
                                        window.location.reload();
                                    }, 1000);
                                } else {
                                    toastrs('Error', response.message);
                                }
                            }
                        });
                    }
                });
            });

            $(document).on('submit', '.project_detail_form', function(e) {
                e.preventDefault();
                var formdata = $(this).serialize();
                var url = $(this).attr('action');
                $.ajax({
                    type: "post",
                    url: url,
                    data: formdata,
                    cache: false,
                    beforeSend: function() {
                        $(this).find('.btn-create').attr('disabled', 'disabled');
                        if ($('#commonModal #project-description').length > 0) {
                            //	tinymce.activeEditor.remove("textarea");
                            tinymce.get('project-description').remove();
                        }
                        if ($('#commonModal #event_description').length > 0) {
                            //	tinymce.activeEditor.remove("textarea");
                            tinymce.get('event_description').remove();
                        }
                        // if($('#commonModal #construction_detail-company_notes').length > 0) {
                        // //	tinymce.activeEditor.remove("textarea");
                        // 	tinymce.get('construction_detail-company_notes').remove();
                        // }
                        // if($('#commonModal #client-company_notes').length > 0) {
                        // //	tinymce.activeEditor.remove("textarea");
                        // 	tinymce.get('client-company_notes').remove();
                        // }
                        if ($('#commonModal #technical-description').length > 0) {
                            tinymce.get('technical-description').remove();
                        }
                    },
                    success: function(data) {
                        if (data.is_success) {
                            toastrs('Success', data.message, 'success');
                            $('#commonModal').modal('hide');
                            $('.project_title').html(data.project.title);
                            $('.project-description').html(data.project.description);
                            $('.technical-description').html(data.project
                                .technical_description);
                            $('.invoice_address').addClass('d-none');
                            $('.invoice_address2').addClass('d-none');

                            if (data.status_changed == 1) {
                                location.reload();
                            }
                            set_construction_address();

                        }
                        if (data.user_details) {
                            var f_name = "";
                            var l_name = "";
                            if (data.user_details.first_name != null) {
                                f_name = data.user_details.first_name;
                            }
                            if (data.user_details.last_name != null) {
                                l_name = data.user_details.last_name;
                            }
                            var full_name = f_name + " " + l_name;
                            $('.client_full_name').html(full_name);
                        } else {
                            toastrs('Error', data.message, 'error');
                        }
                    },
                    complete: function() {
                        $(this).find('.btn-create').removeAttr('disabled');
                    },
                });
            });

            $(document).on("click", ".status", function() {
                var status = $(this).attr('data-id');
                var url = $(this).attr('data-url');

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: {
                        status: status,
                        "_token": $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(data) {
                        location.reload();
                    }
                });
            });

            $(document).on("select2:clear", "#property", function(e) {
                store_to_project_data('property_type', e);
            });
            $(document).on("select2:clear", "#construction_type", function(e) {
                store_to_project_data('construction_type', e)
            });

            $('.filter_select2').select2({
                placeholder: "Select",
                //	multiple: true,
                tags: true,
                templateSelection: function(data, container) {
                    $(container).css("background-color", $(data.element).data("background_color"));
                    if (data.element) {
                        $(container).css("color", $(data.element).data("font_color"));
                    }
                    return data.text;
                }
            });
        });

        function store_to_project_data(field, event) {
            if (field != "") {
                var field_value = $(event).val();
                if (field_value != "" && field_value != null) {
                    if (field == "label") {
                        field_value = field_value.join(", ")
                    }
                    if (field == "construction_type") {
                        field_value = field_value.join(", ")
                    }
                    if (field == "property_type") {
                        field_value = field_value.join(", ")
                    }
                    if (field == "priority") {
                        field_value = field_value.join(", ")
                    }
                }
                $.ajax({
                    url: '{{ route('project.add.status_data', $project->id) }}',
                    type: "POST",
                    data: {
                        field: field,
                        field_value: field_value,
                        "_token": $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(data) {
                        if (data.is_success) {
                            toastrs('Success', data.message, 'success');
                        } else {
                            toastrs('Error', data.message, 'error');
                        }
                    }
                });
            }
        }

        function set_construction_address() {
            var csrfToken = $('meta[name="csrf-token"]').attr('content');
            $.ajax({
                url: '{{ route('project.get_all_address', $project->id) }}',
                type: "POST",
                data: {
                    html: true,
                    _token: csrfToken
                },
                success: function(response) {
                    if (response.status == true) {
                        $(".project_all_address").html(response.html_data);
                    }
                }
            });
        }

        function selected_estimations() {
            var total_selected = 0;
            var estimation_ids = [];
            $('.estimation_selection').each(function() {

                if ($(this).prop('checked') == true) {
                    total_selected++;
                    var estimation_id = $(this).val();
                    estimation_ids.push(estimation_id);
                }
            });
            if (total_selected > 0) {
                $('.delete_estimation_form').removeClass('d-none');
                $('.btn_bulk_delete_estimations').addClass('show_confirm');
                $('.btn_bulk_delete_estimations').removeClass('show_error_toaster');
            } else {
                $('.delete_estimation_form').addClass('d-none');
                $('.btn_bulk_delete_estimations').removeClass('show_confirm');
                $('.btn_bulk_delete_estimations').addClass('show_error_toaster');
            }
            $('#remove_estimation_ids').val(JSON.stringify(estimation_ids));

        }

        function getItems(estimation_id) {
            let project_id = '{{ $project->id }}';
            var csrfToken = $('meta[name="csrf-token"]').attr('content');

            $('#progress-table').DataTable({
                "lengthMenu": [
                    [10, 25, 50, 100, 200, -1],
                    [10, 25, 50, 100, 200, "All"]
                ],
                'pageLength': 200,
                'dom': 'lrt',
                "bPaginate": false,
                "bFilter": false,
                "bInfo": false,
                "destroy": true,
                "processing": true,
                "serverSide": true,
                'order': [
                    [0, 'DESC']
                ],
                "bSort": false,
                "ajax": {
                    "url": '{{ route('progress.list') }}',
                    "type": "POST",
                    data: {
                        project_id: project_id,
                        _token: csrfToken
                    },
                },
                "columns": [{
                        "data": "id",
                        "className": "id",
                        "orderable": false
                    },
                    {
                        "data": "client_name",
                        "className": "client_name",
                        "orderable": false
                    },
                    {
                        "data": "comment",
                        "className": "comment",
                        "orderable": false
                    },
                    {
                        "data": "name",
                        "className": "history",
                        "orderable": false
                    },
                    {
                        "data": "date",
                        "className": "date",
                        "orderable": false
                    },
                    {
                        "data": "action",
                        "className": "action",
                        "orderable": false
                    }
                ],
                initComplete: function(settings, json) {

                },
            });
        }
    </script>

    {{-- <script>
        Dropzone.autoDiscover = false;
        myDropzone = new Dropzone("#dropzonewidget", {
            maxFiles: 20,
            maxFilesize: 20,
            parallelUploads: 1,
            acceptedFiles: ".jpeg,.jpg,.png,.pdf,.doc,.txt",
            url: "{{ route('projects.file.upload', [$project->id]) }}",
            success: function(file, response) {
                if (response.is_success) {
                    dropzoneBtn(file, response);
                    toastrs('{{ __('Success') }}', 'File Successfully Uploaded', 'success');
                } else {
                    myDropzone.removeFile(response.error);
                    toastrs('Error', response.error, 'error');
                }
            },
            error: function(file, response) {
                myDropzone.removeFile(file);
                if (response.error) {
                    toastrs('Error', response.error, 'error');
                } else {
                    toastrs('Error', response, 'error');
                }
            }
        });
        myDropzone.on("sending", function(file, xhr, formData) {
            formData.append("_token", $('meta[name="csrf-token"]').attr('content'));
            formData.append("project_id", {{ $project->id }});
        });

        @if (isset($permisions) && in_array('show uploading', $permisions))
            $(".dz-hidden-input").prop("disabled", true);
            myDropzone.removeEventListeners();
        @endif

        function dropzoneBtn(file, response) {

            var html = document.createElement('div');
            var download = document.createElement('a');
            download.setAttribute('href', response.download);
            download.setAttribute('class', "action-btn btn-primary mx-1  btn btn-sm d-inline-flex align-items-center");
            download.setAttribute('data-toggle', "tooltip");
            download.setAttribute('download', file.name);
            download.setAttribute('title', "{{ __('Download') }}");
            download.innerHTML = "<i class='ti ti-download'> </i>";
            html.appendChild(download);

            @if (isset($permisions) && in_array('show uploading', $permisions))
            @else
                var del = document.createElement('a');
                del.setAttribute('href', response.delete);
                del.setAttribute('class', "action-btn btn-danger mx-1  btn btn-sm d-inline-flex align-items-center");
                del.setAttribute('data-toggle', "popover");
                del.setAttribute('title', "{{ __('Delete') }}");
                del.innerHTML = "<i class='ti ti-trash '></i>";

                del.addEventListener("click", function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    if (confirm("Are you sure ?")) {
                        var btn = $(this);
                        $.ajax({
                            url: btn.attr('href'),
                            type: 'DELETE',
                            success: function(response) {
                                if (response.is_success) {
                                    btn.closest('.dz-image-preview').remove();
                                    toastrs('{{ __('Success') }}', 'File Successfully Deleted',
                                        'success');
                                } else {
                                    toastrs('{{ __('Error') }}', 'Something Wents Wrong.', 'error');
                                }
                            },
                            error: function(response) {
                                response = response.responseJSON;
                                if (response.is_success) {
                                    toastrs('{{ __('Error') }}', 'Something Wents Wrong.', 'error');
                                } else {
                                    toastrs('{{ __('Error') }}', 'Something Wents Wrong.', 'error');
                                }
                            }
                        })
                    }
                });

                html.appendChild(del);
            @endif

            file.previewTemplate.appendChild(html);
        }

        @php($files = $project->files)
        @foreach ($files as $file)

            @php($storage_file = get_base_file($file->file_path))
            // Create the mock file:
            var mockFile = {
                name: "{{ $file->file_name }}",
                size: "{{ get_size(get_file($file->file_path)) }}"
            };
            // Call the default addedfile event handler
            myDropzone.emit("addedfile", mockFile);
            // And optionally show the thumbnail of the file:
            myDropzone.emit("thumbnail", mockFile, "{{ get_file($file->file_path) }}");
            myDropzone.emit("complete", mockFile);

            dropzoneBtn(mockFile, {
                download: "{{ get_file($file->file_path) }}",
                delete: "{{ route('projects.file.delete', [$project->id, $file->id]) }}"
            });
        @endforeach
    </script> --}}
    <script>
        (function() {
            var options = {
                chart: {
                    height: 135,
                    type: 'line',
                    toolbar: {
                        show: false,
                    },
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    width: 2,
                    curve: 'smooth'
                },
                series: [
                    @foreach ($chartData['stages'] as $id => $name)
                        {
                            name: "{{ __($name) }}",
                            // data:
                            data: {!! json_encode($chartData[$id]) !!},
                        },
                    @endforeach
                ],
                xaxis: {
                    categories: {!! json_encode($chartData['label']) !!},
                },
                colors: {!! json_encode($chartData['color']) !!},

                grid: {
                    strokeDashArray: 4,
                },
                legend: {
                    show: false,
                },

                yaxis: {
                    tickAmount: 5,
                    min: 1,
                    max: 40,
                },
            };
            var chart = new ApexCharts(document.querySelector("#task-chart"), options);
            chart.render();
        })();

        $('.cp_link').on('click', function() {
            var value = $(this).attr('data-link');
            var $temp = $("<input>");
            $("body").append($temp);
            $temp.val(value).select();
            document.execCommand("copy");
            $temp.remove();
            toastrs('success', '{{ __('Link Copy on Clipboard') }}', 'success')
        });
    </script>
@endpush
