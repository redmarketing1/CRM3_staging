@extends('layouts.main')
@section('page-title')
	{{ __('Smart Template') }}
@endsection
@section('page-breadcrumb')
    {{ __('Smart Template') }}
@endsection
@push('css')
	<link rel="stylesheet" href="{{ asset('Modules/Taskly/Resources/assets/css/datatables.min.css') }}" type="text/css" />
	<link rel="stylesheet" href="{{ asset('Modules/Taskly/Resources/assets/css/custom.css') }}" type="text/css" />
@endpush

@section('page-action')
	<div class="">
		<a href="{{ route('smart-template.setup') }}" class="btn btn-sm btn-primary btn-icon m-1"
           data-bs-whatever="{{ __('Create New Smart Template') }}" data-bs-toggle="tooltip"
           data-bs-original-title="{{ __('Create New Smart Template') }}"> <i class="ti ti-plus text-white"></i></a>
	</div>
@endsection

@section('content')
	<div class="row">
		<div class="col-xl-12">
			<div class="card">
				<div class="card-header card-body table-border-style">
					<div class="table-responsive">
						<table class="table mb-0 pc-dt-simple" id="template-table">
							<thead>
								<tr>
									<th scope="col" class="sort template-name" data-sort="name">{{ __('Name') }}</th>
									<th scope="col" class="sort template-type" data-sort="comment">{{ __('Type') }}</th>
									<th scope="col" class="sort template-ai-model" data-sort="ai-model">{{ __('AI Model') }}</th>
									<th scope="col" class="sort template-created-at" data-sort="created_at">{{ __('Date') }}</th>
									<th scope="col" class="text-right template-action">{{ __('Action') }}</th>
								</tr>
							</thead>
							<tbody>
								@if(isset($smart_templates) && count($smart_templates) > 0)
									@foreach($smart_templates as $smart_template)
										<tr role="row" class="odd">
											<td class=" template-name"><a href="{{ route('smart-template.edit',  Crypt::encrypt($smart_template->id)) }}">{{ $smart_template->title }}</a></td>
											<td class="template-type">{{ ($smart_template->type == "0") ? __('Main Response') : __('Number') }}</td>
											<td class="template-ai-model">{{ isset($smart_template->ai_model->model_label) ? $smart_template->ai_model->model_label : '' }}</td>
											<td class="template-created-at sorting_1">{{ company_datetime_formate($smart_template->created_at) }}</td>
											<td class="project-action">
												<div class="row icons-div">
													<div class="col-sm-12">
														<div class="action-btn btn-primary ms-2">
															<a href="{{ route('smart-template.edit',  Crypt::encrypt($smart_template->id)) }}" class="action-btn btn-info mx-1  btn btn-sm d-inline-flex align-items-center" title="Edit" aria-label="Edit" data-bs-original-title="Edit"><i class="ti ti-edit text-white"></i></a>
														</div>
														{{ Form::open(['route' => ['smart-templates.destroy', Crypt::encrypt($smart_template->id)], 'class' => 'd-inline-flex']) }}
															@method('DELETE')
															<a href="#" class="action-btn btn-danger mx-1  btn btn-sm d-inline-flex align-items-center bs-pass-para show_confirm" data-confirm="Are You Sure?" data-text="This action can not be undone. Do you want to continue?" data-confirm-yes="delete-form2-1" data-toggle="tooltip" title="" data-bs-original-title="Delete">
																<i class="ti ti-trash"></i>
															</a>
														{{ Form::close() }}
													</div>
												</div>
											</td>
										</tr>
									@endforeach
								@endif
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection
@push('scripts')
	<script src="{{ asset('Modules/Taskly/Resources/assets/js/datatables.min.js')}}"></script>
	<script type="text/javascript">
		$(document).ready(function () {
		//	load_templates();

		});

		function load_templates() {
			if ($("#template-table").length > 0) {
				$('#template-table').DataTable({
					"language": {
						"url": datatable_language_path
					},
					"lengthMenu": [[10, 25, 50, 100, 200, -1], [10, 25, 50, 100, 200, "All"]],
					'pageLength': 200,
					"destroy": true,
					"processing": true,
					"serverSide": true,
					'order': [[3, 'DESC']],
					"ajax": {
						"url": '{{ route('smart-template.all_data') }}',
						"type": "POST"
					},
					initComplete: function (settings, json) {
						// init_select2();
    				},
					"dom": "<'row'<'col-lg-2 col-md-2 col-xs-12'l><'col-lg-10 col-md-10 col-xs-12'f>>" +
					"<'row'<'col-sm-12'tr>>" +
					"<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
					'rowCallback': function(row, data, index) {
					},
					"columns": [
						{ "data": "name", "className": "template-name" },
						{ "data": "type", "className": "template-type" },
						{ "data": "ai_model", "className": "template-ai-model" },
						{ "data": "date", "className": "template-created-at" },
						{ "data": "action", "orderable": false, "className": "project-action" },
					],
					'fnDrawCallback': function (oSettings) {
						
					}
				});				
			}
		}
    </script>

@endpush