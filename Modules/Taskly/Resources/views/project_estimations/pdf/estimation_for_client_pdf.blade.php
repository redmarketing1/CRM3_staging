<!DOCTYPE html>
<html>
<head>
	@php
		$pdf_title = $estimation->title . ' - ' . $project->name;
		if(isset($project->construction_detail->address_1)) {
			$pdf_title .= ' - ' . $project->construction_detail->address_1;
		}
		if(isset($project->construction_detail->city)) {
			$pdf_title .= ' - ' . $project->construction_detail->city;
		}
		$pdf_title .= ' - #1' . $estimation->id . ' - ' . $company_details['company_name'];
	@endphp
    <title>{{ $pdf_title }}</title>
    <style>
        .table {
            border-collapse: collapse; /* Merge table borders */
            width: 100%; /* Set table width */
            margin: 0 auto; /* Center the table horizontally in its container */
            background-color: transparent;
            color: #333;
        }

        .th, .td {
            padding: 7px 15px; /* Adjust cell padding */
            border-spacing: 5px; /* Adjust cell spacing */
            /*border: 1px solid #ddd; !* Add borders to table cells *!*/
            /*text-align: left; !* Align text within cells *!*/
            word-wrap: break-word;
            text-align: left; /* Center-align content in cells */
            font-family: Arial, sans-serif; /* Specify font family */
            font-size: 12px; /* Set font size */
            vertical-align: top;
        }
        .th {
            background-color: #77777721; /* Background color for header cells */
            font-weight: bold; /* Bold text for headers */
            font-size: 12px;

        }
        .tr {
            border-bottom: 1px dotted #d5d5d5;
        }
        .tr:last-child {
            border-bottom: none;
        }
        * {
            font-family: Arial, sans-serif; /* Specify font family */
        }
        h3 {font-size: 16px; border-bottom: 2px solid; margin: 20px 0 10px 0; padding: 0 0px 5px 0;}
        .coverpage
        {
            table-layout: fixed;
            background: #FFF;
        }
        .coverpage td, .coverpage p {
            font-size: 12px;
        }
        .coverpage td
        {
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
        .field {width: 100%;}
        h4 {white-space: nowrap;}
        .ctotal {font-size: 16px; font-weight: bold; margin: 10px 0;}
        .ctotal.sum {white-space: nowrap; vertical-align: bottom;}

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
			left: -8%;  /* Horizontale Positionierung */
			top: 0;   /* Vertikale Positionierung */
			opacity: 0.3;  /* Transparenz */
			z-index: -1;  /* Unter den Inhalt legen */
		}

		/*.calctable {border-collapse: collapse;}
		.calctable td, .calctable th {border: 1px solid #999 !important}*/
        td .estimation-no {
            word-break: break-all !important;
            overflow-wrap: break-word !important;
            white-space: normal !important;
            width: 230% !important;
        }
		.totalrow .label {
			text-align: left;
			font-weight: normal;
		}

		.totalrow .total {
			text-align: right;
			min-width: 110px;
			font-weight: normal;
		}

		.row-total-net-discount, .row-total-gross-discount {
			font-size: 16px;
			font-weight: bold;
			margin: 10px 0;
			line-height: 20px;
		}
		.totalrow .label-total-gross, 
		.totalrow .total-total-gross,
		.totalrow .label-subtotal-net, 
		.totalrow .total-subtotal-net,
		.label-total-net-discount,
		.total-total-net-discount,
		.label-total-gross-discount,
		.total-total-gross-discount
		{
			font-weight: bold !important;
		}
    </style>
</head>
<body>
<div class="watermark">
	<img src="https://neu-west.com/CRM/public/assets/images/neuwest-watermark.png">
</div>

