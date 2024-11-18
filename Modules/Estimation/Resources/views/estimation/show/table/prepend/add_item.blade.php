<tr class="item_row parent-row" :data-id="item.id" :data-itemid="item.id" :data-groupid="item.groupId"
    data-type="item">

    <td class="column_reorder">
        <i class="fa fa-bars reorder-item"></i>
    </td>

    <td class="column_checkbox">
        <input type="checkbox" class="item_selection">
    </td>

    <td class="column_pos">
        <div class="pos-inner"></div>
    </td>

    <td class="column_name item_name">
        <div class="div-desc-toggle">
            <i class="desc_toggle fa fas fa-solid" :class="isExpanded(item.id) ? 'fa-caret-down' : 'fa-caret-right'"
                x-on:click="toggleDescription(item.id, $event)"></i>
            <input type="text" name="[item][name]" class="form-control" value="{{ trans('Item Name') }}">
        </div>
    </td>

    <td class="column_quantity">
        <input type="text" class="form-control row_qty" :value="formatDecimal(item.quantity || 0)"
            @blur="handleInputBlur($event, 'quantity')">
    </td>

    <td class="column_unit">
        <input type="text" class="form-control" value="0">
    </td>

    <td class="column_optional border-right">
        <input type="checkbox" name="optional[]" class="select_optional" :checked="item.optional"
            x-on:change="handleOptionalChange($event, item.id)">
    </td>

    @foreach ($allQuotes as $quotes)
        <td class="column_single_price border-left">
            <input type="text" class="form-control row_price" :value="formatCurrency(item.price || 0)"
                @blur="handleInputBlur($event, 'price')">
        </td>
        <td class="column_total_price border-right" x-text="calculateItemTotal(item.id)">
            -
        </td>
    @endforeach
</tr>

<!-- Child Row -->
<tr class="item_child" data-type="item_child" :data-id="item.id"
    :style="isExpanded(item.id) ? 'display: table-row;' : 'display: none;'"
    x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95"
    x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95">
    <td colspan="3"></td>
    <td colspan="4" class="column_name desc_column w-100">
        <textarea class="description_input w-100"name="[item][description]" placeholder="{{ trans('Items Description') }}"></textarea>
    </td>
    <td colspan="4"></td>
</tr>
