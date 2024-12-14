$(document).ready(function () {
    const EstimationTable = {
        estimation: $('.estimation-show'),
        templates: {
            item: $('#add-item-template').html(),
            group: $('#add-group-template').html(),
            comment: $('#add-comment-template').html()
        },
        originalPrices: new Map(),

        init() {
            if (!this.validateTemplates()) return;
            this.bindEvents();
            this.initializeSortable();
            this.updateAllCalculations();
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
            this.estimation.on('click', '.grp-dt-control', this.toggleGroup);

            this.estimation.on('blur', '.item-quantity, .item-price', (event) => {
                const $target = $(event.currentTarget);
                this.formatInput($target);
                this.updateAllCalculations();
            });

            this.estimation.on('change', '.item-optional', () => {
                this.updateAllCalculations();
            });

            this.estimation.on('change', 'select[name^="item"][name$="[tax]"]', () => {
                this.updateAllCalculations();
            });

            this.estimation.on('blur', 'input[name^="item"][name$="[discount]"]', (event) => {
                const $input = $(event.target);
                const value = this.parseGermanDecimal($input.val());

                // Format the number
                $input.val(this.formatGermanDecimal(value));

                // Apply styling for negative values
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

            // Enhanced markup input handler
            this.estimation.on('blur', 'input[name^="item"][name$="[markup]"]', (event) => {
                const $input = $(event.target);
                const value = this.parseGermanDecimal($input.val());
                const cardQuoteId = $input.attr('name').match(/\[(\d+)\]/)[1];

                // Format the number
                $input.val(this.formatGermanDecimal(value));

                // Apply styling for negative values
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
        },

        storeOriginalPrices() {
            // Clear existing stored prices
            this.originalPrices.clear();

            $('.item-price').each((_, element) => {
                const $price = $(element);
                const cardQuoteId = $price.data('cardquotesingleprice');
                const itemId = $price.closest('tr').data('itemid');
                const key = `${cardQuoteId}-${itemId}`;

                // Only store if not already stored
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
                    .replace(/{TEMPLATE_ID}/g, timestamp)
                    .replace(/{TEMPLATE_POS}/g, this.getNextGroupPosition());

                $('#estimation-items').append(newGroup);
                this.updatePOSNumbers();
                this.updateAllCalculations();
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
            this.updateAllCalculations();
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
                    this.updateAllCalculations();
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

        toggleGroup(event) {
            const icon = $(event.currentTarget);
            const row = icon.closest('tr.group_row');
            const groupId = row.data('groupid');

            // First toggle all main items
            const mainItems = $(`tr.item_row[data-groupid="${groupId}"], tr.item_comment[data-groupid="${groupId}"]`);
            mainItems.toggle();

            // Handle description rows based on their item's toggle state
            mainItems.each(function () {
                const itemId = $(this).data('itemid');
                const descRow = $(`.item_child[data-itemid="${itemId}"]`);
                const itemToggleIcon = $(this).find('.desc_toggle');

                // Only show description if parent is visible AND toggle is expanded
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
            if (typeof value === 'number') return value;
            return parseFloat(
                String(value)
                    .replace(/[^\d,-]/g, '')
                    .replace(',', '.')
            ) || 0;
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
                currency: 'EUR'
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
            // If originalPrices is empty, store them first
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
            if (itemRow.find('.item-optional').is(':checked')) return '-';

            const quantity = this.parseGermanDecimal(itemRow.find('.item-quantity').val());
            const currentPrice = this.parseGermanDecimal(
                itemRow.find(`.item-price[data-cardquotesingleprice="${cardQuoteId}"]`).val()
            );
            const total = quantity * currentPrice;

            return total === 0 ? '-' : this.formatGermanCurrency(total);
        }, 

        calculateGroupTotal(groupRow, cardQuoteId) {
            let total = 0;
            const groupId = groupRow.data('groupid');

            $(`.item_row[data-groupid="${groupId}"]`).each((_, item) => {
                const $item = $(item);
                if (!$item.find('.item-optional').is(':checked')) {
                    const quantity = this.parseGermanDecimal($item.find('.item-quantity').val());
                    const basePrice = this.parseGermanDecimal($item.find(`.item-price[data-cardquotesingleprice="${cardQuoteId}"]`).val());
                    const markup = this.parseGermanDecimal($(`input[name="item[${cardQuoteId}][markup]"]`).val()) || 0;

                    // Apply markup to single price
                    const priceWithMarkup = basePrice * (1 + markup / 100);
                    total += quantity * priceWithMarkup;
                }
            });

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

            // Update all display values
            $(`.total-net[data-cardquoteid="${cardQuoteId}"]`).text(this.formatGermanCurrency(netTotal));
            $(`.total-net-discount[data-cardquoteid="${cardQuoteId}"]`).text(this.formatGermanCurrency(netAfterDiscount));
            $(`.total-gross-discount[data-cardquoteid="${cardQuoteId}"]`).text(this.formatGermanCurrency(grossAfterDiscount));
            $(`.total-gross-total[data-cardquoteid="${cardQuoteId}"]`).text(this.formatGermanCurrency(grossTotal));

            // Update additional gross total display
            $(`.totalnr.total-gross-total[data-cardquoteid="${cardQuoteId}"]`).text(this.formatGermanCurrency(grossTotal));
        },

        updateDisplayValues(cardQuoteId, values) {
            const formatCurrency = this.formatGermanCurrency.bind(this);
            const { net, netAfterDiscount, grossAfterDiscount, vatRate } = values;

            $(`.total-net[data-cardquoteid="${cardQuoteId}"]`).text(formatCurrency(net));
            $(`.total-net-discount[data-cardquoteid="${cardQuoteId}"]`).text(formatCurrency(netAfterDiscount));
            $(`.total-gross-discount[data-cardquoteid="${cardQuoteId}"]`).text(formatCurrency(grossAfterDiscount));

            // Update VAT details
            const $vatSelect = $(`select[name="item[${cardQuoteId}][tax]"]`);
            $vatSelect.val(vatRate.toString());

            // Update gross amount with VAT
            const grossWithVat = net * (1 + vatRate / 100);
            $(`.total-gross[data-cardquoteid="${cardQuoteId}"]`).text(formatCurrency(grossWithVat));
        },

        applySinglePriceMarkup(price, markup) {
            return price * (1 + markup / 100);
        },

        updateItemPrices(cardQuoteId) {
            const markup = this.parseGermanDecimal($(`input[name="item[${cardQuoteId}][markup]"]`).val()) || 0;

            // Update all single prices with markup
            $(`.item_row`).each((_, row) => {
                const $row = $(row);
                const $priceInput = $row.find(`.item-price[data-cardquotesingleprice="${cardQuoteId}"]`);
                const basePrice = this.parseGermanDecimal($priceInput.val());
                const priceWithMarkup = this.applySinglePriceMarkup(basePrice, markup);
                $priceInput.val(this.formatGermanCurrency(priceWithMarkup));
            });
        },

        updateTotalDisplay(cardQuoteId, totals) {
            // Update Net incl. Discount
            $(`.total-net-discount[data-cardquoteid="${cardQuoteId}"]`)
                .text(this.formatGermanCurrency(totals.netIncDiscount));

            // Update Gross incl. Discount (with VAT)
            $(`.total-gross-discount[data-cardquoteid="${cardQuoteId}"]`)
                .text(this.formatGermanCurrency(totals.grossIncDiscount));

            // Update Net
            $(`.total-net[data-cardquoteid="${cardQuoteId}"]`)
                .text(this.formatGermanCurrency(totals.net));

            // Update both Gross VAT displays
            $(`.total-gross-total[data-cardquoteid="${cardQuoteId}"]`)
                .text(this.formatGermanCurrency(totals.gross));

            // Update VAT display on both sides
            this.updateVatDisplay(cardQuoteId, totals.vatRate);
        },

        updateVatDisplay(cardQuoteId, vatRate) {
            // Update VAT display for both columns
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
                // Update item totals
                $('.item_row').each((_, row) => {
                    const $row = $(row);
                    const totalPrice = this.calculateItemTotal($row, cardQuoteId);
                    $row.find(`.column_total_price[data-cardquotetotalprice="${cardQuoteId}"]`).text(totalPrice);
                });

                // Update group totals
                $('.group_row').each((_, row) => {
                    const $row = $(row);
                    const groupTotal = this.calculateGroupTotal($row, cardQuoteId);
                    $row.find(`[data-cardquotegrouptotalprice="${cardQuoteId}"]`).text(groupTotal);
                });

                // Calculate final totals
                this.calculateTotals(cardQuoteId);
            });
        },
    };

    EstimationTable.init();
});