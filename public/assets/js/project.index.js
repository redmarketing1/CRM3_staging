/******/ (() => { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ "./Modules/Project/Resources/assets/js/project.index.js":
/*!**************************************************************!*\
  !*** ./Modules/Project/Resources/assets/js/project.index.js ***!
  \**************************************************************/
/***/ (() => {

$(document).ready(function () {
  var table = $('#projectsTable').DataTable({
    lengthChange: true,
    ordering: false,
    searching: true,
    layout: {
      topEnd: null
    },
    select: {
      style: 'multi' // Enable multiple row selection
    },
    pagingType: 'simple',
    language: {
      paginate: {
        previous: 'Previous',
        next: 'Next'
      }
    },
    processing: false,
    serverSide: false,
    ajax: {
      url: route('project.index'),
      type: 'GET',
      dataType: 'json'
    },
    columns: [{
      data: null,
      orderable: false,
      className: 'dt-body-center input-checkbox',
      render: function render(data, type, full, meta) {
        return '<input type="checkbox" class="row-select-checkbox" value="' + data.id + '">';
      }
    }, {
      data: 'thumbnail',
      name: 'thumbnail',
      className: 'thumbnail'
    }, {
      data: 'name',
      name: 'name',
      orderable: true,
      className: 'name'
    }, {
      data: 'comments',
      name: 'comments',
      defaultContent: 'N/A',
      orderable: false,
      className: 'comments'
    }, {
      data: 'is_archive',
      name: 'is_archive',
      visible: false,
      className: 'is_archive'
    }, {
      data: 'status',
      name: 'status',
      defaultContent: 'N/A',
      orderable: true,
      className: 'status'
    }, {
      data: 'priority',
      name: 'priority',
      defaultContent: 'N/A',
      orderable: false,
      className: 'priority'
    }, {
      data: 'construction',
      name: 'construction',
      defaultContent: 'N/A',
      orderable: false,
      className: 'construction'
    }, {
      data: 'budget',
      name: 'budget',
      orderable: true,
      className: 'budget'
    }, {
      data: 'created_at',
      name: 'created_at',
      orderable: true,
      className: 'created_at'
    }, {
      data: 'action',
      name: 'action',
      orderable: false,
      searchable: false,
      className: 'action'
    }],
    initComplete: function initComplete(settings, _ref) {
      var data = _ref.data,
        filterableStatusList = _ref.filterableStatusList,
        filterablePriorityList = _ref.filterablePriorityList;
      $('#projectsTable colgroup').remove();
      if (filterableStatusList.html) {
        var htmlContent = $.parseHTML(filterableStatusList.html);
        $('#projectsTable').parents('.projectsTableContainter').parents('div.col-xl-12').before(htmlContent);
      }
      if (filterableStatusList.data) {
        var selectData = $.map(filterableStatusList.data, function (value, key) {
          return {
            id: removeWhitespace(value.name).toLowerCase(),
            text: value.name,
            backgroundColor: value.background_color,
            fontColor: value.font_color
          };
        });
        $('#filterableStatusDropdown').select2({
          data: selectData,
          placeholder: 'Select Status',
          multiple: true,
          allowClear: false,
          minimumResultsForSearch: Infinity,
          templateResult: formatOption,
          templateSelection: formatSelection
        });
      }
      if (filterablePriorityList) {
        var _selectData = $.map(filterablePriorityList, function (value, key) {
          return {
            id: value.name,
            text: value.name,
            backgroundColor: value.background_color,
            fontColor: value.font_color
          };
        });
        $('#filterablePriorityDropdown').select2({
          data: _selectData,
          placeholder: 'Select Priority',
          multiple: true,
          allowClear: false,
          minimumResultsForSearch: Infinity,
          templateResult: formatOption,
          templateSelection: formatSelection
        });
      }
      var maxBudget = 0;
      table.rows().every(function (rowIdx, tableLoop, rowLoop) {
        var data = this.data();
        var projectBudget = parseFloat(data.budget);
        if (projectBudget > maxBudget) {
          maxBudget = projectBudget;
        }
      });
      maxBudget = convertNumberFormat(maxBudget);
      $('#filter_price_from,#filter_price_to').attr('max', maxBudget);
      $('#filter_price_to,.range-max').val(maxBudget);
      $('#projectsTable tr:first-child.hide').fadeIn();
      $('.daterange').daterangepicker({
        autoUpdateInput: false,
        locale: {
          cancelLabel: 'clear'
        },
        ranges: {
          'Today': [moment(), moment()],
          'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
          'This Week': [moment().subtract(6, 'days'), moment()],
          'This Month': [moment().startOf('month'), moment().endOf('month')],
          'Last 30 Days': [moment().subtract(29, 'days'), moment()],
          'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        }
      }).on('apply.daterangepicker', function (ev, picker) {
        $(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));
        table.draw();
      }).on('cancel.daterangepicker', function (ev, picker) {
        $(this).val('');
        table.draw();
      });
      $('#projectsTable thead tr:nth-child(2) th:first-child').html('<input type="checkbox" id="select-all">');
      $('#projectsTable tbody').on('change', '.row-select-checkbox', function () {
        var selectedRows = $('input.row-select-checkbox:checked').length;
        if (selectedRows > 0) {
          $('#bulk-action-selector').fadeIn();
        } else {
          $('#bulk-action-selector').fadeOut();
        }
      });
      $('#select-all').on('change', function () {
        var isChecked = this.checked;
        $('input.row-select-checkbox').prop('checked', isChecked);
        var selectedRows = $('input.row-select-checkbox:checked').length;
        if (selectedRows > 0) {
          $('#bulk-action-selector').fadeIn();
        } else {
          $('#bulk-action-selector').fadeOut();
        }
      });
      $('.projects-filters .select2').select2({
        minimumResultsForSearch: Infinity
      });
    }
  });

  // Custom format for dropdown options
  function formatOption(option) {
    if (!option.id) {
      return option.text;
    }
    return $('<span>').css({
      'background-color': option.backgroundColor,
      'color': option.fontColor,
      'padding': '5px',
      'border-radius': '4px',
      'display': 'block'
    }).text(option.text);
  }

  // Custom format for selected option
  function formatSelection(option) {
    if (!option.id) {
      return option.text;
    }
    return $('<span>').css({
      'background-color': option.backgroundColor,
      'color': option.fontColor,
      'padding': '5px',
      'border-radius': '4px',
      'display': 'block'
    }).text(option.text);
  }
  $(document).on('click', '#status-tabs a', function (e) {
    e.preventDefault();
    $('#status-tabs a').removeClass('active');
    $(this).addClass('active');
    table.draw();
  });
  $(document).on('input', '#searchProject, #filter_price_from, #filter_price_to', function (e) {
    table.draw();
  });
  $(document).on('change', '#filterableStatusDropdown, #filterablePriorityDropdown, #filterableDaterange, #projectVisibality', function () {
    table.draw();
  });
  $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
    var selectedStatus = removeWhitespace($('#status-tabs .active').data('status-name')).toLowerCase();
    var selectedVisibility = $('#projectVisibality').val() || 'only-active';
    var selectedDropdownStatus = $('#filterableStatusDropdown').val() || [];
    var selectedDropdownPriority = $('#filterablePriorityDropdown').val() || [];
    var selectedDateRange = $('#filterableDaterange').val();
    var minBudget = parseFloat($('#filter_price_from').val());
    var maxBudget = parseFloat($('#filter_price_to').val());
    var searchProject = removeWhitespace($('#searchProject').val()).toLowerCase();
    var projectName = removeWhitespace(data[2]).toLowerCase();
    var projectComment = removeWhitespace(data[3]).toLowerCase();
    var isArchived = parseInt(data[4], 10); // 1 for archived, 0 for not archived
    var projectStatus = removeWhitespace(data[5]).toLowerCase();
    var projectPriority = removeWhitespace(data[6]).toLowerCase();
    var projectBudget = parseFloat(data[8]);
    var projectCreatedAt = data[9];

    /**
     * Check if the project is archived; 
     * we only want to show active projects by default
     */
    if (selectedVisibility === 'only-active' && isArchived === 1 || selectedVisibility === 'only-archive' && isArchived === 0) return false;
    if (selectedVisibility === 'only-archive') {
      $('#status-tabs').find('a').fadeOut().removeClass('active');
      $('#bulk-action-selector').find('option[value=archive]').hide();
      $('#bulk-action-selector').find('option[value=unarchive]').show();
    } else {
      $('#status-tabs').find('a').fadeIn();
      $('#bulk-action-selector').find('option[value=archive]').show();
      $('#bulk-action-selector').find('option[value=unarchive]').hide();
    }

    /**
     * Filter project bewteen price range
     */
    if (minBudget && projectBudget <= minBudget || maxBudget && projectBudget >= maxBudget) {
      return false;
    }

    /**
     * Date Range Filter
     */
    if (selectedDateRange) {
      var dateRange = selectedDateRange.split(' - ');
      var startDate = moment(dateRange[0], 'MM/DD/YYYY');
      var endDate = moment(dateRange[1], 'MM/DD/YYYY');
      var projectDate = moment(projectCreatedAt, 'DD-MM-YYYY HH:mm');
      if (!projectDate.isBetween(startDate, endDate, undefined, '[]')) {
        return false;
      }
    }
    selectedDropdownPriority = selectedDropdownPriority.map(function (priority) {
      return removeWhitespace(priority).toLowerCase();
    });

    // Other Filters (Status, Priority, Project Name)  
    if ((!selectedStatus || projectStatus === selectedStatus) && (!selectedDropdownStatus.length || selectedDropdownStatus.includes(projectStatus)) && (!selectedDropdownPriority.length || selectedDropdownPriority.some(function (priority) {
      return priority === projectPriority;
    })) && (searchProject === '' || projectName.indexOf(searchProject) !== -1 || projectComment.indexOf(searchProject) !== -1)) {
      return true;
    }
    return false;
  });
  $(document).on('change', '#bulk-action-selector', function () {
    var selectedOption = $(this).find('option:selected');
    var value = selectedOption.val();
    if (!value || value === "Bulk actions") {
      return;
    }
    var title = selectedOption.data('title');
    var text = selectedOption.data('text');
    var type = selectedOption.data('type');
    var selectedRows = $('input.row-select-checkbox:checked');
    var selectedData = [];
    for (var i = 0; i < selectedRows.length; i++) {
      selectedData.push(selectedRows[i].value);
    }
    Swal.fire({
      title: title,
      text: text,
      showCancelButton: true,
      confirmButtonText: "Yes, ".concat(type, " it"),
      cancelButtonText: "No, cancel"
    }).then(function (result) {
      if (result.isConfirmed) {
        $.ajax({
          url: route('project.update', 1),
          type: "PUT",
          data: {
            type: type,
            ids: selectedData
          },
          success: function success(response) {
            console.log(response);
          }
        });
        var current = 1;
        var total = selectedData.length;
        var timerInterval;
        Swal.fire({
          icon: 'success',
          title: "".concat(type.charAt(0).toUpperCase() + type.slice(1), " Successful!"),
          html: "<b>".concat(current, "</b> project").concat(total > 1 ? 's' : '', " have been moved to ").concat(type),
          timer: 2000,
          timerProgressBar: true,
          showConfirmButton: false,
          didOpen: function didOpen() {
            Swal.showLoading();
            var b = Swal.getHtmlContainer().querySelector('b');
            timerInterval = setInterval(function () {
              if (current < total) {
                current++;
                b.textContent = "".concat(current);
              } else {
                clearInterval(timerInterval);
              }
            }, 100);
          },
          willClose: function willClose() {
            clearInterval(timerInterval);
          }
        }).then(function () {
          if (type === 'delete') {
            selectedRows.each(function () {
              var row = $(this).closest('tr');
              table.row(row).remove();
            });
          }
          if (type === 'archive') {
            selectedRows.each(function (value, index) {
              var rowId = $(this).val();
              var rowData = table.row('#' + rowId).data();
              rowData.is_archive = 1;
              table.row('#' + rowId).data(rowData).draw();
            });
          }
          if (type === 'unarchive') {
            selectedRows.each(function (value, index) {
              var rowId = $(this).val();
              var rowData = table.row('#' + rowId).data();
              rowData.is_archive = 0;
              table.row('#' + rowId).data(rowData).draw();
            });
          }
          if (type === 'duplicate') {
            selectedRows.each(function (val, element) {
              var rowId = $(this).val();
              var rowData = table.row('#' + rowId).data();
              table.row.add(rowData).draw();
            });
          }
          $('input#select-all,.row-select-checkbox').prop('checked', false);
          $('#bulk-action-selector').val('bulk').fadeOut();
        });
      }
    });
  });
  var rangeInput = $(".range-input input");
  var priceInput = $(".price-input input");
  var range = $(".slider_filter .progress");
  var priceGap = 1;

  // Update range input values based on price inputs
  priceInput.on('input', function () {
    var minPrice = parseInt(priceInput.eq(0).val());
    var maxPrice = parseInt(priceInput.eq(1).val());
    if (maxPrice - minPrice >= priceGap && maxPrice <= parseInt(rangeInput.eq(1).attr('max'))) {
      if ($(this).hasClass("input-min")) {
        rangeInput.eq(0).val(minPrice);
        range.css('left', minPrice / parseInt(rangeInput.eq(0).attr('max')) * 100 + "%");
      } else {
        rangeInput.eq(1).val(maxPrice);
        range.css('right', 100 - maxPrice / parseInt(rangeInput.eq(1).attr('max')) * 100 + "%");
      }
    }
  });

  // Update price inputs based on range input values
  rangeInput.on('input', function () {
    var minVal = parseInt(rangeInput.eq(0).val());
    var maxVal = parseInt(rangeInput.eq(1).val());
    if (maxVal - minVal < priceGap) {
      if ($(this).hasClass("range-min")) {
        rangeInput.eq(0).val(maxVal - priceGap);
      } else {
        rangeInput.eq(1).val(minVal + priceGap);
      }
    } else {
      priceInput.eq(0).val(minVal);
      priceInput.eq(1).val(maxVal);
      range.css('left', minVal / parseInt(rangeInput.eq(0).attr('max')) * 100 + "%");

      // Calculate width and set it to the progress bar
      var width = (maxVal - minVal) / parseInt(rangeInput.eq(1).attr('max')) * 100 + "%";
      range.css('width', width);
    }
  });
  $(document).on('click', '#clearFilter', function () {
    $('input#select-all,.row-select-checkbox').prop('checked', false);
    $('#bulk-action-selector').val('bulk');
    $('#status-tabs a').removeClass('active');
    $('#searchProject,#filterableStatusDropdown,#filterablePriorityDropdown,#filterableDaterange').val(null).trigger('change');
    $('#filter_price_from').val(0);
    $('#filter_price_to,.range-max').val($('#filter_price_to').attr('max'));
    table.draw();
  });
  function convertNumberFormat(number) {
    var format = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 'EU';
    var numString = number.toString();
    if (format === 'EU') {
      numString = numString.replace(/,/g, '.').replace(/\./g, ',');
    } else if (format === 'US') {
      numString = numString.replace(/,/g, '').replace(/\./g, ',').replace(/,/g, '.');
    }
    return numString;
  }
});

/***/ }),

