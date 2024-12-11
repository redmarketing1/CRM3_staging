document.addEventListener('alpine:init', () => {
    Alpine.data('estimationShow', () => ({
        items: {},
        comments: {},
        groups: {},
        newItems: {},
        tableData: {},
        totals: {},
        expandedRows: {},
        lastGroupNumber: 0,
        lastItemNumbers: {},
        searchQuery: '',
        selectAll: false,
        isFullScreen: false,
        autoSaveEnabled: true,
        lastSaveTime: 0,
        saveTimeout: null,
        saveInterval: 1000 * 30, // 30 second
        hasUnsavedChanges: false,
        isInitializing: true,
        lastSaveTimestamp: null,
        lastSaveText: '',
        contextMenu: {
            show: false,
            x: 0,
            y: 0,
            selectedRowId: null
        },
        columnVisibility: {
            column_pos: true,
            column_name: true,
            column_quantity: true,
            column_unit: true,
            column_optional: true,
            quote_th: true
        },

        init() {

            this.tableData = JSON.parse(document.querySelector('#estimation-edit-table').dataset.table);

            this.$nextTick(() => {
                this.isInitializing = true;
                this.initializeData();
                this.initializeSortable();
                this.initializeLastNumbers();
                this.initializeContextMenu();
                this.initializeColumnVisibility();
                this.initializeCardCalculations();
                this.initializeAutoSave();
                this.calculateTotals();
                this.$nextTick(() => {
                    this.isInitializing = false; // Reset flag after all initialization is done
                });
            });


            this.$watch('items', (value) => {
                this.calculateTotals();
                const cardQuoteIds = [...new Set(Array.from(document.querySelectorAll('[data-cardquoteid]')).map(el => el.dataset.cardquoteid))];
                cardQuoteIds.forEach(cardQuoteId => this.calculateCardTotals(cardQuoteId));
            }, { deep: true });

            this.$watch('searchQuery', () => this.filterTable());
            this.$watch('selectAll', (value) => this.checkboxAll(value));

            document.addEventListener('click', (e) => {
                if (!e.target.closest('.context-menu')) {
                    this.showContextMenu = false;
                }
            });

            this.$watch('hasUnsavedChanges', () => {
                if (!this.hasUnsavedChanges) {
                    $('#save-button').css({
                        'cursor': 'not-allowed',
                        'background': '#bfbfbf',
                        'border': '0',
                        'pointer-events': 'none'
                    });
                } else {
                    $('#save-button').removeAttr('style');
                }
            });

            document.addEventListener('fullscreenchange', () => {
                this.isFullScreen = !!document.fullscreenElement;
                const icon = document.querySelector('.fa-expand, .fa-compress');
                if (icon) {
                    icon.className = this.isFullScreen ? 'fa-solid fa-compress' : 'fa-solid fa-expand';
                }
            });

            document.addEventListener('keydown', (e) => {
                if (e.target.classList.contains('item-quantity') || e.target.classList.contains('item-name') || e.target.classList.contains('item-price') || e.target.classList.contains('form-blur')) {
                    this.handleInputBlur(e);
                }
            });

            if (this.lastSaveTimestamp) {
                this.startTimeAgoUpdates();
            }
        },

        initializeData() {
            const cardQuoteIds = [...new Set(
                Array.from(document.querySelectorAll('[data-cardquoteid]'))
                    .map(el => el.dataset.cardquoteid)
            )];

            this.tableData.estimation_groups?.forEach(group => {
                const groupData = {
                    id: group.id,
                    type: 'group',
                    name: group.group_name,
                    pos: group.group_pos,
                    total: 0,
                    expanded: false,
                };

                this.newItems[group.id] = groupData;
                this.lastGroupNumber = Math.max(this.lastGroupNumber, parseInt(group.group_pos));

                group.estimation_products?.forEach(item => {
                    const itemData = {
                        id: item.id,
                        type: item.type,
                        groupId: item.group_id,
                        name: item.name,
                        description: item.description,
                        content: item.comment,
                        quantity: item.quantity,
                        unit: item.unit,
                        optional: item.is_optional,
                        pos: item.pos,
                        prices: this.initializePrices(cardQuoteIds, item, item.quote_items || [])
                    };
                    this.newItems[item.id] = itemData;
                });
            });
        },

        initializePrices(cardQuoteIds, item = null, quoteItems = []) {
            return cardQuoteIds.reduce((acc, quoteId) => {
                const quoteItem = quoteItems.find(quote => quote.estimate_quote_id == quoteId) || {};

                acc[quoteId] = {
                    quoteId: quoteId,
                    type: item?.type || null,
                    singlePrice: quoteItem.base_price || 0,
                    totalPrice: quoteItem.total_price || (item?.quantity || 0) * (quoteItem.price || 0)
                };

                return acc;
            }, {});
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
            if (!document.querySelector('#quote_form')) {
                console.warn('Quote form not found');
                return;
            }

            // Initialize auto-save related event listeners
            window.addEventListener('beforeunload', (e) => {
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

        calculateItemTotal(itemId, quoteId) {
            // Early validation
            if (!itemId || !this.newItems[itemId]) return 0;
            if (this.newItems[itemId].optional) return 0;

            const item = this.newItems[itemId];
            const quantity = this.parseNumber(item.quantity || 0);
            const singlePrice = this.parseNumber(item.prices[quoteId]?.singlePrice || 0);
            const totalPrice = quantity * singlePrice;

            if (item.prices[quoteId]) {
                item.prices[quoteId].totalPrice = totalPrice;
            }

            return totalPrice;
        },

        calculateTotals() {
            this.totals = {};
            document.querySelectorAll('tr.group_row').forEach(row => {
                const groupId = row.dataset.groupid;
                if (!groupId) return;

                this.calculateGroupTotal(groupId);

                if (this.groups[groupId]) {
                    this.groups[groupId].total = this.parseNumber(
                        row.querySelector('.text-right.grouptotal')?.textContent || '0'
                    );
                }
            });
        },

        calculateGroupTotal(groupId) {
            let totals = {};
            const groupRow = document.querySelector(`tr.group_row[data-groupid="${groupId}"]`);
            if (!groupRow) return 0;

            let currentRow = groupRow.nextElementSibling;
            while (currentRow && !currentRow.classList.contains('group_row')) {
                if (currentRow.classList.contains('item_row')) {
                    const itemId = currentRow.dataset.itemid;
                    const isOptional = currentRow.querySelector('.item-optional')?.checked;

                    if (!isOptional) {
                        const quantity = this.parseNumber(currentRow.querySelector('.item-quantity')?.value || '0');
                        const priceInputs = currentRow.querySelectorAll('.item-price');

                        priceInputs.forEach((priceInput, index) => {
                            if (!totals[index]) totals[index] = 0;
                            const price = this.parseNumber(priceInput.value || '0');
                            const total = quantity * price;
                            totals[index] += total;

                            // Update individual item total
                            const totalCell = currentRow.querySelectorAll('.column_total_price')[index];
                            if (totalCell) {
                                totalCell.textContent = this.formatCurrency(total);
                                this.setNegativeStyle(totalCell, total);
                            }
                        });
                    } else {
                        // Clear totals for optional items
                        currentRow.querySelectorAll('.column_total_price').forEach(cell => {
                            cell.textContent = '-';
                            cell.style.backgroundColor = '';
                            cell.style.color = '';
                        });
                    }
                }
                currentRow = currentRow.nextElementSibling;
            }

            // Update group totals
            const totalCells = groupRow.querySelectorAll('.text-right.grouptotal');
            totalCells.forEach((cell, index) => {
                const total = totals[index] || 0;
                cell.textContent = this.formatCurrency(total);
                this.setNegativeStyle(cell, total);

                // Update card totals
                const cardQuoteId = cell.dataset.cardquoteid;
                if (cardQuoteId) {
                    this.calculateCardTotals(cardQuoteId);
                }
            });

            return totals[0] || 0;
        },

        calculateCardTotals(cardQuoteId) {
            let subtotal = 0;
            const groupTotalCells = document.querySelectorAll(`td[data-cardquoteid="${cardQuoteId}"].grouptotal`);
            groupTotalCells.forEach(cell => {
                subtotal += this.parseNumber(cell.textContent);
            });

            const markupInput = document.querySelector(`#quoteMarkup[name="item[${cardQuoteId}][markup]"]`);
            const markup = this.parseNumber(markupInput?.value || '0');
            const netAmount = subtotal + markup;

            const discountInput = document.querySelector(`input[name="item[${cardQuoteId}][discount]"]`);
            const cashDiscount = this.parseNumber(discountInput?.value || '0');
            const discountAmount = (netAmount * cashDiscount) / 100;
            const netWithDiscount = netAmount - discountAmount;

            const vatSelect = document.querySelector(`select[name="item[${cardQuoteId}][tax]"]`);
            const vatRate = vatSelect ? this.parseNumber(vatSelect.value) / 100 : 0;

            let grossWithDiscount = netWithDiscount;
            if (vatRate > 0) {
                const vatAmount = grossWithDiscount * vatRate;
                grossWithDiscount = netWithDiscount + vatAmount;
            }

            this.updateCardTotalUI(cardQuoteId, {
                netAmount,
                netWithDiscount,
                grossWithDiscount,
                subtotal
            });
        },

        updateCardTotalUI(cardQuoteId, totals) {

            const netDiscountElement = document.querySelector(`th[data-cardquoteid="${cardQuoteId}"].total-net-discount`);
            if (netDiscountElement) {
                netDiscountElement.textContent = this.formatCurrency(totals.netWithDiscount);
            }


            const grossDiscountElement = document.querySelector(`th[data-cardquoteid="${cardQuoteId}"].total-gross-discount`);
            if (grossDiscountElement) {
                grossDiscountElement.textContent = this.formatCurrency(totals.grossWithDiscount);
            }


            const netElement = document.querySelector(`th[data-cardquoteid="${cardQuoteId}"].total-net`);
            if (netElement) {
                netElement.textContent = this.formatCurrency(totals.netAmount);
            }


            const grossElement = document.querySelector(`th[data-cardquoteid="${cardQuoteId}"] .total-gross-total`);
            if (grossElement) {
                grossElement.textContent = this.formatCurrency(totals.grossWithDiscount);
            }

            this.autoSaveHandler();
        },

        initializeCardCalculations() {
            const cardQuoteIds = [...new Set(Array.from(document.querySelectorAll('[data-cardquoteid]')).map(el => el.dataset.cardquoteid))];

            cardQuoteIds.forEach(cardQuoteId => {
                // Get initial values
                const markupInput = document.querySelector(`#quoteMarkup[name="item[${cardQuoteId}][markup]"]`);
                const discountInput = document.querySelector(`input[name="item[${cardQuoteId}][discount]"]`);
                const vatSelect = document.querySelector(`select[name="item[${cardQuoteId}][tax]"]`);

                // Format and apply markup
                if (markupInput) {
                    const value = this.parseNumber(markupInput.value);
                    markupInput.value = this.formatDecimal(value);
                    this.setNegativeStyle(markupInput, value);

                    // Apply markup calculations
                    this.updateMarkupCalculations({ target: markupInput }, cardQuoteId);
                }

                // Format and apply discount
                if (discountInput) {
                    const value = this.parseNumber(discountInput.value);
                    discountInput.value = this.formatDecimal(value);
                    // Trigger discount calculations
                    this.handleInputBlur({ target: discountInput }, 'cashDiscount');
                }

                // Apply VAT calculations if set
                if (vatSelect) {
                    this.handleVatChangeAmount({ target: vatSelect }, cardQuoteId);
                }

                // Calculate all totals for this column
                this.calculateCardTotals(cardQuoteId);
            });
        },

        handleVatChangeAmount(event, cardQuoteId) {
            this.calculateCardTotals(cardQuoteId);
        },

        updateMarkupCalculations(event, cardQuoteId) {
            const target = event.target;
            const markup = this.parseNumber(target.value || '0');
            target.value = this.formatDecimal(markup);
            this.setMarkupStyle(target, markup);

            const priceInputs = document.querySelectorAll(`[data-cardquoteid="${cardQuoteId}"] .item-price`);

            priceInputs.forEach(input => {
                const originalPrice = input.dataset.originalPrice ? this.parseNumber(input.dataset.originalPrice) : this.parseNumber(input.value);

                if (!input.dataset.originalPrice) {
                    input.dataset.originalPrice = originalPrice;
                }

                const newPrice = this.parseNumber(input.dataset.originalPrice) + markup;
                input.value = this.formatCurrency(newPrice);

                const row = input.closest('tr');
                if (row) {
                    const itemId = row.dataset.itemid;
                    if (itemId) {
                        this.calculateItemTotal(itemId);
                    }
                }
            });

            this.calculateGroupTotal(this.lastGroupId);
            this.calculateTotals();
            this.calculateCardTotals(cardQuoteId);
        },

        setMarkupStyle(input, value) {
            if (value < 0) {
                input.style.backgroundColor = 'rgb(255 240 240)';
                input.style.color = 'rgb(255 6 6)';
            } else {
                input.style.backgroundColor = '';
                input.style.color = '';
            }
        },

        setNegativeStyle(element, value) {
            if (value < 0) {
                element.style.backgroundColor = 'rgb(255 240 240)';
                element.style.color = 'rgb(255 6 6)';
            } else {
                element.style.backgroundColor = '';
                element.style.color = '';
            }
        },

        formatDecimal(value) {
            return new Intl.NumberFormat('de-DE', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(value);
        },

        formatCurrency(value) {
            return new Intl.NumberFormat('de-DE', {
                style: 'currency',
                currency: 'EUR'
            }).format(value);
        },

        formatDecimalValue(target) {
            target.value = this.formatDecimal(this.parseNumber(target.value));
        },

        formatCurrencyValue(target) {
            target.value = this.formatCurrency(this.parseNumber(target.value));
        },

        parseNumber(value) {
            if (typeof value === 'number') return value;
            return parseFloat(value.replace(/[^\d,-]/g, '').replace(',', '.')) || 0;
        },

        updateItemPriceAndTotal(itemId) {
            if (!itemId || !this.items[itemId]) return [];

            const item = this.items[itemId];
            const quantity = this.parseNumber(item.quantity || '0');

            const cardQuoteIds = [...new Set(
                Array.from(document.querySelectorAll('[data-cardquoteid]'),
                    el => el.dataset.cardquoteid)
            )];

            const prices = cardQuoteIds.map(quoteId => ({
                id: quoteId,
                singlePrice: this.parseNumber(item.price || '0'),
                totalPrice: quantity * this.parseNumber(item.price || '0')
            }));

            if (this.items[itemId]) {
                this.items[itemId].prices = prices;
            }

            return prices;
        },

        handleInputBlur(event, type) {
            if (!event?.target || !type) return;
            if (event.type === 'keydown' && event.key !== 'Enter') return;
            if (event.type === 'keydown') event.target.blur();

            const row = event.target.closest('tr');
            if (!row?.dataset) return;

            const { value } = event.target;
            const { itemid, commentid, groupid } = row.dataset;
            const cardQuoteId = event.target.closest('[data-cardquoteid]')?.dataset.cardquoteid;

            const inputHandlers = {
                item: () => this.updateNewItem(itemid, { name: value }),
                comment: () => this.updateNewItem(commentid, { content: value }),
                group: () => this.updateNewItem(groupid, { name: value }),
                quantity: () => {
                    const quantity = this.parseNumber(value);
                    this.updateNewItem(itemid, { quantity });
                    this.updateItemPrices(itemid);
                    this.formatDecimalValue(event.target);
                },
                price: () => {
                    const singlePrice = this.parseNumber(value);
                    this.updateNewItem(itemid, { singlePrice }, cardQuoteId);
                    this.updateItemPrices(itemid);
                    this.formatCurrencyValue(event.target);
                },
                unit: () => this.updateNewItem(itemid, { unit: value }),
                cashDiscount: () => {
                    event.target.value = this.formatDecimal(this.parseNumber(value) || 0);
                }
            };

            try {
                inputHandlers[type]?.();

                if (!this.isInitializing) {
                    this.hasUnsavedChanges = true;
                    this.calculateTotals();
                    this.autoSaveHandler();
                }
            } catch (error) {
                console.error(`Error in handleInputBlur: ${error.message}`, { type, value });
            }
        },

        updateItemPrices(itemId) {
            if (!this.newItems[itemId]) return;

            const item = this.newItems[itemId];
            const quantity = item.quantity || 0;

            Object.keys(item.prices || {}).forEach(cardQuoteId => {
                const price = item.prices[cardQuoteId];
                price.totalPrice = quantity * (price.singlePrice || 0);
            });
        },

        updateNewItem(id, updates, cardQuoteId = null) {
            if (!this.newItems[id]) return;

            // Handle single field updates
            if (!cardQuoteId) {
                Object.assign(this.newItems[id], updates);
                return;
            }

            // Handle price updates for specific columns
            if (!this.newItems[id].prices) {
                this.newItems[id].prices = {};
            }

            if (!this.newItems[id].prices[cardQuoteId]) {
                this.newItems[id].prices[cardQuoteId] = {
                    singlePrice: 0,
                    totalPrice: 0
                };
            }

            Object.assign(this.newItems[id].prices[cardQuoteId], updates);
        },

        handleOptionalChange(event, itemId) {
            if (this.newItems[itemId]) {
                this.newItems[itemId].optional = event.target.checked ? 1 : 0;
            }

            const row = event.target.closest('tr');
            const groupId = row.dataset.groupid;

            if (groupId) {
                this.calculateGroupTotal(groupId);
            }

            // Update all totals
            this.calculateTotals();

            if (!this.isInitializing) {
                this.hasUnsavedChanges = true;
                this.autoSaveHandler();
            }
        },

        autoSaveHandler() {
            if (!this.autoSaveEnabled || !document.querySelector('#quote_form') || !this.hasUnsavedChanges) return;

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
                    if (this.hasUnsavedChanges) {
                        this.saveTableData();
                        this.lastSaveTime = Date.now();
                    }
                }, this.saveInterval);
            }
        },

        filterTable() {
            const searchTerm = this.searchQuery.toLowerCase();
            Object.entries(this.newItems).forEach(([itemId, item]) => {
                const row = document.querySelector(`tr[data-itemid="${itemId}"]`);
                if (row) {
                    row.style.display = item.name.toLowerCase().includes(searchTerm) ||
                        item.pos.toLowerCase().includes(searchTerm) ? '' : 'none';
                }
            });
        },

        initializeSortable() {
            $("#estimation-edit-table").sortable({
                items: 'tbody tr',
                cursor: 'pointer',
                axis: 'y',
                dropOnEmpty: true,
                handle: '.fa-bars, .fa-up-down',
                animation: 150,
                start: function (e, ui) {
                    // ui.item.addClass("selected");
                },
                stop: (event, ui) => {
                    const movedRow = ui.item[0];

                    if (movedRow.classList.contains('item_row') || movedRow.classList.contains('item_comment')) {
                        let currentRow = movedRow.previousElementSibling;
                        let newGroupRow = null;

                        // Find the new group
                        while (currentRow && !newGroupRow) {
                            if (currentRow.classList.contains('group_row')) {
                                newGroupRow = currentRow;
                            }
                            currentRow = currentRow.previousElementSibling;
                        }

                        if (newGroupRow) {
                            const newGroupId = newGroupRow.dataset.groupid;
                            const itemId = movedRow.dataset.itemid || movedRow.dataset.commentid;
                            const oldGroupId = movedRow.dataset.groupid;

                            // Update group ID in DOM
                            movedRow.dataset.groupid = newGroupId;

                            // Update items/comments in state
                            if (movedRow.classList.contains('item_row')) {
                                if (this.newItems[itemId]) {
                                    this.newItems[itemId].groupId = newGroupId;
                                }
                            } else if (movedRow.classList.contains('item_comment')) {
                                if (this.newItems[itemId]) {
                                    this.newItems[itemId].groupId = newGroupId;
                                }
                            }

                            // Update old and new group calculations
                            this.calculateGroupTotal(oldGroupId);
                            this.calculateGroupTotal(newGroupId);
                        }
                    }

                    // Update positions and recalculate all totals
                    this.updatePOSNumbers();
                    this.calculateTotals();

                    // Mark as unsaved and trigger auto-save
                    if (!this.isInitializing) {
                        this.hasUnsavedChanges = true;
                        this.autoSaveHandler();
                    }
                }
            });
        },

        initializeLastNumbers() {
            const posNumbers = new Set();

            document.querySelectorAll('.pos-inner').forEach(element => {
                const pos = element.textContent.trim();
                if (pos) {
                    posNumbers.add(pos);
                    const [groupNum, itemNum] = pos.split('.');
                    const groupNumber = parseInt(groupNum);
                    const itemNumber = parseInt(itemNum);

                    this.lastGroupNumber = Math.max(this.lastGroupNumber, groupNumber);

                    if (!this.lastItemNumbers[groupNumber] || itemNumber > this.lastItemNumbers[groupNumber]) {

                        this.lastItemNumbers[groupNumber] = Math.min(itemNumber, 99);
                    }
                }
            });

            document.querySelectorAll('.grouppos').forEach(element => {
                const groupNum = parseInt(element.textContent.trim());
                this.lastGroupNumber = Math.max(this.lastGroupNumber, groupNum);
            });
        },

        addItem(type, targetGroupId = null) {
            const timestamp = Date.now();

            if (type === 'group') {
                this.createGroups(type, timestamp);
            } else if (type === 'item' || type === 'comment') {
                this.createItemsAndComments(type, timestamp, targetGroupId);
            }
        },

        createGroups(type, timestamp, targetRowId) {

            if (type !== 'group') return;

            const itemTimestamp = Date.now() + 1;
            const hasAnyGroups = Object.keys(this.groups).length > 0;

            // Helper to create a group
            const createGroup = (id, name, itemCount = 0) => ({
                id,
                type: 'group',
                name,
                total: 0,
                expanded: false,
                pos: '',
                itemCount,
            });

            this.groups[timestamp] = createGroup(timestamp, 'Group Name');
            this.newItems[timestamp] = createGroup(timestamp, 'Group Name');

            if (!hasAnyGroups) {
                this.groups[timestamp] = createGroup(timestamp, 'New Group');
                this.newItems[timestamp] = createGroup(timestamp, 'New Group');
                this.createItemsAndComments('item', itemTimestamp, targetRowId);
            } else {
                this.groups[timestamp] = createGroup(timestamp, 'Group Name');
                this.newItems[timestamp] = createGroup(timestamp, 'Group Name');
            }

            this.$nextTick(() => {
                this.initializeSortable();
                this.updatePOSNumbers();
                this.calculateTotals();
            });

            return;
        },

        createItemsAndComments(type, timestamp, targetGroupId = null) {
            if (type !== 'item' && type !== 'comment') return;

            const initialPrices = [...new Set(
                Array.from(document.querySelectorAll('[data-cardquoteid]'), el => el.dataset.cardquoteid)
            )].map(quoteId => ({ quoteId, type: 'item', singlePrice: 0, totalPrice: 0 }));

            // Use passed targetGroupId or get from current context
            const currentGroupId = targetGroupId || this.getCurrentGroupId();
            if (!currentGroupId) return;

            if (type === 'item') {
                const items = {
                    id: timestamp,
                    type: 'item',
                    groupId: currentGroupId,
                    name: 'New Item',
                    quantity: 0,
                    price: 0,
                    prices: initialPrices,
                    unit: '',
                    optional: 0,
                    expanded: false,
                    pos: '',
                };

                this.items[timestamp] = items;
                this.newItems[timestamp] = items;
            } else {
                const comment = {
                    id: timestamp,
                    type: 'comment',
                    groupId: currentGroupId,
                    content: 'New Comment',
                    expanded: false,
                    pos: '',
                };

                this.comments[timestamp] = comment;
                this.newItems[timestamp] = comment;
            }

            this.$nextTick(() => {
                this.updatePOSNumbers();
                this.calculateTotals();
            });
        },

        removeItem() {
            const selectedCheckboxes = document.querySelectorAll('.item_selection:checked');
            if (selectedCheckboxes.length === 0) {
                toastrs("Error", "Please select checkbox to continue delete");
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
                    const estimationId = this.getFomrData().id;
                    const itemIds = [];
                    const groupIds = [];

                    selectedCheckboxes.forEach(checkbox => {
                        const row = checkbox.closest('tr');

                        if (row.classList.contains('group_row')) {
                            groupIds.push(row.dataset.groupid);
                            delete this.groups[row.dataset.groupid];
                            delete this.newItems[row.dataset.groupid];
                        } else {
                            const IDs = row.dataset.id;
                            itemIds.push(IDs);
                            delete this.newItems[IDs];
                        }
                        row.remove();
                    });

                    document.querySelector('.SelectAllCheckbox').checked = false;

                    $.ajax({
                        url: route('estimation.destroy', estimationId),
                        method: 'DELETE',
                        data: {
                            estimationId: estimationId,
                            items: itemIds,
                            groups: groupIds
                        },
                        success: (response) => {
                            console.log(response);
                        }
                    });

                    this.calculateTotals();
                    this.updatePOSNumbers();
                }
            });
        },

        getCurrentGroupId(targetRowId) {
            if (targetRowId) {
                const targetRow = document.querySelector(
                    `tr[data-id="${targetRowId}"], 
                 tr[data-itemid="${targetRowId}"], 
                 tr[data-commentid="${targetRowId}"], 
                 tr[data-groupid="${targetRowId}"]`
                );
                return targetRow?.dataset.groupid || null;
            } else {
                const allGroupRows = document.querySelectorAll('tr.group.group_row');
                const lastGroupRow = allGroupRows[allGroupRows.length - 1];
                return lastGroupRow?.dataset.groupid || null;
            }
        },

        updatePOSNumbers() {
            let currentGroupPos = 0;
            let itemCountInGroup = 0;
            let lastGroupId = null;

            document.querySelectorAll('tr').forEach(row => {
                if (row.classList.contains('group_row')) {
                    currentGroupPos++;
                    itemCountInGroup = 0;
                    lastGroupId = row.dataset.groupid;

                    const groupPos = currentGroupPos.toString().padStart(2, '0');
                    row.querySelector('.grouppos').textContent = `${groupPos}`;

                    if (this.groups[lastGroupId]) {
                        this.groups[lastGroupId].pos = groupPos;
                    }
                }
                else if (row.classList.contains('item_row')) {
                    itemCountInGroup++;
                    const itemPos = `${currentGroupPos.toString().padStart(2, '0')}.${itemCountInGroup.toString().padStart(2, '0')}`;

                    row.querySelector('.pos-inner').textContent = itemPos;

                    const itemId = row.dataset.itemid;
                    if (this.items[itemId]) {
                        this.items[itemId].pos = itemPos;
                    }
                }
                else if (row.classList.contains('item_comment')) {
                    itemCountInGroup++;
                    const itemPos = `${currentGroupPos.toString().padStart(2, '0')}.${itemCountInGroup.toString().padStart(2, '0')}`;

                    row.querySelector('.pos-inner').textContent = itemPos;

                    const itemId = row.dataset.itemid || row.dataset.commentid;
                    if (this.comments[itemId]) {
                        this.comments[itemId].pos = itemPos;
                    }
                }
            });
        },

        getCurrentGroupId() {
            // Get the last group from sorted groups
            const groups = this.getSortedGroups();
            return groups.length > 0 ? groups[groups.length - 1].id : null;
        },

        getSortedGroups() {
            // Get all groups from newItems
            const groups = Object.values(this.newItems)
                .filter(item => item.type === 'group')
                .sort((a, b) => this.comparePOS(a.pos, b.pos));

            return groups;
        },

        getSortedItemsForGroup(groupId) {
            if (!groupId) return [];

            // Get all items and comments for this group from newItems
            return Object.values(this.newItems)
                .filter(item =>
                    item.groupId === groupId &&
                    (item.type === 'item' || item.type === 'comment')
                )
                .sort((a, b) => this.comparePOS(a.pos, b.pos));
        },

        comparePOS(posA, posB) {
            if (!posA || !posB) return 0;

            const [groupA, itemA = "0"] = String(posA).split('.');
            const [groupB, itemB = "0"] = String(posB).split('.');

            const groupDiff = parseInt(groupA) - parseInt(groupB);
            if (groupDiff !== 0) return groupDiff;

            return parseInt(itemA) - parseInt(itemB);
        },

        duplicateCardColumn(quoteId) {
            alert('Action for duplicateCardColumn' + quoteId);
        },

        deleteCardColumn(quoteId) {
            Swal.fire({
                title: 'Confirmation Delete',
                text: 'Really! You want to remove this column? You can\'t undo',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Delete it',
                cancelButtonText: "No, cancel",
                customClass: {
                    confirmButton: 'btn btn-danger',
                    cancelButton: 'btn btn-secondary'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    try {

                        const elements = document.querySelectorAll(`[data-cardquoteid="${quoteId}"]`);
                        elements.forEach(el => el.remove());

                        this.calculateTotals();

                        Swal.fire({
                            title: 'Deleted!',
                            text: `The Column has been deleted successfully`,
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    } catch (error) {
                        console.error('Error deleting column:', error);
                        Swal.fire({
                            title: 'Error!',
                            text: 'An error occurred while deleting the column',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                }
            });
        },

        toggleDescription(index, event) {
            event.stopPropagation();

            if (!this.expandedRows) {
                this.expandedRows = {};
            }

            this.expandedRows[index] = !this.expandedRows[index];

            const parentRow = event.target.closest('tr');
            const childRow = document.querySelector(`tr.item_child[data-id="${index}"]`);

            if (childRow) {
                childRow.style.display = this.expandedRows[index] ? 'table-row' : 'none';

                const icon = parentRow.querySelector('.desc_toggle');
                if (icon) {
                    icon.classList.toggle('fa-caret-right');
                    icon.classList.toggle('fa-caret-down');
                }
            }
        },

        isExpanded(index) {
            return this.expandedRows[index] || false;
        },

        initializeContextMenu() {
            document.querySelector('#estimation-edit-table').addEventListener('contextmenu', (e) => {

                const row = e.target.closest('tr.item_row, tr.group_row, tr.item_comment');
                if (!row) return;

                e.preventDefault();

                const viewportWidth = window.innerWidth;
                const viewportHeight = window.innerHeight;

                let x = e.clientX;
                let y = e.clientY;

                if (x + 160 > viewportWidth) x = viewportWidth - 160;
                if (y + 160 > viewportHeight) y = viewportHeight - 160;

                this.contextMenu = {
                    show: true,
                    x: x,
                    y: y,
                    selectedRowId: row.dataset.id || row.dataset.itemid || row.dataset.commentid || row.dataset.groupid
                };
            });
        },

        moveRow(direction, rowId) {
            const row = document.querySelector(`tr[data-id="${rowId}"], 
                                         tr[data-itemid="${rowId}"], 
                                         tr[data-commentid="${rowId}"], 
                                         tr[data-groupid="${rowId}"]`);
            if (!row) return;

            if (direction === 'up') {
                const prevRow = row.previousElementSibling;
                if (prevRow) {
                    row.parentNode.insertBefore(row, prevRow);
                }
            } else {
                const nextRow = row.nextElementSibling;
                if (nextRow) {
                    row.parentNode.insertBefore(nextRow, row);
                }
            }

            this.updatePOSNumbers();
            this.calculateTotals();
            this.contextMenu.show = false;
        },

        duplicateRow(rowId) {
            const originalRow = document.querySelector(`tr[data-id="${rowId}"], 
                                                 tr[data-itemid="${rowId}"], 
                                                 tr[data-commentid="${rowId}"], 
                                                 tr[data-groupid="${rowId}"]`);
            if (!originalRow) return;

            const timestamp = Date.now();
            const isGroup = originalRow.classList.contains('group_row');
            const isComment = originalRow.classList.contains('item_comment');
            const groupId = isGroup ? null : originalRow.dataset.groupid;

            if (isGroup) {

                const groupName = originalRow.querySelector('.grouptitle-input').value;

                const newItem = {
                    id: timestamp,
                    type: 'group',
                    name: `${groupName} - copy`,
                    total: 0,
                    expanded: false
                };


                this.items[timestamp] = newItem;


                this.groups[timestamp] = {
                    id: timestamp,
                    pos: '',
                    name: `${groupName} - copy`,
                    total: 0,
                    itemCount: 0
                };
            } else if (isComment) {

                const newItem = {
                    id: timestamp,
                    type: 'comment',
                    groupId: groupId,
                    content: originalRow.querySelector('.item-description').value,
                    expanded: false
                };

                this.items[timestamp] = newItem;
            }
            else {

                const newItem = {
                    id: timestamp,
                    type: originalRow.classList.contains('item_comment') ? 'comment' : 'item',
                    groupId: groupId,
                    name: originalRow.querySelector('.item-name')?.value + ' - copy',
                    quantity: this.parseNumber(originalRow.querySelector('.item-quantity')?.value || '0'),
                    unit: originalRow.querySelector('.item-unit')?.value || '',
                    optional: originalRow.querySelector('.item-optional').checked ? 1 : 0,
                    price: this.parseNumber(originalRow.querySelector('.item-price')?.value || '0'),
                    expanded: false
                };


                this.items[timestamp] = newItem;


                if (this.groups[groupId]) {
                    this.groups[groupId].itemCount++;
                }
            }

            this.$nextTick(() => {

                const newRow = document.querySelector(`tr[data-id="${timestamp}"], tr[data-itemid="${timestamp}"], tr[data-commentid="${timestamp}"]`);
                if (newRow && originalRow.nextSibling) {
                    originalRow.parentNode.insertBefore(newRow, originalRow.nextSibling);
                }

                this.updatePOSNumbers();
                this.calculateTotals();
                this.initializeContextMenu();
            });

            this.contextMenu.show = false;
        },

        removeRowFromMenu(rowId) {
            Swal.fire({
                title: 'Confirmation Delete',
                text: 'Really! You want to remove this item? You can\'t undo',
                showCancelButton: true,
                confirmButtonText: 'Yes, Delete it',
                cancelButtonText: "No, cancel",
            }).then((result) => {
                if (result.isConfirmed) {
                    const estimationId = this.getFomrData().id;
                    const itemIds = [];
                    const comments = [];
                    const groupIds = [];

                    const row = document.querySelector(`tr[data-id="${rowId}"], tr[data-itemid="${rowId}"], tr[data-groupid="${rowId}"]`);
                    if (!row) return;

                    if (row.classList.contains('group_row')) {
                        groupIds.push(row.dataset.groupid);
                        delete this.groups[row.dataset.groupid];
                        delete this.newItems[row.dataset.groupid];
                    } else {
                        itemIds.push(row.dataset.itemid);
                        delete this.items[row.dataset.itemid];
                        delete this.newItems[row.dataset.itemid];

                        comments.push(row.dataset.commentid);
                        delete this.comments[row.dataset.commentid];
                        delete this.newItems[row.dataset.commentid];
                    }

                    $.ajax({
                        url: route('estimation.destroy', estimationId),
                        method: 'DELETE',
                        data: {
                            estimationId: estimationId,
                            items: itemIds.concat(comments),
                            groups: groupIds
                        },
                        success: (response) => {
                            console.log(response);
                        }
                    });

                    row.remove();
                    this.updatePOSNumbers();
                    this.calculateTotals();

                    document.querySelector('.SelectAllCheckbox').checked = false;
                }
            });

            this.contextMenu.show = false;
        },

        handleGroupSelection(event, groupId) {
            const checked = event.target.checked;
            const groupRow = event.target.closest('tr.group_row');

            if (!groupRow) return;


            let currentRow = groupRow.nextElementSibling;
            while (currentRow && !currentRow.classList.contains('group_row')) {
                const checkbox = currentRow.querySelector('.item_selection');
                if (checkbox) {
                    checkbox.checked = checked;
                }
                currentRow = currentRow.nextElementSibling;
            }
        },

        checkboxAll(value) {
            document.querySelectorAll('.item_selection').forEach(checkbox => {
                checkbox.checked = value;
            });
        },

        initializeColumnVisibility() {
            document.querySelectorAll('.column-toggle').forEach(checkbox => {
                checkbox.addEventListener('change', (e) => {

                    const columnClass = e.target.dataset.column;
                    const quoteId = e.target.dataset.quoteid;

                    if (columnClass === 'quote_th' && quoteId) {

                        this.columnVisibility[columnClass] = e.target.checked;
                        this.applyColumnVisibility(quoteId);
                    } else {

                        this.columnVisibility[columnClass] = e.target.checked;
                        this.applyColumnVisibility();
                    }
                });
            });
        },

        applyColumnVisibility(quoteId = null) {

            Object.entries(this.columnVisibility).forEach(([columnClass, isVisible]) => {

                if (columnClass === 'quote_th' && quoteId) {

                    const elements = document.querySelectorAll(
                        `.quote_th${quoteId}, ` +
                        `[data-cardquoteid="${quoteId}"]`
                    );

                    elements.forEach(el => {
                        el.style.display = isVisible ? '' : 'none';
                    });
                } else {
                    const elements = document.querySelectorAll(`.${columnClass}`);
                    elements.forEach(el => {
                        if (el.closest('td, th')) {
                            el.closest('td, th').style.display = isVisible ? '' : 'none';
                        }
                    });
                }
            });
        },

        toggleColumn(columnClass, quoteId = null) {
            this.columnVisibility[columnClass] = !this.columnVisibility[columnClass];
            this.applyColumnVisibility(quoteId);

            const selector = quoteId
                ? `.column-toggle[data-column="${columnClass}"][data-quote="${quoteId}"]`
                : `.column-toggle[data-column="${columnClass}"]`;
            const checkbox = document.querySelector(selector);
            if (checkbox) {
                checkbox.checked = this.columnVisibility[columnClass];
            }
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
                if (this.lastSaveTimestamp) {
                    this.lastSaveText = this.formatTimeAgo(this.lastSaveTimestamp);
                }
            }, 60000);
        },

        saveTableData() {

            if (!this.hasUnsavedChanges || !document.querySelector('#quote_form')) return;

            const columns = {};
            const cardQuoteIds = [...new Set(Array.from(document.querySelectorAll('[data-cardquoteid]')).map(el => el.dataset.cardquoteid))];

            cardQuoteIds.forEach(cardQuoteId => {
                columns[cardQuoteId] = {
                    settings: {
                        markup: this.parseNumber(document.querySelector(`input[name="item[${cardQuoteId}][markup]"]`)?.value || '0'),
                        cashDiscount: this.parseNumber(document.querySelector(`input[name="item[${cardQuoteId}][discount]"]`)?.value || '0'),
                        vat: this.parseNumber(document.querySelector(`select[name="item[${cardQuoteId}][tax]"]`)?.value || '0')
                    },
                    totals: {
                        netIncludingDiscount: this.parseNumber(document.querySelector(`th[data-cardquoteid="${cardQuoteId}"].total-net-discount`)?.textContent),
                        grossIncludingDiscount: this.parseNumber(document.querySelector(`th[data-cardquoteid="${cardQuoteId}"].total-gross-discount`)?.textContent),
                        net: this.parseNumber(document.querySelector(`th[data-cardquoteid="${cardQuoteId}"].total-net`)?.textContent),
                        gross: this.parseNumber(document.querySelector(`th[data-cardquoteid="${cardQuoteId}"] .total-gross-total`)?.textContent)
                    }
                };
            });

            const data = {
                cards: columns,
                form: this.getFomrData(),
                newItems: this.newItems,
            };

            $.ajax({
                url: route('estimation.update', data.form.id),
                method: 'PUT',
                data: data,
                beforeSend: () => {
                    this.lastSaveText = 'is running...';
                    $('#save-button').html('Saving... <i class="fa fa-arrow-right-rotate rotate"></i>');
                },
                success: (idMappings) => {

                    const updateEntities = (oldId, newId) => {
                        // Check if this item exists in newItems
                        const itemKey = Object.keys(this.newItems).find(key =>
                            this.newItems[key].id.toString() === oldId
                        );

                        if (itemKey) {
                            const item = this.newItems[itemKey];

                            // Create updated item with new ID
                            this.newItems[newId] = {
                                ...item,
                                id: newId
                            };

                            // If this is an item (not a group) and has a groupId, update it
                            if (item.type !== 'group' && item.groupId) {
                                this.newItems[newId].groupId = idMappings[item.groupId] || item.groupId;
                            }

                            delete this.newItems[itemKey];

                            // Update DOM
                            const row = document.querySelector(`tr[data-id="${oldId}"]`);
                            if (row) {
                                row.dataset.id = newId;
                                if (row.dataset.groupid === oldId) {
                                    row.dataset.groupid = newId;
                                }

                                // Update input names
                                row.querySelectorAll(`[name*="[${oldId}]"]`).forEach(input => {
                                    input.name = input.name.replace(`[${oldId}]`, `[${newId}]`);
                                });
                            }

                            console.log(this.newItems[newId]); 

                        }
                    };

                    // Update all entities with new IDs
                    Object.entries(idMappings).forEach(([oldId, newId]) => {
                        updateEntities(oldId, newId);
                    });

                    // Update UI state
                    this.lastSaveTimestamp = Date.now();
                    this.lastSaveText = this.formatTimeAgo(this.lastSaveTimestamp);

                    toastrs("success", "Estimation data has been saved.");
                    $('#save-button').html(`Saved last changed.`);

                    this.hasUnsavedChanges = false;
                    this.startTimeAgoUpdates();
                },
                error: (error) => {
                    console.error('Error saving data:', error);
                    toastrs("error", "Failed to save changes." + error.error);

                    this.hasUnsavedChanges = true;
                    this.lastSaveText = 'is failed';
                }
            });
        },

        getFomrData() {
            const form = this.$el.closest('form');
            if (!form) {
                console.warn('Form not found');
                return {};
            }
            const formData = new FormData(form);
            return Object.fromEntries(formData);
        },
    }));
});