<template x-for="group in getSortedGroups()" :key="group.id">
    <tbody>
        {{-- Group Header Row --}}
        <tr class="group group_row" :data-id="group.id" :data-groupid="group.id" data-type="group">
            <td class="column_reorder">
                <i class="fa-solid fa-up-down reorder-item reorder_group_btn"></i>
            </td>
            <td class="column_checkbox">
                <input type="checkbox" class="item_selection" @change="handleGroupSelection($event, group.id)">
            </td>
            <td class="column_pos grouppos" x-text="group.pos"></td>
            <td colspan="4" class="column_name grouptitle border-right">
                <div class="div-desc-toggle">
                    <input type="text" class="form-control grouptitle-input" :value="group?.name || 'New Group'"
                        :name="`item[${group.id}][group]`" @blur="handleInputBlur($event, 'group')">
                </div>
            </td>
            @foreach ($allQuotes as $quotes)
                <td colspan="2" class="text-right grouptotal border-left-right"
                    data-cardquoteid="{{ $quotes->id }}" x-text="formatCurrency(group?.total || 0)"></td>
            @endforeach
        </tr>

        {{-- Items and Comments --}}
        <template x-for="entry in getSortedItemsForGroup(group.id)" :key="entry.id">
            <template x-if="true">
                <tr :class="{ 'item_row': entry.type === 'item', 'item_comment': entry.type === 'comment' }"
                    :data-id="entry.id" :data-itemid="entry.type === 'item' ? entry.id : null"
                    :data-commentid="entry.type === 'comment' ? entry.id : null" :data-groupid="group.id"
                    :data-type="entry.type">
                    <td class="column_reorder">
                        <i class="fa fa-bars reorder-item"></i>
                    </td>
                    <td class="column_checkbox">
                        <input type="checkbox" class="item_selection">
                    </td>
                    <td class="column_pos">
                        <div class="pos-inner" x-text="entry.pos"></div>
                    </td>

                    {{-- Item Content --}}
                    <template x-if="entry.type === 'item'">
                        <td class="column_name">
                            <div class="div-desc-toggle">
                                <i class="desc_toggle fa fas fa-solid"
                                    :class="isExpanded(entry.id) ? 'fa-caret-down' : 'fa-caret-right'"
                                    @click="toggleDescription(entry.id, $event)"></i>
                                <input type="text" :name="`item[${entry.id}][name]`" class="item-name form-control"
                                    :value="entry?.name || 'New Item'" @blur="handleInputBlur($event, 'item')">
                            </div>
                        </td>
                    </template>

                    <td x-show="entry.type === 'item'" class="column_quantity">
                        <input type="text" class="form-control row_qty item-quantity"
                            :value="formatDecimal(entry?.quantity || 0)" @blur="handleInputBlur($event, 'quantity')">
                    </td>

                    <td x-show="entry.type === 'item'" class="column_unit">
                        <input type="text" class="form-control item-unit" :value="entry?.unit || ''"
                            @blur="handleInputBlur($event, 'unit')">
                    </td>

                    <td x-show="entry.type === 'item'" class="column_optional border-right">
                        <input type="checkbox" class="select_optional item-optional"
                            :checked="String(entry.optional) === '1'"
                            @change="handleOptionalChange($event, entry.id)">
                    </td>

                    {{-- Comment Content --}}
                    <template x-if="entry.type === 'comment'">
                        <td colspan="4" class="border-right column_name">
                            <input type="text" :name="`item[${entry.id}][comment]`"
                                class="form-control item-description" :value="entry?.content || 'Write your comment'"
                                @blur="handleInputBlur($event, 'comment')">
                        </td>
                    </template>

                    {{-- Price Columns --}}
                    @foreach ($allQuotes as $index => $quotes)
                        <template x-if="entry.type === 'item'">
                            <td class="column_single_price border-left" data-cardquoteid="{{ $quotes->id }}">
                                <input type="text" class="form-control row_price item-price"
                                    :value="formatCurrency(entry.prices[{{ $quotes->id }}]?.singlePrice || 0)"
                                    @blur="handleInputBlur($event, 'price')">
                            </td>
                        </template>
                        <template x-if="entry.type === 'comment'">
                            <td class="column_single_price border-left" data-cardquoteid="{{ $quotes->id }}">-</td>
                        </template>
                        <td class="column_total_price border-right" data-cardquoteid="{{ $quotes->id }}"
                            x-text="entry.type === 'item' ? (entry?.optional ? '-' : formatCurrency(calculateItemTotal(entry.id, {{ $quotes->id }}))) : '-'">
                        </td>
                    @endforeach
                </tr>

                <template x-if="entry.type === 'item'">
                    <tr class="item_child tr_child_description" :data-id="entry.id" x-show="isExpanded(entry.id)"
                        style="display: none;" x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0">
                        <td colspan="3"></td>
                        <td colspan="4" class="column_name desc_column">
                            <textarea class="description_input w-100 tinyMCE-DIS" :name="`item[${entry.id}][description]`"
                                placeholder="Items Description" x-text="entry?.description || ''"></textarea>
                        </td>
                        <td colspan="{{ count($allQuotes) * 2 }}"></td>
                    </tr>
                </template>
            </template>
        </template>
    </tbody>
</template>
