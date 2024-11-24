Alpine.data('estimationShow', () => ({
    items: {},
    newItems: {},
    groups: {},
    totals: {},
    expandedRows: {},
    lastGroupNumber: 0,
    lastItemNumbers: {},
    searchQuery: '',
    selectAll: false,
    isFullScreen: false,
    autoSaveEnabled: true,
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
        this.initializeData();
        this.initializeSortable();
        this.initializeLastNumbers();
        this.initializeContextMenu();
        this.initializeColumnVisibility();
        this.initializeCardCalculations();

        this.$watch('items', (value) => {

            this.calculateTotals();
            const cardQuoteIds = [...new Set(Array.from(document.querySelectorAll('[data-cardquoteid]')).map(el => el.dataset.cardquoteid))];
            cardQuoteIds.forEach(cardQuoteId => this.calculateCardTotals(cardQuoteId));
        }, { deep: true });

        this.$watch('autoSaveEnabled', (newValue) => {
            if (newValue) {
                this.saveTableData();
            }
        });

        this.$watch('searchQuery', () => this.filterTable());
        this.$watch('selectAll', (value) => this.checkboxAll(value));

        document.addEventListener('click', (e) => {
            if (!e.target.closest('.context-menu')) {
                this.showContextMenu = false;
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
            if (e.target.classList.contains('item-quantity') || e.target.classList.contains('item-price')) {
                this.handleInputBlur(e);
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

    initializeData() {

        this.items = {};
        this.groups = {};
        this.lastGroupNumber = 0;
        this.lastItemNumbers = {};


        document.querySelectorAll('tr.group_row').forEach((groupRow) => {
            const groupId = groupRow.dataset.groupid;
            const groupPos = groupRow.querySelector('.grouppos').textContent.trim();
            const groupNumber = parseInt(groupPos);

            this.groups[groupId] = {
                id: groupId,
                pos: groupPos,
                name: groupRow.querySelector('.grouptitle-input').value,
                total: this.parseNumber(groupRow.querySelector('.text-right').textContent),
                itemCount: 0
            };

            this.lastGroupNumber = Math.max(this.lastGroupNumber, groupNumber);
        });


        document.querySelectorAll('tr.item_row, tr.item_comment').forEach((row) => {
            const isComment = row.classList.contains('item_comment');
            const itemId = isComment ? row.dataset.commentid : row.dataset.itemid;
            const groupId = row.dataset.groupid;

            if (isComment) {
                this.items[itemId] = {
                    id: itemId,
                    type: 'comment',
                    groupId: groupId,
                    pos: row.querySelector('.pos-inner').textContent.trim(),
                    content: row.querySelector('.column_name input').value,
                    expanded: false
                };
            } else {
                this.items[itemId] = {
                    id: itemId,
                    type: 'item',
                    groupId: groupId,
                    pos: row.querySelector('.pos-inner').textContent.trim(),
                    name: row.querySelector('.item-name').value,
                    quantity: this.parseNumber(row.querySelector('.item-quantity').value),
                    price: this.parseNumber(row.querySelector('.item-price').value),
                    optional: row.querySelector('.item-optional').checked,
                    unit: row.querySelector('.item-unit').value
                };
            }

            console.log(this.items);


            this.groups[groupId].itemCount++;
        });


        this.updatePOSNumbers();
        this.calculateTotals();
    },

    calculateItemTotal(itemId, priceColumnIndex = 0) {
        const item = this.items[itemId] || this.newItems[itemId];
        if (!item || item.optional) return 0;

        const row = document.querySelector(`tr[data-itemid="${itemId}"]`);
        if (!row) return 0;

        const quantity = this.parseNumber(row.querySelector('.item-quantity')?.value || '0');
        const priceInputs = row.querySelectorAll('.item-price');
        const priceInput = priceInputs[priceColumnIndex];

        if (!priceInput) return 0;

        if (!priceInput.dataset.originalPrice) {
            priceInput.dataset.originalPrice = this.parseNumber(priceInput.value);
        }

        const cardQuoteId = priceInput.closest('[data-cardquoteid]')?.dataset.cardquoteid;
        let price = this.parseNumber(priceInput.dataset.originalPrice || '0');

        if (cardQuoteId) {
            const markupInput = document.querySelector(`input[name="markup"][data-cardquoteid="${cardQuoteId}"]`);
            const markup = this.parseNumber(markupInput?.value || '0');
            price += markup;
        }

        const total = quantity * price;

        const totalCell = row.querySelectorAll('.column_total_price')[priceColumnIndex];
        if (totalCell) {
            totalCell.textContent = this.formatCurrency(total);
            this.setNegativeStyle(totalCell, total);
        }

        return total;
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
                if (!currentRow.querySelector('.item-optional')?.checked) {
                    const quantity = this.parseNumber(currentRow.querySelector('.item-quantity')?.value || '0');
                    const priceInputs = currentRow.querySelectorAll('.item-price');

                    priceInputs.forEach((priceInput, index) => {
                        if (!totals[index]) totals[index] = 0;
                        const price = this.parseNumber(priceInput.value || '0');
                        totals[index] += quantity * price;

                        const totalCell = currentRow.querySelectorAll('.column_total_price')[index];
                        if (totalCell) {
                            const total = quantity * price;
                            totalCell.textContent = this.formatCurrency(total);
                            this.setNegativeStyle(totalCell, total);
                        }
                    });
                } else {
                    currentRow.querySelectorAll('.column_total_price').forEach(cell => {
                        cell.textContent = '-';
                        cell.style.backgroundColor = '';
                        cell.style.color = '';
                    });
                }
            }
            currentRow = currentRow.nextElementSibling;
        }

        const totalCells = groupRow.querySelectorAll('.text-right.grouptotal');
        totalCells.forEach((cell, index) => {
            cell.textContent = this.formatCurrency(totals[index] || 0);
            this.setNegativeStyle(cell, totals[index] || 0);

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


        const markupInput = document.querySelector(`input[name="markup"][value]:not([value=""])[data-cardquoteid="${cardQuoteId}"]`);
        const markup = this.parseNumber(markupInput?.value || '0');


        const discountInput = document.querySelector(`input[name="discount"][data-cardquoteid="${cardQuoteId}"]`);
        const cashDiscount = this.parseNumber(discountInput?.value || '0');


        const vatSelect = document.querySelector(`select[name="tax[]"][data-cardquoteid="${cardQuoteId}"]`);
        const vatRate = vatSelect ? this.parseNumber(vatSelect.value) / 100 : 0;


        const netAmount = subtotal + markup;
        const discountAmount = (netAmount * cashDiscount) / 100;
        const netWithDiscount = netAmount - discountAmount;
        const vatAmount = netWithDiscount * vatRate;
        const grossWithDiscount = netWithDiscount + vatAmount;


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
    },

    initializeCardCalculations() {
        const cardQuoteIds = [...new Set(Array.from(document.querySelectorAll('[data-cardquoteid]')).map(el => el.dataset.cardquoteid))];

        cardQuoteIds.forEach(cardQuoteId => {
            this.calculateCardTotals(cardQuoteId);

            const markupInput = document.querySelector(`input[name="markup"][data-cardquoteid="${cardQuoteId}"]`);
            if (markupInput) {
                const value = this.parseNumber(markupInput.value);
                markupInput.value = this.formatDecimal(value);
                this.setNegativeStyle(markupInput, value);
            }
        });

        document.addEventListener('change', (e) => {
            const target = e.target;
            const cardQuoteId = target.closest('[data-cardquoteid]')?.dataset.cardquoteid;

            if (!cardQuoteId) return;

            if (target.matches('input[name="markup"]')) {
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
            }

            if (
                target.matches('input[name="markup"]') ||
                target.matches('input[name="discount"]') ||
                target.matches('select[name="tax[]"]') ||
                target.matches('.item-price') ||
                target.matches('.item-quantity') ||
                target.matches('.item-optional')
            ) {
                this.calculateCardTotals(cardQuoteId);
            }
        });
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

    parseNumber(value) {
        if (typeof value === 'number') return value;
        return parseFloat(value.replace(/[^\d,-]/g, '').replace(',', '.')) || 0;
    },

    handleInputBlur(event, type) {
        if (event.type === 'keydown') {
            if (event.key !== 'Enter') return;
            event.target.blur();
        }

        const value = event.target.value;
        const row = event.target.closest('tr');
        const itemId = row.dataset.itemid;
        const groupId = row.dataset.groupid;

        switch (type) {
            case 'item':
                this.items[itemId].name = value;
                break;
            case 'group':
                this.groups[groupId].name = value;
                break;
            case 'comment':
                this.items[itemId].content = value;
                break;
            case 'unit':
                this.items[itemId].unit = value;
                break;
            case 'quantity':
                if (this.items[itemId]) {
                    this.items[itemId].quantity = this.parseNumber(value);
                }
                if (this.newItems[itemId]) {
                    this.newItems[itemId].quantity = this.parseNumber(value);
                }

                this.formatDecimalValue(event.target);
                break;
            case 'price':
                this.items[itemId].price = this.parseNumber(value);
                this.newItems[itemId].price = this.parseNumber(value);
                this.formatCurrencyValue(event.target);
                break;
            default:
                break;
        }

        if (groupId) {
            this.calculateGroupTotal(groupId);
        }
        this.calculateTotals();
    },

    formatDecimalValue(target) {
        target.value = this.formatDecimal(this.parseNumber(target.value));
    },

    formatCurrencyValue(target) {
        target.value = this.formatCurrency(this.parseNumber(target.value));
    },

    handleOptionalChange(event, itemId) {
        if (this.items[itemId]) {
            this.items[itemId].optional = event.target.checked;
            if (this.newItems[itemId]) {
                this.newItems[itemId].optional = event.target.checked;
            }
            this.calculateTotals();
        }
    },

    filterTable() {
        const searchTerm = this.searchQuery.toLowerCase();
        Object.entries(this.items).forEach(([itemId, item]) => {
            const row = document.querySelector(`tr[data-itemid="${itemId}"]`);
            if (row) {
                row.style.display = item.name.toLowerCase().includes(searchTerm) ||
                    item.pos.toLowerCase().includes(searchTerm) ? '' : 'none';
            }
        });

        Object.entries(this.groups).forEach(([groupId, group]) => {
            const row = document.querySelector(`tr[data-groupid="${groupId}"]`);
            if (row) {
                row.style.display = group.name.toLowerCase().includes(searchTerm) ||
                    group.pos.toLowerCase().includes(searchTerm) ? '' : 'none';
            }
        });

        document.querySelectorAll('tr.item_comment').forEach(row => {
            const commentInput = row.querySelector('.item-description');
            const posElement = row.querySelector('.pos-inner');

            if (commentInput && posElement) {
                const commentText = commentInput.value.toLowerCase();
                const posText = posElement.textContent.toLowerCase();

                row.style.display = commentText.includes(searchTerm) ||
                    posText.includes(searchTerm) ? '' : 'none';
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
                ui.item.addClass("selected");
            },
            stop: (event, ui) => {
                const movedRow = ui.item[0];


                if (movedRow.classList.contains('item_row') || movedRow.classList.contains('item_comment')) {

                    let currentRow = movedRow.previousElementSibling;
                    let newGroupRow = null;

                    while (currentRow && !newGroupRow) {
                        if (currentRow.classList.contains('group_row')) {
                            newGroupRow = currentRow;
                        }
                        currentRow = currentRow.previousElementSibling;
                    }

                    if (newGroupRow) {
                        const newGroupId = newGroupRow.dataset.groupid;
                        const itemId = movedRow.dataset.itemid || movedRow.dataset.id;
                        const oldGroupId = movedRow.dataset.groupid;


                        movedRow.dataset.groupid = newGroupId;


                        if (this.items[itemId]) {
                            this.items[itemId].groupId = newGroupId;
                        }
                        if (this.newItems[itemId]) {
                            this.newItems[itemId].groupId = newGroupId;
                        }
                    }
                }


                this.updatePOSNumbers();
                this.calculateTotals();
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

    addItem(type, targetRowId = null) {
        const timestamp = Date.now();
        let currentGroupId;

        // Check if table is completely empty
        const hasAnyGroups = document.querySelectorAll('tr.group_row').length > 0;

        // If table is empty, always create a group first with an item
        if (!hasAnyGroups) {
            // Create group
            const groupTimestamp = Date.now();
            currentGroupId = `group_${groupTimestamp}`;

            const newGroup = {
                id: groupTimestamp,
                type: 'group',
                name: 'New Group',
                total: 0,
                expanded: false,
                pos: ''
            };

            // Add group to collections
            this.items[groupTimestamp] = newGroup;
            this.newItems[groupTimestamp] = newGroup;

            this.groups[currentGroupId] = {
                id: currentGroupId,
                pos: '',
                name: 'New Group',
                total: 0,
                itemCount: 0
            };

            // Create child item
            const itemTimestamp = Date.now() + 1;
            const newItem = {
                id: itemTimestamp,
                type: 'item',
                groupId: currentGroupId,
                name: 'New Item',
                quantity: 0,
                price: 0,
                unit: '',
                optional: false,
                expanded: false,
                pos: ''
            };

            // Add item to collections
            this.items[itemTimestamp] = newItem;
            this.newItems[itemTimestamp] = newItem;

            this.$nextTick(() => {
                this.initializeSortable();
                this.updatePOSNumbers();
                this.calculateTotals();
            });

            return;
        }

        // Normal flow for non-empty table
        if (type === 'group') {
            currentGroupId = `group_${timestamp}`;
            const newItem = {
                id: timestamp,
                type: 'group',
                name: `Group name`,
                total: 0,
                expanded: false,
                pos: ''
            };

            this.items[timestamp] = newItem;
            this.newItems[timestamp] = newItem;

            this.groups[currentGroupId] = {
                id: currentGroupId,
                pos: '',
                name: 'Group name',
                total: 0,
                itemCount: 0
            };

            this.$nextTick(() => {
                this.initializeSortable();
                this.updatePOSNumbers();
                this.calculateTotals();
            });

            return;
        }

        // For items and comments in non-empty table
        if (targetRowId) {
            const targetRow = document.querySelector(`tr[data-id="${targetRowId}"], 
                                               tr[data-itemid="${targetRowId}"], 
                                               tr[data-commentid="${targetRowId}"], 
                                               tr[data-groupid="${targetRowId}"]`);
            if (targetRow) {
                currentGroupId = targetRow.classList.contains('group_row') ?
                    targetRow.dataset.groupid :
                    targetRow.dataset.groupid;
            }
        } else {
            const GroupRow = document.querySelectorAll('tr.group_row');
            const lastGroupRow = GroupRow[GroupRow.length - 1];
            currentGroupId = lastGroupRow ? lastGroupRow.dataset.groupid : null;
        }

        const newItem = {
            id: timestamp,
            type: type,
            groupId: currentGroupId,
            name: type + ` name`,
            quantity: 0,
            price: 0,
            unit: '',
            optional: false,
            expanded: false,
            pos: ''
        };

        this.items[timestamp] = newItem;
        this.newItems[timestamp] = newItem;

        this.$nextTick(() => {
            if (targetRowId) {
                const targetRow = document.querySelector(`tr[data-id="${targetRowId}"], 
                                                   tr[data-itemid="${targetRowId}"], 
                                                   tr[data-commentid="${targetRowId}"], 
                                                   tr[data-groupid="${targetRowId}"]`);
                const newRow = document.querySelector(`tr[data-id="${timestamp}"], 
                                                tr[data-itemid="${timestamp}"]`);
                if (newRow && targetRow.nextSibling) {
                    targetRow.parentNode.insertBefore(newRow, targetRow.nextSibling);
                }
            }

            this.initializeSortable();
            this.updatePOSNumbers();
            this.calculateTotals();
        });

        if (targetRowId) {
            this.contextMenu.show = false;
        }
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
                const itemIds = [];
                const groupIds = [];

                selectedCheckboxes.forEach(checkbox => {
                    const row = checkbox.closest('tr');
                    if (row.classList.contains('group_row')) {
                        groupIds.push(row.dataset.groupid);
                        delete this.groups[row.dataset.groupid];
                    } else {
                        itemIds.push(row.dataset.itemid);
                        delete this.items[row.dataset.itemid];
                    }
                    row.remove();
                });

                document.querySelector('.SelectAllCheckbox').checked = false;

                fetch(route('estimations.remove_items.estimate'), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        estimation_id: window.estimation_id,
                        item_ids: itemIds,
                        group_ids: groupIds
                    })
                });

                this.calculateTotals();
                this.updatePOSNumbers();
            }
        });
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
            else if (row.classList.contains('item_row') || row.classList.contains('item_comment')) {
                itemCountInGroup++;
                const itemPos = `${currentGroupPos.toString().padStart(2, '0')}.${itemCountInGroup.toString().padStart(2, '0')}`;

                row.querySelector('.pos-inner').textContent = itemPos;

                const itemId = row.dataset.itemid || row.dataset.commentid;
                if (this.items[itemId]) {
                    this.items[itemId].pos = itemPos;
                }
            }
        });
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

                    // If using an API, make the delete request
                    // fetch(route('estimations.delete_column'), {
                    //     method: 'POST',
                    //     headers: {
                    //         'Content-Type': 'application/json',
                    //         'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    //     },
                    //     body: JSON.stringify({
                    //         estimation_id: window.estimation_id,
                    //         quote_id: quoteId
                    //     })
                    // }).catch(error => {
                    //     console.error('Error deleting column:', error);
                    // });

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
            const newGroupId = `group_${timestamp}`;

            const newItem = {
                id: timestamp,
                type: 'group',
                name: `${groupName} - copy`,
                total: 0,
                expanded: false
            };


            this.items[timestamp] = newItem;
            this.newItems[timestamp] = newItem;


            this.groups[newGroupId] = {
                id: newGroupId,
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
            this.newItems[timestamp] = newItem;
        }
        else {

            const newItem = {
                id: timestamp,
                type: originalRow.classList.contains('item_comment') ? 'comment' : 'item',
                groupId: groupId,
                name: originalRow.querySelector('.item-name')?.value + ' - copy',
                quantity: this.parseNumber(originalRow.querySelector('.item-quantity')?.value || '0'),
                unit: originalRow.querySelector('.item-unit')?.value || '',
                optional: originalRow.querySelector('.item-optional')?.checked || false,
                price: this.parseNumber(originalRow.querySelector('.item-price')?.value || '0'),
                expanded: false
            };


            this.items[timestamp] = newItem;
            this.newItems[timestamp] = newItem;


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
                const row = document.querySelector(`tr[data-id="${rowId}"], tr[data-itemid="${rowId}"], tr[data-groupid="${rowId}"]`);
                if (!row) return;

                if (row.classList.contains('group_row')) {

                    const groupId = row.dataset.groupid;
                    document.querySelectorAll(`tr[data-groupid="${groupId}"]`).forEach(itemRow => {
                        const itemId = itemRow.dataset.itemid;
                        delete this.items[itemId];
                        delete this.newItems[itemId];
                        itemRow.remove();
                    });
                    delete this.groups[groupId];
                } else {

                    const itemId = row.dataset.itemid || row.dataset.id;
                    delete this.items[itemId];
                    delete this.newItems[itemId];
                }

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

                console.log(elements);

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

    saveTableData() {
        // Prepare the data to be sent to the server
        const data = {
            form: this.getFomrData(),
            item: this.serializeEstimationData(),
            group: this.groups,
        };

        console.log(data);

        $.ajax({
            url: route('estimation.update', 11),
            method: 'PUT',
            data: data,
            beforeSend: function () {
                //TODO:
            },
            success: function (data) {
                console.log(data);
            }
        });


        // Send the data to the server using an AJAX request
        // fetch('/estimations/save', {
        //     method: 'POST',
        //     headers: {
        //         'Content-Type': 'application/json',
        //         'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        //     },
        //     body: JSON.stringify(data),
        // })
        //     .then((response) => response.json())
        //     .then((data) => {
        //         // Handle the response from the server
        //         console.log('Estimation saved:', data);
        //     })
        //     .catch((error) => {
        //         // Handle any errors
        //         console.error('Error saving estimation:', error);
        //     });
    },

    getFomrData() {
        const formData = new FormData(this.$el.closest('form'));
        return Object.fromEntries(formData);
    },
    serializeEstimationData() {
        // return JSON.stringify(this.items);
        return Object.values(this.items);
    },
})); 