<tr class="item_comment" x-data data-groupid="{{ $estimationGroup->id }}" data-commentID="{{ $product->id }}"
    data-type="comment">
    <td class="column_reorder">
        <i class="fa fa-bars reorder-item"></i>
    </td>

    <td class="column_checkbox">
        <input type="checkbox" class="item_selection" value="{{ $product->id }}">
    </td>

    <td class="column_pos">
        <div class="pos-inner"></div>
    </td>

    <td colspan="4" class="border-right column_name">
        <input type="text" class="form-control mr-2 item-description" name="item[{{ $product->id }}]['group']"
            value="{{ $product->comment }}">
    </td>

    @foreach ($allQuotes as $quote)
        <td class="column_single_price border-left" data-cardQuoteID="{{ $quote->id }}">-</td>
        <td class="column_total_price border-right" data-cardQuoteID="{{ $quote->id }}">-</td>
    @endforeach
</tr>
