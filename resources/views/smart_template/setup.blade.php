@extends('layouts.main')
@section('page-title')
	{{ ($template_id > 0) ? __('Smart template Edit') : __('Smart template Create') }}
@endsection
@section('page-breadcrumb')
 <a href="{{ route('smart-templates.index') }}">{{ __('Smart Template') }}</a>,{{ ($template_id > 0) ? __('Update') : __('Create') }}
@endsection
@push('css')
	<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

@section('page-action')

@endsection

@section('content')
<div class="row">
	<!-- [ sample-page ] start -->
	<div class="col-sm-12" id='client-details'>
		<div class="row">
			{{ Form::model($template, ['route' => 'smart-template.create', 'method' => 'POST', 'enctype' => 'multipart/form-data', 'id' => 'smart_template_form']) }}
				<div id="useradd-1" class="card">
					<div class="card-body">
						<input type="hidden" name="template_id" value="{{ \Crypt::encrypt($template_id) }}">
						<div class="row">
							<div class="col-md-6 form-group">
								{{ Form::label('title', __('Title'), ['class' => 'form-label']) }}
								{{ Form::text('title', null, ['class' => 'form-control']) }}
								@error('title')
									<span class="invalid-title" role="alert">
										<strong class="text-danger">{{ $message }}</strong>
									</span>
								@enderror
							</div>
							<div class="col-md-6 form-group">
								@php 
									$main_response_check= "";
									$number_check       = "checked";
									$count_section_class="";
									if (isset($template->type) && ($template->type == '0')) {
										$main_response_check= "checked";
										$number_check       = "";
										$count_section_class="d-none";
									}
								@endphp
								<div class="mt-4">
									<div class="form-check-inline">
										<input type="radio" class="form-check-input block_radio_btn" name="type" id="main_response" value="0" {{ $main_response_check }} />
										<label class="form-check-label" for="main_response">{{  __('Main Response') }}</label>
									</div>
									<div class="form-check-inline">
										<input type="radio" class="form-check-input block_radio_btn" name="type" id="number" value="1" {{ $number_check }} />
										<label class="form-check-label" for="number">{{  __('Number') }}</label>
									</div>
								</div>
								@error('avatar')
									<span class="invalid-feedback text-danger text-xs" role="alert">{{ $message }}</span>
								@enderror
							</div>
						</div>
						<div class="row mt-3 count_outliner_section {{ $count_section_class }}">
							<div class="col-md-6 form-group">
								<div class="form-group">
									{{ $request_count_default = ($template_id > 0) ? null : 7 }}
									{{ Form::label('request_count', __('Request Count'), ['class' => 'form-label']) }}
									{{ Form::text('request_count', $request_count_default, ['class' => 'form-control']) }}
								</div>
							</div>
							<div class="col-md-6 form-group">
								<div class="form-group">
									{{ $outliner_count_default = ($template_id > 0) ? null : 90 }}
									{{ Form::label('outliner', __('Outliner'), ['class' => 'form-label']) }}
									{{ Form::text('outliner', $outliner_count_default, ['class' => 'form-control']) }}
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-6">
								<div class="form-group mt-3">
									{{ Form::label('ai_model', __('Model'), ['class' => 'form-label']) }}
									<select name="ai_model" class="form-control">
										<option value="" selected>{{ __('Select model')}}</option>
										@if(isset($ai_models) && count($ai_models) > 0)
											@foreach($ai_models as $ai_model)
												@php
													$selected_model = "";
													if(isset($template->ai_model_id) && $template->ai_model_id == $ai_model->id) {
														$selected_model = "selected";
													}
												@endphp
												<option value="{{ $ai_model->id }}" {{ $selected_model }}>{{ $ai_model->model_label }}</option>
											@endforeach
										@endif
									</select>
								</div>
							</div>
							<div class="col-md-6 count_outliner_section {{ $count_section_class }}">
								<div class="form-group mt-3">
									{{ Form::label('extraction_ai_model_id', __('Extraction Model'), ['class' => 'form-label']) }}
									<select name="extraction_ai_model_id" class="form-control">
										<option value="" selected>{{ __('Select extraction model')}}</option>
										@if(isset($ai_models) && count($ai_models) > 0)
											@foreach($ai_models as $ai_model)
												@php
													$selected_model = "";
													if(isset($template->extraction_ai_model_id) && $template->extraction_ai_model_id == $ai_model->id) {
														$selected_model = "selected";
													}
												@endphp
												<option value="{{ $ai_model->id }}" {{ $selected_model }}>{{ $ai_model->model_label }}</option>
											@endforeach
										@endif
									</select>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-6">
								<div class="form-group mt-3">
									{{ Form::label('result_operation', __('Result'), ['class' => 'form-label']) }}
									{{ Form::text('result_operation', null, ['class' => 'form-control']) }}
									@error('result_operation')
										<span class="invalid-result_operation" role="alert">
											<strong class="text-danger">{{ $message }}</strong>
										</span>
									@enderror
								</div>
								<div class="form-group mt-3">
									{{ Form::label('prompt_ids', __('Smart Prompt'), ['class' => 'form-label']) }}
									<select type="text" name="prompt_ids" class="form-control template_select2" data-placeholder="{{ __('Select Prompt')}}" multiple>

										@foreach ($smart_prompts as $key => $smart_prompt)
											@php
												$selected_prompt = "";
												if(isset($selected_prompts) && in_array($key, $selected_prompts)) {
													$selected_prompt = "selected";
												}
											@endphp
											<option value="{{ $key }}" {{ $selected_prompt }}>
												{{ $smart_prompt }}
											</option>
										@endforeach
									</select>
									@error('prompt_ids')
										<span class="invalid-prompt_ids" role="alert">
											<strong class="text-danger">{{ $message }}</strong>
										</span>
									@enderror
									<input type="hidden" name="" id="prompt_title_slug" value="">
								</div>
							</div>
						</div>
						<div class="row2 prompt_container"></div>

					</div>
				</div>

				<div class="modal-footer">
					{{ Form::submit(__('Save'), ['class' => 'btn btn-primary d-flex align-items-center']) }}
				</div>
			{{ Form::close() }}
		</div>
	</div>
