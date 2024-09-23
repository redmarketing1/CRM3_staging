$(document).ready(function () {

    var table = $('#projectsTable').DataTable({
        lengthChange: false,
        ordering: false,
        searching: true,
        layout: {
            topEnd: null
        },
        select: {
            style: 'multi'  // Enable multiple row selection
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
            dataType: 'json',
        },
        columns: [
            {
                data: null,
                orderable: false,
                className: 'dt-body-center',
                render: function (data, type, full, meta) {
                    return '<input type="checkbox" class="row-select-checkbox" value="' + data.id + '">';
                }
            },
            { data: 'thumbnail', name: 'thumbnail' },
            { data: 'name', name: 'name', orderable: true },
            { data: 'is_archive', name: 'is_archive', visible: false },
            { data: 'status', name: 'status', defaultContent: 'N/A', orderable: true },
            { data: 'comments', name: 'comments', defaultContent: 'N/A', orderable: false },
            { data: 'priority', name: 'priority', defaultContent: 'N/A', orderable: false },
            { data: 'construction', name: 'construction', defaultContent: 'N/A', orderable: false },
            { data: 'budget', name: 'budget', orderable: true },
            { data: 'created_at', name: 'created_at', orderable: true },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        initComplete: function (settings, { filterableStatusList, filterablePriorityList }) {

            $('#projectsTable colgroup').remove();

            if (filterableStatusList.html) {
                var htmlContent = $.parseHTML(filterableStatusList.html);
                $('#projectsTable')
                    .parents('.projectsTableContainter')
                    .parents('div.col-xl-12')
                    .before(htmlContent)
            }

            if (filterableStatusList.data) {
                const selectData = $.map(filterableStatusList.data, function (value, key) {
                    return {
                        id: removeWhitespace(value.name).toLowerCase(),
                        text: value.name,
                        backgroundColor: value.background_color,
                        fontColor: value.font_color
                    };
                });

                $('#filterableStatusDropdown').select2({
                    data: selectData,
                    multiple: true,
                    // templateResult: formatOption,
                    // templateSelection: formatSelection
                });
            }

            if (filterablePriorityList) {
                const selectData = $.map(filterablePriorityList, function (value, key) {
                    return {
                        id: value.name,
                        text: value.name,
                        backgroundColor: value.background_color,
                        fontColor: value.font_color
                    };
                });

                $('#filterablePriorityDropdown').select2({
                    data: selectData
                });
            }

            let maxBudget = 0;
            table.rows().every(function (rowIdx, tableLoop, rowLoop) {
                let data = this.data();
                let projectBudget = parseFloat(data.budget);
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
                    'Last Month': [moment().subtract(1, 'month').startOf('month'), moment()
                        .subtract(1, 'month').endOf('month')
                    ]
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

    $(document).on('input', '#searchByProjectName, #searchByComment, #filter_budget_from, #filter_budget_to', function (e) {
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

    $('#filterableStatusDropdown, #filterablePriorityDropdown, #filterableDaterange').on('change', function () {
        table.draw();
    });

    $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
        var selectedStatus = removeWhitespace($('#status-tabs .active').data('status-name')).toLowerCase();
        var selectedDropdownStatus = $('#filterableStatusDropdown').val();
        var selectedDropdownPriority = removeWhitespace($('#filterablePriorityDropdown').val()).toLowerCase();
        var selectedDateRange = $('#filterableDaterange').val();

        const selectedProjectBudgetRange = $('.range-input-selector').val();
        const minBudget = parseFloat($('#filter_budget_from').val());
        const maxBudget = parseFloat($('#filter_budget_to').val());

        var projectName = removeWhitespace(data[2]).toLowerCase();
        var projectComment = removeWhitespace(data[5]).toLowerCase();
        var isArchived = parseInt(data[3], 10);  // 1 for archived, 0 for not archived
        var projectStatus = removeWhitespace(data[4]).toLowerCase();
        var projectPriority = removeWhitespace(data[6]).toLowerCase();
        var projectBudget = parseFloat(data[8]);
        var projectCreatedAt = data[9];

        var searchByProjectName = removeWhitespace($('#searchByProjectName').val()).toLowerCase();
        var searchByProjectComment = removeWhitespace($('#searchByComment').val()).toLowerCase();


        // Check if the project budget is within the range
        if (selectedProjectBudgetRange <= projectBudget) {
            return false;
        }

        // Filter project bewteen price range
        if (minBudget && projectBudget <= minBudget || maxBudget && projectBudget >= maxBudget) {
            return false;
        }


        // Date Range Filter
        if (selectedDateRange) {
            var dateRange = selectedDateRange.split(' - ');
            var startDate = moment(dateRange[0], 'MM/DD/YYYY');
            var endDate = moment(dateRange[1], 'MM/DD/YYYY');
            var projectDate = moment(projectCreatedAt, 'DD-MM-YYYY HH:mm');

            if (!projectDate.isBetween(startDate, endDate, undefined, '[]')) {
                return false;
            }
        }

        // Filter arhive project
        if (selectedStatus === 'archivedprojects' && isArchived === 1) {
            return true;
        }

        // Other Filters (Status, Priority, Project Name)
        if (
            (!selectedStatus || projectStatus === selectedStatus) &&
            (!selectedDropdownStatus.length || selectedDropdownStatus.includes(projectStatus)) &&
            (!selectedDropdownPriority || projectPriority === selectedDropdownPriority) &&
            (searchByProjectName === '' || projectName.indexOf(searchByProjectName) !== -1) &&
            (searchByProjectComment === '' || projectComment.indexOf(searchByProjectComment) !== -1)
        ) {
            return true;
        }

        return false;
    });

    $(document).on('change', '#bulk-action-selector', function () {
        const selectedOption = $(this).find('option:selected');
        const value = selectedOption.val();

        if (!value || value === "Bulk actions") {
            return;
        }

        const title = selectedOption.data('title');
        const text = selectedOption.data('text');
        const type = selectedOption.data('type');

        const selectedRows = $('input.row-select-checkbox:checked');
        const selectedData = [];

        for (var i = 0; i < selectedRows.length; i++) {
            selectedData.push(selectedRows[i].value);
        }

        Swal.fire({
            title: title,
            text: text,
            showCancelButton: true,
            confirmButtonText: `Yes, ${type} it`,
            cancelButtonText: "No, cancel",
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: route('project.update', 1),
                    type: "PUT",
                    data: { type: type, ids: selectedData },
                    success: function (response) {
                        console.log(response);
                    }
                });

                let current = 1;
                const total = selectedData.length;
                let timerInterval;
                Swal.fire({
                    icon: 'success',
                    title: `${type.charAt(0).toUpperCase() + type.slice(1)} Successful!`,
                    html: `<b>${current}</b> project${total > 1 ? 's' : ''} have been moved to ${type}`,
                    timer: 2000,
                    timerProgressBar: true,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                        const b = Swal.getHtmlContainer().querySelector('b');
                        timerInterval = setInterval(() => {
                            if (current < total) {
                                current++;
                                b.textContent = `${current}`;
                            } else {
                                clearInterval(timerInterval);
                            }
                        }, 100);
                    },
                    willClose: () => {
                        clearInterval(timerInterval);
                    }
                }).then(function () {

                    if (type === 'delete') {
                        selectedRows.each(function () {
                            var row = $(this).closest('tr');
                            table.row(row).remove();
                        });
                        table.draw();
                    }

                    $('input#select-all').prop('checked', false);
                    $('.bulk_action').fadeOut();
                });
            }
        });
    });

});