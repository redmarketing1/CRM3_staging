@extends('layouts.main')


@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="row">
                @include('project::project.show.utility.header')
                @include('project::project.show.utility.dashboard')

                @includeWhen($project->type == 'project', 'project::project.show.utility.if_project_types')

                @include('project::project.show.utility.activity_log')

                @include('project::project.show.section.files')
                @include('project::project.show.section.estimations')

                @include('project::project.show.section.project_progress')
                {{-- @include('project::project.show.section.project_delay') --}}

                @include('project::project.show.section.milestone')
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        var active_estimation_id = '{{ isset($active_estimation->id) ? $active_estimation->id : 0 }}';
        let moneyFormat = '{{ site_money_format() }}';
        var project_id = '{{ \Crypt::encrypt($project->id) }}';

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
    <script>
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
    </script>
@endpush