<table style="border-collapse: collapse; width: 100%; font-family: Arial; font-size: 12px;" border="0"  class="page-break coverpage">
    <tbody>
		<tr class="logo-estimate-title-class">
			<td style="width: 25%;" colspan="2" class="logo-class">
				<img class="img-fluid mb-3" src="{{ get_file(sidebar_logo()) }}"
					alt="Dashboard-kit Logo">
				<h4 style="font-weight:bold; font-size: 25px;margin-top:50px; margin-bottom:10px;">{{ $estimation->title }}</h4>
				<div class="field mt-2 estimation-no">
							{{__('Estimation No')}} #1{{$estimation->id}}
				</div>
			</td>
			<td style="width: 25%; text-align: right;" colspan="2" class="construction-addrrss-class">
				<strong>{{ isset($company_details['company_name']) ? $company_details['company_name'] : '' }}<br />
				</strong>{{ $company_details['company_address'] . ',' . $company_details['company_zipcode'] .' '. $company_details['company_city'] }}<br />{{ $company_details['company_telephone'] }}<br />{{ $company_details['company_email'] }}<br /> @if(isset($company_details['company_website']) && !empty($company_details['company_website'])) {{ $company_details['company_website'] }} @endif
			</td>
		</tr>
		<tr>
			<td style="padding:50px 20px; vertical-align:top;width: 25%;" colspan="4">
				@if(isset($pdfTopNotes) && !empty($pdfTopNotes))
					{!! $pdfTopNotes !!}
				@endif
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
			<td style="width: 25%;">
				<div class="field">
					{{ isset($client->company_name) ? $client->company_name : '' }}
				</div>
				<div class="field">
				{{ isset($client->salutation) ? __($client->salutation) : '' }}
				{{ isset($client->first_name) ? $client->first_name : '' }}
				{{ isset($client->last_name) ? $client->last_name : '' }}                
				</div>
				<div class="field">{{ isset($client->address_1) ? $client->address_1 : '' }}</div>
				<div class="field">{{ isset($client->zip_code) ? $client->zip_code : '' }}
					{{ isset($client->city) ? $client->city : '' }}</div>
				<div class="field">{{ isset($client->district) ? $client->district : '' }}</div>
				<div class="field">{{ isset($client->state) ? $client->state : '' }}</div>
				@if(!empty($client->countryDetail) && isset($client->countryDetail))
					<div class="field">{{!empty($client->countryDetail) ? ' '. $client->countryDetail->name : '' }}</div>
				@endif
			</td>
			<td style="width: 25%;">
				<div class="field">{{ isset($client->mobile) ? $client->mobile : '' }}</div>
				<div class="field">{{ isset($client->email) ? $client->email : '' }}</div>
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
			<td style="width: 25%;">
			<div class="field">
				{{ isset($project->construction_detail->company_name) ? $project->construction_detail->company_name : '' }}
				</div>
				<div class="field">
				{{ isset($project->construction_detail->salutation) ? __($project->construction_detail->salutation) : '' }}
				{{ isset($project->construction_detail->first_name) ? $project->construction_detail->first_name : '' }}
				{{ isset($project->construction_detail->last_name) ? $project->construction_detail->last_name : '' }}                
				</div>
				<div class="field">{{ isset($project->construction_detail->address_1) ? $project->construction_detail->address_1 : '' }}</div>
				<div class="field">{{ isset($project->construction_detail->zip_code) ? $project->construction_detail->zip_code : '' }}
					{{ isset($project->construction_detail->city) ? $project->construction_detail->city : '' }}</div>
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
				<div class="project-title mt-2" style="margin-top:20px;"><b>{{ __('Project') }}: {{ $estimation->project()->title }}</b></div>
				@if ($contractor)
					<div class="field">{{ isset($client_name) ? $client_name : '' }}</div>
					<div class="field">{{ $project->location }}</div>
				@endif
			</td>

			<td style="width: 25%;" class="project-address">
				@if(!empty($project->construction_detail->mobile) && isset($project->construction_detail->mobile))
					<div class="field">{{ $project->construction_detail->mobile }}</div>
				@endif
				@if(!empty($project->construction_detail->email) && isset($project->construction_detail->email))
					<div class="field">{{ $project->construction_detail->email }}</div>
				@endif
			</td>

			<td style="width: 50%;" colspan="2" class="project-total-labels">

				<div class="totalrow row-subtotal-net" style="overflow: hidden; font-weight: bold;">
					<div class="label label-subtotal-net" style="float: left; width: 50%;">{{__('Subtotal (net)')}}:</div>
					<div class="total total-subtotal-net" style="float: right; width: 50%; text-align: right;">{{ currency_format_with_sym($quote->net) }}</div>
					<div style="clear: both;"></div>
				</div>

				<div class="totalrow row-discount-net" style="overflow: hidden; font-weight: bold;">
					<div class="label label-discount-net" style="float: left; width: 50%;">{{__('Discount (net)')}}:</div>
					<div class="total total-discount-net" style="float: right; width: 50%; text-align: right;">{{ currency_format_with_sym(0) }}</div>
					<div style="clear: both;"></div>
				</div>

				<div class="totalrow row-total-net" style="overflow: hidden; font-weight: bold;">
					<div class="label label-total-net" style="float: left; width: 50%;">{{__('Total (net)')}}:</div>
					<div class="total total-total-net" style="float: right; width: 50%; text-align: right;">{{ currency_format_with_sym($quote->net) }}</div>
					<div style="clear: both;"></div>
				</div>

				<div class="totalrow row-total-net-discount" style="overflow: hidden; font-weight: bold;">
					<div class="label label-total-net-discount" style="float: left; width: 50%;">{{__('Total (net) incl. cash discount')}}:</div>
					<div class="total total-total-net-discount" style="float: right; width: 50%; text-align: right;">{{ currency_format_with_sym($quote->net_with_discount) }}</div>
					<div style="clear: both;"></div>
				</div>

				<div class="totalrow row-tax" style="overflow: hidden; font-weight: bold;">
					<div class="label label-tax" style="float: left; width: 50%;">{{__('plus')}} {{ $quote->tax }}% {{__('VAT')}}:</div>
					<div class="total total-tax" style="float: right; width: 50%; text-align: right;">{{ currency_format_with_sym($quote->gross - $quote->net) }}</div>
					<div style="clear: both;"></div>
				</div>

				<div class="totalrow row-total-gross" style="overflow: hidden; font-weight: bold;">
					<div class="label label-total-gross" style="float: left; width: 50%;">{{__('Total (gross)')}}:</div>
					<div class="total total-total-gross" style="float: right; width: 50%; text-align: right;">{{ currency_format_with_sym($quote->gross) }}</div>
					<div style="clear: both;"></div>
				</div>

				<div class="totalrow row-discount-gross" style="overflow: hidden; font-weight: bold;">
					<div class="label label-discount-gross" style="float: left; width: 50%;">{{__('Cash Discount')}} {{ $quote->discount }}% (7 {{__('calendar days')}}):</div>
					<div class="total total-discount-gross" style="float: right; width: 50%; text-align: right;">{{ currency_format_with_sym($quote->gross - $quote->gross_with_discount) }}</div>
					<div style="clear: both;"></div>
				</div>

				<div class="totalrow row-total-gross-discount" style="overflow: hidden; font-weight: bold;">
					<div class="label label-total-gross-discount" style="float: left; width: 50%;">{{__('Total (gross) incl. cash discount')}}</div>
					<div class="total total-total-gross-discount" style="float: right; width: 50%; text-align: right;">{{ currency_format_with_sym($quote->gross_with_discount) }}</div>
					<div style="clear: both;"></div>
				</div>
			</td>
		</tr>
    </tbody>
