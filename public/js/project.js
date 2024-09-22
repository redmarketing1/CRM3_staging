$(document).ready(function () {
    var table = $('#projectsTable').DataTable({
        lengthMenu: [
            [10, 25, 50, 100, 200, -1],
        ],
        pageLength: 10,
        lengthChange: false,
        ordering: true,
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
            url: 'https://neu-west.com/crm3_staging/project?table',
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
        createdRow: function (row, data, dataIndex) {
            $(row).attr('id', 'project-items');
            $(row).attr('data-id', data.id);
            $(row).attr('project-backgroundColor', data.projectBackgroundColor);
            $(row).attr('project-fontColor', data.projectFontColor);

            let backgroundColorOfRGB = hexToRgb(data.projectBackgroundColor, 0.05);

            $(row).css({
                '--background-color': data.projectBackgroundColor,
                '--font-color': data.projectFontColor,
                '--hover-background-color': backgroundColorOfRGB
            });
        },
        initComplete: function (settings, json) {
            $('#projectsTable colgroup').remove();
        }
    });

    $(document).on('click', '#status-tabs a', function (e) {
        e.preventDefault();

        $('#status-tabs a').removeClass('active');
        $(this).addClass('active');

        table.draw();
    });

    $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
        var selectedStatus = removeWhitespace($('#status-tabs .active').data('status-name'));
        var projectStatus = removeWhitespace(data[2]);

        console.log(selectedStatus);
        console.log(projectStatus);


        if (!selectedStatus || projectStatus == selectedStatus) {
            return true;
        }

        return false;
    });

    /** Toggle to table filter option */
    $('.toggle_filter').on('click', function () {
        $('.filter-wrapper').slideToggle("slow");

        $(this).find('.arrow_icon')
            .toggleClass('fa-arrow-up')
            .toggleClass('fa-arrow-down');
    });

    function removeWhitespace(str) {
        return str ? str.replace(/\s+/g, '') : '';
    }

});