<tr class="group_row group grp_no{{ $estimation_group->group_pos }}" data-group_pos="{{ $estimation_group->group_pos }}"
    data-group="{{ $estimation_group->group_name }}" data-group_id="{{ $estimation_group->id }}"
    data-parent_id="{{ $estimation_group->parent_id }}">
    <td class="column_reorder" data-dt-order="disable">
        <i class="fa-solid fa-up-down reorder-item reorder_group_btn"></i>
    </td>
    <td class="column_checkbox grp_checkbox_td" data-dt-order="disable">
        <input type="checkbox" class="group_checkbox" data-group="Group"
            data-group_pos="{{ $estimation_group->group_pos }}" id="SelectGroupCheckbox" name=""
            value="{{ $estimation_group->id }}">
    </td>
    <td class="column_pos grouppos">
        {{ $estimation_group->group_pos }}
    </td>
    @php
        $padding_left = 0;
        if (isset($nested)) {
            $padding_left = $nested;
        }
    @endphp
    <td colspan="4" class="column_name grouptitle border-right "
        style="padding-left: {{ $padding_left }}px !important">
        <div class="div-desc-toggle">
            <i class="desc_toggle fa fas fa-solid fa-caret-right grp-dt-control"></i>
            <input type="text" class="form-control grouptitle-input" value="{{ $estimation_group->group_name }}">
        </div>
    </td>
    @if (isset($ai_description_field))
        <td class="column_ai_description border-left-right" data-dt-order="disable"></td>
    @endif
    @if (isset($allQuotes) && count($allQuotes) > 0)
        @foreach ($allQuotes as $quote)
            <td class="text-right grouptotal border-left-right quote_th{{ $quote->id }}" colspan="2"
                data-quote_id="{{ $quote->id }}" data-group_total="0">
                0
            </td>
        @endforeach
    @endif
</tr>


