<tr class="group group_row" :data-id="item.id" :data-itemid="item.id" :data-groupid="item.groupId" data-type="group">

    <td class="column_reorder">
        <i class="fa-solid fa-up-down reorder-item reorder_group_btn"></i>
    </td>

    <td class="column_checkbox">
        <input type="checkbox" class="item_selection" @change="handleGroupSelection($event, item.groupId)">
    </td>

    <td class="column_pos grouppos"></td>

    <td colspan="4" class="column_name grouptitle border-right">
        <div class="div-desc-toggle">
            <i class="fa fas fa-solid fa-caret-right"></i>
            <input type="text" class="form-control grouptitle-input" :value="item.name"
                :name="item[item.id]['group']" x-on:blur="handleInputBlur($event, 'group')">
        </div>
    </td>

    @foreach ($allQuotes as $quotes)
        <td colspan="2" class="text-right grouptotal border-left-right" data-cardQuoteID="{{ $quotes->id }}">
            {{ currency_format_with_sym(00) }}
        </td>
    @endforeach
</tr>
