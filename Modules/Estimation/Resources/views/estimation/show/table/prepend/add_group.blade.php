<tr class="group_row group" data-groupid="{TEMPLATE_GROUP_ID}" data-itemID="{TEMPLATE_GROUP_ID}" data-type="group">

    <td class="column_reorder">
        <i class="fa-solid fa-up-down reorder-item reorder_group_btn"></i>
    </td>

    <td class="column_checkbox">
        <input type="checkbox" class="item_selection" @change="handleGroupSelection($event, '{TEMPLATE_GROUP_ID}')">
    </td>

    <td class="column_pos grouppos"></td>

    <td colspan="4" class="column_name grouptitle border-right">
        <div class="div-desc-toggle">
            <i class="desc_toggle fa fas fa-solid fa-caret-right grp-dt-control"></i>
            <input type="text" name="item[{TEMPLATE_GROUP_ID}]['group']"
                class="form-control grouptitle-input heading" value="1"
                x-on:blur="handleInputBlur($event, 'group')">
        </div>
    </td>

    @foreach ($allQuotes as $quote)
        <td class="text-right grouptotal border-left-right" colspan="2" data-cardQuoteID="{{ $quote->id }}"
            x-text="formatCurrency(calculateGroupTotal('{TEMPLATE_GROUP_ID}'))">
        </td>
    @endforeach
</tr>
