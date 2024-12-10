/******/ (() => { // webpackBootstrap
/*!*******************************************************************!*\
  !*** ./Modules/Estimation/Resources/assets/js/estimation.show.js ***!
  \*******************************************************************/
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function _objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? ownKeys(Object(t), !0).forEach(function (r) { _defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
function _slicedToArray(r, e) { return _arrayWithHoles(r) || _iterableToArrayLimit(r, e) || _unsupportedIterableToArray(r, e) || _nonIterableRest(); }
function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t["return"] && (u = t["return"](), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function _arrayWithHoles(r) { if (Array.isArray(r)) return r; }
function _toConsumableArray(r) { return _arrayWithoutHoles(r) || _iterableToArray(r) || _unsupportedIterableToArray(r) || _nonIterableSpread(); }
function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _iterableToArray(r) { if ("undefined" != typeof Symbol && null != r[Symbol.iterator] || null != r["@@iterator"]) return Array.from(r); }
function _arrayWithoutHoles(r) { if (Array.isArray(r)) return _arrayLikeToArray(r); }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
document.addEventListener('alpine:init', function () {
  Alpine.data('estimationShow', function () {
    var _ref5;
    return _ref5 = {
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
      saveInterval: 1000 * 30,
      // 30 second
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
      init: function init() {
        var _this = this;
        this.tableData = JSON.parse(document.querySelector('#estimation-edit-table').dataset.table);
        this.$nextTick(function () {
          _this.isInitializing = true;
          _this.initializeData();
          _this.initializeSortable();
          _this.initializeLastNumbers();
          _this.initializeContextMenu();
          _this.initializeColumnVisibility();
          _this.initializeCardCalculations();
          _this.initializeAutoSave();
          _this.calculateTotals();
          _this.$nextTick(function () {
            _this.isInitializing = false; // Reset flag after all initialization is done
          });
        });
        this.$watch('items', function (value) {
          _this.calculateTotals();
          var cardQuoteIds = _toConsumableArray(new Set(Array.from(document.querySelectorAll('[data-cardquoteid]')).map(function (el) {
            return el.dataset.cardquoteid;
          })));
          cardQuoteIds.forEach(function (cardQuoteId) {
            return _this.calculateCardTotals(cardQuoteId);
          });
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
        this.$watch('hasUnsavedChanges', function () {
          if (!_this.hasUnsavedChanges) {
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
        document.addEventListener('fullscreenchange', function () {
          _this.isFullScreen = !!document.fullscreenElement;
          var icon = document.querySelector('.fa-expand, .fa-compress');
          if (icon) {
            icon.className = _this.isFullScreen ? 'fa-solid fa-compress' : 'fa-solid fa-expand';
          }
        });
        document.addEventListener('keydown', function (e) {
          if (e.target.classList.contains('item-quantity') || e.target.classList.contains('item-name') || e.target.classList.contains('item-price') || e.target.classList.contains('form-blur')) {
            _this.handleInputBlur(e);
          }
        });
        if (this.lastSaveTimestamp) {
          this.startTimeAgoUpdates();
        }
      },
      initializeData: function initializeData() {
        var _this$tableData$estim,
          _this2 = this;
        var cardQuoteIds = _toConsumableArray(new Set(Array.from(document.querySelectorAll('[data-cardquoteid]')).map(function (el) {
          return el.dataset.cardquoteid;
        })));
        (_this$tableData$estim = this.tableData.estimation_groups) === null || _this$tableData$estim === void 0 || _this$tableData$estim.forEach(function (group) {
          var _group$estimation_pro;
          var groupData = {
            id: group.id,
            type: 'group',
            name: group.group_name,
            pos: group.group_pos,
            total: 0,
            expanded: false
          };
          _this2.newItems[group.id] = groupData;
          _this2.lastGroupNumber = Math.max(_this2.lastGroupNumber, parseInt(group.group_pos));
          (_group$estimation_pro = group.estimation_products) === null || _group$estimation_pro === void 0 || _group$estimation_pro.forEach(function (item) {
            var itemData = {
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
              prices: _this2.initializePrices(cardQuoteIds, item, item.quote_items || [])
            };
            _this2.newItems[item.id] = itemData;
          });
        });
      },
      initializePrices: function initializePrices(cardQuoteIds) {
        var item = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;
        var quoteItems = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : [];
        return cardQuoteIds.reduce(function (acc, quoteId) {
          var quoteItem = quoteItems.find(function (quote) {
            return quote.estimate_quote_id == quoteId;
          }) || {};
          acc[quoteId] = {
            quoteId: quoteId,
            type: (item === null || item === void 0 ? void 0 : item.type) || null,
            singlePrice: quoteItem.base_price || 0,
            totalPrice: quoteItem.total_price || ((item === null || item === void 0 ? void 0 : item.quantity) || 0) * (quoteItem.price || 0)
          };
          return acc;
        }, {});
      },
      initializeFullScreen: function initializeFullScreen() {
        var _this3 = this;
        document.addEventListener('fullscreenchange', function () {
          _this3.isFullScreen = !!document.fullscreenElement;
          var btn = document.querySelector('.tools-btn button i.fa-expand, .tools-btn button i.fa-compress');
          if (btn) {
            btn.className = _this3.isFullScreen ? 'fa-solid fa-compress' : 'fa-solid fa-expand';
          }
        });
      },
      initializeAutoSave: function initializeAutoSave() {
        var _this4 = this;
        if (!document.querySelector('#quote_form')) {
          console.warn('Quote form not found');
          return;
        }

        // Initialize auto-save related event listeners
        window.addEventListener('beforeunload', function (e) {
          if (_this4.hasUnsavedChanges) {
            var message = 'You have unsaved changes. Are you sure you want to leave?';
            e.preventDefault();
            e.returnValue = message;
            return message;
          }
        });
      },
      toggleFullScreen: function toggleFullScreen() {
        var estimationSection = document.querySelector('.estimation-show');
        if (!estimationSection) return;
        if (!document.fullscreenElement) {
          estimationSection.requestFullscreen()["catch"](function (err) {
            console.error("Error attempting to enable fullscreen: ".concat(err.message));
          });
        } else {
          document.exitFullscreen();
        }
      },
      calculateItemTotal: function calculateItemTotal(itemId, quoteId) {
        var _item$prices$quoteId;
        // Early validation
        if (!itemId || !this.newItems[itemId]) return 0;
        if (this.newItems[itemId].optional) return 0;
        var item = this.newItems[itemId];
        var quantity = this.parseNumber(item.quantity || 0);
        var singlePrice = this.parseNumber(((_item$prices$quoteId = item.prices[quoteId]) === null || _item$prices$quoteId === void 0 ? void 0 : _item$prices$quoteId.singlePrice) || 0);
        var totalPrice = quantity * singlePrice;
        if (item.prices[quoteId]) {
          item.prices[quoteId].totalPrice = totalPrice;
        }
        return totalPrice;
      },
      calculateTotals: function calculateTotals() {
        var _this5 = this;
        this.totals = {};
        document.querySelectorAll('tr.group_row').forEach(function (row) {
          var groupId = row.dataset.groupid;
          if (!groupId) return;
          _this5.calculateGroupTotal(groupId);
          if (_this5.groups[groupId]) {
            var _row$querySelector;
            _this5.groups[groupId].total = _this5.parseNumber(((_row$querySelector = row.querySelector('.text-right.grouptotal')) === null || _row$querySelector === void 0 ? void 0 : _row$querySelector.textContent) || '0');
          }
        });
      },
      calculateGroupTotal: function calculateGroupTotal(groupId) {
        var _this6 = this;
        var totals = {};
        var groupRow = document.querySelector("tr.group_row[data-groupid=\"".concat(groupId, "\"]"));
        if (!groupRow) return 0;
        var currentRow = groupRow.nextElementSibling;
        var _loop = function _loop() {
          if (currentRow.classList.contains('item_row')) {
            var _currentRow$querySele;
            var itemId = currentRow.dataset.itemid;
            var isOptional = (_currentRow$querySele = currentRow.querySelector('.item-optional')) === null || _currentRow$querySele === void 0 ? void 0 : _currentRow$querySele.checked;
            if (!isOptional) {
              var _currentRow$querySele2;
              var quantity = _this6.parseNumber(((_currentRow$querySele2 = currentRow.querySelector('.item-quantity')) === null || _currentRow$querySele2 === void 0 ? void 0 : _currentRow$querySele2.value) || '0');
              var priceInputs = currentRow.querySelectorAll('.item-price');
              priceInputs.forEach(function (priceInput, index) {
                if (!totals[index]) totals[index] = 0;
                var price = _this6.parseNumber(priceInput.value || '0');
                var total = quantity * price;
                totals[index] += total;

                // Update individual item total
                var totalCell = currentRow.querySelectorAll('.column_total_price')[index];
                if (totalCell) {
                  totalCell.textContent = _this6.formatCurrency(total);
                  _this6.setNegativeStyle(totalCell, total);
                }
              });
            } else {
              // Clear totals for optional items
              currentRow.querySelectorAll('.column_total_price').forEach(function (cell) {
                cell.textContent = '-';
                cell.style.backgroundColor = '';
                cell.style.color = '';
              });
            }
          }
          currentRow = currentRow.nextElementSibling;
        };
        while (currentRow && !currentRow.classList.contains('group_row')) {
          _loop();
        }

        // Update group totals
        var totalCells = groupRow.querySelectorAll('.text-right.grouptotal');
        totalCells.forEach(function (cell, index) {
          var total = totals[index] || 0;
          cell.textContent = _this6.formatCurrency(total);
          _this6.setNegativeStyle(cell, total);

          // Update card totals
          var cardQuoteId = cell.dataset.cardquoteid;
          if (cardQuoteId) {
            _this6.calculateCardTotals(cardQuoteId);
          }
        });
        return totals[0] || 0;
      },
      calculateCardTotals: function calculateCardTotals(cardQuoteId) {
        var _this7 = this;
        var subtotal = 0;
        var groupTotalCells = document.querySelectorAll("td[data-cardquoteid=\"".concat(cardQuoteId, "\"].grouptotal"));
        groupTotalCells.forEach(function (cell) {
          subtotal += _this7.parseNumber(cell.textContent);
        });
        var markupInput = document.querySelector("#quoteMarkup[name=\"item[".concat(cardQuoteId, "][markup]\"]"));
        var markup = this.parseNumber((markupInput === null || markupInput === void 0 ? void 0 : markupInput.value) || '0');
        var netAmount = subtotal + markup;
        var discountInput = document.querySelector("input[name=\"item[".concat(cardQuoteId, "][discount]\"]"));
        var cashDiscount = this.parseNumber((discountInput === null || discountInput === void 0 ? void 0 : discountInput.value) || '0');
        var discountAmount = netAmount * cashDiscount / 100;
        var netWithDiscount = netAmount - discountAmount;
        var vatSelect = document.querySelector("select[name=\"item[".concat(cardQuoteId, "][tax]\"]"));
        var vatRate = vatSelect ? this.parseNumber(vatSelect.value) / 100 : 0;
        var grossWithDiscount = netWithDiscount;
        if (vatRate > 0) {
          var vatAmount = grossWithDiscount * vatRate;
          grossWithDiscount = netWithDiscount + vatAmount;
        }
        this.updateCardTotalUI(cardQuoteId, {
          netAmount: netAmount,
          netWithDiscount: netWithDiscount,
          grossWithDiscount: grossWithDiscount,
          subtotal: subtotal
        });
      },
      updateCardTotalUI: function updateCardTotalUI(cardQuoteId, totals) {
        var netDiscountElement = document.querySelector("th[data-cardquoteid=\"".concat(cardQuoteId, "\"].total-net-discount"));
        if (netDiscountElement) {
          netDiscountElement.textContent = this.formatCurrency(totals.netWithDiscount);
        }
        var grossDiscountElement = document.querySelector("th[data-cardquoteid=\"".concat(cardQuoteId, "\"].total-gross-discount"));
        if (grossDiscountElement) {
          grossDiscountElement.textContent = this.formatCurrency(totals.grossWithDiscount);
        }
        var netElement = document.querySelector("th[data-cardquoteid=\"".concat(cardQuoteId, "\"].total-net"));
        if (netElement) {
          netElement.textContent = this.formatCurrency(totals.netAmount);
        }
        var grossElement = document.querySelector("th[data-cardquoteid=\"".concat(cardQuoteId, "\"] .total-gross-total"));
        if (grossElement) {
          grossElement.textContent = this.formatCurrency(totals.grossWithDiscount);
        }
        this.autoSaveHandler();
      },
      initializeCardCalculations: function initializeCardCalculations() {
        var _this8 = this;
        var cardQuoteIds = _toConsumableArray(new Set(Array.from(document.querySelectorAll('[data-cardquoteid]')).map(function (el) {
          return el.dataset.cardquoteid;
        })));
        cardQuoteIds.forEach(function (cardQuoteId) {
          // Get initial values
          var markupInput = document.querySelector("#quoteMarkup[name=\"item[".concat(cardQuoteId, "][markup]\"]"));
          var discountInput = document.querySelector("input[name=\"item[".concat(cardQuoteId, "][discount]\"]"));
          var vatSelect = document.querySelector("select[name=\"item[".concat(cardQuoteId, "][tax]\"]"));

          // Format and apply markup
          if (markupInput) {
            var value = _this8.parseNumber(markupInput.value);
            markupInput.value = _this8.formatDecimal(value);
            _this8.setNegativeStyle(markupInput, value);

            // Apply markup calculations
            _this8.updateMarkupCalculations({
              target: markupInput
            }, cardQuoteId);
          }

          // Format and apply discount
          if (discountInput) {
            var _value = _this8.parseNumber(discountInput.value);
            discountInput.value = _this8.formatDecimal(_value);
            // Trigger discount calculations
            _this8.handleInputBlur({
              target: discountInput
            }, 'cashDiscount');
          }

          // Apply VAT calculations if set
          if (vatSelect) {
            _this8.handleVatChangeAmount({
              target: vatSelect
            }, cardQuoteId);
          }

          // Calculate all totals for this column
          _this8.calculateCardTotals(cardQuoteId);
        });
      },
      handleVatChangeAmount: function handleVatChangeAmount(event, cardQuoteId) {
        this.calculateCardTotals(cardQuoteId);
      },
      updateMarkupCalculations: function updateMarkupCalculations(event, cardQuoteId) {
        var _this9 = this;
        var target = event.target;
        var markup = this.parseNumber(target.value || '0');
        target.value = this.formatDecimal(markup);
        this.setMarkupStyle(target, markup);
        var priceInputs = document.querySelectorAll("[data-cardquoteid=\"".concat(cardQuoteId, "\"] .item-price"));
        priceInputs.forEach(function (input) {
          var originalPrice = input.dataset.originalPrice ? _this9.parseNumber(input.dataset.originalPrice) : _this9.parseNumber(input.value);
          if (!input.dataset.originalPrice) {
            input.dataset.originalPrice = originalPrice;
          }
          var newPrice = _this9.parseNumber(input.dataset.originalPrice) + markup;
          input.value = _this9.formatCurrency(newPrice);
          var row = input.closest('tr');
          if (row) {
            var itemId = row.dataset.itemid;
            if (itemId) {
              _this9.calculateItemTotal(itemId);
            }
          }
        });
        this.calculateGroupTotal(this.lastGroupId);
        this.calculateTotals();
        this.calculateCardTotals(cardQuoteId);
      },
      setMarkupStyle: function setMarkupStyle(input, value) {
        if (value < 0) {
          input.style.backgroundColor = 'rgb(255 240 240)';
          input.style.color = 'rgb(255 6 6)';
        } else {
          input.style.backgroundColor = '';
          input.style.color = '';
        }
      },
      setNegativeStyle: function setNegativeStyle(element, value) {
        if (value < 0) {
          element.style.backgroundColor = 'rgb(255 240 240)';
          element.style.color = 'rgb(255 6 6)';
        } else {
          element.style.backgroundColor = '';
          element.style.color = '';
        }
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
      formatDecimalValue: function formatDecimalValue(target) {
        target.value = this.formatDecimal(this.parseNumber(target.value));
      },
      formatCurrencyValue: function formatCurrencyValue(target) {
        target.value = this.formatCurrency(this.parseNumber(target.value));
      },
      parseNumber: function parseNumber(value) {
        if (typeof value === 'number') return value;
        return parseFloat(value.replace(/[^\d,-]/g, '').replace(',', '.')) || 0;
      },
      updateItemPriceAndTotal: function updateItemPriceAndTotal(itemId) {
        var _this10 = this;
        if (!itemId || !this.items[itemId]) return [];
        var item = this.items[itemId];
        var quantity = this.parseNumber(item.quantity || '0');
        var cardQuoteIds = _toConsumableArray(new Set(Array.from(document.querySelectorAll('[data-cardquoteid]'), function (el) {
          return el.dataset.cardquoteid;
        })));
        var prices = cardQuoteIds.map(function (quoteId) {
          return {
            id: quoteId,
            singlePrice: _this10.parseNumber(item.price || '0'),
            totalPrice: quantity * _this10.parseNumber(item.price || '0')
          };
        });
        if (this.items[itemId]) {
          this.items[itemId].prices = prices;
        }
        return prices;
      },
      handleInputBlur: function handleInputBlur(event, type) {
        var _event$target$closest,
          _this11 = this;
        if (!(event !== null && event !== void 0 && event.target) || !type) return;
        if (event.type === 'keydown' && event.key !== 'Enter') return;
        if (event.type === 'keydown') event.target.blur();
        var row = event.target.closest('tr');
        if (!(row !== null && row !== void 0 && row.dataset)) return;
        var value = event.target.value;
        var _row$dataset = row.dataset,
          itemid = _row$dataset.itemid,
          commentid = _row$dataset.commentid,
          groupid = _row$dataset.groupid;
        var cardQuoteId = (_event$target$closest = event.target.closest('[data-cardquoteid]')) === null || _event$target$closest === void 0 ? void 0 : _event$target$closest.dataset.cardquoteid;
        var inputHandlers = {
          item: function item() {
            return _this11.updateNewItem(itemid, {
              name: value
            });
          },
          comment: function comment() {
            return _this11.updateNewItem(commentid, {
              content: value
            });
          },
          group: function group() {
            return _this11.updateNewItem(groupid, {
              name: value
            });
          },
          quantity: function quantity() {
            var quantity = _this11.parseNumber(value);
            _this11.updateNewItem(itemid, {
              quantity: quantity
            });
            _this11.updateItemPrices(itemid);
            _this11.formatDecimalValue(event.target);
          },
          price: function price() {
            var singlePrice = _this11.parseNumber(value);
            _this11.updateNewItem(itemid, {
              singlePrice: singlePrice
            }, cardQuoteId);
            _this11.updateItemPrices(itemid);
            _this11.formatCurrencyValue(event.target);
          },
          unit: function unit() {
            return _this11.updateNewItem(itemid, {
              unit: value
            });
          },
          cashDiscount: function cashDiscount() {
            event.target.value = _this11.formatDecimal(_this11.parseNumber(value) || 0);
          }
        };
        try {
          var _inputHandlers$type;
          (_inputHandlers$type = inputHandlers[type]) === null || _inputHandlers$type === void 0 || _inputHandlers$type.call(inputHandlers);
          if (!this.isInitializing) {
            this.hasUnsavedChanges = true;
            this.calculateTotals();
            this.autoSaveHandler();
          }
        } catch (error) {
          console.error("Error in handleInputBlur: ".concat(error.message), {
            type: type,
            value: value
          });
        }
      },
      updateItemPrices: function updateItemPrices(itemId) {
        if (!this.newItems[itemId]) return;
        var item = this.newItems[itemId];
        var quantity = item.quantity || 0;
        Object.keys(item.prices || {}).forEach(function (cardQuoteId) {
          var price = item.prices[cardQuoteId];
          price.totalPrice = quantity * (price.singlePrice || 0);
        });
      },
      updateNewItem: function updateNewItem(id, updates) {
        var cardQuoteId = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : null;
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
      handleOptionalChange: function handleOptionalChange(event, itemId) {
        if (this.newItems[itemId]) {
          this.newItems[itemId].optional = event.target.checked ? 1 : 0;
        }
        var row = event.target.closest('tr');
        var groupId = row.dataset.groupid;
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
      autoSaveHandler: function autoSaveHandler() {
        var _this12 = this;
        if (!this.autoSaveEnabled || !document.querySelector('#quote_form') || !this.hasUnsavedChanges) return;
        if (this.saveTimeout) {
          clearTimeout(this.saveTimeout);
        }
        var currentTime = Date.now();
        var timeSinceLastSave = currentTime - this.lastSaveTime;
        if (timeSinceLastSave >= this.saveInterval) {
          this.saveTableData();
          this.lastSaveTime = currentTime;
        } else {
          this.saveTimeout = setTimeout(function () {
            if (_this12.hasUnsavedChanges) {
              _this12.saveTableData();
              _this12.lastSaveTime = Date.now();
            }
          }, this.saveInterval);
        }
      },
      filterTable: function filterTable() {
        var searchTerm = this.searchQuery.toLowerCase();
        Object.entries(this.newItems).forEach(function (_ref) {
          var _ref2 = _slicedToArray(_ref, 2),
            itemId = _ref2[0],
            item = _ref2[1];
          var row = document.querySelector("tr[data-itemid=\"".concat(itemId, "\"]"));
          if (row) {
            row.style.display = item.name.toLowerCase().includes(searchTerm) || item.pos.toLowerCase().includes(searchTerm) ? '' : 'none';
          }
        });
      },
      initializeSortable: function initializeSortable() {
        var _this13 = this;
        $("#estimation-edit-table").sortable({
          items: 'tbody tr',
          cursor: 'pointer',
          axis: 'y',
          dropOnEmpty: true,
          handle: '.fa-bars, .fa-up-down',
          animation: 150,
          start: function start(e, ui) {
            // ui.item.addClass("selected");
          },
          stop: function stop(event, ui) {
            var movedRow = ui.item[0];
            if (movedRow.classList.contains('item_row') || movedRow.classList.contains('item_comment')) {
              var currentRow = movedRow.previousElementSibling;
              var newGroupRow = null;

              // Find the new group
              while (currentRow && !newGroupRow) {
                if (currentRow.classList.contains('group_row')) {
                  newGroupRow = currentRow;
                }
                currentRow = currentRow.previousElementSibling;
              }
              if (newGroupRow) {
                var newGroupId = newGroupRow.dataset.groupid;
                var itemId = movedRow.dataset.itemid || movedRow.dataset.commentid;
                var oldGroupId = movedRow.dataset.groupid;

                // Update group ID in DOM
                movedRow.dataset.groupid = newGroupId;

                // Update items/comments in state
                if (movedRow.classList.contains('item_row')) {
                  if (_this13.newItems[itemId]) {
                    _this13.newItems[itemId].groupId = newGroupId;
                  }
                } else if (movedRow.classList.contains('item_comment')) {
                  if (_this13.newItems[itemId]) {
                    _this13.newItems[itemId].groupId = newGroupId;
                  }
                }

                // Update old and new group calculations
                _this13.calculateGroupTotal(oldGroupId);
                _this13.calculateGroupTotal(newGroupId);
              }
            }

            // Update positions and recalculate all totals
            _this13.updatePOSNumbers();
            _this13.calculateTotals();

            // Mark as unsaved and trigger auto-save
            if (!_this13.isInitializing) {
              _this13.hasUnsavedChanges = true;
              _this13.autoSaveHandler();
            }
          }
        });
      },
      initializeLastNumbers: function initializeLastNumbers() {
        var _this14 = this;
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
            _this14.lastGroupNumber = Math.max(_this14.lastGroupNumber, groupNumber);
            if (!_this14.lastItemNumbers[groupNumber] || itemNumber > _this14.lastItemNumbers[groupNumber]) {
              _this14.lastItemNumbers[groupNumber] = Math.min(itemNumber, 99);
            }
          }
        });
        document.querySelectorAll('.grouppos').forEach(function (element) {
          var groupNum = parseInt(element.textContent.trim());
          _this14.lastGroupNumber = Math.max(_this14.lastGroupNumber, groupNum);
        });
      },
      addItem: function addItem(type) {
        var targetGroupId = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;
        var timestamp = Date.now();
        if (type === 'group') {
          this.createGroups(type, timestamp);
        } else if (type === 'item' || type === 'comment') {
          this.createItemsAndComments(type, timestamp, targetGroupId);
        }
      },
      createGroups: function createGroups(type, timestamp, targetRowId) {
        var _this15 = this;
        if (type !== 'group') return;
        var itemTimestamp = Date.now() + 1;
        var hasAnyGroups = Object.keys(this.groups).length > 0;

        // Helper to create a group
        var createGroup = function createGroup(id, name) {
          var itemCount = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : 0;
          return {
            id: id,
            type: 'group',
            name: name,
            total: 0,
            expanded: false,
            pos: '',
            itemCount: itemCount
          };
        };
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
        this.$nextTick(function () {
          _this15.initializeSortable();
          _this15.updatePOSNumbers();
          _this15.calculateTotals();
        });
        return;
      },
      createItemsAndComments: function createItemsAndComments(type, timestamp) {
        var _this16 = this;
        var targetGroupId = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : null;
        if (type !== 'item' && type !== 'comment') return;
        var initialPrices = _toConsumableArray(new Set(Array.from(document.querySelectorAll('[data-cardquoteid]'), function (el) {
          return el.dataset.cardquoteid;
        }))).map(function (quoteId) {
          return {
            quoteId: quoteId,
            type: 'item',
            singlePrice: 0,
            totalPrice: 0
          };
        });

        // Use passed targetGroupId or get from current context
        var currentGroupId = targetGroupId || this.getCurrentGroupId();
        if (!currentGroupId) return;
        if (type === 'item') {
          var items = {
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
            pos: ''
          };
          this.items[timestamp] = items;
          this.newItems[timestamp] = items;
        } else {
          var comment = {
            id: timestamp,
            type: 'comment',
            groupId: currentGroupId,
            content: 'New Comment',
            expanded: false,
            pos: ''
          };
          this.comments[timestamp] = comment;
          this.newItems[timestamp] = comment;
        }
        this.$nextTick(function () {
          _this16.updatePOSNumbers();
          _this16.calculateTotals();
        });
      },
      removeItem: function removeItem() {
        var _this17 = this;
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
            var estimationId = _this17.getFomrData().id;
            var itemIds = [];
            var groupIds = [];
            selectedCheckboxes.forEach(function (checkbox) {
              var row = checkbox.closest('tr');
              if (row.classList.contains('group_row')) {
                groupIds.push(row.dataset.groupid);
                delete _this17.groups[row.dataset.groupid];
                delete _this17.newItems[row.dataset.groupid];
              } else {
                var IDs = row.dataset.id;
                itemIds.push(IDs);
                delete _this17.newItems[IDs];
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
              success: function success(response) {
                console.log(response);
              }
            });
            _this17.calculateTotals();
            _this17.updatePOSNumbers();
          }
        });
      },
      getCurrentGroupId: function getCurrentGroupId(targetRowId) {
        if (targetRowId) {
          var targetRow = document.querySelector("tr[data-id=\"".concat(targetRowId, "\"], \n                 tr[data-itemid=\"").concat(targetRowId, "\"], \n                 tr[data-commentid=\"").concat(targetRowId, "\"], \n                 tr[data-groupid=\"").concat(targetRowId, "\"]"));
          return (targetRow === null || targetRow === void 0 ? void 0 : targetRow.dataset.groupid) || null;
        } else {
          var allGroupRows = document.querySelectorAll('tr.group.group_row');
          var lastGroupRow = allGroupRows[allGroupRows.length - 1];
          return (lastGroupRow === null || lastGroupRow === void 0 ? void 0 : lastGroupRow.dataset.groupid) || null;
        }
      },
      updatePOSNumbers: function updatePOSNumbers() {
        var _this18 = this;
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
            if (_this18.groups[lastGroupId]) {
              _this18.groups[lastGroupId].pos = groupPos;
            }
          } else if (row.classList.contains('item_row')) {
            itemCountInGroup++;
            var itemPos = "".concat(currentGroupPos.toString().padStart(2, '0'), ".").concat(itemCountInGroup.toString().padStart(2, '0'));
            row.querySelector('.pos-inner').textContent = itemPos;
            var itemId = row.dataset.itemid;
            if (_this18.items[itemId]) {
              _this18.items[itemId].pos = itemPos;
            }
          } else if (row.classList.contains('item_comment')) {
            itemCountInGroup++;
            var _itemPos = "".concat(currentGroupPos.toString().padStart(2, '0'), ".").concat(itemCountInGroup.toString().padStart(2, '0'));
            row.querySelector('.pos-inner').textContent = _itemPos;
            var _itemId = row.dataset.itemid || row.dataset.commentid;
            if (_this18.comments[_itemId]) {
              _this18.comments[_itemId].pos = _itemPos;
            }
          }
        });
      }
    }, _defineProperty(_defineProperty(_defineProperty(_defineProperty(_defineProperty(_defineProperty(_defineProperty(_defineProperty(_defineProperty(_defineProperty(_ref5, "getCurrentGroupId", function getCurrentGroupId() {
      // Get the last group from sorted groups
      var groups = this.getSortedGroups();
      return groups.length > 0 ? groups[groups.length - 1].id : null;
    }), "getSortedGroups", function getSortedGroups() {
      var _this19 = this;
      // Get all groups from newItems
      var groups = Object.values(this.newItems).filter(function (item) {
        return item.type === 'group';
      }).sort(function (a, b) {
        return _this19.comparePOS(a.pos, b.pos);
      });
      return groups;
    }), "getSortedItemsForGroup", function getSortedItemsForGroup(groupId) {
      var _this20 = this;
      if (!groupId) return [];

      // Get all items and comments for this group from newItems
      return Object.values(this.newItems).filter(function (item) {
        return item.groupId === groupId && (item.type === 'item' || item.type === 'comment');
      }).sort(function (a, b) {
        return _this20.comparePOS(a.pos, b.pos);
      });
    }), "comparePOS", function comparePOS(posA, posB) {
      if (!posA || !posB) return 0;
      var _String$split = String(posA).split('.'),
        _String$split2 = _slicedToArray(_String$split, 2),
        groupA = _String$split2[0],
        _String$split2$ = _String$split2[1],
        itemA = _String$split2$ === void 0 ? "0" : _String$split2$;
      var _String$split3 = String(posB).split('.'),
        _String$split4 = _slicedToArray(_String$split3, 2),
        groupB = _String$split4[0],
        _String$split4$ = _String$split4[1],
        itemB = _String$split4$ === void 0 ? "0" : _String$split4$;
      var groupDiff = parseInt(groupA) - parseInt(groupB);
      if (groupDiff !== 0) return groupDiff;
      return parseInt(itemA) - parseInt(itemB);
    }), "duplicateCardColumn", function duplicateCardColumn(quoteId) {
      alert('Action for duplicateCardColumn' + quoteId);
    }), "deleteCardColumn", function deleteCardColumn(quoteId) {
      var _this21 = this;
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
      }).then(function (result) {
        if (result.isConfirmed) {
          try {
            var elements = document.querySelectorAll("[data-cardquoteid=\"".concat(quoteId, "\"]"));
            elements.forEach(function (el) {
              return el.remove();
            });
            _this21.calculateTotals();
            Swal.fire({
              title: 'Deleted!',
              text: "The Column has been deleted successfully",
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
    }), "toggleDescription", function toggleDescription(index, event) {
      event.stopPropagation();
      if (!this.expandedRows) {
        this.expandedRows = {};
      }
      this.expandedRows[index] = !this.expandedRows[index];
      var parentRow = event.target.closest('tr');
      var childRow = document.querySelector("tr.item_child[data-id=\"".concat(index, "\"]"));
      if (childRow) {
        childRow.style.display = this.expandedRows[index] ? 'table-row' : 'none';
        var icon = parentRow.querySelector('.desc_toggle');
        if (icon) {
          icon.classList.toggle('fa-caret-right');
          icon.classList.toggle('fa-caret-down');
        }
      }
    }), "isExpanded", function isExpanded(index) {
      return this.expandedRows[index] || false;
    }), "initializeContextMenu", function initializeContextMenu() {
      var _this22 = this;
      document.querySelector('#estimation-edit-table').addEventListener('contextmenu', function (e) {
        var row = e.target.closest('tr.item_row, tr.group_row, tr.item_comment');
        if (!row) return;
        e.preventDefault();
        var viewportWidth = window.innerWidth;
        var viewportHeight = window.innerHeight;
        var x = e.clientX;
        var y = e.clientY;
        if (x + 160 > viewportWidth) x = viewportWidth - 160;
        if (y + 160 > viewportHeight) y = viewportHeight - 160;
        _this22.contextMenu = {
          show: true,
          x: x,
          y: y,
          selectedRowId: row.dataset.id || row.dataset.itemid || row.dataset.commentid || row.dataset.groupid
        };
      });
    }), "moveRow", function moveRow(direction, rowId) {
      var row = document.querySelector("tr[data-id=\"".concat(rowId, "\"], \n                                         tr[data-itemid=\"").concat(rowId, "\"], \n                                         tr[data-commentid=\"").concat(rowId, "\"], \n                                         tr[data-groupid=\"").concat(rowId, "\"]"));
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
      this.updatePOSNumbers();
      this.calculateTotals();
      this.contextMenu.show = false;
    }), _defineProperty(_defineProperty(_defineProperty(_defineProperty(_defineProperty(_defineProperty(_defineProperty(_defineProperty(_defineProperty(_defineProperty(_ref5, "duplicateRow", function duplicateRow(rowId) {
      var _this23 = this;
      var originalRow = document.querySelector("tr[data-id=\"".concat(rowId, "\"], \n                                                 tr[data-itemid=\"").concat(rowId, "\"], \n                                                 tr[data-commentid=\"").concat(rowId, "\"], \n                                                 tr[data-groupid=\"").concat(rowId, "\"]"));
      if (!originalRow) return;
      var timestamp = Date.now();
      var isGroup = originalRow.classList.contains('group_row');
      var isComment = originalRow.classList.contains('item_comment');
      var groupId = isGroup ? null : originalRow.dataset.groupid;
      if (isGroup) {
        var groupName = originalRow.querySelector('.grouptitle-input').value;
        var newItem = {
          id: timestamp,
          type: 'group',
          name: "".concat(groupName, " - copy"),
          total: 0,
          expanded: false
        };
        this.items[timestamp] = newItem;
        this.groups[timestamp] = {
          id: timestamp,
          pos: '',
          name: "".concat(groupName, " - copy"),
          total: 0,
          itemCount: 0
        };
      } else if (isComment) {
        var _newItem = {
          id: timestamp,
          type: 'comment',
          groupId: groupId,
          content: originalRow.querySelector('.item-description').value,
          expanded: false
        };
        this.items[timestamp] = _newItem;
      } else {
        var _originalRow$querySel, _originalRow$querySel2, _originalRow$querySel3, _originalRow$querySel4;
        var _newItem2 = {
          id: timestamp,
          type: originalRow.classList.contains('item_comment') ? 'comment' : 'item',
          groupId: groupId,
          name: ((_originalRow$querySel = originalRow.querySelector('.item-name')) === null || _originalRow$querySel === void 0 ? void 0 : _originalRow$querySel.value) + ' - copy',
          quantity: this.parseNumber(((_originalRow$querySel2 = originalRow.querySelector('.item-quantity')) === null || _originalRow$querySel2 === void 0 ? void 0 : _originalRow$querySel2.value) || '0'),
          unit: ((_originalRow$querySel3 = originalRow.querySelector('.item-unit')) === null || _originalRow$querySel3 === void 0 ? void 0 : _originalRow$querySel3.value) || '',
          optional: originalRow.querySelector('.item-optional').checked ? 1 : 0,
          price: this.parseNumber(((_originalRow$querySel4 = originalRow.querySelector('.item-price')) === null || _originalRow$querySel4 === void 0 ? void 0 : _originalRow$querySel4.value) || '0'),
          expanded: false
        };
        this.items[timestamp] = _newItem2;
        if (this.groups[groupId]) {
          this.groups[groupId].itemCount++;
        }
      }
      this.$nextTick(function () {
        var newRow = document.querySelector("tr[data-id=\"".concat(timestamp, "\"], tr[data-itemid=\"").concat(timestamp, "\"], tr[data-commentid=\"").concat(timestamp, "\"]"));
        if (newRow && originalRow.nextSibling) {
          originalRow.parentNode.insertBefore(newRow, originalRow.nextSibling);
        }
        _this23.updatePOSNumbers();
        _this23.calculateTotals();
        _this23.initializeContextMenu();
      });
      this.contextMenu.show = false;
    }), "removeRowFromMenu", function removeRowFromMenu(rowId) {
      var _this24 = this;
      Swal.fire({
        title: 'Confirmation Delete',
        text: 'Really! You want to remove this item? You can\'t undo',
        showCancelButton: true,
        confirmButtonText: 'Yes, Delete it',
        cancelButtonText: "No, cancel"
      }).then(function (result) {
        if (result.isConfirmed) {
          var estimationId = _this24.getFomrData().id;
          var itemIds = [];
          var comments = [];
          var groupIds = [];
          var row = document.querySelector("tr[data-id=\"".concat(rowId, "\"], tr[data-itemid=\"").concat(rowId, "\"], tr[data-groupid=\"").concat(rowId, "\"]"));
          if (!row) return;
          if (row.classList.contains('group_row')) {
            groupIds.push(row.dataset.groupid);
            delete _this24.groups[row.dataset.groupid];
            delete _this24.newItems[row.dataset.groupid];
          } else {
            itemIds.push(row.dataset.itemid);
            delete _this24.items[row.dataset.itemid];
            delete _this24.newItems[row.dataset.itemid];
            comments.push(row.dataset.commentid);
            delete _this24.comments[row.dataset.commentid];
            delete _this24.newItems[row.dataset.commentid];
          }
          $.ajax({
            url: route('estimation.destroy', estimationId),
            method: 'DELETE',
            data: {
              estimationId: estimationId,
              items: itemIds.concat(comments),
              groups: groupIds
            },
            success: function success(response) {
              console.log(response);
            }
          });
          row.remove();
          _this24.updatePOSNumbers();
          _this24.calculateTotals();
          document.querySelector('.SelectAllCheckbox').checked = false;
        }
      });
      this.contextMenu.show = false;
    }), "handleGroupSelection", function handleGroupSelection(event, groupId) {
      var checked = event.target.checked;
      var groupRow = event.target.closest('tr.group_row');
      if (!groupRow) return;
      var currentRow = groupRow.nextElementSibling;
      while (currentRow && !currentRow.classList.contains('group_row')) {
        var checkbox = currentRow.querySelector('.item_selection');
        if (checkbox) {
          checkbox.checked = checked;
        }
        currentRow = currentRow.nextElementSibling;
      }
    }), "checkboxAll", function checkboxAll(value) {
      document.querySelectorAll('.item_selection').forEach(function (checkbox) {
        checkbox.checked = value;
      });
    }), "initializeColumnVisibility", function initializeColumnVisibility() {
      var _this25 = this;
      document.querySelectorAll('.column-toggle').forEach(function (checkbox) {
        checkbox.addEventListener('change', function (e) {
          var columnClass = e.target.dataset.column;
          var quoteId = e.target.dataset.quoteid;
          if (columnClass === 'quote_th' && quoteId) {
            _this25.columnVisibility[columnClass] = e.target.checked;
            _this25.applyColumnVisibility(quoteId);
          } else {
            _this25.columnVisibility[columnClass] = e.target.checked;
            _this25.applyColumnVisibility();
          }
        });
      });
    }), "applyColumnVisibility", function applyColumnVisibility() {
      var quoteId = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
      Object.entries(this.columnVisibility).forEach(function (_ref3) {
        var _ref4 = _slicedToArray(_ref3, 2),
          columnClass = _ref4[0],
          isVisible = _ref4[1];
        if (columnClass === 'quote_th' && quoteId) {
          var elements = document.querySelectorAll(".quote_th".concat(quoteId, ", ") + "[data-cardquoteid=\"".concat(quoteId, "\"]"));
          elements.forEach(function (el) {
            el.style.display = isVisible ? '' : 'none';
          });
        } else {
          var _elements = document.querySelectorAll(".".concat(columnClass));
          _elements.forEach(function (el) {
            if (el.closest('td, th')) {
              el.closest('td, th').style.display = isVisible ? '' : 'none';
            }
          });
        }
      });
    }), "toggleColumn", function toggleColumn(columnClass) {
      var quoteId = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;
      this.columnVisibility[columnClass] = !this.columnVisibility[columnClass];
      this.applyColumnVisibility(quoteId);
      var selector = quoteId ? ".column-toggle[data-column=\"".concat(columnClass, "\"][data-quote=\"").concat(quoteId, "\"]") : ".column-toggle[data-column=\"".concat(columnClass, "\"]");
      var checkbox = document.querySelector(selector);
      if (checkbox) {
        checkbox.checked = this.columnVisibility[columnClass];
      }
    }), "formatTimeAgo", function formatTimeAgo(timestamp) {
      if (!timestamp) return 'Never saved';
      var now = Date.now();
      var diff = Math.floor((now - timestamp) / 1000);
      if (diff < 60) return 'Just now';
      if (diff < 3600) {
        var minutes = Math.floor(diff / 60);
        return "".concat(minutes, " minute").concat(minutes > 1 ? 's' : '', " ago");
      }
      if (diff < 86400) {
        var hours = Math.floor(diff / 3600);
        return "".concat(hours, " hour").concat(hours > 1 ? 's' : '', " ago");
      }
      var days = Math.floor(diff / 86400);
      return "".concat(days, " day").concat(days > 1 ? 's' : '', " ago");
    }), "startTimeAgoUpdates", function startTimeAgoUpdates() {
      var _this26 = this;
      if (this.timeAgoInterval) {
        clearInterval(this.timeAgoInterval);
      }
      this.timeAgoInterval = setInterval(function () {
        if (_this26.lastSaveTimestamp) {
          _this26.lastSaveText = _this26.formatTimeAgo(_this26.lastSaveTimestamp);
        }
      }, 60000);
    }), "saveTableData", function saveTableData() {
      var _this27 = this;
      if (!this.hasUnsavedChanges || !document.querySelector('#quote_form')) return;
      var columns = {};
      var cardQuoteIds = _toConsumableArray(new Set(Array.from(document.querySelectorAll('[data-cardquoteid]')).map(function (el) {
        return el.dataset.cardquoteid;
      })));
      cardQuoteIds.forEach(function (cardQuoteId) {
        var _document$querySelect, _document$querySelect2, _document$querySelect3, _document$querySelect4, _document$querySelect5, _document$querySelect6, _document$querySelect7;
        columns[cardQuoteId] = {
          settings: {
            markup: _this27.parseNumber(((_document$querySelect = document.querySelector("input[name=\"item[".concat(cardQuoteId, "][markup]\"]"))) === null || _document$querySelect === void 0 ? void 0 : _document$querySelect.value) || '0'),
            cashDiscount: _this27.parseNumber(((_document$querySelect2 = document.querySelector("input[name=\"item[".concat(cardQuoteId, "][discount]\"]"))) === null || _document$querySelect2 === void 0 ? void 0 : _document$querySelect2.value) || '0'),
            vat: _this27.parseNumber(((_document$querySelect3 = document.querySelector("select[name=\"item[".concat(cardQuoteId, "][tax]\"]"))) === null || _document$querySelect3 === void 0 ? void 0 : _document$querySelect3.value) || '0')
          },
          totals: {
            netIncludingDiscount: _this27.parseNumber((_document$querySelect4 = document.querySelector("th[data-cardquoteid=\"".concat(cardQuoteId, "\"].total-net-discount"))) === null || _document$querySelect4 === void 0 ? void 0 : _document$querySelect4.textContent),
            grossIncludingDiscount: _this27.parseNumber((_document$querySelect5 = document.querySelector("th[data-cardquoteid=\"".concat(cardQuoteId, "\"].total-gross-discount"))) === null || _document$querySelect5 === void 0 ? void 0 : _document$querySelect5.textContent),
            net: _this27.parseNumber((_document$querySelect6 = document.querySelector("th[data-cardquoteid=\"".concat(cardQuoteId, "\"].total-net"))) === null || _document$querySelect6 === void 0 ? void 0 : _document$querySelect6.textContent),
            gross: _this27.parseNumber((_document$querySelect7 = document.querySelector("th[data-cardquoteid=\"".concat(cardQuoteId, "\"] .total-gross-total"))) === null || _document$querySelect7 === void 0 ? void 0 : _document$querySelect7.textContent)
          }
        };
      });
      var data = {
        cards: columns,
        form: this.getFomrData(),
        items: this.items,
        comments: this.comments,
        groups: this.groups,
        newItems: this.newItems
      };
      $.ajax({
        url: route('estimation.update', data.form.id),
        method: 'PUT',
        data: data,
        beforeSend: function beforeSend() {
          _this27.lastSaveText = 'is running...';
          $('#save-button').html('Saving... <i class="fa fa-arrow-right-rotate rotate"></i>');
        },
        success: function success(idMappings) {
          // Helper function to update IDs and DOM elements
          var updateEntities = function updateEntities(oldId, newId) {
            // Find item in newItems and update its ID
            var itemKey = Object.keys(_this27.newItems).find(function (key) {
              return _this27.newItems[key].id.toString() === oldId;
            });
            if (itemKey) {
              _this27.newItems[newId] = _objectSpread(_objectSpread({}, _this27.newItems[itemKey]), {}, {
                id: newId
              });
              delete _this27.newItems[itemKey];
            }

            // Update DOM - check all possible types
            ['item', 'comment', 'group'].forEach(function (type) {
              var row = document.querySelector("tr[data-".concat(type, "id=\"").concat(oldId, "\"]"));
              if (row) {
                row.dataset["".concat(type, "id")] = newId;
                row.dataset.id = newId;

                // Update input names
                row.querySelectorAll("[name*=\"[".concat(oldId, "]\"]")).forEach(function (input) {
                  input.name = input.name.replace("[".concat(oldId, "]"), "[".concat(newId, "]"));
                });
              }
            });
          };

          // Update all entities with new IDs
          // Object.entries(idMappings).forEach(([oldId, newId]) => {
          //     updateEntities(oldId, newId);
          // });

          // Update UI state
          _this27.lastSaveTimestamp = Date.now();
          _this27.lastSaveText = _this27.formatTimeAgo(_this27.lastSaveTimestamp);
          toastrs("success", "Estimation data has been saved.");
          $('#save-button').html("Saved last changed.");
          _this27.hasUnsavedChanges = false;
          _this27.startTimeAgoUpdates();
        },
        error: function error(_error) {
          console.error('Error saving data:', _error);
          toastrs("error", "Failed to save changes." + _error.error);
          _this27.hasUnsavedChanges = true;
          _this27.lastSaveText = 'is failed';
        }
      });
    }), _defineProperty(_ref5, "getFomrData", function getFomrData() {
      var form = this.$el.closest('form');
      if (!form) {
        console.warn('Form not found');
        return {};
      }
      var formData = new FormData(form);
      return Object.fromEntries(formData);
    });
  });
});
/******/ })()
;