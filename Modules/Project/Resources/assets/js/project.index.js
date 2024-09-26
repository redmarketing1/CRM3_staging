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
                className: 'dt-body-center input-checkbox',
                render: function (data, type, full, meta) {
                    return '<input type="checkbox" class="row-select-checkbox" value="' + data.id + '">';
                }
            },
            { data: 'thumbnail', name: 'thumbnail', className: 'thumbnail' },
            { data: 'name', name: 'name', orderable: true, className: 'name' },
            { data: 'comments', name: 'comments', defaultContent: 'N/A', orderable: false, className: 'comments' },
            { data: 'is_archive', name: 'is_archive', visible: false, className: 'is_archive' },
            { data: 'status', name: 'status', defaultContent: 'N/A', orderable: true, className: 'status' },
            { data: 'priority', name: 'priority', defaultContent: 'N/A', orderable: false, className: 'priority' },
            { data: 'construction', name: 'construction', defaultContent: 'N/A', orderable: false, className: 'construction' },
            { data: 'budget', name: 'budget', orderable: true, className: 'budget' },
            { data: 'created_at', name: 'created_at', orderable: true, className: 'created_at' },
            { data: 'action', name: 'action', orderable: false, searchable: false, className: 'action' }
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
                    placeholder: 'Select Status',
                    multiple: true,
                    allowClear: false,
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
                    placeholder: 'Select Priority',
                    multiple: true,
                    allowClear: false,
                    minimumResultsForSearch: Infinity,
                    templateResult: formatOption,
                    templateSelection: formatSelection
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

    $(document).on('input', '#searchProject, #filter_price_from, #filter_price_to', function (e) {
        table.draw();
    });

    $(document).on('change', '#filterableStatusDropdown, #filterablePriorityDropdown, #filterableDaterange, #projectVisibality', function () {
        table.draw();
    });

    $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
        let selectedStatus = removeWhitespace($('#status-tabs .active').data('status-name')).toLowerCase();
        let selectedVisibility = $('#projectVisibality').val() || 'only-active';
        let selectedDropdownStatus = $('#filterableStatusDropdown').val() || [];
        let selectedDropdownPriority = $('#filterablePriorityDropdown').val() || [];
        let selectedDateRange = $('#filterableDaterange').val();
        let minBudget = parseFloat($('#filter_price_from').val());
        let maxBudget = parseFloat($('#filter_price_to').val());
        let searchProject = removeWhitespace($('#searchProject').val()).toLowerCase();


        const projectName = removeWhitespace(data[2]).toLowerCase();
        const projectComment = removeWhitespace(data[3]).toLowerCase();
        const isArchived = parseInt(data[4], 10);  // 1 for archived, 0 for not archived
        const projectStatus = removeWhitespace(data[5]).toLowerCase();
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

        selectedDropdownPriority = selectedDropdownPriority.map(priority => removeWhitespace(priority).toLowerCase());

        // Other Filters (Status, Priority, Project Name)  
        if (
            (!selectedStatus || projectStatus === selectedStatus) &&
            (!selectedDropdownStatus.length || selectedDropdownStatus.includes(projectStatus)) &&
            (!selectedDropdownPriority.length || selectedDropdownPriority.some(priority => priority === projectPriority)) &&
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
                            const rowId = $(this).val();
                            const rowData = table.row('#' + rowId).data();
                            rowData.is_archive = 1;
                            table.row('#' + rowId).data(rowData).draw();
                        });
                    }

                    if (type === 'unarchive') {
                        selectedRows.each(function (value, index) {
                            const rowId = $(this).val();
                            const rowData = table.row('#' + rowId).data();
                            rowData.is_archive = 0;
                            table.row('#' + rowId).data(rowData).draw();
                        });
                    }

                    if (type === 'duplicate') {
                        selectedRows.each(function (val, element) {
                            const rowId = $(this).val();
                            const rowData = table.row('#' + rowId).data();
                            table.row.add(rowData).draw();
                        });
                    }


                    $('input#select-all,.row-select-checkbox').prop('checked', false);
                    $('#bulk-action-selector').val('bulk').fadeOut();
                });
            }
        });
    });


    const rangeInput = $(".range-input input");
    const priceInput = $(".price-input input");
    const range = $(".slider_filter .progress");
    let priceGap = 1;

    // Update range input values based on price inputs
    priceInput.on('input', function () {
        let minPrice = parseInt(priceInput.eq(0).val());
        let maxPrice = parseInt(priceInput.eq(1).val());

        if (maxPrice - minPrice >= priceGap && maxPrice <= parseInt(rangeInput.eq(1).attr('max'))) {
            if ($(this).hasClass("input-min")) {
                rangeInput.eq(0).val(minPrice);
                range.css('left', (minPrice / parseInt(rangeInput.eq(0).attr('max'))) * 100 + "%");
            } else {
                rangeInput.eq(1).val(maxPrice);
                range.css('right', 100 - (maxPrice / parseInt(rangeInput.eq(1).attr('max'))) * 100 + "%");
            }
        }
    });

    // Update price inputs based on range input values
    rangeInput.on('input', function () {
        let minVal = parseInt(rangeInput.eq(0).val());
        let maxVal = parseInt(rangeInput.eq(1).val());

        if (maxVal - minVal < priceGap) {
            if ($(this).hasClass("range-min")) {
                rangeInput.eq(0).val(maxVal - priceGap);
            } else {
                rangeInput.eq(1).val(minVal + priceGap);
            }
        } else {
            priceInput.eq(0).val(minVal);
            priceInput.eq(1).val(maxVal);
            range.css('left', (minVal / parseInt(rangeInput.eq(0).attr('max'))) * 100 + "%");

            // Calculate width and set it to the progress bar
            let width = (maxVal - minVal) / parseInt(rangeInput.eq(1).attr('max')) * 100 + "%";
            range.css('width', width);
        }
    });



});