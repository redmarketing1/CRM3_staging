@extends('layouts.main')
@php
    if ($project->type == 'project') {
        $name = 'Project';
    } else {
        $name = 'Project Template';
    }
@endphp
@section('page-title')
    {{ __($name . ' Detail') }}
@endsection



@section('page-breadcrumb')
    {{ __($name . ' Detail') }}
@endsection

@section('page-action')
    @if ($project->type == 'project')
        @stack('addButtonHook')
    @else
        @stack('projectConvertButton')
    @endif
    <div class="col-md-auto col-sm-4 pb-3">
        <a href="#" class="btn btn-xs btn-primary btn-icon-only col-12 cp_link"
            data-link="{{ route('project.shared.link', [\Illuminate\Support\Facades\Crypt::encrypt($project->id)]) }}"
            data-toggle="tooltip" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Copy') }}">
            <span class="btn-inner--text text-white">
                <i class="ti ti-copy"></i></span>
        </a>
    </div>
    @permission('project setting')
        @php
            $title =
                module_is_active('ProjectTemplate') && $project->type == 'template'
                    ? __('Shared Project Template Settings')
                    : __('Shared Project Settings');
        @endphp
        <div class="col-sm-auto">
            <a href="#" class="btn btn-xs btn-primary btn-icon-only col-12" data-title="{{ $title }}"
                data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip"
                data-bs-original-title="{{ __('Shared Project Setting') }}"
                data-url="{{ route('project.setting', [$project->id]) }}">
                <i class="ti ti-settings"></i>
            </a>
        </div>
    @endpermission
    <div class="col-sm-auto">
        <a href="{{ route('projects.gantt', [$project->id]) }}"
            class="btn btn-xs btn-primary btn-icon-only width-auto ">{{ __('Gantt Chart') }}</a>
    </div>
    @permission('task manage')
        <div class="col-sm-auto">
            <a href="{{ route('projects.task.board', [$project->id]) }}"
                class="btn btn-xs btn-primary btn-icon-only width-auto ">{{ __('Task Board') }}</a>
        </div>
    @endpermission
    @permission('bug manage')
        <div class="col-sm-auto">
            <a href="{{ route('projects.bug.report', [$project->id]) }}"
                class="btn btn-xs btn-primary btn-icon-only width-auto">{{ __('Bug Report') }}</a>
        </div>
    @endpermission
    @permission('project tracker manage')
        @if (module_is_active('TimeTracker'))
            <div class="col-sm-auto">
                <a href="{{ route('projecttime.tracker', [$project->id]) }}"
                    class="btn btn-xs btn-primary btn-icon-only width-auto ">{{ __('Tracker') }}</a>
            </div>
        @endif
    @endpermission
    @permission('project setting')
        @if ($projectStatus)
            <div class="col-sm-auto btn-group">
                <button class="btn btn-xs btn-primary text-white btn-icon-only width-auto dropdown-toggle rounded-pill"
                    type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    @php
                        $selected_project_status = isset($project->status_data->name)
                            ? $project->status_data->name
                            : '';
                    @endphp
                    {{ $selected_project_status }}
                </button>
                <div class="dropdown-menu">
                    @foreach ($projectStatus as $k => $status)
                        @if ($status->id == env('PROJECT_STATUS_CLIENT'))
                            <a href="javascript:void(0)" data-ajax-popup="true" data-toggle="tooltip" data-size="md"
                                data-url="{{ route('projects.edit_form', [$project->id, 'project_status']) }}"
                                data-bs-toggle="modal" data-bs-target="#exampleModal"
                                data-bs-whatever="{{ __('Select Final Estimation') }}"
                                data-title="{{ __('Select Final Estimation') }}" class="dropdown-item"
                                data-bs-toggle="tooltip">{{ $status->name }}</a>
                        @else
                            <a class="dropdown-item status" data-id="{{ $status->id }}"
                                data-url="{{ route('project.status', $project->id) }}" href="#">{{ $status->name }}</a>
                        @endif
                    @endforeach
                </div>
            </div>
        @endif
    @endpermission
@endsection

