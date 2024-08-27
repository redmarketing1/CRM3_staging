@extends('layouts.main')
@php
    $profile=asset(Storage::url('uploads/avatar'));
@endphp
@section('page-title')
	{{__('Imported Estimation')}}
@endsection
@section('title')
	{{__('Imported Estimation Data')}}
@endsection
@push('css')
	<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
	<link rel="stylesheet" href="{{ asset('css/common.css') }}" type="text/css" />
@endpush
@section('page-breadcrumb')
<a href="{{route('projects.index')}}">{{ __('All Project') }}</a>, {{__('Imported Estimation Data')}}
@endsection

@section('page-action')

@endsection
@push('css')
	
@endpush
@push('scripts')
	

@endpush
@section('content')
    <div class="row">
		<div class="col-xl-12">
			<div class="card">
				<div class="card-header card-body table-border-style">
					{{ Form::open(array('route' => 'estimations.store_import', 'files' => true)) }}
					<input type="hidden" name="title" value="{{ $project_estimation['title'] }}">
					<input type="hidden" name="estimation_id" value="{{ $project_estimation['estimation_id'] }}">
					<input type="hidden" name="project_id" value="{{ $project_estimation['project_id'] }}">
					<input type="hidden" name="issue_date" value="{{ $project_estimation['issue_date'] }}">
					<input type="hidden" name="table_data" value="{{ json_encode($project_estimation['products']) }}">
					<input type="hidden" name="total_single_prices" value="{{ $project_estimation['total_single_prices'] }}">

					<div class="table-responsive">
						<table class="table table-responsive">
							<thead>
							<tr>
								@foreach($project_estimation['table_rows'] as $table_key =>$table_row)
									<th>
										<select name="selected_row[{{$table_key}}]" class="form-control">
											@foreach($project_estimation['table_rows'] as $key => $row)
												<option value="{{$row}}" @if($key == $table_key) selected @endif>{{ ucfirst($row) }}</option>
											@endforeach
										</select>
									</th>
								@endforeach
							</tr>
							</thead>
							<tbody>
								@foreach($project_estimation['products'] as $product_key => $product_value)
								@php
									$product_value =  array_map('utf8_decode',$product_value);
								@endphp
								<tr>
									@foreach($project_estimation['table_rows'] as $table_key => $table_row)
										<td>
											@if($table_row == "price" || $table_row == "price_1" || $table_row == "price_2" || $table_row == "price_3" || $table_row == "price_4" || $table_row == "price_5")
												{{ currency_format_with_sym($product_value[$table_row]) }}
											@else
												{{ $product_value[$table_row] }}
											@endif
										</td>
									@endforeach
								</tr>
								@endforeach
							</tbody>
						</table>
					</div>
					<div class="row">
						<div class="col-md-12 text-right">
							<div class="form-group mt-4">
								<a class="btn  btn-light" href="{{route("estimations.index")}}">{{ __('Back') }}</a>
								{{Form::submit(__('Submit'),array('class'=>'btn mx-3 btn-primary'))}}
							</div>
						</div>
					</div>
					{{ Form::close() }}
				</div>
			</div>
		</div>
    </div>
@endsection
@section("script")
	
@endsection
