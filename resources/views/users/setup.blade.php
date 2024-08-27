@extends('layouts.main')
@php
    if(Auth::user()->type=='super admin')
    {
        $plural_name = __('Customers');
        $singular_name = __('Customer');
    }
    else{

        $plural_name =__('Users');
        $singular_name =__('User');
    }
@endphp
@section('page-title')
    {{ $plural_name}}
@endsection
@section('page-breadcrumb')
<a href="{{route('users.index')}}">{{ $plural_name}}</a>, {{ ($user_id > 0) ? __('Update') : __('Create') }}
@endsection
@section('page-action')
    
@endsection
@push('css')
	<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
	<link rel="stylesheet" href="{{ asset('Modules/Taskly/Resources/assets/css/custom.css') }}" type="text/css" />
	<link rel="stylesheet" href="{{ asset('css/common.css') }}" type="text/css" />
	<style>
		 #dropBox {
            width: 100%;
            height: 150px;
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

        .container input.image_selection {
            position: absolute;
            opacity: 0;
            cursor: pointer;
            height: 0;
            width: 0;
        }
        .checkmark {
            position: absolute;
            top: 0;
            left: 0;
            height: 15px;
            width: 15px;
            background-color: rgba(var(--bs-danger-rgb), var(--bs-bg-opacity)) !important;
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

        .selected_image .actionbuttons .bg-danger{
			visibility: visible;
		}

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

        .refresh_password i {
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
        }
	</style>
@endpush
@push('scripts')
	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
	<script src="{{ asset('Modules/Taskly/Resources/assets/js/tinymce/tinymce.min.js') }}"></script>
	<script src="{{ asset('Modules/Taskly/Resources/assets/libs/select2/dist/js/select2.min.js')}}"></script>
	<script src="{{ asset('Modules/Taskly/Resources/assets/js/custom.js')}}"></script>
	<script type="text/javascript" src="https://maps.google.com/maps/api/js?key=AIzaSyBbTqlUNbqPssvetzvRl4n65HB2g_-o9tE&libraries=places" ></script>
@endpush
@section('content')
	<div class="row">
		<!-- [ sample-page ] start -->
		<div class="col-sm-12" id='client-details'>
			<div class="row">
				<div class="col-xl-3">
					<div class="card sticky-top" style="top:30px">
						<div class="list-group list-group-flush" id="useradd-sidenav">
							<a href="#useradd-1" class="list-group-item list-group-item-action border-0">
								{{ __('Personal Info') }} 
								<div class="float-end"><i class="ti ti-chevron-right"></i></div>
							</a>
							<a href="#useradd-2" class="list-group-item list-group-item-action border-0">
								{{ __('Address Info') }} 
								<div  class="float-end"><i class="ti ti-chevron-right"></i></div>
							</a>
							<a href="#useradd-3" class="list-group-item list-group-item-action border-0">
								{{ __('Company Info') }} 
								<div class="float-end"><i class="ti ti-chevron-right"></i></div>
							</a>
							@if ($user_id > 0)
								<a href="#useradd-4" class="list-group-item list-group-item-action border-0">
									{{ __('Documents') }} 
									<div class="float-end"><i class="ti ti-chevron-right"></i></div>
								</a>
							@endif
						</div>
					</div>
				</div>
				<div class="col-xl-9">
					{{ Form::model($user, ['route' => ['users.commit', $user_id], 'method' => 'POST', 'enctype' => 'multipart/form-data', 'id' => 'contact_form']) }}
					<input type="hidden" id='user_id' name="user_id" value="{{ $user_id }}">
					<div id="useradd-1" class="card">
						<div class="card-header">
							<h5>{{ __('Personal Info') }}</h5>
							<small class="text-muted">{{ __('Edit details about your personal information') }}</small>
						</div>

						<div class="card-body">
							<div class="row">
								<div class=" col-md-6">
									<div class="form-group">
										{{ Form::label('roles', __('Roles'),['class'=>'form-label']) }}
				                        {{ Form::select('roles',$roles, null, ['class' => 'form-control', 'id' => 'user_id', 'data-toggle' => 'select']) }}
									</div>
									<div class="form-group">
										{{ Form::label('company_name', __('Company Name'), ['class' => 'form-label']) }}
										{{ Form::text('company_name', null, ['class' => 'form-control', 'id' => 'client-company_name']) }}
										@error('company_name')
											<span class="invalid-company_name" role="alert">
												<strong class="text-danger">{{ $message }}</strong>
											</span>
										@enderror
									</div>
								</div>
								<div class=" col-md-6">
									<div class="form-group">
										<label class="form-label">Avatar</label>
										<div class="choose-files">
											<label for="file-1">
												<div class=" bg-primary company_logo_update"> <i class="ti ti-upload px-1"></i>{{ __('Choose file here') }} </div>
												<input type="file" name="profile" id="file-1" class="form-control file mb-3 d-none" onchange="document.getElementById('blah').src = window.URL.createObjectURL(this.files[0])" data-multiple-caption="{count} files selected" multiple/>
												<span class="text-xs text-muted">{{ __('Please upload a valid image file. Size of image should not be more than 2MB.')}}</span>
												<img id="blah"  width="25%" src="{{ isset($user->image_path) ? $user->image_path : '' }}" />
											</label>
										</div>
										@error('avatar')
											<span class="invalid-feedback text-danger text-xs" role="alert">{{ $message }}</span>
										@enderror
									</div>
								</div>
							</div>
							<div class="row">
								<div class="form-group col-md-6">
									{{ Form::label('title', __('Academic Title'), ['class' => 'form-label']) }}
									{{ Form::select(
										'title',
										[
											'Dr.' => __('Dr.'),
											'Prof.' => __('Prof.'),
											'Prof. Dr.' => __('Prof. Dr.'),
											'B.Sc.' => __('B.Sc.'),
											'M.Sc.' => __('M.Sc.'),
											'Dipl.-Ing.' => __('Dipl.-Ing.'),
											'Mag.' => __('Mag.'),
										],
										null,
										['class' => 'form-control', 'placeholder' => __('Select'), 'id' => 'client-title'],
									) }}
								</div>
								<div class="form-group col-md-6">
									{{ Form::label('salutation', __('Salutation Title'), ['class' => 'form-label']) }}
									{{ Form::select(
										'salutation',
										[
											'Mr.' => __('Mr.'),
											'Ms.' => __('Ms.'),
										],
										null,
										['class' => 'form-control', 'placeholder' => __('Select'), 'id' => 'client-salutation'],
									) }}
								</div>

								<div class="col-sm-6">
									<div class="form-group">
										{{ Form::label('first_name', __('First Name'), ['class' => 'form-label']) }}
										{{ Form::text('first_name', null, ['class' => 'form-control font-style', 'id' => 'client-first_name']) }}
										@error('first_name')
											<span class="invalid-first_name" role="alert">
												<strong class="text-danger">{{ $message }}</strong>
											</span>
										@enderror
									</div>
								</div>
								<div class="col-sm-6">
									<div class="form-group">
										{{ Form::label('last_name', __('Last Name'), ['class' => 'form-label']) }}
										{{ Form::text('last_name', null, ['class' => 'form-control font-style', 'id' => 'client-last_name']) }}
										@error('last_name')
											<span class="invalid-last_name" role="alert">
												<strong class="text-danger">{{ $message }}</strong>
											</span>
										@enderror
									</div>
								</div>

								<div class="col-sm-6">
									<div class="form-group">
										{{ Form::label('email', __('Email'), ['class' => 'form-label']) }}
										{{ Form::text('email', null, ['class' => 'form-control', 'rows' => '4', 'id' => 'client-email']) }}
										@error('email')
											<span class="invalid-email" role="alert">
												<strong class="text-danger">{{ $message }}</strong>
											</span>
										@enderror
									</div>
								</div>

								<div class="col-sm-6">
									<div class="form-group">
										{{ Form::label('phone', __('Phone'), ['class' => 'form-label']) }}
										{{ Form::text('phone', null, ['class' => 'form-control', 'id' => 'client-phone']) }}
										@error('phone')
											<span class="invalid-mobile" role="alert">
												<strong class="text-danger">{{ $message }}</strong>
											</span>
										@enderror
									</div>
								</div>

								<div class="col-sm-6">
									<div class="form-group">
										{{ Form::label('mobile_no', __('Mobile'), ['class' => 'form-label']) }}
										{{ Form::text('mobile_no', null, ['class' => 'form-control', 'id' => 'client-mobile']) }}
										@error('mobile_no')
											<span class="invalid-mobile" role="alert">
												<strong class="text-danger">{{ $message }}</strong>
											</span>
										@enderror
									</div>
								</div>


								<div class="col-sm-6">
									<div class="form-group">
										{{ Form::label('website', __('Website'), ['class' => 'form-label']) }}
										{{ Form::text('website', null, ['class' => 'form-control', 'id' => 'client-company_website']) }}
										@error('website')
											<span class="invalid-website" role="alert">
												<strong class="text-danger">{{ $message }}</strong>
											</span>
										@enderror
									</div>
								</div>

								<div class="col-sm-6">
									<div class="form-group">
										<label>Password</label>
										<div class="input-group">
											<input class="form-control" name="password" type="password" id="client-password" autocomplete="off">
											<div class="input-group-append refresh_password">
												<i class="input-group-text ti ti-refresh" aria-hidden="true"></i>
											</div>
											<div class="input-group-append" id="show_hide_password">
												<i class="input-group-text fa fa-eye-slash" aria-hidden="true"></i>
											</div>
										</div>
										@error('password')
											<span class="invalid-password" role="alert">
												<strong class="text-danger">{{ $message }}</strong>
											</span>
										@enderror
										@if ($user_id > 0)
											<a href="javascript:void(0)" class="send_access">{{ __('Send access to contact') }}</a>
										@else
											<div class="form-check mt-2">
												<input type="checkbox" class="form-check-input" id="send_access" name="send_access" />
												<label class="form-check-label f-w-600 pt-1 ms-1" for="send_access">{{  __('Send access to contact') }}</label>
											</div>
										@endif
									</div>
								</div>

								<div class="col-sm-6">
									<div class="form-check form-switch mt-4 pt-2">
										@php
											$checked = "";
											if(isset($user->is_enable_login) && $user->is_enable_login == 1) {
												$checked = "checked";
											}
										@endphp
										<input type="checkbox" class="form-check-input" id="client_is_active" name="is_enable_login" {{ $checked }} />
										<label class="form-check-label f-w-600 pl-1" for="client_is_active">{{ __('Login Allowed?') }}</label>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div id="useradd-2" class="card">
						<div class="card-header">
							<h5>{{ __('Address Info') }}</h5>
							<small class="text-muted">{{ __('Edit details about your address information') }}</small>
						</div>
						<div class="card-body">
							<div class="row mt-3">
								<div class="col-sm-12">
									<div class="form-group">
										<label>Location/City/Address</label>
										<input type="text" name="autocomplete" id="google-autocomplete" class="form-control" placeholder="Choose Location">
										<input type="hidden" id="client-latitude" name="latitude" class="form-control" value="{{ isset($user->lat) ? $user->lat : ''}}">
										<input type="hidden" id="client-longitude" name="longitude" class="form-control" value="{{ isset($user->long) ? $user->long : ''}}">
									</div>
								</div>
								<div class="col-sm-4">
									<div class="form-group">
										{{ Form::label('address_1', __('Address 1'), ['class' => 'form-label']) }}
										{{ Form::text('address_1', null, ['class' => 'form-control', 'rows' => '4', 'id' => 'client-address_1']) }}
										@error('address_1')
											<span class="invalid-address_1" role="alert">
												<strong class="text-danger">{{ $message }}</strong>
											</span>
										@enderror
									</div>
								</div>
								<div class="col-sm-4">
									<div class="form-group">
										{{ Form::label('address_2', __('Address 2'), ['class' => 'form-label']) }}
										{{ Form::text('address_2', null, ['class' => 'form-control', 'rows' => '4', 'id' => 'client-address_2']) }}
										@error('address_2')
											<span class="invalid-address_2" role="alert">
												<strong class="text-danger">{{ $message }}</strong>
											</span>
										@enderror
									</div>
								</div>

								<div class="col-md-4">
									<div class="form-group ">
										{{ Form::label('zip_code', __('Zip Code'), ['class' => 'form-label']) }}
										{{ Form::text('zip_code', null, ['class' => 'form-control', 'id' => 'client-zip_code']) }}
										@error('zip_code')
											<span class="invalid-zip_code" role="alert">
												<strong class="text-danger">{{ $message }}</strong>
											</span>
										@enderror
									</div>
								</div>


								<div class="col-md-4">
									<div class="form-group ">
										{{ Form::label('city', __('City'), ['class' => 'form-label']) }}
										{{ Form::text('city', null, ['class' => 'form-control', 'id' => 'client-city']) }}
										@error('city')
											<span class="invalid-city" role="alert">
												<strong class="text-danger">{{ $message }}</strong>
											</span>
										@enderror
									</div>
								</div>

								<div class="col-md-4">
									<div class="form-group ">
										{{ Form::label('district_1', __('District 1'), ['class' => 'form-label']) }}
										{{ Form::text('district_1', null, ['class' => 'form-control', 'id' => 'client-district_1']) }}
										@error('district_1')
											<span class="invalid-district" role="alert">
												<strong class="text-danger">{{ $message }}</strong>
											</span>
										@enderror
									</div>
								</div>
								<div class="col-md-4">
									<div class="form-group ">
										{{ Form::label('district_2', __('District 2'), ['class' => 'form-label']) }}
										{{ Form::text('district_2', null, ['class' => 'form-control', 'id' => 'client-district_2']) }}
										@error('district_2')
											<span class="invalid-district" role="alert">
												<strong class="text-danger">{{ $message }}</strong>
											</span>
										@enderror
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group ">
										{{ Form::label('state', __('State'), ['class' => 'form-label']) }}
										{{ Form::text('state', null, ['class' => 'form-control', 'id' => 'client-state']) }}
										@error('state')
											<span class="invalid-state" role="alert">
												<strong class="text-danger">{{ $message }}</strong>
											</span>
										@enderror
									</div>
								</div>

								<div class="col-md-6 selct2-custom">
									{{ Form::label('country', __('Country'), ['class' => 'form-label']) }}
									<select type="text" name="country" class="form-control country_select2"
										id="client-country">
										<option value="">{{ __('Select Country') }}</option>
										@if(isset($countries) && count($countries) > 0)
											@foreach ($countries as $country)
												@php
													$selected_country = "";
													if(isset($user->country) && $user->country == $country->id) {
														$selected_country = "selected";
													}
												@endphp
												<option value="{{ $country->id }}" data-iso="{{ $country->iso }}" {{ $selected_country }}>
													{{ $country->name }}
												</option>
											@endforeach
										@endif
									</select>
									@error('country')
										<span class="invalid-country" role="alert">
											<strong class="text-danger">{{ $message }}</strong>
										</span>
									@enderror
								</div>


							</div>
						</div>
					</div>
					<div id="useradd-3" class="card">
						<div class="card-header">
							<h5>{{ __('Company Info') }}</h5>
							<small class="text-muted">{{ __('Edit details about your company information') }}</small>
						</div>
						<div class="card-body">
							<div class="row mt-3">



								<div class="col-sm-6">
									<div class="form-group">
										{{ Form::label('tax_number', __('Tax Number'), ['class' => 'form-label']) }}
										{{ Form::text('tax_number', null, ['class' => 'form-control', 'id' => 'client-company_tax_number']) }}
										@error('tax_number')
											<span class="invalid-tax_number" role="alert">
												<strong class="text-danger">{{ $message }}</strong>
											</span>
										@enderror
									</div>
								</div>
								<div class="col-md-12">
									<div class="form-group">
										{{ Form::label('notes', __('Notes'), ['class' => 'form-label']) }}
										{{ Form::textarea('notes', null, ['class' => 'form-control', 'rows' => '3', 'id' => 'client-company_notes']) }}
										@error('notes')
											<span class="invalid-notes" role="alert">
												<strong class="text-danger">{{ $message }}</strong>
											</span>
										@enderror
									</div>
								</div>

							</div>
						</div>
					</div>
					@if ($user_id > 0)
						<div id="useradd-4" class="card">
							<div class="card-header">
								<h5>{{ __('Documents') }}</h5>
								<small class="text-muted">{{ __('Upload documents related to contact') }}</small>
							</div>
							<div class="card-body">
								<div class="row mt-3">
									<div class="col-md-12">
										<div id="dropBox" ondrop="handleDrop(event)" ondragover="handleDragOver(event)">
											<p style="font-size:20px ">Drag & Drop files here or click to select</p>
										</div>
										<input type="file" id="fileInput" multiple onchange="handleFileSelect(event)" />
									</div>
									<div class="col-md-12">
										<div id="previewContainer"></div>
									</div>
									<div class="col-md-12">
										<div class="float-end d-flex">
											@if (\Auth::user()->type == 'company')
												<input type="hidden" value="" name="remove_files_ids" id="remove_files_ids">
												<button type="button" class="btn btn-sm btn-primary btn-icon btn_bulk_delete_files m-1 d-none">
													<i class="ti ti-trash text-white" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Delete Files') }}"></i> {{ __('Delete Files') }}
												</button>
											@endif
										</div>
									</div>
									<div class="table-responsive mediabox"></div>
								</div>
							</div>
						</div>
					@endif
					<div class="modal-footer">
						{{ Form::submit(__('Save'), ['class' => 'btn btn-primary d-flex align-items-center']) }}
					</div>
					{{ Form::close() }}

				</div>
			</div>
			<!-- [ sample-page ] end -->
		</div>
		<!-- [ Main Content ] end -->
	</div>
@endsection
@push('scripts')
    {{-- Password  --}}
    <script>
        var scrollSpy = new bootstrap.ScrollSpy(document.body, {
            target: '#useradd-sidenav',
            offset: 300
        })
    </script>
	<script>
        $(document).ready(function(){
            $(document).on("click", "#dropBox", function(e) {
                e.preventDefault();
                $("#fileInput").trigger('click');
            });
            $('#show_hide_password').on('click', function(){
                $(this).find('i').toggleClass('fa-eye-slash').toggleClass('fa-eye');
                var type = $(this).parent('.input-group').find('input').attr('type');
                if (type == 'text') {
                    $(this).parent('.input-group').find('input').attr('type', 'password');
                } else {
                    $(this).parent('.input-group').find('input').attr('type', 'text');
                }
            });

            init_tiny_mce('#client-company_notes');

            load_gallary();

            $(document).on("click", ".btn_bulk_delete_files", function(e) {
				e.preventDefault();

                const swalWithBootstrapButtons = Swal.mixin({
                    customClass: {
                        confirmButton: 'btn btn-success',
                        cancelButton: 'btn btn-danger'
                    },
                    buttonsStyling: false
                })
                swalWithBootstrapButtons.fire({
                    title: 'Are you sure!,,?',
                    text: "This action can not be undone. Do you want to continue?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes',
                    cancelButtonText: 'No',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        var remove_files_ids = $('#remove_files_ids').val();
						var csrfToken = $('meta[name="csrf-token"]').attr('content');
                        $.ajax({
                            url:'{{route('users.files.delete',$user_id)}}',
                            type:"POST",
                            data:{remove_files_ids : remove_files_ids,_token:csrfToken},
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

			$(document).on("click", ".send_access", function(e) {
                e.preventDefault();
				var csrfToken = $('meta[name="csrf-token"]').attr('content');
				$.ajax({
					url:'{{route('users.send_access',$user_id)}}',
					type:"POST",
					data:{html:true,_token:csrfToken},
					success:function (response) {
						if(response.status == true){
							toastrs('Success', response.message, 'success')
						} else {
							toastrs('Error', response.message)
						}
			        }
				});
            });

            $(document).on("click", ".refresh_password", function(e) {
                e.preventDefault();
                $(this).parent('.input-group').find('input').val(generate_string(6))
				
            });
        });

		$(".country_select2").select2({
			multiple: false,
			dropdownParent:$("#contact_form"),
			placeholder: "Select an country",
			allowClear: true,
			dropdownAutoWidth:true,
		});

		google.maps.event.addDomListener(window, 'load', initialize);
        function initialize() {
            var input = document.getElementById('google-autocomplete');
            var autocomplete = new google.maps.places.Autocomplete(input);
            autocomplete.addListener('place_changed', function () {
                var place           = autocomplete.getPlace();
                var google_address  = get_address_google(place, 'client');
            });
        }

        var user_id     = "<?php echo $user_id; ?>";
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
            previewContainer.innerHTML = ''; // Clear previous previews
            const formData = new FormData();
            for (const file of files) {
                const preview = document.createElement('img');
                preview.classList.add('preview');

                // Display file preview (for images)
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        preview.src = e.target.result;
                    };
                    reader.readAsDataURL(file);
                } else {
                    // For non-images, you can handle differently (e.g., display file name)
                    preview.src = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAOEAAADhCAMAAAAJbSJIAAAAdVBMVEX///8AAAD6+vptbW2jo6NGRkbo6Ojs7OxhYWFTU1Pb29vPz8+rq6vh4eGenp4PDw+WlpbV1dU3Nzf09PSPj4/BwcFzc3NpaWl6enqwsLCHh4fHx8cwMDCUlJS5ublPT0+Dg4M/Pz9bW1s0NDQeHh4kJCQXFxdqsaCFAAAGsUlEQVR4nO2d63qiMBBAAe+IF4ooivXS1r7/I+4qCUhIQGNmInTOr91ObXI+MJlcII5DEARBEAQMQ58xG0a262Kew9gVOM+7pDkX9TKWtutlCv8iF/x/HbtxGQOV35Wh7doZoF8n6Loz2/V7mbRe0HXbfqNGTYLu0XYVX+SrUNkE03B2xQ/j+V3nsbVdx5eY5h47Twh95iErNTPFN7eIq7GYx+b49TJGWiPoOKsOXESey2zk4T0Lt7hT5H3hVB5O2284r78Nw84YLhThaWcMB4pwdwzHinB3DBVNaYcMe4pwqwy9tL9Kxsfz/sZPmv10mRmsFR8SDLerMp+MZZk+Z3s4HOLpNPQRhifRzi0T6xj2XG2gE7/qGFDLcK1v6H6PIAUl0xQKw+mNlNfGoKHr+nCCslE8M2QjJN7SeCzKR4RGDeHmQ0ay0pjhSm7IRxqC4eY1w4U4AjXFkZcwXgbXlu0Ga912csNUbuhPHyaND4dt0O8vl6ukqACMIK/lRXaTCIZ80mYqfPbF/nD4Ifxds0yyP36WBtldynMafkOH7P/GevxjXSVexGeVlH8HBMMZ+2V+uY0Z8ts/bP7Vp0myP32QRz/LXxAfytDZwn0TszzkVxFl/SEfPU3BDJ1yQSbJDL8UUWEELM5aGDScABuqhkd8nsYr/1eR07zCF7ChKvHkU6IsiTkLzZJBwzGwYaKI8sbzlsWM+Ex+Pm1j0HADbHhShd2cwTn/Zz4g5oYGxgU9YMOdKiyOG2/kqYdBwzWw4UoV9iSCRcNr0DABNlRvOQirhsWcQ4sMayYRKqPHu8zKoOHJnqF4Fe+HIB0xdLyk8EtKGbpBw50lQ9YCRctbUjXpC9N+HTA8F+2mbHjVAcOJ+12Xrxg0XAEb9hXRaz68U89Ic0MDc9a2DLNMNFFdx7T9hnx+UBHugOEazfDTkiHvCRVztXF9+BlsGZ7qG8uDOcMlsGGgiPLBk2JJgS/pGKiHLUO+d00xj9mwGeUZbBmy2UTVdPuq/Yb8Iinip/Ybsqlo1SwHW/O4GKjH3JIhT8v28vBvFlXNJz+DLcN8D7S0qeFzjScD9bBl6PCVPelmbp4PmNgGbc0w38SQVmN8JcrIArw1w2KrfkUjYt9CM9WyZugUczRCZucvai7v89gzvHveYlFM00Rx8TSCmaVpe4Z5ds1anF5vM9mXfmRm217fnmHTPqC6jz6BTcP7x2aqnAzVw6ph3VVULlo9i13DPD2toNjCoYFlQyc6yfzGBndM2jZ0nNF8L/idjO4ltG/4nygNVqtkPBmvT8ut6d1Lb2EIChnqQ4ZYkKE+ZIgFGepDhliQoT5/0tAb4VBaV0U1zDcGA1MafZEhGZIhGXbM0PFwKJVJPb4+ZIgFGepDhliQoT5kiAUZ6kOGWKAaRsl48nX++Lgs6vm57D9KnDMGjOONiYpjaS8VqmF5jxAcpW0OqIYNr0YmwxYY1r6/uxOGyv1BnTFMB8r2zyQDe22pFchQn79iqHo6Dw8y1IcMsSBDfcgQCzLU510MoZ8KKhvaWD9ENbSyBkyGZEiGZNgxw+73FlaANrR/6hYZ6kOGWJChPmSIBRnqQ4ZYkKE+EsNo3segfDQ7qmEnR09kSIZk+GaGw8kYg0npFVrUH+pDhliQoT5/xVB9zgwWZKgPGWJBhvq8iyH0G8s/Af7yc5ChPhLDF0ZP+vWA/h5iGkaj2cwPw/B6snMaX8923gZBAH2GZel0QDDDKFgP6j/fcsMHnsVpt2Hvgc+32rDhpdkZJs4geMDQG2qjLkd6oKnIQv15o4YQPPa0GMjp8TiGsgNbq8DUAsfwoYc2VUdnv0hmaOzV6goeaWYUZ9m8DI7hsVlwYeB4Oik4hudmQ6PvP7/nXQyNHHQiBcdQfGN9BYiunoFj+NMgCNmW4xgu6gXXkGWjGDZ0+BPQwjPDE2gZDYZmDhtSYt/wx8AZn3XYN4Tq6TkohlGNIFhPz7FtaPqskyqWDWPYgq/YNcR4l4NVQ5TlkswwgS1EYQhcKsOiYQ+2UA6K4UgmCJurFVgzBM7VClAMh1XBC3CuVmDJ8Bc6VytAMayuExg8da8JO4bgyegdPYyG2xcEfdjiymyyMkFWDHIEQ9jCRPiSCeh9E5YEEbLtEvzsacg7p2SI/ua0fO15vAq22ziO07Bgpr+UeMfo/o2oFjZ6fleackjAVyolSJNGKHCGEyJiWw4I6NRvDZK0EQagNdAHiBo28hjCzi3K8IPk6wwo93Nc9zFTtToiz/Oi4cx/nVtXM4rQhkkEQRAE8ef4B4bzeu12dFKWAAAAAElFTkSuQmCC'; // Placeholder for non-images
                }

                previewContainer.appendChild(preview);
                formData.append('files[]', file, file.name);
            }
            uploadFile(formData);
        }
        function uploadFile(formData){
			var csrfToken = $('meta[name="csrf-token"]').attr('content');
			formData.append('_token', csrfToken);
            $.ajax({
                url:'{{route('users.file.store',$user_id)}}',
                type:"POST",
                data:formData,
                contentType:false,
                processData:false,
                beforeSend:function () {
                    showHideLoader('visible');
                },
                success:function (response) {

                    showHideLoader('hidden');
					load_gallary();
                }
            });
        }
        function load_gallary() {
			var csrfToken = $('meta[name="csrf-token"]').attr('content');
            $.ajax({
                url:'{{route('users.all_files',$user_id)}}',
                type:"POST",
                data:{html:true,_token:csrfToken},
                success:function (items) {
                    $(".mediabox").html(items);
					$("img.preview").remove();
					selected_images();
                }
            })
		}

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

		var projectClientId = `{{ isset($project->client_data->user_id) ? $project->client_data->user_id : ''}}`;
    </script>
@endpush
