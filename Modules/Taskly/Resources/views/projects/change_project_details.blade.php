<style>
	.selct2-custom span.select2.select2-container.select2-container--default {
		width: 100% !important;
	}
	.selct2-custom .select2-container--default .select2-selection--single .select2-selection__clear {
    	margin-top: 0px !important;
	}

	.selct2-custom .select2-container .select2-selection--single {
		height: 38px !important;
	}

	.selct2-custom .select2-container--default .select2-selection--single .select2-selection__rendered {
		line-height: 35px !important;
	}
	.selct2-custom .select2-container--default .select2-selection--single .select2-selection__placeholder {
		color: black !important;
	}

	.selct2-custom .select2-container--default .select2-selection--single .select2-selection__arrow {
		top: 8px !important;
	}

    .pac-container {
        z-index: 10000 !important;
    }

</style>

{{Form::open(array('route' => array('project.update_details', [$project_id, $form_field]),'method'=>'post','id'=>'title_form','class'=>'project_detail_form'))}}
<div class="card-body p-0">
	<div class="row">
		@if($form_field == "title")
			<div class="col-md-12">
				<div class="form-group">
					<input type="text" class="form-control" placeholder="{{ __('Project Title') }}" name="title" value="{{ isset($project->title) ? $project->title : ''}}">
				</div>
			</div>
		@elseif($form_field == "description")
			<div class="col-md-12">
				<div class="form-group">
					<label for="description" class="form-label">{{ __('Project Description') }}</label>
					<textarea class="form-control" rows="2" name="description" id="project-description" spellcheck="false">{{ isset($project->description) ? $project->description : ''}}</textarea>
				</div>
			</div>
		@elseif($form_field == "client")
			<div class="col-md-12">
				<div class="client-details">
					<div class="form-group col-md-12 selct2-custom">
						{{ Form::label('client', __('Client'), ['class' => 'form-label']) }}
						<select name="client" id="client-select" class="form-control">
							<option value="" data-type="">{{ __('Select') }}
							</option>
							@foreach ($clients as $client)
							<option value="{{$client['id']}}" data-type="{{ $client['type'] }}"> {{$client['name']}}</option>
							@endforeach
						</select>
						
					</div>

					<input type="hidden" id='client_id' name="client_id">
					<input type="hidden" id='client_type' name="client_type">
					<div class="contact-details-columns row ">
						<!-- Client Details Column -->
						<div class="col-md-12 row d-none" id="client-details">
							<h3>{{ __('Client Details') }}</h3>
							<div class="row">

								<h6 class="my-5">
									{{ __('Personal info') }}
								</h6>

								<div class="form-group col-md-6">
									<label for="client-salutation" class="form-label">{{ __('Salutation') }}</label>
									<div class="d-flex radio-check">
										<div class="form-check m-1">
											<input type="radio" value="Mr." id="client-salutation_mr" name="salutation" class="form-check-input">
											<label class="form-check-label" for="client-salutation_mr">{{__('Mr.')}}</label>
										</div>
										<div class="form-check m-1">
											<input type="radio" value="Ms." id="client-salutation_ms" name="salutation" class="form-check-input" >
											<label class="form-check-label" for="client-salutation_ms">{{ __('Ms.') }}</label>
										</div>
									</div>
								</div>
								<div class="form-group col-md-6">
									<label for="client-title" class="form-label">{{ __('Title') }}</label>
									<select class="form-control" id="client-title" name="title">
										<option selected="selected" value="">{{ __('Select') }}</option>
										<option value="Dr.">{{ __('Dr.') }}</option>
										<option value="Prof.">{{ __('Prof.') }}</option>
										<option value="Prof. Dr.">{{ __('Prof. Dr.') }}</option>
										<option value="B.Sc.">{{ __('B.Sc.') }}</option>
										<option value="M.Sc.">{{ __('M.Sc.') }}</option>
										<option value="Dipl.-Ing.">{{ __('Dipl.-Ing.') }}</option>
										<option value="Mag.">{{ __('Mag.') }}</option>
									</select>
								</div>
								<div class="form-group col-md-6">
									<label for="client_first_name" class="form-label">{{ __('First Name') }}</label>
									<input class="form-control" placeholder="{{ __('First Name') }}" id="client-first_name" name="first_name" type="text">
								</div>
								<div class="form-group col-md-6">
									<label for="client_last_name" class="form-label">{{ __('Last Name') }}</label>
									<input class="form-control" placeholder="{{ __('Last Name') }}" id="client-last_name" name="last_name" type="text">
								</div>

								<div class="form-group col-md-12">
									<label>{{ __('Location/City/Address') }}</label>
									<input type="text" name="autocomplete" id="invoice-autocomplete" class="form-control" placeholder="{{ __('Choose Location') }}">
									<input type="hidden" id="invoice-latitude" name="latitude" class="form-control">
									<input type="hidden" id="invoice-longitude" name="longitude" class="form-control">
                                </div>
								<div class="form-group col-md-12">
									<label for="client_address_1" class="form-label">{{ __('Street + Nr.') }}</label>
									<input class="form-control invoice-address_1" placeholder="{{ __('Address 1') }}" id="invoice-address_1" name="address_1" type="text">
								</div>
								<div class="form-group col-md-6">
									<label for="client_address_2" class="form-label">{{ __('Additional Address') }}</label>
									<input class="form-control" placeholder="{{ __('Address 2') }}" id="invoice-address_2" name="address_2" type="text">
								</div>

								<div class="form-group col-md-6">
									<label for="client_zip_code" class="form-label">{{ __('Zipcode') }}</label>
									<input class="form-control" placeholder="{{ __('Zipcode') }}" id="invoice-zip_code" name="zip_code" type="text">
								</div>

								<div class="form-group col-md-6">
									<label for="client_city" class="form-label">{{ __('City') }}</label>
									<input class="form-control" placeholder="{{ __('City') }}" id="invoice-city" name="city" type="text">
								</div>
								<div class="form-group col-md-6">
									<label for="invoice-district_1" class="form-label">{{ __('District 1') }}</label>
									<input class="form-control" placeholder="{{ __('District 1') }}" id="invoice-district_1" name="district_1" type="text">
								</div>
								<div class="form-group col-md-6">
									<label for="invoice-district_2" class="form-label">{{ __('District 2') }}</label>
									<input class="form-control" placeholder="{{ __('District 2') }}" id="invoice-district_2" name="district_2" type="text">
								</div>

								<div class="form-group col-md-6">
									<label for="client_state" class="form-label">{{ __('State') }}</label>
									<input class="form-control" placeholder="{{ __('State') }}" id="invoice-state" name="state" type="text">
								</div>

								<div class="form-group col-md-6 selct2-custom">
									{{ Form::label('country-select', __('Country'), ['class' => 'form-label']) }}
									<select name="country" id="invoice-country" class="form-control country_select2">
										<option value="" data-iso="">{{ __('Select') }}</option>
										@foreach ($countries as $country)
											<option value="{{$country->id}}" data-iso="{{ $country->iso }}"> {{$country->name}}</option>
										@endforeach
									</select>
								</div>

							</div>

							<div class="row">
								<h6 class="my-5">{{ __('Contact Info') }}</h6>
								<div class="form-group col-md-6">
									<label for="client_email" class="form-label">{{ __('Email') }}</label>
									<input class="form-control" placeholder="{{ __('Email') }}" id="client-email" name="email" type="text">
								</div>
								<div class="form-group col-md-6">
									<label for="client_phone" class="form-label">{{ __('Phone') }}</label>
									<input class="form-control" placeholder="{{ __('Phone') }}" id="client-phone" name="phone" type="text">
								</div>
								<div class="form-group col-md-6">
									<label for="client_mobile" class="form-label">{{ __('Mobile') }}</label>
									<input class="form-control" placeholder="{{ __('Mobile') }}" id="client-mobile" name="mobile" type="text">
								</div>
							</div>

							
							<div class="row">
								<h6 class="my-5">
									{{ __('Company Info') }}
								</h6>
								<div class="form-group col-md-12">
									<label for="client_company_name" class="form-label">{{ __('Company Name') }}</label>
									<input class="form-control" placeholder="{{ __('Company Name') }}" id="client-company_name" name="company_name" type="text">
								</div>
								<div class="form-group col-md-6">
									<label for="client_company_website" class="form-label">{{ __('Website') }}</label>
									<input class="form-control" placeholder="{{ __('Website') }}" id="client-company_website" name="website" type="text">
								</div>
								<div class="form-group col-md-6">
									<label for="client_company_tax_number" class="form-label">{{ __('Tax Number') }}</label>
									<input class="form-control" placeholder="{{ __('Tax Number') }}" id="client-company_tax_number" name="tax_number" type="text">
								</div>
							</div>
							<div class="row">
								<div class="form-group">
									<label for="client_company_notes" class="form-label">{{ __('Notes') }}</label>
									<!-- <input class="form-control" placeholder="{{ __('Notes') }}" id="client-company_notes" name="notes" type="text"> -->
									<textarea rows="3" id="client-company_notes" class="form-control border-0 resize-none" name="notes" placeholder="{{ __('Notes') }}" ></textarea>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		@elseif($form_field == "project_status")
			<div class="col-md-12">
				<div class="client-details">
					<div class="form-group col-md-12 selct2-custom">
						{{ Form::label('client_estimation_id', __('Client Estimation'), ['class' => 'form-label']) }}
						<select name="client_estimation" id="client_estimation_id" class="form-control">
							<option value="" data-type="">{{ __('Select') }}</option> 
							@foreach ($estimation_quotes as $quote) 
								<option value="{{$quote->id}}"> {{$quote->title}} ({{$quote->estimation->title}}) - {{$quote->net}}</option> 
							@endforeach
						</select>
					</div>

					<div class="form-group col-md-12 selct2-custom">
						{{ Form::label('sub_contractor_estimation_id', __('Sub Contractor Estimation'), ['class' => 'form-label']) }}
						<select name="sub_contractor_estimation" id="sub_contractor_estimation_id" class="form-control">
							<option value="" data-type="">{{ __('Select') }}</option>
							@foreach ($estimation_quotes as $quote)
								<option value="{{$quote->id}}" > {{$quote->title}} ({{$quote->estimation->title}}) - {{$quote->net}}</option>
							@endforeach
						</select>
					</div>
				</div>
			</div>
		@elseif($form_field == "ConstructionDetails")
			<div class="col-md-6">
				<div class="form-group col-md-12 selct2-custom">
					{{ Form::label('construction_details', __('Construction Details'), ['class' => 'form-label']) }}
					<select name="construction_user_id" id="construction-select" class="form-control">
						<option value="" data-type="">{{ __('Select') }}</option>
						@foreach ($clients as $user)
						@php 
							$selected_construction_user = (isset($project->construction_detail_id) && ($project->construction_detail_id == $user['id'])) ? 'selected' : '';
						@endphp
							<option value="{{$user['id']}}" data-type="{{ $user['type'] }}" {{ $selected_construction_user }}> {{$user['name']}}</option>
						@endforeach
					</select>
				</div>
				<div class="col-md-12">
					@php
						$checked = "checked";
						if(isset($project->is_same_invoice_address) && $project->is_same_invoice_address == 0){
							$checked = "";	
						}
					@endphp
					<input type="checkbox" name="same_invoice_address" id="same_invoice_address" {{ $checked }} value="1">
					<label class="custom-control-label" for="same_invoice_address" >{{ __('Same Invoice Address') }}</label>
				</div>
				<input type="hidden" id='construction_detail_id' name="construction_detail_id">
				<input type="hidden" id='client_type1' name="client_type1">

				<!-- Construction Details Column -->
				<div class="col-md-12 row d-none" id='construction-details'></div>
			</div>
			<div class="col-md-6">
				<div class="client-details different-invoice-address-block @if(isset($project->is_same_invoice_address) && $project->is_same_invoice_address == 1) d-none @endif">
					<div class="form-group col-md-12 selct2-custom">
						{{ Form::label('client', __('Contact'), ['class' => 'form-label']) }}
						<select name="client" id="client-select" class="form-control">
							<option value="" data-type="">{{ __('Select') }} 
							</option>
							@foreach ($clients as $client)
								@php 
									$selected_client_user = (isset($project->client) && ($project->client == $client['id'])) ? 'selected' : '';
								@endphp
							<option value="{{$client['id']}}" {{ $selected_client_user }} data-type="{{ $client['type'] }}"> {{$client['name']}}</option>
							@endforeach
						</select>
						
					</div>
					<div class="col-md-12">
						<label class="custom-control-label" for="" ></label>
					</div>
					<input type="hidden" id='client_id' name="client_id">
					<input type="hidden" id='client_type' name="client_type">
					<div class="contact-details-columns row ">
						<!-- Client Details Column -->
						<div class="col-md-12 row d-none" id="client-details"></div>
					</div>
				</div>
			</div>
		@elseif($form_field == "technical_description")
			<div class="col-md-12">
				<div class="form-group">
					<label for="technical_description" class="form-label">{{ __('Technical Description') }}</label>
					<textarea class="form-control" rows="2" name="technical_description" id="technical-description" spellcheck="false">{{ isset($project->technical_description) ? $project->technical_description : ''}}</textarea>
				</div>
			</div>
		@endif
    </div>
