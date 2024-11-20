@php
    $site_money_format = site_money_format();
@endphp
@if (isset($with_group) && $with_group == true)
    <tr class="group_row group grp_no{{ $product->group->group_pos }}" data-group_pos="{{ $product->group->group_pos }}"
        data-group="{{ $product->group->group_name }}" data-group_id="{{ $product->group_id }}"
        data-parent_id="{{ $product->group->parent_id }}">
        <td class="column_reorder" data-dt-order="disable">
            <i class="fa-solid fa-up-down reorder-item reorder_group_btn"></i>
        </td>
        <td class="column_checkbox grp_checkbox_td" data-dt-order="disable">
            <input type="checkbox" class="group_checkbox" data-group="Group"
                data-group_pos="{{ $product->group->group_pos }}" id="SelectGroupCheckbox" name=""
                value="{{ $product->group_id }}">
        </td>
        <td class="column_pos grouppos">
            {{ $product->group->group_pos }}
        </td>
        <td colspan="4" class="column_name grouptitle border-right">
            <div class="div-desc-toggle">
                <i class="desc_toggle fa fas fa-solid fa-caret-right grp-dt-control"></i>
                <input type="text" class="form-control grouptitle-input" value="{{ $product->group->group_name }}">
            </div>
        </td>
        @if (isset($ai_description_field))
            <td class="column_ai_description border-left-right">
                <div class="ai-result">{{ $product->ai_description }}</div>
            </td>
        @endif
        @foreach ($quote_items[$product->id] as $quoteItem)
            <td class="text-right grouptotal border-left-right" colspan="2"
                data-quote_id="{{ $quoteItem->estimate_quote_id }}" data-group_total="0">
                0
            </td>
        @endforeach
    </tr>
@endif
@php
    $description_hidden =
        '<input type="hidden" class="form-control description_input_' .
        $product->id .
        '" value="' .
        $product->description .
        '">';

    $optional_checked = $product->is_optional == 0 ? 'checked' : '';
@endphp
<tr class="item_row" data-id="{{ $product->id }}" data-group_id="{{ $product->group_id }}"
    data-group_pos="{{ $product->group->group_pos }}" data-type="{{ $product->type }}">
    <td class="column_reorder">
        <i class="fa fa-bars reorder-item"></i>
    </td>
    <td class="column_checkbox">
        <input type="checkbox" name="multi_id" class="item_selection  grp_checkbox{{ $product->group_id }}"
            value="{{ $product->id }}" onchange="selected_quote_items()">
    </td>
    <td class="column_pos">
        <div class="pos-inner">{{ $product->pos }}</div>
        <input type="hidden" class="form-control pos_input_{{ $product->id }}" value="{{ $product->pos }}">
    </td>
    <td class="column_group">{{ $product->group->group_name }}</td>
    @php
        $padding_left = 0;
        $explode_pos = explode('.', $product->pos);
        $count_points = count($explode_pos);
        $new_count = $count_points - 2;
        $padding_left = $new_count * 20;
    @endphp
    <td class="column_name" style="padding-left: {{ $padding_left }}px !important">
        <div class="div-desc-toggle">
            <i class="desc_toggle fa fas fa-solid fa-caret-right dt-control"></i>
            <div class="name-div show div-view">{{ $product->name }}</div>
            <input type="text" name="item[{{ $product->id }}]['name']"
                class="form-control hide edit-view name_input_{{ $product->id }}" value="{{ $product->name }}">
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
        <input type="checkbox" name="optional[]" class="select_optional optional_checkbox_{{ $product->id }}"
            value="{{ $product->id }}" {{ $optional_checked }}>
    </td>
    @if (isset($ai_description_field))
        <td class="column_ai_description border-left-right">
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
                                                                                ? nl2br($cost_data->result_description)
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
                        value="{{ currency_format_with_sym($quoteItem->price, '', '', false) }}">
                    <input type="hidden"
                        class="base_single_price_sc_input_{{ $product->id . '_' . $quoteItem->estimate_quote_id }}"
                        value="{{ $quoteItem->base_price }}">
                    <input type="hidden"
                        class="total_price_sc_input_{{ $product->id . '_' . $quoteItem->estimate_quote_id }} total_price_input{{ $quoteItem->estimate_quote_id }}"
                        value="{{ $quoteItem->total_price }}">
                </div>
            </td>
            <td
                class="column_total_price border-righ quote_th{{ $quoteItem->estimate_quote_id }}t tot_price_{{ $product->id . '_' . $quoteItem->estimate_quote_id }}">
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
