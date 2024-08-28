@extends('layouts.main')
@php 
	$show_everything = (isset($_GET['display']) && ($_GET['display'] == 'all')) ? 1 : 0;
@endphp
@section('page-title')
	{{ __('Project Progress') }}
@endsection
@section('title')
	{{ __('Project Progress') }} ({{ $project->title }})
@endsection
@push('css')
	<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
	<link rel="stylesheet" href="{{ asset('assets/css/plugins/datatable/dataTables.dataTables.css') }}">
	<link href="{{  asset('assets/js/plugins/summernote-0.8.18-dist/summernote-bs4.css')  }}" rel="stylesheet">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
	<link rel="stylesheet" href="{{ asset('Modules/Taskly/Resources/assets/css/custom.css') }}" type="text/css" />
@endpush
@section('page-breadcrumb')
<a href="{{route('projects.index')}}">{{ __('All Project') }}</a>,<a href="{{route('projects.show', [$project->id])}}">{{$project->name }}</a>
@endsection

@section('page-action')
	<a href="{{ route('projects.show', $project->id) }}" class="btn btn-sm btn-primary btn-icon m-1" data-bs-whatever="Create New Project" data-bs-toggle="tooltip" data-bs-original-title="Create"> 
		<i class="ti ti-arrow-back text-white"></i>Back to Project
	</a>
@endsection
@push('css')
	<style>
		
		#card2 {
            right: -61px;
            width: 48%;
        }

        #card1 {
            width: 48%;
        }

        #useradd-8 {
            display: flex;
        }

        #progressdropBox {
            width: 100%;
            height: 100px !important;
            border: 2px dashed #ccc !important;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            cursor: pointer;
        }

        #progressdropBox:hover {
            border-color: #4CAF50 !important;
        }

		.item-signature .progress_files{
			margin-top: 10px !important;
		}

        .progressfileInput {
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
		/* The container */
		.checkbox-btn .container {
			display: block;
			position: relative;
			margin-bottom: 25px !important;
			cursor: pointer;
			font-size: 22px;
			-webkit-user-select: none;
			-moz-user-select: none;
			-ms-user-select: none;
			user-select: none;
		}

		/* Hide the browser's default checkbox */
		.checkbox-btn .container input {
		position: absolute;
		opacity: 0;
		cursor: pointer;
		height: 0;
		width: 0;
		}

		/* Create a custom checkbox */
		.checkmark {
            position: absolute;
            top: 0;
            left: 0;
            height: 15px;
            width: 15px;
            background-color: rgba(var(--bs-danger-rgb), var(--bs-bg-opacity)) !important;
		}
        .header_buttons .checkmark {
            background-color: #0427e9 !important;
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
		.selected_image .actionbuttons .bg-danger, .default_file .header_buttons .action-btn{
			visibility: visible;
		}

		.progress-step {
			position: relative !important;
		}

        .flex-div span {
            display: flex;
        }

		#progress-table tr.group, #progress-table tr.group:hover {
			background-color: rgba(0, 0, 0, 0.1) !important;
		}
        .construction_detail_address span, .client_invoice_address span, .address-class span { 
            display : block;
        }

		.CellWithComment{
			position:relative;
		}
		.CellWithComment i{
            color: #333333 !important;
        }
		.CellWithComment:hover span.CellComment{
			display:block;
		}
		.CellWithComment .CellComment{
			top: 33px !important;
		}
		.CellWithComment .CellComment:before{
			margin-top: -28px !important;
			margin-right: 28px !important;
		}
		.progress-signature .sign_btn_block{
			display: flex;
    		justify-content: space-between;
		}

		.progress-signature .sign_btn_block_small {
			display: flex;
			gap: 1px;
		}
		.progress-signature .progress_final_clear_sig, .progress-signature .progress_final_clear_sig:hover {
			color: #333 !important;
			background: none !important;
			border: none !important;
			margin-top: 1px !important;
		}
		.item-signature .progress_amount {
			border: 1px solid #d8d8d8 !important;
			padding: 10px !important;
			background: #ffffff !important;
			margin-top: 5px !important;
		}
		.progress_files_row .progress_mediaimg{
			padding: 10px !important;
		}
		.progress_files_row .lightbox-link{
			margin: 0 auto !important;
			display: block !important;
		}
		.progress_files_row .mediabox .mediainfo{
			text-align: center !important;
		}
		.progress_files_row .preview{
			margin: 10px !important;
		}
		.media-body a .fileprev{
            margin: 0 auto !important;
        }
		.progress_files_row #progress_bulk_delete_form .btn-primary{
			background: #48494B !important;
			padding: 5px 10px !important;
			color: #fff !important;
		}
		.progress_files_row #progress_bulk_delete_form .btn-primary i{
            color: #fff !important;
        }		
	</style>
