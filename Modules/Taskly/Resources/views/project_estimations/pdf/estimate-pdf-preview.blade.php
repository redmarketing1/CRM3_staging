<!DOCTYPE html>
<html>
	<head>
		<title>{{__('Estimation PDF Template')}}</title>
		<style>
			.table {
				border-collapse: collapse;
				/* Merge table borders */
				width: 100%;
				/* Set table width */
				margin: 0 auto;
				/* Center the table horizontally in its container */
				background-color: transparent;
				color: #333;
			}

			.th,
			.td {
				padding: 7px 15px;
				/* Adjust cell padding */
				border-spacing: 5px;
				/* Adjust cell spacing */
				/*border: 1px solid #ddd; !* Add borders to table cells *!*/
				/*text-align: left; !* Align text within cells *!*/
				word-wrap: break-word;
				text-align: left;
				/* Center-align content in cells */
				font-family: Arial, sans-serif;
				/* Specify font family */
				font-size: 12px;
				/* Set font size */
				vertical-align: top;
			}

			.th {
				background-color: #77777721;
				/* Background color for header cells */
				font-weight: bold;
				/* Bold text for headers */
				font-size: 12px;

			}

			.tr {
				border-bottom: 1px dotted #d5d5d5;
			}

			.tr:last-child {
				border-bottom: none;
			}

			* {
				font-family: Arial, sans-serif;
				/* Specify font family */
			}

			h3 {
				font-size: 16px;
				border-bottom: 2px solid;
				margin: 20px 0 10px 0;
				padding: 0 0px 5px 0;
			}

			.coverpage {
				table-layout: fixed;
				background: #FFF;
			}

			.coverpage td,
			.coverpage p {
				font-size: 12px;
			}

			.coverpage td {
				padding-left: 20px;
				padding-right: 20px;
				vertical-align: top;
				word-break: break-all;
				overflow-wrap: break-word;
			}

			.coverpage td .field {
				word-break: break-all !important;
				overflow-wrap: break-word !important;
				white-space: normal !important;
				max-width: 100% !important;
			}

			.caption {
				font-size: 16px;
				font-weight: bold;
			}

			.page-break {
				page-break-after: always;
			}

			.field {
				width: 100%;
			}

			h4 {
				white-space: nowrap;
			}

			.ctotal {
				font-size: 16px;
				font-weight: bold;
				margin: 10px 0;
			}

			.ctotal.sum {
				white-space: nowrap;
				vertical-align: bottom;
			}

			.footer {
				border-top: 1px solid #000;
				position: fixed;
				left: 0;
				bottom: 0;
				width: 100%;
				text-align: left;
				font-size: 10px;
			}

			.watermark {
				position: fixed;
				left: -8%;
				/* Horizontale Positionierung */
				top: 0;
				/* Vertikale Positionierung */
				opacity: 0.3;
				/* Transparenz */
				z-index: -1;
				/* Unter den Inhalt legen */
			}

			/*.calctable {border-collapse: collapse;}
	.calctable td, .calctable th {border: 1px solid #999 !important}*/

			td .estimation-no {
				word-break: break-all !important;
				overflow-wrap: break-word !important;
				white-space: normal !important;
				width: 230% !important;
			}
		</style>
	</head>
	<body>

		<table style="border-collapse: collapse; width: 100%; font-family: Arial; font-size: 12px; margin-bottom: 50px;" border="0"
			class="page-break coverpage">
			<tbody>
				<tr class="logo-estimate-title-class">
					<td style="width: 25%;" colspan="2" class="logo-class">
						<img class="img-fluid mb-3" src="{{ get_file(sidebar_logo()) }}"
							alt="Dashboard-kit Logo">
						<h4 class="estimation-pdf-title">{{ $estimation->title }}</h4>
						<div class="field mt-2 estimation-no">
							{{__('Estimation No')}} #1{{$estimation->id}}
						</div>
					</td>
					<td style="width: 25%; text-align: right;" colspan="2" class="construction-addrrss-class">
						<strong>{{ isset($company_details['company_name']) ? $company_details['company_name'] : '' }}<br />
						</strong>{{ $company_details['company_address'] . ',' . $company_details['company_zipcode'] .' '. $company_details['company_city'] }}<br />{{ $company_details['company_telephone'] }}<br />{{ $company_details['company_email'] }}<br /> @if(isset($company_details['company_website']) && !empty($company_details['company_website'])) {{$company_details['company_website'] }} @endif
					</td>
				</tr>
				<tr class="estimate-pdf-top-class">
					<td style="padding:50px 20px; vertical-align:top;width: 25%;" colspan="4">
						<textarea name="pdf_top_notes" id="pdf_top_notes" class="form-control tinyMCE" cols="30" rows="10">{!! $estimatePdfTopTemplate ?? $estimatePdfTopTemplate !!}</textarea>
					</td>
				</tr>
				<tr class="label-auftraggeber-planer">
					<td style="width: 25%;" colspan="2" class="label-auftraggeber">
						<h3 style=""><strong>{{__('Client')}}</strong></h3>
					</td>
					<td style="width: 25%;" colspan="2" class="label-planer">
						<h3 style=""><strong>{{__('Planner')}}</strong></h3>
					</td>
				</tr>
				<tr class="auftraggeber-details">
					@php
						$project = $estimation->project();
					@endphp
					<td style="width: 25%;">
						<div class="field">{{ isset($client->company_name) ? $client->company_name : '' }}</div>
						<div class="field">{{ isset($client->salutation) ? __($client->salutation) : '' }} {{ isset($client_name) ? $client_name : '' }}</div>
						<div class="field">{{ isset($client->address_1) ? $client->address_1 : '' }}</div>
						<div class="field">{{ isset($client->zip_code) ? $client->zip_code : '' }}
							{{ isset($client->city) ? $client->city : '' }}</div>
						<div class="field">{{ isset($client->district) ? $client->district : '' }}</div>
						<div class="field">{{ isset($client->state) ? $client->state : '' }}</div>
						<div class="field">{{ isset($client->countryDetail->name) ? $client->countryDetail->name : '' }}</div>
					</td>
					<td style="width: 25%;">
						<div class="field">{{ isset($client->mobile) ? $client->mobile : '' }}</div>
						<div class="field">{{ isset($client_email) ? $client_email : '' }}</div>
					</td>
					<td style="width: 25%;">
						<div class="field">{{-- {{planer.name}} --}}</div>
						<div class="field">{{-- {{planer.street}} --}}</div>
						<div class="field">{{-- {{planer.zip}} {{planer.city}} --}}</div>
						<div class="field">{{-- {{planer.country}} --}}</div>
					</td>
					<td style="width: 25%;">
						<div class="field">{{-- {{planer.contact}} --}}</div>
						<div class="field">{{-- {{planer.phone}} --}}</div>
						<div class="field">{{-- {{planer.email}} --}}</div>

					</td>
				</tr>
				<tr class="label-projekt-zusammenfassung">
					<td style="width: 25%;" colspan="2" class="label-projekt">
						<h3 style=""><strong>{{__('Project')}}</strong></h3>
					</td>
					<td style="width: 25%;" colspan="2" class="label-zusammenfassung">
						<h3 style=""><strong>{{__('Summary')}}</strong></h3>
					</td>
				</tr>
					
				<tr class="project-estimate-details">
					<td style="width: 25%;" class="project-name-title">
						<div class="field">{{ isset($project->construction_detail->company_name) ? $project->construction_detail->company_name : '' }}</div>
						<div class="field">{{ isset($project->construction_detail->salutation) ? __($project->construction_detail->salutation) : '' }} 
							{{ !empty($project->construction_detail->first_name) ? $project->construction_detail->first_name : '' }}
							{{ !empty($project->construction_detail->last_name) ? $project->construction_detail->last_name : '' }}
						</div>
						<div class="field">{{ !empty($project->construction_detail->address_1) ? $project->construction_detail->address_1 : '' }}</div>
						<div class="field">{{ !empty($project->construction_detail->zip_code) ? $project->construction_detail->zip_code : '' }}
							{{ !empty($project->construction_detail->city) ? $project->construction_detail->city : '' }}</div>

						@if(!empty($project->construction_detail->district))
							<div class="field">
								{{ $project->construction_detail->district ?? ''}}
							</div>
						@endif
						@if(!empty($project->construction_detail->state))
							<div class="field">
								{{ $project->construction_detail->state ?? ''}}
							</div>
						@endif
						@if(!empty($project->construction_detail->countryDetail))
							<div class="field">
								{{ $project->construction_detail->countryDetail->name ?? ''}}
							</div>
						@endif
						<div class="project-title mt-2"><b>{{ __('Project') }}: {{ $estimation->project()->name }}</b></div>
					</td>
					<td style="width: 25%;">
						<div class="field">{{ isset($project->construction_detail->mobile) ? $project->construction_detail->mobile : '' }}</div>
						<div class="field">{{ isset($estimation->project()->construction_detail->email) ? $estimation->project()->construction_detail->email : '' }}</div>
					</td>

					<td style="width: 50%;" colspan="2" class="project-total-labels">

						<div class="totalrow row-subtotal-net">
							<div class="label label-subtotal-net">{{__('Subtotal (net)')}}:</div>
							<div class="total total-subtotal-net">{{ currency_format_with_sym($quote->net) }}</div>
						</div>

						<div class="totalrow row-discount-net">
							<div class="label label-discount-net">{{__('Discount (net)')}}:</div>
							<div class="total total-discount-net">{{ currency_format_with_sym(0) }}</div>
						</div>

						<div class="totalrow row-total-net">
							<div class="label label-total-net">{{__('Total (net)')}}:</div>
							<div class="total total-total-net">{{ currency_format_with_sym($quote->net) }}</div>
						</div>

						<div class="totalrow row-total-net-discount">
							<div class="label label-total-net-discount">{{__('Total (net) incl. cash discount')}}:</div>
							<div class="total total-total-net-discount">{{ currency_format_with_sym($quote->net_with_discount) }}</div>
						</div>

						<div class="totalrow row-tax">
							<div class="label label-tax">{{__('plus')}} {{ $quote->tax }}% {{__('VAT')}}:</div>
							<div class="total total-tax">{{ currency_format_with_sym($quote->gross - $quote->net) }}</div>
						</div>

						<div class="totalrow row-total-gross">
							<div class="label label-total-gross">{{__('Total (gross)')}}:</div>
							<div class="total total-total-gross">{{ currency_format_with_sym($quote->gross) }}</div>
						</div>

						<div class="totalrow row-discount-gross">
							<div class="label label-discount-gross">{{__('Cash Discount')}} {{ $quote->discount }}% (7 {{__('calendar days')}}):</div>
							<div class="total total-discount-gross">{{ currency_format_with_sym($quote->gross - $quote->gross_with_discount) }}</div>
						</div>

						<div class="totalrow row-total-gross-discount">
							<div class="label label-total-gross-discount">{{__('Total (gross) incl. cash discount')}}</div>
							<div class="total total-total-gross-discount">{{ currency_format_with_sym($quote->gross_with_discount) }}</div>
						</div>
					</td>
				</tr>
			</tbody>
		</table>
			
		<h4 style="margin-bottom: 20px; font-weight: normal;" class="estim-address-text-info"><b>{{ $estimation->title }}</b> -
			{{ $estimation->project()->name }} - {{ isset($client->address_1) ? $client->address_1 : '' }}
		</h4>

		<table style="width: 100%;margin:0 0 100px 0; " class="table calctable estimation-preview-table">
			<thead>
				<tr class="tr" style="border-bottom: 2px solid #FFF;">
					<th class="th">#</th>
					<th class="th">{{__('Description')}}</th>
					<th class="th">{{__('Quantity')}}</th>
					<th class="th" style="text-align:right;">EP (@if(isset($site_money_format) && $site_money_format == "en_US")$@else€@endif)</th>
					<th class="th" style="text-align:right;">GP (@if(isset($site_money_format) && $site_money_format == "en_US")$@else€@endif)</th>
				</tr>
			</thead>
			<tbody>
				@php
					$total = 0;
					
					$group = '';
					$quate_items = array();
					foreach($quote->quoteItem()->orderBy('id')->get() as $key => $q_item) {
						$quate_items[$q_item->product_id] = $q_item;
					}
				@endphp
				@foreach ($estimation->estimation_groups()->orderBy('group_pos')->get() as $key => $item_group)
					@php 
						$subtotal = 0;
					@endphp

					@if ($group != $item_group->group_name)
						<tr class="tr" style="; border-bottom: none;">
							<td class="th">{{ $item_group->group_pos }}</td>
							<td class="th" style="text-align: left;margin-left: 150px" colspan="4">
								<div style="width:100%;font-size: 14px; padding: 5px 0;"> {{ $item_group->group_name }}</div>
							</td>
						</tr>
					@endif
					@foreach ($item_group->estimation_products as $key2 => $item)
						@php 
							$item_name          = $item->name;
							$item_total_price   = isset($quate_items[$item->id]->total_price) ? $quate_items[$item->id]->total_price : 0;
							$single_price   = isset($quate_items[$item->id]->price) ? $quate_items[$item->id]->price : 0;
							if ($item->is_optional == 0) {
								$item_name          = __('Optional').': '.$item->name;
								$item_total_price   = 0;
							}
							$subtotal += $item_total_price;
						@endphp
						@if ($item->type == 'item')
							<tr class="tr">
								<td class="td column_pos">{{ $item->pos }}</td>
								<td class="td column_name">
									<div style="width: 100%;" class="name"> <b> {{ $item_name }}</b></div>
									<div class="description">
										{{ ($item->description) }}
									</div>
								</td>
								<td class="td column_quantity" style="text-align: right; vertical-align: bottom;">
									<div style="white-space: nowrap;"> {{ number_format($item->quantity, 3, ',', '.') }}
										{{ $item->unit }}</div>
								</td>
								<td class="td column_price" style="text-align: right; vertical-align: bottom;">
									<div style="white-space: nowrap;"> {{ currency_format_with_sym($single_price) }}</div>
								</td>
								<td class="td column_total" style="text-align: right;  vertical-align: bottom;">
									<div style="white-space: nowrap;"> {{ currency_format_with_sym($item_total_price) }}</div>
								</td>
							</tr>
						@else
							<tr class="tr">
								<td class="td column_pos">{{ $item->pos }}</td>
								<td class="td column_name" colspan="4">{{ $item->name }}</td>
							</tr>
						@endif
						@php
							$total += $item->total_price; 
						@endphp
					@endforeach
					@if ($group != $item_group->group_name)
						<tr class="tr"
							style="background: transparent; border-top: 2px solid #000; border-bottom: none;; font-size: 16px;">
							<td class="td"></td>
							<td class="td" style="text-align: left;" colspan="2"><b>{{__('Subtotal')}}</b><br /><br />
							</td>
							<td class="td" colspan="2" style="text-align: right">
								<b>{{ currency_format_with_sym($subtotal) }}</b><br /><br /></td>
						</tr>
					@endif
					@php
						$group = $item_group->group_name;
					@endphp
				@endforeach

				@php
					$tax = $quote->gross - $total;
				@endphp
			</tbody>
		</table>
		<table style="width: 100%;margin:100px 0; ">
			<tr>
				<td style="font-size:14px; font-family:Arial; padding: 100px 50px;">
					<div>
						<label for="">{{__('Extra Notes')}}</label>
						<textarea name="extra_notes" id="extra_notes" class="form-control" cols="30" rows="10">{!! $estimatePdfEndTemplate ?? $estimatePdfEndTemplate !!}</textarea>
					</div>
				</td>
			</tr>
		</table>
		<table>
			<tr>

				<td>
					<div class="footer-top" style="font-size: 13px;">Ein Angebot der Neuwest GmbH | www.neu-west.com |
						030 232 55 74 70</div>
					<div class="footer-bottom" style="">Dieses Dokument ist geistiges Eigentum der Neuwest GmbH
						® [EU-Trademark] und ist ausschließlich für den adressierten Empfänger bestimmt. Die Weitergabe
						an Dritte und Mitbewerber oder andere unbefugte Nutzung ist nicht gestattet. Die in diesem
						Dokument genannten Preise sind vorläufig, nicht verbindlich und basieren auf ersten Schätzungen
						(Irrtümer und Änderungen sind vorbehalten). Vor einer endgültigen Beauftragung erfolgt eine
						abschließende Kostenprüfung. Jegliche Beauftragung unterliegt dieser Prüfung.
					</div>
				</td>
				<td style="padding-left: 30px;font-size: 12px;">
					#/#
				</td>

			</tr>
		</table>
	</body>
</html>
