<div class="address-box">
	<div class="d-flex construction_address">
		<div class="construction_detail_address">
			@if(isset($project->construction_detail->id))
				<div class="personal-detail-class">
					<span>
						@if((isset($project->construction_detail->salutation) && !empty($project->construction_detail->salutation)) || 
						isset($project->construction_detail->title) && !empty($project->construction_detail->title))
							@if(isset($project->construction_detail->salutation) && !empty($project->construction_detail->salutation))
								{{ __($project->construction_detail->salutation) }}
							@endif
							@if(isset($project->construction_detail->title) && !empty($project->construction_detail->title))
								{{__($project->construction_detail->title)}}
							@endif
						@endif

						@if((isset($project->construction_detail->first_name) && !empty($project->construction_detail->first_name)) || (isset($project->construction_detail->last_name) && !empty($project->construction_detail->last_name)))
							@if(isset($project->construction_detail->first_name) && !empty($project->construction_detail->first_name))
								{{$project->construction_detail->first_name}}
							@endif
							@if(isset($project->construction_detail->last_name) && !empty($project->construction_detail->last_name))
								{{$project->construction_detail->last_name}}
							@endif
						@endif
					</span>

					@if(isset($project->construction_detail->company_name) && !empty($project->construction_detail->company_name))
						<span>{{$project->construction_detail->company_name}}</span>
					@endif

					@if(isset($project->construction_detail->email) && !empty($project->construction_detail->email))
						<span><a href="mailto:{{$project->construction_detail->email}}">{{$project->construction_detail->email}}</a></span>
					@endif

					<?php
					// Function to format WhatsApp links correctly with country code
					if (!function_exists('formatWhatsAppLink')) {
						function formatWhatsAppLink($phoneNumber, $salutation, $lastName) {
							// Remove all non-digits
							$cleanNumber = preg_replace('/\D+/', '', $phoneNumber);

							// Append country code '49' if not already present
							if (substr($cleanNumber, 0, 2) == "49") {
								$internationalNumber = $cleanNumber;
							} elseif (substr($cleanNumber, 0, 4) == "0049") {
								$internationalNumber = substr($cleanNumber, 2);
							} else {
								$internationalNumber = "49" . $cleanNumber;
							}

							// Encode the greeting message
							$messageText = urlencode("Hallo " . $salutation . " " . $lastName);

							// Create the full WhatsApp link
							return "https://wa.me/$internationalNumber?text=$messageText";
						}
					}
					?>

					@if(isset($project->construction_detail->phone) && !empty($project->construction_detail->phone))
						<span>
							<a href="tel:+{{$project->construction_detail->phone}}">{{$project->construction_detail->phone}}</a>
							<a href="{{ formatWhatsAppLink($project->construction_detail->phone, $project->construction_detail->salutation, $project->construction_detail->last_name) }}" target="_blank"><i class="fa-brands fa-whatsapp"></i></a>
						</span>    
					@endif

					@if(isset($project->construction_detail->mobile) && !empty($project->construction_detail->mobile))
						<span>
							<a href="tel:+{{$project->construction_detail->mobile}}">{{$project->construction_detail->mobile}}</a>
							<a href="{{ formatWhatsAppLink($project->construction_detail->mobile, $project->construction_detail->salutation, $project->construction_detail->last_name) }}" target="_blank"><i class="fa-brands fa-whatsapp"></i></a>
						</span>    
					@endif

					
					@if(isset($project->construction_detail->website) && !empty($project->construction_detail->website))
						<span><a href="{{$project->construction_detail->website}}">{{$project->construction_detail->website}}</a></span>    
					@endif
				</div>
				<div class="address-class">
					@if(isset($project->construction_detail->address_1)) 
						<span class="address_1">{{ ' '.$project->construction_detail->address_1 }}</span>
					@endif
					@if(isset($project->construction_detail->address_2)) 
						<span class="address_2">{{ ' '.$project->construction_detail->address_2 }}</span>
					@endif
					@if(isset($project->construction_detail->zip_code) && !empty($project->construction_detail->city))
						<span class="zip_city">{{ ' '.$project->construction_detail->zip_code . ' ' . $project->construction_detail->city }}</span>
					@else
						@if(isset($project->construction_detail->zip_code))
							<span class="zip_code">{{ ' '.$project->construction_detail->zip_code }}</span>
						@endif
						@if(!empty($project->construction_detail->city))
							<span class="city">{{ ' '. $project->construction_detail->city }}</span>
						@endif
					@endif
					@if (!empty($project->construction_detail->district))
						<span class="district">{{ ' '. $project->construction_detail->district }}</span>
					@endif
					@if (!empty($project->construction_detail->state))
						<span class="state">{{ ' '. $project->construction_detail->state }}</span>
					@endif
					@if (!empty($project->construction_detail->country))
						<span class="country_name">{{ !empty($project->construction_detail->countryDetail) ? ' '. $project->construction_detail->countryDetail->name : '' }}</span>
					@endif
				</div>

				@if((isset($project->construction_detail->tax_number) && !empty($project->construction_detail->tax_number)) || (isset($project->construction_detail->notes) && !empty($project->construction_detail->notes)))
					<div class="company-info-class">
						@if(isset($project->construction_detail->tax_number) && !empty($project->construction_detail->tax_number))
							<span>{{$project->construction_detail->tax_number}}</span>
						@endif

						@if(isset($project->construction_detail->notes) && !empty($project->construction_detail->notes))
							<span>{!! $project->construction_detail->notes !!}</span>
						@endif
					</div>
				@endif
			@endif
		</div>
	</div>
	@if ($user->type == 'company')
		@php
			$same_invoice_div2 = "d-none";
			if($project->is_same_invoice_address == 0){
				$same_invoice_div2 = "";
			}
		@endphp
		<div class="d-flex invoice_address2 {{ $same_invoice_div2 }}">
			<div class="client_invoice_address">
				@if(isset($project->client_data->id))
					<div class="personal-detail-class">
						<span>
							@if(isset($project->client_data->salutation) && !empty($project->client_data->salutation))
								{{ __($project->client_data->salutation) }}     
								@if(isset($project->client_data->title))
									{{ __($project->client_data->title) }}
								@endif
							@endif

							@if(isset($project->client_data) && ((isset($project->client_data->first_name) && !empty($project->client_data->first_name) ) || (isset($project->client_data->last_name) && !empty($project->client_data->last_name))))
								@if(isset($project->client_data->first_name))
									{{$project->client_data->first_name}}
								@endif
								@if(isset($project->client_data->last_name))
									{{$project->client_data->last_name}}
								@endif
							@endif
						</span> 

						@if(isset($project->client_data->company_name) && !empty($project->client_data->company_name))
							<span>{{$project->client_data->company_name}} </span> 
						@endif

						@if(isset($project->client_data->email) && !empty($project->client_data->email))
							<span><a href="mailto:{{$project->client_data->email}}">{{$project->client_data->email}}</a></span> 
						@endif

						@if(isset($project->client_data->phone) && !empty($project->client_data->phone))
							<span><a href="tel:+{{$project->client_data->phone}}">{{$project->client_data->phone}}</a></span>
						@endif

						@if(isset($project->client_data->mobile) && !empty($project->client_data->mobile))
							<span><a href="tel:+{{$project->client_data->mobile}}">{{$project->client_data->mobile}}</a></span> 
						@endif
						
						@if(isset($project->client_data->website) && !empty($project->client_data->website))
							<span><a href="{{$project->client_data->website}}">{{$project->client_data->website}}</a></span>    
						@endif
					</div>

					<div class="address-class">
						@if((isset($project->client_data->address_1) && !empty($project->client_data->address_1)))
							<span class="address_1">{{ ' '.$project->client_data->address_1 }}</span>
						@endif
						@if((isset($project->client_data->address_2) && !empty($project->client_data->address_2)))
							<span class="address_2">{{ ' '.$project->client_data->address_2 }}</span>
						@endif
						@if(isset($project->client_data->zip_code) && !empty($project->client_data->zip_code) && !empty($project->client_data->city))
							<span class="zip_city">{{ ' '.$project->client_data->zip_code . ' ' . $project->client_data->city }}</span>
						@else
							@if((isset($project->client_data->zip_code) && !empty($project->client_data->zip_code)))
								<span class="zip_code">{{ ' '.$project->client_data->zip_code }}</span>
							@endif
							@if(!empty($project->client_data->city))
								<span class="city">{{ ' '. $project->client_data->city }}</span>
							@endif
						@endif
						@if (!empty($project->client_data->district))
							<span class="district">{{ ' '. $project->client_data->district }}</span>
						@endif
						@if (!empty($project->client_data->state))
							<span class="state">{{ ' '. $project->client_data->state }}</span>
						@endif
						@if (!empty($project->client_data->country))
							<span class="country_name">{{ ' '. $project->client_data->countryDetail->name }}</span>
						@endif
					</div>
					
					@if((isset($project->client_data->tax_number) && !empty($project->client_data->tax_number)) || (isset($project->client_data->notes) && !empty($project->client_data->notes)))
						<div class="company-info-class">
							@if(isset($project->client_data->tax_number) && !empty($project->client_data->tax_number))
							<span>{{$project->client_data->tax_number}}</span>
							@endif

							@if(isset($project->client_data->notes) && !empty($project->client_data->notes))
								<span>{{$project->client_data->notes}}</span>
							@endif
						</div>
					@endif
				@endif
			</div>
		</div>
	@endif
</div>
@if ($user->type == 'company')
	<div class="d-flex same_invoice_address {{ (isset($project->construction_detail->id) && $project->is_same_invoice_address == 1) ? '' : 'd-none'}}">
		<b class="mb-3 mt-2">
			<i class="fa-regular fa-square-check"></i> {{ __('Same Invoice address') }} 
		</b>
	</div>
@endif
