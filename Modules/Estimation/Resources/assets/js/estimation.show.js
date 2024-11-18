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

    init() {
        this.initializeData();
        this.initializeLastNumbers();
        this.initializeSortable();

        this.$watch('items', () => this.calculateTotals(), { deep: true });
        this.$watch('searchQuery', () => this.filterTable());
        this.$watch('selectAll', (value) => this.checkboxAll(value));
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

    calculateItemTotal(itemId) {
        const item = this.items[itemId];
        if (!item || item.optional) return 0;
        return item.quantity * item.price;
    },

    calculateTotals() {
        // Reset totals
        this.totals = {};

        // Initialize group totals
        document.querySelectorAll('tr.group_row').forEach(groupRow => {
            const groupId = groupRow.dataset.groupid;
            if (!groupId) return;

            // Calculate total for this group
            const groupTotal = this.calculateGroupTotal(groupId);
            this.totals[groupId] = groupTotal;

            // Update the display
            const totalCell = groupRow.querySelector('.text-right');
            if (totalCell) {
                totalCell.textContent = this.formatCurrency(groupTotal);
            }

            // Update group data
            if (this.groups[groupId]) {
                this.groups[groupId].total = groupTotal;
            }
        });
    },

    calculateGroupTotal(groupId) {
        let total = 0;
        document.querySelectorAll(`tr.item_row[data-groupid="${groupId}"]`).forEach(itemRow => {
            if (!itemRow.querySelector('.item-optional')?.checked) {
                const quantity = this.parseNumber(itemRow.querySelector('.item-quantity')?.value || '0');
                const price = this.parseNumber(itemRow.querySelector('.item-price')?.value || '0');
                total += quantity * price;
            }
        });

        return total;
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
        const itemId = row.dataset.itemid;

        if (this.items[itemId]) {
            this.items[itemId][type] = parsedValue;
            this.calculateTotals();
        }
    },

    handleOptionalChange(event, itemId) {
        console.log(this.items);

        if (this.items[itemId]) {
            this.items[itemId].optional = event.target.checked;
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
                        const newGroupId = newGroupRow.dataset.id || newGroupRow.dataset.groupid;
                        const itemId = movedRow.dataset.id || movedRow.dataset.itemid || movedRow.dataset.commentid;
                        const oldGroupId = this.items[itemId]?.groupId;

                        // Only update if group actually changed
                        if (oldGroupId && oldGroupId !== newGroupId) {
                            // Decrease old group count
                            if (this.groups[oldGroupId]) {
                                this.groups[oldGroupId].itemCount = Math.max(0, (this.groups[oldGroupId].itemCount || 1) - 1);
                            }

                            // Increase new group count
                            if (this.groups[newGroupId]) {
                                this.groups[newGroupId].itemCount = (this.groups[newGroupId].itemCount || 0) + 1;
                            }

                            // Update item's groupId in all relevant places
                            if (this.items[itemId]) {
                                this.items[itemId].groupId = newGroupId;
                            }
                            if (this.newItems[itemId]) {
                                this.newItems[itemId].groupId = newGroupId;
                            }

                            // Update the DOM element's groupId
                            movedRow.dataset.groupid = newGroupId;
                        }
                    }
                }

                // Update positions and recalculate totals
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

    addItem(type) {
        const timestamp = Date.now();

        this.newItems[timestamp] = {
            id: timestamp,
            type: type,
            quantity: 0,
            optional: false,
            expanded: false
        };

        this.items[timestamp] = {
            id: timestamp,
            type: type,
            name: type,
            quantity: 0,
            optional: false,
            expanded: false,
        };

        this.$nextTick(() => {
            this.initializeSortable();
            this.updatePOSNumbers();
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

    checkboxAll(value) {
        document.querySelectorAll('.item_selection').forEach(checkbox => {
            checkbox.checked = value;
        });
    },
})); 