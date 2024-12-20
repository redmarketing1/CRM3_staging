$(document).on("click", ".status", function (event) {
    event.preventDefault();

    var projectID = $(this).attr('data-id');
    const statusID = $(this).attr('data-status');
    const backgroundColor = $(this).attr('data-background');
    const fontColor = $(this).attr('data-font');
    const statusName = $(this).text();

    $('.project-statusName').text(statusName).attr('style',
        `background-color: ${backgroundColor} !important; color: ${fontColor} !important;`);

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

$(document).on('click', '.change-archive', function (event) {
    event.preventDefault();

    const id = $(this).data('id');
    const title = $(this).data('title');
    const text = $(this).data('text');
    const type = $(this).data('type');

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
                data: { type: type, ids: [id] },
                success: function (response) {
                    console.log(response);
                    window.location.reload();
                }
            });

            Swal.fire({
                icon: 'success',
                title: `${type.charAt(0).toUpperCase() + type.slice(1)} Successful!`,
                html: `Project have been moved to ${type}`,
                timer: 2000,
                timerProgressBar: true,
                showConfirmButton: false,
            });
        }
    });

});

$(document).on('click', '#copyProjectShareLinks', function () {
    const copyText = document.getElementById('copyText');
    copyText.select();
    copyText.setSelectionRange(0, 99999);
    document.execCommand('copy');

    this.textContent = 'Copied!';
    this.style.backgroundColor = '#6fd943';
    setTimeout(() => {
        this.textContent = 'Copy';
        this.style.backgroundColor = '';
    }, 2000);

    toastrs('success', 'Project\'s shared links has copied to clipboard', 'success');
});

