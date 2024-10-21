<<<<<<< HEAD
/******/ (() => { // webpackBootstrap
/*!******************************************************************!*\
  !*** ./Modules/Project/Resources/assets/js/project.quickView.js ***!
  \******************************************************************/
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
$(document).on("click", ".status", function (event) {
  event.preventDefault();
  var projectID = $(this).attr('data-id');
  var statusID = $(this).attr('data-status');
  var backgroundColor = $(this).attr('data-background');
  var fontColor = $(this).attr('data-font');
  var statusName = $(this).text();
  $('.project-statusName').text(statusName).attr('style', "background-color: ".concat(backgroundColor, " !important; color: ").concat(fontColor, " !important;"));
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
$(document).on('click', '.change-archive', function (event) {
  event.preventDefault();
  var id = $(this).data('id');
  var title = $(this).data('title');
  var text = $(this).data('text');
  var type = $(this).data('type');
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
          ids: [id]
        },
        success: function success(response) {
          console.log(response);
          window.location.reload();
        }
      });
      Swal.fire({
        icon: 'success',
        title: "".concat(type.charAt(0).toUpperCase() + type.slice(1), " Successful!"),
        html: "Project have been moved to ".concat(type),
        timer: 2000,
        timerProgressBar: true,
        showConfirmButton: false
      });
    }
  });
});
$(document).on('click', '#copyProjectShareLinks', function () {
  var _this = this;
  var copyText = document.getElementById('copyText');
  copyText.select();
  copyText.setSelectionRange(0, 99999);
  document.execCommand('copy');
  this.textContent = 'Copied!';
  this.style.backgroundColor = '#6fd943';
  setTimeout(function () {
    _this.textContent = 'Copy';
    _this.style.backgroundColor = '';
  }, 2000);
  toastrs('success', 'Project\'s shared links has copied to clipboard', 'success');
});
function loadQuickViewer(projectID) {
  $('#changeProjectMember').select2({
    placeholder: "Nutzer wählen",
    tags: true,
    allowHtml: true,
    templateSelection: function templateSelection(data, container) {
      $(container).css("background-color", $(data.element).data("background_color"));
      if (data.element) {
        $(container).css("color", $(data.element).data("font_color"));
      }
      return data.text;
    }
  }).on('select2:open', function () {
    $('.select2-container.select2-container--open').css({
      zIndex: 99999999
    });
  }).on('change', function (event) {
    var projectID = $(this).data('projectid');
    var userID = $(this).val();
    $.ajax({
      url: route('project.member.add', projectID),
      type: "POST",
      data: {
        users: userID
      },
      success: function success(data) {
        if (data.is_success) {
          $('.projectteamcount').html(data.count);
          toastrs('Success', data.message, 'success');
        } else {
          toastrs('Error', data.message, 'error');
        }
      },
      error: function error(jqXHR, textStatus, errorThrown) {
        toastrs('Error', 'Something went wrong: ' + textStatus, 'error');
      }
    });
  });
  $.ajax({
    url: route('project.get_all_address', projectID),
    type: "POST",
    data: {
      html: true
    },
    success: function success(response) {
      if (response.status == true) {
        $(".project_all_address").html(response.html_data);
      }
    }
  });
  $('.filter_select2').select2({
    placeholder: "Select",
    multiple: true,
    tags: true,
    templateSelection: function templateSelection(data, container) {
      $(container).css("background-color", $(data.element).data("background_color"));
      if (data.element) {
        $(container).css("color", $(data.element).data("font_color"));
      }
      return data.text;
    }
  }).on('select2:open', function () {
    $('.select2-container.select2-container--open').css({
      zIndex: 99999999
    });
  });
  $(document).on('change', '.filter_select2', function (event) {
    var labelType = $(this).data('labeltype');
    var ids = $(this).val().join(", ");
    if (!labelType && !ids) return;
    $.ajax({
      url: route('project.add.status_data', projectID),
      type: "POST",
      data: {
        field: labelType,
        field_value: ids
      },
      success: function success(data) {
        if (data.is_success) {
          toastrs('Success', data.message, 'success');
        } else {
          toastrs('Error', data.message, 'error');
        }
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
        if ($('#construction_detail-company_notes').length > 0) {
          init_tiny_mce('#construction_detail-company_notes');
        }

        // Remove the d-none class if the element is found
        if (clientDetailsElement) {
          clientDetailsElement.classList.remove('d-none');
        }
        initGoogleMapPlaced('construction_detail-autocomplete', 'construction_detail');
        $(".country_select2").select2(_defineProperty(_defineProperty(_defineProperty({
          placeholder: "Country",
          multiple: false,
          dropdownParent: $("#title_form")
        }, "placeholder", "Select an country"), "allowClear", true), "dropdownAutoWidth", true));
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
        initGoogleMapPlaced('invoice-autocomplete', 'invoice');
      });
    } else {
      var clientDetailsElement = document.getElementById('client-details');
      // Remove the d-none class if the element is found
      if (clientDetailsElement) {
        clientDetailsElement.classList.add('d-none');
      }
    }
  });
}
loadQuickViewer(projectID);
function dropdownItemsToSetPreMessageContent() {
  /**
   * Set pre message tempate inside feedback & comment box in popup modal
   * */
  var dropdownItems = document.querySelectorAll('.dropdown-premsg .dropdown-menu .dropdown-item');
  var dropdownTriggerText = document.querySelector('.dropdown-premsg .dropdown-toggle .drp-text');
  dropdownItems.forEach(function (item) {
    item.addEventListener('click', function (e) {
      e.preventDefault();

      // Get the template content and name from the clicked item
      var content = this.getAttribute('data-content');
      var templateName = this.textContent.trim();

      // Set the content in the TinyMCE editor
      if (tinymce.get('premsg')) {
        tinymce.get('premsg').setContent(content);
      } else {
        $('#premsg').val(content);
      }

      // Update the dropdown trigger text with the selected template name
      dropdownTriggerText.textContent = templateName;
    });
  });
}
dropdownItemsToSetPreMessageContent();
$(document).on("click", ".projectusers img", function () {
  var user_id = $(this).data('user_id');
  var estimation_id = $(this).data('estimation_id');
  var swalWithBootstrapButtons = Swal.mixin({
    customClass: {
      confirmButton: 'btn btn-success',
      cancelButton: 'btn btn-danger'
    },
    buttonsStyling: false
  });
  swalWithBootstrapButtons.fire({
    title: 'Are you sure to Remove this User from this Estimation?',
    text: "This action can not be undone. Do you want to continue?",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Yes',
    cancelButtonText: 'No',
    reverseButtons: true
  }).then(function (result) {
    if (result.isConfirmed) {
      $.ajax({
        url: route('estimation.remove_estimation_user'),
        type: "POST",
        data: {
          estimation_id: estimation_id,
          user_id: user_id
        },
        beforeSend: function beforeSend() {
          showHideLoader('visible');
        },
        success: function success(response) {
          if (response.status == true) {
            showHideLoader('hidden');
            toastrs('Success', response.message, 'success');
            setTimeout(function () {
              location.reload();
            }, 1000);
          } else {
            toastrs('Error', response.message);
          }
        }
      });
    }
  });
});
$(document).on("change", "#same_invoice_address", function () {
  $('.different-invoice-address-block').toggleClass('d-none');
});

