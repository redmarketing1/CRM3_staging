<tr class="group_row group" data-itemID="{TEMPLATE_GROUP_ID}" data-groupid="{TEMPLATE_GROUP_ID}" data-type="group">

    <td class="column_reorder">
        <i class="fa-solid fa-up-down reorder-item reorder_group_btn"></i>
    </td>

    <td class="column_checkbox">
        <input type="checkbox" class="item_selection">
    </td>

    <td class="column_pos grouppos">{TEMPLATE_POS}</td>

    <td colspan="4" class="column_name grouptitle border-right">
        <div class="div-desc-toggle">
            <i class="desc_toggle fa fas fa-solid fa-caret-right grp-dt-control"></i>
            <input type="text" 
                name="item[{TEMPLATE_GROUP_ID}]['group']"
                class="form-control grouptitle-input heading" 
                value="{{ trans('New Group') }}">
        </div>
    </td>

    @foreach ($allQuotes as $quote)
        <td class="text-right grouptotal border-left-right" 
            colspan="2" 
            id="cardQuoteGroupTotalPrice"
            data-cardQuoteID="{{ $quote->id }}">
            -
        </td>
    @endforeach
</tr>
