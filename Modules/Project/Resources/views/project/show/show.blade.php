@extends('layouts.main')

@push('css')
    <style>
        #card2 {
            right: -61px;
            width: 48%;
        }

        #card1 {
            width: 48%;
        }

        #useradd-8 {
            display: flex;
        }

        #dropBox {
            width: 100%;
            height: 100px;
            margin-top:20px!important;
            border: 2px dashed #ccc;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            cursor: pointer;
        }

        #dropBox:hover {
            border-color: #4CAF50;
        }

        #fileInput {
            display: none;
        }

        #previewContainer {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            margin-top: 20px;
        }

        .preview {
            max-width: 100%;
            max-height: 150px;
            margin: 10px;
        }
        /* The container */
        .container {
            display: block;
            position: relative;
            padding-left: 35px;
            margin-bottom: 25px !important;
            cursor: pointer;
            font-size: 22px;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }

        /* Hide the browser's default checkbox */
        .container input {
            position: absolute;
            opacity: 0;
            cursor: pointer;
            height: 0;
            width: 0;
        }

        /* Create a custom checkbox */
        .checkmark {
            position: absolute;
            top: 0;
            left: 11px;
            height: 15px;
            width: 15px;
            background-color: rgba(var(--bs-danger-rgb), var(--bs-bg-opacity)) !important;
        }
        .header_buttons .checkmark {
            background-color: #0427e9 !important;
        }

        /* Create the checkmark/indicator (hidden when not checked) */
        .checkmark:after {
            content: "";
            position: absolute;
            display: none;
        }

        /* Show the checkmark when checked */
        .container input:checked ~ .checkmark:after {
            display: block;
        }

        /* Style the checkmark/indicator */
        .container .checkmark:after {
            left: 9px;
            top: 5px;
            width: 5px;
            height: 10px;
            border: solid white;
            border-width: 0 3px 3px 0;
            -webkit-transform: rotate(45deg);
            -ms-transform: rotate(45deg);
            transform: rotate(45deg);
        }
        .selected_image .actionbuttons .bg-danger, .default_file .header_buttons .action-btn{
            visibility: visible;
        }

        .progress-step {
            position: relative !important;
        }

        .flex-div span {
            display: flex;
        }

        #progress-table tr.group, #progress-table tr.group:hover {
            background-color: rgba(0, 0, 0, 0.1) !important;
        }
        .construction_detail_address span, .client_invoice_address span, .address-class span { 
            display : block;
        }

        .progress-signature .sign_btn_block{
            display: flex;
            justify-content: space-between;
        }

        .progress-signature .sign_btn_block_small {
            display: flex;
            gap: 1px;
        }
        .progress-signature .progress_final_clear_sig, .progress-signature .progress_final_clear_sig:hover {
            color: #333 !important;
            background: none !important;
            border: none !important;
            margin-top: 1px !important;
        }
        .item-signature .progress_amount {
            border: 1px solid #d8d8d8 !important;
            padding: 10px !important;
            background: #ffffff !important;
            margin-top: 5px !important;
        }
        .dash-sidebar .dash-submenu .dash-link {
            padding: 5px 30px 5px 65px !important;
        }
        #progressdropBox {
            width: 100%;
            height: 100px !important;
            border: 2px dashed #ccc !important;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            cursor: pointer;
        }
        #progressdropBox:hover {
            border-color: #4CAF50 !important;
        }
        .item-signature .progress_files{
            margin-top: 10px !important;
        }
        .progressfileInput {
            display: none;
        }
        .progress_files_row .progress_mediaimg{
            padding: 10px !important;
        }
        .progress_files_row .progress_mediaimg{
            padding: 10px !important;
        }
        .progress_files_row .lightbox-link{
            margin: 0 auto !important;
            display: block !important;
        }
        .progress_files_row .mediabox .mediainfo{
            text-align: center !important;
        }
        .progress_files_row .preview{
            margin: 10px !important;
        }
        .media-body a .fileprev{
            margin: 0 auto !important;
        }
        .progress_files_row #progress_bulk_delete_form .btn-primary{
            background: #48494B !important;
            padding: 5px 10px !important;
            color: #fff !important;
        }
        .progress_files_row #progress_bulk_delete_form .btn-primary i{
            color: #fff !important;
        }
    </style>
