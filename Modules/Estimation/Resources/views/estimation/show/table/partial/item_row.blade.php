<tr class="item_row" data-itemID="{{ $product->id }}">
    <td class="column_reorder">
        <i class="fa fa-bars reorder-item"></i>
    </td>

    <td class="column_checkbox">
        <input type="checkbox" name="multi_id" class="item_selection" value="{{ $product->id }}">
    </td>

    <td class="column_pos">
        <div class="pos-inner">
            {{ $product->pos }}
        </div>
    </td>

    <td class="column_name">
        <div class="div-desc-toggle">
            <i class="desc_toggle fa fas fa-solid fa-caret-right dt-control"></i>
            <input type="text" name="item[]['name']" class="form-control" value="{{ $product->name }}">
        </div>
    </td>

    <td class="column_quantity">
        <input type="text" name="item[]['quantity']" class="form-control row_qty"
            value="{{ currency_format_with_sym($product->quantity, '', '', false) }}">
    </td>

    <td class="column_unit">
        <input type="text" name="item[]['unit']" class="form-control" value="{{ $product->unit }}">
    </td>

    <td class="column_optional border-right">
        <input type="checkbox" name="optional[]" class="select_optional" value="{{ $product->id }}"
            {{ $product->is_optional == 0 ? 'checked' : '' }}>
    </td>

    @foreach ($quoteItems->get($product->id, []) as $quoteItem)
        <td class="column_single_price border-left">
            <div class="d-flex">
                <input type="text" class="form-control row_price"
                    value="{{ currency_format_with_sym($quoteItem->price, '', '', false) }}">
            </div>
        </td>

        <td class="column_total_price border-right">
            {{ currency_format_with_sym($product->is_optional ? $quoteItem->total_price : 0) }}
        </td>
    @endforeach
</tr>
