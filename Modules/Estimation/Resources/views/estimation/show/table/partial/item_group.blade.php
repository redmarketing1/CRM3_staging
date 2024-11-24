<tr class="group_row group" x-data data-groupid="{{ $estimationGroup->id }}" data-type="group">

    <td class="column_reorder">
        <i class="fa-solid fa-up-down reorder-item reorder_group_btn"></i>
    </td>

    <td class="column_checkbox">
        <input type="checkbox" class="item_selection" @change="handleGroupSelection($event, '{{ $estimationGroup->id }}')">
    </td>

    <td class="column_pos grouppos"></td>

    <td colspan="4" class="column_name grouptitle border-right">
        <div class="div-desc-toggle">
            <i class="desc_toggle fa fas fa-solid fa-caret-right grp-dt-control"></i>
            <input type="text" name="item[{{ $estimationGroup->id }}]['group']"
                class="form-control grouptitle-input heading" value="{{ $estimationGroup->group_name }}"
                x-on:blur="handleInputBlur($event, 'group')">
        </div>
    </td>

    @foreach ($allQuotes as $quote)
        <td class="text-right grouptotal border-left-right" colspan="2" data-cardQuoteID="{{ $quote->id }}"
            x-text="formatCurrency(calculateGroupTotal('{{ $estimationGroup->id }}'))">
        </td>
    @endforeach
</tr>
