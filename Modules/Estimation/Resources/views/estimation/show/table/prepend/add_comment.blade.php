<tr class="item_comment" data-ID="{TEMPLATE_ID}" data-groupid="{TEMPLATE_GROUP_ID}" data-type="comment">
    <td class="column_reorder">
        <i class="fa fa-bars reorder-item"></i>
    </td>

    <td class="column_checkbox">
        <input type="checkbox" class="item_selection" value="1">
    </td>

    <td class="column_pos">
        <div class="pos-inner"></div>
    </td>

    <td colspan="4" class="border-right column_name">
        <input type="text" class="form-control mr-2 item-description" name="item[{TEMPLATE_ID}]['group']"
            value="{{ trans('Wriye your comment') }}" x-on:blur="handleInputBlur($event, 'comment')">
    </td>

    @foreach ($product->quoteItems as $quoteItem)
        <td class="column_single_price border-left" data-cardQuoteID="{{ $quoteItem->id }}">-</td>
        <td class="column_total_price border-right" data-cardQuoteID="{{ $quoteItem->id }}">-</td>
    @endforeach
</tr>
