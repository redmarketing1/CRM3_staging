$(document).ready(function () {
    const EstimationTable = {
        estimation: $('.estimation-show'),
        templates: {
            item: $('#add-item-template').html(),
            group: $('#add-group-template').html(),
            comment: $('#add-comment-template').html()
        },

        init() {
            if (!this.validateTemplates()) return;
            this.bindEvents();
            this.initializeSortable();
        },

        validateTemplates() {
            return Object.values(this.templates).every(template => template);
        },

        bindEvents() {
            this.estimation.find('button[data-actioninsert]').on('click', (event) => {
                event.preventDefault();
                const button = $(event.currentTarget);
                const target = button.data('actioninsert');
                if (target) this.addItems(target);
            });
            this.estimation.on('click', '.desc_toggle', this.toggleDescription);
        },

        addItems(type) {
            if (!this.templates[type]) return;

            const timestamp = Date.now();
            const template = this.templates[type];

            if (type === 'group') {
                const newGroup = template
                    .replace(/{TEMPLATE_ID}/g, timestamp)
                    .replace(/{TEMPLATE_POS}/g, this.getNextGroupPosition());

                $('#estimation-items').append(newGroup);
                this.updatePOSNumbers();
                return;
            }

            const currentGroupId = this.getCurrentGroupId();
            if (!currentGroupId) {
                toastr?.error('Please create a group first');
                return;
            }

            const newItem = template
                .replace(/{TEMPLATE_ID}/g, timestamp)
                .replace(/{TEMPLATE_GROUP_ID}/g, currentGroupId)
                .replace(/{TEMPLATE_POS}/g, this.getNextItemPosition(currentGroupId));

            const lastGroupItem = $(`tr[data-groupid="${currentGroupId}"]:last`);
            lastGroupItem.length ? lastGroupItem.after(newItem) : $(`tr[data-groupid="${currentGroupId}"]`).after(newItem);

            this.updatePOSNumbers();
        },

        getCurrentGroupId() {
            const lastGroup = $('.group_row').last();
            return lastGroup.length ? lastGroup.data('groupid') : Date.now() + 10;
        },

        getNextGroupPosition() {
            let maxPos = 0;
            $('.group_row').each(function () {
                const pos = parseInt($(this).find('.grouppos').text()) || 0;
                maxPos = Math.max(maxPos, pos);
            });
            return (maxPos + 1).toString().padStart(2, '0');
        },

        getNextItemPosition(groupId) {
            let maxPos = 0;
            $(`tr[data-groupid="${groupId}"]`).not('.group_row').each(function () {
                const pos = parseInt($(this).find('.pos-inner').text().split('.')[1]) || 0;
                maxPos = Math.max(maxPos, pos);
            });

            const groupPos = $(`tr[data-groupid="${groupId}"].group_row .grouppos`).text();
            return `${groupPos}.${(maxPos + 1).toString().padStart(2, '0')}`;
        },

        updatePOSNumbers() {
            let currentGroupPos = 0;
            let itemCountInGroup = 0;

            $('.group_row, .item_row, .item_comment').each(function () {
                if ($(this).hasClass('group_row')) {
                    currentGroupPos++;
                    itemCountInGroup = 0;
                    $(this).find('.grouppos').text(currentGroupPos.toString().padStart(2, '0'));
                } else {
                    itemCountInGroup++;
                    $(this).find('.pos-inner').text(
                        `${currentGroupPos.toString().padStart(2, '0')}.${itemCountInGroup.toString().padStart(2, '0')}`
                    );
                }
            });
        },

        initializeSortable() {
            $("#estimation-items").sortable({
                items: 'tr.group_row, tr.item_row, tr.item_comment',
                handle: '.reorder-item, .reorder_group_btn',
                axis: 'y',
                helper: function (e, tr) {
                    const item = $(e.target).closest('tr');
                    const helperContainer = $('<div class="drag-helper"></div>');

                    if (item.hasClass('group_row')) {
                        const groupId = item.data('groupid');
                        const clonedGroup = item.clone();
                        helperContainer.append(clonedGroup);

                        $(`tr.item_row[data-groupid="${groupId}"], tr.item_comment[data-groupid="${groupId}"]`).each(function () {
                            helperContainer.append($(this).clone());
                        });
                    } else {
                        helperContainer.append(item.clone());
                    }

                    const originalCells = item.children();
                    helperContainer.find('td').each(function (index) {
                        $(this).width(originalCells.eq(index).outerWidth());
                    });
                    helperContainer.width(item.closest('table').width());

                    return helperContainer;
                },
                start: function (e, ui) {
                    const item = ui.item;

                    if (item.hasClass('group_row')) {
                        const groupId = item.data('groupid');
                        $(`tr.item_row[data-groupid="${groupId}"], tr.item_comment[data-groupid="${groupId}"]`).hide();
                    }
                },
                stop: (e, ui) => {
                    const item = ui.item;

                    if (item.hasClass('group_row')) {
                        const groupId = item.data('groupid');
                        const groupItems = $(`tr.item_row[data-groupid="${groupId}"], tr.item_comment[data-groupid="${groupId}"]`);
                        item.after(groupItems);
                        groupItems.show();
                    } else if (item.hasClass('item_row')) {
                        // Get next and previous elements
                        const next = item.next();
                        const prev = item.prev();

                        // If next row is a description row but not for this item, move this item elsewhere
                        if (next.hasClass('item_child') && next.data('itemid') !== item.data('itemid')) {
                            // Find proper position
                            const prevItem = item.prevAll('.item_row').first();
                            if (prevItem.length) {
                                prevItem.after(item);
                            } else {
                                const prevGroup = item.prevAll('.group_row').first();
                                if (prevGroup.length) {
                                    prevGroup.after(item);
                                }
                            }
                        }

                        // Always keep description row with its item
                        const itemId = item.data('itemid');
                        const descRow = $(`.item_child[data-itemid="${itemId}"]`);
                        if (descRow.length) {
                            item.after(descRow);
                        }
                    }

                    this.handleItemMove(ui.item);
                    this.updatePOSNumbers();
                },
                change: function (e, ui) {
                    const item = ui.item;
                    const placeholder = ui.placeholder;

                    if (item.hasClass('item_row')) {
                        const next = placeholder.next();
                        if (next.hasClass('item_child') && next.data('itemid') !== item.data('itemid')) {
                            placeholder.insertAfter(next);
                        }
                    }
                }
            });
        },

        handleItemMove(movedItem) {
            if (movedItem.hasClass('item_row') || movedItem.hasClass('item_comment')) {
                let prevGroup = movedItem.prevAll('.group_row').first();
                if (prevGroup.length) {
                    const newGroupId = prevGroup.data('groupid');
                    const itemId = movedItem.data('itemid');
                    const oldGroupId = movedItem.data('groupid');

                    movedItem.attr('data-groupid', newGroupId);

                    // For item rows, also move description
                    if (movedItem.hasClass('item_row')) {
                        $(`.item_child[data-itemid="${itemId}"]`).attr('data-groupid', newGroupId);
                    }

                    if (this.templates[itemId]) {
                        this.templates[itemId].groupId = newGroupId;
                    }
                }
            }
        },

        handleItemMove(movedItem) {
            if (movedItem.hasClass('item_row')) {
                let prevGroup = movedItem.prevAll('.group_row').first();
                if (prevGroup.length) {
                    const newGroupId = prevGroup.data('groupid');
                    const itemId = movedItem.data('itemid');

                    movedItem.attr('data-groupid', newGroupId);
                    $(`.item_child[data-itemid="${itemId}"]`).attr('data-groupid', newGroupId);
                }
            }
        },

        toggleDescription(event) {
            const icon = $(event.currentTarget);
            const row = icon.closest('tr');
            const parentID = row.data('itemid');
            const descRow = $(`.item_child.tr_child_description[data-itemID="${parentID}"]`);

            descRow.toggle();
            icon.toggleClass('fa-caret-right fa-caret-down');
        },
    };

    EstimationTable.init();
});