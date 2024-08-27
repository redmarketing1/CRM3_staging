@extends('layouts.main')
@section('page-title')
	{{__('Smart Progress')}}
@endsection
@section('page-breadcrumb')
	{{__('Smart Progress')}}
@endsection
@push('css')
<style>
	.CellWithComment{
		position:relative;
	}

	.CellComment{
		display:none;
		position:absolute; 
		z-index:100;
		border:2px;
		background-color:white;
		border-style:solid;
		border-width:1px;
		padding:3px;
		top:0px; 
		/* left:10px; */
		right:10px;
		width: max-content;
	}

	.CellWithComment:hover span.CellComment{
		display:block;
	}
</style>
@endpush

@section('page-action')

@endsection

@section('content')
	<div class="row">
		<div class="col-xl-12">
			<div class="card">
				<div class="card-header card-body table-border-style">
					<div class="table-responsive">
						<table class="table table-bordered smart-progress-table">
							<thead>
								<tr>
									<th>{{__('Date')}}</th>
									<th>{{__('Progress bar')}}</th>
									<th>{{__('Project Title')}}</th>
									<th>{{__('Estimation Title')}}</th>
									<th>{{__('Smart Template')}}</th>
								</tr>
							</thead>
							<tbody>
								@foreach($queue_result as $key => $row)
									@if (!empty($row['estimations_list']))
										@foreach($row['estimations_list'] as $erow)
											@if (!empty($erow['estimation_queues_list']))
												@foreach($erow['estimation_queues_list'] as $qrow)
													<tr>
														<td># add Date #</td>
														<td>
															<div class="project_block" data-id="{{$qrow['project_id']}}">
																<div class="estimation_block d-flex" data-id="{{$erow['estimation_id']}}">
																	@php
																		$progress_class = "bg-success";
																		$info_icon = "d-none";
																		if($qrow['cancelled_record'] > 0 || $qrow['error_record'] > 0) {
																			$progress_class = "bg-danger";
																			$info_icon = "";
																		}
																	@endphp
																	<div class="progress queue_progress" data-id="{{ $qrow['smart_template_id'] }}">
																		<div class="progress-bar {{ $progress_class }}" role="progressbar" style="width: {{ $qrow['completed_percentage'] }}%" aria-valuenow="{{ $qrow['completed_percentage'] }}" aria-valuemin="0" aria-valuemax="100">{{ $qrow['completed_percentage'] }}%</div>
																	</div>
																	<span class="CellWithComment {{ $info_icon }} p-1">
																		<i class="fa fa-info-circle "></i>
																		<span class="CellComment">{{ $qrow['error_message'] }}</span>
																	</span>
																</div>
															</div>
															@if($qrow['completed_percentage'] < 100 && $qrow['pending_record'] > 0)
																<div class="">
																	{!! Form::open(['method' => 'POST', 'route' => ['estimations.cancel_queue', \Crypt::encrypt($erow['estimation_id'])]]) !!}
																		<a href="javascript:void(0)" class="mx-3 btn btn-sm d-flex align-items-center show_confirm">
																			{{ __('Cancel Now') }}
																		</a>
																	{!! Form::close() !!}
																</div>
															@endif
														</td>
														<td><a href="{{route('projects.show', [$key])}}">{{ $row['project_title'] }}</a></td>
														
														<td><a href="{{ route('estimations.setup.estimate',\Crypt::encrypt($erow['estimation_id'])) }}">{{$erow['estimation_title']}}</a></td>
														<td>{{$qrow['smart_template_main_title']}}</td>
														
													</tr>
												@endforeach
											@endif
										@endforeach
									@endif
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
<script type="application/javascript">
	var execute_request = true;
	$(document).ready(function() {
		setInterval(function(){
			if (execute_request == true) {
				check_progress();
			}
		}, 3000);
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
								var selector = $('.project_block[data-id="'+project_id+'"] .estimation_block[data-id="'+estimation_id+'"] .queue_progress[data-id="'+queue.smart_template_id+'"] .progress-bar');
								if (selector.length > 0) {
									selector.css('width', queue.completed_percentage+'%');
									selector.text(queue.completed_percentage+'%');
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
				console.error("Error sending data to the server:", error);
			}
		});
	}
</script>

@endpush