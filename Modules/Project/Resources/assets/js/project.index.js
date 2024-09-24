$(document).ready(function () {

    var table = $('#projectsTable').DataTable({
        lengthChange: true,
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
                    multiple: false,
                    minimumResultsForSearch: Infinity,
                    templateResult: formatOption,
                    templateSelection: formatSelection
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
                    data: selectData,
                    minimumResultsForSearch: Infinity
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
        let selectedStatus = removeWhitespace($('#status-tabs .active').data('status-name')).toLowerCase();
        let selectedVisibility = $('#projectVisibality').val() || 'only-active';
        let selectedDropdownStatus = $('#filterableStatusDropdown').val();
        let selectedDropdownPriority = removeWhitespace($('#filterablePriorityDropdown').val()).toLowerCase();
        let selectedDateRange = $('#filterableDaterange').val();
        let selectedProjectBudgetRange = $('.range-input-selector').val();
        let minBudget = parseFloat($('#filter_budget_from').val());
        let maxBudget = parseFloat($('#filter_budget_to').val());
        let searchProject = removeWhitespace($('#searchProject').val()).toLowerCase();


        const projectName = removeWhitespace(data[2]).toLowerCase();
        const projectComment = removeWhitespace(data[5]).toLowerCase();
        const isArchived = parseInt(data[3], 10);  // 1 for archived, 0 for not archived
        const projectStatus = removeWhitespace(data[4]).toLowerCase();
        const projectPriority = removeWhitespace(data[6]).toLowerCase();
        const projectBudget = parseFloat(data[8]);
        const projectCreatedAt = data[9];


        /**
         * Check if the project is archived; 
         * we only want to show active projects by default
         */
        if ((selectedVisibility === 'only-active' && isArchived === 1) ||
            (selectedVisibility === 'only-archive' && isArchived === 0))
            return false;


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
        if (
            (!selectedStatus || projectStatus === selectedStatus) &&
            (!selectedDropdownStatus.length || selectedDropdownStatus.includes(projectStatus)) &&
            (!selectedDropdownPriority || projectPriority === selectedDropdownPriority) &&
            (searchProject === '' || projectName.indexOf(searchProject) !== -1 || projectComment.indexOf(searchProject) !== -1)
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
                            const rowId = $(this).val();
                            const rowData = table.row('#' + rowId).data();

                            if (rowData) { // Check if rowData is defined
                                const newRowData = [...rowData];

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