/*** edit feedback ***/
$(document).on("click", ".client_feedback_edit", function (e) {
  e.preventDefault();
  var feedback_id = $(this).data('id');
  if (feedback_id != '') {
    $.ajax({
      url: route('get.project.client.feedback', projectID),
      type: "POST",
      data: {
        feedback_id: feedback_id
      },
      beforeSend: function beforeSend() {
        showHideLoader('visible');
      },
      success: function success(response) {
        if (response.status == true) {
          showHideLoader('hidden');
          if (response.data.feedback != null) {
            tinymce.get('feedbackEditor').setContent(response.data.feedback);
          }
          $('#feedback_id').val(response.data.id);
          if (response.data.file != null) {
            $('#feedback_old_file').val(response.data.file);
          }
          $('.feedback_old_file_link').html(response.file_link);
          $('.feedback_collapse' + feedback_id).collapse('hide');
          $("#collapseFeedback").collapse('show');
          $('html, body').animate({
            scrollTop: $("#feedbackAccordion").offset().top
          }, 200);
        } else {
          toastrs('Error', response.message);
        }
      }
    });
  }
});

/*** delete feedback ***/
$(document).on("click", ".client_feedback_delete", function (e) {
  e.preventDefault();
  var feedback_id = $(this).data('id');
  var swalWithBootstrapButtons = Swal.mixin({
    customClass: {
      confirmButton: 'btn btn-success',
      cancelButton: 'btn btn-danger'
    },
    buttonsStyling: false
  });
  swalWithBootstrapButtons.fire({
    title: "{{ __('Are you sure to remove this client message?') }}",
    text: "{{ __('This action can not be undone. Do you want to continue?') }}",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: "{{ __('Yes') }}",
    cancelButtonText: "{{ __('No') }}",
    reverseButtons: true
  }).then(function (result) {
    if (result.isConfirmed) {
      $.ajax({
        url: route('project.client.feedback.delete', projectID),
        type: "POST",
        data: {
          feedback_id: feedback_id
        },
        beforeSend: function beforeSend() {
          showHideLoader('visible');
        },
        success: function success(response) {
          if (response.status == true) {
            showHideLoader('hidden');
            $('.feedback_heading' + feedback_id).remove();
            $('.feedback_collapse' + feedback_id).remove();
            toastrs('Success', response.message, 'success');
            setTimeout(function () {
              window.location.reload();
            }, 1000);
          } else {
            toastrs('Error', response.message);
          }
        }
      });
    }
  });
});

