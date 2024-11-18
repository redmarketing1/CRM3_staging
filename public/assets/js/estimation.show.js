/******/ (() => { // webpackBootstrap
/*!*******************************************************************!*\
  !*** ./Modules/Estimation/Resources/assets/js/estimation.show.js ***!
  \*******************************************************************/
function _slicedToArray(r, e) { return _arrayWithHoles(r) || _iterableToArrayLimit(r, e) || _unsupportedIterableToArray(r, e) || _nonIterableRest(); }
function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function _iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t["return"] && (u = t["return"](), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function _arrayWithHoles(r) { if (Array.isArray(r)) return r; }
Alpine.data('estimationShow', function () {
  return {
    items: {},
    newItems: {},
    groups: {},
    totals: {},
    expandedRows: {},
    lastGroupNumber: 0,
    lastItemNumbers: {},
    searchQuery: '',
    selectAll: false,
    contextMenu: {
      show: false,
      x: 0,
      y: 0,
      selectedRowId: null
    },
    init: function init() {
      var _this = this;
      this.initializeData();
      this.initializeSortable();
      this.initializeLastNumbers();
      this.initializeContextMenu();
      this.$watch('items', function () {
        return _this.calculateTotals();
      }, {
        deep: true
      });
      this.$watch('searchQuery', function () {
        return _this.filterTable();
      });
      this.$watch('selectAll', function (value) {
        return _this.checkboxAll(value);
      });
      document.addEventListener('click', function (e) {
        if (!e.target.closest('.context-menu')) {
          _this.showContextMenu = false;
        }
      });
    },
    initializeData: function initializeData() {
      var _this2 = this;
      // Reset all data structures
      this.items = {};
      this.groups = {};
      this.lastGroupNumber = 0;
      this.lastItemNumbers = {};

      // Process groups first without modifying POS
      document.querySelectorAll('tr.group_row').forEach(function (groupRow) {
        var groupId = groupRow.dataset.groupid;
        var groupPos = groupRow.querySelector('.grouppos').textContent.trim();
        var groupNumber = parseInt(groupPos);
        _this2.groups[groupId] = {
          id: groupId,
          pos: groupPos,
          name: groupRow.querySelector('.grouptitle-input').value,
          total: _this2.parseNumber(groupRow.querySelector('.text-right').textContent),
          itemCount: 0
        };
        _this2.lastGroupNumber = Math.max(_this2.lastGroupNumber, groupNumber);
      });

      // Process items and comments without modifying POS
      document.querySelectorAll('tr.item_row, tr.item_comment').forEach(function (row) {
        var isComment = row.classList.contains('item_comment');
        var itemId = isComment ? row.dataset.commentid : row.dataset.itemid;
        var groupId = row.closest('tbody').querySelector('tr.group_row').dataset.groupid;
        if (isComment) {
          _this2.items[itemId] = {
            id: itemId,
            type: 'comment',
            groupId: groupId,
            pos: row.querySelector('.pos-inner').textContent.trim(),
            content: row.querySelector('.column_name input').value,
            expanded: false
          };
        } else {
          _this2.items[itemId] = {
            id: itemId,
            type: 'item',
            groupId: groupId,
            pos: row.querySelector('.pos-inner').textContent.trim(),
            name: row.querySelector('.item-name').value,
            quantity: _this2.parseNumber(row.querySelector('.item-quantity').value),
            price: _this2.parseNumber(row.querySelector('.item-price').value),
            optional: row.querySelector('.item-optional').checked,
            unit: row.querySelector('.item-unit').value
          };
        }
        _this2.groups[groupId].itemCount++;
      });

      // Now update all POS numbers once
      this.updatePOSNumbers();

      // Calculate totals
      this.calculateTotals();
    },
    calculateItemTotal: function calculateItemTotal(itemId) {
      // Check both items and newItems
      var item = this.items[itemId] || this.newItems[itemId];
      if (!item || item.optional) return 0;
      return (item.quantity || 0) * (item.price || 0);
    },
    calculateTotals: function calculateTotals() {
      var _this3 = this;
      // Reset totals
      this.totals = {};

      // Initialize group totals for both existing and new groups
      document.querySelectorAll('tr.group_row').forEach(function (groupRow) {
        var groupId = groupRow.dataset.id || groupRow.dataset.groupid;
        if (!groupId) return;

        // Calculate total for this group
        var groupTotal = _this3.calculateGroupTotal(groupId);
        _this3.totals[groupId] = groupTotal;

        // Update the display
        var totalCell = groupRow.querySelector('.text-right');
        if (totalCell) {
          totalCell.textContent = _this3.formatCurrency(groupTotal);
        }

        // Update group data
        if (_this3.groups[groupId]) {
          _this3.groups[groupId].total = groupTotal;
        }
      });
    },
    calculateGroupTotal: function calculateGroupTotal(groupId) {
      var _this4 = this;
      var total = 0;

      // Calculate for existing items
      document.querySelectorAll("tr.item_row[data-groupid=\"".concat(groupId, "\"]")).forEach(function (itemRow) {
        var _itemRow$querySelecto;
        if (!((_itemRow$querySelecto = itemRow.querySelector('.item-optional')) !== null && _itemRow$querySelecto !== void 0 && _itemRow$querySelecto.checked)) {
          var _itemRow$querySelecto2, _itemRow$querySelecto3;
          var quantity = _this4.parseNumber(((_itemRow$querySelecto2 = itemRow.querySelector('.item-quantity')) === null || _itemRow$querySelecto2 === void 0 ? void 0 : _itemRow$querySelecto2.value) || '0');
          var price = _this4.parseNumber(((_itemRow$querySelecto3 = itemRow.querySelector('.item-price')) === null || _itemRow$querySelecto3 === void 0 ? void 0 : _itemRow$querySelecto3.value) || '0');
          total += quantity * price;
        }
      });

      // Calculate for new items in this group
      Object.values(this.newItems).forEach(function (item) {
        if (item.groupId === groupId && !item.optional && item.type === 'item') {
          total += (item.quantity || 0) * (item.price || 0);
        }
      });
      return total;
    },
    formatDecimal: function formatDecimal(value) {
      return new Intl.NumberFormat('de-DE', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      }).format(value);
    },
    formatCurrency: function formatCurrency(value) {
      return new Intl.NumberFormat('de-DE', {
        style: 'currency',
        currency: 'EUR'
      }).format(value);
    },
    parseNumber: function parseNumber(value) {
      if (typeof value === 'number') return value;
      return parseFloat(value.replace(/[^\d,-]/g, '').replace(',', '.')) || 0;
    },
    handleInputBlur: function handleInputBlur(event, type) {
      var value = event.target.value;
      var parsedValue = this.parseNumber(value);
      event.target.value = type === 'quantity' ? this.formatDecimal(parsedValue) : this.formatCurrency(parsedValue);
      var row = event.target.closest('tr');
      var itemId = row.dataset.id || row.dataset.itemid;

      // Update both items and newItems
      if (this.items[itemId]) {
        this.items[itemId][type] = parsedValue;
      }
      if (this.newItems[itemId]) {
        this.newItems[itemId][type] = parsedValue;
      }
      this.calculateTotals();
    },
    handleOptionalChange: function handleOptionalChange(event, itemId) {
      if (this.items[itemId]) {
        this.items[itemId].optional = event.target.checked;
        if (this.newItems[itemId]) {
          this.newItems[itemId].optional = event.target.checked;
        }
        this.calculateTotals();
      }
    },
    filterTable: function filterTable() {
      var searchTerm = this.searchQuery.toLowerCase();
      Object.entries(this.items).forEach(function (_ref) {
        var _ref2 = _slicedToArray(_ref, 2),
          itemId = _ref2[0],
          item = _ref2[1];
        var row = document.querySelector("tr[data-itemid=\"".concat(itemId, "\"]"));
        if (row) {
          row.style.display = item.name.toLowerCase().includes(searchTerm) || item.pos.toLowerCase().includes(searchTerm) ? '' : 'none';
        }
      });
      Object.entries(this.groups).forEach(function (_ref3) {
        var _ref4 = _slicedToArray(_ref3, 2),
          groupId = _ref4[0],
          group = _ref4[1];
        var row = document.querySelector("tr[data-groupid=\"".concat(groupId, "\"]"));
        if (row) {
          row.style.display = group.name.toLowerCase().includes(searchTerm) || group.pos.toLowerCase().includes(searchTerm) ? '' : 'none';
        }
      });
    },
    initializeSortable: function initializeSortable() {
      var _this5 = this;
      $("#estimation-edit-table").sortable({
        items: 'tbody tr',
        cursor: 'pointer',
        axis: 'y',
        dropOnEmpty: true,
        handle: '.fa-bars, .fa-up-down',
        animation: 150,
        start: function start(e, ui) {
          ui.item.addClass("selected");
        },
        stop: function stop(event, ui) {
          var movedRow = ui.item[0];

          // Only process if it's an item or comment row
          if (movedRow.classList.contains('item_row') || movedRow.classList.contains('item_comment')) {
            // Find the closest previous group row
            var currentRow = movedRow.previousElementSibling;
            var newGroupRow = null;
            while (currentRow && !newGroupRow) {
              if (currentRow.classList.contains('group_row')) {
                newGroupRow = currentRow;
              }
              currentRow = currentRow.previousElementSibling;
            }
            if (newGroupRow) {
              var _this5$items$itemId;
              var newGroupId = newGroupRow.dataset.id || newGroupRow.dataset.groupid;
              var itemId = movedRow.dataset.id || movedRow.dataset.itemid || movedRow.dataset.commentid;
              var oldGroupId = (_this5$items$itemId = _this5.items[itemId]) === null || _this5$items$itemId === void 0 ? void 0 : _this5$items$itemId.groupId;

              // Only update if group actually changed
              if (oldGroupId && oldGroupId !== newGroupId) {
                // Decrease old group count
                if (_this5.groups[oldGroupId]) {
                  _this5.groups[oldGroupId].itemCount = Math.max(0, (_this5.groups[oldGroupId].itemCount || 1) - 1);
                }

                // Increase new group count
                if (_this5.groups[newGroupId]) {
                  _this5.groups[newGroupId].itemCount = (_this5.groups[newGroupId].itemCount || 0) + 1;
                }

                // Update item's groupId in all relevant places
                if (_this5.items[itemId]) {
                  _this5.items[itemId].groupId = newGroupId;
                }
                if (_this5.newItems[itemId]) {
                  _this5.newItems[itemId].groupId = newGroupId;
                }

                // Update the DOM element's groupId
                movedRow.dataset.groupid = newGroupId;
              }
            }
          }

          // Update positions and recalculate totals
          _this5.updatePOSNumbers();
          _this5.calculateTotals();
        }
      });
    },
    initializeLastNumbers: function initializeLastNumbers() {
      var _this6 = this;
      var posNumbers = new Set();
      document.querySelectorAll('.pos-inner').forEach(function (element) {
        var pos = element.textContent.trim();
        if (pos) {
          posNumbers.add(pos);
          var _pos$split = pos.split('.'),
            _pos$split2 = _slicedToArray(_pos$split, 2),
            groupNum = _pos$split2[0],
            itemNum = _pos$split2[1];
          var groupNumber = parseInt(groupNum);
          var itemNumber = parseInt(itemNum);
          _this6.lastGroupNumber = Math.max(_this6.lastGroupNumber, groupNumber);
          if (!_this6.lastItemNumbers[groupNumber] || itemNumber > _this6.lastItemNumbers[groupNumber]) {
            // Ensure item numbers stay within 2 digits (max 99)
            _this6.lastItemNumbers[groupNumber] = Math.min(itemNumber, 99);
          }
        }
      });
      document.querySelectorAll('.grouppos').forEach(function (element) {
        var groupNum = parseInt(element.textContent.trim());
        _this6.lastGroupNumber = Math.max(_this6.lastGroupNumber, groupNum);
      });
    },
    addItem: function addItem(type) {
      var _this7 = this;
      var timestamp = Date.now();
      var GroupRow = document.querySelectorAll('tr.group_row');
      var lastGroupRow = GroupRow[GroupRow.length - 1];
      var currentGroupId = lastGroupRow ? lastGroupRow.dataset.groupid : null;
      if (!currentGroupId) return; // Need a group to add items

      var newItem = {
        id: timestamp,
        type: type,
        groupId: currentGroupId,
        name: type + " name",
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
      this.$nextTick(function () {
        _this7.initializeSortable();
        _this7.updatePOSNumbers();
        _this7.calculateTotals();
      });
    },
    removeItem: function removeItem() {
      var _this8 = this;
      var selectedCheckboxes = document.querySelectorAll('.item_selection:checked');
      if (selectedCheckboxes.length === 0) {
        toastrs("Error", "Please select checkbox to continue delete");
        return;
      }
      Swal.fire({
        title: 'Confirmation Delete',
        text: 'Really! You want to remove them? You can\'t undo',
        showCancelButton: true,
        confirmButtonText: 'Yes, Delete it',
        cancelButtonText: "No, cancel"
      }).then(function (result) {
        if (result.isConfirmed) {
          var itemIds = [];
          var groupIds = [];
          selectedCheckboxes.forEach(function (checkbox) {
            var row = checkbox.closest('tr');
            if (row.classList.contains('group_row')) {
              groupIds.push(row.dataset.groupid);
              delete _this8.groups[row.dataset.groupid];
            } else {
              itemIds.push(row.dataset.itemid);
              delete _this8.items[row.dataset.itemid];
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
          _this8.calculateTotals();
          _this8.updatePOSNumbers();
        }
      });
    },
    updatePOSNumbers: function updatePOSNumbers() {
      var _this9 = this;
      var currentGroupPos = 0;
      var itemCountInGroup = 0;
      var lastGroupId = null;
      document.querySelectorAll('tr').forEach(function (row) {
        if (row.classList.contains('group_row')) {
          currentGroupPos++;
          itemCountInGroup = 0;
          lastGroupId = row.dataset.groupid;
          var groupPos = currentGroupPos.toString().padStart(2, '0');
          row.querySelector('.grouppos').textContent = "".concat(groupPos);
          if (_this9.groups[lastGroupId]) {
            _this9.groups[lastGroupId].pos = groupPos;
          }
        } else if (row.classList.contains('item_row') || row.classList.contains('item_comment')) {
          itemCountInGroup++;
          var itemPos = "".concat(currentGroupPos.toString().padStart(2, '0'), ".").concat(itemCountInGroup.toString().padStart(2, '0'));
          row.querySelector('.pos-inner').textContent = itemPos;
          var itemId = row.dataset.itemid || row.dataset.commentid;
          if (_this9.items[itemId]) {
            _this9.items[itemId].pos = itemPos;
          }
        }
      });
    },
    toggleDescription: function toggleDescription(index, event) {
      event.stopPropagation();
      this.expandedRows[index] = !this.expandedRows[index];
    },
    isExpanded: function isExpanded(index) {
      return this.expandedRows[index] || false;
    },
    checkboxAll: function checkboxAll(value) {
      document.querySelectorAll('.item_selection').forEach(function (checkbox) {
        checkbox.checked = value;
      });
    },
    initializeContextMenu: function initializeContextMenu() {
      var _this10 = this;
      var handleContextMenu = function handleContextMenu(e) {
        var row = e.target.closest('tr.item_row, tr.group_row');
        if (!row) return;
        e.preventDefault();

        // Get viewport dimensions
        var viewportWidth = window.innerWidth;
        var viewportHeight = window.innerHeight;

        // Calculate position
        var x = e.clientX;
        var y = e.clientY;
        if (x + 160 > viewportWidth) x = viewportWidth - 160;
        if (y + 160 > viewportHeight) y = viewportHeight - 160;
        _this10.contextMenu = {
          show: true,
          x: x,
          y: y,
          selectedRowId: row.dataset.id || row.dataset.itemid || row.dataset.groupid
        };
      };

      // Use event delegation
      document.querySelector('#estimation-edit-table').addEventListener('contextmenu', handleContextMenu);
    },
    moveRow: function moveRow(direction, rowId) {
      var row = document.querySelector("tr[data-id=\"".concat(rowId, "\"], tr[data-itemid=\"").concat(rowId, "\"], tr[data-groupid=\"").concat(rowId, "\"]"));
      if (!row) return;
      if (direction === 'up') {
        var prevRow = row.previousElementSibling;
        if (prevRow) {
          row.parentNode.insertBefore(row, prevRow);
        }
      } else {
        var nextRow = row.nextElementSibling;
        if (nextRow) {
          row.parentNode.insertBefore(nextRow, row);
        }
      }

      // Update POS numbers and totals
      this.updatePOSNumbers();
      this.calculateTotals();
      this.contextMenu.show = false;
    },
    duplicateRow: function duplicateRow(rowId) {
      var _originalRow$querySel,
        _originalRow$querySel2,
        _originalRow$querySel3,
        _originalRow$querySel4,
        _originalRow$querySel5,
        _this11 = this;
      var originalRow = document.querySelector("tr[data-id=\"".concat(rowId, "\"], tr[data-itemid=\"").concat(rowId, "\"], tr[data-groupid=\"").concat(rowId, "\"]"));
      if (!originalRow) return;
      var timestamp = Date.now();
      var isGroup = originalRow.classList.contains('group_row');
      var groupId = isGroup ? null : originalRow.dataset.groupid;
      var type = originalRow.dataset.type;
      var name = ((_originalRow$querySel = originalRow.querySelector('.item-name, .grouptitle-input')) === null || _originalRow$querySel === void 0 ? void 0 : _originalRow$querySel.value) + " - copy " || 0;
      var quantity = parseInt(((_originalRow$querySel2 = originalRow.querySelector('.item-quantity')) === null || _originalRow$querySel2 === void 0 ? void 0 : _originalRow$querySel2.value) || '0');
      var unit = ((_originalRow$querySel3 = originalRow.querySelector('.item-unit')) === null || _originalRow$querySel3 === void 0 ? void 0 : _originalRow$querySel3.value) || 'unknown';
      var optional = ((_originalRow$querySel4 = originalRow.querySelector('.item-optional')) === null || _originalRow$querySel4 === void 0 ? void 0 : _originalRow$querySel4.checked) || false;
      var price = parseInt(((_originalRow$querySel5 = originalRow.querySelector('.item-price')) === null || _originalRow$querySel5 === void 0 ? void 0 : _originalRow$querySel5.value) || '0');
      if (isGroup) {
        var newItem = {
          id: timestamp,
          type: 'group',
          name: name,
          total: 0,
          expanded: false
        };

        // Add to both collections
        this.items[timestamp] = newItem;
        this.newItems[timestamp] = newItem;
      } else {
        // Get values from original row
        var _newItem = {
          id: timestamp,
          type: type,
          groupId: groupId,
          name: name,
          quantity: quantity,
          unit: unit,
          optional: optional,
          price: price,
          expanded: false
        };

        // Add to both collections
        this.items[timestamp] = _newItem;
        this.newItems[timestamp] = _newItem;
      }

      // Ensure proper order
      this.$nextTick(function () {
        // Find the new row and move it after the original
        var newRow = document.querySelector("tr[data-id=\"".concat(timestamp, "\"], tr[data-itemid=\"").concat(timestamp, "\"]"));
        if (newRow && originalRow.nextSibling) {
          originalRow.parentNode.insertBefore(newRow, originalRow.nextSibling);
        }
        _this11.updatePOSNumbers();
        _this11.calculateTotals();
        _this11.initializeContextMenu();
      });
      this.contextMenu.show = false;
    },
    removeRowFromMenu: function removeRowFromMenu(rowId) {
      var _this12 = this;
      Swal.fire({
        title: 'Confirmation Delete',
        text: 'Really! You want to remove this item? You can\'t undo',
        showCancelButton: true,
        confirmButtonText: 'Yes, Delete it',
        cancelButtonText: "No, cancel"
      }).then(function (result) {
        if (result.isConfirmed) {
          var row = document.querySelector("tr[data-id=\"".concat(rowId, "\"], tr[data-itemid=\"").concat(rowId, "\"], tr[data-groupid=\"").concat(rowId, "\"]"));
          if (!row) return;
          if (row.classList.contains('group_row')) {
            // If it's a group, also remove all its items
            var groupId = row.dataset.groupid;
            document.querySelectorAll("tr[data-groupid=\"".concat(groupId, "\"]")).forEach(function (itemRow) {
              var itemId = itemRow.dataset.itemid;
              delete _this12.items[itemId];
              delete _this12.newItems[itemId];
              itemRow.remove();
            });
            delete _this12.groups[groupId];
          } else {
            // Remove single item
            var itemId = row.dataset.itemid || row.dataset.id;
            delete _this12.items[itemId];
            delete _this12.newItems[itemId];
          }
          row.remove();
          _this12.updatePOSNumbers();
          _this12.calculateTotals();

          // Handle UI updates
          document.querySelector('.SelectAllCheckbox').checked = false;
        }
      });
      this.contextMenu.show = false;
    }
  };
});
/******/ })()
;