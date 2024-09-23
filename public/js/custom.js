/**
 *
 * You can write your JS code here, DO NOT touch the default style file
 * because it will make it harder for you to update.
 *
 */


"use strict";
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    },
    cache: false,
    complete: function () {

        $('[data-toggle="tooltip"]').tooltip();
    },
});

$(function () {
    if ($('.custom-scroll').length) {
        $(".custom-scroll").niceScroll();
        $(".custom-scroll-horizontal").niceScroll();
    }

    if ($('.activity-wrap').length) {
        $(".activity-wrap").niceScroll();
    }

    // if ($(".select2").length > 0) {
    //     $(".select2").select2({
    //         disableOnMobile: false,
    //         nativeOnMobile: false
    //     });
    // }


});

$(document).ready(function () {
    if ($(".pc-dt-simple").length > 0) {
        $($(".pc-dt-simple")).each(function (index, element) {
            var id = $(element).attr('id');
            const dataTable = new simpleDatatables.DataTable("#" + id);
        });
    }


    common_bind();
    summernote();

    if ($("#prompt_input").length) {
        init_tiny_mce('#prompt_input');
    }


    // for Choose file
    $(document).on('change', 'input[type=file]', function () {
        var fileclass = $(this).attr('data-filename');
        var finalname = $(this).val().split('\\').pop();
        $('.' + fileclass).html(finalname);
    });

    $(document).on('click', function (event) {
        if (!$(event.target).closest('.chat_widget').length) {
            // ... clicked on the 'body', but not inside of .chat_widget
            if (!$(event.target).closest('.smart-chat-btn-wrapper').length && !$(event.target).closest('.swal2-container').length) {
                if ($('#smart-chat-btn').hasClass('d-none')) {
                    $('.close_chat_widget').trigger('click');
                }
            }
        }
    });

    $(document).on('click', '#smart-chat-btn', function () {
        $('.chat_widget').removeClass('d-none');
        $('#smart-chat-btn').addClass('d-none');
        load_conversatios();
    });

    $(document).on('click', '.close_chat_widget', function () {
        $('.chat_widget').addClass('d-none');
        $('#smart-chat-btn').removeClass('d-none');
    });

    $(document).on('click', '.chat-item', function (event) {
        if ($(event.target).closest('.conversation_rename').length || $(event.target).closest('.chat-item-actions').length) {
            return false;
        } else {
            var conversation_id = $(this).data('conversation_id');
            $('#conversation_id').val('');
            $('#chat_id').val('');
            $('.user_msg').html('');
            $('.default_chat').addClass('d-none');
            $('.chat_back_btn').removeClass('d-none');

            get_conversation_chat(conversation_id, 'open_chat');
        }
    });

    $(document).on('click', '.chat_back_btn', function () {
        load_conversatios();
        $('.landing_page').removeClass('d-none');
        $('.chatting_page').addClass('d-none');
        $('#ai_model_id').val('');
        clearInterval(chat_interval);

        $('#conversation_id').val('');
        $('#chat_id').val('');
        $('#smart-chat-image-previews').html('');;
        $('.chat_uploaded_images').html('');
        tinymce.get('prompt_input').setContent('');
        $('.chat_back_btn').addClass('d-none');
    });

    $(document).on('click', '.btn_conversation_rename', function () {

        var conversation_name = $(this).parents('.chat-details').find('.chat-name').text();
        $(this).parents('.chat-details').find('.conversation_rename').removeClass('d-none');
        $(this).parents('.chat-details').find('.conversation_name_last_msg').addClass('d-none');
        if ($(this).parents('.chat-details').find('.input_rename').val() == "") {
            $(this).parents('.chat-details').find('.input_rename').val(conversation_name);
        }
    });

    $(document).on('click', '.btn_rename_cancel', function () {
        $(this).parents('.chat-details').find('.conversation_name_last_msg').removeClass('d-none');
        $(this).parents('.chat-details').find('.conversation_rename').addClass('d-none');
    });

    $(document).on('click', '.btn_rename_save', function () {
        if ($(this).parents('.chat-details').find('.input_rename').val() == "") {
            toastrs('Error', 'Please Enter Conversation Name', 'error');
            return false;
        }
        var name = $(this).parents('.chat-details').find('.input_rename').val();
        var conversation_id = $(this).parents('.chat-item').data('conversation_id');

        $.ajax({
            url: base_url + "/rename_conversation",
            type: "POST",
            data: {
                name: name,
                conversation_id: conversation_id,
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                if (response.status == true) {
                    toastrs("success", response.message);
                    load_conversatios();
                    $(this).parents('.chat-details').find('.conversation_name_last_msg').removeClass('d-none');
                    $(this).parents('.chat-details').find('.conversation_rename').addClass('d-none');
                } else {
                    toastrs("Error", response.message);
                }
            }
        });
    });

    $(document).on('click', '.btn_conversation_delete', function () {
        var conversation_id = $(this).parents('.chat-item').data('conversation_id');
        const swalWithBootstrapButtons = Swal.mixin({
            customClass: {
                confirmButton: 'btn btn-success',
                cancelButton: 'btn btn-danger'
            },
            buttonsStyling: false
        })
        swalWithBootstrapButtons.fire({
            title: 'Are you sure!,,?',
            text: "This action can not be undone. Do you want to continue?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes',
            cancelButtonText: 'No',
            reverseButtons: true,
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: base_url + "/delete_conversation",
                    type: "POST",
                    data: {
                        conversation_id: conversation_id,
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {
                        if (response.status == true) {
                            toastrs("success", response.message);
                            load_conversatios();
                        } else {
                            toastrs("Error", response.message);
                        }
                    }
                });
            }
        })
    });

    $(document).on('submit', '#smartChatForm', function (e) {
        e.preventDefault();
        var formData = new FormData(this);
        var prompt_msg = tinymce.get("prompt_input").getContent({ format: "html" });
        var user_msg = "";
        var is_update = 0;
        if (prompt_msg == '') {
            toastrs('Error', 'Please Enter Message', 'error');
            return false;
        }
        var template_id = $('#smart_template_id').val();
        var ai_model_id = $('#ai_model_id').val();
        var conversation_id = $('#conversation_id').val();
        var chat_id = $('#chat_id').val();
        if (ai_model_id == '') {
            toastrs('Error', 'Please Select AI Model', 'error');
            return false;
        }
        formData.append('ai_model_id', ai_model_id);
        formData.append('template_id', template_id);
        formData.append('conversation_id', conversation_id);
        if (chat_id != '') {
            is_update = 1;
            formData.append('chat_id', chat_id);
        }

        user_msg = prompt_msg;

        var img_preview = $('#smart-chat-image-previews').html();
        var img_html = "";
        if (img_preview.length > 0) {
            img_html = '<div class="smart-chat-user-attachments">';
            img_html += img_preview;
            img_html += '</div>';
        }
        user_msg += img_html;

        $('#smart_template_id').val('');

        tinymce.get('prompt_input').setContent('');

        $('#smart-chat-file-input').val('');
        $('#smart-chat-image-previews').html('');
        $('.chat_uploaded_images').html('');

        formData.append('prompt', prompt_msg);

        if (typeof project_id != 'undefined') {
            formData.append('project_id', project_id);
        }

        var dots_html = $('.default_chat').html();

        $('.dots-msg').addClass('d-none');
        $('.dots-msg').remove();

        $.ajax({
            url: base_url + "/chat_request",
            method: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function (response) {
                if (response.status == true) {
                    $('.chat-messages .all_chats').append(response.html_data);
                    const all_chats = $('.chat-messages');
                    all_chats.animate({
                        scrollTop: all_chats.prop("scrollHeight")
                    }, 'slow');

                    if (response.edit_msg == true) {
                        var from_msg = response.edit_msg_data.from_chat_id;
                        var to_msg = response.edit_msg_data.to_chat_id;

                        for (var i = from_msg; i < to_msg; i++) {
                            $('.msg_' + i).remove();
                        }
                    }
                    get_conversation_latest_chat($('#conversation_id').val(), response.latest_request_id);

                } else {
                    toastrs('Error', response.message, 'error');
                }
            }
        });
    });

    $(document).on('click', '.nav_fileModalTab', function () {
        $('.nav_fileModalTab').removeClass('active');
        $(this).addClass('active');
        var tab_href = $(this).attr('href');
        $('#fileModalTabContent .tab-pane').removeClass('active');
        $(tab_href).addClass('active');
    });

    $(document).on('click', '.btn_chat_upload', function (e) {
        e.preventDefault();
        if (document.getElementById("smart-chat-file-input").files.length == 0) {
            toastrs('Error', 'no files selected', 'error');
        } else {
            $("#commonModal").modal('hide');
            setTimeout(function () {
                $('.chat_widget').removeClass('d-none');
                $('#smart-chat-btn').addClass('d-none');
            }, 100);


            $('#smart-chat-image-previews').empty(); // Clear existing previews

            setTimeout(function () {
                var files = document.getElementById("smart-chat-file-input").files;
                const $hiddenFieldsContainer = $('.chat_uploaded_images');
                $hiddenFieldsContainer.empty();
                if (files.length > 0) {
                    $.each(files, function (index, file) {
                        var reader = new FileReader();
                        reader.onload = function (e) {
                            // Create a hidden input field
                            const hiddenInput = $('<input>', {
                                type: 'hidden',
                                name: 'files[]',
                                value: e.target.result
                            });
                            // Append the hidden input to the form
                            $hiddenFieldsContainer.append(hiddenInput);

                            $('#smart-chat-image-previews').append(
                                '<div>' +
                                '<img src="' + e.target.result + '" alt="Image Preview"/>' +
                                '</div>'
                            );
                        }
                        reader.readAsDataURL(file);
                    });
                }
            }, 1000);
        }
    });

    $(document).on('click', '.btn_chat_attach', function (e) {
        e.preventDefault();
        const $checkboxes = $('.image_selection:checked');
        const $hiddenFieldsContainer = $('.chat_uploaded_images');
        $hiddenFieldsContainer.empty();  // Clear any previous fields

        // Clear existing previews

        if ($checkboxes.length == 0) {
            toastrs('Error', 'no files selected', 'error');
        } else {
            $("#commonModal").modal('hide');
            setTimeout(function () {
                $('.chat_widget').removeClass('d-none');
                $('#smart-chat-btn').addClass('d-none');
            }, 100);

            $('#smart-chat-image-previews').empty();

            $checkboxes.each(function () {
                const imageSrc = $(this).data('image_src');

                $('#smart-chat-image-previews').append(
                    '<div>' +
                    '<img src="' + imageSrc + '" alt="Image Preview"/>' +
                    '</div>'
                );

                var img = new Image();
                img.crossOrigin = 'Anonymous'; // To handle CORS issues

                // When the image loads, convert it to base64
                img.onload = function () {
                    var canvas = document.createElement('canvas');
                    var ctx = canvas.getContext('2d');

                    // Set canvas dimensions to match the image
                    canvas.width = img.width;
                    canvas.height = img.height;

                    // Draw the image onto the canvas
                    ctx.drawImage(img, 0, 0);

                    // Get the base64 string from the canvas
                    var base64String = canvas.toDataURL('image/png');

                    // Display the base64 string in the result paragraph
                    $('#result').text(base64String);

                    const hiddenInput = $('<input>', {
                        type: 'hidden',
                        name: 'files[]',
                        value: base64String
                    });
                    $hiddenFieldsContainer.append(hiddenInput)
                };

                // Set image source to the URL
                img.src = imageSrc;

                // Handle image loading errors
                img.onerror = function () {
                    alert('Failed to load image. Make sure the URL is correct and accessible.');
                };
            });
        }
    });

    $(document).on('change', '#smart_template_id', function () {
        var template_id = $(this).val();
        var token = $('meta[name="csrf-token"]').attr('content');
        $.ajax({
            url: base_url + "/get_prompt_data",
            type: "POST",
            contentType: "application/json",
            data: JSON.stringify({ template_id: template_id }),
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                tinymce.get('prompt_input').setContent(response.replace(/&nbsp;/g, ''));
            },
            error: function (error) {
                // Handle any errors that occur during the Ajax request
                console.error("Error sending data to the server:", error);
            }
        });
    });

    $(document).on('click', '.edit_chat_msg', function () {
        var chat_id = $(this).data('id');
        $.ajax({
            url: base_url + "/edit_chat",
            type: "POST",
            data: {
                chat_id: chat_id,
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                if (response.status == true) {
                    var set_message = response.data;
                    tinymce.get('prompt_input').setContent(set_message.replace(/&nbsp;/g, ''));
                    $('#chat_id').val(response.chat_id);
                } else {
                    toastrs("Error", response.message);
                }
            }
        });
    });

    $(document).on('click', '.delete_chat_msg', function () {
        var chat_id = $(this).data('id');
        const swalWithBootstrapButtons = Swal.mixin({
            customClass: {
                confirmButton: 'btn btn-success',
                cancelButton: 'btn btn-danger'
            },
            buttonsStyling: false
        })
        swalWithBootstrapButtons.fire({
            title: 'Are you sure!,,?',
            text: "This action can not be undone. Do you want to continue?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes',
            cancelButtonText: 'No',
            reverseButtons: true,
        }).then((result) => {
            if (result.isConfirmed) {
                var parent_element = $(this).parents('.message-user');
                $.ajax({
                    url: base_url + "/delete_chat",
                    type: "POST",
                    data: {
                        chat_id: chat_id,
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {
                        if (response.status == true) {
                            toastrs("success", response.message);
                            parent_element.remove();
                        } else {
                            toastrs("Error", response.message);
                        }
                    }
                });
            }
        });
    });

    /** call ajaxComplete after open data-popup **/
    $(document).ajaxComplete(function () {
        tinymce.remove();
        document.querySelectorAll('.tinyMCE').forEach(function (editor) {
            init_tiny_mce('#' + editor.id);
        });
    });

    document.querySelectorAll('.tinyMCE').forEach(function (editor) {
        tinymce.remove();
        init_tiny_mce('#' + editor.id);
    });

}); //end document ready function