/*** edit project comments ***/
$(document).on("click", ".project_comments_edit", function (e) {
  e.preventDefault();
  var comment_id = $(this).data('id');
  if (comment_id != '') {
    $.ajax({
      url: route('get.project.comment', projectID),
      type: "POST",
      data: {
        comment_id: comment_id
      },
      beforeSend: function beforeSend() {
        showHideLoader('visible');
      },
      success: function success(response) {
        if (response.status == true) {
          showHideLoader('hidden');
          if (response.data.comment != null) {
            tinymce.get('commentEditor').setContent(response.data.comment);
          }
          $('#project_comment_id').val(response.data.id);
          $('#project_comment_old_file').val(response.data.file);
          $('.project_comment_old_file_link').html(response.file_link);
          $('.comment_collapse' + comment_id).collapse('hide');
          $("#collapseComment").collapse('show');
          $('html, body').animate({
            scrollTop: $("#commentAccordion").offset().top
          }, 200);
        } else {
          toastrs('Error', response.message);
        }
      }
    });
  }
});

/*** delete project comments ***/
$(document).on("click", ".project_comments_delete", function (e) {
  e.preventDefault();
  var comment_id = $(this).data('id');
  var swalWithBootstrapButtons = Swal.mixin({
    customClass: {
      confirmButton: 'btn btn-success',
      cancelButton: 'btn btn-danger'
    },
    buttonsStyling: false
  });
  swalWithBootstrapButtons.fire({
    title: "{{ __('Are you sure to remove this comment?') }}",
    text: "{{ __('This action can not be undone. Do you want to continue?') }}",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: "{{ __('Yes') }}",
    cancelButtonText: "{{ __('No') }}",
    reverseButtons: true
  }).then(function (result) {
    if (result.isConfirmed) {
      $.ajax({
        url: route('project.comment.delete', projectID),
        type: "POST",
        data: {
          comment_id: comment_id
        },
        beforeSend: function beforeSend() {
          showHideLoader('visible');
        },
        success: function success(response) {
          if (response.status == true) {
            showHideLoader('hidden');
            $('.comment_heading' + comment_id).remove();
            $('.comment_collapse' + comment_id).remove();
            toastrs('Success', response.message, 'success');
            setTimeout(function () {
              window.location.reload();
            }, 1000);
          } else {
            toastrs('Error', response.message);
          }
        }
      });
    }
  });
});
$(document).on('submit', '.project_detail_form', function (e) {
  e.preventDefault();
  var formdata = $(this).serialize();
  var url = $(this).attr('action');
  $.ajax({
    type: "post",
    url: url,
    data: formdata,
    cache: false,
    beforeSend: function beforeSend() {
      $(this).find('.btn-create').attr('disabled', 'disabled');
      if ($('#commonModal #project-description').length > 0) {
        //	tinymce.activeEditor.remove("textarea");
        tinymce.get('project-description').remove();
      }
      if ($('#commonModal #event_description').length > 0) {
        //	tinymce.activeEditor.remove("textarea");
        tinymce.get('event_description').remove();
      }
      // if($('#commonModal #construction_detail-company_notes').length > 0) {
      // //	tinymce.activeEditor.remove("textarea");
      // 	tinymce.get('construction_detail-company_notes').remove();
      // }
      // if($('#commonModal #client-company_notes').length > 0) {
      // //	tinymce.activeEditor.remove("textarea");
      // 	tinymce.get('client-company_notes').remove();
      // }
      if ($('#commonModal #technical-description').length > 0) {
        tinymce.get('technical-description').remove();
      }
    },
    success: function success(data) {
      if (data.is_success) {
        toastrs('Success', data.message, 'success');
        $('#commonModal').modal('hide');
        $('.project_title').html(data.project.title);
        $('.project-description').html(data.project.description);
        $('.technical-description').html(data.project.technical_description);
        $('.invoice_address').addClass('d-none');
        $('.invoice_address2').addClass('d-none');
        if (data.status_changed == 1) {
          location.reload();
        }
        set_construction_address();
      }
      if (data.user_details) {
        var f_name = "";
        var l_name = "";
        if (data.user_details.first_name != null) {
          f_name = data.user_details.first_name;
        }
        if (data.user_details.last_name != null) {
          l_name = data.user_details.last_name;
        }
        var full_name = f_name + " " + l_name;
        $('.client_full_name').html(full_name);
      } else {
        toastrs('Error', data.message, 'error');
      }
    },
    complete: function complete() {
      $(this).find('.btn-create').removeAttr('disabled');
    }
  });
});
function getItems() {
  $('#progress-table').DataTable({
    "lengthMenu": [[10, 25, 50, 100, 200, -1], [10, 25, 50, 100, 200, "All"]],
    'pageLength': 200,
    'dom': 'lrt',
    "bPaginate": false,
    "bFilter": false,
    "bInfo": false,
    "destroy": true,
    "processing": true,
    "serverSide": true,
    'order': [[0, 'DESC']],
    "bSort": false,
    "ajax": {
      "url": route('progress.list'),
      "type": "POST",
      data: {
        project_id: projectID
      }
    },
    "columns": [{
      "data": "id",
      "className": "id",
      "orderable": false
    }, {
      "data": "client_name",
      "className": "client_name",
      "orderable": false
    }, {
      "data": "comment",
      "className": "comment",
      "orderable": false
    }, {
      "data": "name",
      "className": "history",
      "orderable": false
    }, {
      "data": "date",
      "className": "date",
      "orderable": false
    }, {
      "data": "action",
      "className": "action",
      "orderable": false
    }]
  });
}
getItems();
$(document).ready(function () {
  load_gallary();
});
function handleDragOver(event) {
  event.preventDefault();
  event.dataTransfer.dropEffect = 'copy';
  document.getElementById('dropBox').style.border = '2px dashed #4CAF50';
}
function handleDrop(event) {
  event.preventDefault();
  document.getElementById('dropBox').style.border = '2px dashed #ccc';
  var files = event.dataTransfer.files;
  handleFiles(files);
}
function handleFileSelect(event) {
  var files = event.target.files;
  handleFiles(files);
}
function handleFiles(files) {
  var previewContainer = document.getElementById('previewContainer');
  previewContainer.innerHTML = '';
  var formData = new FormData();
  var counter = 0;
  Array.from(files).forEach(function (file) {
    var fileExtension = file.name.split('.').pop().toLowerCase();
    if (!supportedFormats.includes(fileExtension)) {
      Swal.fire({
        icon: 'error',
        title: 'Unsupported File Format',
        text: "The file format \".".concat(fileExtension, "\" is not supported.")
      });
      return;
    }
    if (!file.type.startsWith('image/')) {
      formData.append('files[]', file, file.name);
      counter++;
      if (counter === files.length) {
        uploadFile(formData);
      }
    } else {
      var reader = new FileReader();
      reader.onload = function (event) {
        var img = new Image();
        img.src = event.target.result;
        img.onload = function () {
          EXIF.getData(img, function () {
            var dateTaken = EXIF.getTag(this, 'DateTimeOriginal');
            var canvas = document.createElement('canvas');
            var ctx = canvas.getContext('2d');
            var max_width = 1500;
            var scaleFactor = max_width / img.width;
            canvas.width = max_width;
            canvas.height = img.height * scaleFactor;
            ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
            function drawTextWithBackground(ctx, text, x, y, bgColor, textColor, padding) {
              ctx.fillStyle = bgColor;
              ctx.font = 'bold 20px Arial';
              var textMetrics = ctx.measureText(text);
              var textWidth = textMetrics.width;
              var textHeight = 20;
              var backgroundX = canvas.width - textWidth - padding.leftRight - x;
              var backgroundY = canvas.height - textHeight - padding.topBottom - y;
              var backgroundWidth = textWidth + padding.leftRight * 2;
              var backgroundHeight = textHeight + padding.topBottom * 2;
              ctx.fillRect(backgroundX, backgroundY, backgroundWidth, backgroundHeight);
              ctx.fillStyle = textColor;
              ctx.fillText(text, backgroundX + padding.leftRight, backgroundY + textHeight);
            }
            drawTextWithBackground(ctx, dateTaken, 10, 10, '#ee232ac2', '#FFF', {
              topBottom: 2,
              leftRight: 5
            });
            ctx.canvas.toBlob(function (blob) {
              var compressedFile = new File([blob], file.name, {
                type: 'image/jpeg',
                lastModified: Date.now()
              });
              formData.append('files[]', compressedFile, compressedFile.name);
              var preview = document.createElement('img');
              preview.src = URL.createObjectURL(compressedFile);
              preview.classList.add('preview');
              previewContainer.appendChild(preview);
              counter++;
              if (counter === files.length) {
                uploadFile(formData);
              }
            }, 'image/jpeg', 0.85);
          });
        };
      };
      reader.readAsDataURL(file);
    }
  });
}
function uploadFile(formData) {
  // Perform the AJAX upload
  $.ajax({
    url: route('project.files_upload', projectID),
    type: 'POST',
    data: formData,
    contentType: false,
    processData: false,
    beforeSend: function beforeSend() {
      showHideLoader('visible'); // Optional: Show a loader graphic
    },
    success: function success(response) {
      showHideLoader('hidden'); // Optional: Hide the loader
      toastrs('Success', response.message, 'success');
      load_gallary(); // Refresh or update the gallery
    },
    error: function error(xhr, status, _error) {
      // Handle errors
      console.error('Upload failed:', _error);
      showHideLoader('hidden'); // Hide the loader even if there's an error
    }
  });
}
$(document).on("click", "#dropBox", function (e) {
  e.preventDefault();
  $("#fileInput").trigger('click');
});
function load_gallary() {
  $.ajax({
    url: route('project.all_files', projectID),
    type: "POST",
    data: {
      html: true
    },
    success: function success(items) {
      $(".mediabox").html(items);
      $("img.preview").remove();
      selected_images();
    }
  });
}
$(document).on("click", ".default_image_selection", function (e) {
  e.preventDefault();
  var file_id = $(this).val();
  var swalWithBootstrapButtons = Swal.mixin({
    customClass: {
      confirmButton: 'btn btn-success',
      cancelButton: 'btn btn-danger'
    },
    buttonsStyling: false
  });
  swalWithBootstrapButtons.fire({
    title: 'Are you sure?',
    text: "This action can not be undone. Do you want to continue?",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Yes',
    cancelButtonText: 'No',
    reverseButtons: true
  }).then(function (result) {
    if (result.isConfirmed) {
      $.ajax({
        url: route('project.files.set_default_file', projectID),
        type: "POST",
        data: {
          file: file_id
        },
        beforeSend: function beforeSend() {
          showHideLoader('visible');
        },
        success: function success(response) {
          if (response.is_success == true) {
            showHideLoader('hidden');
            toastrs('Success', response.message, 'success');
            load_gallary();
          } else {
            toastrs('Error', response.message);
          }
        }
      });
    }
  });
});

