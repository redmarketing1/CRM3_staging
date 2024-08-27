@extends('layouts.main')
@php
    $profile=asset(Storage::url('uploads/avatar'));
@endphp
@section('page-title')
	{{__('Edit Estimation')}}
@endsection
@section('title')
	{{ __('Edit Estimation') }}
@endsection
@push('css')
	<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
	<link rel="stylesheet" href="{{ asset('Modules/Taskly/Resources/assets/css/custom.css') }}" type="text/css" />
@endpush
@section('page-breadcrumb')
<a href="{{route('projects.index')}}">{{ __('All Project') }}</a>,<a href="{{route('projects.show', [$estimation->project_id])}}">{{$estimation->getProjectDetail->name }}</a>,{{ __('Edit') }}
@endsection

@section('page-action')

@endsection
@push('css')
	<style>
		
	</style>
@endpush
@push('scripts')
	<script src="{{ asset('Modules/Taskly/Resources/assets/js/jquery-ui.min.js')}}"></script>
    <script src="{{ asset('js/jquery.repeater.min.js') }}"></script>

	<script src="{{ asset('Modules/Taskly/Resources/assets/libs/select2/dist/js/select2.min.js')}}"></script>
	<script src="{{ asset('Modules/Taskly/Resources/assets/js/jquery.nestable.js')}}"></script>
	<script src="{{ asset('Modules/Taskly/Resources/assets/js/tinymce/tinymce.min.js') }}"></script>
	<script src="{{ asset('Modules/Taskly/Resources/assets/js/custom.js')}}"></script>