function load_conversatios() {
    $.ajax({
        url: base_url + "/get_all_conversations",
        type: "POST",
        data: {},
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function (response) {
            if (response.status == true) {
                $('.landing_page .chat-list').html(response.html_data);
            }
        }
    });
}

function get_conversation_chat(conversation_id, action) {
    var token = $('meta[name="csrf-token"]').attr('content');
    var chat_project_id = "";
    if (typeof project_id != 'undefined') {
        chat_project_id = project_id;
    }
    if (is_second_time_call > 0 && action == "refresh_chat") {
        clearInterval(chat_interval);
        $('.default_chat').addClass('d-none');
        return false;
    }
    var file_upload_url = $('.file_upload_modal_button').data('default_url');
    file_upload_url = file_upload_url.replace("conversation_id", conversation_id);
    $('.file_upload_modal_button').attr('data-url', file_upload_url);


    $.ajax({
        url: base_url + "/get_chat",
        type: "POST",
        data: {
            conversation_id: conversation_id,
            action: action,
            chat_project_id: chat_project_id
        },
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function (response) {
            if (response.status == true) {
                $('.landing_page').addClass('d-none');
                $('.chatting_page').removeClass('d-none');
                $('#conversation_id').val(response.conversation_id);
                if (action == "open_chat") {
                    $('.chat-messages .all_chats').html(response.html_data);
                    $('#ai_model_id').val(response.ai_model_id);
                    const all_chats = $('.chat-messages');
                    all_chats.animate({
                        scrollTop: all_chats.prop("scrollHeight")
                    }, 1000);
                    if (chat_interval != null) {
                        clearInterval(chat_interval);
                    }
                } else if (action == "refresh_chat") {
                    $('.chat-messages .all_chats').append(response.html_data);
                    const all_chats = $('.chat-messages');
                    all_chats.animate({
                        scrollTop: all_chats.prop("scrollHeight")
                    }, 'slow');
                    clearInterval(chat_interval);
                    is_second_time_call = chat_interval;
                }
            }
        }
    });
}

