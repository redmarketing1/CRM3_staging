/******/ (() => { // webpackBootstrap
/*!*************************************************************!*\
  !*** ./Modules/Project/Resources/assets/js/project.show.js ***!
  \*************************************************************/
$(document).on("click", ".status", function (event) {
  event.preventDefault();
  var projectID = $(this).attr('data-id');
  var statusID = $(this).attr('data-status');
  var statusName = $(this).text();
  $('.project-statusName').text(statusName);
  $.ajax({
    url: route('project.update', projectID),
    type: 'PUT',
    data: {
      ids: projectID,
      statusID: statusID,
      type: "changeStatus"
    },
    success: function success(response) {
      toastrs('Success', response.success, 'success');
    }
  });
});
/******/ })()
;