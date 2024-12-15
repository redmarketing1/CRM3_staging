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
		.select-payment-progress .select-payment-progress-inner {
			display: flex !important;
		}

		.select-payment-progress .select-payment-progress-inner .payment-slider-value {
			text-wrap: nowrap !important;
			font-weight: bold!important;
			font-size: 18px!important;
			min-width: 50px!important;
			text-align: right!important;
			margin-left: 5px !important;
		}

		.select-payment-progress-inner input[type='range'] {
			overflow: hidden!important;
			-webkit-appearance: none!important;
			background-color: #ececec!important;
			height: 30px!important;
		}

		.select-payment-progress-inner input[type='range']::-webkit-slider-runnable-track {
			height: 10px!important;
			-webkit-appearance: none!important;
			color: #FFBF00!important;
			margin-top: -1px!important;
		}

		.select-payment-progress-inner input[type='range']::-webkit-slider-thumb {
			width: 30px!important;
			-webkit-appearance: none!important;
			height: 40px!important;
			margin-top: -10px!important;
			cursor: ew-resize!important;
			background: #FFBF00!important;
			box-shadow: -180px 0 0 180px #FFAC1C!important;
			content: "+"!important;
		}

		.payment-progress{
			border-radius: 10px!important;
		}

		/* .finance-items{
			display: none!important;
		} */
		
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
		<div class="col-sm-12 progress-html">
			
		</div>
	</div>
@endsection