function get_conversation_latest_chat(conversation_id, latest_request_id, dots_dispay = 1) {
    $.ajax({
        url: base_url + "/get_latest_chat",
        type: "POST",
        data: {
            conversation_id: conversation_id,
            latest_request_id: latest_request_id
        },
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function (response) {
            if (response.status == true) {
                if (response.latest_msg_id != latest_request_id) {
                    $('.dots-msg').addClass('d-none');
                    $('.chat-messages .all_chats').append(response.html_data);
                    const all_chats = $('.chat-messages');
                    all_chats.animate({
                        scrollTop: all_chats.prop("scrollHeight")
                    }, 'slow');
                }
                if (response.display_dots == true) {
                    if (response.latest_msg_id == latest_request_id) {
                        if (dots_dispay == 0) {
                            //	$('.dots-msg').addClass('d-none');
                        }
                        setTimeout(function () {
                            get_conversation_latest_chat(conversation_id, response.latest_msg_id, 0);
                        }, 3000);
                    }
                } else {
                    $('.dots-msg').addClass('d-none');
                    //	alert("all_done");
                }
            }
        }
    });
}

function chat_request_action(action, chat_id) {
    $.ajax({
        url: base_url + "/chat_request_action",
        type: "POST",
        contentType: "application/json",
        data: JSON.stringify({ action: action, chat_id: chat_id }),
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function (response) {
            if (response.status == true) {
                toastrs("success", response.message);

                $('.chat-messages .all_chats').append(response.html_data);
                const all_chats = $('.chat-messages');
                all_chats.animate({
                    scrollTop: all_chats.prop("scrollHeight")
                }, 'slow');
                get_conversation_latest_chat($('#conversation_id').val(), response.latest_request_id);

                //	get_conversation_chat($('#conversation_id').val(), 'open_chat');
            } else {
                toastrs("Error", response.message);
            }
        },
        error: function (error) {
            // Handle any errors that occur during the Ajax request
            console.error("Error sending data to the server:", error);
        }
    });
}

function smart_chat_response_action(action, smart_chat_id, project_id) {
    var token = $('meta[name="csrf-token"]').attr('content');

    $.ajax({
        url: base_url + "/chat_response_action",
        type: "POST",
        contentType: "application/json",
        data: JSON.stringify({ action: action, smart_chat_id: smart_chat_id, project_id: project_id }),
        success: function (response) {
            if (response.status == true) {
                toastrs("success", response.message);
            } else {
                toastrs("Error", response.message);
            }
        },
        error: function (error) {
            // Handle any errors that occur during the Ajax request
            console.error("Error sending data to the server:", error);
        }
    });
}

function summernote() {
    if ($(".summernote").length > 0) {
        $($(".summernote")).each(function (index, element) {
            var id = $(element).attr('id');
            $('#' + id).summernote({
                placeholder: "Write Here… ",
                tabsize: 2,
                minHeight: 200,
                maxHeight: 250,
                toolbar: [
                    ['style', ['style']],
                    ['color', ['color']],
                    ['font', ['bold', 'italic', 'underline', 'strikethrough']],
                    ['list', ['ul', 'ol', 'paragraph']],
                    ['insert', ['link', 'unlink']],
                ]
            });
        });
    }
}

function toastrs(text, message, type) {
    var f = document.getElementById('liveToast');
    var a = new bootstrap.Toast(f).show();
    if (type == 'success') {
        $('#liveToast').addClass('bg-primary');
    } else {
        $('#liveToast').addClass('bg-danger');
    }
    $('#liveToast .toast-body').html(message);
}

