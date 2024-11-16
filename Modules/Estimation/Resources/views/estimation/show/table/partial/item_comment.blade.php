<tr class="item_row comment_row">
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

    <td colspan="4" class="border-right column_name">
        <input type="text" class="form-control mr-2" value="{{ $product->comment }}">
    </td>

    @if (isset($quote_items[$product->id]))
        @foreach ($quote_items[$product->id] as $quoteItem)
            <td class="column_single_price border-left">-</td>
            <td class="column_total_price border-right">-</td>
        @endforeach
    @endif
</tr>