function loadQuickViewer(projectID) {

    $('#changeProjectMember').select2({
        placeholder: "Nutzer wählen",
        tags: true,
        allowHtml: true,
        templateSelection: function (data, container) {
            $(container).css("background-color", $(data.element).data("background_color"));
            if (data.element) {
                $(container).css("color", $(data.element).data("font_color"));
            }
            return data.text;
        }
    })
        .on('select2:open', function () {
            $('.select2-container.select2-container--open').css({
                zIndex: 99999999,
            });
        })
        .on('change', function (event) {
            const projectID = $(this).data('projectid');
            const userID = $(this).val();

            $.ajax({
                url: route('project.member.add', projectID),
                type: "POST",
                data: { users: userID },
                success: function (data) {
                    if (data.is_success) {
                        $('.projectteamcount').html(data.count);
                        toastrs('Success', data.message, 'success');
                    } else {
                        toastrs('Error', data.message, 'error');
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    toastrs('Error', 'Something went wrong: ' + textStatus, 'error');
                }
            });
        });

    $.ajax({
        url: route('project.get_all_address', projectID),
        type: "POST",
        data: { html: true },
        success: function (response) {
            if (response.status == true) {
                $(".project_all_address").html(response.html_data);
            }
        }
    });

    $('.filter_select2').select2({
        placeholder: "Select",
        multiple: true,
        tags: true,
        templateSelection: function (data, container) {
            $(container).css("background-color", $(data.element).data("background_color"));
            if (data.element) {
                $(container).css("color", $(data.element).data("font_color"));
            }
            return data.text;
        }
    }).on('select2:open', function () {
        $('.select2-container.select2-container--open').css({
            zIndex: 99999999,
        });
    });

    $(document).on('change', '.filter_select2', function (event) {
        const labelType = $(this).data('labeltype');
        const ids = $(this).val().join(", ");
        if (!labelType && !ids) return;

        $.ajax({
            url: route('project.add.status_data', projectID),
            type: "POST",
            data: {
                field: labelType,
                field_value: ids
            },
            success: function (data) {
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
            }).then((response) => {

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


                $(".country_select2").select2({
                    placeholder: "Country",
                    multiple: false,
                    dropdownParent: $("#title_form"),
                    placeholder: "Select an country",
                    allowClear: true,
                    dropdownAutoWidth: true,
                });
            })
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
            }).then((response) => {
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

            })
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
    const dropdownItems = document.querySelectorAll(
        '.dropdown-premsg .dropdown-menu .dropdown-item');
    const dropdownTriggerText = document.querySelector(
        '.dropdown-premsg .dropdown-toggle .drp-text');

    dropdownItems.forEach(item => {
        item.addEventListener('click', function (e) {
            e.preventDefault();

            // Get the template content and name from the clicked item
            const content = this.getAttribute('data-content');
            const templateName = this.textContent.trim();

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

    const swalWithBootstrapButtons = Swal.mixin({
        customClass: {
            confirmButton: 'btn btn-success',
            cancelButton: 'btn btn-danger'
        },
        buttonsStyling: false
    })
    swalWithBootstrapButtons.fire({
        title: 'Are you sure to Remove this User from this Estimation?',
        text: "This action can not be undone. Do you want to continue?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes',
        cancelButtonText: 'No',
        reverseButtons: true,
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: route('estimation.remove_estimation_user'),
                type: "POST",
                data: {
                    estimation_id: estimation_id,
                    user_id: user_id
                },
                beforeSend: function () {
                    showHideLoader('visible');
                },
                success: function (response) {
                    if (response.status == true) {
                        showHideLoader('hidden');
                        toastrs('Success', response.message, 'success');
                        setTimeout(function () {
                            location.reload();
                        }, 1000)
                    } else {
                        toastrs('Error', response.message)
                    }
                }
            });
        }
    })
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
                feedback_id: feedback_id,
            },
            beforeSend: function () {
                showHideLoader('visible');
            },
            success: function (response) {
                if (response.status == true) {
                    showHideLoader('hidden');
                    if (response.data.feedback != null) {
                        tinymce.get('feedbackEditor').setContent(response.data
                            .feedback);
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
    const swalWithBootstrapButtons = Swal.mixin({
        customClass: {
            confirmButton: 'btn btn-success',
            cancelButton: 'btn btn-danger'
        },
        buttonsStyling: false
    })
    swalWithBootstrapButtons.fire({
        title: "{{ __('Are you sure to remove this client message?') }}",
        text: "{{ __('This action can not be undone. Do you want to continue?') }}",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: "{{ __('Yes') }}",
        cancelButtonText: "{{ __('No') }}",
        reverseButtons: true,
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: route('project.client.feedback.delete', projectID),
                type: "POST",
                data: {
                    feedback_id: feedback_id,
                },
                beforeSend: function () {
                    showHideLoader('visible');
                },
                success: function (response) {
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
                comment_id: comment_id,
            },
            beforeSend: function () {
                showHideLoader('visible');
            },
            success: function (response) {
                if (response.status == true) {
                    showHideLoader('hidden');
                    if (response.data.comment != null) {
                        tinymce.get('commentEditor').setContent(response.data
                            .comment);
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
    const swalWithBootstrapButtons = Swal.mixin({
        customClass: {
            confirmButton: 'btn btn-success',
            cancelButton: 'btn btn-danger'
        },
        buttonsStyling: false
    })
    swalWithBootstrapButtons.fire({
        title: "{{ __('Are you sure to remove this comment?') }}",
        text: "{{ __('This action can not be undone. Do you want to continue?') }}",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: "{{ __('Yes') }}",
        cancelButtonText: "{{ __('No') }}",
        reverseButtons: true,
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: route('project.comment.delete', projectID),
                type: "POST",
                data: {
                    comment_id: comment_id,
                },
                beforeSend: function () {
                    showHideLoader('visible');
                },
                success: function (response) {
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
        beforeSend: function () {
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
        success: function (data) {
            if (data.is_success) {
                toastrs('Success', data.message, 'success');
                $('#commonModal').modal('hide');
                $('.project_title').html(data.project.title);
                $('.project-description').html(data.project.description);
                $('.technical-description').html(data.project
                    .technical_description);
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
        complete: function () {
            $(this).find('.btn-create').removeAttr('disabled');
        },
    });
});

function getItems() {
    $('#progress-table').DataTable({
        "lengthMenu": [
            [10, 25, 50, 100, 200, -1],
            [10, 25, 50, 100, 200, "All"]
        ],
        'pageLength': 200,
        'dom': 'lrt',
        "bPaginate": false,
        "bFilter": false,
        "bInfo": false,
        "destroy": true,
        "processing": true,
        "serverSide": true,
        'order': [
            [0, 'DESC']
        ],
        "bSort": false,
        "ajax": {
            "url": route('progress.list'),
            "type": "POST",
            data: { project_id: projectID },
        },
        "columns": [{
            "data": "id",
            "className": "id",
            "orderable": false
        },
        {
            "data": "client_name",
            "className": "client_name",
            "orderable": false
        },
        {
            "data": "comment",
            "className": "comment",
            "orderable": false
        },
        {
            "data": "name",
            "className": "history",
            "orderable": false
        },
        {
            "data": "date",
            "className": "date",
            "orderable": false
        },
        {
            "data": "action",
            "className": "action",
            "orderable": false
        }
        ],
    });
}
getItems();

$(document).ready(function () {
    load_gallary()
});

function handleDragOver(event) {
    event.preventDefault();
    event.dataTransfer.dropEffect = 'copy';
    document.getElementById('dropBox').style.border = '2px dashed #4CAF50';
}

function handleDrop(event) {
    event.preventDefault();
    document.getElementById('dropBox').style.border = '2px dashed #ccc';

    const files = event.dataTransfer.files;
    handleFiles(files);
}

function handleFileSelect(event) {
    const files = event.target.files;
    handleFiles(files);
}

function handleFiles(files) {
    const previewContainer = document.getElementById('previewContainer');
    previewContainer.innerHTML = '';
    let formData = new FormData();
    let counter = 0;

    Array.from(files).forEach((file) => {
        const fileExtension = file.name.split('.').pop().toLowerCase();

        if (!supportedFormats.includes(fileExtension)) {
            Swal.fire({
                icon: 'error',
                title: 'Unsupported File Format',
                text: `The file format ".${fileExtension}" is not supported.`,
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
            const reader = new FileReader();
            reader.onload = function (event) {
                const img = new Image();
                img.src = event.target.result;
                img.onload = function () {
                    EXIF.getData(img, function () {
                        const dateTaken = EXIF.getTag(this, 'DateTimeOriginal');
                        const canvas = document.createElement('canvas');
                        const ctx = canvas.getContext('2d');
                        const max_width = 1500;
                        const scaleFactor = max_width / img.width;
                        canvas.width = max_width;
                        canvas.height = img.height * scaleFactor;
                        ctx.drawImage(img, 0, 0, canvas.width, canvas.height);

                        function drawTextWithBackground(ctx, text, x, y, bgColor, textColor,
                            padding) {
                            ctx.fillStyle = bgColor;
                            ctx.font = 'bold 20px Arial';

                            const textMetrics = ctx.measureText(text);
                            const textWidth = textMetrics.width;
                            const textHeight = 20;

                            const backgroundX = canvas.width - textWidth - padding
                                .leftRight - x;
                            const backgroundY = canvas.height - textHeight - padding
                                .topBottom - y;
                            const backgroundWidth = textWidth + padding.leftRight * 2;
                            const backgroundHeight = textHeight + padding.topBottom * 2;

                            ctx.fillRect(backgroundX, backgroundY, backgroundWidth,
                                backgroundHeight);
                            ctx.fillStyle = textColor;
                            ctx.fillText(text, backgroundX + padding.leftRight,
                                backgroundY + textHeight);
                        }

                        drawTextWithBackground(ctx, dateTaken, 10, 10, '#ee232ac2',
                            '#FFF', {
                            topBottom: 2,
                            leftRight: 5
                        });

                        ctx.canvas.toBlob(function (blob) {
                            const compressedFile = new File([blob], file.name, {
                                type: 'image/jpeg',
                                lastModified: Date.now(),
                            });
                            formData.append('files[]', compressedFile,
                                compressedFile.name);

                            const preview = document.createElement('img');
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
        beforeSend: function () {
            showHideLoader('visible'); // Optional: Show a loader graphic
        },
        success: function (response) {
            showHideLoader('hidden'); // Optional: Hide the loader
            toastrs('Success', response.message, 'success');
            load_gallary(); // Refresh or update the gallery
        },
        error: function (xhr, status, error) {
            // Handle errors
            console.error('Upload failed:', error);
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
        success: function (items) {
            $(".mediabox").html(items);
            $("img.preview").remove();
            selected_images();
        }
    })
}

$(document).on("click", ".default_image_selection", function (e) {
    e.preventDefault();

    var file_id = $(this).val();

    const swalWithBootstrapButtons = Swal.mixin({
        customClass: {
            confirmButton: 'btn btn-success',
            cancelButton: 'btn btn-danger'
        },
        buttonsStyling: false
    })
    swalWithBootstrapButtons.fire({
        title: 'Are you sure?',
        text: "This action can not be undone. Do you want to continue?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes',
        cancelButtonText: 'No',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: route('project.files.set_default_file', projectID),
                type: "POST",
                data: {
                    file: file_id
                },
                beforeSend: function () {
                    showHideLoader('visible');
                },
                success: function (response) {
                    if (response.is_success == true) {
                        showHideLoader('hidden');
                        toastrs('Success', response.message, 'success')
                        load_gallary();
                    } else {
                        toastrs('Error', response.message)
                    }
                }
            });
        }
    })
});

//bulk Files Delete
$(document).on("submit", "#bulk_delete_form", function (e) {
    e.preventDefault();
    const formData = new FormData(this);

    $.ajax({
        url: route('project.files.delete', projectID),
        type: "POST",
        data: formData,
        contentType: false,
        processData: false,
        beforeSend: function () {
            showHideLoader('visible');
        },
        success: function (response) {
            if (response.is_success == true) {
                showHideLoader('hidden');
                toastrs('Success', response.message, 'success')
                load_gallary();
            } else {
                toastrs('Error', response.message)
            }
        }
    });
});

//Delete Single File
$(document).on("click", ".delete_single_file_p", function (e) {
    e.preventDefault();

    var url = $(this).data('url');
    const swalWithBootstrapButtons = Swal.mixin({
        customClass: {
            confirmButton: 'btn btn-success',
            cancelButton: 'btn btn-danger'
        },
        buttonsStyling: false
    })
    swalWithBootstrapButtons.fire({
        title: 'Are you sure?',
        text: "This action can not be undone. Do you want to continue?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes',
        cancelButtonText: 'No',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: url,
                type: "GET",
                beforeSend: function () {
                    showHideLoader('visible');
                },
                success: function (response) {
                    if (response.is_success == true) {
                        showHideLoader('hidden');
                        toastrs('Success', response.message, 'success')
                        load_gallary();
                    } else {
                        toastrs('Error', response.message)
                    }
                }
            });
        }
    })
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