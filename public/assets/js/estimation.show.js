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
$(document).ready(function () {
  var EstimationTable = {
    // autoSaveEnabled: $('#autoSaveEnabled').is(':checked'), //OLD
    autoSaveEnabled: false,
    lastSaveTime: 0,
    saveTimeout: null,
    saveInterval: 1000 * 30,
    hasUnsavedChanges: false,
    isFullScreen: false,
    estimation: $('.estimation-show'),
    originalPrices: new Map(),
    templates: {
      item: $('#add-item-template').html(),
      group: $('#add-group-template').html(),
      comment: $('#add-comment-template').html()
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
      this.estimation.on('click', '#toggleFullScreen', this.toggleFullScreen);
      this.estimation.on('blur', '.item-quantity, .item-price, .item-name, input[name^="item"][name$="[markup]"], input[name^="item"][name$="[discount]"]', function () {
        _this.hasUnsavedChanges = true;
        _this.autoSaveHandler();
      });
      this.estimation.on('change', '.item-optional, select[name^="item"][name$="[tax]"]', function () {
        _this.hasUnsavedChanges = true;
        _this.autoSaveHandler();
      });
      this.estimation.on('blur', '.item-quantity, .item-price', function (event) {
        var $target = $(event.currentTarget);
        _this.formatInput($target);
        _this.updateAllCalculations();
      });
      this.estimation.on('click', '#save-button', function () {
        _this.saveTableData();
      });
      this.estimation.on('click', '#save-complete', function () {
        _this.saveTableData('complete');
      });
      this.estimation.on('input', '#table-search', function () {
        _this.searchTableItem();
      });
      this.estimation.on('change', '.item-optional', function () {
        _this.updateAllCalculations();
      });
      this.estimation.on('change', 'select[name^="item"][name$="[tax]"]', function () {
        _this.updateAllCalculations();
      });
      this.estimation.on('change', '#QuateTypesStatus', function (event) {
        var Checkbox = $(event.currentTarget).is(':checked');
        var Id = $(event.currentTarget).data('id');
        var Type = $(event.currentTarget).data('type');
        _this.updateQuateTypeStatus(Checkbox, Id, Type);
      });
      this.estimation.on('blur', 'input[name^="item"][name$="[discount]"]', function (event) {
        var $input = $(event.target);
        var value = _this.parseGermanDecimal($input.val());
        $input.val(_this.formatGermanDecimal(value));
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
      this.estimation.on('blur', 'input[name^="item"][name$="[markup]"]', function (event) {
        var $input = $(event.target);
        var value = _this.parseGermanDecimal($input.val());
        var cardQuoteId = $input.attr('name').match(/\[(\d+)\]/)[1];

        // Format and style markup input
        $input.val(_this.formatGermanDecimal(value));
        _this.styleNegativeInput($input, value);
        _this.applyMarkupToSinglePrices(cardQuoteId, value);
        _this.updateAllCalculations();
      });
      this.estimation.on('change', '.SelectAllCheckbox', function (e) {
        var isChecked = $(e.target).prop('checked');
        $('.item_selection').prop('checked', isChecked);
      });
      this.estimation.on('change', '.group_selection', function (e) {
        var $groupCheckbox = $(e.target);
        var groupId = $groupCheckbox.data('groupid');
        var isChecked = $groupCheckbox.prop('checked');
        $(".item_row[data-groupid=\"".concat(groupId, "\"], .item_comment[data-groupid=\"").concat(groupId, "\"]")).find('.item_selection').prop('checked', isChecked);
        _this.updateSelectAllState();
      });
      this.estimation.on('change', '.item_selection:not(.group_selection)', function () {
        _this.updateSelectAllState();
      });
      $('button[data-actionremove]').on('click', function () {
        var $selectedCheckboxes = $('.item_selection:checked:not(.SelectAllCheckbox)');
        if ($selectedCheckboxes.length === 0) {
          toastrs("error", "Please select checkbox to continue delete");
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
            var estimationId = $('#quote_form').find('input[name="id"]').val();
            var itemIds = [];
            var groupIds = [];
            $selectedCheckboxes.each(function () {
              var $row = $(this).closest('tr');
              var id = $row.data('itemid') || $row.data('groupid');
              var isGroup = $row.hasClass('group_row');
              (isGroup ? groupIds : itemIds).push(id);
              if ($row.hasClass('item_row')) {
                var itemChild = $row.next("[data-itemid=\"".concat(id, "\"]"));
                itemChild.remove();
              }
              $row.remove();
            });
            $.ajax({
              url: route('estimation.destroy', estimationId),
              method: 'DELETE',
              data: {
                estimationId: estimationId,
                items: itemIds,
                groups: groupIds
              }
            });
            document.querySelector('.SelectAllCheckbox').checked = false;
            _this.updateAllCalculations();
            _this.updatePOSNumbers();
          }
        });
      });
      $(document).on('click', '#delete-quate', function (event) {
        var _this2 = this;
        event.preventDefault();
        Swal.fire({
          title: 'Confirmation Delete',
          text: 'Really! You want to remove them? You can\'t undo',
          showCancelButton: true,
          confirmButtonText: 'Yes, Delete it',
          cancelButtonText: "No, cancel"
        }).then(function (result) {
          if (result.isConfirmed) {
            var IDs = $(_this2).data('id');
            $.ajax({
              url: route('estimation.deleteQuote', IDs),
              method: 'DELETE',
              data: {
                id: IDs
              },
              success: function success() {
                window.location.reload();
              }
            });
          }
        });
      });
      this.estimation.on('blur', '.item-price', function (event) {
        var $input = $(event.target);
        _this.updateBasePrice($input);
        _this.updateAllCalculations();
      });
    },
    init: function init() {
      var _this3 = this;
      if (!this.validateTemplates()) return;
      this.initializeOriginalPrices();
      this.bindEvents();
      this.bindCalculationEvents();
      this.initializeSortable();
      this.updateAllCalculations();
      this.updatePOSNumbers();
      this.initializeAutoSave();
      this.initializePriceStyles();
      document.addEventListener('fullscreenchange', function () {
        _this3.isFullScreen = !!document.fullscreenElement;
        var icon = document.querySelector('.fa-expand, .fa-compress');
        if (icon) {
          icon.className = _this3.isFullScreen ? 'fa-solid fa-compress' : 'fa-solid fa-expand';
        }
      });
    },
    initializeFullScreen: function initializeFullScreen() {
      var _this4 = this;
      document.addEventListener('fullscreenchange', function () {
        _this4.isFullScreen = !!document.fullscreenElement;
        var btn = document.querySelector('.tools-btn button i.fa-expand, .tools-btn button i.fa-compress');
        if (btn) {
          btn.className = _this4.isFullScreen ? 'fa-solid fa-compress' : 'fa-solid fa-expand';
        }
      });
    },
    initializeAutoSave: function initializeAutoSave() {
      var _this5 = this;
      $('#autoSaveEnabled').on('change', function (e) {
        _this5.autoSaveEnabled = $(e.target).is(':checked');
        if (_this5.autoSaveEnabled && _this5.hasUnsavedChanges) {
          _this5.autoSaveHandler();
        }
      });
      $(window).on('beforeunload', function (e) {
        if (_this5.hasUnsavedChanges) {
          var message = 'You have unsaved changes. Are you sure you want to leave?';
          e.preventDefault();
          e.returnValue = message;
          return message;
        }
      });
    },
    initializePriceStyles: function initializePriceStyles() {
      var _this6 = this;
      $('.item-price').each(function (_, element) {
        var $price = $(element);
        var value = _this6.parseGermanDecimal($price.val());
        _this6.styleNegativeInput($price, value);
      });
    },
    initializeOriginalPrices: function initializeOriginalPrices() {
      var _this7 = this;
      $('.item-price').each(function (_, element) {
        var $price = $(element);
        var cardQuoteId = $price.data('cardquotesingleprice');
        var itemId = $price.closest('tr').data('itemid');
        var currentPrice = _this7.parseGermanDecimal($price.val());
        var markup = _this7.parseGermanDecimal($("input[name=\"item[".concat(cardQuoteId, "][markup]\"]")).val()) || 0;

        // Calculate the original price by reverse-applying the markup
        var originalPrice = markup !== 0 ? currentPrice / (1 + markup / 100) : currentPrice;
        var key = "".concat(cardQuoteId, "-").concat(itemId);
        _this7.originalPrices.set(key, originalPrice);

        // Update the display price with the correct markup
        if (markup !== 0) {
          $price.val(_this7.formatGermanCurrency(currentPrice));
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
    autoSaveHandler: function autoSaveHandler() {
      var _this8 = this;
      if (!this.autoSaveEnabled || !$('#quote_form').length || !this.hasUnsavedChanges) return;
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
          if (_this8.hasUnsavedChanges && _this8.autoSaveEnabled) {
            _this8.saveTableData();
            _this8.lastSaveTime = Date.now();
          }
        }, this.saveInterval);
      }
    },
    saveTableData: function saveTableData() {
      var _this9 = this;
      var isComplete = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : '';
      // if (!this.autoSaveEnabled) return;

      var columns = {};
      var cardQuoteIds = new Set();
      $('.column_single_price').each(function () {
        var quoteId = $(this).data('cardquoteid');
        if (quoteId) cardQuoteIds.add(quoteId);
      });
      cardQuoteIds.forEach(function (cardQuoteId) {
        columns[cardQuoteId] = {
          settings: {
            markup: _this9.parseGermanDecimal($("input[name=\"item[".concat(cardQuoteId, "][markup]\"]")).val() || '0'),
            cashDiscount: _this9.parseGermanDecimal($("input[name=\"item[".concat(cardQuoteId, "][discount]\"]")).val() || '0'),
            vat: _this9.parseGermanDecimal($("select[name=\"item[".concat(cardQuoteId, "][tax]\"]")).val() || '0')
          },
          totals: {
            netIncludingDiscount: _this9.parseGermanDecimal($(".total-net-discount[data-cardquoteid=\"".concat(cardQuoteId, "\"]")).text()),
            grossIncludingDiscount: _this9.parseGermanDecimal($(".total-gross-discount[data-cardquoteid=\"".concat(cardQuoteId, "\"]")).text()),
            net: _this9.parseGermanDecimal($(".total-net[data-cardquoteid=\"".concat(cardQuoteId, "\"]")).text()),
            gross: _this9.parseGermanDecimal($(".total-gross-total[data-cardquoteid=\"".concat(cardQuoteId, "\"]")).text())
          }
        };
      });
      var data = {
        cards: columns,
        form: this.getFormData(),
        newItems: this.prepareNewItemsForSubmission()
      };
      $.ajax({
        url: route('estimation.update', data.form.id),
        method: 'PUT',
        data: data,
        beforeSend: function beforeSend() {
          $('.lastSaveTimestamp').text('is running...');
          $('#save-button').html('Saving... <i class="fa fa-arrow-right-rotate rotate"></i>');
        },
        success: function success(idMappings) {
          _this9.updateEntitiesWithNewIds(idMappings);
          _this9.lastSaveTime = Date.now();
          _this9.hasUnsavedChanges = false;
          var lastSavedText = _this9.formatTimeAgo(_this9.lastSaveTime);
          _this9.startTimeAgoUpdates();
          $('.lastSaveTimestamp').text(lastSavedText);
          $('#save-button').html("Saved last changed.");
          if (isComplete) {
            window.location.href = route('estimations.finalize.estimate', data.form.id);
          } else {
            window.location.reload();
          }
        },
        error: function error(_error) {
          toastrs('error', 'Failed to save changes.');
          $('.lastSaveTimestamp').text('is failed.');
          _this9.hasUnsavedChanges = true;
        }
      });
    },
    formatTimeAgo: function formatTimeAgo(timestamp) {
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
    },
    startTimeAgoUpdates: function startTimeAgoUpdates() {
      var _this10 = this;
      if (this.timeAgoInterval) {
        clearInterval(this.timeAgoInterval);
      }
      this.timeAgoInterval = setInterval(function () {
        if (_this10.lastSaveTime) {
          var lastSavedText = _this10.formatTimeAgo(_this10.lastSaveTime);
          $('.lastSaveTimestamp').text(lastSavedText);
        }
      }, 60000);
    },
    prepareNewItemsForSubmission: function prepareNewItemsForSubmission() {
      var _this11 = this;
      var newItems = [];
      var groupRows = document.querySelectorAll('.group_row');
      groupRows.forEach(function (row) {
        var groupId = row.dataset.groupid;
        var groupName = row.querySelector('.grouptitle-input').value;
        var groupPos = row.querySelector('.grouppos').textContent.trim();
        var group = {
          id: groupId,
          type: 'group',
          name: groupName,
          pos: groupPos,
          total: null
        };
        newItems.push(group);
      });
      var itemRows = document.querySelectorAll('.item_row, .item_comment');
      itemRows.forEach(function (row) {
        var _row$querySelector, _row$querySelector2, _tinymce, _$, _row$querySelector3, _row$querySelector4, _row$querySelector5;
        var itemId = row.dataset.itemid;
        var type = row.dataset.type;
        var groupId = row.dataset.groupid;
        var name = ((_row$querySelector = row.querySelector('.item-name, .item-comment')) === null || _row$querySelector === void 0 ? void 0 : _row$querySelector.value.trim()) || null;
        var comment = ((_row$querySelector2 = row.querySelector('.item-comment')) === null || _row$querySelector2 === void 0 ? void 0 : _row$querySelector2.value.trim()) || null;
        var descriptionID = $(row).next(".tr_child_description[data-itemid=\"".concat(itemId, "\"]")).find('.description_input').attr('id');
        var description = ((_tinymce = tinymce) === null || _tinymce === void 0 || (_tinymce = _tinymce.get(descriptionID)) === null || _tinymce === void 0 ? void 0 : _tinymce.getContent()) || ((_$ = $(descriptionID)) === null || _$ === void 0 ? void 0 : _$.val()) || null;
        var item = {
          id: itemId,
          type: type,
          groupId: groupId,
          pos: row.querySelector('.pos-inner').textContent.trim(),
          name: name,
          description: description,
          comment: comment,
          quantity: _this11.parseGermanDecimal(((_row$querySelector3 = row.querySelector('.item-quantity')) === null || _row$querySelector3 === void 0 ? void 0 : _row$querySelector3.value) || '0'),
          unit: ((_row$querySelector4 = row.querySelector('.item-unit')) === null || _row$querySelector4 === void 0 ? void 0 : _row$querySelector4.value) || 0,
          optional: (_row$querySelector5 = row.querySelector('.item-optional')) !== null && _row$querySelector5 !== void 0 && _row$querySelector5.checked ? 0 : 1,
          prices: type == 'item' ? _this11.updateItemPriceAndTotal(itemId) : _this11.updateCommentPrices()
        };
        newItems.push(item);
      });
      return newItems;
    },
    updateCommentPrices: function updateCommentPrices() {
      var cardQuoteIds = Array.from(new Set(Array.from(document.querySelectorAll('[data-cardquoteid]')).map(function (el) {
        return el.dataset.cardquoteid;
      })));
      return cardQuoteIds.map(function (quoteId) {
        return {
          quoteId: quoteId,
          singlePrice: 0,
          totalPrice: 0
        };
      });
    },
    updateItemPriceAndTotal: function updateItemPriceAndTotal(itemId) {
      var row = document.querySelector(".item_row[data-itemid=\"".concat(itemId, "\"]"));
      var $self = this;
      var singlePricing = row.querySelectorAll('.item-price');
      var prices = Array.from(singlePricing).map(function (element) {
        var quoteId = element.closest('td[data-cardquoteid]').dataset.cardquoteid;
        var singlePrice = $self.parseNumber(element.value);
        var quantity = $self.parseNumber(row.querySelector('.item-quantity').value);
        var total = singlePrice * quantity;
        return {
          quoteId: quoteId,
          singlePrice: singlePrice,
          totalPrice: total
        };
      });
      return prices;
    },
    updateEntitiesWithNewIds: function updateEntitiesWithNewIds(idMappings) {
      Object.entries(idMappings).forEach(function (_ref) {
        var _ref2 = _slicedToArray(_ref, 2),
          oldId = _ref2[0],
          newId = _ref2[1];
        var rows = document.querySelectorAll("\n                    tr[data-itemid=\"".concat(oldId, "\"], \n                    tr[data-groupid=\"").concat(oldId, "\"]\n                "));
        rows.forEach(function (row) {
          if (row.dataset.itemid == oldId) row.dataset.itemid = newId;
          if (row.dataset.groupid == oldId) row.dataset.groupid = newId;
          row.querySelectorAll("[name*=\"[".concat(oldId, "]\"]")).forEach(function (input) {
            input.name = input.name.replace("[".concat(oldId, "]"), "[".concat(newId, "]"));
          });
        });
      });
    },
    getFormData: function getFormData() {
      var $form = $('#quote_form');
      var formData = $form.serializeArray();
      var formObject = {};
      formData.forEach(function (item) {
        formObject[item.name] = item.value;
      });
      return formObject;
    },
    validateTemplates: function validateTemplates() {
      return Object.values(this.templates).every(function (template) {
        return template;
      });
    },
    parseNumber: function parseNumber(value) {
      if (typeof value === 'number') return value;
      return parseFloat(value.replace(/[^\d,-]/g, '').replace(',', '.')) || 0;
    },
    updateBasePrice: function updateBasePrice($input) {
      var cardQuoteId = $input.data('cardquotesingleprice');
      var itemId = $input.closest('tr').data('itemid');
      var key = "".concat(cardQuoteId, "-").concat(itemId);
      var currentPrice = this.parseGermanDecimal($input.val());
      var markup = this.parseGermanDecimal($("input[name=\"item[".concat(cardQuoteId, "][markup]\"]")).val()) || 0;

      // Store the original price without markup
      var originalPrice = markup !== 0 ? currentPrice / (1 + markup / 100) : currentPrice;
      this.originalPrices.set(key, originalPrice);
    },
    addItems: function addItems(type) {
      var _this12 = this;
      if (!this.templates[type]) return;
      var timestamp = Date.now();
      var template = this.templates[type];
      if (type === 'group') {
        var newGroup = template.replace(/{TEMPLATE_GROUP_ID}/g, timestamp).replace(/{TEMPLATE_POS}/g, this.getNextGroupPosition());
        $('#estimation-items').append(newGroup);
        this.updatePOSNumbers();
        this.updateAllCalculations();
        return;
      }
      var existingGroups = document.querySelectorAll('.group_row');
      if (existingGroups.length === 0) {
        this.addItems('group');
      }
      var currentGroupId = this.getCurrentGroupId();
      var newItem = template.replace(/{TEMPLATE_ID}/g, timestamp).replace(/{TEMPLATE_GROUP_ID}/g, currentGroupId).replace(/{TEMPLATE_POS}/g, this.getNextItemPosition(currentGroupId));
      var lastGroupItem = $("tr[data-groupid=\"".concat(currentGroupId, "\"]:last"));
      if (lastGroupItem.length) {
        lastGroupItem.after(newItem);
      } else {
        $("tr[data-groupid=\"".concat(currentGroupId, "\"]")).after(newItem);
      }

      // Force recalculation of the group total
      var groupRow = $(".group_row[data-groupid=\"".concat(currentGroupId, "\"]"));
      if (groupRow.length) {
        var cardQuoteIds = new Set();
        $('.column_single_price').each(function () {
          var quoteId = $(this).data('cardquoteid');
          if (quoteId) cardQuoteIds.add(quoteId);
        });
        cardQuoteIds.forEach(function (cardQuoteId) {
          var groupTotal = _this12.calculateGroupTotal(groupRow, cardQuoteId);
          groupRow.find("[data-cardquotegrouptotalprice=\"".concat(cardQuoteId, "\"]")).text(groupTotal);
        });
      }
      this.updatePOSNumbers();
      this.updateAllCalculations();
      this.hasUnsavedChanges = true;
      this.autoSaveHandler();
    },
    updateSelectAllState: function updateSelectAllState() {
      var totalCheckboxes = $('.item_selection:not(.SelectAllCheckbox)').length;
      var checkedCheckboxes = $('.item_selection:not(.SelectAllCheckbox):checked').length;
      $('.SelectAllCheckbox').prop('checked', totalCheckboxes === checkedCheckboxes && totalCheckboxes > 0);
    },
    getCurrentGroupId: function getCurrentGroupId() {
      var groupRows = document.querySelectorAll('.group_row');
      if (groupRows.length > 0) {
        var latestID = groupRows[groupRows.length - 1].getAttribute('data-groupid');
        console.log(latestID);
        return latestID;
      } else {
        return Date.now() + 10;
      }
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
      var lastGroupId = null;
      $('.group_row, .item_row, .item_comment').each(function () {
        var $row = $(this);
        if ($row.hasClass('group_row')) {
          currentGroupPos++;
          itemCountInGroup = 0;
          lastGroupId = $row.data('groupid');
          $row.find('.grouppos').text(currentGroupPos.toString().padStart(2, '0'));
        } else if (lastGroupId) {
          itemCountInGroup++;
          $row.attr('data-groupid', lastGroupId);
          $row.find('.pos-inner').text("".concat(currentGroupPos.toString().padStart(2, '0'), ".").concat(itemCountInGroup.toString().padStart(2, '0')));
        }
      });
    },
    initializeSortable: function initializeSortable() {
      var _this13 = this;
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

            // Clone all related items regardless of visibility
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
          if (!item.hasClass('group_row')) {
            // Only hide description row for individual items
            var itemId = item.data('itemid');
            $(".item_child[data-itemid=\"".concat(itemId, "\"]")).hide();
          }
        },
        stop: function stop(e, ui) {
          var item = ui.item;
          if (item.hasClass('group_row')) {
            var groupId = item.data('groupid');
            var groupItems = $("tr[data-groupid=\"".concat(groupId, "\"]")).not('.group_row');
            item.after(groupItems);

            // Maintain visibility state of group items based on group's expanded state
            var isGroupExpanded = item.find('.grp-dt-control.fa-caret-down').length > 0;
            if (isGroupExpanded) {
              groupItems.filter('.item_row, .item_comment').each(function () {
                var $row = $(this);
                var itemId = $row.data('itemid');
                var isItemExpanded = $row.find('.desc_toggle.fa-caret-down').length > 0;
                if (isItemExpanded) {
                  $(".item_child[data-itemid=\"".concat(itemId, "\"]")).show();
                }
              });
            }
          } else if (item.hasClass('item_row') || item.hasClass('item_comment')) {
            var prevGroup = item.prevAll('.group_row').first();
            if (prevGroup.length) {
              var newGroupId = prevGroup.data('groupid');
              var itemId = item.data('itemid');
              item.attr('data-groupid', newGroupId);
              var descRow = $(".item_child[data-itemid=\"".concat(itemId, "\"]"));
              descRow.attr('data-groupid', newGroupId);
            }
          }
          _this13.updatePOSNumbers();
          _this13.updateAllCalculations();
          _this13.hasUnsavedChanges = true;
          _this13.autoSaveHandler();
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
      var mainItems = $("tr.item_row[data-groupid=\"".concat(groupId, "\"], tr.item_comment[data-groupid=\"").concat(groupId, "\"]"));
      mainItems.toggle();
      mainItems.each(function () {
        var itemId = $(this).data('itemid');
        var descRow = $(".item_child[data-itemid=\"".concat(itemId, "\"]"));
        var itemToggleIcon = $(this).find('.desc_toggle');
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
      if (!value || typeof value !== 'string') return 0;
      value = value.replace(/[€\s]/g, '').replace(/\./g, '').replace(',', '.');
      var parsed = parseFloat(value);
      return isNaN(parsed) ? 0 : parsed;
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
        currency: 'EUR',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      }).format(value);
    },
    styleNegativeInput: function styleNegativeInput($input, value) {
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
    },
    applyMarkupToSinglePrices: function applyMarkupToSinglePrices(cardQuoteId, markup) {
      var _this14 = this;
      $(".item-price[data-cardquotesingleprice=\"".concat(cardQuoteId, "\"]")).each(function (_, element) {
        var $price = $(element);
        var itemId = $price.closest('tr').data('itemid');
        var key = "".concat(cardQuoteId, "-").concat(itemId);
        var basePrice = _this14.originalPrices.get(key);
        if (basePrice !== undefined) {
          var newPrice = markup === 0 ? basePrice : basePrice * (1 + markup / 100);
          $price.val(_this14.formatGermanCurrency(newPrice));
          _this14.styleNegativeInput($price, newPrice);
        }
      });
    },
    calculateItemTotal: function calculateItemTotal(itemRow, cardQuoteId) {
      if (itemRow.find('.item-optional').is(':checked')) {
        return this.formatGermanCurrency(0);
      }
      var quantity = this.parseGermanDecimal(itemRow.find('.item-quantity').val() || '0');
      var price = this.parseGermanDecimal(itemRow.find(".item-price[data-cardquotesingleprice=\"".concat(cardQuoteId, "\"]")).val() || '0');
      var total = quantity * price;
      return this.formatGermanCurrency(total);
    },
    calculateGroupTotal: function calculateGroupTotal(groupRow, cardQuoteId) {
      var _this15 = this;
      var total = 0;
      var groupId = groupRow.data('groupid');

      // Get all items in this group
      $(".item_row[data-groupid=\"".concat(groupId, "\"]")).each(function (_, item) {
        var $item = $(item);

        // Skip optional items
        if ($item.find('.item-optional').is(':checked')) {
          return;
        }
        var quantity = _this15.parseGermanDecimal($item.find('.item-quantity').val() || '0');
        var price = _this15.parseGermanDecimal($item.find(".item-price[data-cardquotesingleprice=\"".concat(cardQuoteId, "\"]")).val() || '0');
        var itemTotal = quantity * price;
        total += itemTotal;
      });

      // Return formatted total or dash
      return total === 0 ? '-' : this.formatGermanCurrency(total);
    },
    calculateTotals: function calculateTotals(cardQuoteId) {
      var _this16 = this;
      var netTotal = 0;
      $(".group_row [data-cardquotegrouptotalprice=\"".concat(cardQuoteId, "\"]")).each(function (_, element) {
        var groupTotal = _this16.parseGermanDecimal($(element).text());
        if (!isNaN(groupTotal)) netTotal += groupTotal;
      });
      var cashDiscount = this.parseGermanDecimal($("input[name=\"item[".concat(cardQuoteId, "][discount]\"]")).val()) || 0;
      var vatRate = parseFloat($("select[name=\"item[".concat(cardQuoteId, "][tax]\"]")).val()) || 0;
      var netAfterDiscount = netTotal * (1 - cashDiscount / 100);
      var vatAmount = netTotal * (vatRate / 100);
      var grossTotal = netTotal + vatAmount;
      var grossAfterDiscount = netAfterDiscount + netAfterDiscount * vatRate / 100;
      $(".total-net[data-cardquoteid=\"".concat(cardQuoteId, "\"]")).text(this.formatGermanCurrency(netTotal));
      $(".total-net-discount[data-cardquoteid=\"".concat(cardQuoteId, "\"]")).text(this.formatGermanCurrency(netAfterDiscount));
      $(".total-gross-discount[data-cardquoteid=\"".concat(cardQuoteId, "\"]")).text(this.formatGermanCurrency(grossAfterDiscount));
      $(".total-gross-total[data-cardquoteid=\"".concat(cardQuoteId, "\"]")).text(this.formatGermanCurrency(grossTotal));
      $(".totalnr.total-gross-total[data-cardquoteid=\"".concat(cardQuoteId, "\"]")).text(this.formatGermanCurrency(grossTotal));
    },
    bindCalculationEvents: function bindCalculationEvents() {
      var _this17 = this;
      // Update on quantity change
      this.estimation.on('input', '.item-quantity', function (e) {
        var $row = $(e.target).closest('tr');
        _this17.updateRowCalculations($row);
        _this17.updateAllCalculations();
      });

      // Update on price change
      this.estimation.on('input', '.item-price', function (e) {
        var $row = $(e.target).closest('tr');
        _this17.updateRowCalculations($row);
        _this17.updateAllCalculations();
      });

      // Update on optional checkbox change
      this.estimation.on('change', '.item-optional', function (e) {
        var $row = $(e.target).closest('tr');
        _this17.updateRowCalculations($row);
        _this17.updateAllCalculations();
      });
    },
    updateRowCalculations: function updateRowCalculations($row) {
      var _this18 = this;
      var itemId = $row.data('itemid');
      var cardQuoteIds = new Set();
      $('.column_single_price').each(function (_, el) {
        var quoteId = $(el).data('cardquoteid');
        if (quoteId) cardQuoteIds.add(quoteId);
      });
      cardQuoteIds.forEach(function (cardQuoteId) {
        var total = _this18.calculateItemTotal($row, cardQuoteId);
        $row.find("[data-cardquotetotalprice=\"".concat(cardQuoteId, "\"]")).text(total);

        // Update group total
        var $groupRow = $(".group_row[data-groupid=\"".concat($row.data('groupid'), "\"]"));
        if ($groupRow.length) {
          var groupTotal = _this18.calculateGroupTotal($groupRow, cardQuoteId);
          $groupRow.find("[data-cardquotegrouptotalprice=\"".concat(cardQuoteId, "\"]")).text(groupTotal);
        }
      });
    },
    updateDisplayValues: function updateDisplayValues(cardQuoteId, values) {
      var formatCurrency = this.formatGermanCurrency.bind(this);
      var net = values.net,
        netAfterDiscount = values.netAfterDiscount,
        grossAfterDiscount = values.grossAfterDiscount,
        vatRate = values.vatRate;
      $(".total-net[data-cardquoteid=\"".concat(cardQuoteId, "\"]")).text(formatCurrency(net));
      $(".total-net-discount[data-cardquoteid=\"".concat(cardQuoteId, "\"]")).text(formatCurrency(netAfterDiscount));
      $(".total-gross-discount[data-cardquoteid=\"".concat(cardQuoteId, "\"]")).text(formatCurrency(grossAfterDiscount));
      var $vatSelect = $("select[name=\"item[".concat(cardQuoteId, "][tax]\"]"));
      $vatSelect.val(vatRate.toString());
      var grossWithVat = net * (1 + vatRate / 100);
      $(".total-gross[data-cardquoteid=\"".concat(cardQuoteId, "\"]")).text(formatCurrency(grossWithVat));
    },
    applySinglePriceMarkup: function applySinglePriceMarkup(price, markup) {
      return price * (1 + markup / 100);
    },
    updateItemPrices: function updateItemPrices(cardQuoteId) {
      var _this19 = this;
      var markup = this.parseGermanDecimal($("input[name=\"item[".concat(cardQuoteId, "][markup]\"]")).val()) || 0;
      $(".item_row").each(function (_, row) {
        var $row = $(row);
        var $priceInput = $row.find(".item-price[data-cardquotesingleprice=\"".concat(cardQuoteId, "\"]"));
        var basePrice = _this19.parseGermanDecimal($priceInput.val());
        var priceWithMarkup = _this19.applySinglePriceMarkup(basePrice, markup);
        $priceInput.val(_this19.formatGermanCurrency(priceWithMarkup));
      });
    },
    updateTotalDisplay: function updateTotalDisplay(cardQuoteId, totals) {
      $(".total-net-discount[data-cardquoteid=\"".concat(cardQuoteId, "\"]")).text(this.formatGermanCurrency(totals.netIncDiscount));
      $(".total-gross-discount[data-cardquoteid=\"".concat(cardQuoteId, "\"]")).text(this.formatGermanCurrency(totals.grossIncDiscount));
      $(".total-net[data-cardquoteid=\"".concat(cardQuoteId, "\"]")).text(this.formatGermanCurrency(totals.net));
      $(".total-gross-total[data-cardquoteid=\"".concat(cardQuoteId, "\"]")).text(this.formatGermanCurrency(totals.gross));
      this.updateVatDisplay(cardQuoteId, totals.vatRate);
    },
    updateVatDisplay: function updateVatDisplay(cardQuoteId, vatRate) {
      $(".total-vat-input[data-cardquoteid=\"".concat(cardQuoteId, "\"]")).each(function (_, element) {
        var $select = $(element).find('select');
        $select.val(vatRate.toString());
      });
    },
    updateAllCalculations: function updateAllCalculations() {
      var _this20 = this;
      var cardQuoteIds = new Set();
      $('.column_single_price').each(function () {
        var quoteId = $(this).data('cardquoteid');
        if (quoteId) cardQuoteIds.add(quoteId);
      });
      cardQuoteIds.forEach(function (cardQuoteId) {
        $('.item_row').each(function (_, row) {
          var $row = $(row);
          var totalPrice = _this20.calculateItemTotal($row, cardQuoteId);
          $row.find(".column_total_price[data-cardquotetotalprice=\"".concat(cardQuoteId, "\"]")).text(totalPrice);
        });
        $('.group_row').each(function (_, row) {
          var $row = $(row);
          var groupTotal = _this20.calculateGroupTotal($row, cardQuoteId);
          $row.find("[data-cardquotegrouptotalprice=\"".concat(cardQuoteId, "\"]")).text(groupTotal);
        });
        _this20.calculateTotals(cardQuoteId);
      });
    },
    searchTableItem: function searchTableItem() {
      var searchInput = document.querySelector('#table-search');
      var tableRows = document.querySelectorAll('#estimation-edit-table tbody tr:not(.item_child)');
      if (!searchInput || !tableRows.length) {
        console.error('Required elements not found');
        return;
      }
      var searchTerm = searchInput.value.trim().toLowerCase();
      tableRows.forEach(function (row) {
        var nameCell = row.querySelector('.column_name input');
        if (!nameCell) {
          row.style.display = 'none';
          return;
        }
        var name = nameCell.value.trim().toLowerCase();

        // If search is empty, show all rows
        if (searchTerm === '') {
          row.style.display = '';
          return;
        }

        // Show/hide based on search match
        row.style.display = name.includes(searchTerm) ? '' : 'none';
      });
    },
    updateQuateTypeStatus: function updateQuateTypeStatus(Checkbox, QuoteId, Type) {
      var checkboxValue = Checkbox ? 1 : 0;
      var $cardQuote = $(".cardQuote[data-cardquoteid=\"".concat(QuoteId, "\"]"));
      $cardQuote.removeClass('quote clientQuote subcontractor');
      if (Checkbox) {
        if (Type === 'quote') {
          $cardQuote.addClass('quote');
        }
        if (Type === 'clientQuote') {
          $cardQuote.addClass('clientQuote');
        }
        if (Type === 'subcontractor') {
          $cardQuote.addClass('subcontractor');
        }
      }
      $.ajax({
        url: route('estimation.quateTypesStatus', QuoteId),
        type: "POST",
        data: {
          type: Type,
          checkbox: checkboxValue
        }
      });
    }
  };
  $(document).ajaxComplete(function () {
    if ($('#sub-contractor').length > 0) {
      $('#sub-contractor').select2({
        dropdownParent: $("#commonModal")
      });
    }
  });
  EstimationTable.init();
});
/******/ })()
;