@if (isset($estimation_group->estimation_products) && count($estimation_group->estimation_products) > 0)
    @foreach ($estimation_group->estimation_products as $product)
        @php
            $optional_checked = $product->is_optional == 0 ? 'checked' : '';
            $comment =
                '<input type="text" class="form-control comment_input_box mr-2 comment_input_' .
                $product->id .
                '" value="' .
                $product->comment .
                '">';
            $read_only = '';
        @endphp
        @if ($product->type == 'item')
            <tr class="item_row" data-id="{{ $product->id }}" data-group_id="{{ $product->group_id }}"
                data-group_pos="{{ $product->group->group_pos }}" data-type="{{ $product->type }}">
                <td class="column_reorder">
                    <i class="fa fa-bars reorder-item"></i>
                </td>
                <td class="column_checkbox">
                    <input type="checkbox" name="multi_id" class="item_selection grp_checkbox{{ $product->group_id }}"
                        value="{{ $product->id }}" onchange="selected_quote_items()">
                </td>
                <td class="column_pos">
                    <div class="pos-inner">{{ $product->pos }}</div>
                    <input type="hidden" class="form-control pos_input_{{ $product->id }}"
                        value="{{ $product->pos }}">
                </td>
                <td class="column_name" style="padding-left: {{ $padding_left }}px !important">
                    <div class="div-desc-toggle">
                        <i class="desc_toggle fa fas fa-solid fa-caret-right dt-control"></i>
                        <input type="text" name="item[{{ $product->id }}]['name']"
                            class="form-control edit-view name_input_{{ $product->id }}"
                            value="{{ $product->name }}">
                    </div>
                </td>
                <td class="column_quantity">
                    <input type="text" name="item[{{ $product->id }}]['quantity']"
                        class="form-control row_qty quantity_input_{{ $product->id }}" oninput="validateInput(this)"
                        value="{{ currency_format_with_sym($product->quantity, '', '', false) }}">
                </td>
                <td class="column_unit">
                    <input type="text" name="item[{{ $product->id }}]['unit']"
                        class="form-control unit_input_{{ $product->id }}" value="{{ $product->unit }}">
                </td>
                <td class="column_optional border-right">
                    <input type="checkbox" name="optional[]"
                        class="select_optional optional_checkbox_{{ $product->id }}" value="{{ $product->id }}"
                        {{ $optional_checked }}>
                </td>
                @if (isset($ai_description_field))
                    <td class="column_ai_description ai-content border-left-right">
                        <div class="ai-result">{{ $product->ai_description }}</div>
                    </td>
                @endif
                @if (isset($quote_items[$product->id]))
                    @foreach ($quote_items[$product->id] as $quoteItem)
                        @php
                            $material_cost_final_value = '';
                            $smart_template_data = isset($quoteItem->smart_template_data)
                                ? json_decode($quoteItem->smart_template_data)
                                : [];

                            $item_total = $product->is_optional == 0 ? 0 : $quoteItem->total_price;
                            if (!empty($queues_result) && count($queues_result) > 0) {
                                foreach ($queues_result['estimation_queues_list'] as $qrow) {
                                    if (
                                        $qrow['completed_percentage'] >= 0 &&
                                        $qrow['completed_percentage'] < 100 &&
                                        $quoteItem->estimate_quote_id == $qrow['quote_id']
                                    ) {
                                        $read_only = 'readonly';
                                    }
                                }
                            }
                        @endphp
                        <td class="column_single_price border-left quote_th{{ $quoteItem->estimate_quote_id }}"
                            data-quote="{{ $quoteItem->estimate_quote_id }}">
                            <div class="d-flex">
                                @if (isset($quoteItem->quote->is_ai) && $quoteItem->quote->is_ai == 1 && !empty($smart_template_data))
                                    <span class="CellWithComment">
                                        <i class="fa fa-info-circle"></i>
                                        <span class="CellComment">
                                            <table>
                                                <tr class="final-value-tr">
                                                    <td>
                                                        <div class="sb-final-results">
                                                            @foreach ($smart_template_data->result as $costs)
                                                                {{ $costs->label }}: {{ $costs->value }} <br>
                                                                <div class="sb-all-results">
                                                                    @if (isset($costs->details) && count($costs->details) > 0)
                                                                        @foreach ($costs->details as $cost_data)
                                                                            <div class="result-outer">
                                                                                <div class="result-inner">
                                                                                    <div class="result-nr">
                                                                                        {{ $cost_data->result_number }}
                                                                                    </div>
                                                                                    @php
                                                                                        $result_desc = isset(
                                                                                            $cost_data->result_description,
                                                                                        )
                                                                                            ? nl2br(
                                                                                                $cost_data->result_description,
                                                                                            )
                                                                                            : '';
                                                                                    @endphp
                                                                                    <div class="result-description">
                                                                                        {!! $result_desc !!}
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        @endforeach
                                                                    @endif
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </td>
                                                </tr>
                                            </table>
                                        </span>
                                    </span>
                                @endif

                                <input type="text"
                                    class="form-control row_price single_price_sc{{ $quoteItem->estimate_quote_id }} single_price_sc_input_{{ $product->id . '_' . $quoteItem->estimate_quote_id }}"
                                    oninput="validateInput(this)" data-id="{{ $quoteItem->estimate_quote_id }}"
                                    data-item_id="{{ $product->id }}"
                                    value="{{ currency_format_with_sym($quoteItem->price, '', '', false) }}"
                                    {{ $read_only }}>
                                <input type="hidden"
                                    class="base_single_price_sc_input_{{ $product->id . '_' . $quoteItem->estimate_quote_id }}"
                                    value="{{ $quoteItem->base_price }}">
                                <input type="hidden"
                                    class="total_price_sc_input_{{ $product->id . '_' . $quoteItem->estimate_quote_id }} total_price_input{{ $quoteItem->estimate_quote_id }}"
                                    value="{{ $quoteItem->total_price }}">
                            </div>
                        </td>
                        <td class="column_total_price border-right quote_th{{ $quoteItem->estimate_quote_id }} tot_price_{{ $product->id . '_' . $quoteItem->estimate_quote_id }}"
                            data-quote="{{ $quoteItem->estimate_quote_id }}">
                            {{ currency_format_with_sym($item_total) }}
                        </td>
                    @endforeach
                @endif
            </tr>
            @include('taskly::project_estimations.description_row', [
                'product' => $product,
                'ai_description_field' => $ai_description_field,
                'quote_items' => $quote_items,
            ])
        @else
            <tr class="item_row comment_row" data-id="{{ $product->id }}" data-group_id="{{ $product->group_id }}"
                data-group_pos="{{ $product->group->group_pos }}" data-type="{{ $product->type }}">
                <td class="column_reorder"><i class="fa fa-bars reorder-item"></i></td>
                <td class="column_checkbox">
                    <input type="checkbox" name="multi_id"
                        class="item_selection  grp_checkbox{{ $product->group_id }}" value="{{ $product->id }}"
                        onchange="selected_quote_items()">
                </td>
                <td class="column_pos">
                    <div class="pos-inner">{{ $product->pos }}</div><input type="hidden"
                        class="form-control pos_input_{{ $product->id }}" value="{{ $product->pos }}">
                </td>
                <td colspan="4" class="border-right column_name"
                    style="padding-left: {{ $padding_left }}px !important">
                    <input type="text"
                        class="form-control comment_input_box mr-2 comment_input_{{ $product->id }}"
                        value="{{ $product->comment }}">
                </td>
                @if (isset($ai_description_field))
                    <td class="column_ai_description ai-content border-left-right">
                        <div class="ai-result">{{ $product->ai_description }}</div>
                    </td>
                @endif
                @if (isset($quote_items[$product->id]))
                    @foreach ($quote_items[$product->id] as $quoteItem)
                        <td class="column_single_price border-left">-</td>
                        <td class="column_total_price border-right">-</td>
                    @endforeach
                @endif
            </tr>
        @endif
    @endforeach
@endif
@if ($estimation_group->children_data->isNotEmpty())
    @foreach ($estimation_group->children_data as $child)
        @include('taskly::project_estimations.partial_setup', [
            'estimation_group' => $child,
            'ai_description_field' => $ai_description_field,
            'allQuotes' => $allQuotes,
            'nested' => 0,
        ])
    @endforeach
@endif
