<template x-for="group in getSortedGroups()" :key="group.id">
    <tbody>
        <!-- Group Row -->
        <template x-if="group.type === 'group'">
            <tr class="group group_row" :data-id="group.id" :data-groupid="group.id" data-type="group">
                <td class="column_reorder">
                    <i class="fa-solid fa-up-down reorder-item reorder_group_btn"></i>
                </td>
                <td class="column_checkbox">
                    <input type="checkbox" class="item_selection" @change="handleGroupSelection($event, group.groupId)">
                </td>
                <td class="column_pos grouppos" x-text="group.pos"></td>
                <td colspan="4" class="column_name grouptitle border-right m-l-20">
                    <div class="div-desc-toggle">
                        <input type="text" class="form-control grouptitle-input" :value="group?.name || 'New Group'"
                            :name="`item[${group.id}][group]`" @blur="handleInputBlur($event, 'group')">
                    </div>
                </td>
                @foreach ($allQuotes as $quotes)
                    <td colspan="2" class="text-right grouptotal border-left-right"
                        data-cardQuoteID="{{ $quotes->id }}">
                        {{ currency_format_with_sym(00) }}
                    </td>
                @endforeach
            </tr>
        </template>

        <!-- Items and Comments -->
        <template x-for="item in getSortedItemsForGroup(group.id)" :key="item.id">
            <!-- Item Rows -->
            <template x-if="item.type === 'item'">
                <!-- Parent Row -->
                <tr class="item_row parent-row" :data-id="item.id" :data-itemid="item.id"
                    :data-groupid="item.groupId" data-type="item">
                    <td class="column_reorder">
                        <i class="fa fa-bars reorder-item"></i>
                    </td>
                    <td class="column_checkbox">
                        <input type="checkbox" class="item_selection">
                    </td>
                    <td class="column_pos">
                        <div class="pos-inner" x-text="item.pos"></div>
                    </td>
                    <td class="column_name item_name">
                        <div class="div-desc-toggle">
                            <i class="desc_toggle fa fas fa-solid"
                                :class="isExpanded(item.id) ? 'fa-caret-down' : 'fa-caret-right'"
                                @click="toggleDescription(item.id, $event)"></i>
                            <input type="text" :name="`item[${item.id}][name]`" class="item-name form-control"
                                :value="item?.name || 'New Item'" @blur="handleInputBlur($event, 'item')">
                        </div>
                    </td>
                    <td class="column_quantity">
                        <input type="text" class="form-control row_qty item-quantity"
                            :value="formatDecimal(item?.quantity || 0)" @blur="handleInputBlur($event, 'quantity')">
                    </td>
                    <td class="column_unit">
                        <input type="text" class="form-control item-unit" :value="item?.unit || ''"
                            @blur="handleInputBlur($event, 'unit')">
                    </td>
                    <td class="column_optional border-right">
                        <input type="checkbox" name="optional[]" class="select_optional item-optional"
                            :checked="item?.optional || false" @change="handleOptionalChange($event, item.id)">
                    </td>
                    @foreach ($allQuotes as $index => $quotes)
                        <td class="column_single_price border-left" data-cardQuoteID="{{ $quotes->id }}">
                            <input type="text" class="form-control row_price item-price"
                                :value="formatCurrency(item?.price || 0)" @blur="handleInputBlur($event, 'price')">
                        </td>
                        <td class="column_total_price border-right" data-cardQuoteID="{{ $quotes->id }}"
                            x-text="item?.optional ? '-' : formatCurrency(calculateItemTotal(item.id, {{ $index }}))">
                            -
                        </td>
                    @endforeach
                </tr>

                <!-- Description Row -->
                <tr class="item_child tr_child_description" data-type="item_child" :data-id="item.id"
                    :style="isExpanded(item.id) ? 'display: table-row;' : 'display: none;'"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 transform scale-95"
                    x-transition:enter-end="opacity-100 transform scale-100"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 transform scale-100"
                    x-transition:leave-end="opacity-0 transform scale-95">
                    <td colspan="3"></td>
                    <td colspan="4" class="column_name desc_column w-100 m-l-20">
                        <textarea class="description_input w-100 tinyMCE-DIS" :name="`item[${item.id}][description]`"
                            placeholder="Items Description" x-text="item?.description || ''"></textarea>
                    </td>
                    <td colspan="{{ count($allQuotes) * 2 }}"></td>
                </tr>
            </template>

            <!-- Comment Row -->
            <template x-if="item.type === 'comment'">
                <tr class="item_comment" :data-id="item.id" :data-commentid="item.id"
                    :data-groupid="item.groupId" data-type="comment">
                    <td class="column_reorder">
                        <i class="fa fa-bars reorder-item"></i>
                    </td>
                    <td class="column_checkbox">
                        <input type="checkbox" class="item_selection">
                    </td>
                    <td class="column_pos">
                        <div class="pos-inner" x-text="item.pos"></div>
                    </td>
                    <td colspan="4" class="border-right column_name">
                        <input type="text" :name="`item[${item.id}][comment]`" class="form-control item-description"
                            :value="item?.content || 'write your comment'" @blur="handleInputBlur($event, 'comment')">
                    </td>
                    @foreach ($allQuotes as $quotes)
                        <td class="column_single_price border-left" data-cardQuoteID="{{ $quotes->id }}">-</td>
                        <td class="column_total_price border-right" data-cardQuoteID="{{ $quotes->id }}">-</td>
                    @endforeach
                </tr>
            </template>
        </template>
    </tbody>
</template>
