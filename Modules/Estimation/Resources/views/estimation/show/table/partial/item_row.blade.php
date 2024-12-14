<tr class="item_row" data-groupid="{{ $estimationGroup->id }}" data-itemID="{{ $product->id }}" data-type="item">
    <td class="column_reorder">
        <i class="fa fa-bars reorder-item"></i>
    </td>

    <td class="column_checkbox">
        <input type="checkbox" class="item_selection" data-id="{{ $product->id }}">
    </td>

    <td class="column_pos">
        <div class="pos-inner"></div>
    </td>

    <td class="column_name">
        <div class="div-desc-toggle">
            <i class="desc_toggle fa fas fa-solid fa-caret-right dt-control"></i>
            <input type="text" name="item[{{ $product->id }}]['name']" class="item-name form-control heading"
                value="{{ $product->name }}">
        </div>
    </td>

    <td class="column_quantity">
        <input type="text" class="form-control item-quantity"
            value="{{ currency_format_with_sym($product->quantity, '', '', false) }}">
    </td>

    <td class="column_unit">
        <input type="text" name="item[{{ $product->id }}]['unit']" class="form-control item-unit"
            value="{{ $product->unit }}">
    </td>

    <td class="column_optional border-right">
        <input type="checkbox" class="item-optional" 
            data-id="{{ $product->id }}" 
            {{ $product->is_optional == 1 ? 'checked' : '' }}>
    </td>

    @foreach ($product->quoteItems as $quoteItem)
        <td class="column_single_price border-left" data-cardQuoteID="{{ $quoteItem->estimate_quote_id }}">
            <div class="d-flex">
                <input type="text" class="form-control item-price"
                    id="cardQuoteSinglePrice"
                    data-cardQuoteSinglePrice="{{ $quoteItem->estimate_quote_id }}"
                    name="cardQuote[][{{ $quoteItem->estimate_quote_id }}]"
                    value="{{ currency_format_with_sym($quoteItem->price) }}">
            </div>
        </td>

        <td class="column_total_price border-right" id="cardQuoteTotalPrice" data-cardQuoteTotalPrice="{{ $quoteItem->estimate_quote_id }}">
            -
        </td>
    @endforeach
</tr>

<tr class="item_child tr_child_description" 
    data-itemID="{{ $product->id }}"
    data-groupid="{{ $estimationGroup->id }}"
    style="display: none;">

    <td colspan="7" class="column_name desc_column border-right">
        <textarea 
            class="description_input w-100 tinyMCE" 
            name="`item[{{ $product->id }}][description]`"
            placeholder="{{ trans('Items Description') }}">{{ $product->description }}</textarea>
    </td>

    @foreach ($product->quoteItems as $quoteItem)
        <td colspan="2" class="border-right text-center">-</td>
    @endforeach
</tr>