</table>

@if(!empty($project_images_files))
    <div class="project-images" style="text-align: center; background:#FFF;">
        <h4 style="margin-bottom: 20px; font-weight: normal;" class="estim-address-text-info">
            <b>{{__('Project Images')}}</b>
        </h4>
        <!-- @foreach($project->files->sortBy('file') as $file)
            <div style="display: inline-block; vertical-align: top; padding:1px 0; margin-left:-2px; width:232px;">
                <img class="watermark-img" style="position:absolute; z-index:99; margin-left:5px; margin-top:5px; opacity:0.5;" src="https://neu-west.com/CRM/public/assets/images/neuwest-watermark-small.png">
                <img src="https://neu-west.com/CRM/storage/uploads/files/{{ $file->file }}" alt="Project Image" style="width: 100%; height: auto;">
            </div>
        @endforeach -->
		@foreach($project_images_files as $prow)
		@php 
			$fname = '';
        	$fname = str_replace(" ", "%20", $prow['file']);
		@endphp
			<div style="display: inline-block; vertical-align: top; padding:1px 0; margin-left:-2px; width:232px;">
				<img class="watermark-img" style="position:absolute; z-index:99; margin-left:5px; margin-top:5px; opacity:0.5;" src="https://neu-west.com/CRM/public/assets/images/neuwest-watermark-small.png">
				<img src="https://neu-west.com/CRM/storage/uploads/files/{{ $fname }}" alt="Project Image" style="width: 100%; height: auto;">
			</div>
		@endforeach
    </div>
@endif

