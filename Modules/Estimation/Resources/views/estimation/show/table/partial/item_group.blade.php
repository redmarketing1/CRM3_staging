<tr class="group_row group" data-itemID="{{ $estimationGroup->id }}" data-groupid="{{ $estimationGroup->id }}" data-type="group">

    <td class="column_reorder">
        <i class="fa-solid fa-up-down reorder-item reorder_group_btn"></i>
    </td>

    <td class="column_checkbox">
        <input type="checkbox" class="item_selection group_selection" data-groupid="{{ $estimationGroup->id }}">
    </td>

    <td class="column_pos grouppos">
        {{ $estimationGroup->group_pos }}
    </td>

    <td colspan="4" class="column_name grouptitle border-right">
        <div class="div-desc-toggle">
            <i class="desc_toggle fa fas fa-solid fa-caret-right grp-dt-control"></i>
            <input type="text" name="item[{{ $estimationGroup->id }}]['group']"
                class="form-control grouptitle-input heading" 
                value="{{ $estimationGroup->group_name }}">
        </div>
    </td>

    @foreach ($allQuotes as $quote)
        <td class="text-right grouptotal border-left-right" 
            colspan="2" 
            id="cardQuoteGroupTotalPrice"
            data-cardQuoteGroupTotalPrice="{{ $quote->id }}">
        </td>
    @endforeach
</tr> 