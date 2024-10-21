<<<<<<< HEAD
/******/ (() => { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ "./Modules/Project/Resources/assets/js/project.index.js":
/*!**************************************************************!*\
  !*** ./Modules/Project/Resources/assets/js/project.index.js ***!
  \**************************************************************/
/***/ (() => {

function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
$(document).ready(function () {
  function loadDataTables() {
    var table = $('#projectsTable').DataTable(_defineProperty(_defineProperty(_defineProperty(_defineProperty(_defineProperty(_defineProperty(_defineProperty(_defineProperty(_defineProperty(_defineProperty({
      pageLength: 50,
      lengthMenu: [[25, 50, 100, 200], [25, 50, 100, 200]],
      lengthChange: true,
      ordering: false,
      searching: true
    }, "ordering", false), "layout", {
      topEnd: null
    }), "select", {
      style: 'multi'
    }), "pagingType", 'simple'), "language", {
      paginate: {
        previous: 'Previous',
        next: 'Next'
      }
    }), "processing", false), "serverSide", false), "ajax", {
      url: route('project.index'),
      type: 'GET',
      dataType: 'json'
    }), "columns", [{
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
      data: 'status',
      name: 'status',
      visible: false,
      orderable: true,
      className: 'status'
    }, {
      data: 'name',
      name: 'name',
      orderable: true,
      className: 'name'
    }, {
      data: 'comments',
      name: 'comments',
      orderable: false,
      className: 'comments'
    }, {
      data: 'is_archive',
      name: 'is_archive',
      visible: false,
      className: 'is_archive'
    }, {
      data: 'priority',
      name: 'priority',
      orderable: false,
      className: 'priority'
    }, {
      data: 'construction',
      name: 'construction',
      orderable: false,
      className: 'construction'
    }, {
      data: 'budget',
      name: 'budget',
      orderable: true,
      className: 'budget',
      createdCell: function createdCell(td, cellData, rowData, row, col) {
        var isZero = parseInt(cellData);
        if (isZero == 0) {
          $(td).addClass('zero');
        }
      }
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
    }]), "initComplete", function initComplete(settings, _ref) {
      var data = _ref.data,
        filterableStatusList = _ref.filterableStatusList,
        filterablePriorityList = _ref.filterablePriorityList,
        minBudget = _ref.minBudget,
        maxBudget = _ref.maxBudget;
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

        /** 
            $('#filterableStatusDropdown').select2({
                data: selectData,
                placeholder: 'Select Status',
                multiple: true,
                allowClear: false,
                minimumResultsForSearch: Infinity,
                templateResult: formatOption,
                templateSelection: formatSelection
            });
        */
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
      $('#filter_price_from,#filter_price_to').attr('max', removeSymbol(maxBudget));
      $('#filter_price_to,.range-max').val(removeSymbol(maxBudget));
    }));

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
    $(document).on('click', '#projectContactDetailsToggle', function (e) {
      e.preventDefault();
      table.rows().every(function () {
        var rowNode = this.node();
        $(rowNode).find('.data-sub-name').toggle();
      });
      table.draw();
    });
    function handleBulkAction(type, title, text, selectedData, selectedRows, selectedType) {
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
                var row = $(this).val();
                $(row).remove();
                table.row(row).remove();
              });
            }
            if (type === 'archive' || type === 'unarchive') {
              selectedRows.each(function () {
                var rowId = $(this).val();
                var rowData = table.row('#' + rowId).data();
                rowData.is_archive = type === 'archive' ? 1 : 0;
                table.row('#' + rowId).data(rowData).draw();
              });
            }
            if (type === 'duplicate') {
              selectedRows.each(function () {
                var rowId = $(this).val();
                var rowData = table.row('#' + rowId).data();
                table.row.add(rowData).draw();
              });
            }
            if (selectedType === 'select') {
              $('input#select-all,.row-select-checkbox').prop('checked', false);
              $('#bulk-action-selector').val('bulk').fadeOut();
            }
          });
        }
      });
    }
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
      selectedRows.each(function () {
        selectedData.push($(this).val());
      });
      handleBulkAction(type, title, text, selectedData, selectedRows, 'select');
    });

    // Handle button click (e.g., delete button)
    $(document).on('click', '.action-btn button', function (e) {
      e.preventDefault();
      var button = $(this);
      var id = $(this).val();
      var type = button.data('type');
      var title = button.data('title');
      var text = button.data('text');
      var selectedRows = button;
      var selectedData = [id];
      handleBulkAction(type, title, text, selectedData, selectedRows, 'click');
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
    function findByTabStatus(data) {
      var activeTabs = $('#status-tabs .active');
      var projectStatus = removeWhitespace(data[2] || '').toLowerCase();
      projectStatus = removeSelectedWords(projectStatus);
      var searchProject = removeWhitespace($('#searchProject').val()).toLowerCase();
      if (searchProject.length > 0) {
        return true;
      }
      if (activeTabs.length === 0) {
        $('#status-tabs a').removeClass('active');
        $('#status-tabs a').first().addClass('active');
        return true;
      }
      var selectedStatus = removeWhitespace(activeTabs.map(function () {
        return $(this).data('status-name');
      }).get().join(' ')).toLowerCase();
      if (selectedStatus === '' || selectedStatus === 'all') return true;

      /** Disable multiple tab selector
      if (activeTabs.length > 1) {
          $('#status-tabs a').first().removeClass('active');
          return selectedStatus.includes(projectStatus)
      }*/

      return selectedStatus === projectStatus;
    }
    function projectVisibility(data) {
      var selectedVisibility = $('#projectVisibality').val() || 'only-active';
      var isArchived = parseInt(data[5], 10);
      if (selectedVisibility === 'only-archive') {
        $('#status-tabs').find('a').fadeOut().removeClass('active');
        $('#bulk-action-selector').find('option[value=archive]').hide();
        $('#bulk-action-selector').find('option[value=unarchive]').show();
      } else {
        $('#status-tabs').find('a').fadeIn();
        $('#bulk-action-selector').find('option[value=archive]').show();
        $('#bulk-action-selector').find('option[value=unarchive]').hide();
      }
      if (selectedVisibility === 'only-active' && isArchived === 1 || selectedVisibility === 'only-archive' && isArchived === 0) {
        return false;
      } else {
        return true;
      }
    }
    function findByMultipleStatus(data) {
      /** Don't remove will do later */
      var selectedStatus = removeWhitespace($('#filterableStatusDropdown').val() || []).toLowerCase();
      var projectStatus = removeWhitespace(data[2]).toLowerCase();
      if (selectedStatus.length == 0) {
        return true;
      }
      if (selectedStatus.length > 0 && selectedStatus.includes(projectStatus)) {
        $('#status-tabs a.active').removeClass('active');
        return true;
      } else {
        return false;
      }
    }
    function findByNameANDComment(data) {
      var searchProject = removeWhitespace($('#searchProject').val()).toLowerCase();
      var projectName = removeWhitespace(data[3]).toLowerCase();
      var projectComment = removeWhitespace(data[4]).toLowerCase();
      if (searchProject.length > 0 && projectName.indexOf(searchProject) !== -1 || projectComment.indexOf(searchProject) !== -1) return true;
      return false;
    }
    function findByPriority(data) {
      var selectedPriority = $('#filterablePriorityDropdown').val() || [];
      var projectPriority = removeWhitespace(data[6]).toLowerCase();
      if (selectedPriority.length === 0) {
        return true;
      }
      if (selectedPriority.length > 0 && selectedPriority.some(function (priority) {
        return priority === projectPriority;
      })) {
        return true;
      }
    }
    function findByDateRange(data) {
      var selectedDateRange = $('#filterableDaterange').val();
      var projectCreatedAt = data[9];
      if (selectedDateRange) {
        var dateRange = selectedDateRange.split(' - ');
        var startDate = moment(dateRange[0], 'MM/DD/YYYY');
        var endDate = moment(dateRange[1], 'MM/DD/YYYY');
        var projectDate = moment(projectCreatedAt, 'DD-MM-YYYY HH:mm');
        if (!projectDate.isBetween(startDate, endDate, undefined, '[]')) {
          return false;
        }
      }
      return true;
    }
    function findByBudgetRnage(data) {
      var minBudget = parseFloat($('#filter_price_from').val());
      var maxBudget = parseFloat($('#filter_price_to').val());
      var projectBudget = parseFloat(data[8]);
      if (minBudget && projectBudget <= minBudget || maxBudget && projectBudget >= maxBudget) {
        return false;
      } else {
        return true;
      }
    }
    $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
      return findByTabStatus(data) && projectVisibility(data) && findByNameANDComment(data) && findByPriority(data) && findByBudgetRnage(data) && findByDateRange(data);
    });

    // const rangeInput = $(".range-input input");
    // const priceInput = $(".price-input input");
    // const range = $(".slider_filter .progress");
    // let priceGap = 1;

    // // Update range input values based on price inputs
    // priceInput.on('input', function () {
    //     let minPrice = parseInt(priceInput.eq(0).val());
    //     let maxPrice = parseInt(priceInput.eq(1).val());

    //     if (maxPrice - minPrice >= priceGap && maxPrice <= parseInt(rangeInput.eq(1).attr('max'))) {
    //         if ($(this).hasClass("input-min")) {
    //             rangeInput.eq(0).val(minPrice);
    //             range.css('left', (minPrice / parseInt(rangeInput.eq(0).attr('max'))) * 100 + "%");
    //         } else {
    //             rangeInput.eq(1).val(maxPrice);
    //             range.css('right', 100 - (maxPrice / parseInt(rangeInput.eq(1).attr('max'))) * 100 + "%");
    //         }
    //     }
    // });

    // // Update price inputs based on range input values
    // rangeInput.on('input', function () {
    //     let minVal = parseInt(rangeInput.eq(0).val());
    //     let maxVal = parseInt(rangeInput.eq(1).val());

    //     if (maxVal - minVal < priceGap) {
    //         if ($(this).hasClass("range-min")) {
    //             rangeInput.eq(0).val(maxVal - priceGap);
    //         } else {
    //             rangeInput.eq(1).val(minVal + priceGap);
    //         }
    //     } else {
    //         priceInput.eq(0).val(minVal);
    //         priceInput.eq(1).val(maxVal);
    //         range.css('left', (minVal / parseInt(rangeInput.eq(0).attr('max'))) * 100 + "%");

    //         // Calculate width and set it to the progress bar
    //         let width = (maxVal - minVal) / parseInt(rangeInput.eq(1).attr('max')) * 100 + "%";
    //         range.css('width', width);
    //     }
    // });

    function removeSymbol(input) {
      return input.replace(/[€\s]/g, '');
    }
    function removeSelectedWords(inputWord) {
      var wordsToRemove = ["lvprufen!", "ruckruf"];
      var regex = new RegExp("(".concat(wordsToRemove.join('|'), ")"), 'gi');
      return inputWord.replace(regex, '').replace(/\s+/g, ' ').trim();
    }
  }
  loadDataTables();
  function loadTabMenuPagination() {
    var projectsPerPage = 25;
    function initPaginationForTab(tabContentId) {
      var totalProjects = document.querySelectorAll("#".concat(tabContentId, " a.tab-link")).length;
      var currentVisibleProjects = projectsPerPage;
      function hideProjects(fromIndex) {
        var projectItems = document.querySelectorAll("#".concat(tabContentId, " .tab-item"));
        for (var i = fromIndex; i < totalProjects; i++) {
          projectItems[i].style.display = 'none';
        }
      }
      function showProjects(fromIndex, toIndex) {
        var projectItems = document.querySelectorAll("#".concat(tabContentId, " .tab-item"));
        for (var i = fromIndex; i < toIndex && i < totalProjects; i++) {
          projectItems[i].style.display = 'list-item';
        }
      }
      function updateButtonLabel(number, isLess) {
        var btn = document.querySelector("#".concat(tabContentId, " .pagination-btn"));
        if (isLess) {
          btn.innerText = "Show Less Projects";
        } else {
          btn.innerText = "Show More ".concat(number, " Projects");
        }
      }

      // Create the button dynamically if totalProjects exceeds projectsPerPage
      if (totalProjects > projectsPerPage) {
        var tabContent = document.getElementById(tabContentId);
        var button = document.createElement('div');
        button.classList = 'pagination-btn font-semibold mb-3 mt-3 pointer text-center';
        button.innerText = "Show More ".concat(projectsPerPage, " Projects");
        tabContent.appendChild(button); // Append the button to the tab content

        button.addEventListener('click', function (event) {
          event.preventDefault();
          if (currentVisibleProjects >= totalProjects) {
            currentVisibleProjects = projectsPerPage;
            hideProjects(currentVisibleProjects);
            updateButtonLabel(projectsPerPage, false);
          } else {
            var nextVisible = currentVisibleProjects + projectsPerPage;
            nextVisible = Math.min(nextVisible, totalProjects);
            showProjects(currentVisibleProjects, nextVisible);
            currentVisibleProjects = nextVisible;
            if (currentVisibleProjects >= totalProjects) {
              updateButtonLabel(0, true);
            } else {
              updateButtonLabel(projectsPerPage, false);
            }
          }
        });
        hideProjects(currentVisibleProjects); // Hide projects exceeding projectsPerPage
        updateButtonLabel(projectsPerPage, false);
      } else {
        // If totalProjects is less than or equal to projectsPerPage, show all projects
        var projectItems = document.querySelectorAll("#".concat(tabContentId, " .tab-item"));
        projectItems.forEach(function (item) {
          item.style.display = 'list-item';
        });
      }
    }
    $('#project a.nav-link').each(function () {
      var tabContentId = $(this).attr('id').replace('tab-', '');
      initPaginationForTab(tabContentId);
    });
  }
  $('#searchInput').on('input', function () {
    var searchTerm = $(this).val().toLowerCase();
    if (searchTerm.length <= 0) {
      $('.pagination-btn').show();
      return;
    }
    ;
    if (!$('#allprojects').hasClass('active')) {
      $('.tab-pane.fade').removeClass('active show');
      $('#allprojects').addClass('active show');
    }
    $('#allprojects li.tab-item').each(function () {
      var projectName = $(this).find('a').text().toLowerCase();
      var isMatch = projectName.includes(searchTerm);
      $(this).toggle(isMatch);
      $('.pagination-btn').hide();
    });
  });
  loadTabMenuPagination();

  /** call ajaxComplete after open data-popup **/
  // $(document).ajaxComplete(function (event, xhr, settings) {

  //     function loadQuickViewer(projectID) {

  //         $('#changeProjectMember').select2({
  //             placeholder: "Nutzer wählen",
  //             tags: true,
  //             allowHtml: true,
  //             templateSelection: function (data, container) {
  //                 $(container).css("background-color", $(data.element).data("background_color"));
  //                 if (data.element) {
  //                     $(container).css("color", $(data.element).data("font_color"));
  //                 }
  //                 return data.text;
  //             }
  //         })
  //             .on('select2:open', function () {
  //                 $('.select2-container.select2-container--open').css({
  //                     zIndex: 99999999,
  //                 });
  //             })
  //             .on('change', function (event) {
  //                 const projectID = $(this).data('projectid');
  //                 const userID = $(this).val();

  //                 $.ajax({
  //                     url: route('project.member.add', projectID),
  //                     type: "POST",
  //                     data: { users: userID },
  //                     success: function (data) {
  //                         if (data.is_success) {
  //                             $('.projectteamcount').html(data.count);
  //                             toastrs('Success', data.message, 'success');
  //                         } else {
  //                             toastrs('Error', data.message, 'error');
  //                         }
  //                     },
  //                     error: function (jqXHR, textStatus, errorThrown) {
  //                         toastrs('Error', 'Something went wrong: ' + textStatus, 'error');
  //                     }
  //                 });
  //             });

  //         $.ajax({
  //             url: route('project.get_all_address', projectID),
  //             type: "POST",
  //             data: { html: true },
  //             success: function (response) {
  //                 if (response.status == true) {
  //                     $(".project_all_address").html(response.html_data);
  //                 }
  //             }
  //         });

  //         $('.filter_select2').select2({
  //             placeholder: "Select",
  //             multiple: true,
  //             tags: true,
  //             templateSelection: function (data, container) {
  //                 $(container).css("background-color", $(data.element).data("background_color"));
  //                 if (data.element) {
  //                     $(container).css("color", $(data.element).data("font_color"));
  //                 }
  //                 return data.text;
  //             }
  //         }).on('select2:open', function () {
  //             $('.select2-container.select2-container--open').css({
  //                 zIndex: 99999999,
  //             });
  //         });

  //         $(document).on('change', '.filter_select2', function (event) {
  //             const labelType = $(this).data('labeltype');
  //             const ids = $(this).val().join(", ");
  //             if (!labelType && !ids) return;

  //             $.ajax({
  //                 url: route('project.add.status_data', projectID),
  //                 type: "POST",
  //                 data: {
  //                     field: labelType,
  //                     field_value: ids
  //                 },
  //                 success: function (data) {
  //                     if (data.is_success) {
  //                         toastrs('Success', data.message, 'success');
  //                     } else {
  //                         toastrs('Error', data.message, 'error');
  //                     }
  //                 }
  //             });
  //         });

  //         $(document).on('change', '#construction-select', function () {
  //             var selectedOption = $('#construction-select option:selected');
  //             var selectedType = selectedOption.data('type');
  //             var clientTypeInput = document.getElementById('client_type1');

  //             if (selectedType !== undefined && selectedType !== null) {
  //                 clientTypeInput.value = selectedType;
  //             } else {
  //                 clientTypeInput.value = 'new';
  //             }

  //             var url = route('users.get_user');
  //             var user_id = this.value;

  //             // Get the selected values
  //             if (user_id) {
  //                 axios.post(url, {
  //                     'user_id': user_id,
  //                     'from': 'construction'
  //                 }).then((response) => {

  //                     var clientDetailsElement = document.getElementById('construction-details');

  //                     $('#construction-details').html(response.data.html_data);
  //                     $('#construction_detail_id').val(response.data.user_id);

  //                     if ($('#construction_detail-company_notes').length > 0) {
  //                         init_tiny_mce('#construction_detail-company_notes');
  //                     }

  //                     // Remove the d-none class if the element is found
  //                     if (clientDetailsElement) {
  //                         clientDetailsElement.classList.remove('d-none');
  //                     }

  //                     initGoogleMapPlaced('construction_detail-autocomplete', 'construction_detail');

  //                     $(".country_select2").select2({
  //                         placeholder: "Country",
  //                         multiple: false,
  //                         dropdownParent: $("#title_form"),
  //                         placeholder: "Select an country",
  //                         allowClear: true,
  //                         dropdownAutoWidth: true,
  //                     });
  //                 })
  //             } else {
  //                 var clientDetailsElement = document.getElementById('construction-details');
  //                 // Remove the d-none class if the element is found
  //                 if (clientDetailsElement) {
  //                     clientDetailsElement.classList.add('d-none');
  //                 }
  //             }
  //         });

  //         $(document).on('change', '#client-select', function () {
  //             var selectedOption = $('#client-select option:selected');
  //             var selectedType = selectedOption.data('type');
  //             var clientTypeInput = document.getElementById('client_type');

  //             if (selectedType !== undefined && selectedType !== null) {
  //                 clientTypeInput.value = selectedType;
  //             } else {
  //                 clientTypeInput.value = 'new';
  //             }
  //             var url;

  //             var url = route('users.get_user');

  //             init_tiny_mce('.client-company_notes');

  //             // Get the selected values
  //             if (this.value) {
  //                 axios.post(url, {
  //                     'user_id': this.value,
  //                     'from': 'client'
  //                 }).then((response) => {
  //                     var clientDetailsElement = document.getElementById('client-details');

  //                     $('#client-details').html(response.data.html_data);
  //                     $('#client_id').val(response.data.user_id);
  //                     // initialize();

  //                     if ($('#client-company_notes').length > 0) {
  //                         init_tiny_mce('#client-company_notes');
  //                     }

  //                     // Remove the d-none class if the element is found
  //                     if (clientDetailsElement) {
  //                         clientDetailsElement.classList.remove('d-none');
  //                     }

  //                     initGoogleMapPlaced('invoice-autocomplete', 'invoice');

  //                 })
  //             } else {
  //                 var clientDetailsElement = document.getElementById('client-details');
  //                 // Remove the d-none class if the element is found
  //                 if (clientDetailsElement) {
  //                     clientDetailsElement.classList.add('d-none');
  //                 }
  //             }
  //         });
  //     }
  //     if (settings.url.includes('project/quick-view')) {
  //         const url = settings.url.split('?')[0];
  //         const triggerElement = $('a[data-url="' + url + '"]');
  //         const projectID = triggerElement.data('projectid');

  //         loadQuickViewer(projectID);
  //     }
  // });
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