<div style="page-break-before: always;"></div>
	<h4 style="margin-bottom: 20px; font-weight: normal;" class="estim-address-text-info"><b>{{ $estimation->title }}</b> -
		{{ $project->title }} - {{ isset($project->construction_detail) ? $project->construction_detail->address_1." - ".$project->construction_detail->city : "" }}
	</h4>
	<table style="width: 100%;margin:0 0 100px 0; " class="table calctable">
		<thead>
		<tr class="tr" style="border-bottom: 2px solid #FFF;">
			<th class="th">#</th>
			<th class="th">{{__('Description')}}</th>
			<th class="th">{{__('Quantity')}}</th>
			<th class="th" style="text-align:right;">{{__('Single Price')}} (@if(isset($site_money_format) && $site_money_format == "en_US")$@else€@endif)</th>
			<th class="th" style="text-align:right;">{{__('Total Price')}} (@if(isset($site_money_format) && $site_money_format == "en_US")$@else€@endif)</th>
		</tr>
		</thead>
		<tbody>
			@php
				$total = 0;
				$subtotal = 0;
				$group = "";

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
						<td class="th"><div style="width:100%;font-size: 14px; padding: 5px 0;"> {{ $item_group->group_pos }}</div></td>
						<td  class="th" style="text-align: left;margin-left: 150px" colspan="4"><div style="width:100%;font-size: 14px; padding: 5px 0;"> {{$item_group->group_name}}</div></td>
					</tr>
				@endif

				@foreach ($item_group->estimation_products as $item)
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
					@if($item->type == "item")
						<tr class="tr">
							<td class="td column_pos">{{$item->pos}}</td>
							<td class="td column_name"><div style="width: 100%;"><b>{{$item_name}}</b></div>
								{{$item->description}}
							</td>
							<td class="td column_quantity" style="text-align: right; vertical-align: bottom;"><div style="white-space: nowrap;"> {{number_format($item->quantity,3,",",".")}} {{$item->unit}}</div></td>
							<td class="td column_price" style="text-align: right; vertical-align: bottom;"><div style="white-space: nowrap;"> {{currency_format_with_sym($single_price)}}</div></td>
							<td class="td column_total" style="text-align: right;  vertical-align: bottom;"><div style="white-space: nowrap;"> {{currency_format_with_sym($item_total_price)}}</div></td>
						</tr>
					@else
						<tr class="tr">
							<td class="td column_pos">{{$item->pos}}</td>
							<td class="td column_name" colspan="4">{{$item->name}}</td>
						</tr>
					@endif
					@php
						$total += $item->total_price; 
					@endphp
				@endforeach
				@if ($group != $item_group->group_name)
					<tr class="tr" style="background: transparent; border-top: 2px solid #000; border-bottom: none;; font-size: 16px;">
						<td class="td" ></td>
						<td class="td" style="text-align: left;" colspan="2"><b>{{__('Subtotal')}}</b><br /><br />
						<td class="td" colspan="2" style="text-align: right"><b>{{currency_format_with_sym($subtotal)}}</b><br/><br/></td>
					</tr>
				@endif
				@php
					$group =  $item_group->group_name;
				@endphp
			@endforeach

			@php
				$tax = $quote->gross - $total;
			@endphp
		</tbody>
	</table>

	<table style="width: 100%;margin:100px 0; ">
		<tr>
			<td style="font-size:14px; font-family:Arial; padding: 100px 0;">
			@if(isset($extra_notes) && !empty($extra_notes))
				{!! $extra_notes !!}
			@endif
			</td>
		</tr>

	</table>
</div>

<div class="footer" style="background:#FFF;">
	<table>
		<tr>
			<td>
				<div class="footer-top" style="font-size: 13px;">Ein Angebot der Neuwest GmbH | www.neu-west.com | 030 232 55 74 70</div>
				<div class="footer-bottom" style="">Dieses Angebot der Neuwest GmbH ® [EU-Trademark] ist speziell für den Adressaten erstellt und vertraulich. Die Weitergabe an Dritte oder Mitbewerber ist nicht erlaubt. Die angegebenen Preise sind vorläufig und unverbindlich. Vor Auftragserteilung erfolgt eine finale Prüfung.</div>
			</td>
			<td style="padding-left: 30px;font-size: 12px;">
				<!-- #/# -->
			</td>
		</tr>
	</table>
</div>
</body>
</html>