@endpush
@section('content')
	<div class="row">
		<div class="row">
			<div class="col-md-12">
				<div class="row">
					<div class="col-md-12 order-lg-2">
						<div class="card repeater table-card-full">
							@php
							$queues_result = $estimation->getQueuesProgress();
							$user = Auth::user();
							@endphp
							{{ Form::open(array('route' => 'estimations.importdata', 'files' => true, 'id' => 'quote_form')) }}
								<div class="card-body pb-0">
									<div class="row estimation-header">
										@php
											$col_md_class = 'col-md-4';
											$is_display_import = "d-none";
											if(count($estimation->estimation_products) == 0) {
												$col_md_class = 'col-md-3';
												$is_display_import = "";
											}
											if(Auth::user()->type != 'company' && Auth::user()->isAbleTo('estimation import') == false) {
												$col_md_class = 'col-md-4';
												$is_display_import = "d-none";
											}
										@endphp
										<div class="{{ $col_md_class }}">
											<div class="form-group">
												{{ Form::label('project', __('Project'),['class' => 'form-label']) }}
												<input type="text" class="form-control" value="{{$estimation->project()->name}}" readonly>
												<input type="hidden" name="project" value="{{ $project_id }}" />
											</div>
										</div>
										<div class="{{ $col_md_class }}">
											<div class="form-group">
												{{ Form::label('title', __('Estimation Title'),['class' => 'form-label']) }}
												<input class="form-control" name="title" id="estimation_title" type="text" value="{{$estimation->title}}">
												<input type="hidden" name="estimation_id" value="{{$estimation->id}}">
											</div>
										</div>
										<div class="{{ $col_md_class }}">
											<div class="form-group">
												{{ Form::label('issue_date', __('Issue Date'),['class' => 'form-label']) }}
												{{Form::date('issue_date', new \DateTime(),array('class'=>'form-control','required'=>'required'))}}
											</div>
										</div>
										@if(count($estimation->estimation_products) == 0)
										<div class="col-md-3 import-excel-box {{ $is_display_import }}">
											<div class="form-group">
												{{ Form::label('import_file', __('Import Excel File'), ['class' => 'form-label']) }}
												<a href="{{ asset('public/estimation_import_format.xlsx') }}" class="small ms-2">({{ __('Sample File Download') }})</a>
												{{ Form::file('import_file', ['class' => 'form-control']) }}
												{{ Form::submit(__('Import'), ['class' => 'btn mx-3 btn-primary']) }}
											</div>
										</div>
										@endif
									</div>
									<div class="table-responsive pt-5">
										<table class="table w-100 table-hover" id="estimation-edit-table">
											<thead>
												<tr class="total-line-top no-sort" id="net_with_discount_row">
													<th colspan="4" class="toplabel buttons-top" data-dt-order="disable">
		
													</th>
													<th colspan="3" class="toplabel total-net-discount" data-dt-order="disable">{{ __('Net incl. Discount') }}</th>
													@if(isset($ai_description_field))
														<th id="" rowspan="5" class="border-left-right toptotal ai-head column_ai_description" data-dt-order="disable">
															<button type="button" class="btn_replace_descriptions d-none" data-bs-whatever="{{__('Replace with AI description')}}">
																<!-- <i class="fa-solid fa-retweet"></i><br> -->
																{{ __('Replace current Descriptions') }}
															</button>
														</th>
													@endif
													@foreach($total_prices['net_with_discount'] as $net_with_discount_key => $net_with_discount)
														<th colspan="2" id="{{$net_with_discount_key}}_neuwest" class="totalnr {{$net_with_discount_key}} toptotal total-net-discount border-left-right" data-dt-order="disable">
															<div id="{{$net_with_discount_key}}" class="">
																{{ currency_format_with_sym($net_with_discount)}}
															</div>
															<input type="hidden" id="{{$net_with_discount_key}}_value" name="{{$net_with_discount_key}}" value="{{$net_with_discount}}">
														</th>
													@endforeach
												</tr>

												<tr class="total-line-top no-fixed-header no-sort" id="gross_with_discount_row">
												<th colspan="4" rowspan="4" class="toplabel buttons-top" data-dt-order="disable">
														
													</th>
													<th colspan="3" class="toplabel total-gross-discount" data-dt-order="disable">{{ __('Gross incl. Discount') }}</th>
													
													@foreach($total_prices['gross_with_discount'] as $gross_with_discount_key => $gross_with_discount)
														<th colspan="2" id="{{$gross_with_discount_key}}_neuwest" class="totalnr {{$gross_with_discount_key}} toptotal total-gross-discount border-left-right" data-dt-order="disable">
															<div id="{{$gross_with_discount_key}}" class="">
																{{ currency_format_with_sym($gross_with_discount)}}
															</div>
															<input type="hidden" id="{{$gross_with_discount_key}}_value" name="{{$gross_with_discount_key}}" value="{{$gross_with_discount}}">
														</th>
													@endforeach
												</tr>
												<tr class="total-line-top no-fixed-header no-sort" id="net_row1">
													<th colspan="3" class="toplabel total-net" data-dt-order="disable">{{ __('Net') }}</th>
													
													@foreach($total_prices['net'] as $net_key => $net)
														<th colspan="2" id="{{$net_key}}_neuwest" class="totalnr {{$net_key}} toptotal total-net border-left-right" data-dt-order="disable">
															<div id="{{$net_key}}" class="">
																{{ currency_format_with_sym($net)}}
															</div>
															<input type="hidden" id="{{$net_key}}_value" name="{{$net_key}}" value="{{$net}}">
														</th>
													@endforeach
												</tr>
												<tr class="total-line-top no-fixed-header" id="gross_row">
													<th colspan="3" class="toplabel total-gross no-sort" data-dt-order="disable">{{ __('Gross (incl. VAT)') }}</th>
													
													@php
														$tax = $total_prices['tax'];
														$gross = $total_prices['gross'];
													@endphp
													
													@foreach($allQuotes as $key => $quotes1)
														@php
															$tax_key = "tax_sc".$quotes1->id;
															$gross_key = "gross_sc".$quotes1->id;
														@endphp
														<th colspan="2" class="toptotal total-gross border-left-right finalize_quote{{$quotes1->id}} gross_tax_sc{{$quotes1->id}}" data-dt-order="disable">
															<span class="dt-column-title">
																<div id="{{$tax_key}}_neuwest" class="totalnr {{$tax_key}} toptotal total-discount total-vat-input">
																		<select name="tax[{{$tax_key}}]" id="{{$tax_key}}">
																			<option value="19" {{$tax[$tax_key]==19?"selected":""}}>19%</option>
																			<option value="0" {{$tax[$tax_key]==0?"selected":""}}>0%</option>
																		</select>
																</div>
																<div id="{{$gross_key}}_neuwest" class="totalnr {{$gross_key}}totalsetting total-gross-total">
																	<div id="{{$gross_key}}" class="">
																		{{ currency_format_with_sym($gross[$gross_key])}}
																	</div>
																	<input type="hidden" id="{{$gross_key}}_value" name="{{$gross_key}}" value="{{$gross[$gross_key]}}">
																</div>
															</span>
														</th>
													@endforeach
												</tr>

												<tr class="total-line-top totalsetting no-fixed-header" id="discount_row">
													<th colspan="3" class="toplabel total-discount" data-dt-order="disable"></th>
													
													@php
														$markup = $total_prices['markup'];
														$discount = $total_prices['discount'];
													@endphp
													@foreach($allQuotes as $key => $quotes1)
														@php
															$markup_key = "markup_sc".$quotes1->id;
															$discount_key = "discount_sc".$quotes1->id;
															$type = "quote";
														@endphp
														<th colspan="2" data-dt-order="disable" class="total-settings border-left-right finalize_quote{{$quotes1->id}} markup_discount_sc{{$quotes1->id}}">
															<span class="dt-column-title">
																<div id="{{$markup_key}}_neuwest" class="totalnr {{$markup_key}} toptotal total-markup">
																	@if($user->type != "company")
																		<input type="hidden" id="{{ $markup_key }}" name="markup[{{$markup_key}}]" data-old="{{$markup[$markup_key]->markup}}" value="{{ currency_format_with_sym($markup[$markup_key]->markup,'','',false)}}" class="form-control" onfocusout="calculateMarkup(this,'{{$quotes1->id}}','{{$type}}')">
																	@else
																		<div class="total-setting-label">{{ __('Markup') }}</div>
																		<div class="total-markup-input">
																			<input type="text" id="{{ $markup_key }}" name="markup[{{$markup_key}}]" data-old="{{$markup[$markup_key]->markup}}" value="{{ currency_format_with_sym($markup[$markup_key]->markup,'','',false)}}" class="form-control" onfocusout="calculateMarkup(this,'{{$quotes1->id}}','{{$type}}')">
																		</div>
																	@endif
																</div>
																<div id="{{$discount_key}}_neuwest" class="totalnr {{$discount_key}}toptotal total-markup">
																	<div class="total-setting-label">{{ __('Cash Discount') }}</div>
																		<div class="total-discount-input">
																			<input type="text" id="{{$discount_key}}" name="discount[{{$discount_key}}]" value="{{$discount[$discount_key]}}" class="form-control">
																		</div>
																	</div>
																</div>
															</span>
														</th>
													@endforeach
												</tr>

												<tr class="total-line-top" id="contractors_row">
												<th colspan="4" class="toplabel buttons-top" data-dt-order="disable">
														<input type="text" placeholder="{{ __('Search') }}..." id="table-search">
														<input type="hidden" name="" id="remove_item_ids" value="">
														<input type="hidden" name="" id="remove_group_ids" value="">
														<input type="hidden" name="" id="duplicate_item_ids" value="">
														<input type="hidden" name="" id="duplicate_group_ids" value="">
														<span class="btn-separator"></span>
														<button id="update_pos_btn" type="button" data-bs-toggle="tooltip" title="{{ __('Update POS Numbers') }}"><i class="fa-solid fa-list-ol"></i></button>
														<!-- <button class="reorder_group_btn" type="button"><i class="fa-solid fa-list"></i> {{ __('Reorder Group') }}</button> -->
														@if($user->type == "company")
															@permission('estimation download option')
															<div class="dropdown download-dropdown">
																<span data-bs-toggle="tooltip" title="{{ __('Download') }}">
																	<a class="dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false"> 
																		<i class="fa-solid fa-download"></i>
																	</a>
																	<div class="dropdown-menu dash-h-dropdown">
																		<a href="{{ route('estimation.export.excel', ['id' =>\Crypt::encrypt($estimation->id), 'type' => 'download']) }}" target="_blank" class="dropdown-item"><span>{{ __('Excel') }}</span></a>
																		<a href="{{ route('estimation.export.csv', ['id' =>\Crypt::encrypt($estimation->id), 'type' => 'download']) }}" target="_blank" class="dropdown-item"><span>{{ __('CSV') }}</span></a>
																		<a href="{{ route('estimation.export.gaeb', ['id' =>\Crypt::encrypt($estimation->id), 'type' => 'download']) }}" target="_blank" class="dropdown-item"><span>{{ __('GAEB') }}</span></a>
																	</div>
																</span>
															</div>
															@endpermission
															<div class="dropdown column-dropdown">
																<span data-bs-toggle="tooltip" title="{{ __('Show / Hide Columns') }}">
																	<a class="dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false"> 
																		<i class="fa-solid fa-table-columns"></i>
																	</a>
																	<div class="dropdown-menu dash-h-dropdown">
																		<h6>{{ __('Show / Hide Columns') }}</h6>
																		<label class="dropdown-item">
																			<input type="checkbox" class="column-toggle" data-column="column_pos" checked>
																			<span>{{ __('Pos') }}</span>
																		</label>
																		{{-- <label class="dropdown-item">
																			<input type="checkbox" class="column-toggle" data-column="column_name" checked>
																			<span>{{ __('Name') }}</span>
																		</label> --}}
																		<label class="dropdown-item">
																			<input type="checkbox" class="column-toggle" data-column="column_quantity" checked>
																			<span>{{ __('Quantity') }}</span>
																		</label>
																		<label class="dropdown-item">
																			<input type="checkbox" class="column-toggle" data-column="column_unit" checked>
																			<span>{{ __('Unit') }}</span>
																		</label>
																		<label class="dropdown-item">
																			<input type="checkbox" class="column-toggle" data-column="column_optional" checked>
																			<span>{{ __('Opt') }}</span>
																		</label>
																		@if(isset($ai_description_field))
																			<label class="dropdown-item">
																				<input type="checkbox" class="column-toggle" data-column="column_ai_description" checked>
																				<span>{{ __('Auto Description') }}</span>
																			</label>
																		@endif
																		@foreach($allQuotes as $key => $quotes)
																			@php 
																				$quote_title = isset($quotes->subContractor->name) ? $quotes->subContractor->name : $quotes->title;
																			@endphp
																			<label class="dropdown-item">
																				<input type="checkbox" class="column-toggle" data-column="quote_th" data-quote="{{$quotes->id}}" checked>
																				<span>{{$quote_title}}</span>
																			</label>
																		@endforeach
																	</div>
																</span>
															</div>
														@endif
													</th>
													<th colspan="3" data-orderable="false" data-dt-order="disable" class="toplabel markup_discount_th"></th>
													@if(isset($ai_description_field))
														<th id="" class="total-main-title total-company-title border-left-right text-nowrap column_ai_description" data-orderable="false" data-dt-order="disable">
															<span>{{ isset($desc_template->title) ? $desc_template->title : '' }}</span>
															@if(!empty($queues_result) && count($queues_result) > 0)
																<div class="row m-1 ai-progress-bar">
																	@foreach($queues_result['estimation_queues_list'] as $qrow)
																		@if($qrow['completed_percentage'] >= 0 && $qrow['completed_percentage'] < 100 && $qrow['type'] == 0)
																			<div class="col-md-12 project_block" data-id="{{$estimation->project_id}}">
																				<div class="form-group estimation_block" data-id="{{$estimation->id}}">
																						<div class="progress queue_progress" data-id="{{ $qrow['smart_template_id'] }}">
																						<div class="progress-bar bg-success" role="progressbar" style="width: {{$qrow['completed_percentage']}}%" aria-valuenow="{{$qrow['completed_percentage']}}" aria-valuemin="0" aria-valuemax="100">{{$qrow['completed_percentage']}}%</div>
																					</div>
																				</div>
																			</div>
																		@endif
																	@endforeach
																</div>
															@endif
														</th>
													@endif
													@foreach($allQuotes as $key => $quotes)
														<th colspan="2" data-orderable="false" class="total-main-title total-company-title border-left-right finalize_quote_title{{$quotes->id}} title_sc{{$quotes->id}} {{ $quotes->title }}" data-dt-order="disable">
																@php
																//	$quote_title = $quotes->title;
																	$quote_title = isset($quotes->subContractor->name) ? $quotes->subContractor->name : $quotes->title;
																	// if($key == 0) {
																	// 	$quote_title = $company_name;
																	// }
																	$hide_options = '';
																@endphp
																<div class="total-company-title-inner">
																	<span class="sc{{$quotes->id}}">
																		{{ $quote_title }}
																	</span>
																	<div class="company-total-settings {{ $hide_options }}">
																		<i class="ti ti-settings float-end" id="dropdownMenuButton{{$quotes->id}}" data-bs-toggle="dropdown" aria-expanded="false"></i>
																		<ul class="dropdown-menu quote_options{{$quotes->id}}" aria-labelledby="dropdownMenuButton{{$quotes->id}}">
																			@permission('estimation duplicate quote option')
																			<li>
																				<a class="dropdown-item" href="javascript:void(0)" onclick="duplicateColumn('{{$quotes->id}}','{{$quotes->title}}','{{$quotes->user_id}}','{{$quotes->markup}}',false,'{{$type}}')"><i class="fa-regular fa-copy"></i>{{ __('Duplicate') }}</a>
																			</li>
																			@endpermission
																			@if (Auth::user()->type == 'company')
																				@if($key > 0)
																					<li><a class="dropdown-item" href="javascript:void(0)" onclick="duplicateColumn('{{$quotes->id}}','{{$quotes->title}}','{{$quotes->user_id}}','{{$quotes->markup}}',true)"><i class="fa-solid fa-pencil"></i>{{ __('Change Name or Contact') }}</a></li>
																				@endif
																			@endif
																			@if($quotes->is_clone)
																				<li class="delete"><a class="dropdown-item" href="javascript:void(0)" onclick="deleteColumn('{{$quotes->id}}',this)"><i class="fa-regular fa-trash-can"></i>{{ __('Delete') }}</a></li>
																			@endif
																			@if (Auth::user()->type == 'company')
																				<li>
																					<label class="dropdown-item">
																						<input type="checkbox" id="final_quote_checkbox" onchange="handleCheckboxChange(this, '{{$quotes->id}}')">
																						{{ __('Client Quote') }}
																					</label>
																				</li>
		
																				<li>
																					<label class="dropdown-item">
																						<input type="checkbox" id="client_quote_checkbox" onchange="handleCheckboxChange(this, '{{$quotes->id}}', 'client')">
																						{{ __('Final Estimation for Client') }}
																					</label>
																				</li>
		
																				<li>
																					<label class="dropdown-item">
																						<input type="checkbox" id="sub_contractor_quote_checkbox" onchange="handleCheckboxChange(this, '{{$quotes->id}}', 'sub_contractor')">
																						{{ __('Final Estimation for Subcontractor') }}
																					</label>
																				</li>
																			@endif
																		</ul>
																	</div>
																</div>
																
																@if(!empty($queues_result) && count($queues_result) > 0)
																	<div class="row m-1 ai-progress-bar">
																		@foreach($queues_result['estimation_queues_list'] as $qrow)
																			@if($qrow['completed_percentage'] >= 0 && $qrow['completed_percentage'] < 100 && $quote_title == $qrow['smart_template_main_title'])
																			@php
																				$hide_options = 'hide';
																				$progress_class = "bg-success";
																				$info_icon = "d-none";
																				if($qrow['cancelled_record'] > 0 || $qrow['error_record'] > 0) {
																					$progress_class = "bg-danger";
																					$info_icon = "";
																				}
																			@endphp
																			<div class="col-md-12 project_block" data-id="{{$estimation->project_id}}">
																					<div class="form-group estimation_block" data-id="{{$estimation->id}}">
																						<div class="progress queue_progress" data-id="{{ $qrow['smart_template_id'] }}">
																							<div class="progress-bar {{ $progress_class }}" role="progressbar" style="width: {{$qrow['completed_percentage']}}%" aria-valuenow="{{$qrow['completed_percentage']}}" aria-valuemin="0" aria-valuemax="100">{{$qrow['completed_percentage']}}%</div>
																						</div>
																					</div>
																					<span class="CellWithComment {{ $info_icon }}">
																						<i class="fa fa-info-circle "></i>
																						<span class="CellComment">{{ $qrow['error_message'] }}</span>
																					</span>
																				</div>
																			@endif
																		@endforeach
																	</div>
																@endif
																<input type="hidden" class="sc{{$quotes->id}}_value" value="{{ $quote_title }}">
																@php
																	if($user->type != "company") {
																		$hide_options = 'hide';
																	}
																@endphp
															</div>
														</th>
													@endforeach
												</tr>
												<tr id="estimation-edit-table-thead">
													<th class="column_reorder" data-dt-order="disable">
													</th>
													<th class="column_checkbox" data-dt-order="disable">
														<input type="checkbox" class="SelectAllCheckbox" name="" value="">
													</th>

													<th class="column_pos" data-dt-order="disable">{{ __('Pos') }}</th>
													<th class="column_group" data-dt-order="disable">{{ __('Group Name') }}</th>
													<th class="column_name" data-dt-order="disable">
														<i class="fa-solid fa-indent expand_more show_all"></i> 
														{{ __('Name') }}</th>
													<th class="column_quantity" data-dt-order="disable">{{ __('Quantity') }}</th>
													<th class="column_unit" data-dt-order="disable">{{ __('Unit') }}</th>
													<th class="column_optional border-right" data-dt-order="disable">{{ __('Opt') }}</th>
													@if(isset($ai_description_field))
														<th class="column_ai_description border-left-right" data-dt-order="disable">{{ __('Auto Description') }}</th>
													@endif
													@foreach($allQuotes as $key => $quotes)
														<th class="column_single_price border-left quote_th{{$quotes->id}}" data-dt-order="disable">{{ __('Single Price') }}</th>
														<th class="column_total_price border-right quote_th{{$quotes->id}}" data-dt-order="disable">{{ __('Total Price') }}</th>
													@endforeach
												</tr>
											</thead>
											<tbody>
												@foreach($estimation_groups as $estimation_group)
													@include('taskly::project_estimations.partial_setup', ['estimation_group' => $estimation_group,'ai_description_field'=>$ai_description_field,'allQuotes'=>$allQuotes])
												@endforeach
											</tbody>
											<tfoot>
												<tr id="estimation-edit-table-tfoot">
													<th class="column_reorder" data-dt-order="disable">
													</th>
													<th class="column_checkbox" data-dt-order="disable">
														<input type="checkbox" class="SelectAllCheckbox" name="" value="">
													</th>
													<th class="column_pos" data-dt-order="disable">{{ __('POS') }}</th>
													<th class="column_group" data-dt-order="disable">{{ __('Group Name') }}</th>
													<th class="column_name" data-dt-order="disable">{{ __('Name') }}</th>
													<th class="column_quantity" data-dt-order="disable">{{ __('Quantity') }}</th>
													<th class="column_unit" data-dt-order="disable">{{ __('Unit') }}</th>
													<th class="column_optional border-right" data-dt-order="disable">{{ __('Opt') }}</th>
													@if(isset($ai_description_field))
														<th class="column_ai_description border-left-right" data-dt-order="disable">{{ __('Auto Description') }}</th>
													@endif
													@foreach($allQuotes as $key => $quotes)
														<th class="column_single_price border-left quote_th{{$quotes->id}}" data-dt-order="disable">{{ __('Single Price') }}</th>
														<th class="column_total_price border-right quote_th{{$quotes->id}}" data-dt-order="disable">{{ __('Total Price') }}</th>
													@endforeach
												</tr>
											</tfoot>
										</table>
										@if (Auth::user()->type == 'company')
											<div class="estimation-footer-box">
												<div class="form-group col-md-6">
													{{ Form::label('technical_description', __('Technical Description'),['class' => 'form-label']) }}
													<textarea rows="3" class="form-control border-0 resize-none tinyMCE" name="technical_description" id="technical_description" placeholder="{{ __('Technical Description') }}">
														@if($estimation->init_status == 0)
															{!! nl2br(e($estimation->technical_description)) !!}
														@else
															{{$estimation->technical_description}}
														@endif
													</textarea>
												</div>
											</div>
										</div>
										@endif
										
									</div>
									{{-- @if (Auth::user()->type == 'company') --}}
									@if($estimation->status != 2 && $estimation->status != 3)
										<div class="button-wrapper-fixed">
											<div class="button-wrapper-left">
												@permission('estimation add item option')<button type="button" id="add_estimation_item_btn"><i class="fa-solid fa-plus"></i> {{ __('Item') }}</button>@endpermission
												@permission('estimation add group option')<button type="button" id="add_estimation_group_btn"><i class="fa-solid fa-plus"></i> {{ __('Group') }}</button>@endpermission
												@permission('estimation add comment option')<button type="button" id="add_estimation_comment_btn"><i class="fa-solid fa-plus"></i> {{ __('Comment') }}</button>@endpermission 
												<!-- <button id="remove_items_btn" class="remove_items_btn d-none" ><i class="fa-regular fa-trash-can"></i> {{ __('Remove') }}</button> -->
												<div class="buttons-top">
													<div class="dropdown options-dropdown">
														<span>
															<a class="dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
																<i class="fa-solid fa-list"></i>
																{{ __('Options') }}
															</a>
															<div class="dropdown-menu dash-h-dropdown">
																@permission('estimation remove option')<a id="remove_items_btn" class="remove_items_btn dropdown-item"><span>{{ __('Remove') }}</span></a>@endpermission
																<!-- <a id="duplicate_items_btn" class="duplicate_items_btn dropdown-item"><span>{{ __('Duplicate') }}</span></a> -->
															</div>
														</span>
													</div>
												</div>
												<span class="btn-separator"></span>
												@if($user->type == "company")		
													<div class="select-smart-template-div">
														<select name="" id="smart_template_id" class="ai_fields d-none">
															<option value="">{{ __('Select Smart Block') }}</option>
															@if(isset($smart_templates) && count($smart_templates) > 0)
																@foreach($smart_templates as $template)
																	<option value="{{ $template->id }}">{{ $template->title }}</option>
																@endforeach
															@endif
														</select>
														<button id="smart_template_generate" class="call-ai-smart-template sb-go-btn sb-go-selected ai_fields d-none">{{ __('Go') }}</button>
													</div>
												@endif
											</div>	
											<div class="button-wrapper-right">
												<button type="button" id="save-button" onclick="saveTableDataMultiple()" class="btn btn-primary">{{ __('Save') }}</button>
												@if (Auth::user()->type == 'company')
													<button type="button" id="" onclick="saveTableData('preview')" class="btn btn-secondary mx-2">{{ __('Preview & Complete') }}</button>
												@endif
											</div>
										</div>
									@endif
									{{-- @endif --}}
								</div>
							{{ Form::close() }}
						</div>
					</div>
				</div>
			</div>
		</div>
		 <!-- Modal -->
		 <div class="modal fade" id="duplicateModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog  modal-dialog-centered">
                <div class="modal-content" id="modal-content">

                </div>
            </div>
        </div>

		<div class="modal fade" data-backdrop="static" id="group_reorder_Modal" tabindex="-1" role="dialog" aria-hidden="true">
			<div class="modal-dialog modal-lg" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title">{{ __('Reorder Group')}} <i class="fa-regular fa-circle-question" data-bs-toggle="tooltip" title="{{ __('Reorder Groups by dragging and dropping each group up and down or left to right for Sublevels')}}"></i></h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>
					<div class="modal-body body">
					
					</div>
					<div class="modal-footer p-2">
						<button type="button" class="btn btm-sm btn-primary" onClick="store_group_reorder(true)">{{ __('Apply')}}</button>
					</div>
				</div>
			</div>
		</div>
		@php
			$project = $estimation->project();
			$csv_file_name = $estimation->title . ' - ' . $project->title;
			if (isset($project->construction_detail->address_1)) {
				$csv_file_name .= ' - ' . $project->construction_detail->address_1;
			}
			if (isset($project->construction_detail->city)) {
				$csv_file_name .= ' - ' . $project->construction_detail->city;
			}
			$csv_file_name .= ' - #1' . $estimation->id . ' - ' . $company_name;
		@endphp
	</div>