</div>
<div class="modal-footer pr-0">
    <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{ __('Close') }}</button>
    {{Form::submit(__('Save'),array('class'=>'btn  btn-primary btn-create'))}}
</div>
{{Form::close()}}
<script>
	var projectClientId = `{{ isset($project->client_data->id) ? $project->client_data->id : ''}}`;
	var construction_detail_user = `{{ isset($project->construction_detail->id) ? $project->construction_detail->id : ''}}`;
	var constructionDetailId = "<?php echo $project->construction_detail_id; ?>";


	if (construction_detail_user != '') {
		$('#construction-select').trigger('change');
	}

	var selectedOption = $('#client-select option[value="' + projectClientId + '"]');
	if (selectedOption.length > 0) {
		selectedOption.prop('selected', true);
		$('#client-select').trigger('change');
	}

	
	if (projectClientId != '') {
	//	$('#client-select').trigger('change');
	}

	/*
	var selectedConstruction = $('#construction-select option[value="' + constructionDetailId + '"]');
	if (selectedConstruction.length > 0) {
		selectedConstruction.prop('selected', true);
		$('#construction-select').trigger('change');
	}
	*/

	$(".country_select2").select2({
		placeholder: "Country",
		multiple: false,
		dropdownParent:$("#title_form"),
		placeholder: "Select an country",
  		allowClear: true,
		dropdownAutoWidth:true,
	});

</script>