function formatState(state) {

    var userType = '';
    $(state.element).each(function (t, p) {

        userType = $(this).attr('data-type');
    });

    if (!state.id) {
        return state.text;
    }

    var clientSvgIcon = '';

    if (userType == 'client') {

        clientSvgIcon = '<i class="fa-solid fa-user-tie client-icon"></i>';

    } else if (userType == 'employee') {

        clientSvgIcon = '<i class="fa-solid fa-address-card employee-icon"></i>';

    } else if (userType == 'subcontractor') {

        clientSvgIcon = '<i class="fa-solid fa-user-gear subcontractor-icon"></i>';

    } else if (userType == 'construction_detail') {

        clientSvgIcon = '<i class="fa-solid fa-user-gear subcontractor-icon"></i>';
    }

    var $state = $(
        '<span>  ' + clientSvgIcon + state.text + '</span>'
    );
    return $state;

};

function init_select2() {
    if ($('#contacts-select2').length > 0) {
        $('#contacts-select2').select2({
            dropdownParent: $("#commonModal"),
            tags: true,
            allowHtml: true,
            multiple: true,
            templateResult: formatState,
            // templateSelection: formatState,
        });
        // Fix for search functionality not working in modal
        $.fn.modal.Constructor.prototype._enforceFocus = function () { };
    }
}

function showHideLoader(type) {
    if (type == 'hidden') {
        $(".loader-wrapper").addClass('d-none');
        //	document.getElementById("loader-wrapper").style.visibility = "hidden";
    } else if (type == 'visible') {
        $(".loader-wrapper").removeClass('d-none');
        //	document.getElementById("loader-wrapper").style.visibility = "visible";
    }
}

$('#commonModal').on('hidden.bs.modal', function () {
    if ($('#commonModal #project-description').length > 0) {
        //	tinymce.activeEditor.remove("textarea");
        tinymce.get('project-description').remove();
    }
    if ($('#commonModal #event_description').length > 0) {
        //	tinymce.activeEditor.remove("textarea");
        tinymce.get('event_description').remove();
    }
    if ($('#commonModal #construction_detail-company_notes').length > 0) {
        //	tinymce.activeEditor.remove("textarea");
        tinymce.get('construction_detail-company_notes').remove();
    }
    if ($('#commonModal #client-company_notes').length > 0) {
        //	tinymce.activeEditor.remove("textarea");
        tinymce.get('client-company_notes').remove();
    }
    if ($('#commonModal #technical-description').length > 0) {
        tinymce.get('technical-description').remove();
    }

    if ($('#commonModal .color_picker').length > 0) {
        $(".color_picker").minicolors('destroy');
    }
})

function initialize() {
    var input = document.getElementById('invoice-autocomplete');
    var autocomplete = new google.maps.places.Autocomplete(input);
    autocomplete.addListener('place_changed', function () {
        var place = autocomplete.getPlace();
        var google_address = get_address_google(place, 'invoice');
    });
}

function initialize_construction() {
    var input = document.getElementById('construction_detail-autocomplete');
    var autocomplete = new google.maps.places.Autocomplete(input);
    autocomplete.addListener('place_changed', function () {
        var place = autocomplete.getPlace();
        var google_address = get_address_google(place, 'construction_detail');
    });
}

function init_mini_colors(selector_id) {
    $(selector_id).minicolors({
        control: $(this).attr('data-control') || 'hue',
        defaultValue: $(this).attr('data-defaultValue') || '',
        format: $(this).attr('data-format') || 'hex',
        keywords: $(this).attr('data-keywords') || '',
        inline: $(this).attr('data-inline') === 'true',
        letterCase: $(this).attr('data-letterCase') || 'lowercase',
        opacity: $(this).attr('data-opacity'),
        position: $(this).attr('data-position') || 'bottom',
        swatches: $(this).attr('data-swatches') ? $(this).attr('data-swatches').split('|') : [],
        change: function (value, opacity) {
            if (!value) return;
            if (opacity) value += ', ' + opacity;
            if (typeof console === 'object') {
                console.log(value);
            }
        },
        theme: 'bootstrap'
    });
}

function init_tiny_mce(selector_id) {
    tinymce.init({
        selector: selector_id,
        height: 270,
        plugins: 'print preview importcss searchreplace autolink autosave save directionality visualblocks visualchars fullscreen image link media template codesample table charmap hr pagebreak nonbreaking anchor toc insertdatetime advlist lists wordcount imagetools textpattern noneditable help charmap quickbars emoticons code',

        mobile: {
            plugins: 'print preview importcss searchreplace autolink autosave save directionality visualblocks visualchars fullscreen image link media template codesample table charmap hr pagebreak nonbreaking anchor toc insertdatetime advlist lists wordcount textpattern noneditable help charmap quickbars emoticons code'
        },
        menu: {
            tc: {
                title: 'Comments',
                items: 'addcomment showcomments deleteallconversations'
            }
        },
        menubar: 'file edit view insert format tools table tc help',
        toolbar: 'undo redo | bold italic underline strikethrough | fontselect fontsizeselect formatselect | alignleft aligncenter alignright alignjustify | outdent indent |  numlist bullist | forecolor backcolor casechange removeformat | pagebreak | charmap emoticons | code | fullscreen  preview save print | insertfile image media template link anchor codesample | a11ycheck ltr rtl | showcomments addcomment',
        autosave_ask_before_unload: true,
        deprecation_warnings: false,
        autosave_interval: '30s',
        autosave_prefix: '{path}{query}-{id}-',
        autosave_restore_when_empty: false,
        autosave_retention: '2m',
        image_class_list: [
            { title: 'None', value: '' },
            { title: 'Some class', value: 'class-name' }
        ],
        importcss_append: true,
        templates: [
            { title: 'New Table', description: 'creates a new table', content: '<div class="mceTmpl"><table width="98%%"  border="0" cellspacing="0" cellpadding="0"><tr><th scope="col"> </th><th scope="col"> </th></tr><tr><td> </td><td> </td></tr></table></div>' },
            { title: 'Starting my story', description: 'A cure for writers block', content: 'Once upon a time...' },
            { title: 'New list with dates', description: 'New List with dates', content: '<div class="mceTmpl"><span class="cdate">cdate</span><br /><span class="mdate">mdate</span><h2>My List</h2><ul><li></li><li></li></ul></div>' }
        ],
        template_cdate_format: '[Date Created (CDATE): %m/%d/%Y : %H:%M:%S]',
        template_mdate_format: '[Date Modified (MDATE): %m/%d/%Y : %H:%M:%S]',
        image_caption: true,
        quickbars_selection_toolbar: 'bold italic | quicklink h2 h3 blockquote quickimage quicktable',
        noneditable_noneditable_class: 'mceNonEditable',
        toolbar_mode: 'sliding',
        content_style: '.mymention{ color: gray; }',
        contextmenu: 'link image imagetools table',
        setup: function (ed) {
            ed.on('change', function (e) {
                if (selector_id == "#technical_description") {
                    $(selector_id).trigger('change');
                    console.log('the content ', ed.getContent());
                }
            });
        }
    });
}