@endpush
@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="row">
                @include('project::project.show.utility.header')
                @include('project::project.show.utility.dashboard')

                @includeWhen($project->type == 'project', 'project::project.show.utility.if_project_types')

                @include('project::project.show.utility.activity_log')

                @permission('files manage')
                    @include('project::project.show.section.files')
                @endpermission
                @include('project::project.show.section.estimations')
                @permission('progress manage')
                    @include('project::project.show.section.project_progress')
                @endpermission
                @include('project::project.show.section.delay')

                @include('project::project.show.section.milestone')
            </div>
        </div>
    </div>
    @php
        $canManageTeamMember = Auth::user()->isAbleTo('team member manage');
    @endphp
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/exif-js"></script>
    <script>
        var active_estimation_id = '{{ isset($active_estimation->id) ? $active_estimation->id : 0 }}';
        let moneyFormat = '{{ site_money_format() }}';
        var project_id = '{{ \Crypt::encrypt($project->id) }}';
        var csrfToken =  $('meta[name="csrf-token"]').attr('content')
        
        $(document).ready(function() {

            /** call ajaxComplete after open data-popup **/
            $(document).ajaxComplete(function() {

                dropdownItemsToSetPreMessageContent();

            });

            function dropdownItemsToSetPreMessageContent() {
                /**
                 * Set pre message tempate inside feedback & comment box in popup modal
                 * */
                const dropdownItems = document.querySelectorAll(
                    '.dropdown-premsg .dropdown-menu .dropdown-item');
                const dropdownTriggerText = document.querySelector(
                    '.dropdown-premsg .dropdown-toggle .drp-text');

                dropdownItems.forEach(item => {
                    item.addEventListener('click', function(e) {
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

            var type = '{{ $project->type }}';
            if (type == 'template') {
                $('.pro_type').addClass('d-none');
            } else {
                $('.pro_type').removeClass('d-none');
            }

            set_construction_address();
            getItems(active_estimation_id);

            $(document).on("click", ".projectusers img", function() {
                var csrfToken = $('meta[name="csrf-token"]').attr('content');
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
                            url: '{{ route('estimation.remove_estimation_user') }}',
                            type: "POST",
                            data: {
                                estimation_id: estimation_id,
                                user_id: user_id,
                                _token: csrfToken
                            },
                            beforeSend: function() {
                                showHideLoader('visible');
                            },
                            success: function(response) {
                                if (response.status == true) {
                                    showHideLoader('hidden');
                                    toastrs('Success', response.message, 'success');
                                    setTimeout(function() {
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

            $(document).on("change", "#same_invoice_address", function() {
                $('.different-invoice-address-block').toggleClass('d-none');
            });

            /*** edit feedback ***/
            $(document).on("click", ".client_feedback_edit", function(e) {
                e.preventDefault();
                var feedback_id = $(this).data('id');
                if (feedback_id != '') {
                    $.ajax({
                        url: "{{ route('get.project.client.feedback', $project->id) }}",
                        type: "POST",
                        data: {
                            feedback_id: feedback_id,
                            // _token : csrfToken
                        },
                        beforeSend: function() {
                            showHideLoader('visible');
                        },
                        success: function(response) {
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
            $(document).on("click", ".client_feedback_delete", function(e) {
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
                            url: "{{ route('project.client.feedback.delete', $project->id) }}",
                            type: "POST",
                            data: {
                                feedback_id: feedback_id,
                                //	_token : csrfToken
                            },
                            beforeSend: function() {
                                showHideLoader('visible');
                            },
                            success: function(response) {
                                if (response.status == true) {
                                    showHideLoader('hidden');
                                    $('.feedback_heading' + feedback_id).remove();
                                    $('.feedback_collapse' + feedback_id).remove();
                                    toastrs('Success', response.message, 'success');
                                    setTimeout(function() {
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
            $(document).on("click", ".project_comments_edit", function(e) {
                e.preventDefault();
                var comment_id = $(this).data('id');
                if (comment_id != '') {
                    $.ajax({
                        url: "{{ route('get.project.comment', $project->id) }}",
                        type: "POST",
                        data: {
                            comment_id: comment_id,
                        },
                        beforeSend: function() {
                            showHideLoader('visible');
                        },
                        success: function(response) {
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
            $(document).on("click", ".project_comments_delete", function(e) {
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
                            url: "{{ route('project.comment.delete', $project->id) }}",
                            type: "POST",
                            data: {
                                comment_id: comment_id,
                            },
                            beforeSend: function() {
                                showHideLoader('visible');
                            },
                            success: function(response) {
                                if (response.status == true) {
                                    showHideLoader('hidden');
                                    $('.comment_heading' + comment_id).remove();
                                    $('.comment_collapse' + comment_id).remove();
                                    toastrs('Success', response.message, 'success');
                                    setTimeout(function() {
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

            $(document).on('submit', '.project_detail_form', function(e) {
                e.preventDefault();
                var formdata = $(this).serialize();
                var url = $(this).attr('action');
                $.ajax({
                    type: "post",
                    url: url,
                    data: formdata,
                    cache: false,
                    beforeSend: function() {
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
                    success: function(data) {
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
                    complete: function() {
                        $(this).find('.btn-create').removeAttr('disabled');
                    },
                });
            });


            $(document).on("select2:clear", "#property", function(e) {
                store_to_project_data('property_type', e);
            });
            $(document).on("select2:clear", "#construction_type", function(e) {
                store_to_project_data('construction_type', e)
            });

            $('.filter_select2').select2({
                placeholder: "Select",
                //	multiple: true,
                tags: true,
                templateSelection: function(data, container) {
                    $(container).css("background-color", $(data.element).data("background_color"));
                    if (data.element) {
                        $(container).css("color", $(data.element).data("font_color"));
                    }
                    return data.text;
                }
            });
           
            //Team member select2
            $('.member_select2').select2({
                placeholder: "Nutzer wählen",
                tags: true,
                disabled:{{ $canManageTeamMember ? 'false' : 'true' }},
                allowHtml: true,
                templateResult: formatState,
                templateSelection: function(data, container) {
                    $(container).css("background-color", $(data.element).data("background_color"));
                    if (data.element) {
                        $(container).css("color", $(data.element).data("font_color"));
                    }
                    return data.text;
                }
            });
        });

        //Team Member Ajax
        function save_project_member_details(event) {
            var user_ids = $(event).val();
            $.ajax({
                url: '{{ route('project.member.add', $project->id) }}',
                type: "POST",
                data: {
                    users: user_ids, // Send the selected user IDs as an array (changed 'user' to 'users')
                    "_token": $('meta[name="csrf-token"]').attr('content')
                },
                success: function(data) {
                    console.log(data);
                    if (data.is_success) {
                        $('.projectteamcount').html(data.count);
                        toastrs('Success', data.message, 'success');
                    } else {
                        toastrs('Error', data.message, 'error');
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    toastrs('Error', 'Something went wrong: ' + textStatus, 'error');
                }
            });
        }

        function store_to_project_data(field, event) {
            if (field != "") {
                var field_value = $(event).val();
                if (field_value != "" && field_value != null) {
                    if (field == "label") {
                        field_value = field_value.join(", ")
                    }
                    if (field == "construction_type") {
                        field_value = field_value.join(", ")
                    }
                    if (field == "property_type") {
                        field_value = field_value.join(", ")
                    }
                    if (field == "priority") {
                        field_value = field_value.join(", ")
                    }
                }
                $.ajax({
                    url: '{{ route('project.add.status_data', $project->id) }}',
                    type: "POST",
                    data: {
                        field: field,
                        field_value: field_value,
                        "_token": $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(data) {
                        if (data.is_success) {
                            toastrs('Success', data.message, 'success');
                        } else {
                            toastrs('Error', data.message, 'error');
                        }
                    }
                });
            }
        }

        function set_construction_address() {
            var csrfToken = $('meta[name="csrf-token"]').attr('content');
            $.ajax({
                url: '{{ route('project.get_all_address', $project->id) }}',
                type: "POST",
                data: {
                    html: true,
                    _token: csrfToken
                },
                success: function(response) {
                    if (response.status == true) {
                        $(".project_all_address").html(response.html_data);
                    }
                }
            });
        }

        function selected_estimations() {
            var total_selected = 0;
            var estimation_ids = [];
            $('.estimation_selection').each(function() {

                if ($(this).prop('checked') == true) {
                    total_selected++;
                    var estimation_id = $(this).val();
                    estimation_ids.push(estimation_id);
                }
            });
            if (total_selected > 0) {
                $('.delete_estimation_form').removeClass('d-none');
                $('.btn_bulk_delete_estimations').addClass('show_confirm');
                $('.btn_bulk_delete_estimations').removeClass('show_error_toaster');
            } else {
                $('.delete_estimation_form').addClass('d-none');
                $('.btn_bulk_delete_estimations').removeClass('show_confirm');
                $('.btn_bulk_delete_estimations').addClass('show_error_toaster');
            }
            $('#remove_estimation_ids').val(JSON.stringify(estimation_ids));

        }

        function getItems(estimation_id) {
            let project_id = '{{ $project->id }}';
            var csrfToken = $('meta[name="csrf-token"]').attr('content');

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
                    "url": '{{ route('progress.list') }}',
                    "type": "POST",
                    data: {
                        project_id: project_id,
                        _token: csrfToken
                    },
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
                initComplete: function(settings, json) {

                },
            });
        }
    </script>
@endpush

@push('scripts')
    <script>
        //Section: Progress (Last Week Tasks) 
        (function() {
            var options = {
                chart: {
                    height: 135,
                    type: 'line',
                    toolbar: {
                        show: false,
                    },
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    width: 2,
                    curve: 'smooth'
                },
                series: [
                    @foreach ($chartData['stages'] as $id => $name)
                        {
                            name: "{{ __($name) }}", // Use Laravel translation helper
                            data: {!! json_encode($chartData[$id]) !!}, // Safely convert to JSON
                        },
                    @endforeach
                ],
                xaxis: {
                    categories: {!! json_encode($chartData['label']) !!}, // Safely convert to JSON
                },
                colors: {!! json_encode($chartData['color']) !!}, // Safely convert to JSON

                grid: {
                    strokeDashArray: 4,
                },
                legend: {
                    show: false,
                },

                yaxis: {
                    tickAmount: 5,
                    min: 1,
                    max: 40,
                },
            };

            /*var chart = new ApexCharts(document.querySelector("#task-chart"), options);
            chart.render();*/
        })();

        // Copy link to clipboard
        $('.cp_link').on('click', function() {
            var value = $(this).attr('data-link');
            var $temp = $("<input>");
            $("body").append($temp);
            $temp.val(value).select();
            document.execCommand("copy");
            $temp.remove();

            // Show toastr notification on success
            toastrs('success', '{{ __('Link Copy on Clipboard') }}', 'success');
        });
    </script>
@endpush

@push('scripts')
    {{-- <script>
        Dropzone.autoDiscover = false;
        var myDropzone = new Dropzone("#dropzonewidget", {
            maxFiles: 20,
            maxFilesize: 20, // In MB
            parallelUploads: 1,
            acceptedFiles: ".jpeg,.jpg,.png,.pdf,.doc,.txt",
            url: "{{ route('projects.file.upload', [$project->id]) }}",
            success: function(file, response) {
                if (response.is_success) {
                    dropzoneBtn(file, response);
                    toastrs('{{ __('Success') }}', 'File Successfully Uploaded', 'success');
                } else {
                    myDropzone.removeFile(file);
                    toastrs('Error', response.error, 'error');
                }
            },
            error: function(file, response) {
                myDropzone.removeFile(file);
                toastrs('Error', response.error || response, 'error');
            }
        });

        myDropzone.on("sending", function(file, xhr, formData) {
            formData.append("_token", $('meta[name="csrf-token"]').attr('content'));
            formData.append("project_id", {{ $project->id }});
        });


        function dropzoneBtn(file, response) {
            var html = document.createElement('div');

            var download = document.createElement('a');
            download.setAttribute('href', response.download);
            download.setAttribute('class', "action-btn btn-primary mx-1 btn btn-sm d-inline-flex align-items-center");
            download.setAttribute('data-toggle', "tooltip");
            download.setAttribute('download', file.name);
            download.setAttribute('title', "{{ __('Download') }}");
            download.innerHTML = "<i class='ti ti-download'></i>";
            html.appendChild(download);

            var lightboxLink = document.createElement('a');
            lightboxLink.setAttribute('href', response.download);
            lightboxLink.setAttribute('data-lightbox', "gallery");
            lightboxLink.setAttribute('data-title', file.name);
            lightboxLink.style.display = 'none';
            file.previewTemplate.appendChild(lightboxLink);

            file.previewTemplate.querySelector('img').addEventListener('click', function() {
                lightboxLink.click();
            });

            var view = document.createElement('a');
            view.setAttribute('href', response.download);
            view.setAttribute('class', "action-btn btn-secondary mx-1 btn btn-sm d-inline-flex align-items-center");
            view.setAttribute('target', "_blank");
            view.setAttribute('title', "{{ __('View') }}");
            view.innerHTML = "<i class='ti ti-crosshair'></i>";
            html.appendChild(view);


            var del = document.createElement('a');
            del.setAttribute('href', response.delete);
            del.setAttribute('class', "action-btn btn-danger mx-1 btn btn-sm d-inline-flex align-items-center");
            del.setAttribute('data-toggle', "popover");
            del.setAttribute('title', "{{ __('Delete') }}");
            del.innerHTML = "<i class='ti ti-trash'></i>";

            del.addEventListener("click", function(e) {
                e.preventDefault();
                if (confirm("Are you sure?")) {
                    $.ajax({
                        url: del.getAttribute('href'),
                        type: 'DELETE',
                        success: function(response) {
                            if (response.is_success) {
                                del.closest('.dz-image-preview').remove();
                                toastrs('{{ __('Success') }}', 'File Successfully Deleted',
                                    'success');
                            } else {
                                toastrs('{{ __('Error') }}', 'Something went wrong.', 'error');
                            }
                        },
                        error: function() {
                            toastrs('{{ __('Error') }}', 'Something went wrong.', 'error');
                        }
                    });
                }
            });

            html.appendChild(del);


            file.previewTemplate.appendChild(html);
        }
    </script> --}}
@endpush

<!--- Files Upload -->
@push('scripts')
    <script>

        $(document).ready(function(){
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
            previewContainer.innerHTML = ''; // Clear out any previous previews
            let formData = new FormData();
            let counter = 0; // Counter for processed files

            Array.from(files).forEach((file) => {
                if (!file.type.startsWith('image/')) {
                    formData.append('files[]', file, file.name);
                    counter++;
                    if (counter === files.length) {
                        uploadFile(formData);
                    }
                } else {
                    const reader = new FileReader();
                    reader.onload = function(event) {
                        const img = new Image();
                        img.src = event.target.result;
                        img.onload = function() {
                            EXIF.getData(img, function() {
                                const dateTaken = EXIF.getTag(this, 'DateTimeOriginal'); // Get the original date from EXIF data
                                const canvas = document.createElement('canvas');
                                const ctx = canvas.getContext('2d');
                                const max_width = 1500;
                                const scaleFactor = max_width / img.width;
                                canvas.width = max_width;
                                canvas.height = img.height * scaleFactor;
                                ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                                
                                // Funktion zum Hinzufügen von Text mit Hintergrund
                                function drawTextWithBackground(ctx, text, x, y, bgColor, textColor, padding) {
                                    ctx.fillStyle = bgColor;
                                    ctx.font = 'bold 20px Arial';

                                    const textMetrics = ctx.measureText(text);
                                    const textWidth = textMetrics.width;
                                    const textHeight = 20; // Geschätzte Höhe basierend auf Schriftgröße

                                    // Berechne die Position und Größe des Hintergrunds mit Padding
                                    const backgroundX = canvas.width - textWidth - padding.leftRight - x;
                                    const backgroundY = canvas.height - textHeight - padding.topBottom - y;
                                    const backgroundWidth = textWidth + padding.leftRight * 2;
                                    const backgroundHeight = textHeight + padding.topBottom * 2;

                                    // Zeichne den Hintergrund
                                    ctx.fillRect(backgroundX, backgroundY, backgroundWidth, backgroundHeight);

                                    // Zeichne den Text
                                    ctx.fillStyle = textColor;
                                    ctx.fillText(text, backgroundX + padding.leftRight, backgroundY + textHeight);
                                }

                                // Add the text with background to the canvas
                                const dateText = dateTaken ? formatDate(dateTaken) : '';
                                drawTextWithBackground(ctx, dateText, 10, 10, '#ee232ac2', '#FFF', { topBottom: 2, leftRight: 5 });

                                ctx.canvas.toBlob(function(blob) {
                                    const compressedFile = new File([blob], file.name, {
                                        type: 'image/jpeg',
                                        lastModified: Date.now(),
                                    });
                                    formData.append('files[]', compressedFile, compressedFile.name);
                                    
                                    // Add image preview
                                    const preview = document.createElement('img');
                                    preview.src = URL.createObjectURL(compressedFile);
                                    preview.classList.add('preview');
                                    previewContainer.appendChild(preview);

                                    counter++;
                                    if (counter === files.length) {
                                        uploadFile(formData);
                                    }
                                }, 'image/jpeg', 0.85); // Compress as JPEG with 85% quality
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
                url: '{{route('project.files_upload',$project->id)}}',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                beforeSend: function() {
                    showHideLoader('visible'); // Optional: Show a loader graphic
                },
                success: function(response) {
                    showHideLoader('hidden'); // Optional: Hide the loader
                    toastrs('Success', response.message, 'success');
                    load_gallary(); // Refresh or update the gallery
                },
                error: function(xhr, status, error) {
                    // Handle errors
                    console.error('Upload failed:', error);
                    showHideLoader('hidden'); // Hide the loader even if there's an error
                }
            });
        }

        $(document).on("click", "#dropBox", function(e) {
			e.preventDefault();
			$("#fileInput").trigger('click');
        });

        //Load Gallery
        function load_gallary() {
            $.ajax({
                url:'{{route('project.all_files',$project->id)}}',
                type:"POST",
                data:{html:true,_token:csrfToken},
                success:function (items) {
                    $(".mediabox").html(items);
					$("img.preview").remove();
					selected_images();
                }
            })
		}

        //set default image
        $(document).on("click", ".default_image_selection", function(e) {
            // e.preventDefault();

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
                        url:'{{route('project.files.set_default_file',$project->id)}}',
                        type:"POST",
                        data:{file : file_id,_token:csrfToken},
                        beforeSend:function () {
                            showHideLoader('visible');
                        },
                        success:function (response) {
                            if(response.status == true){
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
        $(document).on("submit", "#bulk_delete_form", function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            $.ajax({
                url:'{{route('project.files.delete',$project->id)}}',
                type:"POST",
                data:formData,
                contentType:false,
                processData:false,
                beforeSend:function () {
                    showHideLoader('visible');
                },
                success:function (response) {
                    if(response.status == true){
                        showHideLoader('hidden');
                        toastrs('Success', response.message, 'success')
                        load_gallary();
                    } else {
                        toastrs('Error', response.message)
                    }
                }
            });
        });

        function selected_images() {
			var total_selected = 0;
			var files_ids = [];
			$('.image_selection').each(function () {
				var id = $(this).data('id');
				if ($(this).prop('checked')==true){
					total_selected++;
					var file_id = $(this).val();
					files_ids.push(file_id);
					$(".project_file_"+id).parents('.mediaimg').addClass('selected_image');
				} else {
					$(".project_file_"+id).parents('.mediaimg').removeClass('selected_image');
				}
			});
			if(total_selected > 0){
				$('.btn_bulk_delete_files').removeClass('d-none');
			} else {
				$('.btn_bulk_delete_files').addClass('d-none');
			}
			$('#remove_files_ids').val(JSON.stringify(files_ids));

		}
    </script>
@endpush
