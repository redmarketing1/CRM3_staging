<!DOCTYPE html>
<html>

<head>
    <title>{{ __('Progress Invoice') }}</title>
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

        table .progress-history-item:last-child {}

        .last-progress-nr {
            font-size: 18px;
            font-weight: bold;
        }

        table.coverpage.table td {
            vertical-align: middle;
            width: auto !important;
        }

        table.coverpage.table .item-bottom td {
            text-align: center;
        }

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

        .main-text p {
            font-size: 14px;
        }


        div[class*="progress-0"] {
            color: #ddd;
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




        td.pos-td[class*="progress-0"] {
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
            background: #66CC99;
        }



        *[class*="progress-0"] {
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
            background: #66CC99;
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
                    <img class="img-fluid mb-3" src="{{ get_file(sidebar_logo()) }}" alt="Dashboard-kit Logo"
                        style="width:100%">
                </td>
                <td style="width: 25%; text-align: right;" colspan="2" class="construction-addrrss-class">
                    <strong>{{ $settings['company_name'] }}<br />
                    </strong>{{ $settings['company_address'] . ',' . $settings['company_zipcode'] . ' ' . $settings['company_city'] }}<br />{{ $settings['company_telephone'] }}<br />{{ $settings['company_email'] }}<br />
                    @if (isset($settings['company_website']) && !empty($settings['company_website']))
                        {{ $settings['company_website'] }}
                    @endif
                </td>
            </tr>
            <tr>
                <td colspan="4" class="main-text" style="padding:250px 20px 50px; font-size: 14px;">
                    <h2 style="margin-bottom: 30px; font-size:30px;">{{ __('Invoice') }}
                        @if (!empty($invoice))
                            @if (isset($invoice->created_at))
                                {{ date('d.m.y', strtotime($invoice->created_at)) }}
                            @endif
                        @endif
                    </h2>
                    <p>Sehr geehrte*r {{ isset($client->salutation) ? __($client->salutation) : '' }}
                        {{ isset($client->last_name) ? $client->last_name : '' }},
                    </p>
                    <p>vielen Dank für die gemeinsame Abnahme vom
                        @if (!empty($invoice))
                            @if (isset($invoice->created_at))
                                <b>{{ date('d.m.y', strtotime($invoice->created_at)) }}</b>
                            @endif
                        @endif
                        Ihres Bauvorhabens in
                        <b>{{ isset($project->construction_detail->address_1) ? $project->construction_detail->address_1 : '' }},
                            {{ isset($project->construction_detail->zip_code) ? $project->construction_detail->zip_code : '' }}
                            {{ isset($project->construction_detail->city) ? $project->construction_detail->city : '' }}</b>.
                        Nachfolgend erhalten Sie eine Kopie des Abnahmeprotokolls.
                    </p>
                </td>
            </tr>
            <tr class="label-auftraggeber-planer">
                <td style="width: 25%;" colspan="2" class="label-auftraggeber">
                    <h3 class="h3_title"><strong>{{ __('Client') }}</strong></h3>
                </td>
                <td style="width: 25%;" colspan="2" class="label-planer">
                    <h3 class="h3_title"><strong>{{ __('Planner') }}</strong></h3>
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
                    @if (!empty($client->countryDetail) && isset($client->countryDetail))
                        <div class="field">
                            {{ !empty($client->countryDetail) ? ' ' . $client->countryDetail->name : '' }}
                        </div>
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
                    <h3 class="h3_title"><strong>{{ __('Project') }}</strong></h3>
                </td>
                <td style="width: 25%;" colspan="2" class="label-zusammenfassung">
                    <h3 class="h3_title"><strong>{{ __('Summary') }}</strong></h3>
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
                    <div class="field">
                        {{ isset($project->construction_detail->address_1) ? $project->construction_detail->address_1 : '' }}
                    </div>
                    <div class="field">
                        {{ isset($project->construction_detail->zip_code) ? $project->construction_detail->zip_code : '' }}
                        {{ isset($project->construction_detail->city) ? $project->construction_detail->city : '' }}
                    </div>
                    @if (!empty($project->construction_detail->district))
                        <div class="field">
                            {{ $project->construction_detail->district ?? '' }}
                        </div>
                    @endif
                    @if (!empty($project->construction_detail->state))
                        <div class="field">
                            {{ $project->construction_detail->state ?? '' }}
                        </div>
                    @endif
                    @if (!empty($project->construction_detail->countryDetail))
                        <div class="field">
                            {{ $project->construction_detail->countryDetail->name ?? '' }}
                        </div>
                    @endif

                    @if (!empty($contractor))
                        <div class="field">{{ isset($client_name) ? $client_name : '' }}</div>
                        <div class="field">{{ $project->location }}</div>
                    @endif
                </td>
                <td style="width: 25%;" class="project-address">
                    @if (!empty($project->construction_detail->mobile) && isset($project->construction_detail->mobile))
                        <div class="field">{{ $project->construction_detail->mobile }}</div>
                    @endif
                    @if (!empty($project->construction_detail->email) && isset($project->construction_detail->email))
                        <div class="field">{{ $project->construction_detail->email }}</div>
                    @endif
                </td>
            </tr>
        </tbody>
    </table>

    <!--- Invoice Deails ------->
    <table
        style="width: 100%; font-family: Arial; font-size: 12px; margin-bottom: 50px; border: 1px solid #f1f1f1; table-layout: unset;" class="coverpage table">
        <thead style="display: table-row-group;">
            <tr>
                <td colspan="5" class="main-text" style="">
                    <h3 style="font-size:20px;">{{ __('Invoice Details') }}</h3>
                </td>
            </tr>
            <tr>
                <tr class="tr" style="border-bottom: 2px solid #FFF;">
                    <th class="th">{{ __('POS') }}</th>
                    <th class="th">{{ __('Name') }}</th>
                    <th class="th">{{ __('Comments') }}</th>
                    <th class="th">{{ __('Quantity') }}</th>
                    <th class="th">{{ __('Single Price') }}</th>
                    <th class="th">{{ __('Total Price') }}</th>
                    <th class="th">{{ __('Progress') }}</th>
                    <th class="th">{{ __('Total') }}</th>
                </tr>
            </tr>
        </thead>
        <tbody>
            @if(isset($invoice->items) && count($invoice->items) > 0)
                @php
                    $group = '';
                    $subtotal = 0;
                @endphp
                @foreach($invoice->items as $key => $item)
                    {{-- @php $group = $item->group() @endphp --}}
                    @if ($item->group->group_name)
                        <tr class="tr" style="border-bottom: none;">
                            <td class="th" style="text-align:right;">{{ get_group_pos($item->group->group_pos) }}</td>
                            <td class="th" style="text-align: left;margin-left: 150px" colspan="7">
                                <div style="width:100%;font-size: 14px; padding: 5px 0;"> {{ $item->group->group_name }}</div>
                            </td>
                        </tr>
                    @endif
                    <tr class="tr">
                        <td class="td">{{ $item->projectEstimationProduct->pos }}</td>
                        <td class="td">{{ $item->item }}</td>
                        <td class="td">
                            @foreach ($item->project_all_progress() as $progress)
                                @php
                                    $user_name = "";
                                @endphp
                                @if (isset($progress->progress_id) && !empty($progress->progress_id))
                                    @php
                                        $user_name = ($progress->project_progress[0]['name']) ? $progress->project_progress[0]['name'] : '';
                                    @endphp
                                @endif
                                <div style="display:flex !important;">
                                    @if(isset($progress->created_at))
                                        {{ date('d.m.y', strtotime($progress->created_at))}}
                                    @endif
                                    @if(isset($user_name))
                                        <b>- {{ $user_name }}</b>
                                    @endif
                                </div>
                                @if(isset($progress->remarks))
                                    <div style="display: flex; align-items: center;">
                                        <progress id="file" value="{{ $progress->progress }}" max="100">
                                        </progress>
                                        <span style="margin-left: 8px;">{{ $progress->progress.'%' }}</span>
                                    </div>
                                @endif
                            @endforeach
                        </td>
                        <td class="td" style="text-align:right;">{{ $item->quantity }} {{ $item->unit }}</td>
                        <td class="td">{{ currency_format_with_sym($item->price) }}</td>
                        <td class="td">{{ currency_format_with_sym($item->total_price) }}</td>
                        <td class="td">{{ $item->progress }}%</td>
                        <td class="td">{{ currency_format_with_sym($item->progress_amount) }}</td>
                        @php 
                            $subtotal += $item->progress_amount;
                        @endphp
                    </tr>
                @endforeach
            @endif
        </tbody>
    </table>

        
    <div style="display: flex; justify-content: flex-end; width: 95%"> 
        <table>
            @php 
                $grosstotal = 0;
                $totalwithdiscount = $subtotal - $invoice->discount;
                $tax = ($invoice->tax * $totalwithdiscount)/100; 
                $grosstotal = $tax + $totalwithdiscount;
            @endphp
            <tr class="tr" style="border-bottom: 2px solid #FFF;">
                <td class="td"><strong style="font-size: 16px">{{__('Subtotal (net)')}}:</strong></td>
                <td>{{ currency_format_with_sym($subtotal) }}</td>
            </tr>
            <tr class="tr" style="border-bottom: 2px solid #FFF;">
                <td class="td"><strong style="font-size: 16px">{{__('Discount (net)')}} (-):</strong></td>
                <td>{{ currency_format_with_sym($invoice->discount) }}</td>
            </tr>
            <tr class="tr" style="border-bottom: 2px solid #FFF;">
                <td class="td"><strong style="font-size: 16px">{{ __('Total (net) incl. cash discount') }}:</strong></td>
                <td>{{ currency_format_with_sym($totalwithdiscount) }}</td>
            </tr>
            <tr class="tr" style="border-bottom: 2px solid #FFF;">
                <td class="td"><strong style="font-size: 16px">{{ $invoice->tax }}% {{ __('VAT') }} (+):</strong></td>
                <td>{{ currency_format_with_sym($tax) }}</td>
            </tr>
            <tr class="tr" style="border-bottom: 2px solid #FFF;">
                <td class="td"><strong style="font-size: 16px">{{ __('Total (net)') }}:</strong></td>
                <td>{{ currency_format_with_sym($grosstotal) }}</td>
            </tr>
        </table>
    </div>
    
</body>

</html>