@endsection
@push("scripts")
    <script>
		var project_estimation_id = '{{$encryptId}}';
		let estimation_id = {{$estimation->id}};
		var project_id = '{{\Crypt::encrypt($estimation->project_id)}}';
		let first_quote_id = {{$first_quote_id}};
		let ai_description_field = {{ isset($ai_description_field) ? 1 : 0}};
		let all_quotes = {!! json_encode($allQuotes) !!};
		let all_contractors = {!! json_encode($all_contractors) !!};
		let final_id = {!! json_encode($final_id) !!};
		let client_final_quote_id = {!! json_encode($client_final_quote_id) !!};
		let sub_contractor_final_quote_id = {!! json_encode($sub_contractor_final_quote_id) !!};
		let moneyFormat = '{{$site_money_format}}';
		var estimation_table;
		let counter = 1;
		var total_colspan = 3;
		let columns_data = [];
		let subContractorDiscountIds = '#discount';
        let subContractorTaxIds = '#tax';
        for (const quote of all_quotes) {
            let single_price =  "single_price_sc"+quote.id;
            subContractorDiscountIds += ", #discount_sc"+quote.id;
            subContractorTaxIds += ", #tax_sc"+quote.id;
        }
		 // Formatter to display price in German format
		var moneyFormatter = site_money_format(moneyFormat);

		var execute_request = true;
		
        $(document).ready(function() {
			update_all_prices();

			setInterval(function(){
                if (execute_request == true) {
                    check_progress();
                }
            }, 3000);

			if ($('#final_quote_checkbox').is(':checked')) {
            updateCellColors(final_id, null, true);
			} else {
				updateCellColors(final_id, null, false);
			}
			if ($('#client_quote_checkbox').is(':checked')) {
				updateCellColors(client_final_quote_id, 'client', true);
			} else {
				updateCellColors(client_final_quote_id, 'client', false);
			}
			if ($('#sub_contractor_quote_checkbox').is(':checked')) {
				updateCellColors(sub_contractor_final_quote_id, 'sub_contractor', true);
			} else {
				updateCellColors(sub_contractor_final_quote_id, 'sub_contractor', false);
			}

			var tinyMCE = init_tiny_mce('#technical_description');

        	$('#estimation-edit-table thead tr th.no-sort').css('pointer-events', 'none');

		//	$(subContractorDiscountIds+","+subContractorTaxIds).on("keyup", update_all_prices);
       	//	$(subContractorDiscountIds+","+subContractorTaxIds).on("change", update_all_prices);

			$(document).on("keyup", subContractorDiscountIds+","+subContractorTaxIds, function(e) {
				update_all_prices();
				saveTableDataMultiple();
			});
			$(document).on("change", subContractorDiscountIds+","+subContractorTaxIds, function(e) {
				update_all_prices();
				saveTableDataMultiple();
			});

			$(document).on("click", ".column-toggle", function(e) {
				// e.preventDefault();
				var column_name 		= $(this).data('column');
				var quote_id 			= $(this).data('quote');

				if ($(this).is(":checked")) {
					if (column_name == 'quote_th') {
						$('.title_sc'+quote_id).show();
						$('.markup_discount_sc'+quote_id).show();
						$('.gross_tax_sc'+quote_id).show();
						$('.net_sc'+quote_id).show();
						$('.gross_with_discount_sc'+quote_id).show();
						$('.net_with_discount_sc'+quote_id).show();
						$('.quote_th'+quote_id).show();
					} else {
						$('.'+column_name).show();
					}
				} else {
					if (column_name == 'quote_th') {
						$('.title_sc'+quote_id).hide();
						$('.markup_discount_sc'+quote_id).hide();
						$('.gross_tax_sc'+quote_id).hide();
						$('.net_sc'+quote_id).hide();
						$('.gross_with_discount_sc'+quote_id).hide();
						$('.net_with_discount_sc'+quote_id).hide();
						$('.quote_th'+quote_id).hide();
					} else {
						$('.'+column_name).hide();
					}
				}

				let col_span 			= 3;
				let main_visible_column = 3;
				let total_col_span 		= 7;
				let name_header_span	= 1;
				let group_title_span	= 4;

				if ($('.column-toggle[data-column="column_pos"]').is(":checked")) {
					main_visible_column++;
				}
				if ($('.column-toggle[data-column="column_name"]').is(":checked")) {
					// main_visible_column++;
					// group_span++;
					// $('.group_row .grouptitle').show();
					// $('.group_row .grouptitle').attr('colspan', 4);
					// $('.group_row .column_reorder').removeAttr('colspan');
				} else {
					// $('.group_row .grouptitle').show();
					// $('.group_row .column_reorder').attr('colspan', 4);
					// $('.group_row .grouptitle').removeAttr('colspan');
				}
				if ($('.column-toggle[data-column="column_quantity"]').is(":checked")) {
					main_visible_column++;
				} else {
					group_title_span--;
				}
				if ($('.column-toggle[data-column="column_unit"]').is(":checked")) {
					main_visible_column++;
				} else {
					group_title_span--;
				}
				if ($('.column-toggle[data-column="column_optional"]').is(":checked")) {
					main_visible_column++;
				} else {
					group_title_span--;
				}
				if ($('.column-toggle[data-column="column_ai_description"]').length > 0 && $('.column-toggle[data-column="column_ai_description"]').is(":checked")) {
					// main_visible_column++;
				}

				// $('.group_row .grouptitle').attr('colspan', group_span);
				if (main_visible_column < 5) {
					name_header_span = name_header_span + (5 - main_visible_column);
					group_title_span = group_title_span + (5 - main_visible_column);
				}
					
				console.log('main_visible_column', main_visible_column);
				console.log('group_title_span', group_title_span);
				console.log('name_header_span', name_header_span);

				$('.column_name').attr('colspan', name_header_span);
				$('.column_name.grouptitle').attr('colspan', group_title_span);
				$('.column_name.desc_column').attr('colspan', group_title_span);
				$('th.column_name').attr('colspan', name_header_span);
				

				if (main_visible_column <= total_col_span) {
					if (main_visible_column > 3) {
						var adjust_span = main_visible_column -4;
						if (adjust_span <= 0) {
							adjust_span = 1;
						}
						$('thead .toplabel.total-net-discount').attr('colspan', adjust_span);
						$('thead .toplabel.total-gross-discount').attr('colspan', adjust_span);
						$('thead .toplabel.total-net').attr('colspan', adjust_span);
						$('thead .toplabel.total-gross').attr('colspan', adjust_span);
						$('thead .toplabel.total-discount').attr('colspan', adjust_span);
						$('thead .toplabel.markup_discount_th').attr('colspan', adjust_span);
						// $('thead .totalsetting .total-settings').attr('colspan', adjust_span);
						// $('thead .total-main-title').attr('colspan', adjust_span);

					} else {
						console.log('You are here');
						// $('.column_name').attr('colspan', 3);
						
					}
				}
			});
			$(document).on("click", ".grp-dt-control", function(e) {
				var group_pos = $(this).parents('.group_row').data('group_pos');
				if ($(this).hasClass("fa-caret-right")) {
					$('.description_row[data-group_pos="'+group_pos+'"]').show();
					$('.item_row[data-group_pos="'+group_pos+'"]').find('.desc_toggle').removeClass('fa-caret-right').addClass('fa-caret-down');
				} else {
					$('.description_row[data-group_pos="'+group_pos+'"]').hide();
					$('.item_row[data-group_pos="'+group_pos+'"]').find('.desc_toggle').addClass('fa-caret-right').removeClass('fa-caret-down');
				}
			});

			$(document).on("click", ".desc_toggle", function(e) {
				var item_id = $(this).parents('.item_row').data('id');
				$('.description_row[data-id="'+item_id+'"]').toggle();
				$(this).toggleClass('fa-caret-right').toggleClass('fa-caret-down');
			});

			$(document).on("click", ".expand_more", function(e) {
				var show_all = $(this).hasClass('show_all');
				$(".group_row").each(function() {
					var group_pos = $(this).data('group_pos');
					if (show_all === true) {
						$('.description_row[data-group_pos="'+group_pos+'"]').show();	
						$('.desc_toggle').removeClass('fa-caret-right').addClass('fa-caret-down');
					} else {
						$('.description_row[data-group_pos="'+group_pos+'"]').hide();	
						$('.desc_toggle').addClass('fa-caret-right').removeClass('fa-caret-down');
					}
				});
				$(this).toggleClass('show_all');
			});
			
			$(document).on("keyup", ".row_qty", function(e) {
				update_all_prices();
			});
			$(document).on("keyup", ".row_price", function(e) {
				var quate_id = $(this).data('id');
				var item_id = $(this).data('item_id');
				var item_price = $(this).val();
				item_price = parseLocaleNumber(moneyFormat,item_price);
				$('.base_single_price_sc_input_'+item_id+'_'+ quate_id).val(item_price);
				update_all_prices();
			});
			$(document).on('change', '.select_optional', function(e) {
				update_all_prices();
			});

			$(document).on("click", "#add_estimation_item_btn", function(e) {
				e.preventDefault();
				addItem(false)
			});

			$(document).on("click", "#add_estimation_comment_btn", function(e) {
				e.preventDefault();
				addComment();
			});

			$(document).on("click", "#add_estimation_group_btn", function(e) {
				e.preventDefault();
				addItem(true)
			});



			// select all checkbox
			$(document).on('change', '.SelectAllCheckbox', function() {
				if($(this).is(':checked')){
					$('.item_selection').prop('checked', true);
					$('.group_checkbox').prop('checked', true);
				} else {
					$('.item_selection').prop('checked', false);
					$('.group_checkbox').prop('checked', false);
				}
				selected_quote_items();
			});
			$(document).on("change", ".group_checkbox", function(e) {
				var group_id = $(this).val();
				if($(this).is(':checked')){
					$('.grp_checkbox'+group_id).prop('checked', true);
				} else {
					$('.grp_checkbox'+group_id).prop('checked', false);
				}
				selected_quote_items();
			});

			$(document).on("click", "#remove_items_btn", function(e) {
				e.preventDefault();
				var token = $('meta[name="csrf-token"]').attr('content');
				var item_ids = $('#remove_item_ids').val();
				var group_ids = $('#remove_group_ids').val();

				if (item_ids != '' || group_ids != '') {
					$.ajax({
						url: "{{route('estimations.remove_items.estimate')}}",
						type: "POST",
						contentType: "application/json",
						data: JSON.stringify({estimation_id:estimation_id,item_ids:item_ids,group_ids:group_ids, _token: token}),
						success: function(response) {
							if (response.status == true) {
								$('.remove_items_btn').addClass('d-none');
								$('.btn_replace_descriptions').addClass('d-none');
								$('.ai_fields').addClass('d-none');
								toastrs('{{ __('Success') }}', response.message);
								if(group_ids != '') {
									var myArray = JSON.parse(group_ids);
									var i;
									for (i = 0; i < myArray.length; ++i) {
										$('tr[data-group_id="'+myArray[i]+'"]').remove();
									}
								} 
								if(item_ids != '') {
									var myArray = JSON.parse(item_ids);
									var i;
									for (i = 0; i < myArray.length; ++i) {
										$('tr[data-id="'+myArray[i]+'"]').remove();
									}
								}
							} else {
								toastrs("Error",response.message);
							}					
						},
						error: function(error) {
							// Handle any errors that occur during the Ajax request
							console.error("Error sending data to the server:", error);
						}
					});
				} else {
					toastrs("Error","Please select items to remove");
				}
			});

			$(document).on("click", "#update_pos_btn", function(e) {
				e.preventDefault();
				updatePOS();
			});
			
			$(document).on("click", ".reorder_group_btn", function(e) {
				e.preventDefault();

				$.ajax({
					url: "{{route('estimations.reorder_group_modal')}}",
					type: "POST",
					contentType: "application/json",
					data: JSON.stringify({estimation_id:estimation_id}),
					success: function(response) {
						if (response.status == true) {
							$("#group_reorder_Modal .modal-body").html(response.html_data);
            				$("#group_reorder_Modal").modal('show');

							$('#nestable').nestable({
								group: 1
							}).on('change', store_group_reorder);
						} else {
							toastrs("Error",response.message);
						}					
					},
					error: function(error) {
						// Handle any errors that occur during the Ajax request
						console.error("Error sending data to the server:", error);
					}
				});
			});

			$('#nestable-menu').on('click', function(e)
			{
				var target = $(e.target),
					action = target.data('action');
				if (action === 'expand-all') {
					$('.dd').nestable('expandAll');
				}
				if (action === 'collapse-all') {
					$('.dd').nestable('collapseAll');
				}
			});

			$("#estimation-edit-table").sortable({
				// items: 'tr:not(tr:first-child)',
				items: 'tr.item_row',
				cursor: 'pointer',
				axis: 'y',
				dropOnEmpty: false,
				start: function (e, ui) {
					ui.item.addClass("selected");
				},
				stop: function (e, ui) {
					var item_id = $(ui.item).data('id');
					var description_row = $('.description_row[data-id="'+item_id+'"]');
					$('.description_row[data-id="'+item_id+'"]').remove();
					description_row.insertAfter($(ui.item));
					
					var group_pos = $(ui.item).prevAll("tr.group_row:first").data('group_pos');
					var group_id = $(ui.item).prevAll("tr.group_row:first").data('group_id');
					$(ui.item).attr('data-group_pos',group_pos);
					$(ui.item).attr('data-group_id',group_id);
					
					ui.item.removeClass("selected");
					setTimeout(function(){
						handleSaveOrder();
                    }, 300);
				}
			});
        });

		function validateInput(inputElement) {
			inputElement.value = inputElement.value.replace(/[^\d.,-]/g, '');
		}

		function countRows(text) {
	        var newlineRegex = /\n/g;
	        var matches = text.match(newlineRegex);
	        var totalRows = matches ? matches.length + 1 : 1;
	        return totalRows;
	    }

		function priceFormat(price) {
            price = price > 0 ? parseFloat(price).toFixed(2): 0.00;
            return moneyFormatter.format(price);
        }

		function calculateMarkup(e,quote_id,type = 'quote') {
			var new_table_data = [];
			var quates_ids 		= [];
			$('.item_row').each(function(value) {
				var item_id 	= $(this).data('id');

				var tableData = {};
				tableData['item_id'] 	= item_id;

				$(this).find(".column_single_price").each(function() {
					var quote_id 	= $(this).data('quote');
					if (jQuery.inArray(quote_id, quates_ids) === -1) {
						quates_ids.push(quote_id);
					}
					var single_price_key = "single_price_sc" + quote_id;
					var total_price_key = "total_price_sc" + quote_id;
				//	tableData[i][single_price_key] = parseFloat($('.base_single_price_sc_input_'+item_id+'_'+quote_id).val());
					var converte_input = $('.base_single_price_sc_input_'+item_id+'_'+quote_id).val();
					tableData[single_price_key] = converte_input;
					var converte_total = $('.total_price_sc_input_'+item_id+'_'+quote_id).val();
					tableData[total_price_key] = converte_total;
				});
				new_table_data.push(tableData);
			});
            let markup = $(e).val();
			markup = parseLocaleNumber(moneyFormat,markup);
            let old = $(e).attr("data-old");
            if(quote_id > 0 && markup != old){

                $.ajax({
                    url: "{{route('estimations.markup.calculate')}}",
                    type: "POST",
                    contentType: "application/json",
                    data: JSON.stringify({
                        type: type,
                        markup:markup,
                        quote_id:quote_id,
                        estimation_id:estimation_id,
                        data:new_table_data
                    }, replacerFunc()),
                    success: function(response) {

 						for (let i = 0; i < response.length; i++) {
							$.each(quates_ids, function(key,val) {
								if(quote_id == val) {
									let key_class = 'single_price_sc_input_'+response[i].item_id+'_'+val;
									let key = 'single_price_sc'+val;
									if (typeof response[i][key] != "undefined") {
										$('.'+key_class).val(response[i][key]);
									//	changePrice($('.'+key_class).closest("tr"), false);
									}
								}
							});
						}

                        setTimeout(function (){
                            $(e).attr("data-old",markup);
							update_all_prices();
							saveTableDataMultiple();
                        },1000);
                    },
                    error: function(error) {
                        // Handle any errors that occur during the Ajax request
                        console.error("Error sending data to the server:", error);
                    }
                });
            }
        }

		function update_all_prices() {
			var totals 			= {};
			var last_group_id = "";
			var group_index 	= 0;
			var aData 			= [];
			var quates_ids 		= [];
			$(".item_row").each(function(i) {
				var type 		= $(this).data('type');
				var group_pos 		= $(this).data('group_pos');
				var group_id 		= $(this).data('group_id');
				if (type == "item") {
					var item_id 	= $(this).data('id');
					var quantity 	= $('.quantity_input_'+item_id).val();
					quantity 		= parseLocaleNumber(moneyFormat,quantity);
					var is_optional = $('.optional_checkbox_'+item_id).is(":checked");

					if (last_group_id != group_id) {
						last_group_id = group_id;
						group_index++;
					}
					
					if (typeof aData[group_index] == 'undefined') {
						aData[group_index] = new Array();
						aData[group_index].group_id = group_id;
						aData[group_index].rows = [];
						aData[group_index].total_prices = [];
					}
					aData[group_index].rows.push(i); 
					price_details = {};
	
					$(this).find(".column_single_price").each(function() {
						var quote_id 	= $(this).data('quote');

						if (jQuery.inArray(quote_id, quates_ids) === -1) {
							quates_ids.push(quote_id);
						}


						let key 		= 'total_price_sc'+quote_id;
						// var markup 		= $('#markup_sc'+ quote_id).val();
						// var base_price 	= $('.base_single_price_sc_input_'+item_id+'_'+ quote_id).val();
						// if(base_price == "") {
						// 	$('.base_single_price_sc_input_'+item_id+'_'+ quote_id).val(0);
						// 	base_price 	= 0;
						// }

						if ($('.single_price_sc_input_'+item_id+'_'+ quote_id).length > 0) {
							// var devided_markup = parseFloat(markup) / 100;
							// var new_price = parseFloat(base_price) + (parseFloat(base_price) * parseFloat(devided_markup));
							// $('.single_price_sc_input_'+item_id+'_'+ quote_id).val(new_price);
	
							var price 		= $('.single_price_sc_input_'+item_id+'_'+ quote_id).val();
							price = parseLocaleNumber(moneyFormat,price);
							var totalPrice = 0;
							if (is_optional != true) {
								var totalPrice = parseFloat(price) * parseFloat(quantity);
							}
							var new_total = moneyFormatter.format(parseFloat(totalPrice));
							price_details[key] = parseFloat(totalPrice);
							$('.total_price_sc_input_'+item_id+'_'+ quote_id).val(parseFloat(totalPrice));
							$('.tot_price_'+item_id+'_'+quote_id).html(new_total);
						//	$('#markup_sc'+ quote_id).attr("data-old",markup);
							
							if (!isNaN(totalPrice)) {
								if(Object.keys(totals).includes(key)){
									totals[key] += parseFloat(totalPrice);
								}else{
									totals[key] = parseFloat(totalPrice);
								}
							}
						}
					});
					aData[group_index].total_prices.push(price_details);
				}
			});

			let all_prices = totals;
			
			var idx= 0;
			var group_pos = 1;
			for (var group in aData) {
				$.each(quates_ids, function(key,val) {
					 let total_price_key = "total_price_sc"+val;
					 var sum = 0;
					if ($('.quote_th'+val).length > 0) {
						$.each(aData[group].total_prices,function(k,v){
							sum = sum + v[total_price_key];
						});
					}
					
					$('.group_row[data-group_id="'+aData[group].group_id+'"]').find('.grouptotal[data-quote_id="'+val+'"]').html(moneyFormatter.format(sum));
					$('.group_row[data-group_id="'+aData[group].group_id+'"]').find('.grouptotal[data-quote_id="'+val+'"]').attr('data-group_total',sum);
				});
			}

			$($(".group_row").get().reverse()).each(function() {
				var group_id = $(this).data('group_id');
				var parent_id = $(this).data('parent_id');
				$.each(quates_ids, function(key2,quate_id) {
					var group_total 	= $('.group_row[data-group_id="'+group_id+'"]').find('.grouptotal[data-quote_id="'+quate_id+'"]').attr('data-group_total');
					$('.group_row[data-group_id="'+group_id+'"]').find('.grouptotal[data-quote_id="'+quate_id+'"]').html(moneyFormatter.format(group_total));
					var parent_total 	= $('.group_row[data-group_id="'+parent_id+'"]').find('.grouptotal[data-quote_id="'+quate_id+'"]').attr('data-group_total');
					var group_sub_total = 0;
					group_sub_total = parseFloat(group_total) + parseFloat(parent_total);
					$('.group_row[data-group_id="'+parent_id+'"]').find('.grouptotal[data-quote_id="'+quate_id+'"]').html(moneyFormatter.format(group_sub_total));
					$('.group_row[data-group_id="'+parent_id+'"]').find('.grouptotal[data-quote_id="'+quate_id+'"]').attr('data-group_total',group_sub_total);
					if(quate_id == 987 && group_id == 790){
						console.log(group_total);
					}
				});
			});

			//update Total Price
			$.each(all_prices, function(index) {
				var split_index = index.split('total_price_sc');
				let id =  split_index[1];
                let key = "total_price_sc"+id;
                let net_sc = 0;
				if (typeof all_prices[key] != "undefined") {
					net_sc = all_prices[key];
				}

				let vat_sc 		= 0;
				let discount_sc = 0;
				if ($('#'+"tax_sc"+id).length > 0) {
					vat_sc = document.getElementById("tax_sc"+id).value;
				}
				if ($('#'+"discount_sc"+id).length > 0) {
					discount_sc = document.getElementById("discount_sc"+id).value;
					discount_sc = parseLocaleNumber(moneyFormat,discount_sc);
				}
                let netWithDiscount_sc = net_sc - (net_sc * (discount_sc/100));
				let gross_sc = net_sc + (net_sc * (vat_sc/100));
                let grossWithDiscount_sc = gross_sc - (gross_sc * (discount_sc/100));

				if ($('#'+"net_sc"+id).length > 0) {
					document.getElementById('net_sc'+id).innerHTML = moneyFormatter.format(net_sc);
					document.getElementById('net_sc'+id+"_value").value = net_sc;
					document.getElementById('net_with_discount_sc'+id).innerHTML = moneyFormatter.format(netWithDiscount_sc);
					document.getElementById('net_with_discount_sc'+id+"_value").value = netWithDiscount_sc;
					document.getElementById('gross_sc'+id).innerHTML = moneyFormatter.format(gross_sc);
					document.getElementById('gross_sc'+id+"_value").value = gross_sc;
					document.getElementById('gross_with_discount_sc'+id).innerHTML = moneyFormatter.format(grossWithDiscount_sc);
					document.getElementById('gross_with_discount_sc'+id+"_value").value = grossWithDiscount_sc;
				}
			});

			// apply color scale (color range total prices)
			$('#estimation-edit-table tbody tr').each(function() {
                var priceCells = $(this).find('td.column_total_price');
                var prices = priceCells.map(function() {
                    return parseFloat($(this).text().replace(/[^0-9,.-]+/g,"").replace(',', '.'));
                }).get();
                var maxVal = Math.max.apply(Math, prices);
                var minVal = Math.min.apply(Math, prices);
                var range = maxVal - minVal;

                priceCells.each(function() {
                    var cellValue = parseFloat($(this).text().replace(/[^0-9,.-]+/g,"").replace(',', '.'));
                    $(this).css('background-color', getColorForValue(cellValue, minVal, range));

                    if (cellValue < 0) {
                        $(this).css('color', '#CC0000');
                    } else {
                        $(this).css('color', ''); 
                    }

                    if (cellValue === 0) {
                        $(this).addClass('zero-value');
                    } else {
                        $(this).removeClass('zero-value');
                    }
                });
            });
			
			setTimeout(function () {
				// updatePOS();
				// handleSaveOrder();
				//saveTableDataMultiple();
			},1000);
		}

		function handleCheckboxChange(checkbox, quoteId, role = null) {
    if (role === 'sub_contractor' && checkbox.checked) {
        document.getElementById('client_quote_checkbox').checked = false;
        removeCellColors(quoteId, 'client');
    } else if (role === 'client' && checkbox.checked) {
        document.getElementById('sub_contractor_quote_checkbox').checked = false;
        removeCellColors(quoteId, 'sub_contractor');
    }

    if (checkbox.checked) {
        updateCellColors(quoteId, role, true);
    } else {
        updateCellColors(quoteId, role, false);
    }
}



		function removeCellColors(id, type = null) {
			if (id > 0) {
				if (type === 'sub_contractor') {
					$(`.sc${id},.gross_sc${id},.gross_with_discount_sc${id},.net_sc${id},.net_with_discount_sc${id},.discount_sc${id},.tax_sc${id},.markup_sc${id},.finalize_quote${id},.finalize_quote_title${id}`).removeClass('finalized_sub_contractor_quote');
				} else if (type === 'client') {
					$(`.sc${id},.gross_sc${id},.gross_with_discount_sc${id},.net_sc${id},.net_with_discount_sc${id},.discount_sc${id},.tax_sc${id},.markup_sc${id},.finalize_quote${id},.finalize_quote_title${id}`).removeClass('finalized_client_quote');
				} else {
					$(`.sc${id},.gross_sc${id},.gross_with_discount_sc${id},.net_sc${id},.net_with_discount_sc${id},.discount_sc${id},.tax_sc${id},.markup_sc${id},.finalize_quote${id},.finalize_quote_title${id}`).removeClass('finalized_quote');
					if (!$(`.finalize_quote_title${id}`).hasClass('total-main-title')) {
						$(`.finalize_quote_title${id}`).addClass('total-main-title');
					}
				}
			} else {
				$(`.1,.gross,.gross_with_discount,.net,.net_with_discount,.discount,.tax,.markup`).css("background",'#f5f5f5');
			}
		}


		function updateCellColors(id,type = null, update = true) {
            $("#totals-table td.totalnr").css("background",'#f5f5f5');
            $("#totals-table td.cotitle").css("background",'rgb(229, 229, 229)');
            if(update && id > 0){
				var form_details = {id: id};
				if(type && type != null){
					Object.assign(form_details, {type: type});
				}
                $.ajax({
                    url: "{{route('estimations.quote.final')}}",
                    type: "POST",
                    contentType: "application/json",
                    data: JSON.stringify(form_details),
                    success: function(response) {
						if (type === null) {
							var new_final_id = response.id;
							$(`.sc${final_id},.gross_sc${final_id},.gross_with_discount_sc${final_id},.net_sc${final_id},.net_with_discount_sc${final_id},.discount_sc${final_id},.tax_sc${final_id},.markup_sc${final_id},.finalize_quote${final_id},.finalize_quote_title${final_id}`).removeClass('finalized_quote');
							if(!$(`.finalize_quote_title${final_id}`).hasClass('total-main-title')){
								$(`.finalize_quote_title${final_id}`).addClass('total-main-title');
							}
							final_id = new_final_id;
							updateCellColors(new_final_id, type, false);
						}else{
							if (type === 'sub_contractor') {
								$(`.sc${sub_contractor_final_quote_id},.gross_sc${sub_contractor_final_quote_id},.gross_with_discount_sc${sub_contractor_final_quote_id},.net_sc${sub_contractor_final_quote_id},.net_with_discount_sc${sub_contractor_final_quote_id},.discount_sc${sub_contractor_final_quote_id},.tax_sc${sub_contractor_final_quote_id},.markup_sc${sub_contractor_final_quote_id},.finalize_quote${sub_contractor_final_quote_id},.finalize_quote_title${sub_contractor_final_quote_id}`).removeClass('finalized_sub_contractor_quote');
								sub_contractor_final_quote_id = id;
							} else if (type === 'client') {
								$(`.sc${client_final_quote_id},.gross_sc${client_final_quote_id},.gross_with_discount_sc${client_final_quote_id},.net_sc${client_final_quote_id},.net_with_discount_sc${client_final_quote_id},.discount_sc${client_final_quote_id},.tax_sc${client_final_quote_id},.markup_sc${client_final_quote_id},.finalize_quote${client_final_quote_id},.finalize_quote_title${client_final_quote_id}`).removeClass('finalized_client_quote');
								client_final_quote_id = id;
							}
							updateCellColors(id, type, false);
						}
                    },
                    error: function(error) {
                        // Handle any errors that occur during the Ajax request
                        console.error("Error sending data to the server:", error);
                    }
                });
            }else{
                if(id> 0){
					if (type === 'sub_contractor') {
						$(`.sc${id},.gross_sc${id},.gross_with_discount_sc${id},.net_sc${id},.net_with_discount_sc${id},.discount_sc${id},.tax_sc${id},.markup_sc${id},.finalize_quote${id},.finalize_quote_title${id}`).addClass('finalized_sub_contractor_quote');
					} else if (type === 'client') {
						$(`.sc${id},.gross_sc${id},.gross_with_discount_sc${id},.net_sc${id},.net_with_discount_sc${id},.discount_sc${id},.tax_sc${id},.markup_sc${id},.finalize_quote${id},.finalize_quote_title${id}`).addClass('finalized_client_quote');
					} else {
						// $(`.sc${id},.gross_sc${id},.gross_with_discount_sc${id},.net_sc${id},.net_with_discount_sc${id},.discount_sc${id},.tax_sc${id},.markup_sc${id},.finalize_quote${id}`).attr('style', 'background: #5eb839 !important');
						$(`.sc${id},.gross_sc${id},.gross_with_discount_sc${id},.net_sc${id},.net_with_discount_sc${id},.discount_sc${id},.tax_sc${id},.markup_sc${id},.finalize_quote${id},.finalize_quote_title${id}`).addClass('finalized_quote');
						if($(`.finalize_quote_title${id}`).hasClass('total-main-title')){
							$(`.finalize_quote_title${id}`).removeClass('total-main-title');
						}
					}
                }else{
                    $(`.1,.gross,.gross_with_discount,.net,.net_with_discount,.discount,.tax,.markup`).css("background",'#5eb839');
                }
            }
        }

		function duplicateColumn(id,title,sub_contractor_id,markup,edit=false,type='quote') {
			let modal_title = (edit) ? "Change Name" : "Duplicate";
            markup = $("#markup_sc"+id).val();
			markup = parseLocaleNumber(moneyFormat,markup);
            let btn = (edit) ? "Update" : "Create";
            let options  = "";
            for (contractor of all_contractors) {
				let selected = edit && sub_contractor_id == contractor.id ? "selected" :"";
                options += `<option value="${contractor.id}" ${selected} data-type="${contractor.type}"> ${contractor.name}</option>`;
            }
            if(sub_contractor_id > 0){
				let contractor = all_contractors.find(item => item.id == sub_contractor_id);
            //    title = contractor.name;
			//	title = '';
            }
		//	var current_title = $(".sc"+id+"_value").val();
            let html = `<div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">${modal_title}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="javascript:void(0)" id="clone-form" onsubmit="addColumn(${edit})">
                    <div class="modal-body">
                        <input type="hidden" name="clone_id" id="clone-id" value="${id}">
                        <div class="form-group">
                            <label for="">Title</label>
                            <input type="text" name="title" id="title" class="form-control" value="${title}" placeholder="Enter Title">
                            <input type="hidden" name="type" id="type" class="form-control" value="${type}">
                        </div>
                        <div class="form-group">
                            <h3 class="text-center">OR</h3>
                        </div>
                        <div class="form-group selct2-custom">
                            <label for="">{{ __('Contact') }}</label>
                            <select name="sub_contractor" id="sub-contractor" class="form-control">
                            <option value=""  data-type="">{{ __('Select') }} {{ __('Contact') }}</option>
                                ${options}
                            </select>
							<input type="hidden" name="markup" id="mark-up" class="form-control" value="${markup}">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">${btn}</button>
                    </div>
                    </form>`;
            $("#modal-content").html(html);
            $("#duplicateModal").modal('show');

            if($('#sub-contractor').length > 0) {
				$('#sub-contractor').select2({
					dropdownParent: $("#duplicateModal"),
					tags: true,
	                allowHtml: true,
	                templateResult: formatState,
					createTag: function(params) {
						return {
							id: params.term,
							text: 'Create New'
						}
					}
				});
			}
        }


        // $(document).on('change', '#sub-contractor', function(event) {
        // 	event.preventDefault();
        	
        // 	var title = $('#title').val();
        // 	if(title == '' || title == null){
        //         title = $.trim($("#sub-contractor option:selected").text());
        //         $('#title').val(title)
        // 	}
        // });

		function addColumn(edit=false) {
            let sub_contractor = $("#sub-contractor").val();
            let title = $("#title").val();
            if(title == "" && sub_contractor == ""){
                toastrs("Error","Enter Title or select Sub Contractor");
                return;
            }

            if(edit){
                let clone_id = $("#clone-id").val();
                let markup = $("#mark-up").val();
                if(sub_contractor > 0){
                //    title = $("#sub-contractor option:selected").text();
                }

				var new_table_data = [];

				$('.item_row').each(function(value){
					var tableData = {};

					var item_id 				= $(this).data('id');

					var converte_quantity 				= "0";
					if($('.quantity_input_'+item_id).length > 0){
						converte_quantity 				= $('.quantity_input_'+item_id).val();
					}
					tableData['quantity'] 		= parseLocaleNumber(moneyFormat,converte_quantity);
					tableData['name'] 			= $('.name_input_'+item_id).val();
					tableData['comment'] 		= $('.comment_input_'+item_id).val();
					tableData['unit'] 			= $('.unit_input_'+item_id).val();
					tableData['description'] 	= $('.description_input_'+item_id).val();
					tableData['optional'] 		= $('.optional_checkbox_'+item_id).is(":checked");
					tableData['pos'] 			= $('.pos_input_'+item_id).val();
					tableData['estimation_product_id'] = item_id;

					$(this).find(".column_single_price").each(function() {
						var quote_id 	= $(this).data('quote');
						var single_price_key = "single_price_sc" + quote_id;
						if ($('.'+single_price_key).length > 0 && $('.single_price_sc_input_'+item_id+'_'+quote_id).length > 0) {
							var total_price_key = "total_price_sc" + quote_id;
							var converte_input = $('.single_price_sc_input_'+item_id+'_'+quote_id).val();
							tableData[single_price_key] = parseLocaleNumber(moneyFormat,converte_input);
							var converte_total = $('.total_price_sc_input_'+item_id+'_'+quote_id).val();
							tableData[total_price_key] = converte_total;
						}
					});
					new_table_data.push(tableData);
				});

                $.ajax({
                    url: "{{route('estimations.duplicate.quote.edit')}}",
                    type: "POST",
                    contentType: "application/json",
                    data: JSON.stringify({
						type: 'quote',
						title:title,
                        sub_contractor:sub_contractor,
                        markup:markup,
                        quote_id: clone_id,
                        estimation_id:estimation_id,
                        data:new_table_data
                    }, replacerFunc()),
                    success: function (response) {
						setTimeout(function () {
                           $("#duplicateModal").modal('hide');
						   location.reload();
                        },500)
                    }
                });
            } else {
				let formdata = $("#clone-form").serialize();
                $.ajax({
                    url: "{{route('estimations.duplicate.quote')}}",
                    type: "POST",
                    // contentType: "application/json",
                    data: formdata+"&estimation_id="+estimation_id,
                    success: function (response) {
						location.reload()
						let title = response.title;
						let quote_id = response.quote.id;
						let quote = response.quote;
						all_quotes = response.all_quotes;

						$("#duplicateModal").modal('hide');
                    },
                    error: function (error) {
                        // Handle any errors that occur during the Ajax request
                        console.error("Error sending data to the server:", error);
                    }
                });
            }
        }

		function replacerFunc() {
			const visited = new WeakSet();
				return (key, value) => {
				if (typeof value === "object" && value !== null) {
					if (visited.has(value)) {
					return;
					}
					visited.add(value);
				}
				return value;
				};
		}

        var timeout;

        $('#estimation-edit-table').on('input', 'tbody td', function() {
			var className = $(this).attr('class');
			var class_array = className.split(' ');

			if(jQuery.inArray('column_checkbox', class_array) !== -1) {
			// if (className == "column_checkbox"){
				return false;
			} else if(jQuery.inArray('grouptitle', class_array) !== -1) {
				return false;
			} else {
				var $this = this;
				var td_tag = "";

				if ($(this).parents('td').length > 0) {
					td_tag = $(this).parents('td');
				} else {
					td_tag = $(this);
				}

				if (td_tag.find('.div-desc-toggle').length > 0) {
					if (td_tag.find('.div-desc-toggle').find('.fa-spinner').length == 0) {
						td_tag.find('.div-desc-toggle').append("<i class='fa fa-spinner fa-spin'></i>");
					}
				}

				var item_id = $(this).parents('.item_row').data('id');
				
				setTimeout(function () {
					saveTableDataMultiple(item_id);
				},1000);
			}
        });

		$(document).on('change', '#estimation-edit-table textarea', function(event) {
			var item_id = $(this).parents('.description_row').data('id');
			setTimeout(function () {
				saveTableDataMultiple(item_id);
			},1000);
		});

        function taxInfo(){
        	let estimation_title = document.getElementById("estimation_title").value;
			let issue_date = document.getElementById("issue_date").value;

			let all_gross = {};
            let all_gross_with_discount = {};
            let all_net = {};
            let all_net_with_discount = {};
            let all_tax = {};
            let all_discount = {};
            let all_markup = {};
			var all_quotes2 = [];
			
            for (const quote of all_quotes) {
				if ($('#'+`gross_sc${quote.id}_value`).length > 0) {
					all_gross[`gross_sc${quote.id}`] = document.getElementById(`gross_sc${quote.id}_value`).value;
					all_gross_with_discount[`gross_with_discount_sc${quote.id}`] = document.getElementById(`gross_with_discount_sc${quote.id}_value`).value;
					all_net[`net_sc${quote.id}`] = document.getElementById(`net_sc${quote.id}_value`).value;
					all_net_with_discount[`net_with_discount_sc${quote.id}`] = document.getElementById(`net_with_discount_sc${quote.id}_value`).value;
					all_tax[`tax_sc${quote.id}`] =  document.getElementById(`tax_sc${quote.id}`).value;
					var all_discounts = document.getElementById(`discount_sc${quote.id}`).value;
					all_discount[`discount_sc${quote.id}`] =  parseLocaleNumber(moneyFormat,all_discounts);
					var all_markups = document.getElementById(`markup_sc${quote.id}`).value;
					all_markup[`markup_sc${quote.id}`] =  parseLocaleNumber(moneyFormat,all_markups);
					all_quotes2.push({ "id": quote.id });
				}
            }

            return {

                estimation_id:estimation_id,
                estimation_title:estimation_title,
				issue_date:issue_date,
                all_quotes : all_quotes2,
                all_gross : all_gross,
                all_net : all_net,
                all_gross_with_discount : all_gross_with_discount,
                all_net_with_discount : all_net_with_discount,
                all_tax : all_tax,
                all_discount : all_discount,
                all_markup : all_markup,

            }
        }


		function saveTableData(action, tableData = null) {

			if(action == "preview" && final_id < 1){
                toastrs("Error","At least One Quote mark as finalize");
                return;
            }

            if(action == 'preview'){
                window.location.href = '{{route("estimations.finalize.estimate",encrypt($estimation->id))}}';
                return true
			}

			var new_table_data = [];
				var quantity_input 			= tableData[0].quantity_input;
				var item_id 				= $(quantity_input).data('id');

			//	tableData[0].quantity 		= parseFloat($('.quantity_input_'+item_id).val());
				var converte_quantity 		= $('.quantity_input_'+item_id).val();
				tableData[0].quantity 		= parseLocaleNumber(moneyFormat,converte_quantity);
				tableData[0].name 			= $('.name_input_'+item_id).val();
				tableData[0].comment 		= $('.comment_input_'+item_id).val();
				tableData[0].unit 			= $('.unit_input_'+item_id).val();
				tableData[0].description 	= $('.description_input_'+item_id).val();
				tableData[0].optional 		= $('.optional_checkbox_'+item_id).is(":checked");
				tableData[0].pos 			= $('.pos_input_'+item_id).val();

				for (const quote of all_quotes) {
					var single_price_key = "single_price_sc" + quote.id;
					if ($('.'+single_price_key).length > 0 && $('.single_price_sc_input_'+item_id+'_'+quote.id).length > 0) {
						var total_price_key = "total_price_sc" + quote.id;
					//	tableData[0][single_price_key] =$('.single_price_sc_input_'+item_id+'_'+quote.id).val();
						var converte_input = $('.single_price_sc_input_'+item_id+'_'+quote.id).val();
						tableData[0][single_price_key] = parseLocaleNumber(moneyFormat,converte_input);
						var converte_total = $('.total_price_sc_input_'+item_id+'_'+quote.id).val();
						tableData[0][total_price_key] = converte_total;
					}
				}
				new_table_data.push(tableData[0]);
		
			var taxInfos = taxInfo();

            let data = {
            	...taxInfos,
                project_estimation_product_id:item_id,
                items : new_table_data
            }

            // Send the table data to the server using Ajax
            $.ajax({
              url: "{{route('estimations.save_finalize.estimate')}}",
              type: "POST",
              contentType: "application/json",
              data: JSON.stringify(data, replacerFunc()),
              success: function(response) {
                // Handle the server response, if needed
                  if(action == 'stay'){
                      
					// estimation_table.settings()[0].oFeatures.bServerSide = false;
                    // estimation_table.draw(false)
					// estimation_table.settings()[0].oFeatures.bServerSide = true;
                  }else{
                      window.location.href = '{{route("estimations.finalize.estimate",encrypt($estimation->id))}}';
                  }
              },
              error: function(error) {
                // Handle any errors that occur during the Ajax request
                console.error("Error sending data to the server:", error);
              }
            });
        }

        function saveTableDataMultiple(save_item_id = 0) {

			var new_table_data = [];

			$('.item_row').each(function(value) {
				
				var item_id 						= $(this).data('id');
				
				if (save_item_id == 0 || save_item_id == item_id ) {
					var tableData = {};

					var converte_quantity 				= "0";
					if($('.quantity_input_'+item_id).length > 0){
						converte_quantity 				= $('.quantity_input_'+item_id).val();
					}
					console.log(converte_quantity);
					tableData['quantity'] 				= parseLocaleNumber(moneyFormat,converte_quantity);
					tableData['name'] 					= $('.name_input_'+item_id).val();
					tableData['comment'] 				= $('.comment_input_'+item_id).val();
					tableData['unit'] 					= $('.unit_input_'+item_id).val();
					tableData['description'] 			= $('.description_input_'+item_id).val();
					tableData['optional'] 				= $('.optional_checkbox_'+item_id).is(":checked");
					tableData['pos'] 					= $('.pos_input_'+item_id).val();
					tableData['estimation_product_id'] 	= item_id;

					for (const quote of all_quotes) {
						var single_price_key = "single_price_sc" + quote.id;
						if ($('.'+single_price_key).length > 0 && $('.single_price_sc_input_'+item_id+'_'+quote.id).length > 0) {
							var total_price_key = "total_price_sc" + quote.id;
						//	tableData[i][single_price_key] = $('.single_price_sc_input_'+item_id+'_'+quote.id).val();
							var converte_input = $('.single_price_sc_input_'+item_id+'_'+quote.id).val();
							tableData[single_price_key] = parseLocaleNumber(moneyFormat,converte_input);
							var converte_total = $('.total_price_sc_input_'+item_id+'_'+quote.id).val();
							tableData[total_price_key] = converte_total;
						}
					}
					new_table_data.push(tableData);
				}
			});

			var taxInfos = taxInfo();
			
			let data = {
				...taxInfos,
				items : new_table_data,
				multiple:true
			}

			if (save_item_id == 0) {
				$('#save-button').html('{{ __("Saving") }} <i class="fa fa-spinner fa-spin"></i>').addClass('saving');

				setTimeout(function() {
					// $('#save-button').html('{{ __("Save") }}').removeClass('saving');
				}, 2000);
			} else {
				data.multiple = false;
			}

			// Send the table data to the server using Ajax
			$.ajax({
			url: "{{route('estimations.save_finalize.estimate')}}",
			type: "POST",
			contentType: "application/json",
			data: JSON.stringify(data, replacerFunc()),
			success: function(response) {
				if (save_item_id == 0) {
					$('#save-button').html('{{ __("Saved") }} <i class="fa fa-circle-check"></i>').removeClass('saving');
					setTimeout(function(){
						$('#save-button').html('{{ __("Save") }}')
					}, 3000);
				}

				if (save_item_id > 0) {
					$('.item_row[data-id="'+save_item_id+'"] td .fa-spinner').remove();
				}
			},
			error: function(error) {
				// Handle any errors that occur during the Ajax request
				console.error("Error sending data to the server:", error);
			}
			});
		}

        $(document).on('change', '.grouptitle-input', function(event) {
			event.preventDefault();
			var newGroupName = $(this).val();
			// var pos = $(this).parent('td').siblings().eq(2).text();
			var group_id = $(this).parents('tr').data('group_id');
			var data = {
				grpname: newGroupName,
				estimation_id: estimation_id,
				group_id: group_id,
			}

			$.ajax({
				url: "{{route('estimations.updateGrpname')}}",
				type: "POST",
				data: data,
				success: function (response) {
					// Aktualisieren Sie den Gruppennamen direkt, ohne die Tabelle neu zu zeichnen
					if (response.success) {
						// Nehmen Sie an, dass die Antwort ein 'success'-Feld enthält
						$(event.target).val(newGroupName);
					}
				}
			});
		});

		$(document).on('change', '#estimation_title, #issue_date, #technical_description', function(event) {
        	event.preventDefault();
        	
        	var estimation_title = $('#estimation_title').val();
        	var issue_date = $('#issue_date').val();
			// var technical_description = $('#technical_description').val();
			tinyMCE.triggerSave();

        var technical_description = tinymce.get("technical_description").getContent({ format: "html" });

        	$.ajax({
                url: "{{route('estimations.saveEstimationTitle')}}",
                type: "POST",
                data: {
                    estimation_id:estimation_id,
                    estimation_title:estimation_title,
                    issue_date:issue_date,
					technical_description:technical_description,
                },
                success: function (response) {
                }
            });
        });

		function updatePOS() {
        	$.ajax({
                url: "{{route('update.estimation.pos')}}",
                type: "POST",
                data: {
					estimation_id:estimation_id
                },
                success: function (response) {
					if(response.status == true) {
						$(".item_row").each(function(index) {
							if (typeof response.data[index]!=='undefined'){
								var item_id = $(this).data('id');
								$(this).find('.pos-inner').text(response.data[index]);
								$('.pos_input_'+item_id).val(response.data[index]);
							}
						});
					}
                }
            });
        }

		function handleSaveOrder() {
			let project_estimation_id = $('#estimation-edit-table').find('tr.item_row').map(function() {
							var id = $(this).attr('data-id');
							var group_pos = $(this).attr('data-group_pos');
							var group_id = $(this).attr('data-group_id');

							return id + "_" + group_id + "_" + group_pos;
						}).toArray();

			// return false;
        	$.ajax({
                url: "{{route('estimations.pos.ordering')}}",
                type: "POST",
                data: {
					estimation_id:estimation_id,
                    project_estimation_id:project_estimation_id,
					item_html:true
                },
                success: function (response) {
					if(response.status == true) {
						$(".item_row").each(function(index) {
							if (typeof response.all_new_pos[index]!=='undefined'){
								var item_id = $(this).data('id');
								$(this).find('.pos-inner').text(response.all_new_pos[index]);
								$('.pos_input_'+item_id).val(response.all_new_pos[index]);
							}
						});
					}
                }
            });
        }

		function deleteColumn(id,e) {
            $.ajax({
                url: "{{route('estimations.delete.quote')}}",
                type: "POST",
                // contentType: "application/json",
                data: {
                    quote_id:id,
                    estimation_id:estimation_id,
                },
                success: function (response) {
                	location.reload()
                }
            });
        }

		// Function to add an item row dynamically
		function addItem(item_with_group = false) {
			var token = $('meta[name="csrf-token"]').attr('content');

			$.ajax({
				url: "{{route('estimations.add_item.estimate')}}",
				type: "POST",
				contentType: "application/json",
				data: JSON.stringify({estimation_id:estimation_id,item_with_group:item_with_group,item_html:true,ai_description_field:ai_description_field}),
				success: function(response) {
					if (response.status == true) {
						// toastrs("Success",response.message);
						$("#estimation-edit-table tbody").append(response.html_data);
					} else {
						toastrs("Error",response.message);
					}					
				},
				error: function(error) {
					// Handle any errors that occur during the Ajax request
					console.error("Error sending data to the server:", error);
				}
			});
        }

		// Function to add an Comment row dynamically
		function addComment() {
			var token = $('meta[name="csrf-token"]').attr('content');

			$.ajax({
				url: "{{route('estimations.add_comment.estimate')}}",
				type: "POST",
				contentType: "application/json",
				data: JSON.stringify({estimation_id:estimation_id,item_html:true,ai_description_field:ai_description_field}),
				success: function(response) {
					if (response.status == true) {
						// toastrs("Success",response.message);
						$("#estimation-edit-table tbody").append(response.html_data);
                  	} else {
						toastrs("Error",response.message);
					}					
				},
				error: function(error) {
					// Handle any errors that occur during the Ajax request
					console.error("Error sending data to the server:", error);
				}
			});
        }

				// Function to add an Comment row dynamically
		function store_group_reorder(is_refresh = false) {
			var nestable_data = $('#nestable').nestable('serialize');
			var token = $('meta[name="csrf-token"]').attr('content');

			$.ajax({
				url: "{{route('estimations.store_group_reorder')}}",
				type: "POST",
				contentType: "application/json",
				data: JSON.stringify({_token: token,nestable_data:nestable_data,estimation_id:estimation_id}),
				success: function(response) {
					if (response.status == true) {
						// toastrs("Success",response.message);
						if(is_refresh == true) {
							location.reload();
						}
                  	} else {
						toastrs("Error",response.message);
					}					
				},
				error: function(error) {
					// Handle any errors that occur during the Ajax request
					console.error("Error sending data to the server:", error);
				}
			});
        }

		function selected_quote_items() {
			var total_selected = 0;
			var total_grp_selected = 0;
			var item_ids = [];
			var group_ids = [];
			$('.item_selection').each(function () {

				if ($(this).prop('checked')==true){
					total_selected++;
					var item_id = $(this).val();
					item_ids.push(item_id);
				}
			});
			$('.group_checkbox').each(function () {
				if ($(this).prop('checked')==true){
					total_grp_selected++;
					var group_id = $(this).val();
					group_ids.push(group_id);
				}
			});
			if(total_selected > 0 || total_grp_selected > 0){
				$('.btn_replace_descriptions').removeClass('d-none');
				$('.ai_fields').removeClass('d-none');
				$('.remove_items_btn').removeClass('d-none');
			} else {
				$('.btn_replace_descriptions').addClass('d-none');
				$('.ai_fields').addClass('d-none');
				$('.remove_items_btn').addClass('d-none');
			}
			$('#remove_item_ids').val(JSON.stringify(item_ids));
			$('#remove_group_ids').val(JSON.stringify(group_ids));
			$('#duplicate_item_ids').val(JSON.stringify(item_ids));
			$('#duplicate_group_ids').val(JSON.stringify(group_ids));

		}

        function getColorForValue(value, min, range) {
            var valueScaled = (value - min) / range;
            var green = [120, 25, 92]; 
            var white = [0, 0, 100];  
            var red = [0, 25, 92];   

            var color = [0, 0, 0];

            if (valueScaled < 0.5) {
                var midPoint = valueScaled * 2; // Skaliere auf [0, 1]
                color[0] = green[0] + (white[0] - green[0]) * midPoint;
                color[1] = green[1] + (white[1] - green[1]) * midPoint;
                color[2] = green[2] + (white[2] - green[2]) * midPoint;
            } else {
                var midPoint = (valueScaled - 0.5) * 2; // Skaliere auf [0, 1]
                color[0] = white[0] + (red[0] - white[0]) * midPoint;
                color[1] = white[1] + (red[1] - white[1]) * midPoint;
                color[2] = white[2] + (red[2] - white[2]) * midPoint;
            }

            return `hsl(${color[0]}, ${color[1]}%, ${color[2]}%)`;
        }

        $('#table-search').on('keyup', function() {
			var input, filter, table, tr, td, i;
			input = document.getElementById("table-search");
			filter = input.value.toUpperCase();
			table = document.getElementById("estimation-edit-table");
			tr = table.getElementsByTagName("tr");

			// Loop through all table rows, and hide those who don't match the search query
			for (i = 0; i < tr.length; i++) {
				if ($(tr[i]).attr('class')=='item_row') {

				}
				td = tr[i].getElementsByTagName("td") ; 
				for(j=0 ; j<td.length ; j++)
				{
					let tdata = td[j] ;
					if (tdata) {
						if (tdata.innerHTML.toUpperCase().indexOf(filter) > -1) {
							console.log('display', tr[i]);
							tr[i].style.display = "";
							break ; 
						} else {
							tr[i].style.display = "none";
						}
					} 
				}
			}
        });

       // Column Visibility Dropdown 
        var columnVisibilityControl = $('<select></select>')
            .appendTo('.column_visibility')
            .on('change', function() {
                var colIndex = $(this).val();
                var visibility = estimation_table.column(colIndex).visible();
                estimation_table.column(colIndex).visible(!visibility);
            });


		$(document).on('click','.call-ai-smart-template',function(e){
			e.preventDefault();

			var btnText = $(this).html();
			var currentV = $(this);	
			currentV.prop('disabled', true);

			var $btn = currentV.html('<div class="fa fa-spinner fa-spin"></div>');

			var inputLength = $('.item_selection:checked').length;
			if(inputLength == 0){

				toastrs("Error","Please select at least one record");
				currentV.prop('disabled',false);
				currentV.html(btnText);
				return false;
			}

			var item_ids = $('#remove_item_ids').val();
			var smart_template_id = $('#smart_template_id').val();
			var token = $('meta[name="csrf-token"]').attr('content');
			var ai_ajax_url = "{{route('estimations.call-ai-smart-template-new')}}";

			// return false;

			$.ajax({
				url: ai_ajax_url,
				type: "POST",
				contentType: "application/json",
				data: JSON.stringify({
					estimation_id:estimation_id,
					item_ids:item_ids,
					smart_template_id:smart_template_id,
					_token: token
				}),
				success: function(response) {
					if (response.status == true) {
						toastrs("Success",response.message);
						setTimeout(function () {
							location.reload()
						},1000);
					} else {
						toastrs("Error",response.message);
						currentV.prop('disabled',false);
						currentV.html(btnText);
						// return false;
					}					
				},
				error: function(error) {
					currentV.prop('disabled',false);
					currentV.html(btnText);
					toastrs("Error","Failed: something went wrong!");
				}
			});
		});

		$(document).on("click", ".btn_replace_descriptions", function(e) {
			e.preventDefault();
			var token = $('meta[name="csrf-token"]').attr('content');
			var replace_ai_desc_ids = $("#remove_item_ids").val();

			$.ajax({
				url: "{{route('estimations.replace_ai_desc.estimate')}}",
				type: "POST",
				contentType: "application/json",
				data: JSON.stringify({estimation_id:estimation_id,replace_ai_desc_ids:replace_ai_desc_ids, _token: token}),
				success: function(response) {
					if (response.status == true) {
						toastrs("Success",response.message);
						setTimeout(function () {
                           location.reload();
                        },500);
					} else {
						toastrs("Error",response.message);
					}					
				},
				error: function(error) {
					// Handle any errors that occur during the Ajax request
					console.error("Error sending data to the server:", error);
				}
			});
		});

		function check_progress() {
            execute_request = false;
            $.ajax({
                url: "{{route('smart.progress')}}",
                type: "POST",
                contentType: "application/json",
                success: function(response) {
                    execute_request = true;
                    if (response.status == true) {
                        $.each(response.data, function(project_id, row) {
                            $.each(row.estimations_list, function(estimation_id, list) {
                                $.each(list.estimation_queues_list, function(key, queue) {
                                    var selector = $('.project_block[data-id="'+project_id+'"] .estimation_block[data-id="'+estimation_id+'"] .queue_progress[data-id="'+queue.smart_template_id+'"]')
                                    if (selector.length > 0) {
                                        selector.find('.progress-bar').css('width', queue.completed_percentage+'%');
                                        selector.find('.progress-bar').text(queue.completed_percentage+'%');
                                    }                                    
                                });
                            });
                        });
                    } else {
                        console.log(response);
                    }					
                },
                error: function(error) {
                    execute_request = true;
                    // Handle any errors that occur during the Ajax request
                    console.error("Error sending data to the server:", error);
                }
            });
        }

		function copyToClipboard(element) {
            var $temp = $("<input>");
            $("body").append($temp);
            $temp.val($(element).text()).select();
            document.execCommand("copy");
            $temp.remove();

            toastrs('{{ __('Success') }}', '{{ __('Copied to Clipboard!') }}', '{{ __('Success') }}')
        }

		
