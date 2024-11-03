@extends('layouts.main')

@section('page-title')
	{{__('Invoice Finalize')}}
@endsection

@section('title')
	{{__('Invoice Finalize')}}
@endsection

@push('css')
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
	<link href="{{  asset('assets/js/plugins/summernote-0.8.18-dist/summernote-bs4.css')  }}" rel="stylesheet">
	<link rel="stylesheet" href="{{ asset('Modules/Taskly/Resources/assets/css/custom.css') }}" type="text/css" />
	<link rel="stylesheet" href="{{ asset('css/common.css') }}" type="text/css" />
@endpush
@section('page-breadcrumb')
<a href="{{route('projects.index')}}">{{ __('All Project') }}</a>,<a href="{{route('projects.show', [$project->id])}}">{{ $project->name }}</a>,{{__('Invoice Finalize')}}
@endsection
@section('page-action')

@endsection
@push('css')
	<style>
		.tags {
			display: inline-block;
			background-color: #3498db;
			color: white;
			padding: 5px 10px;
			margin: 5px;
			border-radius: 5px;
			cursor: pointer;
		}
		.estimation-preview-table{
			margin: 0 !important;
		}
		.comment_column{
			width: 20% !important;
		}
	</style>
@endpush
@push('scripts')
	<script src="{{ asset('Modules/Taskly/Resources/assets/js/jquery-ui.min.js')}}"></script>
    <script src="{{ asset('js/jquery.repeater.min.js') }}"></script>
	<script src="{{ asset('assets/js/plugins/summernote-0.8.18-dist/summernote-bs4.js') }}"></script>
	<script src="{{ asset('Modules/Taskly/Resources/assets/js/tinymce/tinymce.min.js') }}"></script>
	<script>
		function copyToClipboard(element) {
            var $temp = $("<input>");
            $("body").append($temp);
            $temp.val($(element).text()).select();
            document.execCommand("copy");
            $temp.remove();

            toastrs('{{ __('Success') }}', '{{ __('Copied to Clipboard!') }}', '{{ __('Success') }}')
        }
	</script>
