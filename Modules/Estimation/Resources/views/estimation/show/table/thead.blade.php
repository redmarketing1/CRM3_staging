<thead>
    <tr class="total-line-top no-sort" id="net_with_discount_row">
        <th colspan="4" class="toplabel buttons-top" data-dt-order="disable">
        </th>

        <th colspan="3" class="toplabel total-net-discount" data-dt-order="disable">
            {{ __('Net incl. Discount') }}
        </th>

        @if (isset($ai_description_field))
            <th id="" rowspan="5" class="border-left-right toptotal ai-head column_ai_description"
                data-dt-order="disable">
                <button type="button" class="btn_replace_descriptions d-none"
                    data-bs-whatever="{{ __('Replace with AI description') }}">
                    <!-- <i class="fa-solid fa-retweet"></i><br> -->
                    {{ __('Replace current Descriptions') }}
                </button>
            </th>
        @endif

        @foreach ($total_prices['net_with_discount'] as $net_with_discount_key => $net_with_discount)
            <th colspan="2" id="{{ $net_with_discount_key }}_neuwest"
                class="totalnr {{ $net_with_discount_key }} toptotal total-net-discount border-left-right"
                data-dt-order="disable">
                <div id="{{ $net_with_discount_key }}" class="">
                    {{ currency_format_with_sym($net_with_discount) }}
                </div>
                <input type="hidden" id="{{ $net_with_discount_key }}_value" name="{{ $net_with_discount_key }}"
                    value="{{ $net_with_discount }}">
            </th>
        @endforeach
    </tr>

    <tr class="total-line-top no-fixed-header no-sort" id="gross_with_discount_row">

        <th colspan="4" rowspan="4" class="toplabel buttons-top" data-dt-order="disable">
        </th>

        <th colspan="3" class="toplabel total-gross-discount" data-dt-order="disable">
            {{ __('Gross incl. Discount') }}
        </th>

        @foreach ($total_prices['gross_with_discount'] as $gross_with_discount_key => $gross_with_discount)
            <th colspan="2" id="{{ $gross_with_discount_key }}_neuwest"
                class="totalnr {{ $gross_with_discount_key }} toptotal total-gross-discount border-left-right"
                data-dt-order="disable">
                <div id="{{ $gross_with_discount_key }}" class="">
                    {{ currency_format_with_sym($gross_with_discount) }}
                </div>
                <input type="hidden" id="{{ $gross_with_discount_key }}_value" name="{{ $gross_with_discount_key }}"
                    value="{{ $gross_with_discount }}">
            </th>
        @endforeach
    </tr>


    <tr class="total-line-top no-fixed-header no-sort" id="net_row1">
        <th colspan="3" class="toplabel total-net" data-dt-order="disable">
            {{ __('Net') }}
        </th>

        @foreach ($total_prices['net'] as $net_key => $net)
            <th colspan="2" id="{{ $net_key }}_neuwest"
                class="totalnr {{ $net_key }} toptotal total-net border-left-right" data-dt-order="disable">
                <div id="{{ $net_key }}" class="">
                    {{ currency_format_with_sym($net) }}
                </div>
                <input type="hidden" id="{{ $net_key }}_value" name="{{ $net_key }}"
                    value="{{ $net }}">
            </th>
        @endforeach
    </tr>

    <tr class="total-line-top no-fixed-header" id="gross_row">
        <th colspan="3" class="toplabel total-gross no-sort" data-dt-order="disable">
            {{ __('Gross (incl. VAT)') }}
        </th>

        @php
            $tax = $total_prices['tax'];
            $gross = $total_prices['gross'];
        @endphp

        @foreach ($allQuotes as $key => $quotes1)
            @php
                $tax_key = 'tax_sc' . $quotes1->id;
                $gross_key = 'gross_sc' . $quotes1->id;
            @endphp
            <th colspan="2"
                class="toptotal total-gross border-left-right finalize_quote{{ $quotes1->id }} gross_tax_sc{{ $quotes1->id }}"
                data-dt-order="disable">
                <span class="dt-column-title">
                    <div id="{{ $tax_key }}_neuwest"
                        class="totalnr {{ $tax_key }} toptotal total-discount total-vat-input">
                        <select name="tax[{{ $tax_key }}]" id="{{ $tax_key }}">
                            <option value="19" {{ $tax[$tax_key] == 19 ? 'selected' : '' }}>
                                19%
                            </option>
                            <option value="0" {{ $tax[$tax_key] == 0 ? 'selected' : '' }}>0%
                            </option>
                        </select>
                    </div>
                    <div id="{{ $gross_key }}_neuwest"
                        class="totalnr {{ $gross_key }}totalsetting total-gross-total">
                        <div id="{{ $gross_key }}" class="">
                            {{ currency_format_with_sym($gross[$gross_key]) }}
                        </div>
                        <input type="hidden" id="{{ $gross_key }}_value" name="{{ $gross_key }}"
                            value="{{ $gross[$gross_key] }}">
                    </div>
                </span>
            </th>
        @endforeach
    </tr>

    <tr class="total-line-top totalsetting no-fixed-header" id="discount_row">
        <th colspan="3" class="toplabel total-discount" data-dt-order="disable"></th>

        @php
            $markup = $total_prices['markup'];
            $discount = $total_prices['discount'];
        @endphp
        @foreach ($allQuotes as $key => $quotes1)
            @php
                $markup_key = 'markup_sc' . $quotes1->id;
                $discount_key = 'discount_sc' . $quotes1->id;
                $type = 'quote';
            @endphp
            <th colspan="2" data-dt-order="disable"
                class="total-settings border-left-right finalize_quote{{ $quotes1->id }} markup_discount_sc{{ $quotes1->id }}">
                <span class="dt-column-title">
                    <div id="{{ $markup_key }}_neuwest" class="totalnr {{ $markup_key }} toptotal total-markup">
                        @if (auth()->user()->type != 'company')
                            <input type="hidden" id="{{ $markup_key }}" name="markup[{{ $markup_key }}]"
                                data-old="{{ $markup[$markup_key]->markup }}"
                                value="{{ currency_format_with_sym($markup[$markup_key]->markup, '', '', false) }}"
                                class="form-control"
                                onfocusout="calculateMarkup(this,'{{ $quotes1->id }}','{{ $type }}')">
                        @else
                            <div class="total-setting-label">
                                {{ __('Markup') }}
                            </div>
                            <div class="total-markup-input">
                                <input type="text" id="{{ $markup_key }}" name="markup[{{ $markup_key }}]"
                                    data-old="{{ $markup[$markup_key]->markup }}"
                                    value="{{ currency_format_with_sym($markup[$markup_key]->markup, '', '', false) }}"
                                    class="form-control"
                                    onfocusout="calculateMarkup(this,'{{ $quotes1->id }}','{{ $type }}')">
                            </div>
                        @endif
                    </div>
                    <div id="{{ $discount_key }}_neuwest" class="totalnr {{ $discount_key }}toptotal total-markup">
                        <div class="total-setting-label">
                            {{ __('Cash Discount') }}
                        </div>
                        <div class="total-discount-input">
                            <input type="text" id="{{ $discount_key }}" name="discount[{{ $discount_key }}]"
                                value="{{ $discount[$discount_key] }}" class="form-control">
                        </div>
                    </div>
                </span>
            </th>
        @endforeach
    </tr>

    <tr class="total-line-top" id="contractors_row">
        <th colspan="4" class="toplabel buttons-top" data-dt-order="disable">
            <input type="text" placeholder="{{ __('Search') }}..." id="table-search">
            <input type="hidden" name="" id="remove_item_ids" value="">
            <input type="hidden" name="" id="remove_group_ids" value="">
            <input type="hidden" name="" id="duplicate_item_ids" value="">
            <input type="hidden" name="" id="duplicate_group_ids" value="">
            <span class="btn-separator"></span>
            <button id="update_pos_btn" type="button" data-bs-toggle="tooltip"
                title="{{ __('Update POS Numbers') }}"><i class="fa-solid fa-list-ol"></i></button>
            <!-- <button class="reorder_group_btn" type="button"><i class="fa-solid fa-list"></i> {{ __('Reorder Group') }}</button> -->
            @if (auth()->user()->type == 'company')
                @permission('estimation download option')
                    <div class="dropdown download-dropdown">
                        <span data-bs-toggle="tooltip" title="{{ __('Download') }}">
                            <a class="dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button"
                                aria-haspopup="false" aria-expanded="false">
                                <i class="fa-solid fa-download"></i>
                            </a>
                            <div class="dropdown-menu dash-h-dropdown">
                                <a href="{{ route('estimation.export.excel', ['id' => \Crypt::encrypt($estimation->id), 'type' => 'download']) }}"
                                    target="_blank" class="dropdown-item"><span>{{ __('Excel') }}</span></a>
                                <a href="{{ route('estimation.export.csv', ['id' => \Crypt::encrypt($estimation->id), 'type' => 'download']) }}"
                                    target="_blank" class="dropdown-item"><span>{{ __('CSV') }}</span></a>
                                <a href="{{ route('estimation.export.gaeb', ['id' => \Crypt::encrypt($estimation->id), 'type' => 'download']) }}"
                                    target="_blank" class="dropdown-item"><span>{{ __('GAEB') }}</span></a>
                            </div>
                        </span>
                    </div>
                @endpermission
                <div class="dropdown column-dropdown">
                    <span data-bs-toggle="tooltip" title="{{ __('Show / Hide Columns') }}">
                        <a class="dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button"
                            aria-haspopup="false" aria-expanded="false">
                            <i class="fa-solid fa-table-columns"></i>
                        </a>
                        <div class="dropdown-menu dash-h-dropdown">
                            <h6>{{ __('Show / Hide Columns') }}</h6>
                            <label class="dropdown-item">
                                <input type="checkbox" class="column-toggle" data-column="column_pos" checked>
                                <span>{{ __('Pos') }}</span>
                            </label>
                            {{-- <label class="dropdown-item">
																			<input type="checkbox" class="column-toggle" data-column="column_name" checked>
																			<span>{{ __('Name') }}</span>
																		</label> --}}
                            <label class="dropdown-item">
                                <input type="checkbox" class="column-toggle" data-column="column_quantity" checked>
                                <span>{{ __('Quantity') }}</span>
                            </label>
                            <label class="dropdown-item">
                                <input type="checkbox" class="column-toggle" data-column="column_unit" checked>
                                <span>{{ __('Unit') }}</span>
                            </label>
                            <label class="dropdown-item">
                                <input type="checkbox" class="column-toggle" data-column="column_optional" checked>
                                <span>{{ __('Opt') }}</span>
                            </label>
                            @if (isset($ai_description_field))
                                <label class="dropdown-item">
                                    <input type="checkbox" class="column-toggle" data-column="column_ai_description"
                                        checked>
                                    <span>{{ __('Auto Description') }}</span>
                                </label>
                            @endif
                            @foreach ($allQuotes as $key => $quotes)
                                @php
                                    $quote_title = isset($quotes->subContractor->name)
                                        ? $quotes->subContractor->name
                                        : $quotes->title;
                                @endphp
                                <label class="dropdown-item">
                                    <input type="checkbox" class="column-toggle" data-column="quote_th"
                                        data-quote="{{ $quotes->id }}" checked>
                                    <span>{{ $quote_title }}</span>
                                </label>
                            @endforeach
                        </div>
                    </span>
                </div>
            @endif
        </th>
        <th colspan="3" data-orderable="false" data-dt-order="disable" class="toplabel markup_discount_th"></th>
        @if (isset($ai_description_field))
            <th id=""
                class="total-main-title total-company-title border-left-right text-nowrap column_ai_description"
                data-orderable="false" data-dt-order="disable">
                <span>{{ isset($desc_template->title) ? $desc_template->title : '' }}</span>
                @if (!empty($queues_result) && count($queues_result) > 0)
                    <div class="row m-1 ai-progress-bar">
                        @foreach ($queues_result['estimation_queues_list'] as $qrow)
                            @if ($qrow['completed_percentage'] >= 0 && $qrow['completed_percentage'] < 100 && $qrow['type'] == 0)
                                <div class="col-md-12 project_block" data-id="{{ $estimation->project_id }}">
                                    <div class="form-group estimation_block" data-id="{{ $estimation->id }}">
                                        <div class="progress queue_progress"
                                            data-id="{{ $qrow['smart_template_id'] }}">
                                            <div class="progress-bar bg-success" role="progressbar"
                                                style="width: {{ $qrow['completed_percentage'] }}%"
                                                aria-valuenow="{{ $qrow['completed_percentage'] }}" aria-valuemin="0"
                                                aria-valuemax="100">
                                                {{ $qrow['completed_percentage'] }}%</div>
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
            <th colspan="2" data-orderable="false"
                class="total-main-title total-company-title border-left-right finalize_quote_title{{ $quotes->id }} title_sc{{ $quotes->id }} {{ $quotes->title }}"
                data-dt-order="disable">
                @php
                    //	$quote_title = $quotes->title;
                    $quote_title = isset($quotes->subContractor->name) ? $quotes->subContractor->name : $quotes->title;
                    // if($key == 0) {
                    // 	$quote_title = $company_name;
                    // }
                    $hide_options = '';
                @endphp
                <div class="total-company-title-inner">
                    <span class="sc{{ $quotes->id }}">
                        {{ $quote_title }}
                    </span>
                    <div class="company-total-settings {{ $hide_options }}">
                        <i class="ti ti-settings float-end" id="dropdownMenuButton{{ $quotes->id }}"
                            data-bs-toggle="dropdown" aria-expanded="false"></i>
                        <ul class="dropdown-menu quote_options{{ $quotes->id }}"
                            aria-labelledby="dropdownMenuButton{{ $quotes->id }}">
                            @permission('estimation duplicate quote option')
                                <li>
                                    <a class="dropdown-item" href="javascript:void(0)"
                                        onclick="duplicateColumn('{{ $quotes->id }}','{{ $quotes->title }}','{{ $quotes->user_id }}','{{ $quotes->markup }}',false,'{{ $type }}')"><i
                                            class="fa-regular fa-copy"></i>{{ __('Duplicate') }}</a>
                                </li>
                            @endpermission
                            @if (auth()->user()->type == 'company')
                                @if ($key > 0)
                                    <li><a class="dropdown-item" href="javascript:void(0)"
                                            onclick="duplicateColumn('{{ $quotes->id }}','{{ $quotes->title }}','{{ $quotes->user_id }}','{{ $quotes->markup }}',true)"><i
                                                class="fa-solid fa-pencil"></i>{{ __('Change Name or Contact') }}</a>
                                    </li>
                                @endif
                            @endif
                            @if ($quotes->is_clone)
                                <li class="delete"><a class="dropdown-item" href="javascript:void(0)"
                                        onclick="deleteColumn('{{ $quotes->id }}',this)"><i
                                            class="fa-regular fa-trash-can"></i>{{ __('Delete') }}</a>
                                </li>
                            @endif
                            @if (auth()->user()->type == 'company')
                                <li>
                                    <label class="dropdown-item">
                                        <input type="checkbox" id="final_quote_checkbox"
                                            onchange="handleCheckboxChange(this, '{{ $quotes->id }}')">
                                        {{ __('Client Quote') }}
                                    </label>
                                </li>

                                <li>
                                    <label class="dropdown-item">
                                        <input type="checkbox" id="client_quote_checkbox"
                                            onchange="handleCheckboxChange(this, '{{ $quotes->id }}', 'client')">
                                        {{ __('Final Estimation for Client') }}
                                    </label>
                                </li>

                                <li>
                                    <label class="dropdown-item">
                                        <input type="checkbox" id="sub_contractor_quote_checkbox"
                                            onchange="handleCheckboxChange(this, '{{ $quotes->id }}', 'sub_contractor')">
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
                                        <div class="progress queue_progress"
                                            data-id="{{ $qrow['smart_template_id'] }}">
                                            <div class="progress-bar {{ $progress_class }}" role="progressbar"
                                                style="width: {{ $qrow['completed_percentage'] }}%"
                                                aria-valuenow="{{ $qrow['completed_percentage'] }}"
                                                aria-valuemin="0" aria-valuemax="100">
                                                {{ $qrow['completed_percentage'] }}%</div>
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

                <input type="hidden" class="sc{{ $quotes->id }}_value" value="{{ $quote_title }}">
                @php
                    if (auth()->user()->type != 'company') {
                        $hide_options = 'hide';
                    }
                @endphp
            </th>
        @endforeach
    </tr>

    <tr id="estimation-edit-table-thead">
        <th class="column_reorder" data-dt-order="disable">
        </th>

        <th class="column_checkbox" data-dt-order="disable">
            <input type="checkbox" class="SelectAllCheckbox" name="" value="">
        </th>

        <th class="column_pos" data-dt-order="disable">
            {{ __('Pos') }}
        </th>

        <th class="column_group" data-dt-order="disable">
            {{ __('Group Name') }}
        </th>

        <th class="column_name" data-dt-order="disable">
            <i class="fa-solid fa-indent expand_more show_all"></i>
            {{ __('Name') }}
        </th>

        <th class="column_quantity" data-dt-order="disable">
            {{ __('Quantity') }}
        </th>

        <th class="column_unit" data-dt-order="disable">
            {{ __('Unit') }}
        </th>

        <th class="column_optional border-right" data-dt-order="disable">{{ __('Opt') }}
        </th>

        @if (isset($ai_description_field))
            <th class="column_ai_description border-left-right" data-dt-order="disable">
                {{ __('Auto Description') }}
            </th>
        @endif

        @foreach ($allQuotes as $key => $quotes)
            <th class="column_single_price border-left quote_th{{ $quotes->id }}" data-dt-order="disable">
                {{ __('Single Price') }}
            </th>
            <th class="column_total_price border-right quote_th{{ $quotes->id }}" data-dt-order="disable">
                {{ __('Total Price') }}
            </th>
        @endforeach
    </tr>
</thead>