$(document).on('click', 'a[data-ajax-popup="true"], button[data-ajax-popup="true"], div[data-ajax-popup="true"]', function () {
    var title = $(this).data('title');
    var size = ($(this).data('size') == '') ? 'md' : $(this).data('size');
    var url = $(this).data('url');
    $("#commonModal .modal-title").html(title);
    $("#commonModal .modal-dialog").addClass('modal-' + size);
    $.ajax({
        url: url,
        beforeSend: function () {
            $(".loader-wrapper").removeClass('d-none');
        },
        success: function (data) {
            $(".loader-wrapper").addClass('d-none');
            $('#commonModal .modal-body').html(data);
            $("#commonModal").modal('show');
            summernote();
            taskCheckbox();
            common_bind("#commonModal");
            init_select2();

            if ($('#client-select').length > 0) {
                $('#client-select').select2({
                    dropdownParent: $("#commonModal"),
                    tags: true,
                    allowHtml: true,
                    templateResult: formatState,
                    createTag: function (params) {
                        return {
                            id: params.term,
                            text: 'Create New'
                        }
                    }
                });
            }
            if ($('#construction-select').length > 0) {
                $('#construction-select').select2({
                    dropdownParent: $("#commonModal"),
                    tags: true,
                    allowHtml: true,
                    templateResult: formatState,
                    createTag: function (params) {
                        return {
                            id: params.term,
                            text: 'Create New'
                        }
                    }
                });
            }

            if ($('.color_picker').length > 0) {
                init_mini_colors('.color_picker');
            }
        },
        error: function (xhr) {
            $(".loader-wrapper").addClass('d-none');
            toastrs('Error', xhr.responseJSON.error, 'error')
        }
    });
});

$(document).on('click', 'a[data-ajax-popup-over="true"], button[data-ajax-popup-over="true"], div[data-ajax-popup-over="true"]', function () {

    var validate = $(this).attr('data-validate');
    var id = '';
    if (validate) {
        id = $(validate).val();
    }

    var title = $(this).data('title');
    var size = ($(this).data('size') == '') ? 'md' : $(this).data('size');
    var url = $(this).data('url');

    $("#commonModalOver .modal-title").html(title);
    $("#commonModalOver .modal-dialog").addClass('modal-' + size);

    $.ajax({
        url: url + '?id=' + id,
        beforeSend: function () {
            $(".loader-wrapper").removeClass('d-none');
        },
        success: function (data) {
            $(".loader-wrapper").addClass('d-none');
            $('#commonModalOver .body').html(data);
            $("#commonModalOver").modal('show');
            summernote();
            taskCheckbox();
        },
        error: function (xhr) {
            $(".loader-wrapper").addClass('d-none');
            toastrs('Error', xhr.responseJSON.error, 'error')
        }
    });

});

function arrayToJson(form) {
    var data = $(form).serializeArray();
    var indexed_array = {};

    $.map(data, function (n, i) {
        indexed_array[n['name']] = n['value'];
    });

    return indexed_array;
}

$(document).on("submit", "#commonModalOver form", function (e) {
    e.preventDefault();
    var data = arrayToJson($(this));
    data.ajax = true;

    var url = $(this).attr('action');
    $.ajax({
        url: url,
        data: data,
        type: 'POST',
        success: function (data) {
            toastrs('Success', data.success, 'success');
            $(data.target).append('<option value="' + data.record.id + '">' + data.record.name + '</option>');
            $(data.target).val(data.record.id);
            $(data.target).trigger('change');
            $("#commonModalOver").modal('hide');


        },
        error: function (data) {
            data = data.responseJSON;
            toastrs('Error', data.error, 'error')
        }
    });
});
function common_bind(selector = "body") {
    var $datepicker = $(selector + ' .datepicker');
    if ($(".datepicker-input").length) {
        const d_disable = new Datepicker(document.querySelector('.datepicker-input'), {
            buttonClass: 'btn',
            autohide: true
        });

    }
    if ($(".flatpickr-time-input").length) {
        $(".flatpickr-time-input").flatpickr({
            enableTime: true,
            dateFormat: "Y-m-d H:i",
        });
    }
    if ($(".flatpickr-input").length) {
        $(".flatpickr-input").flatpickr({
            enableTime: false,
            dateFormat: "Y-m-d",
        });
    }
    if ($(".multi-flatpickr-input").length) {
        $(".multi-flatpickr-input").flatpickr({
            mode: "multiple",
            enableTime: false,
            dateFormat: "Y-m-d",
        });
    }
    if ($(".pc-timepicker-2").length) {
        document.querySelector(".pc-timepicker-2").flatpickr({
            enableTime: true,
            noCalendar: true,
        });
    }
    if ($(".flatpickr-with-datetime").length) {
        $(".flatpickr-with-datetime").flatpickr({
            enableTime: true,
            dateFormat: "Y-m-d H:i",
        });
    }
    if ($(".flatpickr-to-input").length) {
        $(".flatpickr-to-input").flatpickr({
            mode: "range",
            dateFormat: "Y-m-d",
        });
    }
    if ($(".custom-datepicker").length) {
        $('.custom-datepicker').daterangepicker({
            singleDatePicker: true,
            format: 'Y-MM',
            locale: {
                format: 'Y-MM'
            }
        });
    }

    if ($(".choices").length > 0) {
        $($(".choices")).each(function (index, element) {
            var id = $(element).attr('id');
            var searchEnabled = $(element).attr('searchEnabled');
            if (searchEnabled == undefined) {
                searchEnabled = false;
            }
            else if (searchEnabled == 'true') {
                searchEnabled = true;
            }
            else {
                searchEnabled = false;
            }
            if (id !== undefined) {
                var multipleCancelButton = new Choices(
                    '#' + id, {
                    loadingText: 'Loading...',
                    searchEnabled: searchEnabled,
                    removeItemButton: true,
                }
                );
            }
        });
    }

    if ($(".jscolor").length) {
        jscolor.installByClassName("jscolor");
    }
    if ($("[avatar]").length) {

        LetterAvatar.transform();
    }
}
function choices(id = null) {
    if ($(".choices").length > 0) {
        $($(".choices")).each(function (index, element) {
            if (id != null) {
                var id = id;
            }
            else {
                var id = $(element).attr('id');
            }

            if (id !== undefined) {
                var multipleCancelButton = new Choices(
                    '#' + id, {
                    removeItemButton: true,
                }
                );
            }
        });
    }
}
function common_bind_confirmation() {
    if ($("[data-confirm]").length) {

        $('[data-confirm]').each(function () {
            var me = $(this),
                me_data = me.data('confirm');

            me_data = me_data.split("|");
            me.fireModal({
                title: me_data[0],
                body: me_data[1],
                buttons: [
                    {
                        text: me.data('confirm-text-yes') || 'Yes',
                        class: 'btn btn-sm btn-danger rounded-pill',
                        handler: function () {
                            eval(me.data('confirm-yes'));
                        }
                    },
                    {
                        text: me.data('confirm-text-cancel') || 'Cancel',
                        class: 'btn btn-sm btn-secondary rounded-pill',
                        handler: function (modal) {
                            $.destroyModal(modal);
                            eval(me.data('confirm-no'));
                        }
                    }
                ]
            })
        });
    }
}
function JsSearchBox() {
    if ($(".js-searchBox").length) {
        $(".js-searchBox").each(function (index) {
            if ($(this).parent().find('.formTextbox').length == 0) {
                $(this).searchBox({ elementWidth: '250' });
            }
        });
    }
}
function taskCheckbox() {
    var checked = 0;
    var count = 0;
    var percentage = 0;

    count = $("#check-list input[type=checkbox]").length;
    checked = $("#check-list input[type=checkbox]:checked").length;
    percentage = parseInt(((checked / count) * 100), 10);
    if (isNaN(percentage)) {
        percentage = 0;
    }
    $(".custom-label").text(percentage + "%");
    $('#taskProgress').css('width', percentage + '%');


    $('#taskProgress').removeClass('bg-warning');
    $('#taskProgress').removeClass('bg-primary');
    $('#taskProgress').removeClass('bg-success');
    $('#taskProgress').removeClass('bg-danger');

    if (percentage <= 15) {
        $('#taskProgress').addClass('bg-danger');
    } else if (percentage > 15 && percentage <= 33) {
        $('#taskProgress').addClass('bg-warning');
    } else if (percentage > 33 && percentage <= 70) {
        $('#taskProgress').addClass('bg-primary');
    } else {
        $('#taskProgress').addClass('bg-success');
    }
}

