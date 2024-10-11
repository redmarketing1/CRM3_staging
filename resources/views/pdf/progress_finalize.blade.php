<!DOCTYPE html>
<html>

<head>
    <title>{{__('Progress Finalize') }}</title>
    <style>
        .table {
            border-collapse: collapse;
            width: 100%;
            margin: 0 auto;
            background-color: transparent;
            color: #333;
        }

        .th,
        .td {
            padding: 7px 2px;
            border-spacing: 5px;
            word-wrap: break-word;
            text-align: left;
            font-family: Arial, sans-serif;
            font-size: 12px;
            vertical-align: top;
        }

        .th {
            background-color: #77777721;
            font-weight: bold;
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
        }

        .h3_title {
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

        .page-break {
            page-break-after: always;
        }

        .field {
            width: 100%;
        }

        h4 {
            white-space: nowrap;
        }

        .progress-history {
    display: flex;
    flex-wrap: wrap;
    gap: 2px;
}

table .progress-history-item {
    background: #f1f1f1;
    border-radius: 3px;
    padding: 2px 5px !important;
    line-height: 18px !important;
    color: #222 !important;
}
table .progress-history-item span:not(:last-child) {
    /* padding-right: 2px !important;
    margin-right: 2px !important; */
}
table .progress-history-item:last-child {
}
.last-progress-nr {
font-size: 18px;
font-weight: bold;
}
table.coverpage.table td {
    vertical-align: middle;
    width: auto !important;
}

table.coverpage.table .item-bottom td {text-align: center;}
table .last-progress-nr {
    font-size: 18px;
    font-weight: bold;
    border-radius: 5px;
    width: 71px;
    /* padding: 1px !important; */
    color: #000000c9;
}
table.coverpage .item-name, 
table.coverpage .item-pos {
    font-size: 14px;
    font-weight: bold;
    padding-top: 10px !important;
}
table.coverpage.table .item-top td {
    padding-top: 10px !important;
}
/* table.coverpage.table .item-bottom td {
    padding-bottom: 10px !important;
} */
table.coverpage .item-pos {
    white-space: nowrap !important;
    word-wrap: unset !important;
}
table.coverpage {
table-layout: unset;
}
table.coverpage .final-field {
    font-weight: bold;
    background: transparent;
    border-bottom: 2px dotted #ccc;
    font-size: 15px;
    text-align: left !important;
    padding: 5px 5px 0;
}
table.coverpage .final-details-bottom td {
    vertical-align: bottom;
}
.main-text p {font-size:14px;}


div[class*="progress-0"] {
    color: #ddd ; 
}
/* 
div[class*="progress-1"],
div[class*="progress-2"],
div[class*="progress-3"] {
    color: #d1e3e9 ; 
}

div[class*="progress-4"],
div[class*="progress-5"],
div[class*="progress-6"] {
    color: #aae9e2; 
}


div[class*="progress-7"],
div[class*="progress-8"],
div[class*="progress-9"] 
{
    color: #9ee4af; 
}

div[class*="progress-100"] {
    color: #66CC99 ;
} 
    */




td.pos-td[class*="progress-0"]
{
    background: #FFF;
}
td.pos-td[class*="progress-1"],
td.pos-td[class*="progress-2"],
td.pos-td[class*="progress-3"] {
    /* background: #d1e3e9;  */
    background: #f3eecf;
    background: #f3e9f6;
}

td.pos-td[class*="progress-4"],
td.pos-td[class*="progress-5"],
td.pos-td[class*="progress-6"] {
    background: #aae9e2; 
}

td.pos-td[class*="progress-7"],
td.pos-td[class*="progress-8"],
td.pos-td[class*="progress-9"] {
    background: #9ee4af; 
}

td.pos-td[class*="progress-100"] {
    background: #66CC99 ; 
}



*[class*="progress-0"]
{
    background: #FFF;
}
*[class*="progress-1"],
*[class*="progress-2"],
*[class*="progress-3"] {
    /* background: #d1e3e9;  */
    background: #f3eecf;
    background: #f3e9f6;
}

*[class*="progress-4"],
*[class*="progress-5"],
*[class*="progress-6"] {
    background: #aae9e2; 
}

*[class*="progress-7"],
*[class*="progress-8"],
*[class*="progress-9"] {
    background: #9ee4af; 
}

*[class*="progress-100"] {
    background: #66CC99 ; 
}

.legend {
    display: flex;
    justify-content: right;
}

.legend span {
    display: block;
    white-space: nowrap !important;
    padding: 1px 5px !important;
    color: #000000c9;
    font-size: 10px;
}

.legend span:first-child {
    border-radius: 5px 0 0 5px;
}
.legend span:last-child {
    border-radius: 0 5px 5px 0;
}




        @page {
            margin: 4mm;
        }
    </style>
</head>

<body>
    <table style="border-collapse: collapse; width: 100%; font-family: Arial; font-size: 12px; margin-bottom: 50px;" border="0" class="page-break coverpage">
        <tbody>
            <tr class="logo-estimate-title-class">
                <td style="width: 25%;" colspan="2" class="logo-class">
                    <img class="img-fluid mb-3" src="{{ get_file(sidebar_logo()) }}" alt="Dashboard-kit Logo" style="width:100%">
                </td>
                <td style="width: 25%; text-align: right;" colspan="2" class="construction-addrrss-class">
                    <strong>{{ $settings['company_name'] }}<br />
                    </strong>{{ $settings['company_address'] . ',' . $settings['company_zipcode'] .' '. $settings['company_city'] }}<br />{{ $settings['company_telephone'] }}<br />{{ $settings['company_email'] }}<br />@if(isset($settings['company_website']) && !empty($settings['company_website'])) {{$settings['company_website']}} @endif
                </td>
            </tr>
            <tr>
                <td colspan="4" class="main-text" style="padding:250px 20px 50px; font-size: 14px;">
                    <h2 style="margin-bottom: 30px; font-size:30px;">{{ __('Project Progress from')}} 
                        @if (!empty($progress_main_details))
                            @if(isset($progress_main_details->created_at))
                                {{ date('d.m.y', strtotime($progress_main_details->created_at)) }}
                            @endif
                        @endif


                    </h2>
                    <p>Sehr geehrte*r {{ isset($client->salutation) ? __($client->salutation) : '' }}
				{{ isset($client->last_name) ? $client->last_name : '' }},    
                    </p>
                    <p>vielen Dank für die gemeinsame Abnahme vom 
                        @if (!empty($progress_main_details))
                            @if(isset($progress_main_details->created_at))
                                <b>{{ date('d.m.y', strtotime($progress_main_details->created_at)) }}</b>
                            @endif
                        @endif   
                    Ihres Bauvorhabens in <b>{{ isset($project->construction_detail->address_1) ? $project->construction_detail->address_1 : '' }}, {{ isset($project->construction_detail->zip_code) ? $project->construction_detail->zip_code : '' }} {{ isset($project->construction_detail->city) ? $project->construction_detail->city : '' }}</b>. Nachfolgend erhalten Sie eine Kopie des Abnahmeprotokolls.    
                    </p>
                    









                </td>
            </tr>
            <tr class="label-auftraggeber-planer">
                <td style="width: 25%;" colspan="2" class="label-auftraggeber">
                    <h3 class="h3_title"><strong>{{__('Client')}}</strong></h3>
                </td>
                <td style="width: 25%;" colspan="2" class="label-planer">
                    <h3 class="h3_title"><strong>{{__('Planner')}}</strong></h3>
                </td>
            </tr>
            <tr class="auftraggeber-details">
                <td style="width: 25%;">
                    <div class="field">
                    {{ isset($client->first_name) ? $client->company_name : '' }}  
                    </div>
                    <div class="field">
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
                    <h3 class="h3_title"><strong>{{__('Project')}}</strong></h3>
                </td>
                <td style="width: 25%;" colspan="2" class="label-zusammenfassung">
                    <h3 class="h3_title"><strong>{{__('Summary')}}</strong></h3>
                     {{-- @php
                    $totalProgress = 0;
                        $itemCount = 0;

                        if(!empty($items)) {
                            foreach($items as $row) {
                                $lastProgressDetails = $row->progress()->where('progress_id', $main_progress_id)->first();
                                if(isset($lastProgressDetails) && !empty($lastProgressDetails)){
                                    $totalProgress += $lastProgressDetails->progress;
                                    $itemCount++;
                                }
                            }
                        }

                        $averageProgress = $itemCount > 0 ? round($totalProgress / $itemCount, 2) : 0;
                     @endphp
                    {{__('Average Project Progress')}}: {{ $averageProgress }}%  --}}
                </td>
            </tr>
            <tr class="project-estimate-details">
                <td style="width: 25%;">
                    <div class="field">
                    {{ isset($project->construction_detail->first_name) ? $project->construction_detail->company_name : '' }}
                    </div>
                    <div class="field">
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

                    @if (!empty($contractor))
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
                     {{-- <div class="totalrow row-subtotal-net" style="overflow: hidden; font-weight: bold;">
                        <div class="label label-subtotal-net" style="float: left; width: 50%;">{{__('Subtotal (net)')}}:</div>
                        <div class="total total-subtotal-net" style="float: right; width: 50%; text-align: right;">{{ convert_to_site_money_format($quote->net) }}</div>
                        <div style="clear: both;"></div>
                    </div>
                    <div class="totalrow row-discount-net" style="overflow: hidden; font-weight: bold;">
                        <div class="label label-discount-net" style="float: left; width: 50%;">{{__('Discount (net)')}}:</div>
                        <div class="total total-discount-net" style="float: right; width: 50%; text-align: right;">{{ convert_to_site_money_format(0) }}</div>
                        <div style="clear: both;"></div>
                    </div>
                    <div class="totalrow row-total-net" style="overflow: hidden; font-weight: bold;">
                        <div class="label label-total-net" style="float: left; width: 50%;">{{__('Total (net)')}}:</div>
                        <div class="total total-total-net" style="float: right; width: 50%; text-align: right;">{{ convert_to_site_money_format($quote->net) }}</div>
                        <div style="clear: both;"></div>
                    </div>
                    <div class="totalrow row-total-net-discount" style="overflow: hidden; font-weight: bold;">
                        <div class="label label-total-net-discount" style="float: left; width: 50%;">{{__('Total (net) incl. cash discount')}}:</div>
                        <div class="total total-total-net-discount" style="float: right; width: 50%; text-align: right;">{{ convert_to_site_money_format($quote->net_with_discount) }}</div>
                        <div style="clear: both;"></div>
                    </div>
                    <div class="totalrow row-tax" style="overflow: hidden; font-weight: bold;">
                        <div class="label label-tax" style="float: left; width: 50%;">{{__('plus')}} {{ $quote->tax }}% {{__('VAT')}}:</div>
                        <div class="total total-tax" style="float: right; width: 50%; text-align: right;">{{ convert_to_site_money_format($quote->gross - $quote->net) }}</div>
                        <div style="clear: both;"></div>
                    </div>
                    <div class="totalrow row-total-gross" style="overflow: hidden; font-weight: bold;">
                        <div class="label label-total-gross" style="float: left; width: 50%;">{{__('Total (gross)')}}:</div>
                        <div class="total total-total-gross" style="float: right; width: 50%; text-align: right;">{{ convert_to_site_money_format($quote->gross) }}</div>
                        <div style="clear: both;"></div>
                    </div>
                    <div class="totalrow row-discount-gross" style="overflow: hidden; font-weight: bold;">
                        <div class="label label-discount-gross" style="float: left; width: 50%;">{{__('Cash Discount')}} {{ $quote->discount }}% (7 {{__('calendar days')}}):</div>
                        <div class="total total-discount-gross" style="float: right; width: 50%; text-align: right;">{{ convert_to_site_money_format($quote->gross - $quote->gross_with_discount) }}</div>
                        <div style="clear: both;"></div>
                    </div>
                    <div class="totalrow row-total-gross-discount" style="overflow: hidden; font-weight: bold;">
                        <div class="label label-total-gross-discount" style="float: left; width: 50%;">{{__('Total (gross) incl. cash discount')}}</div>
                        <div class="total total-total-gross-discount" style="float: right; width: 50%; text-align: right;">{{ convert_to_site_money_format($quote->gross_with_discount) }}</div>
                        <div style="clear: both;"></div>
                    </div>  --}}
                    <div>

                    </div>
                </td>
            </tr>
        </tbody>
    </table>

    <table style="width: 100%; font-family: Arial; font-size: 12px; margin-bottom: 50px; border: 1px solid #f1f1f1; table-layout: unset;" class="coverpage table">
        <thead style="display: table-row-group;">
        <tr>
                <td colspan="5" class="main-text" style="">
                    <h3 style="font-size:20px;">{{ __('Project Progress Details')}}</h3>    
                </td>
                 {{-- <td colspan="2" class="legend-td">
                        <div class="legend" style="display:flex;">
                            <span style="background:#dddddd;display: inline; white-space: nowrap !important; margin:0 !important;;">0%</span>
                            <span style="background:#f3e9f6;display: inline; white-space: nowrap !important; margin:0 !important;;">10-39%</span>
                            <span style="background:#aae9e2;display: inline; white-space: nowrap !important; margin:0 !important;;">40-69%</span>
                            <span style="background:#9ee4af;display: inline; white-space: nowrap !important; margin:0 !important;;">70-99%</span>
                            <span style="background:#66CC99;display: inline; white-space: nowrap !important; margin:0 !important;;">100%</span>
                        </div>
                    </td>  --}}
            </tr>
            <tr>
                <th class="th" style="20px;">{{__('Pos')}}</th>
                 {{-- <th class="th">{{__('Name')}}</th>  --}}
                <th class="th">{{__('Progress')}}</th>
                 {{-- <th class="th">{{__('Description')}}</th>  --}}
                 {{-- <th class="th">{{ __('Single Price') }}</th>
                <th class="th">{{ __('Total Price') }}</th>
                <th class="th">{{ __('Progress') }}</th>
                <th class="th">{{ __('Remaining') }}</th>  --}}
                <th class="th">{{__('Comments')}}</th>
                <th class="th">{{__('Quantity')}}</th>
                <th class="th">{{__('Signature')}}</th>
            </tr>
        </thead>
        <tbody>
            @if(!empty($items))
                @php
                    $group = '';
                @endphp
                @foreach($items as $row)
                    @php
                        $last_signature = "";
                        $last_progress = "0%";
                        $last_progress_details = $row->progress()->latest()->first();
                        if(isset($last_progress_details) && !empty($last_progress_details)){
                            $last_signature = $last_progress_details->signature;
                            $last_progress = ($last_progress_details->progress) ? $last_progress_details->progress.'%' : '0%';
                        }
						$price = 0;
						$total_price = 0;
						if(isset($quote) && count($quote->quoteItem) > 0){
							foreach($quote->quoteItem as $q_item) {
								if($q_item->product_id == $row->id) {
									$price = $q_item->price;
									$total_price = $q_item->total_price;
								}
							}
						}
                    @endphp
                    @if ($group != $row->group->group_name)
                        <tr>
                            <td style="width: 100%;background: #f5f5f5;border: 1px solid #EEE" colspan="5" class="">
                                <h3 style="padding: 5px !important; font-size: 14px;"><strong>{{ $row->group->group_name }}</strong></h3>
                            </td>
                        </tr>
                    @endif
                    <tr class="item-top">
                           <td rowspan="2" class="pos-td progress-{{ $last_progress }}" style="width: 20px; border: 1px solid #EEE; border-bottom: 1px solid #FFF !important; vertical-align:top;">
                             <div class="field item-pos">{{ $row->pos }}</div>
                           </td>
                            <td colspan="4" style="border: 1px solid #EEE; ">
                                <div class="field item-name" style="font-weight:bold;">{{ $row->name }}</div>
                                <div class="field item-description">{{ $row->description }}</div>
                            </td>
                    </tr>
                    <tr style="border: 1px solid #f1f1f1;" class="item-bottom">
                            
                        </td>
                         {{-- <td style="width: 20%; padding: 5px !important;">
                        </td>  --}}
                        <td style="border: 1px solid #EEE; ">
                            <div class="field progress-{{ $last_progress }} last-progress-nr">{{ $last_progress }}</div>
                        </td>
                    	 {{-- <td style="width: 20%; padding: 5px !important;">
                            <div class="field">{{ $row->description }}</div>
                        </td>  --}}
                        
                        {{-- <td style="width: 15%; padding: 5px !important;">
                            <div class="field">{{ convert_to_site_money_format($price); }}</div>

                        </td>
                        <td style="width: 15%; padding: 5px !important;">
                            <div class="field">{{ convert_to_site_money_format($total_price) }}</div>
                        </td>
                        <td style="width: 15%; padding: 5px !important;">
                            <div class="field">{{ convert_to_site_money_format(0) }}</div>
                        </td>
                        <td style="width: 15%; padding: 5px !important;">
                            <div class="field">{{ convert_to_site_money_format(0) }}</div>
                        </td>  --}}
                        <td style=" border: 1px solid #EEE; text-align: left; width:50% !important;">
                            <div class="field progress-history" style="display: flex; flex-wrap: wrap; gap: 2px;">
                                @foreach ($row->progress()->orderBy('id')->get() as $progress)
                                    @php
                                        $user_name = "";
                                    @endphp
                                    @if (isset($progress->progress_id) && !empty($progress->progress_id))
                                        @php
                                            $user_name = ($progress->project_progress[0]['name']) ? $progress->project_progress[0]['name'] : '';
                                        @endphp
                                    @endif
                                    <div class="progress-history-item progress-{{ $last_progress }}" 
     style="margin:3px;display: inline-block;" 
     data-bs-toggle="tooltip" 
     data-bs-placement="top" 
     title="@if(isset($user_name)){{ $user_name }}@endif">
                                    @if(isset($progress->created_at))
                                        <span class="history-date">{{ date('d.m.y', strtotime($progress->created_at))}}</span>
                                    @endif
                                    
                                    
                                    @if(isset($progress->progress))
                                        <span class="history-percent">{{ $progress->progress }}%</span>
                                    @endif
                                    @if(isset($progress->remarks) && !empty($progress->remarks))
                                       <br><span class="history-remarks" style="display: inline-block;">{{ $progress->remarks }}</span>
                                      @endif
                                        
                                        </div>
                                
                            @endforeach</div>
                        </td>
                        <td style="border: 1px solid #EEE; ">
                            <div class="field" style="display: inline-block; white-space: nowrap !important;">{{ $row->quantity }} {{ $row->unit }}</div>
                        </td>
                        <td style="border: 1px solid #EEE; ">
                            <div class="field">@if(isset($last_signature))
                                <img src="{{$last_signature}}" class="" width="100px" />
                            @endif</div>
                        </td>
                    </tr>


                @php
                    $group = $row->group->group_name;
                @endphp
            @endforeach
        @endif
        </tbody>
    </table>
    @if (!empty($progress_main_details))
        <table style="border-collapse: collapse; width: 100%; font-family: Arial; font-size: 12px; margin-bottom: 50px;" border="0" class="coverpage final-details">
            <tbody>
                <tr>
                    <td style="width: 100%;" colspan="3">
                        <h3 class="h3_title"><strong>{{__('Progress Confirm Details')}}</strong></h3>
                    </td>
                    
                </tr>
                <tr class="final-details-top">
                    <td style="padding-top:30px;" colspan="4">
                        <div class="field">
                            
                            <div class="final-text" style="font-size:14px;"><i class="fa-regular fa-square-check" style="margin-right:10px; "></i> {{ __('I confirm the Progress above') }}.</div>
                            @if(isset($progress_main_details->comment) && !empty($progress_main_details->comment))
                                <div class="th">{{ $progress_main_details->comment }}</div>
                            @endif
                        </div>
                    </td>
                </tr>
                <tr class="final-details-bottom">
                    <td style="width: 33%;">
                        <div class="field">
                            @if(isset($progress_main_details->created_at))
                                <div class="th final-field final-date" style="text-align: center;">{{ date('d.m.y', strtotime($progress_main_details->created_at)) }}</div>
                            @endif
                        </div>
                    </td>
                    <td style="width: 33%;">
                        <div class="field">
                            @if(isset($progress_main_details->name))
                            <div class="th final-field final-name" style="text-align: center;">{{ $progress_main_details->name }}</div>
                            @endif
                        </div>
                    </td>
                    <td style="">
                        <div class="field">
                            @if(isset($progress_main_details->signature))
                                 <div class="th final-field final-sig"> <img src="{{ $progress_main_details->signature }}" class="" width="100px" /></div>
                            @endif
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    @endif
</body>

</html>