@section('content')
    @php
        $display_other_tabs = false;

        if (\Auth::user()->hasRole('company') && $project->status == env('PROJECT_STATUS_CLIENT')) {
            $display_other_tabs = true;
        }
    @endphp
    <div class="row">
        <div class="col-sm-12">
            <div class="row">
                <div class="col-xxl-12">
                    <div class="row">
                        <div class="col-xxl-8">
                            @include('project::project.utility.header')
                            @include('project::project.utility.dashboard')
                        </div>

                        <div class="col-xxl-4">
                            @include('project::project.utility.progress_task')
                        </div>
                    </div>
                </div>

                @includeWhen($project->type == 'project', 'project::project.utility.if_project_types')

                @include('project::project.section.milestone')
                @include('project::project.section.files')
                @include('project::project.section.estimations')

                @includeWhen($display_other_tabs, 'project::project.section.project_progress')

                {{-- @include('project::project.utility.activity_log') --}}

            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        var active_estimation_id = '{{ isset($active_estimation->id) ? $active_estimation->id : 0 }}';
        let moneyFormat = '{{ $site_money_format }}';
        var project_id = '{{ \Crypt::encrypt($project->id) }}';

        $(document).ready(function() {

            /** call ajaxComplete after open data-popup **/
            $(document).ajaxComplete(function() {
                tinymce.remove();
                document.querySelectorAll('.tinyMCE').forEach(function(editor) {
                    init_tiny_mce('#' + editor.id);
                });
            });

            var type = '{{ $project->type }}';
            if (type == 'template') {
                $('.pro_type').addClass('d-none');
            } else {
                $('.pro_type').removeClass('d-none');
            }

            init_tiny_mce('.tinyMCE');
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

            $(document).on('change', '#construction-select', function() {
                var selectedOption = $('#construction-select option:selected');
                var selectedType = selectedOption.data('type');
                var clientTypeInput = document.getElementById('client_type1');

                if (selectedType !== undefined && selectedType !== null) {
                    clientTypeInput.value = selectedType;
                } else {
                    clientTypeInput.value = 'new';
                }

                var url = '{{ route('users.get_user') }}';
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
                        initialize_construction();
                        if ($('#construction_detail-company_notes').length > 0) {
                            init_tiny_mce('#construction_detail-company_notes');
                        }




                        // Remove the d-none class if the element is found
                        if (clientDetailsElement) {
                            clientDetailsElement.classList.remove('d-none');
                        }
                    })
                } else {
                    var clientDetailsElement = document.getElementById('construction-details');
                    // Remove the d-none class if the element is found
                    if (clientDetailsElement) {
                        clientDetailsElement.classList.add('d-none');
                    }
                }
            });

            $(document).on('change', '#client-select', function() {
                var selectedOption = $('#client-select option:selected');
                var selectedType = selectedOption.data('type');
                var clientTypeInput = document.getElementById('client_type');

                if (selectedType !== undefined && selectedType !== null) {
                    clientTypeInput.value = selectedType;
                } else {
                    clientTypeInput.value = 'new';
                }
                var url;

                var url = '{{ route('users.get_user') }}';

                // Get the selected values
                if (this.value) {
                    axios.post(url, {
                        'user_id': this.value,
                        'from': 'client'
                    }).then((response) => {
                        var clientDetailsElement = document.getElementById('client-details');

                        $('#client-details').html(response.data.html_data);
                        $('#client_id').val(response.data.user_id);
                        initialize();

                        if ($('#client-company_notes').length > 0) {
                            init_tiny_mce('#client-company_notes');
                        }

                        // Remove the d-none class if the element is found
                        if (clientDetailsElement) {
                            clientDetailsElement.classList.remove('d-none');
                        }
                    })
                } else {
                    var clientDetailsElement = document.getElementById('client-details');
                    // Remove the d-none class if the element is found
                    if (clientDetailsElement) {
                        clientDetailsElement.classList.add('d-none');
                    }
                }
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

            $(document).on("click", ".status", function() {
                var status = $(this).attr('data-id');
                var url = $(this).attr('data-url');

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: {
                        status: status,
                        "_token": $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(data) {
                        location.reload();
                    }
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
        });

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

    <script>
        Dropzone.autoDiscover = false;
        myDropzone = new Dropzone("#dropzonewidget", {
            maxFiles: 20,
            maxFilesize: 20,
            parallelUploads: 1,
            acceptedFiles: ".jpeg,.jpg,.png,.pdf,.doc,.txt",
            url: "{{ route('projects.file.upload', [$project->id]) }}",
            success: function(file, response) {
                if (response.is_success) {
                    dropzoneBtn(file, response);
                    toastrs('{{ __('Success') }}', 'File Successfully Uploaded', 'success');
                } else {
                    myDropzone.removeFile(response.error);
                    toastrs('Error', response.error, 'error');
                }
            },
            error: function(file, response) {
                myDropzone.removeFile(file);
                if (response.error) {
                    toastrs('Error', response.error, 'error');
                } else {
                    toastrs('Error', response, 'error');
                }
            }
        });
        myDropzone.on("sending", function(file, xhr, formData) {
            formData.append("_token", $('meta[name="csrf-token"]').attr('content'));
            formData.append("project_id", {{ $project->id }});
        });

        @if (isset($permisions) && in_array('show uploading', $permisions))
            $(".dz-hidden-input").prop("disabled", true);
            myDropzone.removeEventListeners();
        @endif

        function dropzoneBtn(file, response) {

            var html = document.createElement('div');
            var download = document.createElement('a');
            download.setAttribute('href', response.download);
            download.setAttribute('class', "action-btn btn-primary mx-1  btn btn-sm d-inline-flex align-items-center");
            download.setAttribute('data-toggle', "tooltip");
            download.setAttribute('download', file.name);
            download.setAttribute('title', "{{ __('Download') }}");
            download.innerHTML = "<i class='ti ti-download'> </i>";
            html.appendChild(download);

            @if (isset($permisions) && in_array('show uploading', $permisions))
            @else
                var del = document.createElement('a');
                del.setAttribute('href', response.delete);
                del.setAttribute('class', "action-btn btn-danger mx-1  btn btn-sm d-inline-flex align-items-center");
                del.setAttribute('data-toggle', "popover");
                del.setAttribute('title', "{{ __('Delete') }}");
                del.innerHTML = "<i class='ti ti-trash '></i>";

                del.addEventListener("click", function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    if (confirm("Are you sure ?")) {
                        var btn = $(this);
                        $.ajax({
                            url: btn.attr('href'),
                            type: 'DELETE',
                            success: function(response) {
                                if (response.is_success) {
                                    btn.closest('.dz-image-preview').remove();
                                    toastrs('{{ __('Success') }}', 'File Successfully Deleted',
                                        'success');
                                } else {
                                    toastrs('{{ __('Error') }}', 'Something Wents Wrong.', 'error');
                                }
                            },
                            error: function(response) {
                                response = response.responseJSON;
                                if (response.is_success) {
                                    toastrs('{{ __('Error') }}', 'Something Wents Wrong.', 'error');
                                } else {
                                    toastrs('{{ __('Error') }}', 'Something Wents Wrong.', 'error');
                                }
                            }
                        })
                    }
                });

                html.appendChild(del);
            @endif

            file.previewTemplate.appendChild(html);
        }

        {{-- @php($files = $project->files)
        @foreach ($files as $file)

            @php($storage_file = get_base_file($file->file_path))
            // Create the mock file:
            var mockFile = {
                name: "{{ $file->file_name }}",
                size: "{{ get_size(get_file($file->file_path)) }}"
            };
            // Call the default addedfile event handler
            myDropzone.emit("addedfile", mockFile);
            // And optionally show the thumbnail of the file:
            myDropzone.emit("thumbnail", mockFile, "{{ get_file($file->file_path) }}");
            myDropzone.emit("complete", mockFile);

            dropzoneBtn(mockFile, {
                download: "{{ get_file($file->file_path) }}",
                delete: "{{ route('projects.file.delete', [$project->id, $file->id]) }}"
            });
        @endforeach --}}
    </script>
    <script>
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
                            name: "{{ __($name) }}",
                            // data:
                            data: {!! json_encode($chartData[$id]) !!},
                        },
                    @endforeach
                ],
                xaxis: {
                    categories: {!! json_encode($chartData['label']) !!},
                },
                colors: {!! json_encode($chartData['color']) !!},

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
            var chart = new ApexCharts(document.querySelector("#task-chart"), options);
            chart.render();
        })();

        $('.cp_link').on('click', function() {
            var value = $(this).attr('data-link');
            var $temp = $("<input>");
            $("body").append($temp);
            $temp.val(value).select();
            document.execCommand("copy");
            $temp.remove();
            toastrs('success', '{{ __('Link Copy on Clipboard') }}', 'success')
        });
    </script>
@endpush
