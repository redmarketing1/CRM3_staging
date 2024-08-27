<h3>Construction Details</h3>
<div class="row">
	<h6 class="my-5">
		Personal info
	</h6>
	<div class="form-group col-md-6">
		{{ Form::label('construction-salutation', __('Salutation Title'), ['class' => 'form-label']) }}
		<div class="d-flex radio-check">
			<div class="form-check m-1">
				<input type="radio" value="Mr." id="salutation_mr" name="construction_salutation" class="form-check-input" @if(isset($user->salutation) && $user->salutation == "Mr.") checked @endif>
				<label class="form-check-label" for="salutation_mr">{{__('Mr.')}}</label>
			</div>
			<div class="form-check m-1">
				<input type="radio" value="Ms." id="salutation_ms" name="construction_salutation" class="form-check-input" @if(isset($user->salutation) && $user->salutation == "Ms.") checked @endif>
				<label class="form-check-label" for="salutation_ms">{{ __('Ms.') }}</label>
			</div>
		</div>
	</div>
	<div class="form-group col-md-6">
		{{ Form::label('construction_title', __('Academic Title'), ['class' => 'form-label']) }}
		<select class="form-control" id="construction_detail-title" name="construction_title">
			<option selected="selected" value="">{{__('Select')}}</option>
			<option value="Dr." @if(isset($user->title) && $user->title == "Dr.") selected @endif>{{__('Dr.')}}</option>
			<option value="Prof." @if(isset($user->title) && $user->title == "Prof.") selected @endif>{{__('Prof.')}}</option>
			<option value="Prof. Dr." @if(isset($user->title) && $user->title == "Prof. Dr.") selected @endif>{{__('Prof. Dr.')}}</option>
			<option value="B.Sc." @if(isset($user->title) && $user->title == "B.Sc.") selected @endif>{{__('B.Sc.')}}</option>
			<option value="M.Sc." @if(isset($user->title) && $user->title == "M.Sc.") selected @endif>{{__('M.Sc.')}}</option>
			<option value="Dipl.-Ing." @if(isset($user->title) && $user->title == "Dipl.-Ing.") selected @endif>{{__('Dipl.-Ing.')}}</option>
			<option value="Mag." @if(isset($user->title) && $user->title == "Mag.") selected @endif>{{__('Mag.')}}</option>
		</select>
	</div>
	
	<div class="form-group col-md-6">
		{{ Form::label('construction_first_name', __('First Name'), ['class' => 'form-label']) }}
		<input class="form-control" placeholder="{{__('First Name')}}" id="construction_detail-first_name" name="construction_first_name" type="text" value="{{ isset($user->first_name) ? $user->first_name : ''}}">
	</div>
	<div class="form-group col-md-6">
		{{ Form::label('construction_last_name', __('Last Name'), ['class' => 'form-label']) }}
		<input class="form-control" placeholder="{{__('Last Name')}}" id="construction_detail-last_name" name="construction_last_name" type="text" value="{{ isset($user->last_name) ? $user->last_name : ''}}">
	</div>

	<div class="form-group col-md-12">
		<label>{{ __('Location/City/Address') }}</label>
		<input type="text" name="autocomplete" id="construction_detail-autocomplete" class="form-control" placeholder="{{ __('Choose Location') }}">
		<input type="hidden" id="construction_detail-latitude" name="construction_latitude" class="form-control" value="{{ isset($user->lat) ? $user->lat : ''}}">
		<input type="hidden" id="construction_detail-longitude" name="construction_longitude" class="form-control" value="{{ isset($user->long) ? $user->long : ''}}">
	</div>
	<div class="form-group col-md-12">
		{{ Form::label('construction_address_1', __('Street + Nr.'), ['class' => 'form-label']) }}
		<input class="form-control" placeholder="{{__('Address 1')}}" id="construction_detail-address_1" name="construction_address_1" type="text" value="{{ isset($user->address_1) ? $user->address_1 : ''}}">
	</div>
	<div class="form-group col-md-6">
		{{ Form::label('construction_address_2', __('Additional Address'), ['class' => 'form-label']) }}
		<input class="form-control" placeholder="{{__('Address 2')}}" id="construction_detail-address_2" name="construction_address_2" type="text" value="{{ isset($user->address_2) ? $user->address_2 : ''}}">
	</div>

	<div class="form-group col-md-6">
		{{ Form::label('construction_zip_code', __('Zipcode'), ['class' => 'form-label']) }}
		<input class="form-control" placeholder="{{__('Zipcode')}}" id="construction_detail-zip_code" name="construction_zip_code" type="text" value="{{ isset($user->zip_code) ? $user->zip_code : ''}}">
	</div>

	<div class="form-group col-md-6">
		{{ Form::label('construction_city', __('City'), ['class' => 'form-label']) }}
		<input class="form-control" placeholder="{{__('City')}}" id="construction_detail-city" name="construction_city" type="text" value="{{ isset($user->city) ? $user->city : ''}}">
	</div>
	<div class="form-group col-md-6">
		{{ Form::label('construction_district_1', __('District 1'), ['class' => 'form-label']) }}
		<input class="form-control" placeholder="{{__('District 1')}}" id="construction_detail-district_1" name="construction_district_1" type="text" value="{{ isset($user->district_1) ? $user->district_1 : ''}}">
	</div>
	<div class="form-group col-md-6">
		{{ Form::label('construction_district_2', __('District 2'), ['class' => 'form-label']) }}
		<input class="form-control" placeholder="{{__('District 2')}}" id="construction_detail-district_2" name="construction_district_2" type="text" value="{{ isset($user->district_2) ? $user->district_2 : ''}}">
	</div>
	<div class="form-group col-md-6">
		<label for="client_state" class="form-label">{{ __('State') }}</label>
		<input class="form-control" placeholder="{{ __('State') }}" id="construction_detail-state" name="construction_state" type="text" value="{{ isset($user->state) ? $user->state : ''}}">
	</div>

	<div class="form-group col-md-6">
		{{ Form::label('construction_country', __('Country'), ['class' => 'form-label']) }}
		<div class="form-group col-md-12 selct2-custom">
			<select name="construction_country" id="construction_detail-country" class="form-control country_select2">
				<option value="" data-iso="">{{ __('Select') }}</option>
				@foreach ($countries as $country)
					@php
						$selected_country = "";
						if(isset($user->country) && $country->id == $user->country) {
							$selected_country = "selected";
						}
					@endphp
					<option value="{{$country->id}}" data-iso="{{ $country->iso }}" {{ $selected_country }}> {{$country->name}}</option>
				@endforeach
			</select>
		</div>
	</div>