@endpush
@push('scripts')
	<script src="{{ asset('Modules/Taskly/Resources/assets/libs/select2/dist/js/select2.min.js')}}"></script>
	<script src="{{ asset('assets/js/plugins/datatable/dataTables.js') }}"></script>
	<script src="{{ asset('assets/js/plugins/datatable/intl.js') }}"></script>
	<script src="{{ asset('assets/js/plugins/signature_pad/signature_pad.min.js') }}"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/exif-js/2.3.0/exif.min.js"></script>
	<script src="{{ asset('Modules/Taskly/Resources/assets/js/custom.js')}}"></script>
@endpush
@section('content')
<div class="row">
	<div class="col-sm-12">
		<div class="card">
			<div class="card-header card-body table-border-style">
				<div class="card-body table-responsive" id="progress-div">
				{{ Form::open(['route' => ['progress.sign.store'], 'enctype' => 'multipart/form-data', 'class' => 'project-progress-form']) }}
				<h2>{{ __('Project Progress') }}</h2>
				<h6></h6>
					<table class="table w-100 table-hover table-bordered" id="progress-table">
						<thead>
							<th data-dt-order="disable">&nbsp;</th>
							<th data-dt-order="disable">{{ __('POS') }}</th>
							<th data-dt-order="disable">{{ __('Group') }}</th>
							<th data-dt-order="disable">{{ __('Name') }}</th>
							<th data-dt-order="disable">{{ __('Signature') }}</th>
							<th data-dt-order="disable">{{ __('Description') }}</th>
							<th data-dt-order="disable">{{ __('Quantity') }}</th>
							@if ($show_everything == 1)
								<th data-dt-order="disable">{{ __('Single Price') }}</th>
								<th data-dt-order="disable">{{ __('Total Price') }}</th>
								<th data-dt-order="disable">{{ __('Progress') }}</th>
								<th data-dt-order="disable">{{ __('Remaining') }}</th>
							@endif
							<th data-dt-order="disable">{{ __('History') }}</th>
						</thead>
					</table>
					<div class="progress-footer">
						<div class="float-end confirm_div_top" data-id="">
							<div class="progress-text">
								<label><input type="checkbox" class="form-check-input" name="progress_final_confirm_checkbox" id="progress_final_confirm_checkbox" /> {{ __('I confirm the Progress above') }}.</label><br>
								<a href="javascript:void(0);" class="progress-final-comment-icon"><small>({{ __('add Comments') }})</small></a>
								<div class="progress-final-comment d-none"><textarea name="progress_final_comment" id="progress_final_comment" placeholder="{{ __('Comments...') }}"></textarea></div>
							</div>
							<div class="progress-date">
								<input type="text" id="progress-date-time-picker" name="progress-date-time" value="{{ \Carbon\Carbon::now('Europe/Berlin')->format('d.m.Y - H:i') }}" readonly>
							</div>
							<div class="progress-client">
								<input type="text" name="progress_final_user_name" id="progress_final_user_name" placeholder="{{ __('Name') }}" value="{{isset(\Auth::user()->name) ? \Auth::user()->name : '' }}">
							</div>
							<div class="progress-signature">
								<canvas id="signature-pad" class="signature-pad progress-final-signature" height="100" width="300"></canvas>
								<div class="sign_btn_block">
									<div class="sign_btn_block_small">
										<button type="button" class="btn btn-sm btn-danger progress_final_clear_sig" id="progress_final_clear_sig"><i class="fa-regular fa-trash-can"></i></button>
									</div>
								</div>
								<input type="hidden" id="progress_final_signature" name="progress_final_signature" value="" />
							</div>
						</div>
						<div class="confirm_div" data-id="{{ isset($active_estimation->id) ? $active_estimation->id : 0}}">
							<a href="javascript:void(0)" class="confirm-progress-btn btn btn-sm btn-primary btn-icon m-1" data-id="{{ isset($active_estimation->id) ? $active_estimation->id : 0}}">
								<span class="text-white">
									{{ __('Confirm Progress') }}
								</span>
							</a>
						</div>
					</div>
					{{ Form::close() }}
				</div>
			</div>
		</div>
	</div>
