<h3>Client Details</h3>
<div class="row">
	<h6 class="my-5">
		Personal info
	</h6>
	<div class="form-group col-md-6">
		<label for="client-salutation" class="form-label">Salutation Title</label>
		<div class="d-flex radio-check">
			<div class="form-check m-1">
				<input type="radio" value="Mr." id="client-salutation_mr" name="salutation" class="form-check-input" @if(isset($user->salutation) && $user->salutation == "Mr.") checked @endif>
				<label class="form-check-label" for="client-salutation_mr">{{__('Mr.')}}</label>
			</div>
			<div class="form-check m-1">
				<input type="radio" value="Ms." id="client-salutation_ms" name="salutation" class="form-check-input" @if(isset($user->salutation) && $user->salutation == "Ms.") checked @endif>
				<label class="form-check-label" for="client-salutation_ms">{{ __('Ms.') }}</label>
			</div>
		</div>
	</div>
	<div class="form-group col-md-6">
		<label for="client-title" class="form-label">Academic Title</label>
		<select class="form-control" id="client-title" name="title">
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
		<label for="client_first_name" class="form-label">First Name</label>
		<input class="form-control" placeholder="Name" id="client-first_name" name="first_name" type="text" value="{{ isset($user->first_name) ? $user->first_name : ''}}">
	</div>
	<div class="form-group col-md-6">
		<label for="client_last_name" class="form-label">Last Name</label>
		<input class="form-control" placeholder="Name" id="client-last_name" name="last_name" type="text" value="{{ isset($user->last_name) ? $user->last_name : ''}}">
	</div>
	
	<div class="form-group col-md-12">
		<label>{{ __('Location/City/Address') }}</label>
		<input type="text" name="autocomplete" id="invoice-autocomplete" class="form-control" placeholder="{{ __('Choose Location') }}">
		<input type="hidden" id="invoice-latitude" name="latitude" class="form-control" value="{{ isset($user->lat) ? $user->lat : ''}}">
		<input type="hidden" id="invoice-longitude" name="longitude" class="form-control" value="{{ isset($user->long) ? $user->long : ''}}">
	</div>
	<div class="form-group col-md-12">
		<label for="client_address_1" class="form-label">{{ __('Street + Nr.') }}</label>
		<input class="form-control invoice-address_1" placeholder="{{ __('Address 1') }}" id="invoice-address_1" name="address_1" type="text" value="{{ isset($user->address_1) ? $user->address_1 : ''}}">
	</div>
	<div class="form-group col-md-6">
		<label for="client_address_2" class="form-label">{{ __('Additional Address') }}</label>
		<input class="form-control" placeholder="{{ __('Address 2') }}" id="invoice-address_2" name="address_2" type="text" value="{{ isset($user->address_2) ? $user->address_2 : ''}}">
	</div>

	<div class="form-group col-md-6">
		<label for="client_zip_code" class="form-label">{{ __('Zipcode') }}</label>
		<input class="form-control" placeholder="{{ __('Zipcode') }}" id="invoice-zip_code" name="zip_code" type="text" value="{{ isset($user->zip_code) ? $user->zip_code : ''}}">
	</div>

	<div class="form-group col-md-6">
		<label for="client_city" class="form-label">{{ __('City') }}</label>
		<input class="form-control" placeholder="{{ __('City') }}" id="invoice-city" name="city" type="text" value="{{ isset($user->city) ? $user->city : ''}}">
	</div>
	<div class="form-group col-md-6">
		<label for="invoice-district_1" class="form-label">{{ __('District 1') }}</label>
		<input class="form-control" placeholder="{{ __('District 1') }}" id="invoice-district_1" name="district_1" type="text" value="{{ isset($user->district_1) ? $user->district_1 : ''}}">
	</div>
	<div class="form-group col-md-6">
		<label for="invoice-district_2" class="form-label">{{ __('District 2') }}</label>
		<input class="form-control" placeholder="{{ __('District 2') }}" id="invoice-district_2" name="district_2" type="text" value="{{ isset($user->district_2) ? $user->district_2 : ''}}">
	</div>

	<div class="form-group col-md-6">
		<label for="client_state" class="form-label">{{ __('State') }}</label>
		<input class="form-control" placeholder="{{ __('State') }}" id="invoice-state" name="state" type="text" value="{{ isset($user->state) ? $user->state : ''}}">
	</div>

	<div class="form-group col-md-6 selct2-custom">
		{{ Form::label('country-select', __('Country'), ['class' => 'form-label']) }}
		<select name="country" id="invoice-country" class="form-control country_select2">
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

<div class="row">
	<h6 class="my-5">{{ __('Contact Info') }}</h6>
	<div class="form-group col-md-6">
		<label for="client_email" class="form-label">Email</label>
		<input class="form-control" placeholder="Email" id="client-email" name="email" type="text" value="{{ isset($user->email) ? $user->email : ''}}">
	</div>
	<div class="form-group col-md-6">
		<label for="client_phone" class="form-label">Phone</label>
		<input class="form-control" placeholder="Phone" id="client-phone" name="phone" type="text" value="{{ isset($user->phone) ? $user->phone : ''}}">
	</div>
	<div class="form-group col-md-6">
		<label for="client_mobile" class="form-label">Mobile</label>
		<input class="form-control" placeholder="Mobile" id="client-mobile" name="mobile_no" type="text" value="{{ isset($user->mobile_no) ? $user->mobile_no : ''}}">
	</div>
</div>

<div class="row">
	<h6 class="my-5">
		Company Info
	</h6>
	<div class="form-group col-md-12">
		<label for="client_company_name" class="form-label">Company Name</label>
		<input class="form-control" placeholder="Company Name" id="client-company_name" name="company_name" type="text" value="{{ isset($user->company_name) ? $user->company_name : '' }}">
	</div>
	<div class="form-group col-md-6">
		<label for="client_company_website" class="form-label">Website</label>
		<input class="form-control" placeholder="Website" id="client-company_website" name="website" type="text" value="{{ isset($user->website) ? $user->website : '' }}">
	</div>
	<div class="form-group col-md-6">
		<label for="client_company_tax_number" class="form-label">Tax Number</label>
		<input class="form-control" placeholder="Tax Number" id="client-company_tax_number" name="tax_number" type="text" value="{{ isset($user->tax_number) ? $user->tax_number : '' }}">
	</div>
</div>
<div class="row">
	<div class="form-group">
		<label for="client_company_notes" class="form-label">Notes</label>
		<textarea rows="3" id="client-company_notes" class="form-control border-0 resize-none" name="notes" placeholder="{{ __('Notes') }}" >
			{{ isset($user->notes) ? $user->notes : '' }}
		</textarea>
	</div>
</div>