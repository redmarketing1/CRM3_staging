@extends('layouts.main')
@section('page-title')
    {{ __('Estimation') }}
@endsection
@section('page-breadcrumb')
    {{ __('Estimation') }}
@endsection
@push('css')

	<link rel="stylesheet" href="{{ asset('Modules/Taskly/Resources/assets/css/datatables.min.css') }}" type="text/css" />
	<link rel="stylesheet" href="{{ asset('Modules/Taskly/Resources/assets/libs/select2/dist/css/select2.min.css') }}" type="text/css" />
	<link rel="stylesheet" href="{{ asset('Modules/Taskly/Resources/assets/css/custom.css') }}" type="text/css" />
@endpush
@section('content')
	<div class="row">
		<div class="col-sm-12">
			<div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table mb-0 pc-dt-simple" id="estimation_list">
                            <thead>
                                <tr>
                                    <th class="status">{{ __('Status') }}</th>
									<th class="project_title">{{ __('Project Title') }}</th>
									<th class="title">{{ __('Title') }}</th>
									<th class="net_inc_discount">{{ __('Net incl. Discount') }}</th>
									<th class="gross_inc_discount">{{ __('Gross incl. Discount') }}</th>
									<th class="discount">{{ __('Discount %') }}</th>
									<th class="issue_date">{{ __('Issue Date') }}</th>
									<th class="action">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
								@foreach ($estimations as $e_key => $estimation)
								<tr>
									@php
										$setup_url = route('estimations.setup.estimate',\Crypt::encrypt($estimation->id));
										if(!\Auth::user()->isAbleTo('estimation edit')) {
											$setup_url = 'javascript:void(0)';
										}
										$project_url = route('projects.show',[$estimation->project_id]);
										if(!\Auth::user()->isAbleTo('project manage')) {
											$project_url = 'javascript:void(0)';
										}
									@endphp
									<td class="est-title">
										<span class="badge fix_badges bg-{{$statuesColor[$estimationStatus[$estimation->status]]}} p-2 px-3 rounded">{{$estimationStatus[$estimation->status]}}</span>
									</td>
									<td class="est-title">
										<a href="{{ $project_url }}">{{ $estimation->getProjectDetail->name }}</a>
									</td>
									<td class="est-title">
										@if($estimation->status > 1)
											<a href="{{route("estimations.finalize.estimate",encrypt($estimation->id))}}">
												{{$estimation->title}}
											</a>
										@else
											<a href="{{ $setup_url }}">
												{{$estimation->title}}
											</a>
										@endif
									</td>
									<td class="text-right">
										{{ currency_format_with_sym((isset($estimation->final_quote->net_with_discount) ? $estimation->final_quote->net_with_discount : 0)) }}
									</td>
									<td class="text-right">
										{{ currency_format_with_sym((isset($estimation->final_quote->gross_with_discount) ? $estimation->final_quote->gross_with_discount : 0)) }}
									</td>
									<td class="text-right">
										{{ currency_format_with_sym((isset($estimation->final_quote->discount) ? $estimation->final_quote->discount : 0),'','',false) }} %
									</td>
									<td class="text-right">
										{{ company_date_formate($estimation->issue_date) }}

									</td>
									<td class="actions">
										@if (\Auth::user()->type == 'company')
											<div class="row icons-div">
												<div class="col-sm-12">
													@permission('estimation copy')
													<div class="action-btn btn-primary ms-2">
														<a class="action-btn btn-info mx-1  btn btn-sm d-inline-flex align-items-center"
															data-ajax-popup="true" data-size="lg"
															data-title="{{ __('Create New Item') }}"
															data-url="{{route('estimations.copy',$estimation->id)}}"
															data-toggle="tooltip" title="{{ __('Duplicate Estimation') }}"><i
																class="ti ti-copy text-white"></i></a>
													</div>
													@endpermission
													@permission('estimation delete')
													<form id="delete-form2-{{ $estimation->id }}" action="{{ route('estimations.deleteEstimation', [$estimation->id]) }}" method="POST" style="display: none;" class="d-inline-flex">
														<a href="#" class="action-btn btn-danger mx-1  btn btn-sm d-inline-flex align-items-center bs-pass-para show_confirm" data-confirm="{{ __('Are You Sure?') }}" data-text="{{ __('This action can not be undone. Do you want to continue?') }}" data-confirm-yes="delete-form2-{{ $estimation->id }}" data-toggle="tooltip" title="{{ __('Delete') }}">
															<i class="ti ti-trash"></i>
														</a>
														@csrf
														@method('DELETE')
													</form>
													@endpermission
													@permission('estimation invite user')
													<div class="action-btn btn-primary ms-2">
														<a class="action-btn btn-primary mx-1  btn btn-sm d-inline-flex align-items-center"	data-ajax-popup="true" data-size="md" data-title="{{ __('Add User') }}"
															data-url="{{ route('estimation.allowedUsers', ['estimation_id'=>$estimation->id]) }}" data-toggle="tooltip" title="{{ __('Invite User') }}">
															<i class="ti ti-plus text-white"></i>
														</a>
													</div>
													@endpermission
												</div>
											</div>
											<div class="row mt-1">
												<div class="col-sm-12">
													<div class="user-group projectusers">
														@foreach ($estimation->all_quotes_list as $row)
															@php
																$quote_status = '';
																if($row->is_display == 1){
																	$border_color = '#6FD943';
																	$quote_status = __('Quote Submitted');
																} else{
																	$border_color = '';
																	$quote_status = __('Invited');
																}
															@endphp
															<img @if (!empty($row->user->avatar)) src="{{ get_file($row->user->avatar) }}" @else avatar="{{ $row->user->name }}" @endif class="subc" style="border:4px solid {{$border_color}} !important" data-bs-toggle="tooltip" title="{{ $row->user->name ." - ". ucfirst($quote_status) }}" data-user_id="{{ $row->user->id }}" data-estimation_id="{{ $estimation->id }}">
														@endforeach
													</div>
												</div>
											</div>
										@else
											<div class="row icons-div">
												<div class="col-sm-12">
													@php
														$quote_status = "";
														$est_status = $estimation->estimationStatus()->is_display;
														if($est_status == 0) {
															$quote_status = "invited";
														} else if($est_status == 1) {
															$quote_status = "quote_submitted";
														}
													@endphp
													@if($quote_status == "invited")
														<a href="{{ $setup_url }}" class="dropdown-item" >
															<i class="ti ti-add">{{ __("Create Quote") }}
														</a>
													@elseif($quote_status == "quote_submitted")
														<a href="{{ $setup_url }}" class="dropdown-item" >
															<i class="ti ti-eye"></i>{{ __("View Quote") }}
														</a>
													@else
														<a href="{{ $setup_url }}" class="dropdown-item" >
															<i class="ti ti-eye"></i>{{ __("View Quote") }}
														</a>
													@endif
												</div>
											</div>
										@endif
									</td>
								</tr>
								@endforeach
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
	<script src="{{ asset('Modules/Taskly/Resources/assets/libs/select2/dist/js/select2.min.js')}}"></script>
    <script type="text/javascript">
        $(document).ready(function(){
			$(document).on("click", ".projectusers img", function() {
				var csrfToken = $('meta[name="csrf-token"]').attr('content');
				var user_id = $(this).data('user_id');
				var estimation_id = $(this).data('estimation_id');

				const swalWithBootstrapButtons = Swal.mixin({
					customClass: {
						confirmButton: 'btn btn-success',
						cancelButton: 'btn btn-danger'
					},
					buttonsStyling: false
				})
				swalWithBootstrapButtons.fire({
					title: 'Are you sure to Remove this User from this Estimation?',
					text: "This action can not be undone. Do you want to continue?",
					icon: 'warning',
					showCancelButton: true,
					confirmButtonText: 'Yes',
					cancelButtonText: 'No',
					reverseButtons: true,
				}).then((result) => {
					if (result.isConfirmed) {
						$.ajax({
							url:'{{route('estimation.remove_estimation_user')}}',
							type:"POST",
							data:{
								estimation_id : estimation_id,
								user_id : user_id,
								_token : csrfToken
							},
							beforeSend:function () {
								showHideLoader('visible');
							},
							success:function (response) {
								if(response.status == true){
									showHideLoader('hidden');
									toastrs('Success', response.message, 'success');
									setTimeout(function () {
										location.reload();
									},1000)
								} else {
									toastrs('Error', response.message)
								}
							}
						});
					}
				})
			});
        });

    </script>

@endpush