(function ($, window, i) {
    // Bootstrap 4 Modal
    $.fn.fireModal = function (options) {
        var options = $.extend({
            size: 'modal-md',
            center: false,
            animation: true,
            title: 'Modal Title',
            closeButton: false,
            header: true,
            bodyClass: '',
            footerClass: '',
            body: '',
            buttons: [],
            autoFocus: true,
            created: function () {
            },
            appended: function () {
            },
            onFormSubmit: function () {
            },
            modal: {}
        }, options);
        this.each(function () {
            i++;
            var id = 'fire-modal-' + i,
                trigger_class = 'trigger--' + id,
                trigger_button = $('.' + trigger_class);
            $(this).addClass(trigger_class);
            // Get modal body
            let body = options.body;
            if (typeof body == 'object') {
                if (body.length) {
                    let part = body;
                    body = body.removeAttr('id').clone().removeClass('modal-part');
                    part.remove();
                } else {
                    body = '<div class="text-danger">Modal part element not found!</div>';
                }
            }
            // Modal base template
            var modal_template = '   <div class="modal' + (options.animation == true ? ' fade' : '') + '" tabindex="-1" role="dialog" id="' + id + '">  ' +
                '     <div class="modal-dialog ' + options.size + (options.center ? ' modal-dialog-centered' : '') + '" role="document">  ' +
                '       <div class="modal-content">  ' +
                ((options.header == true) ?
                    '         <div class="modal-header">  ' +
                    '           <h5 class="modal-title mx-auto">' + options.title + '</h5>  ' +
                    ((options.closeButton == true) ?
                        '           <button type="button" class="close" data-dismiss="modal" aria-label="Close">  ' +
                        '             <span aria-hidden="true">&times;</span>  ' +
                        '           </button>  '
                        : '') +
                    '         </div>  '
                    : '') +
                '         <div class="modal-body text-center text-dark">  ' +
                '         </div>  ' +
                (options.buttons.length > 0 ?
                    '         <div class="modal-footer mx-auto">  ' +
                    '         </div>  '
                    : '') +
                '       </div>  ' +
                '     </div>  ' +
                '  </div>  ';
            // Convert modal to object
            var modal_template = $(modal_template);
            // Start creating buttons from 'buttons' option
            var this_button;
            options.buttons.forEach(function (item) {
                // get option 'id'
                let id = "id" in item ? item.id : '';
                // Button template
                this_button = '<button type="' + ("submit" in item && item.submit == true ? 'submit' : 'button') + '" class="' + item.class + '" id="' + id + '">' + item.text + '</button>';
                // add click event to the button
                this_button = $(this_button).off('click').on("click", function () {
                    // execute function from 'handler' option
                    item.handler.call(this, modal_template);
                });
                // append generated buttons to the modal footer
                $(modal_template).find('.modal-footer').append(this_button);
            });
            // append a given body to the modal
            $(modal_template).find('.modal-body').append(body);
            // add additional body class
            if (options.bodyClass) $(modal_template).find('.modal-body').addClass(options.bodyClass);
            // add footer body class
            if (options.footerClass) $(modal_template).find('.modal-footer').addClass(options.footerClass);
            // execute 'created' callback
            options.created.call(this, modal_template, options);
            // modal form and submit form button
            let modal_form = $(modal_template).find('.modal-body form'),
                form_submit_btn = modal_template.find('button[type=submit]');
            // append generated modal to the body
            $("body").append(modal_template);
            // execute 'appended' callback
            options.appended.call(this, $('#' + id), modal_form, options);
            // if modal contains form elements
            if (modal_form.length) {
                // if `autoFocus` option is true
                if (options.autoFocus) {
                    // when modal is shown
                    $(modal_template).on('shown.bs.modal', function () {
                        // if type of `autoFocus` option is `boolean`
                        if (typeof options.autoFocus == 'boolean')
                            modal_form.find('input:eq(0)').focus(); // the first input element will be focused
                        // if type of `autoFocus` option is `string` and `autoFocus` option is an HTML element
                        else if (typeof options.autoFocus == 'string' && modal_form.find(options.autoFocus).length)
                            modal_form.find(options.autoFocus).focus(); // find elements and focus on that
                    });
                }
                // form object
                let form_object = {
                    startProgress: function () {
                        modal_template.addClass('modal-progress');
                    },
                    stopProgress: function () {
                        modal_template.removeClass('modal-progress');
                    }
                };
                // if form is not contains button element
                if (!modal_form.find('button').length) $(modal_form).append('<button class="d-none" id="' + id + '-submit"></button>');
                // add click event
                form_submit_btn.click(function () {
                    modal_form.submit();
                });
                // add submit event
                modal_form.submit(function (e) {
                    // start form progress
                    form_object.startProgress();
                    // execute `onFormSubmit` callback
                    options.onFormSubmit.call(this, modal_template, e, form_object);
                });
            }
            $(document).on("click", '.' + trigger_class, function () {
                $('#' + id).modal(options.modal);
                return false;
            });
        });
    }

    // Bootstrap Modal Destroyer
    $.destroyModal = function (modal) {
        modal.modal('hide');
        modal.on('hidden.bs.modal', function () {
        });
    }
})(jQuery, this, 0);

