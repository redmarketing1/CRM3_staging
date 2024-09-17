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
                                <input type="hidden" value="" name="remove_estimation_ids" id="remove_estimation_ids">
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
                                                class="badge fix_badges bg {{ $estimationStatus[$estimation->status] }} p-2 px-3 rounded">{{ $estimationStatus[$estimation->status] }}</span>
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
                                                $est_status = $estimation->estimationStatus()->is_display;
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
                            $client_final_estimation_id = isset($project->client_final_quote->project_estimation_id)
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
                                        $client_gross_with_discount = isset($client_final_quote->gross_with_discount)
                                            ? $client_final_quote->gross_with_discount
                                            : 0;
                                        $client_net = isset($client_final_quote->net) ? $client_final_quote->net : 0;
                                        $client_net_with_discount = isset($client_final_quote->net_with_discount)
                                            ? $client_final_quote->net_with_discount
                                            : 0;
                                        $client_discount = isset($client_final_quote->discount)
                                            ? $client_final_quote->discount
                                            : 0;
                                    @endphp
                                    <tr class="client_final_quote">
                                        <td>
                                            <div class="form-check form-switch">
                                                <input type="checkbox" class="estimation_selection" id="estimation_check_0"
                                                    value="{{ Crypt::encrypt($estimation->id) }}"
                                                    onchange="selected_estimations()">
                                                <label class="custom-control-label" for="estimation_check_0"></label>
                                            </div>
                                        </td>
                                        <td>
                                            <span
                                                class="badge fix_badges client-final-badge rounded">{{ __('Client Final Quote') }}</span>
                                            <span
                                                class="badge fix_badges bg {{ $estimationStatus[$estimation->status] }} p-2 px-3 rounded">{{ $estimationStatus[$estimation->status] }}</span>
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
                                                            <div class="progress-bar bg-success" role="progressbar"
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
                                                            method="POST" style="display: none;" class="d-inline-flex">
                                                            <a href="#"
                                                                class="action-btn btn-danger mx-1  btn btn-sm d-inline-flex align-items-center bs-pass-para show_confirm"
                                                                data-confirm="{{ __('Are You Sure?') }}"
                                                                data-text="{{ __('This action can not be undone. Do you want to continue?') }}"
                                                                data-confirm-yes="delete-form2-{{ $estimation->id }}"
                                                                data-toggle="tooltip" title="{{ __('Delete') }}"><i
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
                                                                data-toggle="tooltip" title="{{ __('Invite User') }}"><i
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
                                @if (isset($project->sub_contractor_final_quote->id) && isset($project->sub_contractor_final_quote->estimation))
                                    @php
                                        $sub_contractor_final_quote = $project->sub_contractor_final_quote;
                                        $estimation = $project->sub_contractor_final_quote->estimation;
                                        array_push($final_quote_list, $estimation->id);
                                        $sub_contractor_gross = isset($sub_contractor_final_quote->gross)
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
                                        $sub_contractor_discount = isset($sub_contractor_final_quote->discount)
                                            ? $sub_contractor_final_quote->discount
                                            : 0;
                                    @endphp
                                    <tr class="subcontractor_final_quote">
                                        <td>
                                            <div class="form-check form-switch">
                                                <input type="checkbox" class="estimation_selection"
                                                    id="estimation_check_0" value="{{ Crypt::encrypt($estimation->id) }}"
                                                    onchange="selected_estimations()">
                                                <label class="custom-control-label" for="estimation_check_0"></label>
                                            </div>
                                        </td>
                                        <td>
                                            <span
                                                class="badge fix_badges sc-final-badge rounded">{{ __('Subcontractor Final Quote') }}</span>
                                            <span
                                                class="badge fix_badges bg {{ $estimationStatus[$estimation->status] }} p-2 px-3 rounded">{{ $estimationStatus[$estimation->status] }}</span>
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
                                                            method="POST" style="display: none;" class="d-inline-flex">
                                                            <a href="#"
                                                                class="action-btn btn-danger mx-1  btn btn-sm d-inline-flex align-items-center bs-pass-para show_confirm"
                                                                data-confirm="{{ __('Are You Sure?') }}"
                                                                data-text="{{ __('This action can not be undone. Do you want to continue?') }}"
                                                                data-confirm-yes="delete-form2-{{ $estimation->id }}"
                                                                data-toggle="tooltip" title="{{ __('Delete') }}"><i
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
                                                                data-toggle="tooltip" title="{{ __('Invite User') }}"><i
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
                                        $profit_gross = floatval($client_gross) - floatval($sub_contractor_gross);
                                        $profit_gross_with_discount =
                                            floatval($client_gross_with_discount) -
                                            floatval($sub_contractor_gross_with_discount);
                                        $profit_net = floatval($client_net) - floatval($sub_contractor_net);
                                        $profit_net_with_discount =
                                            floatval($client_net_with_discount) -
                                            floatval($sub_contractor_net_with_discount);
                                        $profit_discount =
                                            floatval($client_discount) - floatval($sub_contractor_discount);
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
                                                    if ($client_final_estimation_id == $estimation->id) {
                                                        $bg_color = 'bg-info';
                                                    }
                                                    if ($sub_contractor_final_estimation_id == $estimation->id) {
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
                                                        class="badge fix_badges bg {{ $estimationStatus[$estimation->status] }} p-2 px-3 rounded">{{ $estimationStatus[$estimation->status] }}</span>
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
                                                                        data-toggle="tooltip" title="{{ __('Delete') }}"><i
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
