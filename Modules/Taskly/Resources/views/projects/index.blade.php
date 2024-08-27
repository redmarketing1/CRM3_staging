@extends('layouts.main')
@section('page-title')
    {{__('Manage Projects')}}
@endsection
@section('page-breadcrumb')
   {{__('Manage Projects')}}
@endsection
@push('css')
	<link rel="stylesheet" href="{{ asset('assets/css/plugins/datatables.min.css') }}">
	<link href="{{ asset('assets/css/plugins/select2.min.css') }}" rel="stylesheet" />
	<link href="{{ asset('assets/css/plugins/daterangepicker.css') }}" rel="stylesheet"/>
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

		.range_slider .price-input {
			width: 100%;
			display: flex;
			margin: 30px 0 35px;
		}

		.range_slider .price-input .field {
			display: flex;
			width: 100%;
			height: 45px;
			align-items: center;
		}

		.range_slider .field input {
			width: 100%;
			height: 100%;
			outline: none;
			font-size: 19px;
			margin-left: 12px;
			border-radius: 5px;
			text-align: center;
			border: 1px solid #999;
			-moz-appearance: textfield;
		}

		.range_slider input[type="number"]::-webkit-outer-spin-button,
		.range_slider input[type="number"]::-webkit-inner-spin-button {
			-webkit-appearance: none;
		}

		.range_slider .price-input .separator {
			width: 130px;
			display: flex;
			font-size: 19px;
			align-items: center;
			justify-content: center;
		}

		.range_slider .slider_filter {
			height: 5px;
			position: relative;
			background: #ddd;
			border-radius: 5px;
		}

		.range_slider .slider_filter .progress {
			height: 100%;
			left: 0%;
			width: 50% !important;
			position: absolute;
			border-radius: 5px;
			background: #17a2b8;
		}

		.range_slider .slider_filter .progress2 {
			height: 100%;
			left: 0%;
			width: 50% !important;
			position: absolute;
			border-radius: 5px;
			background: #17a2b8;
		}

		.range_slider .range-input {
			position: relative;
		}

		.range_slider .range-input input {
			position: absolute;
			width: 100%;
			height: 5px;
			top: -5px;
			background: none;
			pointer-events: none;
			-webkit-appearance: none;
			-moz-appearance: none;
		}

		.range_slider input[type="range"]::-webkit-slider-thumb {
			height: 17px;
			width: 17px;
			border-radius: 50%;
			background: #17a2b8;
			pointer-events: auto;
			-webkit-appearance: none;
			box-shadow: 0 0 6px rgba(0, 0, 0, 0.05);
		}

		.range_slider input[type="range"]::-moz-range-thumb {
			height: 17px;
			width: 17px;
			border: none;
			border-radius: 50%;
			background: #17a2b8;
			pointer-events: auto;
			-moz-appearance: none;
			box-shadow: 0 0 6px rgba(0, 0, 0, 0.05);
		}
		#pc-dt-simple-project_wrapper .form-check {
			text-align : center;
			padding-left: 0;
		}
		#pc-dt-simple-project_wrapper .action_btn {
			align-item : center;
			margin-bottom : 10px !important;
		}
		#pc-dt-simple-project_wrapper .check_all {
			padding : 0 20px;
		}
		#pc-dt-simple-project_wrapper .button_action_block {
			display : inline;
		}
		#pc-dt-simple-project_wrapper .action_projects {
			margin-right : 10px;
		}
		.additional_filters .select2-container {
			width : 100% !important;
		}
		.project-list .select_project {
			scale : 3;
		}
	</style>
