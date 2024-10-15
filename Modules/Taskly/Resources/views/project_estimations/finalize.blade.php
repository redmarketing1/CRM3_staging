@extends('layouts.main')
@php
    $profile=asset(Storage::url('uploads/avatar'));
@endphp
@section('page-title')
	{{__('Estimation')}}
@endsection
@section('title')
	{{__('Final Proposal')}}
@endsection
@push('css')
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
	<link href="{{  asset('assets/js/plugins/summernote-0.8.18-dist/summernote-bs4.css')  }}" rel="stylesheet">
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
        .project_file_container {
            position: relative;
            margin: 5px 0px;
        }
        .project_images_files_checkbox {
            position: absolute;
            right: 5px;
            top: 5px;
        }
    </style>
@endpush
@section('page-breadcrumb')
<a href="{{route('projects.index')}}">{{ __('All Project') }}</a>, <a href="{{route('projects.show', [$estimation->project_id])}}">{{$estimation->getProjectDetail->name }}</a>, <a href="{{route("estimations.finalize.estimate",encrypt($estimation->id))}}">{{ $estimation->title }}</a>, {{__('Final Proposal')}}
@endsection

@section('page-action')

@endsection
@push('css')
	
@endpush
@push('scripts')
	<script src="{{ asset('assets/js/plugins/summernote-0.8.18-dist/summernote-bs4.js') }}"></script>
	<script src="{{ asset('Modules/Taskly/Resources/assets/js/jquery-ui.min.js')}}"></script>
    <script src="{{ asset('js/jquery.repeater.min.js') }}"></script>
	<script src="{{ asset('Modules/Taskly/Resources/assets/js/tinymce/tinymce.min.js') }}"></script>
	<script src="{{ asset('Modules/Taskly/Resources/assets/js/custom.js')}}"></script>
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
	<div class="row">
		<div class="col-md-12">
			<div class="row">
				<div class="col-md-12 order-lg-2">
					<div class="card repeater final-wrapper">
						<div class="final-left-col card-body">
							<div class="row final-pdf-paper d-flex justify-content-center">
								<div class="col-md-12">
									<?php 
										$client = $estimation->project()->client_data;  
										$client_name = isset($client->first_name) ? $client->first_name .' '. $client->last_name : '';
										$client_email = isset($client->email) ? $client->email : '';
										$contractor = $quote->subContractor;
										$data = ['estimation' => $estimation,
											'settings' => $settings,
											'quote' => $quote,
											'client' => $client,
											'client_name' => $client_name,
											'client_email' => $client_email,
											'contractor' => $contractor,
										];
									?>
									@include('taskly::project_estimations.pdf.estimate-pdf-preview')
								</div>
							</div>
						</div>
						<div class="final-right-col card-body bg-gray-200 px-3">
							<div class="row">
								<div class="col-12">
									<div class="col-12">
										<div class="final-send-wrapper">
											<div class="mt-3">
												<form action="{{route('estimations.quote.send.client')}}" id="print" method="post">
													<input type="hidden" name="id" value="{{$estimation->id}}">
													<input type="hidden" name="type" value="pdf">
													<input type="hidden" name="subject" value="" id="print-subject">
													<input type="hidden" name="client_email" id="print-client_email" value="">
													<input type="hidden" name="email_text" id="print-email_text" value="">
													<input type="hidden" name="extra_notes" id="print-extra_notes" value="">
													<input type="hidden" name="pdf_top_notes" id="print-pdf-top-notes" value="">
													<input type="hidden" name="project_images_files" id="print-pdf-project-images-files" value="">
													<input type="hidden" name="project_other_files" id="print-pdf-project-other-files" value="">
													@csrf
												</form>
												<div class="btn-group float-end">
													<button class="btn bg-primary text-white btn-icon rounded-pill dropdown-toggle final-send-btn" type="button"
														data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
														{{__('Send to Client')}}
													</button>
													<div class="dropdown-menu">
														<a href="javascript:void(0)" onclick="send('{{$estimation->id}}', 'email')" class="dropdown-item final-email-btn"><i class="fa-regular fa-paper-plane"></i> {{__('Send to Client')}}</a>
														<a href="javascript:void(0)" onclick="send('{{$estimation->id}}', 'save')" class="dropdown-item final-save-btn"><i class="fa-regular fa-floppy-disk"></i> {{__('Finish and save')}}</a>
														<a href="javascript:void(0)" onclick="send('{{$estimation->id}}', 'pdf')" class="dropdown-item final-download-btn"><i class="fa-regular fa-circle-down"></i> {{__('Finish and download')}}</a>
													</div>
												</div>
											</div>
										</div>

										<div class="final-email-header">
											@if(isset($estimation->project()->client_data->first_name))
												<label for="">{{ (isset($estimation->project()->client_data->salutation) ? __($estimation->project()->client_data->salutation) : '') .' '. $estimation->project()->client_data->first_name .' '. $estimation->project()->client_data->last_name }}</label>
											@endif
											<div class="col-12 pt-1">
												<label for="" class="form-label">{{__('Client Email')}}</label>
												<input type="email" name="client_email" class="form-control" id="client_email" value="{{ isset($estimation->project()->construction_detail->email) ? $estimation->project()->construction_detail->email : '' }}">
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
															
											<label class="company-cc"><input type="checkbox" class="form-check-input" name="copy_to_company" value="{{ isset($company_details->email) ? $company_details->email : '' }}" checked/> {{ __('Send copy to') }} {{ isset($company_details->email) ? $company_details->email : '' }}</label>
											@if (isset($quote->subContractor->email))
												<label class="company-cc"><input type="checkbox" class="form-check-input" name="copy_to_subcontractor" value="{{$quote->subContractor->email}}"/> {{ __('Send copy to Subcontractor') }} {{$quote->subContractor->email}}</label>
											@endif
										</div>

										<div class="row mt-3">
											<div class="col-10">            
												<label for="" class="form-label">{{__('Subject')}}</label>
												<input type="text" name="subject" class="form-control" id="subject" value="{estimation.title} - {{ __('CP') }} {construction.street} - {client_name} - #1{{$estimation->id}} - {{ isset($company_details->name) ? $company_details->name : '' }}">
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
											<div class="final-attachments">
												<div class="col-12 pt-1 mt-2">
													@if(!empty($additional_files_list))
														<h6><i class="fa-solid fa-paperclip"></i> {{ __('Attach Additional files to Email') }}</h6>
															<ul>
														@foreach($additional_files_list as $row)
															<li>
																<input type="checkbox" class="form-check-input additional_files_list" name="additional_files_list[]" value="{{$row->getRelativePathname()}}" />
																<a href="{{ asset('public/additional_files/' . $row->getRelativePathname()) }}" target="_blank">
																	{{$row->getRelativePathname()}}
																</a>
															</li>
														@endforeach
														</ul>
													@endif
												</div>
												<div class="col-12 pt-1">
														<h6><i class="fa-solid fa-paperclip"></i> {{ __('Attach Additional Formats to Email') }}</h6>
															<ul>
																<li><label><input type="checkbox" class="form-check-input additional_format_files_list" name="additional_format_files_list[]" value="gaeb" /> <a href="{{ route('estimation.export.gaeb',['id' =>\Crypt::encrypt($estimation->id), 'type' => 'attachment-download']) }}" target="_blank">{{ __('Attach GAEB Format') }}</a></label></li>
																<li><label><input type="checkbox" class="form-check-input additional_format_files_list" name="additional_format_files_list[]" value="excel" /> <a href="{{ route('estimation.export.excel',['id' =>\Crypt::encrypt($estimation->id), 'type' => 'attachment-download']) }}" target="_blank">{{ __('Attach EXCEL Format') }}</a></label></li>
																<li><label><input type="checkbox" class="form-check-input additional_format_files_list" name="additional_format_files_list[]" value="csv" /> <a href="{{ route('estimation.export.csv',['id' =>\Crypt::encrypt($estimation->id), 'type' => 'attachment-download']) }}" target="_blank">{{ __('Attach CSV Format') }}</a></label></li>
																<li><label><input type="checkbox" class="form-check-input additional_format_files_list" name="additional_format_files_list[]" value="image_zip" /> <a href="#" class="download_images_zip" target="_blank">{{ __('Attach Images as ZIP-File') }}</a></label></li>
																<form action="{{route('estimation.create.imageszip')}}" id="download_images_zip" method="post" target="_blank">
																	<input type="hidden" name="id" value="{{$estimation->id}}">
																	<input type="hidden" name="type" value="attachment-download">
																	<input type="hidden" name="project_images" id="zip_project_images_files" value="">
																	@csrf
																</form>
															</ul>
												</div>
												<div class="col-12 pt-1">
													@if(!empty($project_other_files))
														<h6><i class="fa-solid fa-paperclip"></i> {{ __('Attach Project Files to Email') }}</h6>
														<ul>
															@foreach($project_other_files as $row)
																<li>
																	<label><input type="checkbox" class="form-check-input additional_files_list" name="project_other_files_list[]" value="{{ encrypt($row->id) }}" /><a href="{{ get_file('uploads/files/') . rawurlencode($row->file) }}" target="_blank"> {{$row->file}} </a></label>
																</li>
															@endforeach
														</ul>
													@endif
												</div>
											
												<div class="col-12 pt-1 project-attachments mt-2">
													@if(!empty($project_images_files))
														<h6><i class="fa-regular fa-square-plus"></i> {{ __('Add Project Images to PDF') }}</h6>
														<div class="row img-row">
															@foreach($project_images_files as $prow)
																<div class="col-md-5">
																	<div class="project_file_container">
																		<label>
																			<img src="{{get_file($prow->file_path) }}" class="img-thumbnail"/>
																			<input type="checkbox" class="form-check-input project_images_files_checkbox" name="project_images_files_list[]" value="{{ encrypt($prow->id) }}" checked />
																		</label>
																	</div>
																</div>
															@endforeach
														</div>
													@endif
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
	</div>
