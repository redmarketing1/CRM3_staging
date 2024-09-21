$(document).ready(function () {

    var table = $('#projectsTable').DataTable({
        lengthChange: false,
        ordering: false,
        searching: true,
        layout: {
            topEnd: null
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
            url: 'project?table',
            type: 'GET',
            dataType: 'json',
        },
        columns: [
            { data: 'thumbnail', name: 'thumbnail' },
            { data: 'name', name: 'name', orderable: true },
            { data: 'status', name: 'status', defaultContent: 'N/A', orderable: true },
            { data: 'comments', name: 'comments', defaultContent: 'N/A', orderable: false },
            { data: 'priority', name: 'priority', defaultContent: 'N/A', orderable: false },
            { data: 'construction', name: 'construction', defaultContent: 'N/A', orderable: false },
            { data: 'budget', name: 'budget', orderable: true },
            { data: 'created_at', name: 'created_at', orderable: true },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        initComplete: function (settings, { filterableStatusList, filterablePriorityList }) {

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
                        id: value.name,
                        text: value.name,
                        backgroundColor: value.background_color,
                        fontColor: value.font_color
                    };
                });

                $('#filterableStatusDropdown').select2({
                    data: selectData,
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

            $('#projectsTable colgroup').remove();
            $('#projectsTable tr:first-child.hide').fadeIn();

            $('.daterange').daterangepicker({
                autoUpdateInput: false,
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
            });

            $('.daterange').on('apply.daterangepicker', function (ev, picker) {
                $(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));
                console.log(picker);

                table.draw();  // Trigger table redraw
            });

            $('.daterange').on('cancel.daterangepicker', function (ev, picker) {
                $(this).val('');
                table.draw();  // Trigger table redraw
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

    $(document).on('input', '#searchByProjectName', function (e) {
        table.draw();
    });

    $('#filterableStatusDropdown, #filterablePriorityDropdown, #filterableDaterange').on('change', function () {
        table.draw();
    });


    $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
        var selectedStatus = removeWhitespace($('#status-tabs .active').data('status-name')).toLowerCase();
        var selectedDropdownStatus = removeWhitespace($('#filterableStatusDropdown').val()).toLowerCase();
        var selectedDropdownPriority = removeWhitespace($('#filterablePriorityDropdown').val()).toLowerCase();
        var selectedDateRange = $('#filterableDaterange').val();  // Get selected date range

        var projectStatus = removeWhitespace(data[2]).toLowerCase();
        var projectPriority = removeWhitespace(data[4]).toLowerCase();
        var projectName = removeWhitespace(data[1]).toLowerCase();
        var projectCreatedAt = data[7];  // Assuming 'created_at' is in the 8th column (index 7)

        var searchByProjectName = removeWhitespace($('#searchByProjectName').val()).toLowerCase();

        // Date Range Filter
        if (selectedDateRange) {
            var dateRange = selectedDateRange.split(' - ');
            var startDate = moment(dateRange[0], 'MM/DD/YYYY');
            var endDate = moment(dateRange[1], 'MM/DD/YYYY');
            var projectDate = moment(projectCreatedAt, 'DD-MM-YYYY HH:mm');  // Adjusted to your format: "28-06-2023 05:15"

            if (!projectDate.isBetween(startDate, endDate, undefined, '[]')) {
                return false;
            }
        }

        if (
            (!selectedStatus || projectStatus === selectedStatus) &&
            (!selectedDropdownStatus || projectStatus === selectedDropdownStatus) &&
            (!selectedDropdownPriority || projectPriority === selectedDropdownPriority) &&
            (searchByProjectName === '' || projectName.indexOf(searchByProjectName) !== -1)
        ) {
            return true;
        }
        return false;
    });


});