@endpush
@section('page-action')
<div>
	<a href="javascript:void(0)" class="toggle_filter btn btn-sm btn-primary btn-icon" title="{{ __('Show / Hide Filters') }}">
		<span class=""><i class="fa fa-filter"></i><i class="fa fa-arrow-down arrow_icon"></i></span>
	</a>
	@permission('project manage')
		<a href="{{ route('projects.map') }}" class="btn btn-sm btn-primary mx-1" data-toggle="tooltip" title="{{ __('Project Map') }}">
			<i class="fa-solid fa-map"></i>
		</a>
	@endpermission
    @permission('project import')
        <a href="#"  class="btn btn-sm btn-primary" data-ajax-popup="true" data-title="{{__('Project Import')}}" data-url="{{ route('project.file.import') }}"  data-toggle="tooltip" title="{{ __('Import') }}"><i class="ti ti-file-import"></i> </a>
    @endpermission
    <a href="{{ route('projects.grid') }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip"title="{{ __('Grid View') }}">
        <i class="ti ti-layout-grid text-white"></i>
    </a>

    @permission('project create')
        <a class="btn btn-sm btn-primary" data-ajax-popup="true" data-size="md"
            data-title="{{ __('Create New Project') }}" data-url="{{ route('projects.create') }}" data-toggle="tooltip"
            title="{{ __('Create') }}">
            <i class="ti ti-plus"></i>
        </a>
    @endpermission
</div>
@endsection
@section('filter')
@endsection

@section('content')
@php
    //$profile = \App\Models\Utility::get_file('uploads/avatar/');
	$projectmaxprice = (isset($projectmaxprice) && $projectmaxprice > 100) ? $projectmaxprice : 100;
	$half_price = 50;
	if($projectmaxprice > 100){
		$half_price = $projectmaxprice/2;
	}