//bulk Files Delete
$(document).on("submit", "#bulk_delete_form", function (e) {
  e.preventDefault();
  var formData = new FormData(this);
  $.ajax({
    url: route('project.files.delete', projectID),
    type: "POST",
    data: formData,
    contentType: false,
    processData: false,
    beforeSend: function beforeSend() {
      showHideLoader('visible');
    },
    success: function success(response) {
      if (response.is_success == true) {
        showHideLoader('hidden');
        toastrs('Success', response.message, 'success');
        load_gallary();
      } else {
        toastrs('Error', response.message);
      }
    }
  });
});

//Delete Single File
$(document).on("click", ".delete_single_file_p", function (e) {
  e.preventDefault();
  var url = $(this).data('url');
  var swalWithBootstrapButtons = Swal.mixin({
    customClass: {
      confirmButton: 'btn btn-success',
      cancelButton: 'btn btn-danger'
    },
    buttonsStyling: false
  });
  swalWithBootstrapButtons.fire({
    title: 'Are you sure?',
    text: "This action can not be undone. Do you want to continue?",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Yes',
    cancelButtonText: 'No',
    reverseButtons: true
  }).then(function (result) {
    if (result.isConfirmed) {
      $.ajax({
        url: url,
        type: "GET",
        beforeSend: function beforeSend() {
          showHideLoader('visible');
        },
        success: function success(response) {
          if (response.is_success == true) {
            showHideLoader('hidden');
            toastrs('Success', response.message, 'success');
            load_gallary();
          } else {
            toastrs('Error', response.message);
          }
        }
      });
    }
  });
});
function selected_images() {
  var total_selected = 0;
  var files_ids = [];
  $('.image_selection').each(function () {
    var id = $(this).data('id');
    if ($(this).prop('checked') == true) {
      total_selected++;
      var file_id = $(this).val();
      files_ids.push(file_id);
      $(".project_file_" + id).parents('.mediaimg').addClass('selected_image');
    } else {
      $(".project_file_" + id).parents('.mediaimg').removeClass('selected_image');
    }
  });
  if (total_selected > 0) {
    $('.btn_bulk_delete_files').removeClass('d-none');
  } else {
    $('.btn_bulk_delete_files').addClass('d-none');
  }
  $('#remove_files_ids').val(JSON.stringify(files_ids));
}
/******/ })()
;
=======
(()=>{function e(t){return e="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e},e(t)}function t(t,o,n){return(o=function(t){var o=function(t,o){if("object"!=e(t)||!t)return t;var n=t[Symbol.toPrimitive];if(void 0!==n){var s=n.call(t,o||"default");if("object"!=e(s))return s;throw new TypeError("@@toPrimitive must return a primitive value.")}return("string"===o?String:Number)(t)}(t,"string");return"symbol"==e(o)?o:o+""}(o))in t?Object.defineProperty(t,o,{value:n,enumerable:!0,configurable:!0,writable:!0}):t[o]=n,t}var o,n;function s(){$.ajax({url:route("project.all_files",projectID),type:"POST",data:{html:!0},success:function(e){$(".mediabox").html(e),$("img.preview").remove(),function(){var e=0,t=[];$(".image_selection").each((function(){var o=$(this).data("id");if(1==$(this).prop("checked")){e++;var n=$(this).val();t.push(n),$(".project_file_"+o).parents(".mediaimg").addClass("selected_image")}else $(".project_file_"+o).parents(".mediaimg").removeClass("selected_image")})),e>0?$(".btn_bulk_delete_files").removeClass("d-none"):$(".btn_bulk_delete_files").addClass("d-none");$("#remove_files_ids").val(JSON.stringify(t))}()}})}$(document).on("click",".status",(function(e){e.preventDefault();var t=$(this).attr("data-id"),o=$(this).attr("data-status"),n=$(this).attr("data-background"),s=$(this).attr("data-font"),c=$(this).text();$(".project-statusName").text(c).attr("style","background-color: ".concat(n," !important; color: ").concat(s," !important;")),$.ajax({url:route("project.update",t),type:"PUT",data:{ids:t,statusID:o,type:"changeStatus"},success:function(e){toastrs("Success",e.success,"success")}})})),$(document).on("click",".change-archive",(function(e){e.preventDefault();var t=$(this).data("id"),o=$(this).data("title"),n=$(this).data("text"),s=$(this).data("type");Swal.fire({title:o,text:n,showCancelButton:!0,confirmButtonText:"Yes, ".concat(s," it"),cancelButtonText:"No, cancel"}).then((function(e){e.isConfirmed&&($.ajax({url:route("project.update",1),type:"PUT",data:{type:s,ids:[t]},success:function(e){console.log(e),window.location.reload()}}),Swal.fire({icon:"success",title:"".concat(s.charAt(0).toUpperCase()+s.slice(1)," Successful!"),html:"Project have been moved to ".concat(s),timer:2e3,timerProgressBar:!0,showConfirmButton:!1}))}))})),$(document).on("click","#copyProjectShareLinks",(function(){var e=this,t=document.getElementById("copyText");t.select(),t.setSelectionRange(0,99999),document.execCommand("copy"),this.textContent="Copied!",this.style.backgroundColor="#6fd943",setTimeout((function(){e.textContent="Copy",e.style.backgroundColor=""}),2e3),toastrs("success","Project's shared links has copied to clipboard","success")})),function(e){$("#changeProjectMember").select2({placeholder:"Nutzer wählen",tags:!0,allowHtml:!0,templateSelection:function(e,t){return $(t).css("background-color",$(e.element).data("background_color")),e.element&&$(t).css("color",$(e.element).data("font_color")),e.text}}).on("select2:open",(function(){$(".select2-container.select2-container--open").css({zIndex:99999999})})).on("change",(function(e){var t=$(this).data("projectid"),o=$(this).val();$.ajax({url:route("project.member.add",t),type:"POST",data:{users:o},success:function(e){e.is_success?($(".projectteamcount").html(e.count),toastrs("Success",e.message,"success")):toastrs("Error",e.message,"error")},error:function(e,t,o){toastrs("Error","Something went wrong: "+t,"error")}})})),$.ajax({url:route("project.get_all_address",e),type:"POST",data:{html:!0},success:function(e){1==e.status&&$(".project_all_address").html(e.html_data)}}),$(".filter_select2").select2({placeholder:"Select",multiple:!0,tags:!0,templateSelection:function(e,t){return $(t).css("background-color",$(e.element).data("background_color")),e.element&&$(t).css("color",$(e.element).data("font_color")),e.text}}).on("select2:open",(function(){$(".select2-container.select2-container--open").css({zIndex:99999999})})),$(document).on("change",".filter_select2",(function(t){var o=$(this).data("labeltype"),n=$(this).val().join(", ");(o||n)&&$.ajax({url:route("project.add.status_data",e),type:"POST",data:{field:o,field_value:n},success:function(e){e.is_success?toastrs("Success",e.message,"success"):toastrs("Error",e.message,"error")}})})),$(document).on("change","#construction-select",(function(){var e=$("#construction-select option:selected").data("type"),o=document.getElementById("client_type1");o.value=null!=e?e:"new";var n=route("users.get_user"),s=this.value;if(s)axios.post(n,{user_id:s,from:"construction"}).then((function(e){var o=document.getElementById("construction-details");$("#construction-details").html(e.data.html_data),$("#construction_detail_id").val(e.data.user_id),$("#construction_detail-company_notes").length>0&&init_tiny_mce("#construction_detail-company_notes"),o&&o.classList.remove("d-none"),initGoogleMapPlaced("construction_detail-autocomplete","construction_detail"),$(".country_select2").select2(t(t(t({placeholder:"Country",multiple:!1,dropdownParent:$("#title_form")},"placeholder","Select an country"),"allowClear",!0),"dropdownAutoWidth",!0))}));else{var c=document.getElementById("construction-details");c&&c.classList.add("d-none")}})),$(document).on("change","#client-select",(function(){var e=$("#client-select option:selected").data("type"),t=document.getElementById("client_type");t.value=null!=e?e:"new";var o=route("users.get_user");if(init_tiny_mce(".client-company_notes"),this.value)axios.post(o,{user_id:this.value,from:"client"}).then((function(e){var t=document.getElementById("client-details");$("#client-details").html(e.data.html_data),$("#client_id").val(e.data.user_id),$("#client-company_notes").length>0&&init_tiny_mce("#client-company_notes"),t&&t.classList.remove("d-none"),initGoogleMapPlaced("invoice-autocomplete","invoice")}));else{var n=document.getElementById("client-details");n&&n.classList.add("d-none")}}))}(projectID),o=document.querySelectorAll(".dropdown-premsg .dropdown-menu .dropdown-item"),n=document.querySelector(".dropdown-premsg .dropdown-toggle .drp-text"),o.forEach((function(e){e.addEventListener("click",(function(e){e.preventDefault();var t=this.getAttribute("data-content"),o=this.textContent.trim();tinymce.get("premsg")?tinymce.get("premsg").setContent(t):$("#premsg").val(t),n.textContent=o}))})),$(document).on("click",".projectusers img",(function(){var e=$(this).data("user_id"),t=$(this).data("estimation_id");Swal.mixin({customClass:{confirmButton:"btn btn-success",cancelButton:"btn btn-danger"},buttonsStyling:!1}).fire({title:"Are you sure to Remove this User from this Estimation?",text:"This action can not be undone. Do you want to continue?",icon:"warning",showCancelButton:!0,confirmButtonText:"Yes",cancelButtonText:"No",reverseButtons:!0}).then((function(o){o.isConfirmed&&$.ajax({url:route("estimation.remove_estimation_user"),type:"POST",data:{estimation_id:t,user_id:e},beforeSend:function(){showHideLoader("visible")},success:function(e){1==e.status?(showHideLoader("hidden"),toastrs("Success",e.message,"success"),setTimeout((function(){location.reload()}),1e3)):toastrs("Error",e.message)}})}))})),$(document).on("change","#same_invoice_address",(function(){$(".different-invoice-address-block").toggleClass("d-none")})),$(document).on("click",".client_feedback_edit",(function(e){e.preventDefault();var t=$(this).data("id");""!=t&&$.ajax({url:route("get.project.client.feedback",projectID),type:"POST",data:{feedback_id:t},beforeSend:function(){showHideLoader("visible")},success:function(e){1==e.status?(showHideLoader("hidden"),null!=e.data.feedback&&tinymce.get("feedbackEditor").setContent(e.data.feedback),$("#feedback_id").val(e.data.id),null!=e.data.file&&$("#feedback_old_file").val(e.data.file),$(".feedback_old_file_link").html(e.file_link),$(".feedback_collapse"+t).collapse("hide"),$("#collapseFeedback").collapse("show"),$("html, body").animate({scrollTop:$("#feedbackAccordion").offset().top},200)):toastrs("Error",e.message)}})})),$(document).on("click",".client_feedback_delete",(function(e){e.preventDefault();var t=$(this).data("id");Swal.mixin({customClass:{confirmButton:"btn btn-success",cancelButton:"btn btn-danger"},buttonsStyling:!1}).fire({title:"{{ __('Are you sure to remove this client message?') }}",text:"{{ __('This action can not be undone. Do you want to continue?') }}",icon:"warning",showCancelButton:!0,confirmButtonText:"{{ __('Yes') }}",cancelButtonText:"{{ __('No') }}",reverseButtons:!0}).then((function(e){e.isConfirmed&&$.ajax({url:route("project.client.feedback.delete",projectID),type:"POST",data:{feedback_id:t},beforeSend:function(){showHideLoader("visible")},success:function(e){1==e.status?(showHideLoader("hidden"),$(".feedback_heading"+t).remove(),$(".feedback_collapse"+t).remove(),toastrs("Success",e.message,"success"),setTimeout((function(){window.location.reload()}),1e3)):toastrs("Error",e.message)}})}))})),$(document).on("click",".project_comments_edit",(function(e){e.preventDefault();var t=$(this).data("id");""!=t&&$.ajax({url:route("get.project.comment",projectID),type:"POST",data:{comment_id:t},beforeSend:function(){showHideLoader("visible")},success:function(e){1==e.status?(showHideLoader("hidden"),null!=e.data.comment&&tinymce.get("commentEditor").setContent(e.data.comment),$("#project_comment_id").val(e.data.id),$("#project_comment_old_file").val(e.data.file),$(".project_comment_old_file_link").html(e.file_link),$(".comment_collapse"+t).collapse("hide"),$("#collapseComment").collapse("show"),$("html, body").animate({scrollTop:$("#commentAccordion").offset().top},200)):toastrs("Error",e.message)}})})),$(document).on("click",".project_comments_delete",(function(e){e.preventDefault();var t=$(this).data("id");Swal.mixin({customClass:{confirmButton:"btn btn-success",cancelButton:"btn btn-danger"},buttonsStyling:!1}).fire({title:"{{ __('Are you sure to remove this comment?') }}",text:"{{ __('This action can not be undone. Do you want to continue?') }}",icon:"warning",showCancelButton:!0,confirmButtonText:"{{ __('Yes') }}",cancelButtonText:"{{ __('No') }}",reverseButtons:!0}).then((function(e){e.isConfirmed&&$.ajax({url:route("project.comment.delete",projectID),type:"POST",data:{comment_id:t},beforeSend:function(){showHideLoader("visible")},success:function(e){1==e.status?(showHideLoader("hidden"),$(".comment_heading"+t).remove(),$(".comment_collapse"+t).remove(),toastrs("Success",e.message,"success"),setTimeout((function(){window.location.reload()}),1e3)):toastrs("Error",e.message)}})}))})),$(document).on("submit",".project_detail_form",(function(e){e.preventDefault();var t=$(this).serialize(),o=$(this).attr("action");$.ajax({type:"post",url:o,data:t,cache:!1,beforeSend:function(){$(this).find(".btn-create").attr("disabled","disabled"),$("#commonModal #project-description").length>0&&tinymce.get("project-description").remove(),$("#commonModal #event_description").length>0&&tinymce.get("event_description").remove(),$("#commonModal #technical-description").length>0&&tinymce.get("technical-description").remove()},success:function(e){if(e.is_success&&(toastrs("Success",e.message,"success"),$("#commonModal").modal("hide"),$(".project_title").html(e.project.title),$(".project-description").html(e.project.description),$(".technical-description").html(e.project.technical_description),$(".invoice_address").addClass("d-none"),$(".invoice_address2").addClass("d-none"),1==e.status_changed&&location.reload(),set_construction_address()),e.user_details){var t="",o="";null!=e.user_details.first_name&&(t=e.user_details.first_name),null!=e.user_details.last_name&&(o=e.user_details.last_name);var n=t+" "+o;$(".client_full_name").html(n)}else toastrs("Error",e.message,"error")},complete:function(){$(this).find(".btn-create").removeAttr("disabled")}})})),$("#progress-table").DataTable({lengthMenu:[[10,25,50,100,200,-1],[10,25,50,100,200,"All"]],pageLength:200,dom:"lrt",bPaginate:!1,bFilter:!1,bInfo:!1,destroy:!0,processing:!0,serverSide:!0,order:[[0,"DESC"]],bSort:!1,ajax:{url:route("progress.list"),type:"POST",data:{project_id:projectID}},columns:[{data:"id",className:"id",orderable:!1},{data:"client_name",className:"client_name",orderable:!1},{data:"comment",className:"comment",orderable:!1},{data:"name",className:"history",orderable:!1},{data:"date",className:"date",orderable:!1},{data:"action",className:"action",orderable:!1}]}),$(document).ready((function(){s()})),$(document).on("click","#dropBox",(function(e){e.preventDefault(),$("#fileInput").trigger("click")})),$(document).on("click",".default_image_selection",(function(e){e.preventDefault();var t=$(this).val();Swal.mixin({customClass:{confirmButton:"btn btn-success",cancelButton:"btn btn-danger"},buttonsStyling:!1}).fire({title:"Are you sure?",text:"This action can not be undone. Do you want to continue?",icon:"warning",showCancelButton:!0,confirmButtonText:"Yes",cancelButtonText:"No",reverseButtons:!0}).then((function(e){e.isConfirmed&&$.ajax({url:route("project.files.set_default_file",projectID),type:"POST",data:{file:t},beforeSend:function(){showHideLoader("visible")},success:function(e){1==e.is_success?(showHideLoader("hidden"),toastrs("Success",e.message,"success"),s()):toastrs("Error",e.message)}})}))})),$(document).on("submit","#bulk_delete_form",(function(e){e.preventDefault();var t=new FormData(this);$.ajax({url:route("project.files.delete",projectID),type:"POST",data:t,contentType:!1,processData:!1,beforeSend:function(){showHideLoader("visible")},success:function(e){1==e.is_success?(showHideLoader("hidden"),toastrs("Success",e.message,"success"),s()):toastrs("Error",e.message)}})})),$(document).on("click",".delete_single_file_p",(function(e){e.preventDefault();var t=$(this).data("url");Swal.mixin({customClass:{confirmButton:"btn btn-success",cancelButton:"btn btn-danger"},buttonsStyling:!1}).fire({title:"Are you sure?",text:"This action can not be undone. Do you want to continue?",icon:"warning",showCancelButton:!0,confirmButtonText:"Yes",cancelButtonText:"No",reverseButtons:!0}).then((function(e){e.isConfirmed&&$.ajax({url:t,type:"GET",beforeSend:function(){showHideLoader("visible")},success:function(e){1==e.is_success?(showHideLoader("hidden"),toastrs("Success",e.message,"success"),s()):toastrs("Error",e.message)}})}))}))})();
>>>>>>> 7520862 (from server)
