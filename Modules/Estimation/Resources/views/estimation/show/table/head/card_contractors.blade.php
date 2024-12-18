<tr class="total-line-top" id="contractors_row">
    <th colspan="7" class="toplabel buttons-top">
        @include('estimation::estimation.show.table.head.tools')
    </th>

    @if (isset($ai_description_field))
        <th id=""
            class="total-main-title total-company-title border-left-right text-nowrap column_ai_description"
            data-orderable="false">
            <span>{{ isset($desc_template->title) ? $desc_template->title : '' }}</span>
            @if (!empty($queues_result) && count($queues_result) > 0)
                <div class="row m-1 ai-progress-bar">
                    @foreach ($queues_result['estimation_queues_list'] as $qrow)
                        @if ($qrow['completed_percentage'] >= 0 && $qrow['completed_percentage'] < 100 && $qrow['type'] == 0)
                            <div class="col-md-12 project_block" data-id="{{ $estimation->project_id }}">
                                <div class="form-group estimation_block" data-id="{{ $estimation->id }}">
                                    <div class="progress queue_progress" data-id="{{ $qrow['smart_template_id'] }}">
                                        <div class="progress-bar bg-success" role="progressbar"
                                            style="width: {{ $qrow['completed_percentage'] }}%"
                                            aria-valuenow="{{ $qrow['completed_percentage'] }}" aria-valuemin="0"
                                            aria-valuemax="100">
                                            {{ $qrow['completed_percentage'] }}%
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif
        </th>
    @endif

    @foreach ($allQuotes as $key => $quotes)
        <th colspan="2" data-orderable="false" data-cardQuoteID="{{ $quotes->id }}"
            class="total-main-title text-center">

            <div class="font-bold text-lg">
                <span> {{ $quotes->title ?? $quotes->subContractor->name }} </span>
                <div class="company-total-settings">
                    <i class="ti ti-settings float-end" id="dropdownMenuButton{{ $quotes->id }}"
                        data-bs-toggle="dropdown" aria-expanded="false"></i>
                    <ul class="dropdown-menu quote_options{{ $quotes->id }}"
                        aria-labelledby="dropdownMenuButton{{ $quotes->id }}">

                        @permission('estimation duplicate quote option')
                            <li>
                                <a class="dropdown-item" href="javascript:void(0)"
                                    data-url="{{ route('estimation.duplicateQuoteCard', ['id' => $quotes->id, 'mode' => 'duplicate']) }}"
                                    data-ajax-popup="true" data-toggle="tooltip"
                                    data-title="{{ trans('Duplicate Card Quate') }}">
                                    <i class="fa-regular fa-copy"></i>
                                    {{ __('Duplicate') }}
                                </a>
                            </li>
                        @endpermission

                        @if (auth()->user()->type == 'company')
                            <li>
                                <a class="dropdown-item" href="javascript:void(0)" id="edit-clone-quate-modal"
                                    data-id="edit-clone-quate-modal"
                                    data-url="{{ route('estimation.duplicateQuoteCard', ['id' => $quotes->id, 'mode' => 'edit', 'userID' => $quotes->user_id, 'title' => $quotes->title]) }}"
                                    data-ajax-popup="true" data-toggle="tooltip"
                                    data-title="{{ trans('Change Name or Contract') }}">
                                    <i class="fa-solid fa-pencil"></i>
                                    {{ trans('Change Name or Contract') }}
                                </a>
                            </li>
                        @endif

                        @permission('estimation delete')
                            <li>
                                <a class="dropdown-item" href="javascript:void(0)" id="delete-quate"
                                    data-id="{{ $quotes->id }}">
                                    <i class="fa-regular fa-trash-can"></i>
                                    {{ __('Delete') }}
                                </a>
                            </li>
                        @endpermission

                        @if (auth()->user()->type == 'company')
                            <li>
                                <label class="dropdown-item gap-2">
                                    <input type="checkbox"
                                        id="QuateTypesStatus"
                                        data-id="{{ $quotes->id }}"
                                        data-type="quote">
                                    {{ __('Client Quote') }}
                                </label>
                            </li>

                            <li>
                                <label class="dropdown-item gap-2">
                                    <input type="checkbox" 
                                        id="QuateTypesStatus"
                                        data-id="{{ $quotes->id }}"
                                        data-type="clientQuote">
                                    {{ __('Final Estimation for Client') }}
                                </label>
                            </li>

                            <li>
                                <label class="dropdown-item gap-2">
                                    <input type="checkbox"
                                        id="QuateTypesStatus"
                                        data-id="{{ $quotes->id }}"
                                        data-type="subcontractor">
                                    {{ __('Final Estimation for Subcontractor') }}
                                </label>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>

            @if (!empty($queues_result) && count($queues_result) > 0)
                <div class="row m-1 ai-progress-bar">
                    @foreach ($queues_result['estimation_queues_list'] as $qrow)
                        @if (
                            $qrow['completed_percentage'] >= 0 &&
                                $qrow['completed_percentage'] < 100 &&
                                $quote_title == $qrow['smart_template_main_title']
                        )
                            @php
                                $hide_options = 'hide';
                                $progress_class = 'bg-success';
                                $info_icon = 'd-none';
                                if ($qrow['cancelled_record'] > 0 || $qrow['error_record'] > 0) {
                                    $progress_class = 'bg-danger';
                                    $info_icon = '';
                                }
                            @endphp
                            <div class="col-md-12 project_block" data-id="{{ $estimation->project_id }}">
                                <div class="form-group estimation_block" data-id="{{ $estimation->id }}">
                                    <div class="progress queue_progress" data-id="{{ $qrow['smart_template_id'] }}">
                                        <div class="progress-bar {{ $progress_class }}" role="progressbar"
                                            style="width: {{ $qrow['completed_percentage'] }}%"
                                            aria-valuenow="{{ $qrow['completed_percentage'] }}" aria-valuemin="0"
                                            aria-valuemax="100">
                                            {{ $qrow['completed_percentage'] }}%
                                        </div>
                                    </div>
                                </div>
                                <span class="CellWithComment {{ $info_icon }}">
                                    <i class="fa fa-info-circle "></i>
                                    <span class="CellComment">{{ $qrow['error_message'] }}</span>
                                </span>
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif
        </th>
    @endforeach
</tr>