@endphp
<div class="row">

    <div id="multiCollapseExample1 ">
        <div class="card d-none">
            <div class="card-body">
                {{ Form::open(['route' => ['projects.index'], 'method' => 'GET', 'id' => 'project_submit']) }}
                <div class="row d-flex align-items-center justify-content-end">
                    <div class="col-xl-2 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                        <div class="btn-box">
                            {{ Form::label('start_date', __('Start Date'), ['class' => 'form-label']) }}
                            {{ Form::date('start_date', isset($_GET['start_date']) ? $_GET['start_date'] : null, ['class' => 'form-control ','placeholder' => 'Select Date']) }}

                        </div>
                    </div>
                    <div class="col-xl-2 col-lg-3 col-md-6 col-sm-12 col-12">
                        <div class="btn-box">
                            {{ Form::label('end_date', __('End Date'), ['class' => 'form-label']) }}
                            {{ Form::date('end_date', isset($_GET['end_date']) ? $_GET['end_date'] : null, ['class' => 'form-control ','placeholder' => 'Select Date']) }}

                        </div>
                    </div>
                    <div class="col-auto float-end ms-2 mt-4">

                        <a href="#" class="btn btn-sm btn-primary"
                            onclick="document.getElementById('project_submit').submit(); return false;"
                            data-bs-toggle="tooltip" title="{{ __('Apply') }}"
                            data-original-title="{{ __('apply') }}">
                            <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                        </a>
                        <a href="{{ route('projects.index') }}" class="btn btn-sm btn-danger" data-toggle="tooltip"
                            data-original-title="{{ __('Reset') }}">
                            <span class="btn-inner--icon"><i class="ti ti-trash-off text-white-off"></i></span>
                        </a>
                    </div>
                </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>

	<div class="nav nav-tabs project_tabs" id="status-tabs" role="tablist">
    	<a class="nav-item nav-link active project_status_link" id="all-tab" data-toggle="tab" href="#all" role="tab" aria-controls="all" aria-selected="true">{{ __('All') }}</a>
		@if (isset($project_dropdown['project_status']) && !empty($project_dropdown['project_status']))
			@foreach($project_dropdown['project_status'] as $status)
			@php
				$project_count = '';
				$project_count = $all_projects->where('status_data.name', $status->name)->count();
			@endphp
				<a class="nav-item nav-link project_status_link" id="status-tab-{{ $status->id }}" data-toggle="tab" href="#status-{{ $status->id }}" role="tab" aria-controls="status-{{ $status->id }}" aria-selected="false" style="background-color:{{ isset($status->background_color) ? $status->background_color : ''; }}; color:{{ isset($status->font_color) ? $status->font_color : ''; }};">{{ __($status->name) }}
				<span class="project_item_count" style="background-color:{{ isset($status->background_color) ? $status->background_color : ''; }}; color:{{ isset($status->font_color) ? $status->font_color : ''; }};">{{ $project_count }}</span>
				</a>

			@endforeach
		@endif
	</div>

    <div class="col-xl-12">
            <div class="card">
                <div class="card-body table-border-style">
					<div class="row mb-2 additional_filters">
						<label class="pb-3">Additional Filters </label>
						<div class="col-sm-3 form-group">
							<select name="country" class="form-control filter_select2" data-placeholder="{{ __('Select Country') }}" >
								<option value="" data-iso="">{{ __('Select Country') }}</option>
								@foreach ($countries as $country)
									<option value="{{$country->id}}" data-iso="{{ $country->iso }}"> {{ __($country->name) }}</option>
								@endforeach
							</select>
						</div>
						<div class="col-sm-3 form-group">
							<select name="state" id="" class="form-control">
								<option value="">{{ __('Select State') }}</option>
								@foreach ($state as $state_row)
									<option value="{{$state_row}}"> {{$state_row}}</option>
								@endforeach
							</select>
						</div>
						<div class="col-sm-3 form-group">
							<select name="city" id="" class="form-control">
								<option value="">{{ __('Select City') }}</option>
								@foreach ($city as $city_row)
									<option value="{{$city_row}}"> {{$city_row}}</option>
								@endforeach
							</select>
						</div>
						<div class="col-sm-3 form-group">
							<select class="form-control" name="project_type">
								<option value="not_archieve" selected="">{{ __('Not archive projects') }}</option>
								<option value="archieve">{{ __('Archive Projects') }}</option>
							</select>
						</div>
					</div>
					<input type="hidden" name="project_table_filter" id="project_table_filter" value="">
                    <div class="table-responsive">
						<table class="table project-list" id="pc-dt-simple-project">
							<thead>
								<tr class="form-group">
									<th></th>
									<th></th>
									<th>
										<select name="filter_project_status" id="filter_project_status" class="form-control filter_select2 form_filter_field" multiple data-placeholder="{{ __('Status') }}">
											<option value="">{{ __('Status') }}</option>
											@if (isset($project_dropdown['project_status']))
												@foreach($project_dropdown['project_status'] as $project_status)
													<option value="{{ $project_status->id }}" title="{{ $project_status->code }}">{{ $project_status->name }}</option>
												@endforeach
											@endif
										</select>
									</th>
									<th style="" class="range_slider">
										<input type="text" class="form-control form_filter_field" placeholder="{{ __('Name') }}" name="filter_name" id="filter_name">
									</th>
									<th><input type="text" class="form-control form_filter_field" placeholder="{{ __('Comments') }}" name="filter_comment" id="filter_comment"></th>
									<th >
										<select name="filter_priority" id="filter_priority" class="form-control filter_select2 form_filter_field" multiple data-placeholder="{{ __('Priority') }}">
											<option value="">{{ __('Priority') }}</option>
											@if (isset($project_dropdown['priority']))
												@foreach($project_dropdown['priority'] as $priority)
													<option value="{{ $priority->id }}">{{ $priority->name }}</option>
												@endforeach
											@endif
										</select>
									</th>
									
									<th>
										<select name="filter_construction_type" id="filter_construction_type" class="form-control filter_select2 form_filter_field" multiple data-placeholder="{{ __('Construction') }}">
											<option value="">{{ __('Construction') }}</option>
											@if (isset($project_dropdown['construction_type']))
												@foreach($project_dropdown['construction_type'] as $construction_types)
													<option value="{{ $construction_types->id }}" title="{{ $construction_types->code }}">{{ $construction_types->name }}</option>
												@endforeach
											@endif
										</select>
									</th>
									<th class="range_slider">
										<div class="price-input price_input">
											<div class="field">
												<input type="number" id="filter_price_from" class="input-min form_filter_field" value="0">
											</div>
											<div class="separator">-</div>
											<div class="field">
												<input type="number" id="filter_price_to" class="input-max form_filter_field" value="{{ $half_price }}">
											</div>
										</div>
										<div class="slider_filter price_slider">
											<div class="progress2"></div>
										</div>
										<div class="range-input price_range_input">
											<input type="range" class="range-min" min="0" max="{{ $projectmaxprice }}" value="0" step="10" onmouseup="set_filter_values()">
											<input type="range" class="range-max" min="0" max="{{ $projectmaxprice }}" value="{{ $half_price }}" onmouseup="set_filter_values()" step="10">
										</div>
									</th>
									<th >
										<input type='text' class="form-control daterange form_filter_field" placeholder="{{ __('Date') }}" id="filter_date" name="filter_date"/>
									</th>
									<th>
										<select name="filter_users" id="filter_users" class="form-control filter_select2 form_filter_field" multiple data-placeholder="{{ __('Users') }}">
											<option value="">{{ __('Users') }}</option>
											@foreach($projectUser as $key => $p_user)
												<option value="{{$p_user->id}}">{{$p_user->name}}</option>
											@endforeach
										</select>
									</th>
								</tr>
								<tr>
								<th scope="col" class="sort check_all" data-sort="">
									<div class="form-check">
										<input type="checkbox" class="select_all_projects form-check-input" value="all">
									</div>
								</th>
								<th scope="col" class="sort project-image" data-sort="image">{{ __('Image') }}</th>
								<th scope="col" class="sort project-status" data-sort="status">{{ __('Status') }}</th>
								<th scope="col" class="sort project-name" data-sort="name">{{ __('Name') }}</th>
								<th scope="col" class="sort project-comments" data-sort="comment">{{ __('Comments') }}</th>
								<th scope="col" class="sort project-priority" data-sort="priority">{{ __('Priority') }}</th>
								<th scope="col" class="sort project-construction-type" data-sort="construction_type">{{ __('Construction') }}</th>
								<th scope="col" class="sort project-budget" data-sort="budget">{{ __('Project Net') }}</th>
								<th scope="col" class="sort project-created-at" data-sort="created_at">{{ __('Date') }}</th>
								<th scope="col" class="text-right project-action">{{ __('Action') }}</th>
								</tr>
							</thead>
							<tbody>
								
							</tbody>
						</table>
					</div>
                </div>
            </div>
    </div>
