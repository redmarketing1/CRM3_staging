$(document).ready(function () {
    const EstimationTable = {
        // autoSaveEnabled: $('#autoSaveEnabled').is(':checked'), //OLD
        autoSaveEnabled: false,
        lastSaveTime: 0,
        saveTimeout: null,
        saveInterval: 1000 * 30,
        hasUnsavedChanges: false,
        isFullScreen: false,
        estimation: $('.estimation-show'),
        originalPrices: new Map(),
        templates: {
            item: $('#add-item-template').html(),
            group: $('#add-group-template').html(),
            comment: $('#add-comment-template').html()
        },

        bindEvents() {
            this.estimation.find('button[data-actioninsert]').on('click', (event) => {
                event.preventDefault();
                const button = $(event.currentTarget);
                const target = button.data('actioninsert');
                if (target) this.addItems(target);
            });
            this.estimation.on('click', '.desc_toggle', this.toggleDescription);
            this.estimation.on('click', '.grp-dt-control', this.toggleGroup);
            this.estimation.on('click', '#toggleFullScreen', this.toggleFullScreen);

            this.estimation.on('blur', '.item-quantity, .item-price, .item-name, input[name^="item"][name$="[markup]"], input[name^="item"][name$="[discount]"]', () => {
                this.hasUnsavedChanges = true;
                this.autoSaveHandler();
            });

            this.estimation.on('change', '.item-optional, select[name^="item"][name$="[tax]"]', () => {
                this.hasUnsavedChanges = true;
                this.autoSaveHandler();
            });

            this.estimation.on('blur', '.item-quantity, .item-price', (event) => {
                const $target = $(event.currentTarget);
                this.formatInput($target);
                this.updateAllCalculations();
            });

            this.estimation.on('click', '#save-button', () => {
                this.saveTableData();
            });

            this.estimation.on('input', '#table-search', () => {
                this.searchTableItem();
            });

            this.estimation.on('change', '.item-optional', () => {
                this.updateAllCalculations();
            });

            this.estimation.on('change', 'select[name^="item"][name$="[tax]"]', () => {
                this.updateAllCalculations();
            });

            this.estimation.on('change', '#QuateTypesStatus', (event) => {
                const Checkbox = $(event.currentTarget).is(':checked');
                const Id = $(event.currentTarget).data('id');
                const Type = $(event.currentTarget).data('type');
                this.updateQuateTypeStatus(Checkbox, Id, Type);
            });

            this.estimation.on('blur', 'input[name^="item"][name$="[discount]"]', (event) => {
                const $input = $(event.target);
                const value = this.parseGermanDecimal($input.val());


                $input.val(this.formatGermanDecimal(value));

                if (value < 0) {
                    $input.css({
                        'background-color': '#ffebee',
                        'color': '#d32f2f'
                    });
                } else {
                    $input.css({
                        'background-color': '',
                        'color': ''
                    });
                }

                this.updateAllCalculations();
            });

            this.estimation.on('blur', 'input[name^="item"][name$="[markup]"]', (event) => {
                const $input = $(event.target);
                const value = this.parseGermanDecimal($input.val());
                const cardQuoteId = $input.attr('name').match(/\[(\d+)\]/)[1];

                $input.val(this.formatGermanDecimal(value));

                if (value < 0) {
                    $input.css({
                        'background-color': '#ffebee',
                        'color': '#d32f2f'
                    });
                } else {
                    $input.css({
                        'background-color': '',
                        'color': ''
                    });
                }

                this.applyMarkupToSinglePrices(cardQuoteId, value);
                this.updateAllCalculations();
            });

            this.estimation.on('change', '.SelectAllCheckbox', (e) => {
                const isChecked = $(e.target).prop('checked');
                $('.item_selection').prop('checked', isChecked);
            });

            this.estimation.on('change', '.group_selection', (e) => {
                const $groupCheckbox = $(e.target);
                const groupId = $groupCheckbox.data('groupid');
                const isChecked = $groupCheckbox.prop('checked');

                $(`.item_row[data-groupid="${groupId}"], .item_comment[data-groupid="${groupId}"]`)
                    .find('.item_selection')
                    .prop('checked', isChecked);

                this.updateSelectAllState();
            });

            this.estimation.on('change', '.item_selection:not(.group_selection)', () => {
                this.updateSelectAllState();
            });

            $('button[data-actionremove]').on('click', () => {
                const $selectedCheckboxes = $('.item_selection:checked:not(.SelectAllCheckbox)');

                if ($selectedCheckboxes.length === 0) {
                    toastrs("error", "Please select checkbox to continue delete");
                    return;
                }

                Swal.fire({
                    title: 'Confirmation Delete',
                    text: 'Really! You want to remove them? You can\'t undo',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Delete it',
                    cancelButtonText: "No, cancel",
                }).then((result) => {
                    if (result.isConfirmed) {
                        const estimationId = $('#quote_form').find('input[name="id"]').val();
                        const itemIds = [];
                        const groupIds = [];

                        $selectedCheckboxes.each(function () {
                            const $row = $(this).closest('tr');
                            const id = $row.data('itemid') || $row.data('groupid');
                            const isGroup = $row.hasClass('group_row');

                            (isGroup ? groupIds : itemIds).push(id);

                            if ($row.hasClass('item_row')) {
                                const itemChild = $row.next(`[data-itemid="${id}"]`);
                                itemChild.remove();
                            }
                            $row.remove();
                        });

                        $.ajax({
                            url: route('estimation.destroy', estimationId),
                            method: 'DELETE',
                            data: { estimationId, items: itemIds, groups: groupIds },
                        });
                        document.querySelector('.SelectAllCheckbox').checked = false;
                        this.updateAllCalculations();
                        this.updatePOSNumbers();
                    }
                });
            });

            $(document).on('click', '#delete-quate', function (event) {
                event.preventDefault();
                Swal.fire({
                    title: 'Confirmation Delete',
                    text: 'Really! You want to remove them? You can\'t undo',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Delete it',
                    cancelButtonText: "No, cancel",
                }).then((result) => {
                    if (result.isConfirmed) {
                        const IDs = $(this).data('id');
                        $.ajax({
                            url: route('estimation.deleteQuote', IDs),
                            method: 'DELETE',
                            data: { id: IDs },
                            success: () => {
                                window.location.reload();
                            }
                        });
                    }
                });

            });
        },

        init() {
            if (!this.validateTemplates()) return;

            this.bindEvents();
            this.bindCalculationEvents();
            this.initializeSortable();
            this.updateAllCalculations();
            this.updatePOSNumbers();
            this.initializeAutoSave();

            document.addEventListener('fullscreenchange', () => {
                this.isFullScreen = !!document.fullscreenElement;
                const icon = document.querySelector('.fa-expand, .fa-compress');
                if (icon) {
                    icon.className = this.isFullScreen ? 'fa-solid fa-compress' : 'fa-solid fa-expand';
                }
            });
        },

        initializeFullScreen() {
            document.addEventListener('fullscreenchange', () => {
                this.isFullScreen = !!document.fullscreenElement;
                const btn = document.querySelector('.tools-btn button i.fa-expand, .tools-btn button i.fa-compress');
                if (btn) {
                    btn.className = this.isFullScreen ? 'fa-solid fa-compress' : 'fa-solid fa-expand';
                }
            });
        },

        initializeAutoSave() {
            $('#autoSaveEnabled').on('change', (e) => {
                this.autoSaveEnabled = $(e.target).is(':checked');

                if (this.autoSaveEnabled && this.hasUnsavedChanges) {
                    this.autoSaveHandler();
                }
            });

            $(window).on('beforeunload', (e) => {
                if (this.hasUnsavedChanges) {
                    const message = 'You have unsaved changes. Are you sure you want to leave?';
                    e.preventDefault();
                    e.returnValue = message;
                    return message;
                }
            });
        },

        toggleFullScreen() {
            const estimationSection = document.querySelector('.estimation-show');
            if (!estimationSection) return;

            if (!document.fullscreenElement) {
                estimationSection.requestFullscreen().catch(err => {
                    console.error(`Error attempting to enable fullscreen: ${err.message}`);
                });
            } else {
                document.exitFullscreen();
            }
        },

        autoSaveHandler() {
            if (!this.autoSaveEnabled || !$('#quote_form').length || !this.hasUnsavedChanges) return;

            if (this.saveTimeout) {
                clearTimeout(this.saveTimeout);
            }

            const currentTime = Date.now();
            const timeSinceLastSave = currentTime - this.lastSaveTime;

            if (timeSinceLastSave >= this.saveInterval) {
                this.saveTableData();
                this.lastSaveTime = currentTime;
            } else {
                this.saveTimeout = setTimeout(() => {
                    if (this.hasUnsavedChanges && this.autoSaveEnabled) {
                        this.saveTableData();
                        this.lastSaveTime = Date.now();
                    }
                }, this.saveInterval);
            }
        },

        saveTableData() {
            // if (!this.autoSaveEnabled) return;

            const columns = {};
            const cardQuoteIds = new Set();
            $('.column_single_price').each(function () {
                const quoteId = $(this).data('cardquoteid');
                if (quoteId) cardQuoteIds.add(quoteId);
            });

            cardQuoteIds.forEach(cardQuoteId => {
                columns[cardQuoteId] = {
                    settings: {
                        markup: this.parseGermanDecimal($(`input[name="item[${cardQuoteId}][markup]"]`).val() || '0'),
                        cashDiscount: this.parseGermanDecimal($(`input[name="item[${cardQuoteId}][discount]"]`).val() || '0'),
                        vat: this.parseGermanDecimal($(`select[name="item[${cardQuoteId}][tax]"]`).val() || '0')
                    },
                    totals: {
                        netIncludingDiscount: this.parseGermanDecimal($(`.total-net-discount[data-cardquoteid="${cardQuoteId}"]`).text()),
                        grossIncludingDiscount: this.parseGermanDecimal($(`.total-gross-discount[data-cardquoteid="${cardQuoteId}"]`).text()),
                        net: this.parseGermanDecimal($(`.total-net[data-cardquoteid="${cardQuoteId}"]`).text()),
                        gross: this.parseGermanDecimal($(`.total-gross-total[data-cardquoteid="${cardQuoteId}"]`).text())
                    }
                };
            });

            const data = {
                cards: columns,
                form: this.getFormData(),
                newItems: this.prepareNewItemsForSubmission()
            };

            $.ajax({
                url: route('estimation.update', data.form.id),
                method: 'PUT',
                data: data,
                beforeSend: () => {
                    $('.lastSaveTimestamp').text('is running...');
                    $('#save-button').html('Saving... <i class="fa fa-arrow-right-rotate rotate"></i>');
                },
                success: (idMappings) => {

                    this.updateEntitiesWithNewIds(idMappings);

                    this.lastSaveTime = Date.now();
                    this.hasUnsavedChanges = false;

                    let lastSavedText = this.formatTimeAgo(this.lastSaveTime);
                    this.startTimeAgoUpdates();

                    $('.lastSaveTimestamp').text(lastSavedText);
                    $('#save-button').html(`Saved last changed.`);
                    window.location.reload();
                },
                error: (error) => {
                    toastrs('error', 'Failed to save changes.');
                    $('.lastSaveTimestamp').text('is failed.');
                    this.hasUnsavedChanges = true;
                }
            });
        },

        formatTimeAgo(timestamp) {
            if (!timestamp) return 'Never saved';

            const now = Date.now();
            const diff = Math.floor((now - timestamp) / 1000);

            if (diff < 60) return 'Just now';
            if (diff < 3600) {
                const minutes = Math.floor(diff / 60);
                return `${minutes} minute${minutes > 1 ? 's' : ''} ago`;
            }
            if (diff < 86400) {
                const hours = Math.floor(diff / 3600);
                return `${hours} hour${hours > 1 ? 's' : ''} ago`;
            }

            const days = Math.floor(diff / 86400);

            return `${days} day${days > 1 ? 's' : ''} ago`;
        },

        startTimeAgoUpdates() {
            if (this.timeAgoInterval) {
                clearInterval(this.timeAgoInterval);
            }

            this.timeAgoInterval = setInterval(() => {
                if (this.lastSaveTime) {
                    let lastSavedText = this.formatTimeAgo(this.lastSaveTime);
                    $('.lastSaveTimestamp').text(lastSavedText);
                }
            }, 60000);
        },

        prepareNewItemsForSubmission() {
            const newItems = [];

            const groupRows = document.querySelectorAll('.group_row');
            groupRows.forEach(row => {
                const groupId = row.dataset.groupid;
                const groupName = row.querySelector('.grouptitle-input').value;
                const groupPos = row.querySelector('.grouppos').textContent.trim();

                const group = {
                    id: groupId,
                    type: 'group',
                    name: groupName,
                    pos: groupPos,
                    total: null,
                };
                newItems.push(group);
            });

            const itemRows = document.querySelectorAll('.item_row, .item_comment');
            itemRows.forEach(row => {
                const itemId = row.dataset.itemid;
                const type = row.dataset.type;
                const groupId = row.dataset.groupid;
                const name = row.querySelector('.item-name, .item-comment')?.value.trim() || null;
                const comment = row.querySelector('.item-comment')?.value.trim() || null;
                const descriptionID = $(row).next(`.tr_child_description[data-itemid="${itemId}"]`).find('.description_input').attr('id');
                const description = tinymce?.get(descriptionID)?.getContent() || $(descriptionID)?.val() || null;

                const item = {
                    id: itemId,
                    type: type,
                    groupId: groupId,
                    pos: row.querySelector('.pos-inner').textContent.trim(),
                    name: name,
                    description: description,
                    comment: comment,
                    quantity: this.parseGermanDecimal(row.querySelector('.item-quantity')?.value || '0'),
                    unit: row.querySelector('.item-unit')?.value || 0,
                    optional: row.querySelector('.item-optional')?.checked ? 0 : 1,
                    prices: (type == 'item') ? this.updateItemPriceAndTotal(itemId) : this.updateCommentPrices(),
                };

                newItems.push(item);
            });

            return newItems;
        },

        updateCommentPrices() {
            const cardQuoteIds = Array.from(
                new Set(
                    Array.from(document.querySelectorAll('[data-cardquoteid]'))
                        .map(el => el.dataset.cardquoteid)
                )
            );
            return cardQuoteIds.map(quoteId => ({
                quoteId,
                singlePrice: 0,
                totalPrice: 0
            }));
        },

        updateItemPriceAndTotal(itemId) {
            const row = document.querySelector(`.item_row[data-itemid="${itemId}"]`);
            let $self = this;

            const singlePricing = row.querySelectorAll('.item-price');

            const prices = Array.from(singlePricing).map(element => {

                const quoteId = element.closest('td[data-cardquoteid]').dataset.cardquoteid;
                const singlePrice = $self.parseNumber(element.value);
                const quantity = $self.parseNumber(row.querySelector('.item-quantity').value);
                const total = singlePrice * quantity;

                return {
                    quoteId: quoteId,
                    singlePrice: singlePrice,
                    totalPrice: total
                };
            });

            return prices;
        },

        updateEntitiesWithNewIds(idMappings) {
            Object.entries(idMappings).forEach(([oldId, newId]) => {

                const rows = document.querySelectorAll(`
                    tr[data-itemid="${oldId}"], 
                    tr[data-groupid="${oldId}"]
                `);

                rows.forEach(row => {
                    if (row.dataset.itemid == oldId) row.dataset.itemid = newId;
                    if (row.dataset.groupid == oldId) row.dataset.groupid = newId;

                    row.querySelectorAll(`[name*="[${oldId}]"]`).forEach(input => {
                        input.name = input.name.replace(`[${oldId}]`, `[${newId}]`);
                    });
                });
            });
        },

        getFormData() {
            const $form = $('#quote_form');
            const formData = $form.serializeArray();
            const formObject = {};

            formData.forEach(item => {
                formObject[item.name] = item.value;
            });

            return formObject;
        },

        validateTemplates() {
            return Object.values(this.templates).every(template => template);
        },

        parseNumber(value) {
            if (typeof value === 'number') return value;
            return parseFloat(value.replace(/[^\d,-]/g, '').replace(',', '.')) || 0;
        },

        storeOriginalPrices() {
            this.originalPrices.clear();

            $('.item-price').each((_, element) => {
                const $price = $(element);
                const cardQuoteId = $price.data('cardquotesingleprice');
                const itemId = $price.closest('tr').data('itemid');
                const key = `${cardQuoteId}-${itemId}`;

                if (!this.originalPrices.has(key)) {
                    const originalPrice = this.parseGermanDecimal($price.val());
                    this.originalPrices.set(key, originalPrice);
                }
            });
        },

        addItems(type) {
            if (!this.templates[type]) return;

            const timestamp = Date.now();
            const template = this.templates[type];

            if (type === 'group') {
                const newGroup = template
                    .replace(/{TEMPLATE_GROUP_ID}/g, timestamp)
                    .replace(/{TEMPLATE_POS}/g, this.getNextGroupPosition());

                $('#estimation-items').append(newGroup);
                this.updatePOSNumbers();
                this.updateAllCalculations();
                return;
            }

            const existingGroups = document.querySelectorAll('.group_row');

            if (existingGroups.length === 0) {
                this.addItems('group');
            }

            const currentGroupId = this.getCurrentGroupId();

            const newItem = template
                .replace(/{TEMPLATE_ID}/g, timestamp)
                .replace(/{TEMPLATE_GROUP_ID}/g, currentGroupId)
                .replace(/{TEMPLATE_POS}/g, this.getNextItemPosition(currentGroupId));

            const lastGroupItem = $(`tr[data-groupid="${currentGroupId}"]:last`);

            if (lastGroupItem.length) {
                lastGroupItem.after(newItem);
            } else {
                $(`tr[data-groupid="${currentGroupId}"]`).after(newItem);
            }

            // Force recalculation of the group total
            const groupRow = $(`.group_row[data-groupid="${currentGroupId}"]`);
            if (groupRow.length) {
                const cardQuoteIds = new Set();
                $('.column_single_price').each(function () {
                    const quoteId = $(this).data('cardquoteid');
                    if (quoteId) cardQuoteIds.add(quoteId);
                });

                cardQuoteIds.forEach(cardQuoteId => {
                    const groupTotal = this.calculateGroupTotal(groupRow, cardQuoteId);
                    groupRow.find(`[data-cardquotegrouptotalprice="${cardQuoteId}"]`).text(groupTotal);
                });
            }

            this.updatePOSNumbers();
            this.updateAllCalculations();
            this.hasUnsavedChanges = true;
            this.autoSaveHandler();
        },

        updateSelectAllState() {
            const totalCheckboxes = $('.item_selection:not(.SelectAllCheckbox)').length;
            const checkedCheckboxes = $('.item_selection:not(.SelectAllCheckbox):checked').length;

            $('.SelectAllCheckbox').prop('checked', totalCheckboxes === checkedCheckboxes && totalCheckboxes > 0);
        },

        getCurrentGroupId() {
            const groupRows = document.querySelectorAll('.group_row');
            if (groupRows.length > 0) {
                const latestID = groupRows[groupRows.length - 1].getAttribute('data-groupid');
                console.log(latestID);
                return latestID;
            } else {
                return Date.now() + 10;
            }
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
            let lastGroupId = null;

            $('.group_row, .item_row, .item_comment').each(function () {
                const $row = $(this);

                if ($row.hasClass('group_row')) {
                    currentGroupPos++;
                    itemCountInGroup = 0;
                    lastGroupId = $row.data('groupid');
                    $row.find('.grouppos').text(currentGroupPos.toString().padStart(2, '0'));
                } else if (lastGroupId) {
                    itemCountInGroup++;
                    $row.attr('data-groupid', lastGroupId);
                    $row.find('.pos-inner').text(
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
                        $(`tr.item_row[data-groupid="${groupId}"], tr.item_comment[data-groupid="${groupId}"], tr.item_child[data-groupid="${groupId}"]`).hide();
                    }
                },
                stop: (e, ui) => {
                    const item = ui.item;

                    if (item.hasClass('group_row')) {
                        const groupId = item.data('groupid');
                        const groupItems = $(`tr.item_row[data-groupid="${groupId}"], tr.item_comment[data-groupid="${groupId}"], tr.item_child[data-groupid="${groupId}"]`);
                        item.after(groupItems);
                        groupItems.show();
                    } else if (item.hasClass('item_row') || item.hasClass('item_comment')) {
                        // Handle item movement between groups
                        const prevGroup = item.prevAll('.group_row').first();
                        if (prevGroup.length) {
                            const newGroupId = prevGroup.data('groupid');
                            const itemId = item.data('itemid');

                            // Update group ID for the item and its description
                            item.attr('data-groupid', newGroupId);
                            $(`.item_child[data-itemid="${itemId}"]`).attr('data-groupid', newGroupId);

                            // Ensure description row follows the item
                            const descRow = $(`.item_child[data-itemid="${itemId}"]`);
                            if (descRow.length) {
                                item.after(descRow);
                            }

                            // Update position numbers
                            this.updatePOSNumbers();
                        }
                    }

                    this.updateAllCalculations();
                    this.hasUnsavedChanges = true;
                    this.autoSaveHandler();
                },
                change: function (e, ui) {
                    const item = ui.item;
                    const placeholder = ui.placeholder;

                    if (item.hasClass('item_row') || item.hasClass('item_comment')) {
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

                    if (movedItem.hasClass('item_row')) {
                        $(`.item_child[data-itemid="${itemId}"]`).attr('data-groupid', newGroupId);
                    }

                    if (this.templates[itemId]) {
                        this.templates[itemId].groupId = newGroupId;
                    }
                }
            }
        },

        toggleGroup(event) {
            const icon = $(event.currentTarget);
            const row = icon.closest('tr.group_row');
            const groupId = row.data('groupid');

            const mainItems = $(`tr.item_row[data-groupid="${groupId}"], tr.item_comment[data-groupid="${groupId}"]`);
            mainItems.toggle();

            mainItems.each(function () {
                const itemId = $(this).data('itemid');
                const descRow = $(`.item_child[data-itemid="${itemId}"]`);
                const itemToggleIcon = $(this).find('.desc_toggle');

                if ($(this).is(':visible') && itemToggleIcon.hasClass('fa-caret-down')) {
                    descRow.show();
                } else {
                    descRow.hide();
                }
            });

            icon.toggleClass('fa-caret-right fa-caret-down');
        },

        toggleDescription(event) {
            const icon = $(event.currentTarget);
            const row = icon.closest('tr');
            const parentID = row.data('itemid');
            const descRow = $(`.item_child.tr_child_description[data-itemID="${parentID}"]`);

            descRow.toggle();
            icon.toggleClass('fa-caret-right fa-caret-down');
        },

        formatInput(target) {
            const $target = $(target);
            if ($target.hasClass('item-quantity')) {
                const formattedQuantity = this.formatGermanDecimal(
                    this.parseGermanDecimal($target.val())
                );
                $target.val(formattedQuantity);
            } else if ($target.hasClass('item-price')) {
                const formattedPrice = this.formatGermanCurrency(
                    this.parseGermanDecimal($target.val())
                );
                $target.val(formattedPrice);
            }
        },

        parseGermanDecimal(value) {
            if (!value || typeof value !== 'string') return 0;

            value = value.replace(/[€\s]/g, '')
                .replace(/\./g, '')
                .replace(',', '.');

            const parsed = parseFloat(value);
            return isNaN(parsed) ? 0 : parsed;
        },

        formatGermanDecimal(value) {
            return new Intl.NumberFormat('de-DE', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(value);
        },

        formatGermanCurrency(value) {
            return new Intl.NumberFormat('de-DE', {
                style: 'currency',
                currency: 'EUR',
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(value);
        },

        storeOriginalPrices() {
            $('.item-price').each((_, element) => {
                const $price = $(element);
                const id = $price.data('cardquotesingleprice');
                const originalPrice = this.parseGermanDecimal($price.val());
                this.originalPrices.set(`${id}-${$price.closest('tr').data('itemid')}`, originalPrice);
            });
        },

        applyMarkupToSinglePrices(cardQuoteId, markup) {
            if (this.originalPrices.size === 0) {
                this.storeOriginalPrices();
            }

            $(`.item-price[data-cardquotesingleprice="${cardQuoteId}"]`).each((_, element) => {
                const $price = $(element);
                const itemId = $price.closest('tr').data('itemid');
                const key = `${cardQuoteId}-${itemId}`;
                const originalPrice = this.originalPrices.get(key);

                if (originalPrice !== undefined) {
                    const newPrice = markup > 0 ? originalPrice * (1 + markup / 100) : originalPrice;
                    $price.val(this.formatGermanCurrency(newPrice));
                }
            });
        },

        calculateItemTotal(itemRow, cardQuoteId) {
            if (itemRow.find('.item-optional').is(':checked')) {
                return this.formatGermanCurrency(0);
            }

            const quantity = this.parseGermanDecimal(itemRow.find('.item-quantity').val() || '0');
            const price = this.parseGermanDecimal(
                itemRow.find(`.item-price[data-cardquotesingleprice="${cardQuoteId}"]`).val() || '0'
            );

            const total = quantity * price;

            return this.formatGermanCurrency(total);
        },

        calculateGroupTotal(groupRow, cardQuoteId) {
            let total = 0;
            const groupId = groupRow.data('groupid');

            // Get all items in this group
            $(`.item_row[data-groupid="${groupId}"]`).each((_, item) => {
                const $item = $(item);

                // Skip optional items
                if ($item.find('.item-optional').is(':checked')) {
                    return;
                }

                const quantity = this.parseGermanDecimal($item.find('.item-quantity').val() || '0');
                const price = this.parseGermanDecimal(
                    $item.find(`.item-price[data-cardquotesingleprice="${cardQuoteId}"]`).val() || '0'
                );

                const itemTotal = quantity * price;
                total += itemTotal;
            });

            // Return formatted total or dash
            return total === 0 ? '-' : this.formatGermanCurrency(total);
        },

        calculateTotals(cardQuoteId) {
            let netTotal = 0;
            $(`.group_row [data-cardquotegrouptotalprice="${cardQuoteId}"]`).each((_, element) => {
                const groupTotal = this.parseGermanDecimal($(element).text());
                if (!isNaN(groupTotal)) netTotal += groupTotal;
            });

            const cashDiscount = this.parseGermanDecimal($(`input[name="item[${cardQuoteId}][discount]"]`).val()) || 0;
            const vatRate = parseFloat($(`select[name="item[${cardQuoteId}][tax]"]`).val()) || 0;

            const netAfterDiscount = netTotal * (1 - cashDiscount / 100);
            const vatAmount = netTotal * (vatRate / 100);
            const grossTotal = netTotal + vatAmount;
            const grossAfterDiscount = netAfterDiscount + (netAfterDiscount * vatRate / 100);

            $(`.total-net[data-cardquoteid="${cardQuoteId}"]`).text(this.formatGermanCurrency(netTotal));
            $(`.total-net-discount[data-cardquoteid="${cardQuoteId}"]`).text(this.formatGermanCurrency(netAfterDiscount));
            $(`.total-gross-discount[data-cardquoteid="${cardQuoteId}"]`).text(this.formatGermanCurrency(grossAfterDiscount));
            $(`.total-gross-total[data-cardquoteid="${cardQuoteId}"]`).text(this.formatGermanCurrency(grossTotal));
            $(`.totalnr.total-gross-total[data-cardquoteid="${cardQuoteId}"]`).text(this.formatGermanCurrency(grossTotal));
        },

        bindCalculationEvents() {
            // Update on quantity change
            this.estimation.on('input', '.item-quantity', (e) => {
                const $row = $(e.target).closest('tr');
                this.updateRowCalculations($row);
                this.updateAllCalculations();
            });

            // Update on price change
            this.estimation.on('input', '.item-price', (e) => {
                const $row = $(e.target).closest('tr');
                this.updateRowCalculations($row);
                this.updateAllCalculations();
            });

            // Update on optional checkbox change
            this.estimation.on('change', '.item-optional', (e) => {
                const $row = $(e.target).closest('tr');
                this.updateRowCalculations($row);
                this.updateAllCalculations();
            });
        },

        updateRowCalculations($row) {
            const itemId = $row.data('itemid');
            const cardQuoteIds = new Set();

            $('.column_single_price').each((_, el) => {
                const quoteId = $(el).data('cardquoteid');
                if (quoteId) cardQuoteIds.add(quoteId);
            });

            cardQuoteIds.forEach(cardQuoteId => {
                const total = this.calculateItemTotal($row, cardQuoteId);
                $row.find(`[data-cardquotetotalprice="${cardQuoteId}"]`).text(total);

                // Update group total
                const $groupRow = $(`.group_row[data-groupid="${$row.data('groupid')}"]`);
                if ($groupRow.length) {
                    const groupTotal = this.calculateGroupTotal($groupRow, cardQuoteId);
                    $groupRow.find(`[data-cardquotegrouptotalprice="${cardQuoteId}"]`).text(groupTotal);
                }
            });
        },

        updateDisplayValues(cardQuoteId, values) {
            const formatCurrency = this.formatGermanCurrency.bind(this);
            const { net, netAfterDiscount, grossAfterDiscount, vatRate } = values;

            $(`.total-net[data-cardquoteid="${cardQuoteId}"]`).text(formatCurrency(net));
            $(`.total-net-discount[data-cardquoteid="${cardQuoteId}"]`).text(formatCurrency(netAfterDiscount));
            $(`.total-gross-discount[data-cardquoteid="${cardQuoteId}"]`).text(formatCurrency(grossAfterDiscount));

            const $vatSelect = $(`select[name="item[${cardQuoteId}][tax]"]`);
            $vatSelect.val(vatRate.toString());

            const grossWithVat = net * (1 + vatRate / 100);
            $(`.total-gross[data-cardquoteid="${cardQuoteId}"]`).text(formatCurrency(grossWithVat));
        },

        applySinglePriceMarkup(price, markup) {
            return price * (1 + markup / 100);
        },

        updateItemPrices(cardQuoteId) {
            const markup = this.parseGermanDecimal($(`input[name="item[${cardQuoteId}][markup]"]`).val()) || 0;

            $(`.item_row`).each((_, row) => {
                const $row = $(row);
                const $priceInput = $row.find(`.item-price[data-cardquotesingleprice="${cardQuoteId}"]`);
                const basePrice = this.parseGermanDecimal($priceInput.val());
                const priceWithMarkup = this.applySinglePriceMarkup(basePrice, markup);
                $priceInput.val(this.formatGermanCurrency(priceWithMarkup));
            });
        },

        updateTotalDisplay(cardQuoteId, totals) {
            $(`.total-net-discount[data-cardquoteid="${cardQuoteId}"]`)
                .text(this.formatGermanCurrency(totals.netIncDiscount));

            $(`.total-gross-discount[data-cardquoteid="${cardQuoteId}"]`)
                .text(this.formatGermanCurrency(totals.grossIncDiscount));

            $(`.total-net[data-cardquoteid="${cardQuoteId}"]`)
                .text(this.formatGermanCurrency(totals.net));

            $(`.total-gross-total[data-cardquoteid="${cardQuoteId}"]`)
                .text(this.formatGermanCurrency(totals.gross));

            this.updateVatDisplay(cardQuoteId, totals.vatRate);
        },

        updateVatDisplay(cardQuoteId, vatRate) {
            $(`.total-vat-input[data-cardquoteid="${cardQuoteId}"]`).each((_, element) => {
                const $select = $(element).find('select');
                $select.val(vatRate.toString());
            });
        },

        updateAllCalculations() {
            const cardQuoteIds = new Set();
            $('.column_single_price').each(function () {
                const quoteId = $(this).data('cardquoteid');
                if (quoteId) cardQuoteIds.add(quoteId);
            });

            cardQuoteIds.forEach(cardQuoteId => {
                $('.item_row').each((_, row) => {
                    const $row = $(row);
                    const totalPrice = this.calculateItemTotal($row, cardQuoteId);
                    $row.find(`.column_total_price[data-cardquotetotalprice="${cardQuoteId}"]`).text(totalPrice);
                });

                $('.group_row').each((_, row) => {
                    const $row = $(row);
                    const groupTotal = this.calculateGroupTotal($row, cardQuoteId);
                    $row.find(`[data-cardquotegrouptotalprice="${cardQuoteId}"]`).text(groupTotal);
                });

                this.calculateTotals(cardQuoteId);
            });
        },
        searchTableItem() {
            const searchInput = document.querySelector('#table-search');
            const tableRows = document.querySelectorAll('#estimation-edit-table tbody tr:not(.item_child)');
            const searchTerm = searchInput.value.toLowerCase();

            tableRows.forEach(row => {
                const nameCell = row.querySelector('.column_name');
                const name = nameCell.textContent.toLowerCase();

                if (searchTerm === '') {
                    row.style.display = '';
                    const descRow = row.nextElementSibling;
                    if (descRow && descRow.classList.contains('item_child')) {
                        descRow.style.display = '';
                    }
                } else if (name.includes(searchTerm)) {
                    row.style.display = '';
                    const descRow = row.nextElementSibling;
                    if (descRow && descRow.classList.contains('item_child')) {
                        descRow.style.display = '';
                    }
                } else {
                    row.style.display = 'none';
                    const descRow = row.nextElementSibling;
                    if (descRow && descRow.classList.contains('item_child')) {
                        descRow.style.display = 'none';
                    }
                }
            });
        },

        updateQuateTypeStatus(Checkbox, QuoteId, Type) { 
            const checkboxValue = Checkbox ? 1 : 0;   
            const $cardQuote = $(`.cardQuote[data-cardquoteid="${QuoteId}"]`);

            $cardQuote.removeClass('quote clientQuote subcontractor');

            if (Checkbox) {
                if (Type === 'quote') {
                    $cardQuote.addClass('quote');
                }
                if (Type === 'clientQuote') {
                    $cardQuote.addClass('clientQuote');
                }
                if (Type === 'subcontractor') {
                    $cardQuote.addClass('subcontractor');
                }
            }

            $.ajax({
                url: route('estimation.quateTypesStatus', QuoteId),
                type: "POST",
                data: { type: Type, checkbox: checkboxValue },
            });

        },
    };

    $(document).ajaxComplete(function () {
        if ($('#sub-contractor').length > 0) {
            $('#sub-contractor').select2({
                dropdownParent: $("#commonModal")
            });
        }
    });

    EstimationTable.init();
}); 