</div>

<div class="row">
	<h6 class="my-5">{{ __('Contact Info') }}</h6>
	<div class="form-group col-md-6">
		{{ Form::label('construction_email', __('Email'), ['class' => 'form-label']) }}
		<input class="form-control" placeholder="{{__('Email')}}" id="construction_detail-email" name="construction_email" type="text" value="{{ isset($user->email) ? $user->email : ''}}">
	</div>
	<div class="form-group col-md-6">
		{{ Form::label('construction_phone', __('Phone'), ['class' => 'form-label']) }}
		<input class="form-control" placeholder="{{__('Phone')}}" id="construction_detail-phone" name="construction_phone" type="text" value="{{ isset($user->phone) ? $user->phone : ''}}">
	</div>
	<div class="form-group col-md-6">
		{{ Form::label('construction_mobile', __('Mobile'), ['class' => 'form-label']) }}
		<input class="form-control" placeholder="{{__('Mobile')}}" id="construction_detail-mobile" name="construction_mobile" type="text" value="{{ isset($user->mobile_no) ? $user->mobile_no : ''}}">
	</div>
</div>

<div class="row">
	<h6 class="my-5">
		{{ __('Company Info') }}
	</h6>
	<div class="form-group col-md-12">
		{{ Form::label('construction_company_name', __('Company Name'), ['class' => 'form-label']) }}
		<input class="form-control" placeholder="{{__('Company Name')}}" id="construction_detail-company_name" name="construction_company_name" type="text" value="{{ isset($user->company_name) ? $user->company_name : '' }}">
	</div>
	<div class="form-group col-md-6">
		{{ Form::label('construction_company_website', __('Website'), ['class' => 'form-label']) }}
		<input class="form-control" placeholder="{{__('Website')}}" id="construction_detail-company_website" name="construction_company_website" type="text" value="{{ isset($user->website) ? $user->website : '' }}">
	</div>
	<div class="form-group col-md-6">
		{{ Form::label('construction_company_tax_number', __('Tax Number'), ['class' => 'form-label']) }}
		<input class="form-control" placeholder="{{__('Tax Number')}}" id="construction_detail-company_tax_number" name="construction_company_tax_number" type="text" value="{{ isset($user->tax_number) ? $user->tax_number : '' }}">
	</div>
</div>
<div class="row">
	<div class="form-group">
		{{ Form::label('construction_company_notes', __('Notes'), ['class' => 'form-label']) }}
		<textarea rows="3" id="construction_detail-company_notes" class="form-control border-0 resize-none " name="construction_company_notes" placeholder="{{ __('Notes') }}" >
			{{ isset($user->notes) ? $user->notes : '' }}
		</textarea>
	</div>
</div>
