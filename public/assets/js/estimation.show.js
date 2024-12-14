/******/ (() => { // webpackBootstrap
/*!*******************************************************************!*\
  !*** ./Modules/Estimation/Resources/assets/js/estimation.show.js ***!
  \*******************************************************************/
$(document).ready(function () {
  var EstimationTable = {
    estimation: $('.estimation-show'),
    templates: {
      item: $('#add-item-template').html(),
      group: $('#add-group-template').html(),
      comment: $('#add-comment-template').html()
    },
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
      var _this2 = this;
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
          _this2.handleItemMove(ui.item);
          _this2.updatePOSNumbers();
          _this2.updateAllCalculations();
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
    },
    calculateItemTotal: function calculateItemTotal(itemRow, cardQuoteId) {
      // Check if item is optional
      var isOptional = itemRow.find('.item-optional').is(':checked');
      if (isOptional) return '-';

      // Find quantity and price specific to this card
      var quantity = this.parseGermanDecimal(itemRow.find('.item-quantity').val());
      var singlePrice = this.parseGermanDecimal(itemRow.find(".item-price[data-cardquotesingleprice=\"".concat(cardQuoteId, "\"]")).val());

      // Calculate total
      var totalPrice = quantity * singlePrice;
      return totalPrice === 0 ? '-' : this.formatGermanCurrency(totalPrice);
    },
    calculateGroupTotal: function calculateGroupTotal(groupRow, cardQuoteId) {
      var _this3 = this;
      var groupTotal = 0;

      // Find all items in this group
      var groupId = groupRow.data('groupid');
      var groupItems = $(".item_row[data-groupid=\"".concat(groupId, "\"]"));
      groupItems.each(function (index, itemRow) {
        var $itemRow = $(itemRow);
        var isOptional = $itemRow.find('.item-optional').is(':checked');
        if (!isOptional) {
          var quantity = _this3.parseGermanDecimal($itemRow.find('.item-quantity').val());
          var singlePrice = _this3.parseGermanDecimal($itemRow.find(".item-price[data-cardquotesingleprice=\"".concat(cardQuoteId, "\"]")).val());
          groupTotal += quantity * singlePrice;
        }
      });
      return groupTotal === 0 ? '-' : this.formatGermanCurrency(groupTotal);
    },
    updateAllCalculations: function updateAllCalculations() {
      var _this4 = this;
      // Get all unique card quote IDs
      var cardQuoteIds = new Set();
      $('.column_single_price').each(function () {
        var quoteId = $(this).data('cardquoteid');
        if (quoteId) cardQuoteIds.add(quoteId);
      });

      // Update calculations for each card
      cardQuoteIds.forEach(function (cardQuoteId) {
        // Update item totals
        $('.item_row').each(function (index, row) {
          var $row = $(row);
          var totalPrice = _this4.calculateItemTotal($row, cardQuoteId);
          $row.find(".column_total_price[data-cardquotetotalprice=\"".concat(cardQuoteId, "\"]")).text(totalPrice);
        });

        // Update group totals
        $('.group_row').each(function (index, row) {
          var $row = $(row);
          var groupTotal = _this4.calculateGroupTotal($row, cardQuoteId);
          $row.find("[data-cardquotegrouptotalprice=\"".concat(cardQuoteId, "\"]")).text(groupTotal);
        });
      });
    }
  };
  EstimationTable.init();
});
/******/ })()
;