var Charts = (function () {
    // Variable
    var $toggle = $('[data-toggle="chart"]');
    var mode = 'light';//(themeMode) ? themeMode : 'light';
    var fonts = {
        base: 'Open Sans'
    }

    // Colors
    var colors = {
        gray: {
            100: '#f6f9fc',
            200: '#e9ecef',
            300: '#dee2e6',
            400: '#ced4da',
            500: '#adb5bd',
            600: '#8898aa',
            700: '#525f7f',
            800: '#32325d',
            900: '#212529'
        },
        theme: {
            'default': '#172b4d',
            'primary': '#5e72e4',
            'secondary': '#f4f5f7',
            'info': '#11cdef',
            'success': '#2dce89',
            'danger': '#f5365c',
            'warning': '#fb6340'
        },
        black: '#12263F',
        white: '#FFFFFF',
        transparent: 'transparent',
    };


    // Methods

    // Chart.js global options
    function chartOptions() {

        // Options
        var options = {
            defaults: {
                global: {
                    responsive: true,
                    maintainAspectRatio: false,
                    defaultColor: (mode == 'dark') ? colors.gray[700] : colors.gray[600],
                    defaultFontColor: (mode == 'dark') ? colors.gray[700] : colors.gray[600],
                    defaultFontFamily: fonts.base,
                    defaultFontSize: 13,
                    layout: {
                        padding: 0
                    },
                    legend: {
                        display: false,
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 16
                        }
                    },
                    elements: {
                        point: {
                            radius: 0,
                            backgroundColor: colors.theme['primary']
                        },
                        line: {
                            tension: .4,
                            borderWidth: 4,
                            borderColor: colors.theme['primary'],
                            backgroundColor: colors.transparent,
                            borderCapStyle: 'rounded'
                        },
                        rectangle: {
                            backgroundColor: colors.theme['warning']
                        },
                        arc: {
                            backgroundColor: colors.theme['primary'],
                            borderColor: (mode == 'dark') ? colors.gray[800] : colors.white,
                            borderWidth: 4
                        }
                    },
                    tooltips: {
                        enabled: true,
                        mode: 'index',
                        intersect: false,
                    }
                },
                doughnut: {
                    cutoutPercentage: 83,
                    legendCallback: function (chart) {
                        var data = chart.data;
                        var content = '';

                        data.labels.forEach(function (label, index) {
                            var bgColor = data.datasets[0].backgroundColor[index];

                            content += '<span class="chart-legend-item">';
                            content += '<i class="chart-legend-indicator" style="background-color: ' + bgColor + '"></i>';
                            content += label;
                            content += '</span>';
                        });

                        return content;
                    }
                }
            }
        }

        // yAxes
        Chart.scaleService.updateScaleDefaults('linear', {
            gridLines: {
                borderDash: [2],
                borderDashOffset: [2],
                color: (mode == 'dark') ? colors.gray[900] : colors.gray[300],
                drawBorder: false,
                drawTicks: false,
                drawOnChartArea: true,
                zeroLineWidth: 0,
                zeroLineColor: 'rgba(0,0,0,0)',
                zeroLineBorderDash: [2],
                zeroLineBorderDashOffset: [2]
            },
            ticks: {
                beginAtZero: true,
                padding: 10,
                callback: function (value) {
                    if (!(value % 10)) {
                        return value
                    }
                }
            }
        });

        // xAxes
        Chart.scaleService.updateScaleDefaults('category', {
            gridLines: {
                drawBorder: false,
                drawOnChartArea: false,
                drawTicks: false
            },
            ticks: {
                padding: 20
            },
            maxBarThickness: 10
        });

        return options;

    }

    // Parse global options
    function parseOptions(parent, options) {
        for (var item in options) {
            if (typeof options[item] !== 'object') {
                parent[item] = options[item];
            } else {
                parseOptions(parent[item], options[item]);
            }
        }
    }

    // Push options
    function pushOptions(parent, options) {
        for (var item in options) {
            if (Array.isArray(options[item])) {
                options[item].forEach(function (data) {
                    parent[item].push(data);
                });
            } else {
                pushOptions(parent[item], options[item]);
            }
        }
    }

    // Pop options
    function popOptions(parent, options) {
        for (var item in options) {
            if (Array.isArray(options[item])) {
                options[item].forEach(function (data) {
                    parent[item].pop();
                });
            } else {
                popOptions(parent[item], options[item]);
            }
        }
    }

    // Toggle options
    function toggleOptions(elem) {
        var options = elem.data('add');
        var $target = $(elem.data('target'));
        var $chart = $target.data('chart');

        if (elem.is(':checked')) {

            // Add options
            pushOptions($chart, options);

            // Update chart
            $chart.update();
        } else {

            // Remove options
            popOptions($chart, options);

            // Update chart
            $chart.update();
        }
    }

    // Update options
    function updateOptions(elem) {
        var options = elem.data('update');
        var $target = $(elem.data('target'));
        var $chart = $target.data('chart');

        // Parse options
        parseOptions($chart, options);

        // Toggle ticks
        toggleTicks(elem, $chart);

        // Update chart
        $chart.update();
    }



    // Toggle ticks
    function toggleTicks(elem, $chart) {

        if (elem.data('prefix') !== undefined || elem.data('prefix') !== undefined) {
            var prefix = elem.data('prefix') ? elem.data('prefix') : '';
            var suffix = elem.data('suffix') ? elem.data('suffix') : '';

            // Update ticks
            $chart.options.scales.yAxes[0].ticks.callback = function (value) {
                if (!(value % 10)) {
                    return prefix + value + suffix;
                }
            }

            // Update tooltips
            $chart.options.tooltips.callbacks.label = function (item, data) {
                var label = data.datasets[item.datasetIndex].label || '';
                var yLabel = item.yLabel;
                var content = '';

                if (data.datasets.length > 1) {
                    content += '<span class="popover-body-label mr-auto">' + label + '</span>';
                }

                content += '<span class="popover-body-value">' + prefix + yLabel + suffix + '</span>';
                return content;
            }

        }
    }

    $('.remove_workspace').click(function (event) {
        var form = $(this).closest("form");
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
                form.submit();
            }
        })
    });

    $(document).on('click', '.show_confirm', function () {
        var form = $(this).closest("form");
        var title = $(this).attr("data-confirm");
        var text = $(this).attr("data-text");
        if (title == '' || title == undefined) {
            title = "Are you sure?";

        }
        if (text == '' || text == undefined) {
            text = "This action can not be undone. Do you want to continue?";

        }
        const swalWithBootstrapButtons = Swal.mixin({
            customClass: {
                confirmButton: 'btn btn-success',
                cancelButton: 'btn btn-danger'
            },
            buttonsStyling: false
        })
        swalWithBootstrapButtons.fire({
            title: title,
            text: text,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes',
            cancelButtonText: 'No',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        })
    });




    // Events

    // Parse global options
    if (window.Chart) {
        parseOptions(Chart, chartOptions());
    }

    // Toggle options
    $toggle.on({
        'change': function () {
            var $this = $(this);

            if ($this.is('[data-add]')) {
                toggleOptions($this);
            }
        },
        'click': function () {
            var $this = $(this);

            if ($this.is('[data-update]')) {
                updateOptions($this);
            }
        }
    });


    // Return

    return {
        colors: colors,
        fonts: fonts,
        mode: mode
    };

})();
function postAjax(url, data, cb) {
    var token = $('meta[name="csrf-token"]').attr('content');
    var jdata = { _token: token };

    for (var k in data) {
        jdata[k] = data[k];
    }

    $.ajax({
        type: 'POST',
        url: url,
        data: jdata,
        success: function (data) {
            if (typeof (data) === 'object') {
                cb(data);
            } else {
                cb(data);
            }
        },
    });
}

