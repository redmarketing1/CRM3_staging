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
        quote_th: true  // Assuming 1930 is your quote ID
    },

    init() {
        this.initializeData();
        this.initializeSortable();
        this.initializeLastNumbers();
        this.initializeContextMenu();
        this.initializeColumnVisibility();
        this.initializeCardCalculations();

        this.$watch('items', () => {
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
        // Reset all data structures
        this.items = {};
        this.groups = {};
        this.lastGroupNumber = 0;
        this.lastItemNumbers = {};

        // Process groups first without modifying POS
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

        // Process items and comments without modifying POS
        document.querySelectorAll('tr.item_row, tr.item_comment').forEach((row) => {
            const isComment = row.classList.contains('item_comment');
            const itemId = isComment ? row.dataset.commentid : row.dataset.itemid;
            const groupId = row.closest('tbody').querySelector('tr.group_row').dataset.groupid;

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

            this.groups[groupId].itemCount++;
        });

        // Now update all POS numbers once
        this.updatePOSNumbers();

        // Calculate totals
        this.calculateTotals();
    },

    calculateItemTotal(itemId, priceColumnIndex = 0) {
        const item = this.items[itemId] || this.newItems[itemId];
        if (!item || item.optional) return 0;

        const row = document.querySelector(`tr[data-itemid="${itemId}"]`);
        if (!row) return 0;

        const quantity = this.parseNumber(row.querySelector('.item-quantity')?.value || '0');
        const priceInputs = row.querySelectorAll('.item-price');
        const price = this.parseNumber(priceInputs[priceColumnIndex]?.value || '0');

        return quantity * price;
    },

    calculateTotals() {
        // Reset totals
        this.totals = {};

        // Process groups sequentially
        document.querySelectorAll('tr.group_row').forEach(row => {
            const groupId = row.dataset.groupid;
            if (!groupId) return;

            // Calculate totals for this group
            this.calculateGroupTotal(groupId);

            // Update group data
            if (this.groups[groupId]) {
                // Store the first total value in the group data
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
                            totalCell.textContent = this.formatCurrency(quantity * price);
                        }
                    });
                } else {
                    currentRow.querySelectorAll('.column_total_price').forEach(cell => {
                        cell.textContent = '-';
                    });
                }
            }
            currentRow = currentRow.nextElementSibling;
        }

        // Update group totals and trigger card totals update
        const totalCells = groupRow.querySelectorAll('.text-right.grouptotal');
        totalCells.forEach((cell, index) => {
            cell.textContent = this.formatCurrency(totals[index] || 0);

            // Get card quote ID and trigger update
            const cardQuoteId = cell.dataset.cardquoteid;
            if (cardQuoteId) {
                this.calculateCardTotals(cardQuoteId);
            }
        });

        return totals[0] || 0;
    },

    calculateCardTotals(cardQuoteId) {
        let subtotal = 0;

        // Sum all group totals for this card
        const groupTotalCells = document.querySelectorAll(`td[data-cardquoteid="${cardQuoteId}"].grouptotal`);
        groupTotalCells.forEach(cell => {
            subtotal += this.parseNumber(cell.textContent);
        });

        // Get markup value
        const markupInput = document.querySelector(`input[name="markup"][value]:not([value=""])[data-cardquoteid="${cardQuoteId}"]`);
        const markup = this.parseNumber(markupInput?.value || '0');

        // Get cash discount value
        const discountInput = document.querySelector(`input[name="discount"][data-cardquoteid="${cardQuoteId}"]`);
        const cashDiscount = this.parseNumber(discountInput?.value || '0');

        // Get VAT rate
        const vatSelect = document.querySelector(`select[name="tax[]"][data-cardquoteid="${cardQuoteId}"]`);
        const vatRate = vatSelect ? this.parseNumber(vatSelect.value) / 100 : 0;

        // Calculate totals
        const netAmount = subtotal + markup;
        const discountAmount = (netAmount * cashDiscount) / 100;
        const netWithDiscount = netAmount - discountAmount;
        const vatAmount = netWithDiscount * vatRate;
        const grossWithDiscount = netWithDiscount + vatAmount;

        // Update UI totals
        this.updateCardTotalUI(cardQuoteId, {
            netAmount,
            netWithDiscount,
            grossWithDiscount,
            subtotal
        });
    },

    updateCardTotalUI(cardQuoteId, totals) {
        // Update Net incl. Discount
        const netDiscountElement = document.querySelector(`th[data-cardquoteid="${cardQuoteId}"].total-net-discount`);
        if (netDiscountElement) {
            netDiscountElement.textContent = this.formatCurrency(totals.netWithDiscount);
        }

        // Update Gross incl. Discount
        const grossDiscountElement = document.querySelector(`th[data-cardquoteid="${cardQuoteId}"].total-gross-discount`);
        if (grossDiscountElement) {
            grossDiscountElement.textContent = this.formatCurrency(totals.grossWithDiscount);
        }

        // Update Net
        const netElement = document.querySelector(`th[data-cardquoteid="${cardQuoteId}"].total-net`);
        if (netElement) {
            netElement.textContent = this.formatCurrency(totals.netAmount);
        }

        // Update Gross total
        const grossElement = document.querySelector(`th[data-cardquoteid="${cardQuoteId}"] .total-gross-total`);
        if (grossElement) {
            grossElement.textContent = this.formatCurrency(totals.grossWithDiscount);
        }
    },

    initializeCardCalculations() {
        // Get all card quote IDs
        const cardQuoteIds = [...new Set(Array.from(document.querySelectorAll('[data-cardquoteid]')).map(el => el.dataset.cardquoteid))];

        // Calculate initial totals
        cardQuoteIds.forEach(cardQuoteId => {
            this.calculateCardTotals(cardQuoteId);
        });

        // Add event listeners for changes
        document.addEventListener('change', (e) => {
            const target = e.target;
            const cardQuoteId = target.closest('[data-cardquoteid]')?.dataset.cardquoteid;

            if (!cardQuoteId) return;

            // Check if the change is relevant for recalculation
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
        const value = event.target.value;
        const parsedValue = this.parseNumber(value);
        event.target.value = type === 'quantity' ? this.formatDecimal(parsedValue) : this.formatCurrency(parsedValue);

        const row = event.target.closest('tr');
        const itemId = row.dataset.id || row.dataset.itemid;
        const groupId = row.dataset.groupid;

        if (type === 'quantity') {
            // Update quantity in data model
            if (this.items[itemId]) {
                this.items[itemId].quantity = parsedValue;
            }
            if (this.newItems[itemId]) {
                this.newItems[itemId].quantity = parsedValue;
            }
        }

        // Trigger recalculation of totals
        if (groupId) {
            this.calculateGroupTotal(groupId);
        }
        this.calculateTotals();
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

                // Only process if it's an item or comment row
                if (movedRow.classList.contains('item_row') || movedRow.classList.contains('item_comment')) {
                    // Find the closest previous group row
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

                        // Always update the group ID
                        movedRow.dataset.groupid = newGroupId;

                        // Update data structures
                        if (this.items[itemId]) {
                            this.items[itemId].groupId = newGroupId;
                        }
                        if (this.newItems[itemId]) {
                            this.newItems[itemId].groupId = newGroupId;
                        }
                    }
                }

                // Recalculate everything
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
                    // Ensure item numbers stay within 2 digits (max 99)
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

        if (targetRowId) {
            // Context menu: add after specific row
            const targetRow = document.querySelector(`tr[data-id="${targetRowId}"], 
                                                   tr[data-itemid="${targetRowId}"], 
                                                   tr[data-commentid="${targetRowId}"], 
                                                   tr[data-groupid="${targetRowId}"]`);
            if (!targetRow) return;

            // Get group ID from target row or nearest group
            currentGroupId = targetRow.classList.contains('group_row') ?
                targetRow.dataset.groupid :
                targetRow.dataset.groupid;
        } else {
            // Regular add: add to last group
            const GroupRow = document.querySelectorAll('tr.group_row');
            const lastGroupRow = GroupRow[GroupRow.length - 1];
            currentGroupId = lastGroupRow ? lastGroupRow.dataset.groupid : null;
        }

        if (!currentGroupId) return; // Need a group to add items

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

        // Add to both collections
        this.items[timestamp] = newItem;
        this.newItems[timestamp] = newItem;

        this.$nextTick(() => {
            if (targetRowId) {
                // Move new item after target row if context menu was used
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

    toggleDescription(index, event) {
        event.stopPropagation();
        this.expandedRows[index] = !this.expandedRows[index];
    },

    isExpanded(index) {
        return this.expandedRows[index] || false;
    },

    initializeContextMenu() {
        document.querySelector('#estimation-edit-table').addEventListener('contextmenu', (e) => {
            // Include comment rows in selection
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
            // Duplicate group
            const groupName = originalRow.querySelector('.grouptitle-input').value;
            const newGroupId = `group_${timestamp}`;

            const newItem = {
                id: timestamp,
                type: 'group',
                name: `${groupName} - copy`,
                total: 0,
                expanded: false
            };

            // Add to collections
            this.items[timestamp] = newItem;
            this.newItems[timestamp] = newItem;

            // Add to groups
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
            // Get values from original row
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

            // Add to collections
            this.items[timestamp] = newItem;
            this.newItems[timestamp] = newItem;

            // Update group item count
            if (this.groups[groupId]) {
                this.groups[groupId].itemCount++;
            }
        }

        this.$nextTick(() => {
            // Find the new row and move it after the original
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
                    // If it's a group, also remove all its items
                    const groupId = row.dataset.groupid;
                    document.querySelectorAll(`tr[data-groupid="${groupId}"]`).forEach(itemRow => {
                        const itemId = itemRow.dataset.itemid;
                        delete this.items[itemId];
                        delete this.newItems[itemId];
                        itemRow.remove();
                    });
                    delete this.groups[groupId];
                } else {
                    // Remove single item
                    const itemId = row.dataset.itemid || row.dataset.id;
                    delete this.items[itemId];
                    delete this.newItems[itemId];
                }

                row.remove();
                this.updatePOSNumbers();
                this.calculateTotals();

                // Handle UI updates
                document.querySelector('.SelectAllCheckbox').checked = false;
            }
        });

        this.contextMenu.show = false;
    },

    handleGroupSelection(event, groupId) {
        const checked = event.target.checked;
        const groupRow = event.target.closest('tr.group_row');

        if (!groupRow) return;

        // Get all items until next group row
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
        // Initialize column visibility from localStorage if available
        const savedVisibility = localStorage.getItem('columnVisibility');
        if (savedVisibility) {
            this.columnVisibility = JSON.parse(savedVisibility);
            this.applyColumnVisibility();
        }

        // Add event listeners for checkbox changes
        document.querySelectorAll('.column-toggle').forEach(checkbox => {
            checkbox.addEventListener('change', (e) => {

                const columnClass = e.target.dataset.column;
                const quoteId = e.target.dataset.quoteid;

                if (columnClass === 'quote_th' && quoteId) {
                    // Handle quote columns specifically
                    this.columnVisibility[columnClass] = e.target.checked;
                    this.applyColumnVisibility(quoteId);
                } else {
                    // Handle regular columns
                    this.columnVisibility[columnClass] = e.target.checked;
                    this.applyColumnVisibility();
                }

                this.saveColumnVisibility();
            });
        });
    },

    applyColumnVisibility(quoteId = null) {
        // Apply visibility to regular columns
        Object.entries(this.columnVisibility).forEach(([columnClass, isVisible]) => {

            if (columnClass === 'quote_th' && quoteId) {
                // Handle quote columns
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

    saveColumnVisibility() {
        localStorage.setItem('columnVisibility', JSON.stringify(this.columnVisibility));
    },

    toggleColumn(columnClass, quoteId = null) {
        this.columnVisibility[columnClass] = !this.columnVisibility[columnClass];
        this.applyColumnVisibility(quoteId);
        this.saveColumnVisibility();

        // Update checkbox state
        const selector = quoteId
            ? `.column-toggle[data-column="${columnClass}"][data-quote="${quoteId}"]`
            : `.column-toggle[data-column="${columnClass}"]`;
        const checkbox = document.querySelector(selector);
        if (checkbox) {
            checkbox.checked = this.columnVisibility[columnClass];
        }
    }
})); 