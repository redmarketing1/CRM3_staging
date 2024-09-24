/******/ (() => { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ "./Modules/Project/Resources/assets/js/project.index.js":
/*!**************************************************************!*\
  !*** ./Modules/Project/Resources/assets/js/project.index.js ***!
  \**************************************************************/
/***/ (() => {

function _toConsumableArray(r) { return _arrayWithoutHoles(r) || _iterableToArray(r) || _unsupportedIterableToArray(r) || _nonIterableSpread(); }
function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _iterableToArray(r) { if ("undefined" != typeof Symbol && null != r[Symbol.iterator] || null != r["@@iterator"]) return Array.from(r); }
function _arrayWithoutHoles(r) { if (Array.isArray(r)) return _arrayLikeToArray(r); }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
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
      className: 'dt-body-center',
      render: function render(data, type, full, meta) {
        return '<input type="checkbox" class="row-select-checkbox" value="' + data.id + '">';
      }
    }, {
      data: 'thumbnail',
      name: 'thumbnail'
    }, {
      data: 'name',
      name: 'name',
      orderable: true
    }, {
      data: 'is_archive',
      name: 'is_archive',
      visible: false
    }, {
      data: 'status',
      name: 'status',
      defaultContent: 'N/A',
      orderable: true
    }, {
      data: 'comments',
      name: 'comments',
      defaultContent: 'N/A',
      orderable: false
    }, {
      data: 'priority',
      name: 'priority',
      defaultContent: 'N/A',
      orderable: false
    }, {
      data: 'construction',
      name: 'construction',
      defaultContent: 'N/A',
      orderable: false
    }, {
      data: 'budget',
      name: 'budget',
      orderable: true
    }, {
      data: 'created_at',
      name: 'created_at',
      orderable: true
    }, {
      data: 'action',
      name: 'action',
      orderable: false,
      searchable: false
    }],
    initComplete: function initComplete(settings, _ref) {
      var filterableStatusList = _ref.filterableStatusList,
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
          multiple: false,
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
          minimumResultsForSearch: Infinity
        });
      }
      var maxBudget = 0;
      table.rows().every(function (rowIdx, tableLoop, rowLoop) {
        var data = this.data();
        var projectBudget = parseFloat(data.budget);
        if (!isNaN(projectBudget) && projectBudget > maxBudget) {
          maxBudget = projectBudget;
        }
      });
      $('.range-input-selector,#filter_budget_from,#filter_budget_to').attr('max', maxBudget);
      $('.range-input-selector,#filter_budget_to').val(maxBudget);
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
  $(document).on('input', '#searchProject, #filter_budget_from, #filter_budget_to', function (e) {
    table.draw();
  });
  $(document).on('mouseup', '.range-input-selector', function (e) {
    $(this).removeClass('increased-width');
    table.draw();
    table.order([8, 'desc']).draw();
  });
  $('.range-input-selector').on('mousedown', function () {
    $(this).addClass('increased-width');
  });
  $(document).on('change', '#filterableStatusDropdown, #filterablePriorityDropdown, #filterableDaterange, #projectVisibality', function () {
    table.draw();
  });
  $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
    var selectedStatus = removeWhitespace($('#status-tabs .active').data('status-name')).toLowerCase();
    var selectedVisibility = $('#projectVisibality').val() || 'only-active';
    var selectedDropdownStatus = $('#filterableStatusDropdown').val();
    var selectedDropdownPriority = removeWhitespace($('#filterablePriorityDropdown').val()).toLowerCase();
    var selectedDateRange = $('#filterableDaterange').val();
    var selectedProjectBudgetRange = $('.range-input-selector').val();
    var minBudget = parseFloat($('#filter_budget_from').val());
    var maxBudget = parseFloat($('#filter_budget_to').val());
    var searchProject = removeWhitespace($('#searchProject').val()).toLowerCase();
    var projectName = removeWhitespace(data[2]).toLowerCase();
    var projectComment = removeWhitespace(data[5]).toLowerCase();
    var isArchived = parseInt(data[3], 10); // 1 for archived, 0 for not archived
    var projectStatus = removeWhitespace(data[4]).toLowerCase();
    var projectPriority = removeWhitespace(data[6]).toLowerCase();
    var projectBudget = parseFloat(data[8]);
    var projectCreatedAt = data[9];

    /**
     * Check if the project is archived; 
     * we only want to show active projects by default
     */
    if (selectedVisibility === 'only-active' && isArchived === 1 || selectedVisibility === 'only-archive' && isArchived === 0) return false;
    $('#status-tabs').find('a')[selectedVisibility === 'only-archive' ? 'fadeOut' : 'fadeIn']();

    /**
     * Check if the project budget is within the range
     */
    if (selectedProjectBudgetRange <= projectBudget) return false;

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

    // Other Filters (Status, Priority, Project Name)
    if ((!selectedStatus || projectStatus === selectedStatus) && (!selectedDropdownStatus.length || selectedDropdownStatus.includes(projectStatus)) && (!selectedDropdownPriority || projectPriority === selectedDropdownPriority) && (searchProject === '' || projectName.indexOf(searchProject) !== -1 || projectComment.indexOf(searchProject) !== -1)) {
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
              var rowData = value.data();
              rowData[3] = 1;
              value.data(rowData).draw();
            });
          }
          if (type === 'duplicate') {
            selectedRows.each(function (val, element) {
              var rowId = $(this).val();
              var rowData = table.row('#' + rowId).data();
              if (rowData) {
                // Check if rowData is defined
                var newRowData = _toConsumableArray(rowData);
                newRowData[0] = newRowData[0] + ' - Copy'; // Modify the project name
                newRowData[1] = null; // Reset ID or any other field as needed

                table.row.add(newRowData).draw();
              } else {
                console.error('Row data not found for ID:', rowId); // Log if rowData is not found
              }
            });
          }
          table.draw();
          $('input#select-all').prop('checked', false);
          $('#bulk-action-selector').fadeOut();
        });
      }
    });
  });
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