</div>
@endsection
@push('scripts')
	<script src="{{ asset('js/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('Modules/Taskly/Resources/assets/libs/select2/dist/js/select2.min.js')}}"></script>
	<script type="text/javascript">
		var template_id = "{{ $template_id }}";

		$(document).ready(function(){

			$(".template_select2").select2({
				tags: true
			});

			$(document).on("change",".template_select2",function(e){
				e.preventDefault();

				get_prompt_data();
			});

			$(document).on("change keyup",".smart_block_name",function(e){
				var title = $(this).val();
				if (title != ''){
					var new_slug = generate_slug($(this).val());
					$(this).parents('.prompt_block').find('.smart_block_slug').val(new_slug);
					$(this).parents('.prompt_block').find('.smart_block_slug_label').text(new_slug);
				}
				// save_prompt_data();
			});

			$(document).on("change",".block_radio_btn",function(e){
				var block_value = $(this).val();

				if ($('#main_response').is(':checked')) {
					$('.count_outliner_section').addClass('d-none');
				} else {
					$('.count_outliner_section').removeClass('d-none');
				}

				
			});

			setTimeout(function(){
				get_prompt_data();
			}, 500);


			if ($("#smart_template_form").length > 0) {
				$.validator.addMethod("select2Required", function(value, element, arg) {
					return $(element).val().length > 0;
				}, "Please select at least one smart prompt");

				$.validator.addMethod('numberType', function (value, element, param) {
					var template_type = $('#smart_template_form .block_radio_btn').val();
					var valid = true;
					if (template_type == 1 && value == "") {
						valid = false;
					}
					return valid;
				}, "Please enter request_count");

				$("#smart_template_form").validate({
					ignore: [],
					rules: {
						"title": {
							required: true
						},
						"ai_model": {
							required: true
						},
						"request_count": {
							numberType: true
						},
						"outliner": {
							numberType: true
						},
						"extraction_ai_model_id": {
							numberType: true
						},
						// "prompt_ids[]": {
						// 	required: true
						// },
					},
					messages: {
						"title": {
							required: "Please Enter Title"
						},
						"ai_model": {
							required: "Please Select AI model"
						},
						"request_count": {
							numberType: "Please Enter Request Count"
						},
						"outliner": {
							numberType: "Please Enter Outliner"
						},
						"extraction_ai_model_id": {
							numberType: "Please Select Extraction Model"
						},
						// "prompt_ids[]": {
						// 	required: "Please select at least one smart prompt"
						// },
					},
					errorPlacement: function(error, element) {
						if (element.hasClass("select2-hidden-accessible")) {
							element.next('.select2-container').append(error);
						} else {
							error.insertAfter(element);
						}
					}
				});
				$("#smart_template_form").on('submit', function () {
					if ($(this).valid()) {
						return true;
					} else {
						return false;
					}
				});
			}
		});

		function get_prompt_data() {
			if ($('.template_select2').val() != null) {
				save_prompt_data();
				$('.prompt_container').html('');
				var token = $('meta[name="csrf-token"]').attr('content');
				var form_data = JSON.stringify({prompts:$('.template_select2').val(),prompts_data:$("#prompt_title_slug").val(), template_id : template_id, _token: token});
				$.ajax({
					url: "{{route('smart-template.get-smart-block')}}",
					type: "POST",
					contentType: "application/json",
					data: form_data,
					success: function(response) {
						$('.prompt_container').html(response.html);
					},
					error: function(error) {
						// Handle any errors that occur during the Ajax request
						console.error("Error sending data to the server:", error);
					}
				});
			}
		}

		function save_prompt_data() {
			var propmt_data = [];
			$('.prompt_block').each(function () {
				prompt_details = {};
				prompt_details['prompt_id'] = $(this).find('.smart_block_id').val();
				prompt_details['title'] = $(this).find('.smart_block_name').val();
				prompt_details['slug'] = $(this).find('.smart_block_slug').val();
				prompt_details['description'] = $(this).find('.smart_block_description').val();
				propmt_data.push(prompt_details);
			});
			$('#prompt_title_slug').val(JSON.stringify(propmt_data));
		}
    </script>
@endpush