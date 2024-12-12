<tr class="item_comment" data-groupid="{{ $estimationGroup->id }}" data-itemID="{{ $product->id }}" data-type="comment">
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
            value="{{ $product->comment }}" x-on:blur="handleInputBlur($event, 'comment')">
    </td>

    @foreach ($product->quoteItems as $quoteItem)
        <td colspan="2" class="border-right text-center">-</td>
    @endforeach
</tr>
