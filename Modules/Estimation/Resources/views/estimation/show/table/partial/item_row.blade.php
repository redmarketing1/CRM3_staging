<tr class="item_row" x-data data-groupid="{{ $estimationGroup->id }}" data-itemID="{{ $product->id }}">
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
            <input type="text" name="item[]['name']" class="item-name form-control" value="{{ $product->name }}">
        </div>
    </td>

    <td class="column_quantity">
        <input type="text" class="form-control item-quantity"
            value="{{ currency_format_with_sym($product->quantity, '', '', false) }}"
            x-on:blur="handleInputBlur($event, 'quantity')">
    </td>

    <td class="column_unit">
        <input type="text" name="item[]['unit']" class="form-control item-unit" value="{{ $product->unit }}">
    </td>

    <td class="column_optional border-right">
        <input type="checkbox" class="item-optional" :checked="items[{{ $product->id }}]?.optional"
            x-on:change="handleOptionalChange($event, {{ $product->id }})" value="{{ $product->id }}">
    </td>

    @foreach ($quoteItems->get($product->id, []) as $quoteItem)
        <td class="column_single_price border-left">
            <div class="d-flex">
                <input type="text" class="form-control item-price"
                    value="{{ currency_format_with_sym($quoteItem->price) }}"
                    x-on:blur="handleInputBlur($event, 'price')">
            </div>
        </td>

        <td class="column_total_price border-right"
            x-text="items[{{ $product->id }}] ? 
               (items[{{ $product->id }}].optional ? '-' : 
               formatCurrency(calculateItemTotal({{ $product->id }})))
: 
               '{{ currency_format_with_sym($product->is_optional ? 0 : $quoteItem->total_price) }}'">
            {{ currency_format_with_sym($product->is_optional ? 0 : $quoteItem->total_price) }}
        </td>
    @endforeach
</tr>
