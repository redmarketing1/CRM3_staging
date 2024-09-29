$(document).on("click", ".status", function (event) {
    event.preventDefault();

    const projectID = $(this).attr('data-id');
    const statusID = $(this).attr('data-status');
    const statusName = $(this).text();

    $('.project-statusName').text(statusName);

    $.ajax({
        url: route('project.update', projectID),
        type: 'PUT',
        data: {
            ids: projectID,
            statusID: statusID,
            type: "changeStatus"
        },
        success: function (response) {
            toastrs('Success', response.success, 'success');
        }
    });
});