function deleteAjax(url, data, cb) {
    var token = $('meta[name="csrf-token"]').attr('content');
    var jdata = { _token: token };

    for (var k in data) {
        jdata[k] = data[k];
    }

    $.ajax({
        type: 'DELETE',
        url: url,
        data: jdata,
        success: function (data) {
            if (typeof (data) === 'object') {
                cb(data);
            } else {
                cb(data);
            }
        },
    });
}
// Import Data
function SetData(params, count = 0) {
    if (count < 8) {
        var process_area = document.getElementById("process_area");
        if (process_area) {
            $('#process_area').html(params);
        }
        else {
            setTimeout(function () {
                SetData(params, count + 1);
            }, 500);
        }
    }
    else {
        toastrs('Success', '{{ __("Something went wrong please try again!") }}', 'success');
    }
}

function get_address_google(place, selector = "") {
    var result = {};

    result['latitude'] = place.geometry['location'].lat();
    result['longitude'] = place.geometry['location'].lng();

    for (const component of place.address_components) {

        const componentType = component.types[0];
        if (componentType == "street_number") {
            var street_number = component.long_name;
        }
        if (componentType == "route" || componentType == "street_number") {
            result['address_1'] = component.long_name + ((street_number) ? ', ' + street_number : '');
        } else if (componentType == "street_number") {
            result['address_2'] = '';
        } else if (componentType == "locality") {
            result['city'] = component.long_name;
        } else if (componentType == "sublocality_level_1") {
            result['district_1'] = component.long_name;
        } else if (componentType == "administrative_area_level_3") {
            result['district_2'] = component.long_name;
        } else if (componentType == "administrative_area_level_1") {
            result['state'] = component.long_name;
        } else if (componentType == "postal_code") {
            result['zip_code'] = component.long_name;
        } else if (componentType == "postal_code_suffix") {
            result['zip_code'] = component.long_name;
        } else if (componentType == "country") {
            result['country'] = component.short_name;
        } else {
            result[componentType] = component.long_name;
        }
    }

    if (selector != '') {
        var address_array = ['address_1', 'address_2', 'city', 'district_1', 'district_2', 'state', 'zip_code', 'country'];
        $.each(address_array, function (value, key) {
            var item_selector = $('#' + selector + '-' + key);

            if (key == 'country') {
                var selectedOption = item_selector.find('option[data-iso=""]');
                if (selectedOption.length > 0) {
                    selectedOption.prop('selected', true);
                    item_selector.trigger('change');
                }
            } else {
                item_selector.val('');
            }
        });

        $.each(result, function (key, value) {
            var item_selector = $('#' + selector + '-' + key);
            if (item_selector.length > 0) {
                if (key == 'country') {
                    var selectedOption = item_selector.find('option[data-iso="' + value + '"]');
                    if (selectedOption.length > 0) {
                        selectedOption.prop('selected', true);
                        item_selector.trigger('change');
                    }
                } else {
                    item_selector.val(value);
                }
            }
        })
    } else {
        return result;
    }

}

function generate_string(length) {
    let result = '';
    const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    const charactersLength = characters.length;
    let counter = 0;
    while (counter < length) {
        result += characters.charAt(Math.floor(Math.random() * charactersLength));
        counter += 1;
    }
    return result;
}

function generate_slug(text) {
    var slug = text.toLowerCase().replace(/^\s+|\s+$/gm, '').replace(/[`~!@#$%^&*()_\-+=\[\]{};:'"\\|\/,.<>?\s]/g, ' ').replace(/\s+/g, '_');
    slug = '{' + slug + '}';
    return slug;
}


/** project/{id} page */
const tabs = document.querySelectorAll('#projectContent .tab-pane');
tabs.forEach((tab, index) => {
    if (index > 0) {
        tab.classList.remove('show', 'active');
    }
});

$('[data-backgroundColor], [data-fontColor]').each(function () {
    let backgroundColor = $(this).data('backgroundcolor');
    let fontColor = $(this).data('fontcolor');

    $(this).css({
        'background-color': backgroundColor,
        'color': fontColor,
    });
});

function removeWhitespace(str) {
    return str ? str.replace(/\s+/g, '') : '';
}



$('.range-input').each(function () {
    const range = $(this).find('input[type=range]');
    const value = $('.range-output-value');

    value.each(function () {
        var value = $(this).prev().attr('value');
        $(this).html(value);
    });

    range.on('input', function () {
        value.fadeIn();
        $(this).next(value).html(this.value);
    });
});