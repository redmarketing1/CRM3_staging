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
    init: function init() {
      var _this = this;
      this.initializeData();
      this.initializeLastNumbers();
      this.initializeSortable();
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
      var item = this.items[itemId];
      if (!item || item.optional) return 0;
      return item.quantity * item.price;
    },
    calculateTotals: function calculateTotals() {
      var _this3 = this;
      this.totals = {};

      // Reset group totals
      Object.keys(this.groups).forEach(function (groupId) {
        _this3.totals[groupId] = 0;
      });

      // Calculate item totals and update group totals
      Object.entries(this.items).forEach(function (_ref) {
        var _ref2 = _slicedToArray(_ref, 2),
          itemId = _ref2[0],
          item = _ref2[1];
        if (!item.optional) {
          var total = _this3.calculateItemTotal(itemId);
          if (item.groupId && _this3.totals[item.groupId] !== undefined) {
            _this3.totals[item.groupId] += total;
          }
        }
      });

      // Update group total displays
      Object.entries(this.totals).forEach(function (_ref3) {
        var _ref4 = _slicedToArray(_ref3, 2),
          groupId = _ref4[0],
          total = _ref4[1];
        var groupRow = document.querySelector("tr[data-groupid=\"".concat(groupId, "\"]"));
        if (groupRow) {
          var totalCell = groupRow.querySelector('.text-right');
          if (totalCell) {
            totalCell.textContent = _this3.formatCurrency(total);
          }
        }
      });
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
      var itemId = row.dataset.itemid;
      if (this.items[itemId]) {
        this.items[itemId][type] = parsedValue;
        this.calculateTotals();
      }
    },
    handleOptionalChange: function handleOptionalChange(event, itemId) {
      if (this.items[itemId]) {
        this.items[itemId].optional = event.target.checked;
        this.calculateTotals();
      }
    },
    filterTable: function filterTable() {
      var searchTerm = this.searchQuery.toLowerCase();
      Object.entries(this.items).forEach(function (_ref5) {
        var _ref6 = _slicedToArray(_ref5, 2),
          itemId = _ref6[0],
          item = _ref6[1];
        var row = document.querySelector("tr[data-itemid=\"".concat(itemId, "\"]"));
        if (row) {
          row.style.display = item.name.toLowerCase().includes(searchTerm) || item.pos.toLowerCase().includes(searchTerm) ? '' : 'none';
        }
      });
      Object.entries(this.groups).forEach(function (_ref7) {
        var _ref8 = _slicedToArray(_ref7, 2),
          groupId = _ref8[0],
          group = _ref8[1];
        var row = document.querySelector("tr[data-groupid=\"".concat(groupId, "\"]"));
        if (row) {
          row.style.display = group.name.toLowerCase().includes(searchTerm) || group.pos.toLowerCase().includes(searchTerm) ? '' : 'none';
        }
      });
    },
    initializeSortable: function initializeSortable() {
      var _this4 = this;
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
        stop: function stop() {
          return _this4.updatePOSNumbers();
        }
      });
    },
    initializeLastNumbers: function initializeLastNumbers() {
      var _this5 = this;
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
          _this5.lastGroupNumber = Math.max(_this5.lastGroupNumber, groupNumber);
          if (!_this5.lastItemNumbers[groupNumber] || itemNumber > _this5.lastItemNumbers[groupNumber]) {
            // Ensure item numbers stay within 2 digits (max 99)
            _this5.lastItemNumbers[groupNumber] = Math.min(itemNumber, 99);
          }
        }
      });
      document.querySelectorAll('.grouppos').forEach(function (element) {
        var groupNum = parseInt(element.textContent.trim());
        _this5.lastGroupNumber = Math.max(_this5.lastGroupNumber, groupNum);
      });
    },
    addItem: function addItem(type) {
      var _this6 = this;
      var timestamp = Date.now();
      this.newItems[timestamp] = {
        id: timestamp,
        type: type,
        quantity: 0,
        optional: false,
        expanded: false
      };
      this.$nextTick(function () {
        _this6.initializeSortable();
        _this6.updatePOSNumbers();
      });
    },
    removeItem: function removeItem() {
      var _this7 = this;
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
              delete _this7.groups[row.dataset.groupid];
            } else {
              itemIds.push(row.dataset.itemid);
              delete _this7.items[row.dataset.itemid];
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
          _this7.calculateTotals();
          _this7.updatePOSNumbers();
        }
      });
    },
    updatePOSNumbers: function updatePOSNumbers() {
      var _this8 = this;
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
          if (_this8.groups[lastGroupId]) {
            _this8.groups[lastGroupId].pos = groupPos;
          }
        } else if (row.classList.contains('item_row') || row.classList.contains('item_comment')) {
          itemCountInGroup++;
          var itemPos = "".concat(currentGroupPos.toString().padStart(2, '0'), ".").concat(itemCountInGroup.toString().padStart(2, '0'));
          row.querySelector('.pos-inner').textContent = itemPos;
          var itemId = row.dataset.itemid || row.dataset.commentid;
          if (_this8.items[itemId]) {
            _this8.items[itemId].pos = itemPos;
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
    }
  };
});
/******/ })()
;