</div>

@endsection
@push('scripts')
	<script src="{{ asset('assets/js/plugins/datatables.min.js') }}"></script>
	<script src="{{ asset('assets/js/plugins/select2.min.js') }}"></script>
	<script src="{{ asset('assets/js/plugins/moment-with-locales.min.js') }}"></script>
	<script src="{{ asset('assets/js/plugins/daterangepicker.js') }}"></script>
	<script>
		showHideLoader('visible');

		var filter_data = {};

		$(document).ready(function () {
			const rangeInput = document.querySelectorAll(".range-input input"),
				priceInput = document.querySelectorAll(".price-input input"),
				range = document.querySelector(".slider_filter .progress");
			let priceGap = 1;

			priceInput.forEach((input) => {
				input.addEventListener("input", (e) => {
					let minPrice = parseInt(priceInput[0].value),
					maxPrice = parseInt(priceInput[1].value);

					if (maxPrice - minPrice >= priceGap && maxPrice <= rangeInput[1].max) {
					if (e.target.className === "input-min") {
						rangeInput[0].value = minPrice;
						range.style.left = (minPrice / rangeInput[0].max) * 100 + "%";
					} else {
						rangeInput[1].value = maxPrice;
						range.style.right = 100 - (maxPrice / rangeInput[1].max) * 100 + "%";
					}
					}
				});
			});
			rangeInput.forEach((input) => {
				input.addEventListener("input", (e) => {
					let minVal = parseInt(rangeInput[0].value),
					maxVal = parseInt(rangeInput[1].value);

					if (maxVal - minVal < priceGap) {
						if (e.target.className === "range-min") {
							rangeInput[0].value = maxVal - priceGap;
						} else {
							rangeInput[1].value = minVal + priceGap;
						}
					} else {
						priceInput[0].value = minVal;
						priceInput[1].value = maxVal;
						range.style.left = (minVal / rangeInput[0].max) * 100 + "%";
						//range.style.width = 100 - (maxVal / rangeInput[1].max) * 100 + "%";
						width = maxVal - minVal + "%";
						range.style.setProperty("width", width, "important");
					}
				});
			});

			const rangeInput2 = document.querySelectorAll(".price_range_input input"),
				priceInput2 = document.querySelectorAll(".price_input input"),
				range2 = document.querySelector(".price_slider .progress2");
			let priceGap2 = 1;

			priceInput2.forEach((input2) => {
				input2.addEventListener("input", (e) => {
					let minPrice = parseInt(priceInput2[0].value),
					maxPrice = parseInt(priceInput2[1].value);

					if (maxPrice - minPrice >= priceGap2 && maxPrice <= rangeInput2[1].max) {
					if (e.target.className === "input-min") {
						rangeInput2[0].value = minPrice;
						range2.style.left = (minPrice / rangeInput2[0].max) * 100 + "%";
					} else {
						rangeInput2[1].value = maxPrice;
						range2.style.right = 100 - (maxPrice / rangeInput2[1].max) * 100 + "%";
					}
					}
				});
			});
			rangeInput2.forEach((input2) => {
				input2.addEventListener("input", (e) => {
					let minVal = parseInt(rangeInput2[0].value),
					maxVal = parseInt(rangeInput2[1].value);
					var max = 100;
					if (maxVal - minVal < priceGap2) {
						if (e.target.className === "range-min") {
							rangeInput2[0].value = maxVal - priceGap2;
						} else {
							rangeInput2[1].value = minVal + priceGap2;
						}
					} else {
						max = e.target.max;
						priceInput2[0].value = minVal;
						priceInput2[1].value = maxVal;
						range2.style.left = (minVal / rangeInput2[0].max) * 100 + "%";
						//range.style.width = 100 - (maxVal / rangeInput[1].max) * 100 + "%";
						current_width = maxVal - minVal;
						width = current_width / max * 100;
						new_width = width + "%";
						range2.style.setProperty("width", new_width, "important");
					}
				});
			});

			$('.toggle_filter').on('click', function() {
			//	$('.additional_filters').toggleClass('d-none');
				$('.additional_filters').slideToggle();

				$('.toggle_filter .arrow_icon').toggleClass('fa-arrow-up').toggleClass('fa-arrow-down');

				// $('.show_filter').toggleClass('d-none');
				// $('.hide_filter').toggleClass('d-none');
			});

			$('.daterange').on('apply.daterangepicker', function (ev, picker) {
				if (picker.chosenLabel == "Today") {
					$(this).val('Today');
				} else if (picker.chosenLabel == "Yesterday") {
					$(this).val('Yesterday');
				} else if (picker.chosenLabel == "This Week") {
					$(this).val('This Week');
				} else if (picker.chosenLabel == "This Month") {
					$(this).val('This Month');
				} else {
					$(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY'));
				}
				set_filter_values();
			});

			$('.daterange').on('cancel.daterangepicker', function(ev, picker) {
				$(this).val('');
				set_filter_values();
			});

			setTimeout(function () {
				// init_tom_select();
				init_select2();

				$('.daterange').daterangepicker({
					autoUpdateInput: false,
					locale: {
						cancelLabel: 'Clear'
					},
					ranges: {
						'Today': [moment(), moment()],
						'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
						'This Week': [moment().subtract(6, 'days'), moment()],
						'This Month': [moment().startOf('month'), moment().endOf('month')],
						'Last 30 Days': [moment().subtract(29, 'days'), moment()],
						'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
					}
				});
			},500);
			
			load_projects();

			$('.additional_filters').css('display','none');

			$(document).on('change', '.form_filter_field', function (e) {
				e.preventDefault();
				set_filter_values();
			});

			$(document).on('click', '.action_projects', function (e) {
				e.preventDefault();

				var type = $(this).data('type')

				var project_ids = [];
				$('.select_project').each(function () {

					if ($(this).prop('checked')==true){
						project_ids.push($(this).val());
					}
				});

				if (project_ids.length > 0) {
					const swalWithBootstrapButtons = Swal.mixin({
						customClass: {
							confirmButton: 'btn btn-success',
							cancelButton: 'btn btn-danger'
						},
						buttonsStyling: false
					});

					swalWithBootstrapButtons.fire({
						title: 'Are you sure?',
						text: "This action can not be undone. Do you want to continue?",
						icon: 'warning',
						showCancelButton: true,
						confirmButtonText: 'Yes',
						cancelButtonText: 'No',
						reverseButtons: true
					}).then((result)=>{
						if (result.isConfirmed) {
							$.ajax({
								url: '{{route('projects.bulk_action')}}',
								type: "POST",
								data: {
									project_ids: project_ids,
									type : type
								},
								success: function (response) {
									if (response.is_success) {
										toastrs("Success", response.message, 'success');	
										load_projects();
									} else {
										toastrs('Error', '{{ __('Some Thing Is Wrong!') }}', 'error');
									}
								},
								error: function(data) {
									toastrs('Error', '{{ __('Some Thing Is Wrong!') }}', 'error');
								}
							})
						}
					});
				}
			});

			$(document).on('change', '.select_project', function() {
				set_buld_delete_button();

			});
			$('.select_all_projects').on('change', function() {
				if ($(this).is(':checked')) {
					$('.select_project').prop('checked', true);
				} else {
					$('.select_project').prop('checked', false);
				}

				set_buld_delete_button();
			});

			$(document).on('change', '.additional_filters select', function() {
				set_filter_values();
			});

			$('#status-tabs a').on('click', function(e) {
				e.preventDefault();
				$(this).tab('show');

				// Filter projects based on selected status
				var statusId = $(this).attr('href').replace('#status-', '');
				if(statusId === '#all') {
					// Show all projects if "All" tab is selected
					$('.project-row').show();
				} else {
					// Show only projects with the corresponding status
					$('.project-row').hide();
					$('.project-row[data-status="' + statusId + '"]').show();
				}
			});

		});

		function init_tom_select() {
			$(".filter_select2").each(function() {

				new TomSelect(this, {
					plugins: ['remove_button'],
				});
			});
		}

		function set_buld_delete_button() {
			var is_checked = false;
			$('.select_project').each(function () {
				if ($(this).prop('checked')==true){
					is_checked = true;	
					return false;
				}
			});

			if (is_checked == true) {
				$('.button_action_block').removeClass('d-none');
			} else {
				$('.button_action_block').addClass('d-none');
			}
		}

		function set_filter_values() {
			var date_range 	= get_dates_from_date_range_picker();
			var from_date 	= "";
			var to_date 	= "";
			if (date_range != "") {
				date_range 	= date_range.split('-');
				from_date 	= date_range[0];
				to_date 	= date_range[1];
			}

			filter_data['name'] = $("#filter_name").val();
			filter_data['status'] = $("#filter_project_status").val();
			// filter_data['label'] = $("#filter_project_label").val();
			filter_data['priority'] = $("#filter_priority").val();
			filter_data['progress_from'] = $("#filter_progress_from").val();
			filter_data['progress_to'] = $("#filter_progress_to").val();
			filter_data['construction'] = $("#filter_construction_type").val();
			filter_data['property'] = $("#filter_property_type").val();
			filter_data['price_from'] = $("#filter_price_from").val();
			filter_data['price_to'] =  $("#filter_price_to").val();
			filter_data['date_from'] = from_date;
			filter_data['date_to'] = to_date;
			filter_data['comment'] = $("#filter_comment").val();
			filter_data['users'] =  $("#filter_users").val();
			filter_data['project_type'] =  $(".additional_filters select[name='project_type']").val();
			filter_data['country'] 		=  $(".additional_filters select[name='country']").val();
			filter_data['city'] 		=  $(".additional_filters select[name='city']").val();
			filter_data['state'] 		=  $(".additional_filters select[name='state']").val();
			$('#project_table_filter').val(JSON.stringify(filter_data));
			load_projects();
		}

		function changeStatus(project_id, column, e) {
			let status = $(e).val();
			$.ajax({
				url: '{{route('project.custom-status')}}',
				type: "POST",
				data: {
					project_id: project_id,
					column: column,
					status: status
				},
				success: function (response) {
					let status = column.replace("_", " ");
					let message = response.message.replace("{$status}", status);
					toastrs("Success", message, 'success');

					load_projects();
				}
			})
		}

		function init_select2() {
			setTimeout(function(){
				$('.filter_select2').select2({
					placeholder: "Select",
					tags: true,
					templateSelection: function (data, container) {
						$(container).css("background-color", data.title);
						if (data.element) {
							$(container).css("color", $(data.element).attr("font_color"));
						}
						return data.text;
					}
				});
			}, 500);
		}

		function load_projects() {
			if ($("#pc-dt-simple-project").length > 0) {

				showHideLoader('visible');

				var project_table_filter = $('#project_table_filter').val();
				$('#pc-dt-simple-project').DataTable({
					"language": {
						"url": datatable_language_path
					},
					"lengthMenu": [[10, 25, 50, 100, 200, -1], [10, 25, 50, 100, 200, "All"]],
					'pageLength': 25,
					"destroy": true,
					"processing": true,
					"serverSide": true,
					'order': [[7, 'DESC']],
					"ajax": {
						"url": '{{ route('project.all_data') }}',
						"type": "POST",
					//	"processData": false,
						data :  { project_table_filter }
					},

					initComplete: function (settings, json) {
						init_select2();
					},
					"dom": "<'row'<'col-lg-2 col-md-2 col-xs-12'l><'col-lg-10 col-md-10 col-xs-12'f>>" +
					"<'row'<'col-sm-12'tr>>" +
					"<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",

					'rowCallback': function(row, data, index) {
						var priority_color = data.priority_color;
						if (priority_color != '') {
							$(row).find('td:eq(5)').addClass('bg_color_' + priority_color.replace('#', ''));
							$(row).find('td:eq(5) select').css('background-color', priority_color);
							if(data.priority_font_color){
								$(row).find('td:eq(5) select').css('color', data.priority_font_color);
							}
						}

						var status_color = data.status_color;
						if (status_color != '') {
							$(row).addClass('bg_color_' + status_color.replace('#', ''));
							$(row).find('td:eq(2)').addClass('bg_color_' + status_color.replace('#', ''));
							$(row).find('td:eq(2) select').css('background-color', status_color);
							if(data.status_font_color){
								$(row).find('td:eq(2) select').css('color', data.status_font_color);
							}
						}

						$(row).addClass('project-row');
						$(row).attr('data-status',data.project_status);
					},
					"columns": [
						{ "data": "checkbox", "orderable": false, "className": "check_all" },
						{ "data": "image", "orderable": false, "className": "project-image" },
						{ "data": "status", "className": "project-status" },
						{ "data": "name", "className": "project-name" },
						{ "data": "comments", "orderable": false, "className": "project-comments" },
						{ "data": "priority", "className": "project-priority" },
						{ "data": "construction", "className": "project-construction-type" },
						// { "data": "property", "className": "project-property-type" },
						// { "data": "label", "className": "project-label" },
						{ "data": "project_net", "className": "project-budget" },
						{ "data": "date", "className": "project-created-at" },
						//{ "data": "users", "orderable": false, "className": "project-users" },
						{ "data": "action", "orderable": false, "className": "project-action text-end" },
					],
					"columnDefs": [
						{
							'targets': 3,
							'createdCell':  function (td, cellData, rowData, row, col) {
							$(td).attr('nowrap', ''); 
							}
						},
						{
							'targets': 4,
							'createdCell':  function (td, cellData, rowData, row, col) {
							$(td).attr('nowrap', ''); 
							}
						}
					],
					'fnDrawCallback': function (oSettings) {
						showHideLoader('hidden');

						$('.dataTables_filter').each(function () {
							var delete_button_html = '<div class="button_action_block d-none">';
							if (filter_data['project_type'] == 'archieve') {
								delete_button_html += '<a href="javascript:void(0)" class="btn btn-sm btn-primary action_projects" data-type="remove_archieve"><i class="ti ti-archive text-white"></i> Remove from Archive</a>';
							} else {
								delete_button_html += '<a href="javascript:void(0)" class="btn btn-sm btn-primary action_projects" data-type="archieve"><i class="ti ti-archive text-white"></i> Move to Archive</a>';
							}							
							delete_button_html += '<a href="javascript:void(0)" class="btn btn-sm btn-primary action_projects" data-type="delete"><i class="ti ti-trash text-white"></i> Delete Projects</a>';
							delete_button_html += '</div>';

							$(this).prepend(delete_button_html);

							var not_archieve_selected 	= "selected";
							var archive_selected 		= "";

							if (filter_data['project_type'] == 'archieve') {
								not_archieve_selected 	= "";
								archive_selected 		= "selected";
							}

						});
					}
				});				
			}
		}

		function changeStatus(project_id, column, e) {
            let status = $(e).val();
            $.ajax({
                url: '{{route('project.custom-status')}}',
                type: "POST",
                data: {
                    project_id: project_id,
                    column: column,
                    status: status
                },
                success: function (response) {
                    let status = column.replace("_", " ");
                    let message = response.message.replace("{$status}", status);
                    toastrs("Success", message, 'success');

					load_projects();
                }
            })
        }

		function get_dates_from_date_range_picker() {
			if ($('.daterange').length > 0) {
				var from_date = "";
				var to_date = "";
				var date_range = $('.daterange').val();
				if (date_range != "") {
					if (date_range == "Today" || date_range == "Yesterday" || date_range == "This Week" || date_range == "This Month") {
						if (date_range == "Today") {
							from_date = moment().format('MM/DD/YYYY');
							to_date = moment().format('MM/DD/YYYY');
						}
						if (date_range == "Yesterday") {
							from_date = moment().subtract(1, 'days').format('MM/DD/YYYY');
							to_date = moment().subtract(1, 'days').format('MM/DD/YYYY');
						}
						if (date_range == "This Week") {
							from_date = moment().subtract(6, 'days').format('MM/DD/YYYY');
							to_date = moment().format('MM/DD/YYYY');
						}
						if (date_range == "This Month") {
							from_date = moment().startOf('month').format('MM/DD/YYYY');
							to_date = moment().endOf('month').format('MM/DD/YYYY');
						}
					} else {
						var date_range_res = date_range.split('-');
						from_date = date_range_res[0].replace(/\s/g, '');
						if (from_date != "") {
							var f_splt = from_date.split('/');
							from_date = f_splt[1] + '/' + f_splt[0] + '/' + f_splt[2];
						}
						to_date = date_range_res[1].replace(/\s/g, '');
						if (to_date != "") {
							var t_splt = to_date.split('/');
							to_date = t_splt[1] + '/' + t_splt[0] + '/' + t_splt[2];
						}
					}
				}
				if (from_date != "" && to_date != "") {
					return from_date + "-" + to_date;
				} else {
					return '';
				}
			}
		}
	</script>
@endpush