@endpush
@section('content')
<div class="row progressFinalize">
    <div class="col-md-12">
        <div class="row">
            <div class="col-md-12 order-lg-2" style="overflow-x: scroll;">
                <div class="card repeater final-wrapper">
                    <div class="final-left-col card-body">
                        <div class="row final-pdf-paper d-flex justify-content-center" style="padding: 50px 0px;">
                            <div class="col-md-12">
                                <?php
                                $client = $project->client_data;
                                $client_name = isset($client->first_name) ? $client->first_name . ' ' . $client->last_name : '';
                                $client_email = isset($client->email) ? $client->email : '';
                                $contractor = '';
                                $data = [
                                    'settings' => $settings,
                                    'client' => $client,
                                    'client_name' => $client_name,
                                    'client_email' => $client_email,
                                ];
                                ?>
                                <!--- using render -->
                                {!! $html !!}
                                <!--- without render use -->
                                {{-- <!-- @include('invoice.templates.template11') --> --}}
                            </div>
                        </div>
                    </div>
                    <div class="final-right-col card-body bg-gray-200 px-3" style="min-width: 350px!important;padding:20px!important">
                        <div class="row">
                            <div class="col-12">
                                <div class="col-12">
                                    <div class="final-send-wrapper">
                                        <div class="mt-3">
                                            <form action="{{route('progress.invoice.send.client')}}" id="print" method="post">
                                                <input type="hidden" name="id" value="{{$invoice->id}}">
                                                <input type="hidden" name="type" value="pdf">
                                                <input type="hidden" name="subject" value="" id="print-subject">
                                                <input type="hidden" name="client_email" id="print-client_email" value="">
                                                <input type="hidden" name="email_text" id="print-email_text" value="">
                                                <input type="hidden" name="extra_notes" id="print-extra_notes" value="">
                                                <input type="hidden" name="pdf_top_notes" id="print-pdf-top-notes" value="">
                                                @csrf
                                            </form>
                                            <div class="btn-group float-end">
                                                <button class="btn bg-primary text-white btn-icon rounded-pill dropdown-toggle final-send-btn" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    {{__('Send to Client')}}
                                                </button>
                                                <div class="dropdown-menu">
                                                    <a href="javascript:void(0)" onclick="send_progress('{{$invoice->id}}', 'email')" class="dropdown-item">{{__('Send to Client')}}</a>
                                                    <a href="javascript:void(0)" onclick="send_progress('{{$invoice->id}}', 'pdf')" class="dropdown-item">{{__('Download')}}</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="final-email-header">
                                        @if(isset($project->client_data->first_name))
                                        <label for="">{{ $project->client_data->first_name .' '. $project->client_data->last_name }}</label>
                                        @endif
                                        <div class="col-12 pt-1">
                                            <label for="" class="form-label">{{__('Client Email')}}</label>
                                            <input type="email" name="client_email" class="form-control" id="client_email" value="{{ isset($project->construction_detail->email) ? $project->construction_detail->email : '' }}">
                                        </div>
                                        <div class="col-12 pt-1 tag_box">
                                            <label for="" class="form-label mt-1">{{__('Other CC-E-Mails')}} ({{__('Separate emails with Comma')}})</label>
                                            <input type="text" name="cc_email" class="form-control tagInput" placeholder="Enter Email" value="" onkeydown="handleKeyPress(event)">
                                            <div class="tagContainer"></div>
                                            <div class="tagList" class="d-none"></div>
                                        </div>
                                        <div class="col-12 pt-1 tag_box">
                                            <label for="" class="form-label mt-1">{{__('Other BCC-E-Mails')}} ({{__('Separate emails with Comma')}})</label>
                                            <input type="text" name="bcc_email" class="form-control tagInput" placeholder="Enter Email" value="" onkeydown="handleKeyPress(event)">
                                            <div class="tagContainer"></div>
                                            <div class="tagList" class="d-none"></div>
                                        </div>
                                        <label class="company-cc"><input type="checkbox" class="form-check-input" name="copy_to_company" value="{{ $settings['company_email'] }}" checked /> {{ __('Send copy to') }} {{ $settings['company_email'] }}</label>
                                    </div>

                                    <div class="row mt-3">
                                        <div class="col-10">
                                            <label for="" class="form-label">{{__('Subject')}}</label>
                                            <input type="text" name="subject" class="form-control" id="subject" value="{{ __('Invoice') }}-{{ $invoice->invoice_id }}-{{ $client_name }}-{estimation.title}-{{ $invoice->progress_id }}-{{ $settings['company_name'] }}">
                                        </div>
                                        <div class="col-2" style="align-content:center;">
                                            <span class="col-12 lright variable-box">
                                                <i class="fa-solid fa-list"></i>
                                                <div class="variable-items">
                                                    <h5>{{ __('Click to copy variables') }}</h5>
                                                    <div class="section">
                                                        <a href="javascript:void(0)" id="con20" onclick="copyToClipboard('#con20')" class="list-group-item list-group-item-action border-0">{estimation.title}</a>
                                                        <a href="javascript:void(0)" id="con1" onclick="copyToClipboard('#con1')" class="list-group-item list-group-item-action border-0">{client_name}</a>
                                                        <a href="javascript:void(0)" id="con2" onclick="copyToClipboard('#con2')" class="list-group-item list-group-item-action border-0">{client.company_name}</a>
                                                        <a href="javascript:void(0)" id="con3" onclick="copyToClipboard('#con3')" class="list-group-item list-group-item-action border-0">{client.salutation_title}</a>
                                                        <a href="javascript:void(0)" id="con4" onclick="copyToClipboard('#con4')" class="list-group-item list-group-item-action border-0">{client.academic_title}</a>
                                                        <a href="javascript:void(0)" id="con5" onclick="copyToClipboard('#con5')" class="list-group-item list-group-item-action border-0">{client.first_name}</a>
                                                        <a href="javascript:void(0)" id="con6" onclick="copyToClipboard('#con6')" class="list-group-item list-group-item-action border-0">{client.last_name}</a>
                                                        <a href="javascript:void(0)" id="con7" onclick="copyToClipboard('#con7')" class="list-group-item list-group-item-action border-0">{client.email}</a>
                                                        <a href="javascript:void(0)" id="con8" onclick="copyToClipboard('#con8')" class="list-group-item list-group-item-action border-0">{client.phone}</a>
                                                        <a href="javascript:void(0)" id="con9" onclick="copyToClipboard('#con9')" class="list-group-item list-group-item-action border-0">{client.mobile}</a>
                                                        <a href="javascript:void(0)" id="con21" onclick="copyToClipboard('#con21')" class="list-group-item list-group-item-action border-0">{client.salutation}</a>
                                                        <a href="javascript:void(0)" id="con22" onclick="copyToClipboard('#con22')" class="list-group-item list-group-item-action border-0">{construction.salutation}</a>
                                                    </div>
                                                    <div class="section">
                                                        <a href="javascript:void(0)" id="con10" onclick="copyToClipboard('#con10')" class="list-group-item list-group-item-action border-0">{client.website}</a>
                                                        <a href="javascript:void(0)" id="con11" onclick="copyToClipboard('#con11')" class="list-group-item list-group-item-action border-0">{construction.street}</a>
                                                        <a href="javascript:void(0)" id="con12" onclick="copyToClipboard('#con12')" class="list-group-item list-group-item-action border-0">{construction.additional_address}</a>
                                                        <a href="javascript:void(0)" id="con13" onclick="copyToClipboard('#con13')" class="list-group-item list-group-item-action border-0">{construction.zipcode}</a>
                                                        <a href="javascript:void(0)" id="con14" onclick="copyToClipboard('#con14')" class="list-group-item list-group-item-action border-0">{construction.city}</a>
                                                        <a href="javascript:void(0)" id="con15" onclick="copyToClipboard('#con15')" class="list-group-item list-group-item-action border-0">{construction.state}</a>
                                                        <a href="javascript:void(0)" id="con16" onclick="copyToClipboard('#con16')" class="list-group-item list-group-item-action border-0">{construction.country}</a>
                                                        <a href="javascript:void(0)" id="con17" onclick="copyToClipboard('#con17')" class="list-group-item list-group-item-action border-0">{construction.tax_number}</a>
                                                        <a href="javascript:void(0)" id="con18" onclick="copyToClipboard('#con18')" class="list-group-item list-group-item-action border-0">{construction.notes}</a>
                                                        <a href="javascript:void(0)" id="con19" onclick="copyToClipboard('#con19')" class="list-group-item list-group-item-action border-0">{current.date+21days}</a>
                                                    </div>
                                                </div>
                                            </span>
                                        </div>
                                        <div class="col-12 pt-1">
                                            <label for="" class="form-label mt-1">{{__('E-Mail-Text')}}</label>
                                            <div id="email-text"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push("scripts")