</div>
@endsection
@push("scripts")
<script>
	var csrfToken = $('meta[name="csrf-token"]').attr('content');
		var show_everything = '{{$show_everything}}';
		var signaturePad = {};
		var active_estimation_id = '{{ isset($active_estimation->id) ? $active_estimation->id : 0}}';
		let moneyFormat = '{{$site_money_format}}';
		var moneyFormatter = site_money_format(moneyFormat);
		var project_id = '{{\Crypt::encrypt($project->id)}}';
		$(document).ready(function() {
			getItems(active_estimation_id);

			$(document).on("click", ".btn_sign_here", function(e) {
				e.preventDefault();
				var item_id = $(this).data('id');
				var data = signaturePad[item_id].toDataURL('image/png');
				$('#SignupImage1').val(data);

				var form = $(this).closest("form");
				let estimation_id = $(this).closest("form").find("input[name='estimation_id']").val();
				$.ajax({
					url: "{{ route('progress.sign.store') }}",
					type: 'POST',
					data: form.serialize(),
					beforeSend: function() {
						$("#addSig").attr("disabled", true);
						$("#loader").removeClass("hidden");
					},
					success: function(data) {
						$("#addSign").attr("disabled", false);
						$("#loader").addClass("hidden");
						$("#exampleModal").modal("hide");
						$(".modal-backdrop").remove();
						$("body").css("overflow", "scroll");
						toastrs("success", "Progress Approved.");
						getItems(estimation_id);
						$("body").css("overflow", "auto");
					},
					error: function(data) {
						$("#addSign").prop("disabled", false);
						$("#loader").addClass("hidden");
					}
				});
			});

			$(document).on("click", ".clearSig", function() {
				var item_id = $(this).data('id');
				signaturePad[item_id].clear();
			});

			$(document).on("click", ".progress_final_clear_sig", function() {
				this.canvas = document.querySelector(".progress-final-signature");
				var final_signature = new SignaturePad(this.canvas);
				final_signature.clear();
				$("#progress_final_signature").val('');
			});

			$(document).on("click", ".items-signature-pad", function() {
				$(this).parents('td').find('.sign_btn_block').removeClass('d-none');
			});

			$(document).on("input change", ".progress", function() {
				var item_id = $(this).data('id');
				var scrolled_value = $(this).val();
				var limiting_value = $(this).data('min');
				if (scrolled_value < limiting_value) {
					$(this).val(limiting_value);
				} else {
					$('#slider-value-' + item_id).text(scrolled_value + "%")
					$('#progress-slider-' + item_id).val(scrolled_value);
					$('#item' + item_id).removeClass("d-none");
				}
			});

			$(document).on('click', '.commentSig', function() {
				var id = $(this).data('id');
				$('.comment_text[data-id="' + id + '"]').toggleClass('d-none');
			});
			// Toggle Progress amount
			$(document).on('click', '.quantitySig', function() {
				var id = $(this).data('id');
				$('.progress_amount[data-id="' + id + '"]').toggleClass('d-none');
			});
			// Toggle Upload files
			$(document).on('click', '.uploadSig', function() {
				var id = $(this).data('id');
				// $('.progress-files-group-'+ id).toggleClass('d-none');
				$('.progress_files[data-id="' + id + '"]').toggleClass('d-none');
			});
			// Toggle Final Comment
			$('a.progress-final-comment-icon').on('click', function() {
				$('.progress-final-comment').toggleClass('d-none');
			});
		});

        function getItems(estimation_id) {
			if ( $.fn.DataTable.isDataTable('#progress-table') ) {
				$('#progress-table').DataTable().destroy();
			}
			$('#progress-table tbody').empty();
			$('#progress-table colgroup').empty();

			var columns_data = [
				{ "data": "product_id", "className": "product-id", "orderable": false },
				{ "data": "pos", "className": "position", "orderable": false },
				{ "data": "group", "className": "group", "orderable": false },
				{ "data": "name", "className": "name", "orderable": false },
				{ "data": "item_signature", "className": "item-signature", "orderable": false },
				{ "data": "description", "className": "description", "orderable": false },
				{ "data": "quantity", "className": "quantity", "orderable": false },
				{ "data": "history", "className": "history", "orderable": false },
			];
			if (show_everything == 1) {
				columns_data = [
					{ "data": "product_id", "className": "product-id", "orderable": false },
					{ "data": "pos", "className": "position", "orderable": false },
					{ "data": "group", "className": "group", "orderable": false },
					{ "data": "name", "className": "name", "orderable": false },
					{ "data": "item_signature", "className": "item-signature", "orderable": false },
					{ "data": "description", "className": "description", "orderable": false },
					{ "data": "quantity", "className": "quantity", "orderable": false },
					{ "data": "price", "className": "price", "orderable": false, "render": DataTable.render.intlNumber('de', { style: 'currency', currency: 'EUR' }) },
					{ "data": "totalPrice", "className": "total-price", "orderable": false, "render": DataTable.render.intlNumber('de', { style: 'currency', currency: 'EUR' }) },
					{ "data": "progress_amount", "className": "progress-amount", "orderable": false, "render": DataTable.render.intlNumber('de', { style: 'currency', currency: 'EUR' }) },
					{ "data": "progress_remaining", "className": "progress-remaining", "orderable": false, "render": DataTable.render.intlNumber('de', { style: 'currency', currency: 'EUR' }) },
					{ "data": "history", "className": "history", "orderable": false },
				];
			}
            var csrfToken = $('meta[name="csrf-token"]').attr('content');
            var groupColumn = 2;
            $('#progress-table').DataTable({
                "lengthMenu": [[10, 25, 50, 100, 200, -1], [10, 25, 50, 100, 200, "All"]],
                'pageLength': 200,
                'dom': 'lrt',
                "bPaginate": false,
                "bFilter": false,
                "bInfo": false,
                "destroy": true,
                "processing": true,
                "serverSide": true,
                'order': [[1, 'ASC']],
				"bSort": false,
                "ajax": {
                    "url": "{{route('progress.estimation.item')}}",
                    "type": "POST",
                    data:{estimation_id:estimation_id,html:true,_token:csrfToken},
                },
                "columns": columns_data,
                initComplete: function (settings, json) {
                    setTimeout(function(){
						init_signature();
						confirm_signature();
					}, 500);
                },
                "columnDefs": [{ visible: false, targets: groupColumn }],
                "drawCallback": function (res) {
                    var api = this.api();
                    var rows = api.rows({ page: 'current' }).nodes();
                    var last = null;
                    var aData = [];
					var col_span = (show_everything == 1) ? 11 : 7;
					api.column(groupColumn, {page:'current'} ).data().each( function ( group, i ) {

						var vals = api.row(api.row($(rows).eq(i)).index()).data();
						/*** add progress files rows after each row ***/
						$(rows).eq(i).after('<tr class="group progress_files_row progress-files-group-'+vals.progress_item_id+' "><td colspan="'+col_span+'">'+vals.item_files+'</td></tr>');

						var totalPrice = vals['totalPrice'] ? parseFloat(vals['totalPrice']) : 0;
						if (typeof aData[group] == 'undefined') {
							aData[group] = new Array();
							aData[group].rows = [];
							aData[group].totalPrice = [];
						}
						aData[group].rows.push(i); 
						aData[group].totalPrice.push(totalPrice); 
					});
					var idx= 0;
					for(var group in aData){
						idx =  Math.min.apply(Math,aData[group].rows);
						var sum = 0; 
						$.each(aData[group].totalPrice,function(k,v){
							sum = sum + v;
						});
						

						// $(rows).eq(idx).before('<tr class="group progress-group"><td colspan="10"><b>'+group+'</b></td><td class="text-right" colspan="2"><b>'+moneyFormatter.format(parseFloat(sum))+'</b></td></tr>');
                        $(rows).eq(idx).before('<tr class="group progress-group"><td colspan="'+col_span+'">'+group+'</td></tr>');
					};
                },
            });
        }

		function init_signature() {
			if($("#progress-div").length > 0) {
				$('.signature-pad').each(function() {
					var item_id = $(this).data('id');
					if (item_id > 0) {
						var signature = {
							canvas: null,
							clearButton: null,
							init: function init() {
								this.canvas = document.querySelector("#items-signature-pad-" + item_id);
								signaturePad[item_id] = new SignaturePad(this.canvas,
									{
										onEnd : function() {
											$("#SignupImage" + item_id).val(signaturePad[item_id].toDataURL());
										}
									}
								);
							}
						};
						signature.init();
					}
				});
			}
		}

		function confirm_signature() {
			var signature = {
				canvas: null,
				clearButton: null,
				init: function init() {
					this.canvas = document.querySelector(".progress-final-signature");
					var final_signature = new SignaturePad(this.canvas,
						{
							onEnd : function() {
								$("#progress_final_signature").val(final_signature.toDataURL());
							}
						}
					);
				}
			};
			signature.init();
		}

		$(document).ready(function() {

			$(document).on("click", "#progressdropBox", function(e) {
				e.preventDefault();
				var item_id = $(this).data('id');
				$("#progressfileInput"+item_id).trigger('click');
			});

			var isSubmitable = true;
			$(document).on("change", ".progress_amount", function(e) {
				e.preventDefault();
				isSubmitable = true;
				var item_id = $(this).data('id');
				$('#error-message-'+item_id).remove();
				var progress_amount = parseFloat($('#progress_amount_'+item_id).val());
				var	progress_max_qty = parseFloat($('#progress_amount_'+item_id).attr('max'));
				var	progress_min_qty = parseFloat($('#progress_amount_'+item_id).attr('min'));
				if (!$('#progress_amount_'+item_id).hasClass('d-none')) {
					if(progress_amount < progress_min_qty){
						if(progress_min_qty == 0){
							$('#progress_amount_'+item_id).addClass("error");
							$('#progress_amount_'+item_id).after("<span class='error-message' id='error-message-"+item_id+"'>{{ __('Please enter amount is greater than 0.') }}</span>");
							isSubmitable = false;
							return false;
						} else {
							$('#progress_amount_'+item_id).addClass("error");
							$('#progress_amount_'+item_id).after("<span class='error-message' id='error-message-"+item_id+"'>{{ __('Please enter amount is greater than of previous amount.') }}</span>");
							isSubmitable = false;
							return false;
						}
					} else if ((progress_amount > progress_max_qty)) {
						$('#progress_amount_'+item_id).addClass("error");
						$('#progress_amount_'+item_id).after("<span class='error-message' id='error-message-"+item_id+"'>{{ __('Please enter amount is less than or equal to of quantity.') }}</span>");
						isSubmitable = false;
						return false;
					}
				}
			});

			$(document).on("click", ".confirm-progress-btn", function() {
				var form_data = {};
				$('.signature-pad').each(function() {
					var item_id = $(this).data('id');
					if (item_id > 0) {
						var comment = '';
						var progress_bar = '';
						var signature = '';
						var progress_amount = '';
						var progress_total_qty = '';
						var progress_old_qty = '';
						comment = $('#comment-'+item_id).val();
						progress_bar = $('#progress-slider-'+item_id).val();
						signature = $('#SignupImage'+item_id).val();
						progress_amount = parseFloat($('#progress_amount_'+item_id).val());
						progress_total_qty = parseFloat($('#progress_amount_'+item_id).attr('max'));
						progress_old_qty = parseFloat($('#progress_amount_'+item_id).attr('min'));
						if(signature != "" && signature != null){
							form_data[item_id] = {progress: progress_bar, signature: signature, comment: comment, progress_amount: progress_amount, progress_old_qty: progress_old_qty, progress_total_qty: progress_total_qty};
						}
					}
				});
				var estimation_id = $(this).data('id');
				var project_id = '{{\Crypt::encrypt($project->id)}}';
				var user_id = '{{\Crypt::encrypt(\Auth::user()->id)}}';
				var confirm_signature = "";
				var confirm_user_name = "";
				var confirm_comment = "";
				confirm_signature = $('#progress_final_signature').val();
				confirm_user_name = $('#progress_final_user_name').val();
				confirm_comment = $('#progress_final_comment').val();
				if($("#progress_final_confirm_checkbox").prop('checked') == true){
					if(confirm_signature != "" && confirm_signature != null){
						if(isSubmitable) {
							if(form_data != "" && form_data != null){
								$.ajax({
									url: "{{ route('progress.sign.store') }}",
									type: 'POST',
									data: { _token: csrfToken, estimation_id: estimation_id, confirm_signature: confirm_signature, confirm_user_name: confirm_user_name, confirm_comment: confirm_comment, formdata: form_data, project_id: project_id, user_id: user_id },
									beforeSend: function() {
										showHideLoader('visible');
									},
									success: function(response) {
										showHideLoader('hidden');
										$('#progress_final_confirm_checkbox').prop('checked', false);
										$('#progress_final_comment').val('');
										$('#progress_final_user_name').val('');
										if (response.status == true){
											toastrs('Success', response.message, 'success');
											getItems(estimation_id);
										} else {
											toastrs('Error', response.message, 'error');
										}
									},
									error: function(data) {
										showHideLoader('hidden');
										$('#progress_final_confirm_checkbox').prop('checked', false);
										$('#progress_final_comment').val('');
										$('#progress_final_user_name').val('');
									}
								});
							}
						}else{
							toastrs('Error', "{{ __('Please fill the all details properly.') }}", 'error');
						}
					}else{
						toastrs('Error', "{{ __('Please do confirmation signature.') }}", 'error');
					}
				}else{
					toastrs('Error', "{{ __('Please check the confirmation checkbox.') }}", 'error');
				}
			});

			/*** delete selected progress files ****/
			$(document).on("submit", "#progress_bulk_delete_form", function(e) {
				e.preventDefault();
				const formData = new FormData(this);
				var estimation_id = $('input[name="estimation_id"]').val();
				$.ajax({
					url:"{{route('progress.files.delete')}}",
					type:"POST",
					data:formData,
					contentType:false,
					processData:false,
					beforeSend:function () {
						showHideLoader('visible');
					},
					success:function (response) {
						if(response.status == true){
							showHideLoader('hidden');
							toastrs('Success', response.message, 'success')
							getItems(estimation_id);
						} else {
							toastrs('Error', response.message)
						}
					}
				});
			});

		});

		function handleProgressDragOver(event) {
            event.preventDefault();
            event.dataTransfer.dropEffect = 'copy';
            document.getElementById('progressdropBox').style.border = '2px dashed #4CAF50';
        }

        function handleProgressDrop(event, item) {
            event.preventDefault();
            document.getElementById('progressdropBox').style.border = '2px dashed #ccc';
            const files = event.dataTransfer.files;
            handleProgressFiles(files, item);
        }

		function handleProgressFileSelect(event, item) {
            const files = event.target.files;
            handleProgressFiles(files, item);
        }

		/*** selected progress files preview ****/
		function handleProgressFiles(files, item) {
			var product_id = $(item).data('id');
			var estimation_id = $(item).data('estimationid');
			const ProgressFilesPreviewContainer = document.getElementById('ProgressFilesPreviewContainer'+product_id);
            ProgressFilesPreviewContainer.innerHTML = '';
            let formData = new FormData();
            let counter = 0;
			formData.append('estimation_id', estimation_id);
			formData.append('product_id', product_id);
			formData.append('description', '');
            Array.from(files).forEach((file) => {
                if (!file.type.startsWith('image/')) {
                    formData.append('files[]', file, file.name);
                    counter++;
                    if (counter === files.length) {
                        uploadProgressFile(formData, estimation_id, product_id);
                    }
                } else {
                    const reader = new FileReader();
                    reader.onload = function(event) {
                        const img = new Image();
                        img.src = event.target.result;
                        img.onload = function() {
                            EXIF.getData(img, function() {
                                const dateTaken = EXIF.getTag(this, 'DateTimeOriginal');
                                const canvas = document.createElement('canvas');
                                const ctx = canvas.getContext('2d');
                                const max_width = 1500;
                                const scaleFactor = max_width / img.width;
                                canvas.width = max_width;
                                canvas.height = img.height * scaleFactor;
                                ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                                function drawTextWithBackground(ctx, text, x, y, bgColor, textColor, padding) {
                                    ctx.fillStyle = bgColor;
                                    ctx.font = 'bold 20px Arial';
                                    const textMetrics = ctx.measureText(text);
                                    const textWidth = textMetrics.width;
                                    const textHeight = 20;
                                    const backgroundX = canvas.width - textWidth - padding.leftRight - x;
                                    const backgroundY = canvas.height - textHeight - padding.topBottom - y;
                                    const backgroundWidth = textWidth + padding.leftRight * 2;
                                    const backgroundHeight = textHeight + padding.topBottom * 2;
                                    ctx.fillRect(backgroundX, backgroundY, backgroundWidth, backgroundHeight);
                                    ctx.fillStyle = textColor;
                                    ctx.fillText(text, backgroundX + padding.leftRight, backgroundY + textHeight);
                                }
                                const dateText = dateTaken ? formatDate(dateTaken) : '';
                                drawTextWithBackground(ctx, dateText, 10, 10, '#ee232ac2', '#FFF', { topBottom: 2, leftRight: 5 });
                                ctx.canvas.toBlob(function(blob) {
                                    const compressedFile = new File([blob], file.name, {
                                        type: 'image/jpeg',
                                        lastModified: Date.now(),
                                    });
                                    formData.append('files[]', compressedFile, compressedFile.name);
                                    const preview = document.createElement('img');
                                    preview.src = URL.createObjectURL(compressedFile);
                                    preview.classList.add('preview');
                                    preview.classList.add('img-thumbnail');
                                    ProgressFilesPreviewContainer.appendChild(preview);
                                    counter++;
                                    if (counter === files.length) {
                                        uploadProgressFile(formData, estimation_id, product_id);
                                    }
                                }, 'image/jpeg', 0.85);
                            });
                        };
                    };
                    reader.readAsDataURL(file);
                }
            });
        }

		/*** upload progress files****/
		function uploadProgressFile(formData, estimation_id, product_id) {
            $.ajax({
                url: "{{ route('progress.file.store') }}",
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                beforeSend: function() {
                    showHideLoader('visible');
                },
                success: function(response) {
                    showHideLoader('hidden');
					if (response.status == true){
						// toastrs('Success', response.message, 'success');
						// getItems(estimation_id);
						$('.item_mediabox_'+product_id).append(response.html);
						$('#ProgressFilesPreviewContainer'+product_id).html('');
					} else {
						toastrs('Error', response.message, 'error');
					}
                },
                error: function(xhr, status, error) {
                    console.error('Upload failed:', error);
                    showHideLoader('hidden');
                }
            });
        }

		/*** select progress files for delete ****/
		function selected_progress_images(item) {
			var total_selected = 0;
			var files_ids = [];
			var product_id = $(item).data('item-id');
			$('.progress_image_selection').each(function () {
				var id = $(this).data('id');
				if ($(this).prop('checked')==true){
					total_selected++;
					var file_id = $(this).val();
					files_ids.push(file_id);
					$(".project_progress_file_"+id).parents('.progress_mediaimg').addClass('selected_image');
				} else {
					$(".project_progress_file_"+id).parents('.progress_mediaimg').removeClass('selected_image');
				}
			});
			if(total_selected > 0){
				$('.btn_progress_bulk_delete_files_'+product_id).removeClass('d-none');
			} else {
				$('.btn_progress_bulk_delete_files_'+product_id).addClass('d-none');
			}
			$('#remove_progress_files_ids'+product_id).val(JSON.stringify(files_ids));
		}
</script>
@endpush
