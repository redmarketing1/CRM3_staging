<div class="context-menu" x-show="contextMenu.show"
    :style="`position: fixed; left: ${contextMenu.x}px; top: ${contextMenu.y}px; z-index: 1000;`"
    @click.outside="contextMenu.show = false">
    <button @click="moveRow('up', contextMenu.selectedRowId)" class="menu-item">
        <i class="fas fa-arrow-up"></i>
        Move Up
    </button>
    <button @click="moveRow('down', contextMenu.selectedRowId)" class="menu-item">
        <i class="fas fa-arrow-down"></i>
        Move Down
    </button>
    <button @click="duplicateRow(contextMenu.selectedRowId)" class="menu-item">
        <i class="fas fa-copy"></i>
        Duplicate
    </button>
    <button @click="addItem('item', contextMenu.selectedRowId)" class="menu-item">
        <i class="fas fa-plus"></i>
        Add Item
    </button>
    <button @click="addItem('group', contextMenu.selectedRowId)" class="menu-item">
        <i class="fas fa-folder-plus"></i>
        Add Group
    </button>
    <button @click="removeRowFromMenu(contextMenu.selectedRowId)" class="menu-item text-red-600">
        <i class="fas fa-trash"></i>
        Remove
    </button>
</div>
