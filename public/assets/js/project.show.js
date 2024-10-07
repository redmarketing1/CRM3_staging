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
$(document).on('change', '#construction-select', function () {
  var selectedOption = $('#construction-select option:selected');
  var selectedType = selectedOption.data('type');
  var clientTypeInput = document.getElementById('client_type1');
  if (selectedType !== undefined && selectedType !== null) {
    clientTypeInput.value = selectedType;
  } else {
    clientTypeInput.value = 'new';
  }
  var url = route('users.get_user');
  var user_id = this.value;

  // Get the selected values
  if (user_id) {
    axios.post(url, {
      'user_id': user_id,
      'from': 'construction'
    }).then(function (response) {
      var clientDetailsElement = document.getElementById('construction-details');
      $('#construction-details').html(response.data.html_data);
      $('#construction_detail_id').val(response.data.user_id);
      // initialize_construction();
      if ($('#construction_detail-company_notes').length > 0) {
        init_tiny_mce('#construction_detail-company_notes');
      }

      // Remove the d-none class if the element is found
      if (clientDetailsElement) {
        clientDetailsElement.classList.remove('d-none');
      }
    });
  } else {
    var clientDetailsElement = document.getElementById('construction-details');
    // Remove the d-none class if the element is found
    if (clientDetailsElement) {
      clientDetailsElement.classList.add('d-none');
    }
  }
});
$(document).on('change', '#client-select', function () {
  var selectedOption = $('#client-select option:selected');
  var selectedType = selectedOption.data('type');
  var clientTypeInput = document.getElementById('client_type');
  if (selectedType !== undefined && selectedType !== null) {
    clientTypeInput.value = selectedType;
  } else {
    clientTypeInput.value = 'new';
  }
  var url;
  var url = route('users.get_user');
  init_tiny_mce('.client-company_notes');

  // Get the selected values
  if (this.value) {
    axios.post(url, {
      'user_id': this.value,
      'from': 'client'
    }).then(function (response) {
      var clientDetailsElement = document.getElementById('client-details');
      $('#client-details').html(response.data.html_data);
      $('#client_id').val(response.data.user_id);
      // initialize();

      if ($('#client-company_notes').length > 0) {
        init_tiny_mce('#client-company_notes');
      }

      // Remove the d-none class if the element is found
      if (clientDetailsElement) {
        clientDetailsElement.classList.remove('d-none');
      }
    });
  } else {
    var clientDetailsElement = document.getElementById('client-details');
    // Remove the d-none class if the element is found
    if (clientDetailsElement) {
      clientDetailsElement.classList.add('d-none');
    }
  }
});
/******/ })()
;