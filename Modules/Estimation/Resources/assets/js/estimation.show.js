const { vModelCheckbox } = require("vue");

Alpine.data('estimationShow', () => ({
    items: [],
    expandedRows: {},
    lastGroupNumber: 0,
    lastItemNumbers: {},
    searchQuery: '',
    selectAll: false,

    init() {
        this.items = [];
        this.initializeSortable();
        this.initializeLastNumbers();
        this.setupCalculations();

        this.$watch('searchQuery', () => this.filterTable());
        this.$watch('selectAll', (value) => this.checkboxAll(value));
    },

    /**
     * Watch for changes in quantity, price, and optional checkbox
     */
    setupCalculations() {
        this.$nextTick(() => {
            document.querySelectorAll('.item_row').forEach(row => {
                const qtyInput = row.querySelector('.row_qty');
                const priceInput = row.querySelector('.row_price');
                const optionalCheck = row.querySelector('.select_optional');

                qtyInput?.addEventListener('blur', (e) => {
                    const rawValue = e.target.value.replace(/[^\d,-]/g, '');
                    this.calculateRowTotal(row);
                    qtyInput.value = this.formatDecimal(this.parseNumber(rawValue));
                });

                qtyInput?.addEventListener('keyup', (e) => {
                    if (e.key === 'Enter') {
                        e.target.blur();
                    }
                });

                priceInput?.addEventListener('blur', (e) => {
                    const rawValue = e.target.value.replace(/[^\d,-]/g, '');
                    this.calculateRowTotal(row);
                    priceInput.value = this.formatDecimal(this.parseNumber(rawValue));
                });

                priceInput?.addEventListener('keyup', (e) => {
                    if (e.key === 'Enter') {
                        e.target.blur();
                    }
                });

                optionalCheck?.addEventListener('change', () => this.calculateRowTotal(row));
            });
        });
    },

    calculateRowTotal(row) {
        const quantity = this.parseNumber(row.querySelector('.row_qty').value);
        const price = this.parseNumber(row.querySelector('.row_price').value);
        const isOptional = row.querySelector('.select_optional').checked;
        const totalCell = row.querySelector('.column_total_price');

        const total = isOptional ? 0 : quantity * price;
        totalCell.textContent = this.formatCurrency(total);

        this.updateGroupTotals();
    },

    updateGroupTotals() {
        document.querySelectorAll('.group_row').forEach(groupRow => {
            const groupPos = groupRow.dataset.group_pos;
            let groupTotal = 0;

            // Sum all non-optional items in this group
            document.querySelectorAll(`.item_row[data-group_pos="${groupPos}"]`).forEach(itemRow => {
                if (!itemRow.querySelector('.select_optional').checked) {
                    const total = this.parseNumber(itemRow.querySelector('.column_total_price').textContent);
                    groupTotal += total;
                }
            });

            // Update group total
            const groupTotalCell = groupRow.querySelector('.grouptotal');
            groupTotalCell.textContent = this.formatCurrency(groupTotal);
        });
    },

    parseNumber(value) {
        return parseFloat(value.replace(/[^\d,-]/g, '').replace(',', '.')) || 0;
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

    formatNumber(value) {
        return new Intl.NumberFormat('de-DE', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(value);
    },

    addItem(type) {
        let pos;
        if (type === 'group') {
            this.lastGroupNumber++;
            pos = `${this.lastGroupNumber.toString().padStart(2, '0')}`;
            this.lastItemNumbers[this.lastGroupNumber] = 0;
        } else {
            // For items and comments
            const currentGroup = this.lastGroupNumber;
            this.lastItemNumbers[currentGroup] = (this.lastItemNumbers[currentGroup] || 0) + 1;
            pos = `${currentGroup.toString().padStart(2, '0')}.${this.lastItemNumbers[currentGroup].toString().padStart(2, '0')}`;
        }

        const newItem = {
            id: Date.now(),
            type: type,
            pos: pos
        };

        this.items.push(newItem);
    },

    removeItem() {

        const selectedCheckboxes = document.querySelectorAll('input[type="checkbox"].item_selection:checked');

        if (selectedCheckboxes.length == 0) {
            toastrs("Error", "Please select checkbox to contiune delete");
            return;
        }

        Swal.fire({
            title: 'Confirmation Delete',
            text: 'Really! You want to remove them? You can\'t undo',
            showCancelButton: true,
            confirmButtonText: `Yes, Delete it`,
            cancelButtonText: "No, cancel",
        }).then((result) => {
            if (result.isConfirmed) {
                selectedCheckboxes.forEach(element => {
                    const parentElement = element.closest('tr');
                    const parentID = parentElement.dataset.id ?? null;

                    parentElement.remove();
                    $('.SelectAllCheckbox').prop('checked', false)

                    $.ajax({
                        url: route('estimations.remove_items.estimate'),
                        type: "POST",
                        contentType: "application/json",
                        data: JSON.stringify({
                            estimation_id: estimation_id,
                            item_ids: item_ids,
                            group_ids: group_ids
                        }),
                        success: function (response) {
                            console.log(response);
                        }
                    });

                });
            }
        });
    },

    getPosNumber(index) {
        const groupCounter = Math.floor(index / 100) + 1;
        const itemCounter = (index % 100) + 1;
        return `${groupCounter.toString().padStart(2, '0')}.${itemCounter.toString().padStart(2, '0')}`;
    },

    toggleDescription(index, event) {
        event.stopPropagation();
        this.expandedRows[index] = !this.expandedRows[index];
    },

    isExpanded(index) {
        return this.expandedRows[index] || false;
    },

    initializeSortable() {
        const tbody = document.querySelector("#estimation-edit-table tbody");
        if (tbody && typeof Sortable !== 'undefined') {
            new Sortable(tbody, {
                handle: '.fa-bars',
                animation: 150,
                onEnd: () => {
                    this.updatePOSNumbers();
                }
            });
        }
    },

    initializeLastNumbers() {
        // Get all existing POS numbers from backend-loaded items
        const existingPosElements = document.querySelectorAll('.pos-inner');
        existingPosElements.forEach(element => {
            const pos = element.textContent.trim();
            if (pos) {
                const [groupNum, itemNum] = pos.split('.');
                const groupNumber = parseInt(groupNum);
                const itemNumber = parseInt(itemNum);

                // Update last group number
                this.lastGroupNumber = Math.max(this.lastGroupNumber, groupNumber);

                // Update last item number for this group
                if (!this.lastItemNumbers[groupNumber] || itemNumber > this.lastItemNumbers[groupNumber]) {
                    this.lastItemNumbers[groupNumber] = itemNumber;
                }
            }
        });
    },

    updatePOSNumbers() {
        this.items.forEach((item, index) => {
            const posNumber = this.getPosNumber(index);
            const row = document.querySelector(`tr[data-id="${index}"] .pos-inner`);
            if (row) row.textContent = posNumber;
        });
    },

    filterTable() {
        const filter = this.searchQuery.toUpperCase();
        const table = document.getElementById('estimation-edit-table');
        const rows = table.getElementsByTagName('tr');

        for (let i = 0; i < rows.length; i++) {
            const cells = rows[i].getElementsByTagName('td');
            let shouldDisplay = false;

            // Process all cells in the row
            for (let j = 0; j < cells.length; j++) {
                const cellContent = cells[j].innerHTML;
                if (cellContent.toUpperCase().indexOf(filter) > -1) {
                    shouldDisplay = true;
                    break;
                }
            }

            rows[i].style.display = shouldDisplay ? '' : 'none';
        }
    },

    checkboxAll(value) {
        document.querySelectorAll('.item_selection').forEach(checkbox => {
            checkbox.checked = value;
        });
        document.querySelectorAll('.group_checkbox').forEach(checkbox => {
            checkbox.checked = value;
        });
    }
}))



$("#estimation-edit-table").sortable({
    items: 'tr.item_row',
    cursor: 'pointer',
    axis: 'y',
    dropOnEmpty: false,
    start: function (e, ui) {
        ui.item.addClass("selected");
    },
    stop: function (e, ui) {
        var item_id = $(ui.item).data('id');
        var description_row = $(
            '.description_row[data-id="' +
            item_id + '"]');
        $('.description_row[data-id="' + item_id +
            '"]').remove();
        description_row.insertAfter($(ui.item));

        var group_pos = $(ui.item).prevAll(
            "tr.group_row:first").data(
                'group_pos');
        var group_id = $(ui.item).prevAll(
            "tr.group_row:first").data(
                'group_id');
        $(ui.item).attr('data-group_pos',
            group_pos);
        $(ui.item).attr('data-group_id', group_id);

        ui.item.removeClass("selected");
    }
});