/***/ }),

/***/ "./Modules/Project/Resources/assets/css/project.show.scss":
/*!****************************************************************!*\
  !*** ./Modules/Project/Resources/assets/css/project.show.scss ***!
  \****************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "./Modules/Project/Resources/assets/css/project.maps.scss":
/*!****************************************************************!*\
  !*** ./Modules/Project/Resources/assets/css/project.maps.scss ***!
  \****************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "./Modules/Project/Resources/assets/css/project.quickView.scss":
/*!*********************************************************************!*\
  !*** ./Modules/Project/Resources/assets/css/project.quickView.scss ***!
  \*********************************************************************/
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
/******/ 			"assets/css/project.quickView": 0,
/******/ 			"assets/css/project.maps": 0,
/******/ 			"assets/css/project.show": 0,
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
/******/ 	__webpack_require__.O(undefined, ["assets/css/project.quickView","assets/css/project.maps","assets/css/project.show","assets/css/project.index"], () => (__webpack_require__("./Modules/Project/Resources/assets/js/project.index.js")))
/******/ 	__webpack_require__.O(undefined, ["assets/css/project.quickView","assets/css/project.maps","assets/css/project.show","assets/css/project.index"], () => (__webpack_require__("./Modules/Project/Resources/assets/css/project.index.scss")))
/******/ 	__webpack_require__.O(undefined, ["assets/css/project.quickView","assets/css/project.maps","assets/css/project.show","assets/css/project.index"], () => (__webpack_require__("./Modules/Project/Resources/assets/css/project.show.scss")))
/******/ 	__webpack_require__.O(undefined, ["assets/css/project.quickView","assets/css/project.maps","assets/css/project.show","assets/css/project.index"], () => (__webpack_require__("./Modules/Project/Resources/assets/css/project.maps.scss")))
/******/ 	var __webpack_exports__ = __webpack_require__.O(undefined, ["assets/css/project.quickView","assets/css/project.maps","assets/css/project.show","assets/css/project.index"], () => (__webpack_require__("./Modules/Project/Resources/assets/css/project.quickView.scss")))
/******/ 	__webpack_exports__ = __webpack_require__.O(__webpack_exports__);
/******/ 	
/******/ })()
;
=======
(()=>{var e,t={190:()=>{function e(t){return e="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e},e(t)}function t(t,a,o){return(a=function(t){var a=function(t,a){if("object"!=e(t)||!t)return t;var o=t[Symbol.toPrimitive];if(void 0!==o){var r=o.call(t,a||"default");if("object"!=e(r))return r;throw new TypeError("@@toPrimitive must return a primitive value.")}return("string"===a?String:Number)(t)}(t,"string");return"symbol"==e(a)?a:a+""}(a))in t?Object.defineProperty(t,a,{value:o,enumerable:!0,configurable:!0,writable:!0}):t[a]=o,t}$(document).ready((function(){!function(){var e=$("#projectsTable").DataTable(t(t(t(t(t(t(t(t(t(t({pageLength:50,lengthMenu:[[25,50,100,200],[25,50,100,200]],lengthChange:!0,ordering:!1,searching:!0},"ordering",!1),"layout",{topEnd:null}),"select",{style:"multi"}),"pagingType","simple"),"language",{paginate:{previous:"Previous",next:"Next"}}),"processing",!1),"serverSide",!1),"ajax",{url:route("project.index"),type:"GET",dataType:"json"}),"columns",[{data:null,orderable:!1,className:"dt-body-center input-checkbox",render:function(e,t,a,o){return'<input type="checkbox" class="row-select-checkbox" value="'+e.id+'">'}},{data:"thumbnail",name:"thumbnail",className:"thumbnail"},{data:"status",name:"status",visible:!1,orderable:!0,className:"status"},{data:"name",name:"name",orderable:!0,className:"name"},{data:"comments",name:"comments",orderable:!1,className:"comments"},{data:"is_archive",name:"is_archive",visible:!1,className:"is_archive"},{data:"priority",name:"priority",orderable:!1,className:"priority"},{data:"construction",name:"construction",orderable:!1,className:"construction"},{data:"budget",name:"budget",orderable:!0,className:"budget",createdCell:function(e,t,a,o,r){0==parseInt(t)&&$(e).addClass("zero")}},{data:"created_at",name:"created_at",orderable:!0,className:"created_at"},{data:"action",name:"action",orderable:!1,searchable:!1,className:"action"}]),"initComplete",(function(t,r){r.data;var n=r.filterableStatusList,i=r.filterablePriorityList,l=(r.minBudget,r.maxBudget);if($("#projectsTable colgroup").remove(),n.html){var s=$.parseHTML(n.html);$("#projectsTable").parents(".projectsTableContainter").parents("div.col-xl-12").before(s)}if(n.data)$.map(n.data,(function(e,t){return{id:removeWhitespace(e.name).toLowerCase(),text:e.name,backgroundColor:e.background_color,fontColor:e.font_color}}));if(i){var u=$.map(i,(function(e,t){return{id:e.name,text:e.name,backgroundColor:e.background_color,fontColor:e.font_color}}));$("#filterablePriorityDropdown").select2({data:u,placeholder:"Select Priority",multiple:!0,allowClear:!1,minimumResultsForSearch:1/0,templateResult:a,templateSelection:o})}$("#projectsTable tr:first-child.hide").fadeIn(),$(".daterange").daterangepicker({autoUpdateInput:!1,locale:{cancelLabel:"clear"},ranges:{Today:[moment(),moment()],Yesterday:[moment().subtract(1,"days"),moment().subtract(1,"days")],"This Week":[moment().subtract(6,"days"),moment()],"This Month":[moment().startOf("month"),moment().endOf("month")],"Last 30 Days":[moment().subtract(29,"days"),moment()],"Last Month":[moment().subtract(1,"month").startOf("month"),moment().subtract(1,"month").endOf("month")]}}).on("apply.daterangepicker",(function(t,a){$(this).val(a.startDate.format("MM/DD/YYYY")+" - "+a.endDate.format("MM/DD/YYYY")),e.draw()})).on("cancel.daterangepicker",(function(t,a){$(this).val(""),e.draw()})),$("#projectsTable thead tr:nth-child(2) th:first-child").html('<input type="checkbox" id="select-all">'),$("#projectsTable tbody").on("change",".row-select-checkbox",(function(){$("input.row-select-checkbox:checked").length>0?$("#bulk-action-selector").fadeIn():$("#bulk-action-selector").fadeOut()})),$("#select-all").on("change",(function(){var e=this.checked;$("input.row-select-checkbox").prop("checked",e),$("input.row-select-checkbox:checked").length>0?$("#bulk-action-selector").fadeIn():$("#bulk-action-selector").fadeOut()})),$(".projects-filters .select2").select2({minimumResultsForSearch:1/0}),$("#filter_price_from,#filter_price_to").attr("max",c(l)),$("#filter_price_to,.range-max").val(c(l))})));function a(e){return e.id?$("<span>").css({"background-color":e.backgroundColor,color:e.fontColor,padding:"5px","border-radius":"4px",display:"block"}).text(e.text):e.text}function o(e){return e.id?$("<span>").css({"background-color":e.backgroundColor,color:e.fontColor,padding:"5px","border-radius":"4px",display:"block"}).text(e.text):e.text}function r(t,a,o,r,n,c){Swal.fire({title:a,text:o,showCancelButton:!0,confirmButtonText:"Yes, ".concat(t," it"),cancelButtonText:"No, cancel"}).then((function(a){if(a.isConfirmed){$.ajax({url:route("project.update",1),type:"PUT",data:{type:t,ids:r},success:function(e){console.log(e)}});var o,i=1,l=r.length;Swal.fire({icon:"success",title:"".concat(t.charAt(0).toUpperCase()+t.slice(1)," Successful!"),html:"<b>".concat(i,"</b> project").concat(l>1?"s":""," have been moved to ").concat(t),timer:2e3,timerProgressBar:!0,showConfirmButton:!1,didOpen:function(){Swal.showLoading();var e=Swal.getHtmlContainer().querySelector("b");o=setInterval((function(){i<l?(i++,e.textContent="".concat(i)):clearInterval(o)}),100)},willClose:function(){clearInterval(o)}}).then((function(){"delete"===t&&n.each((function(){var t=$(this).val();$(t).remove(),e.row(t).remove()})),"archive"!==t&&"unarchive"!==t||n.each((function(){var a=$(this).val(),o=e.row("#"+a).data();o.is_archive="archive"===t?1:0,e.row("#"+a).data(o).draw()})),"duplicate"===t&&n.each((function(){var t=$(this).val(),a=e.row("#"+t).data();e.row.add(a).draw()})),"select"===c&&($("input#select-all,.row-select-checkbox").prop("checked",!1),$("#bulk-action-selector").val("bulk").fadeOut())}))}}))}function n(e){var t,a,o=$("#status-tabs .active"),r=removeWhitespace(e[2]||"").toLowerCase();if(t=r,a=new RegExp("(".concat(["lvprufen!","ruckruf"].join("|"),")"),"gi"),r=t.replace(a,"").replace(/\s+/g," ").trim(),removeWhitespace($("#searchProject").val()).toLowerCase().length>0)return!0;if(0===o.length)return $("#status-tabs a").removeClass("active"),$("#status-tabs a").first().addClass("active"),!0;var n=removeWhitespace(o.map((function(){return $(this).data("status-name")})).get().join(" ")).toLowerCase();return""===n||"all"===n||n===r}function c(e){return e.replace(/[€\s]/g,"")}$(document).on("click","#status-tabs a",(function(t){t.preventDefault(),$("#status-tabs a").removeClass("active"),$(this).addClass("active"),e.draw()})),$(document).on("input","#searchProject, #filter_price_from, #filter_price_to",(function(t){e.draw()})),$(document).on("change","#filterableStatusDropdown, #filterablePriorityDropdown, #filterableDaterange, #projectVisibality",(function(){e.draw()})),$(document).on("click","#projectContactDetailsToggle",(function(t){t.preventDefault(),e.rows().every((function(){var e=this.node();$(e).find(".data-sub-name").toggle()})),e.draw()})),$(document).on("change","#bulk-action-selector",(function(){var e=$(this).find("option:selected"),t=e.val();if(t&&"Bulk actions"!==t){var a=e.data("title"),o=e.data("text"),n=e.data("type"),c=$("input.row-select-checkbox:checked"),i=[];c.each((function(){i.push($(this).val())})),r(n,a,o,i,c,"select")}})),$(document).on("click",".action-btn button",(function(e){e.preventDefault();var t=$(this),a=$(this).val();r(t.data("type"),t.data("title"),t.data("text"),[a],t,"click")})),$(document).on("click","#clearFilter",(function(){$("input#select-all,.row-select-checkbox").prop("checked",!1),$("#bulk-action-selector").val("bulk"),$("#status-tabs a").removeClass("active"),$("#searchProject,#filterableStatusDropdown,#filterablePriorityDropdown,#filterableDaterange").val(null).trigger("change"),$("#filter_price_from").val(0),$("#filter_price_to,.range-max").val($("#filter_price_to").attr("max")),e.draw()})),$.fn.dataTable.ext.search.push((function(e,t,a){return n(t)&&function(e){var t=$("#projectVisibality").val()||"only-active",a=parseInt(e[5],10);return"only-archive"===t?($("#status-tabs").find("a").fadeOut().removeClass("active"),$("#bulk-action-selector").find("option[value=archive]").hide(),$("#bulk-action-selector").find("option[value=unarchive]").show()):($("#status-tabs").find("a").fadeIn(),$("#bulk-action-selector").find("option[value=archive]").show(),$("#bulk-action-selector").find("option[value=unarchive]").hide()),!("only-active"===t&&1===a||"only-archive"===t&&0===a)}(t)&&function(e){var t=removeWhitespace($("#searchProject").val()).toLowerCase(),a=removeWhitespace(e[3]).toLowerCase(),o=removeWhitespace(e[4]).toLowerCase();return t.length>0&&-1!==a.indexOf(t)||-1!==o.indexOf(t)}(t)&&function(e){var t=$("#filterablePriorityDropdown").val()||[],a=removeWhitespace(e[6]).toLowerCase();return 0===t.length||!!(t.length>0&&t.some((function(e){return e===a})))||void 0}(t)&&function(e){var t=parseFloat($("#filter_price_from").val()),a=parseFloat($("#filter_price_to").val()),o=parseFloat(e[8]);return!(t&&o<=t||a&&o>=a)}(t)&&function(e){var t=$("#filterableDaterange").val(),a=e[9];if(t){var o=t.split(" - "),r=moment(o[0],"MM/DD/YYYY"),n=moment(o[1],"MM/DD/YYYY");if(!moment(a,"DD-MM-YYYY HH:mm").isBetween(r,n,void 0,"[]"))return!1}return!0}(t)}))}(),$("#searchInput").on("input",(function(){var e=$(this).val().toLowerCase();e.length<=0?$(".pagination-btn").show():($("#allprojects").hasClass("active")||($(".tab-pane.fade").removeClass("active show"),$("#allprojects").addClass("active show")),$("#allprojects li.tab-item").each((function(){var t=$(this).find("a").text().toLowerCase().includes(e);$(this).toggle(t),$(".pagination-btn").hide()})))})),function(){var e=25;function t(t){var a=document.querySelectorAll("#".concat(t," a.tab-link")).length,o=e;function r(e){for(var o=document.querySelectorAll("#".concat(t," .tab-item")),r=e;r<a;r++)o[r].style.display="none"}function n(e,a){var o=document.querySelector("#".concat(t," .pagination-btn"));o.innerText=a?"Show Less Projects":"Show More ".concat(e," Projects")}if(a>e){var c=document.getElementById(t),i=document.createElement("div");i.classList="pagination-btn font-semibold mb-3 mt-3 pointer text-center",i.innerText="Show More ".concat(e," Projects"),c.appendChild(i),i.addEventListener("click",(function(c){if(c.preventDefault(),o>=a)r(o=e),n(e,!1);else{var i=o+e;i=Math.min(i,a),function(e,o){for(var r=document.querySelectorAll("#".concat(t," .tab-item")),n=e;n<o&&n<a;n++)r[n].style.display="list-item"}(o,i),(o=i)>=a?n(0,!0):n(e,!1)}})),r(o),n(e,!1)}else{document.querySelectorAll("#".concat(t," .tab-item")).forEach((function(e){e.style.display="list-item"}))}}$("#project a.nav-link").each((function(){t($(this).attr("id").replace("tab-",""))}))}()}))},822:()=>{},900:()=>{},663:()=>{},159:()=>{}},a={};function o(e){var r=a[e];if(void 0!==r)return r.exports;var n=a[e]={exports:{}};return t[e](n,n.exports,o),n.exports}o.m=t,e=[],o.O=(t,a,r,n)=>{if(!a){var c=1/0;for(u=0;u<e.length;u++){for(var[a,r,n]=e[u],i=!0,l=0;l<a.length;l++)(!1&n||c>=n)&&Object.keys(o.O).every((e=>o.O[e](a[l])))?a.splice(l--,1):(i=!1,n<c&&(c=n));if(i){e.splice(u--,1);var s=r();void 0!==s&&(t=s)}}return t}n=n||0;for(var u=e.length;u>0&&e[u-1][2]>n;u--)e[u]=e[u-1];e[u]=[a,r,n]},o.o=(e,t)=>Object.prototype.hasOwnProperty.call(e,t),(()=>{var e={393:0,718:0,941:0,819:0,380:0};o.O.j=t=>0===e[t];var t=(t,a)=>{var r,n,[c,i,l]=a,s=0;if(c.some((t=>0!==e[t]))){for(r in i)o.o(i,r)&&(o.m[r]=i[r]);if(l)var u=l(o)}for(t&&t(a);s<c.length;s++)n=c[s],o.o(e,n)&&e[n]&&e[n][0](),e[n]=0;return o.O(u)},a=self.webpackChunk=self.webpackChunk||[];a.forEach(t.bind(null,0)),a.push=t.bind(null,a.push.bind(a))})(),o.O(void 0,[718,941,819,380],(()=>o(190))),o.O(void 0,[718,941,819,380],(()=>o(822))),o.O(void 0,[718,941,819,380],(()=>o(900))),o.O(void 0,[718,941,819,380],(()=>o(663)));var r=o.O(void 0,[718,941,819,380],(()=>o(159)));r=o.O(r)})();
>>>>>>> 7520862 (from server)
