<tr class="item_row" x-data data-groupid="{{ $estimationGroup->id }}" data-itemID="{{ $product->id }}" data-type="item">
    <td class="column_reorder">
        <i class="fa fa-bars reorder-item"></i>
    </td>

    <td class="column_checkbox">
        <input type="checkbox" name="multi_id" class="item_selection" value="{{ $product->id }}">
    </td>

    <td class="column_pos">
        <div class="pos-inner"></div>
    </td>

    <td class="column_name">
        <div class="div-desc-toggle">
            <i class="desc_toggle fa fas fa-solid fa-caret-right dt-control"></i>
            <input type="text" name="item[{{ $product->id }}]['name']" class="item-name form-control heading"
                value="{{ $product->name }}" x-on:blur="handleInputBlur($event, 'item')">
        </div>
    </td>

    <td class="column_quantity">
        <input type="text" class="form-control item-quantity"
            value="{{ currency_format_with_sym($product->quantity, '', '', false) }}"
            x-on:blur="handleInputBlur($event, 'quantity', '{{ $product->id }}')">
    </td>

    <td class="column_unit">
        <input type="text" name="item[{{ $product->id }}]['unit']" class="form-control item-unit"
            value="{{ $product->unit }}" x-on:blur="handleInputBlur($event, 'unit')">
    </td>

    <td class="column_optional border-right">
        <input type="checkbox" class="item-optional" :checked="items[{{ $product->id }}]?.optional"
            x-on:change="handleOptionalChange($event, {{ $product->id }})" value="{{ $product->id }}">
    </td>

    @foreach ($quoteItems->get($product->id, []) as $quoteItem)
        <td class="column_single_price border-left" data-cardQuoteID="{{ $quoteItem->estimate_quote_id }}">
            <div class="d-flex">
                <input type="text" class="form-control item-price" name="cardQuote[][{{ $quoteItem->estimate_quote_id }}]"
                    value="{{ currency_format_with_sym($quoteItem->price) }}"
                    x-on:blur="handleInputBlur($event, 'price')">
            </div>
        </td>

        <td class="column_total_price border-right" data-cardQuoteID="{{ $quoteItem->estimate_quote_id }}"
            x-text="items[{{ $product->id }}].optional ? '-' : formatCurrency(calculateItemTotal({{ $product->id }}))">
        </td>
    @endforeach
</tr>