@endsection
@push("scripts")
<script>
	const ccArray = [];
	const bccArray = [];
	var project_id = '{{\Crypt::encrypt($estimation->project_id)}}';

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

	function send(id,type="") {

		tinymce.triggerSave();

		var additional_files_list = new Array();
		$("input[name='additional_files_list[]']:checked").each(function(i) {
			additional_files_list.push($(this).val());
		});
		var project_images_files_list = new Array();
		$("input[name='project_images_files_list[]']:checked").each(function(i) {
			project_images_files_list.push($(this).val());
		});
		var additional_format_files_list = new Array();
		$("input[name='additional_format_files_list[]']:checked").each(function(i) {
			additional_format_files_list.push($(this).val());
		});
		$("#print-pdf-project-images-files").val(project_images_files_list);
		var project_other_files_list = new Array();
		$("input[name='project_other_files_list[]']:checked").each(function(i) {
			project_other_files_list.push($(this).val());
		});
		$("#print-pdf-project-other-files").val(project_other_files_list);
		let data = {
			id:id,
			email_text : $("#email-text").summernote('code'),
			extra_notes : $("#extra_notes").val(),
			pdf_top_notes : $("#pdf_top_notes").val(),
			subject : $("#subject").val(),
			client_email : $("#client_email").val(),
			copy_to_company : $("input[name='copy_to_company']").is(':checked'),
			copy_to_subcontractor : $("input[name='copy_to_subcontractor']").is(':checked'),
			cc_email: ccArray,
			bcc_email: bccArray,
			type : type,
			additional_files: additional_files_list,
			project_images_files: $("#print-pdf-project-images-files").val(),
			project_other_files: $("#print-pdf-project-other-files").val(),
			additional_format_files: additional_format_files_list
		}
		if(data.client_email == "" && type == 'email'){
			toastrs('Error', "Please enter client email");
			$("#client_email").focus();
			return false;
		}
		if(type == "pdf"){
			$("#print-email_text").val(data.email_text);
			$("#print-subject").val(data.subject);
			$("#print-client_email").val(data.client_email);
			$("#print-extra_notes").val(data.extra_notes);
			$("#print-pdf-top-notes").val(data.pdf_top_notes);
			$("#print-pdf-project-images-files").val(data.project_images_files);
			$("#print-pdf-project-other-files").val(data.project_other_files);
			$("#print").submit();
			return false;
		}
		// return;
		$.ajax({
			url: "{{route('estimations.quote.send.client')}}",
			type: "POST",
			contentType: "application/json",
			data: JSON.stringify(data),
			beforeSend:function () {
				showHideLoader('visible');
			},
			success: function(response) {
				showHideLoader('hidden');
				if(response.status == true){
					toastrs('Success', response.message, 'success')
				} else {
					toastrs('Error', response.message)
				}
			},
			error: function(error) {
				// Handle any errors that occur during the Ajax request
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
		
		$("#email-text").summernote("code",`{!! $estimateEmailTemplate !!}`);
		$(".download_images_zip").on("click", function() {
			var project_images_list = new Array();
			$("input[name='project_images_files_list[]']:checked").each(function(i) {
				project_images_list.push($(this).val());
			});
			if (project_images_list.length != 0 && project_images_list != null) {
				$("#zip_project_images_files").val(project_images_list);
				$("#download_images_zip").submit();
				return false;
			} else {
				toastrs('Error', "{{ __('Please select the images') }}");
				return false;
			}
		});
		init_tiny_mce('#extra_notes');

		init_tiny_mce('#pdf_top_notes');
	});

</script>
@endpush