document.addEventListener('DOMContentLoaded', function() {
    document.body.addEventListener('click', function(event) {
        const target = event.target;
        // Erkenne Klicks auf alle Anzeige-Divs (div-view)
        if (target.classList.contains('div-view')) {
            toggleVisibility(target);
        } else {
            // Schließe alle Eingaben, wenn außerhalb der bearbeitbaren Bereiche geklickt wird
            if (!target.classList.contains('edit-view')) {
                closeAllInputs(); 
            }
        }
    });
});

function toggleVisibility(div) {
    const input = div.nextElementSibling; // Das zugehörige Eingabeelement
    if (div.classList.contains('show')) {
        div.classList.replace('show', 'hide');
        input.classList.replace('hide', 'show');
        input.focus(); // Fokus setzen, wenn das Eingabefeld angezeigt wird
    } else {
        div.textContent = input.value; // Aktualisiere den Text im Div mit dem Eingabewert
        div.classList.replace('hide', 'show');
        input.classList.replace('show', 'hide');
    }
}

function closeAllInputs() {
    document.querySelectorAll('.div-view.hide').forEach(function(div) {
        const input = div.nextElementSibling;
        if (input && input.classList.contains('show')) {
            toggleVisibility(div); // Schließe die Eingabe und zeige das Div an
        }
    });
}
</script>

	
@endpush