/***/ "./Modules/Project/Resources/assets/css/project.index.scss":
/*!*****************************************************************!*\
  !*** ./Modules/Project/Resources/assets/css/project.index.scss ***!
  \*****************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = __webpack_modules__;
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/chunk loaded */
/******/ 	(() => {
/******/ 		var deferred = [];
/******/ 		__webpack_require__.O = (result, chunkIds, fn, priority) => {
/******/ 			if(chunkIds) {
/******/ 				priority = priority || 0;
/******/ 				for(var i = deferred.length; i > 0 && deferred[i - 1][2] > priority; i--) deferred[i] = deferred[i - 1];
/******/ 				deferred[i] = [chunkIds, fn, priority];
/******/ 				return;
/******/ 			}
/******/ 			var notFulfilled = Infinity;
/******/ 			for (var i = 0; i < deferred.length; i++) {
/******/ 				var [chunkIds, fn, priority] = deferred[i];
/******/ 				var fulfilled = true;
/******/ 				for (var j = 0; j < chunkIds.length; j++) {
/******/ 					if ((priority & 1 === 0 || notFulfilled >= priority) && Object.keys(__webpack_require__.O).every((key) => (__webpack_require__.O[key](chunkIds[j])))) {
/******/ 						chunkIds.splice(j--, 1);
/******/ 					} else {
/******/ 						fulfilled = false;
/******/ 						if(priority < notFulfilled) notFulfilled = priority;
/******/ 					}
/******/ 				}
/******/ 				if(fulfilled) {
/******/ 					deferred.splice(i--, 1)
/******/ 					var r = fn();
/******/ 					if (r !== undefined) result = r;
/******/ 				}
/******/ 			}
/******/ 			return result;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/jsonp chunk loading */
/******/ 	(() => {
/******/ 		// no baseURI
/******/ 		
/******/ 		// object to store loaded and loading chunks
/******/ 		// undefined = chunk not loaded, null = chunk preloaded/prefetched
/******/ 		// [resolve, reject, Promise] = chunk loading, 0 = chunk loaded
/******/ 		var installedChunks = {
/******/ 			"/assets/js/project.index": 0,
/******/ 			"assets/css/project.index": 0
/******/ 		};
/******/ 		
/******/ 		// no chunk on demand loading
/******/ 		
/******/ 		// no prefetching
/******/ 		
/******/ 		// no preloaded
/******/ 		
/******/ 		// no HMR
/******/ 		
/******/ 		// no HMR manifest
/******/ 		
/******/ 		__webpack_require__.O.j = (chunkId) => (installedChunks[chunkId] === 0);
/******/ 		
/******/ 		// install a JSONP callback for chunk loading
/******/ 		var webpackJsonpCallback = (parentChunkLoadingFunction, data) => {
/******/ 			var [chunkIds, moreModules, runtime] = data;
/******/ 			// add "moreModules" to the modules object,
/******/ 			// then flag all "chunkIds" as loaded and fire callback
/******/ 			var moduleId, chunkId, i = 0;
/******/ 			if(chunkIds.some((id) => (installedChunks[id] !== 0))) {
/******/ 				for(moduleId in moreModules) {
/******/ 					if(__webpack_require__.o(moreModules, moduleId)) {
/******/ 						__webpack_require__.m[moduleId] = moreModules[moduleId];
/******/ 					}
/******/ 				}
/******/ 				if(runtime) var result = runtime(__webpack_require__);
/******/ 			}
/******/ 			if(parentChunkLoadingFunction) parentChunkLoadingFunction(data);
/******/ 			for(;i < chunkIds.length; i++) {
/******/ 				chunkId = chunkIds[i];
/******/ 				if(__webpack_require__.o(installedChunks, chunkId) && installedChunks[chunkId]) {
/******/ 					installedChunks[chunkId][0]();
/******/ 				}
/******/ 				installedChunks[chunkId] = 0;
/******/ 			}
/******/ 			return __webpack_require__.O(result);
/******/ 		}
/******/ 		
/******/ 		var chunkLoadingGlobal = self["webpackChunk"] = self["webpackChunk"] || [];
/******/ 		chunkLoadingGlobal.forEach(webpackJsonpCallback.bind(null, 0));
/******/ 		chunkLoadingGlobal.push = webpackJsonpCallback.bind(null, chunkLoadingGlobal.push.bind(chunkLoadingGlobal));
/******/ 	})();
/******/ 	
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module depends on other loaded chunks and execution need to be delayed
/******/ 	__webpack_require__.O(undefined, ["assets/css/project.index"], () => (__webpack_require__("./Modules/Project/Resources/assets/js/project.index.js")))
/******/ 	var __webpack_exports__ = __webpack_require__.O(undefined, ["assets/css/project.index"], () => (__webpack_require__("./Modules/Project/Resources/assets/css/project.index.scss")))
/******/ 	__webpack_exports__ = __webpack_require__.O(__webpack_exports__);
/******/ 	
/******/ })()
;