@push("scripts")
	<script>
		var csrfToken = $('meta[name="csrf-token"]').attr('content');
		var active_estimation_id = '{{ isset($active_estimation->id) ? $active_estimation->id : 0}}';
		var project_id = '{{ $project->id }}';
		let moneyFormat = '{{$site_money_format}}';
		var moneyFormatter = site_money_format(moneyFormat);
		var signaturePad = {};

		$(document).ready(function(){
			
			//Fetch Estimation Items
			getItems(active_estimation_id);

			//Not sure about use
			// $(document).on("click", ".btn_sign_here", function(e) {
			// 	e.preventDefault();
			// 	var item_id = $(this).data('id');
			// 	var data = signaturePad[item_id].toDataURL('image/png');
			// 	$('#SignupImage1').val(data);

			// 	var form = $(this).closest("form");
			// 	let estimation_id = $(this).closest("form").find("input[name='estimation_id']").val();
			// 	$.ajax({
			// 		url: "{{ route('progress.sign.store') }}",
			// 		type: 'POST',
			// 		data: form.serialize(),
			// 		beforeSend: function() {
			// 			$("#addSig").attr("disabled", true);
			// 			$("#loader").removeClass("hidden");
			// 		},
			// 		success: function(data) {
			// 			$("#addSign").attr("disabled", false);
			// 			$("#loader").addClass("hidden");
			// 			$("#exampleModal").modal("hide");
			// 			$(".modal-backdrop").remove();
			// 			$("body").css("overflow", "scroll");
			// 			toastrs("success", "Progress Approved.");
			// 			getItems(estimation_id);
			// 			$("body").css("overflow", "auto");
			// 		},
			// 		error: function(data) {
			// 			$("#addSign").prop("disabled", false);
			// 			$("#loader").addClass("hidden");
			// 		}
			// 	});
			// });

			$(document).on("click", ".clearSig", function(e) {
				e.preventDefault();
				var item_id = $(this).data('id');
				signaturePad[item_id].clear();
				return false;
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

			$(document).on("click", ".commentSig", function(e) {
				e.preventDefault();
				var id = $(this).data('id');
				$('.comment_text[data-id="' + id + '"]').toggleClass('d-none');
				return false;
			});
 
			$(document).on('click', '.quantitySig', function(e) {
				e.preventDefault();
				var id = $(this).data('id');  
				$('.progress_amount[data-id="' + id + '"]').toggleClass('d-none');
				return false;
			});

			$(document).on('click', '.uploadSig', function(e) {
				e.preventDefault();
				var id = $(this).data('id');
				$('.progress_files[data-id="' + id + '"]').toggleClass('d-none');
				return false;
			});
			// Toggle Final Comment
			$('a.progress-final-comment-icon').on('click', function() {
				$('.progress-final-comment').toggleClass('d-none');
			});
		});

		//Estimation Items Function
		function getItems(estimation_id) {
			$.ajax({
				url: "{{route('progress.estimation.progressitem')}}",
				type: 'POST',
				data:{estimation_id:estimation_id,project_id:project_id, html:true,_token:csrfToken},
				beforeSend: function() {
					$("#addSig").attr("disabled", true);
					$("#loader").removeClass("hidden");
				},
				success: function(data) {
					$("#addSign").attr("disabled", false);
					$("#loader").addClass("hidden");
					$('.progress-html').html(data);
					init_signature();
					confirm_signature();
					$('.finance-items').hide(); // Hide all matching elements

				},
				error: function(data) {
					$("#addSign").prop("disabled", false);
					$("#loader").addClass("hidden");
				}
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
						var payment_progress_bar = '';
						var signature = '';
						var progress_amount = '';
						var progress_total_qty = '';
						var progress_old_qty = '';
						comment = $('#comment-'+item_id).val();
						progress_bar = $('#progress-slider-'+item_id).val();
						payment_progress_bar = $('#payment-progress-slider-'+item_id).val();
						signature = $('#SignupImage'+item_id).val();
						progress_amount = parseFloat($('#progress_amount_'+item_id).val());
						progress_total_qty = parseFloat($('#progress_amount_'+item_id).attr('max'));
						progress_old_qty = parseFloat($('#progress_amount_'+item_id).attr('min'));
						if(signature != "" && signature != null){
							form_data[item_id] = {progress: progress_bar, payment_progress: payment_progress_bar, signature: signature, comment: comment, progress_amount: progress_amount, progress_old_qty: progress_old_qty, progress_total_qty: progress_total_qty};
						}
					}
				});
				var estimation_id = $(this).data('id');
				var project_id = '{{ $project->id }}';
				var user_id = '{{ Auth::user()->id }}';
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
										
										if (response.status == true){
											$('#progress_final_confirm_checkbox').prop('checked', false);
											$('#progress_final_comment').val('');
											$('#progress_final_user_name').val('');
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
										//$('#progress_final_user_name').val('');
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
			// $(document).on("submit", "#progress_bulk_delete_form", function(e) {
			// 	e.preventDefault();
			// 	const formData = new FormData(this);
			// 	var estimation_id = $('input[name="estimation_id"]').val();
			// 	$.ajax({
			// 		url:"{{route('progress.files.delete')}}",
			// 		type:"POST",
			// 		data:formData,
			// 		contentType:false,
			// 		processData:false,
			// 		beforeSend:function () {
			// 			showHideLoader('visible');
			// 		},
			// 		success:function (response) {
			// 			if(response.status == true){
			// 				showHideLoader('hidden');
			// 				toastrs('Success', response.message, 'success')
			// 				getItems(estimation_id);
			// 			} else {
			// 				toastrs('Error', response.message)
			// 			}
			// 		}
			// 	});
			// });

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
			let total_selected = 0;
			let files_ids = [];
			const product_id = $(item).data('item-id');

			$('.progress_image_selection').each(function () {
				const id = $(this).data('id');
				if ($(this).prop('checked')) {
					total_selected++;
					const file_id = $(this).val();
					files_ids.push(file_id);
					$(".project_progress_file_" + id).parents('.progress_mediaimg').addClass('selected_image');
				} else {
					$(".project_progress_file_" + id).parents('.progress_mediaimg').removeClass('selected_image');
				}
			});

			if (total_selected > 0) {
				$('.btn_progress_bulk_delete_files_' + product_id).removeClass('d-none').data('file-ids', JSON.stringify(files_ids));
			} else {
				$('.btn_progress_bulk_delete_files_' + product_id).addClass('d-none').removeData('file-ids');
			}
		}

		//delete files
		function triggerDeleteFiles(button) {
			const product_id = $(button).data('product-id');
			const estimation_id = $(button).data('estimation-id');
			const files_ids = $(button).data('file-ids');

			if (!files_ids || files_ids.length === 0) {
				toastrs('Error', 'No files selected for deletion.', 'error');
				return;
			}

			$.ajax({
				url: "{{ route('progress.files.delete') }}",
				type: "POST",
				data: {
					_token: "{{ csrf_token() }}",
					remove_progress_files_ids: files_ids,
					estimation_id: estimation_id,
				},
				beforeSend: function () {
					showHideLoader('visible');
				},
				success: function (response) {
					showHideLoader('hidden');
					if (response.status) {
						getItems(estimation_id);
						toastrs('Success', response.message, 'success');
					} else {
						toastrs('Error', response.message, 'error');
					}
				},
				error: function () {
					showHideLoader('hidden');
					toastrs('Error', 'An unexpected error occurred.', 'error');
				},
			});
		}

		// New JS for Payment Progress
		$(document).ready(function () {
			
			$(document).on('change', 'input#show-payment-progress', function () {
				if ($(this).is(':checked')) {
					$('.finance-items').show(); // Show all matching elements
				} else {
					$('.finance-items').hide(); // Hide all matching elements
				}
			});

            $(document).on("input change", ".payment-progress", function() {
				var item_id = $(this).data('id');
				var scrolled_value = $(this).val();
				var limiting_value = $(this).data('min');
				if (scrolled_value < limiting_value) {
					$(this).val(limiting_value);
				} else {
					$('#payment-slider-value-' + item_id).text(scrolled_value + "%")
					$('#payment-progress-slider-' + item_id).val(scrolled_value);
				}
			});

		});

	</script>
@endpush