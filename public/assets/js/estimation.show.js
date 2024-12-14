/******/ (() => { // webpackBootstrap
/*!*******************************************************************!*\
  !*** ./Modules/Estimation/Resources/assets/js/estimation.show.js ***!
  \*******************************************************************/
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
$(document).ready(function () {
  var _EstimationTable;
  var EstimationTable = (_EstimationTable = {
    estimation: $('.estimation-show'),
    templates: {
      item: $('#add-item-template').html(),
      group: $('#add-group-template').html(),
      comment: $('#add-comment-template').html()
    },
    originalPrices: new Map(),
    init: function init() {
      if (!this.validateTemplates()) return;
      this.bindEvents();
      this.initializeSortable();
      this.updateAllCalculations();
    },
    validateTemplates: function validateTemplates() {
      return Object.values(this.templates).every(function (template) {
        return template;
      });
    },
    bindEvents: function bindEvents() {
      var _this = this;
      this.estimation.find('button[data-actioninsert]').on('click', function (event) {
        event.preventDefault();
        var button = $(event.currentTarget);
        var target = button.data('actioninsert');
        if (target) _this.addItems(target);
      });
      this.estimation.on('click', '.desc_toggle', this.toggleDescription);
      this.estimation.on('click', '.grp-dt-control', this.toggleGroup);
      this.estimation.on('blur', '.item-quantity, .item-price', function (event) {
        var $target = $(event.currentTarget);
        _this.formatInput($target);
        _this.updateAllCalculations();
      });
      this.estimation.on('change', '.item-optional', function () {
        _this.updateAllCalculations();
      });
      this.estimation.on('change', 'select[name^="item"][name$="[tax]"]', function () {
        _this.updateAllCalculations();
      });
      this.estimation.on('blur', 'input[name^="item"][name$="[discount]"]', function (event) {
        var $input = $(event.target);
        var value = _this.parseGermanDecimal($input.val());

        // Format the number
        $input.val(_this.formatGermanDecimal(value));

        // Apply styling for negative values
        if (value < 0) {
          $input.css({
            'background-color': '#ffebee',
            'color': '#d32f2f'
          });
        } else {
          $input.css({
            'background-color': '',
            'color': ''
          });
        }
        _this.updateAllCalculations();
      });

      // Enhanced markup input handler
      this.estimation.on('blur', 'input[name^="item"][name$="[markup]"]', function (event) {
        var $input = $(event.target);
        var value = _this.parseGermanDecimal($input.val());
        var cardQuoteId = $input.attr('name').match(/\[(\d+)\]/)[1];

        // Format the number
        $input.val(_this.formatGermanDecimal(value));

        // Apply styling for negative values
        if (value < 0) {
          $input.css({
            'background-color': '#ffebee',
            'color': '#d32f2f'
          });
        } else {
          $input.css({
            'background-color': '',
            'color': ''
          });
        }
        _this.applyMarkupToSinglePrices(cardQuoteId, value);
        _this.updateAllCalculations();
      });
    },
    storeOriginalPrices: function storeOriginalPrices() {
      var _this2 = this;
      // Clear existing stored prices
      this.originalPrices.clear();
      $('.item-price').each(function (_, element) {
        var $price = $(element);
        var cardQuoteId = $price.data('cardquotesingleprice');
        var itemId = $price.closest('tr').data('itemid');
        var key = "".concat(cardQuoteId, "-").concat(itemId);

        // Only store if not already stored
        if (!_this2.originalPrices.has(key)) {
          var originalPrice = _this2.parseGermanDecimal($price.val());
          _this2.originalPrices.set(key, originalPrice);
        }
      });
    },
    addItems: function addItems(type) {
      if (!this.templates[type]) return;
      var timestamp = Date.now();
      var template = this.templates[type];
      if (type === 'group') {
        var newGroup = template.replace(/{TEMPLATE_ID}/g, timestamp).replace(/{TEMPLATE_POS}/g, this.getNextGroupPosition());
        $('#estimation-items').append(newGroup);
        this.updatePOSNumbers();
        this.updateAllCalculations();
        return;
      }
      var currentGroupId = this.getCurrentGroupId();
      if (!currentGroupId) {
        var _toastr;
        (_toastr = toastr) === null || _toastr === void 0 || _toastr.error('Please create a group first');
        return;
      }
      var newItem = template.replace(/{TEMPLATE_ID}/g, timestamp).replace(/{TEMPLATE_GROUP_ID}/g, currentGroupId).replace(/{TEMPLATE_POS}/g, this.getNextItemPosition(currentGroupId));
      var lastGroupItem = $("tr[data-groupid=\"".concat(currentGroupId, "\"]:last"));
      lastGroupItem.length ? lastGroupItem.after(newItem) : $("tr[data-groupid=\"".concat(currentGroupId, "\"]")).after(newItem);
      this.updatePOSNumbers();
      this.updateAllCalculations();
    },
    getCurrentGroupId: function getCurrentGroupId() {
      var lastGroup = $('.group_row').last();
      return lastGroup.length ? lastGroup.data('groupid') : Date.now() + 10;
    },
    getNextGroupPosition: function getNextGroupPosition() {
      var maxPos = 0;
      $('.group_row').each(function () {
        var pos = parseInt($(this).find('.grouppos').text()) || 0;
        maxPos = Math.max(maxPos, pos);
      });
      return (maxPos + 1).toString().padStart(2, '0');
    },
    getNextItemPosition: function getNextItemPosition(groupId) {
      var maxPos = 0;
      $("tr[data-groupid=\"".concat(groupId, "\"]")).not('.group_row').each(function () {
        var pos = parseInt($(this).find('.pos-inner').text().split('.')[1]) || 0;
        maxPos = Math.max(maxPos, pos);
      });
      var groupPos = $("tr[data-groupid=\"".concat(groupId, "\"].group_row .grouppos")).text();
      return "".concat(groupPos, ".").concat((maxPos + 1).toString().padStart(2, '0'));
    },
    updatePOSNumbers: function updatePOSNumbers() {
      var currentGroupPos = 0;
      var itemCountInGroup = 0;
      $('.group_row, .item_row, .item_comment').each(function () {
        if ($(this).hasClass('group_row')) {
          currentGroupPos++;
          itemCountInGroup = 0;
          $(this).find('.grouppos').text(currentGroupPos.toString().padStart(2, '0'));
        } else {
          itemCountInGroup++;
          $(this).find('.pos-inner').text("".concat(currentGroupPos.toString().padStart(2, '0'), ".").concat(itemCountInGroup.toString().padStart(2, '0')));
        }
      });
    },
    initializeSortable: function initializeSortable() {
      var _this3 = this;
      $("#estimation-items").sortable({
        items: 'tr.group_row, tr.item_row, tr.item_comment',
        handle: '.reorder-item, .reorder_group_btn',
        axis: 'y',
        helper: function helper(e, tr) {
          var item = $(e.target).closest('tr');
          var helperContainer = $('<div class="drag-helper"></div>');
          if (item.hasClass('group_row')) {
            var groupId = item.data('groupid');
            var clonedGroup = item.clone();
            helperContainer.append(clonedGroup);
            $("tr.item_row[data-groupid=\"".concat(groupId, "\"], tr.item_comment[data-groupid=\"").concat(groupId, "\"]")).each(function () {
              helperContainer.append($(this).clone());
            });
          } else {
            helperContainer.append(item.clone());
          }
          var originalCells = item.children();
          helperContainer.find('td').each(function (index) {
            $(this).width(originalCells.eq(index).outerWidth());
          });
          helperContainer.width(item.closest('table').width());
          return helperContainer;
        },
        start: function start(e, ui) {
          var item = ui.item;
          if (item.hasClass('group_row')) {
            var groupId = item.data('groupid');
            $("tr.item_row[data-groupid=\"".concat(groupId, "\"], tr.item_comment[data-groupid=\"").concat(groupId, "\"]")).hide();
          }
        },
        stop: function stop(e, ui) {
          var item = ui.item;
          if (item.hasClass('group_row')) {
            var groupId = item.data('groupid');
            var groupItems = $("tr.item_row[data-groupid=\"".concat(groupId, "\"], tr.item_comment[data-groupid=\"").concat(groupId, "\"]"));
            item.after(groupItems);
            groupItems.show();
          } else if (item.hasClass('item_row')) {
            // Get next and previous elements
            var next = item.next();
            var prev = item.prev();

            // If next row is a description row but not for this item, move this item elsewhere
            if (next.hasClass('item_child') && next.data('itemid') !== item.data('itemid')) {
              // Find proper position
              var prevItem = item.prevAll('.item_row').first();
              if (prevItem.length) {
                prevItem.after(item);
              } else {
                var prevGroup = item.prevAll('.group_row').first();
                if (prevGroup.length) {
                  prevGroup.after(item);
                }
              }
            }

            // Always keep description row with its item
            var itemId = item.data('itemid');
            var descRow = $(".item_child[data-itemid=\"".concat(itemId, "\"]"));
            if (descRow.length) {
              item.after(descRow);
            }
          }
          _this3.handleItemMove(ui.item);
          _this3.updatePOSNumbers();
          _this3.updateAllCalculations();
        },
        change: function change(e, ui) {
          var item = ui.item;
          var placeholder = ui.placeholder;
          if (item.hasClass('item_row')) {
            var next = placeholder.next();
            if (next.hasClass('item_child') && next.data('itemid') !== item.data('itemid')) {
              placeholder.insertAfter(next);
            }
          }
        }
      });
    },
    handleItemMove: function handleItemMove(movedItem) {
      if (movedItem.hasClass('item_row') || movedItem.hasClass('item_comment')) {
        var prevGroup = movedItem.prevAll('.group_row').first();
        if (prevGroup.length) {
          var newGroupId = prevGroup.data('groupid');
          var itemId = movedItem.data('itemid');
          var oldGroupId = movedItem.data('groupid');
          movedItem.attr('data-groupid', newGroupId);

          // For item rows, also move description
          if (movedItem.hasClass('item_row')) {
            $(".item_child[data-itemid=\"".concat(itemId, "\"]")).attr('data-groupid', newGroupId);
          }
          if (this.templates[itemId]) {
            this.templates[itemId].groupId = newGroupId;
          }
        }
      }
    },
    toggleGroup: function toggleGroup(event) {
      var icon = $(event.currentTarget);
      var row = icon.closest('tr.group_row');
      var groupId = row.data('groupid');

      // First toggle all main items
      var mainItems = $("tr.item_row[data-groupid=\"".concat(groupId, "\"], tr.item_comment[data-groupid=\"").concat(groupId, "\"]"));
      mainItems.toggle();

      // Handle description rows based on their item's toggle state
      mainItems.each(function () {
        var itemId = $(this).data('itemid');
        var descRow = $(".item_child[data-itemid=\"".concat(itemId, "\"]"));
        var itemToggleIcon = $(this).find('.desc_toggle');

        // Only show description if parent is visible AND toggle is expanded
        if ($(this).is(':visible') && itemToggleIcon.hasClass('fa-caret-down')) {
          descRow.show();
        } else {
          descRow.hide();
        }
      });
      icon.toggleClass('fa-caret-right fa-caret-down');
    },
    toggleDescription: function toggleDescription(event) {
      var icon = $(event.currentTarget);
      var row = icon.closest('tr');
      var parentID = row.data('itemid');
      var descRow = $(".item_child.tr_child_description[data-itemID=\"".concat(parentID, "\"]"));
      descRow.toggle();
      icon.toggleClass('fa-caret-right fa-caret-down');
    },
    formatInput: function formatInput(target) {
      var $target = $(target);
      if ($target.hasClass('item-quantity')) {
        var formattedQuantity = this.formatGermanDecimal(this.parseGermanDecimal($target.val()));
        $target.val(formattedQuantity);
      } else if ($target.hasClass('item-price')) {
        var formattedPrice = this.formatGermanCurrency(this.parseGermanDecimal($target.val()));
        $target.val(formattedPrice);
      }
    },
    parseGermanDecimal: function parseGermanDecimal(value) {
      if (typeof value === 'number') return value;
      return parseFloat(String(value).replace(/[^\d,-]/g, '').replace(',', '.')) || 0;
    },
    formatGermanDecimal: function formatGermanDecimal(value) {
      return new Intl.NumberFormat('de-DE', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      }).format(value);
    },
    formatGermanCurrency: function formatGermanCurrency(value) {
      return new Intl.NumberFormat('de-DE', {
        style: 'currency',
        currency: 'EUR'
      }).format(value);
    }
  }, _defineProperty(_defineProperty(_defineProperty(_defineProperty(_defineProperty(_defineProperty(_defineProperty(_defineProperty(_defineProperty(_defineProperty(_EstimationTable, "storeOriginalPrices", function storeOriginalPrices() {
    var _this4 = this;
    $('.item-price').each(function (_, element) {
      var $price = $(element);
      var id = $price.data('cardquotesingleprice');
      var originalPrice = _this4.parseGermanDecimal($price.val());
      _this4.originalPrices.set("".concat(id, "-").concat($price.closest('tr').data('itemid')), originalPrice);
    });
  }), "applyMarkupToSinglePrices", function applyMarkupToSinglePrices(cardQuoteId, markup) {
    var _this5 = this;
    // If originalPrices is empty, store them first
    if (this.originalPrices.size === 0) {
      this.storeOriginalPrices();
    }
    $(".item-price[data-cardquotesingleprice=\"".concat(cardQuoteId, "\"]")).each(function (_, element) {
      var $price = $(element);
      var itemId = $price.closest('tr').data('itemid');
      var key = "".concat(cardQuoteId, "-").concat(itemId);
      var originalPrice = _this5.originalPrices.get(key);
      if (originalPrice !== undefined) {
        var newPrice = markup > 0 ? originalPrice * (1 + markup / 100) : originalPrice;
        $price.val(_this5.formatGermanCurrency(newPrice));
      }
    });
  }), "calculateItemTotal", function calculateItemTotal(itemRow, cardQuoteId) {
    if (itemRow.find('.item-optional').is(':checked')) return '-';
    var quantity = this.parseGermanDecimal(itemRow.find('.item-quantity').val());
    var currentPrice = this.parseGermanDecimal(itemRow.find(".item-price[data-cardquotesingleprice=\"".concat(cardQuoteId, "\"]")).val());
    var total = quantity * currentPrice;
    return total === 0 ? '-' : this.formatGermanCurrency(total);
  }), "calculateGroupTotal", function calculateGroupTotal(groupRow, cardQuoteId) {
    var _this6 = this;
    var total = 0;
    var groupId = groupRow.data('groupid');
    $(".item_row[data-groupid=\"".concat(groupId, "\"]")).each(function (_, item) {
      var $item = $(item);
      if (!$item.find('.item-optional').is(':checked')) {
        var quantity = _this6.parseGermanDecimal($item.find('.item-quantity').val());
        var basePrice = _this6.parseGermanDecimal($item.find(".item-price[data-cardquotesingleprice=\"".concat(cardQuoteId, "\"]")).val());
        var markup = _this6.parseGermanDecimal($("input[name=\"item[".concat(cardQuoteId, "][markup]\"]")).val()) || 0;

        // Apply markup to single price
        var priceWithMarkup = basePrice * (1 + markup / 100);
        total += quantity * priceWithMarkup;
      }
    });
    return total === 0 ? '-' : this.formatGermanCurrency(total);
  }), "calculateTotals", function calculateTotals(cardQuoteId) {
    var _this7 = this;
    var netTotal = 0;
    $(".group_row [data-cardquotegrouptotalprice=\"".concat(cardQuoteId, "\"]")).each(function (_, element) {
      var groupTotal = _this7.parseGermanDecimal($(element).text());
      if (!isNaN(groupTotal)) netTotal += groupTotal;
    });
    var cashDiscount = this.parseGermanDecimal($("input[name=\"item[".concat(cardQuoteId, "][discount]\"]")).val()) || 0;
    var vatRate = parseFloat($("select[name=\"item[".concat(cardQuoteId, "][tax]\"]")).val()) || 0;
    var netAfterDiscount = netTotal * (1 - cashDiscount / 100);
    var vatAmount = netTotal * (vatRate / 100);
    var grossTotal = netTotal + vatAmount;
    var grossAfterDiscount = netAfterDiscount + netAfterDiscount * vatRate / 100;

    // Update all display values
    $(".total-net[data-cardquoteid=\"".concat(cardQuoteId, "\"]")).text(this.formatGermanCurrency(netTotal));
    $(".total-net-discount[data-cardquoteid=\"".concat(cardQuoteId, "\"]")).text(this.formatGermanCurrency(netAfterDiscount));
    $(".total-gross-discount[data-cardquoteid=\"".concat(cardQuoteId, "\"]")).text(this.formatGermanCurrency(grossAfterDiscount));
    $(".total-gross-total[data-cardquoteid=\"".concat(cardQuoteId, "\"]")).text(this.formatGermanCurrency(grossTotal));

    // Update additional gross total display
    $(".totalnr.total-gross-total[data-cardquoteid=\"".concat(cardQuoteId, "\"]")).text(this.formatGermanCurrency(grossTotal));
  }), "updateDisplayValues", function updateDisplayValues(cardQuoteId, values) {
    var formatCurrency = this.formatGermanCurrency.bind(this);
    var net = values.net,
      netAfterDiscount = values.netAfterDiscount,
      grossAfterDiscount = values.grossAfterDiscount,
      vatRate = values.vatRate;
    $(".total-net[data-cardquoteid=\"".concat(cardQuoteId, "\"]")).text(formatCurrency(net));
    $(".total-net-discount[data-cardquoteid=\"".concat(cardQuoteId, "\"]")).text(formatCurrency(netAfterDiscount));
    $(".total-gross-discount[data-cardquoteid=\"".concat(cardQuoteId, "\"]")).text(formatCurrency(grossAfterDiscount));

    // Update VAT details
    var $vatSelect = $("select[name=\"item[".concat(cardQuoteId, "][tax]\"]"));
    $vatSelect.val(vatRate.toString());

    // Update gross amount with VAT
    var grossWithVat = net * (1 + vatRate / 100);
    $(".total-gross[data-cardquoteid=\"".concat(cardQuoteId, "\"]")).text(formatCurrency(grossWithVat));
  }), "applySinglePriceMarkup", function applySinglePriceMarkup(price, markup) {
    return price * (1 + markup / 100);
  }), "updateItemPrices", function updateItemPrices(cardQuoteId) {
    var _this8 = this;
    var markup = this.parseGermanDecimal($("input[name=\"item[".concat(cardQuoteId, "][markup]\"]")).val()) || 0;

    // Update all single prices with markup
    $(".item_row").each(function (_, row) {
      var $row = $(row);
      var $priceInput = $row.find(".item-price[data-cardquotesingleprice=\"".concat(cardQuoteId, "\"]"));
      var basePrice = _this8.parseGermanDecimal($priceInput.val());
      var priceWithMarkup = _this8.applySinglePriceMarkup(basePrice, markup);
      $priceInput.val(_this8.formatGermanCurrency(priceWithMarkup));
    });
  }), "updateTotalDisplay", function updateTotalDisplay(cardQuoteId, totals) {
    // Update Net incl. Discount
    $(".total-net-discount[data-cardquoteid=\"".concat(cardQuoteId, "\"]")).text(this.formatGermanCurrency(totals.netIncDiscount));

    // Update Gross incl. Discount (with VAT)
    $(".total-gross-discount[data-cardquoteid=\"".concat(cardQuoteId, "\"]")).text(this.formatGermanCurrency(totals.grossIncDiscount));

    // Update Net
    $(".total-net[data-cardquoteid=\"".concat(cardQuoteId, "\"]")).text(this.formatGermanCurrency(totals.net));

    // Update both Gross VAT displays
    $(".total-gross-total[data-cardquoteid=\"".concat(cardQuoteId, "\"]")).text(this.formatGermanCurrency(totals.gross));

    // Update VAT display on both sides
    this.updateVatDisplay(cardQuoteId, totals.vatRate);
  }), "updateVatDisplay", function updateVatDisplay(cardQuoteId, vatRate) {
    // Update VAT display for both columns
    $(".total-vat-input[data-cardquoteid=\"".concat(cardQuoteId, "\"]")).each(function (_, element) {
      var $select = $(element).find('select');
      $select.val(vatRate.toString());
    });
  }), _defineProperty(_EstimationTable, "updateAllCalculations", function updateAllCalculations() {
    var _this9 = this;
    var cardQuoteIds = new Set();
    $('.column_single_price').each(function () {
      var quoteId = $(this).data('cardquoteid');
      if (quoteId) cardQuoteIds.add(quoteId);
    });
    cardQuoteIds.forEach(function (cardQuoteId) {
      // Update item totals
      $('.item_row').each(function (_, row) {
        var $row = $(row);
        var totalPrice = _this9.calculateItemTotal($row, cardQuoteId);
        $row.find(".column_total_price[data-cardquotetotalprice=\"".concat(cardQuoteId, "\"]")).text(totalPrice);
      });

      // Update group totals
      $('.group_row').each(function (_, row) {
        var $row = $(row);
        var groupTotal = _this9.calculateGroupTotal($row, cardQuoteId);
        $row.find("[data-cardquotegrouptotalprice=\"".concat(cardQuoteId, "\"]")).text(groupTotal);
      });

      // Calculate final totals
      _this9.calculateTotals(cardQuoteId);
    });
  }));
  EstimationTable.init();
});
/******/ })()
;