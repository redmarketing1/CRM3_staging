Alpine.data('estimationShow', () => ({
    newItem: {},
    items: [],
    isLoading: false,

    init() {
        this.setupEventListeners();
        this.updatePOSNumbers();
    },

    setupEventListeners() {
        this.$watch('items', () => {
            this.updatePOSNumbers();
        });
    },

    updatePOSNumbers() {
        const rows = document.querySelectorAll('.item_row');
        let currentGroup = '';
        let groupCounter = 1;
        let itemCounter = 1;

        rows.forEach((row) => {
            const groupId = row.dataset.group_id;

            // Reset item counter when group changes
            if (groupId !== currentGroup) {
                currentGroup = groupId;
                groupCounter++;
                itemCounter = 1;
            }

            const posNumber = `${groupCounter.toString().padStart(2, '0')}.${itemCounter.toString().padStart(2, '0')}`;

            row.querySelector('.pos-inner').textContent = posNumber;

            itemCounter++;
        });
    },

    async addItem() {
        this.items.push({ ...this.newItem });
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

    // Rest of your methods...
}));