<script type="application/javascript">
    const ccArray = [];
    const bccArray = [];
    function addTag() {
        $('.tag_box').each(function() {
            // const inputElement = document.getElementById("tagInput");
            // const tagContainer = document.getElementById("tagContainer");
            // const tagList = document.getElementById("tagList");
            const inputElement = $(this).find(".tagInput");
            const tagContainer = $(this).find(".tagContainer");
            const tagList = $(this).find(".tagList");
            // const tagText = inputElement.value.trim();
            const tagText = inputElement.val();
            if (tagText) {
                const tagElement = document.createElement("div");
                tagElement.textContent = tagText;
                tagElement.className = "tags";
                tagElement.addEventListener("click", function() {
                    tagElement.remove();
                    // tagContainer.removeChild(tagElement);
                    removeTagFromList(inputElement, tagText);
                });
                tagContainer.append(tagElement);
                if (inputElement.attr('name') == 'bcc_email') {
                    bccArray.push(tagText);
                } else {
                    ccArray.push(tagText);
                }
                inputElement.val("");
                updateTagList($(this));
            }
        });
    }

    function handleKeyPress(event) {
        if (event.key === "Enter" || event.key === "," || event.key === " ") {
            event.preventDefault();
            addTag();
        }
    }

    function removeTagFromList(inputElement, tagText) {
        if (inputElement.attr('name') == 'bcc_email') {
            const index = bccArray.indexOf(tagText);
            if (index !== -1) {
                bccArray.splice(index, 1);
            }
        } else {
            const index = ccArray.indexOf(tagText);
            if (index !== -1) {
                ccArray.splice(index, 1);
            }
        }
        // if (index !== -1) {
        //     tagsArray.splice(index, 1);
        // }
        updateTagList(inputElement.parents(".tag_box"));
    }

    function updateTagList(selector) {
        // const tagList = document.getElementById("tagList");
        const tagList = selector.find(".tagList");
        if (tagList.length > 0) {
            if (selector.find('input[name="bcc_email"]').length > 0) {
                tagList.textContent = "Tags: " + bccArray.join(", ");
            } else {
                tagList.textContent = "Tags: " + ccArray.join(", ");
            }
        }
    }

    function send_progress(id, type = "") {
        // tinymce.triggerSave();
        let data = {
            id: id,
            email_text: '',
            email_text: $("#email-text").summernote('code'),
          //  extra_notes: $("#extra_notes").val(),
          // pdf_top_notes: $("#pdf_top_notes").val(),
            subject: $("#subject").val(),
            client_email: $("#client_email").val(),
            copy_to_company: $("input[name='copy_to_company']").is(':checked'),
        //    copy_to_subcontractor: $("input[name='copy_to_subcontractor']").is(':checked'),
            cc_email: ccArray,
            bcc_email: bccArray,
            type: type,
        }
        if (data.client_email == "" && type == 'email') {
            toastrs('Error', "Please enter client email");
            $("#client_email").focus();
            return false;
        }
        if (type == "pdf") {
            $("#print-email_text").val(data.email_text);
            $("#print-subject").val(data.subject);
            $("#print-client_email").val(data.client_email);
            $("#print-extra_notes").val(data.extra_notes);
            $("#print-pdf-top-notes").val(data.pdf_top_notes);
            $("#print").submit();
            return false;
        }
        $.ajax({
            url: "{{route('progress.invoice.send.client')}}",
            type: "POST",
            contentType: "application/json",
            data: JSON.stringify(data),
            beforeSend: function() {
                showHideLoader('visible');
            },
            success: function(response) {
                showHideLoader('hidden');
                if (response.status == true) {
                    toastrs('Success', response.message, 'success')
                } else {
                    toastrs('Error', response.message)
                }
            },
            error: function(error) {
                console.error("Error sending data to the server:", error);
            }
        });
    }

    $(document).ready(function() {
        $('#email-text').summernote({
            height: 300, // Set the height of the editor
            toolbar: [
                ['style', ['bold', 'italic', 'underline', 'clear']],
                ['font', ['strikethrough', 'superscript', 'subscript']],
                ['fontsize', ['fontsize']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['height', ['height']],
                ['table', ['table']],
                ['insert', ['link', 'picture', 'video']],
                ['view', ['fullscreen', 'codeview']],
            ],
            fontNames: ['Arial', 'Arial Black', 'Comic Sans MS', 'Courier New'],
            fontNamesIgnoreCheck: ['FontAwesome'],
            callbacks: {
                // Additional callbacks can be added here if needed
            }
        });
        $(".view-button").on("click", function() {
            $(this).siblings(".child-row").toggle();
        });

        $("#email-text").summernote("code", `{!! $progressInvoiceFinalizeEmailTemplate !!}`);
    });

    init_tiny_mce('#extra_notes');
    init_tiny_mce('#pdf_top_notes');
</script>
@endpush
