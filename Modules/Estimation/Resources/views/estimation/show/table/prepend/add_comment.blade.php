<tr class="item_comment" :data-id="item.id" :data-itemid="item.id" :data-groupid="item.groupId" data-type="comment">

    <td class="column_reorder">
        <i class="fa fa-bars reorder-item"></i>
    </td>

    <td class="column_checkbox">
        <input type="checkbox" class="item_selection">
    </td>

    <td class="column_pos">
        <div class="pos-inner"></div>
    </td>

    <td colspan="4" class="border-right column_name">
        <input type="text" name="[item][name]" class="form-control item-description"
            :value="item.content || 'write your comment'">
    </td>

    @foreach ($allQuotes as $quotes)
        <td class="column_single_price border-left" data-cardQuoteID="{{ $quotes->id }}">-</td>
        <td class="column_total_price border-right" data-cardQuoteID="{{ $quotes->id }}">-</td>
    @endforeach
</tr>
