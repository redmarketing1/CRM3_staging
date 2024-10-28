<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $settings['site_rtl'] == 'on' ? 'rtl' : '' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>New York - invoice</title>
    <link href="https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&display=swap"
        rel="stylesheet">


    <style type="text/css">
        :root {
            --theme-color: #003580;
            --white: #ffffff;
            --black: #000000;
        }

        body {
            font-family: 'Lato', sans-serif;
        }

        p,
        li,
        ul,
        ol {
            margin: 0;
            padding: 0;
            list-style: none;
            line-height: 1.5;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table tr th {
            padding: 0.75rem;
            text-align: left;
        }

        table tr td {
            padding: 0.75rem;
            text-align: left;
        }

        table th small {
            display: block;
            font-size: 12px;
        }

        .invoice-preview-main {
            max-width: 700px;
            width: 100%;
            margin: 0 auto;
            background: #ffff;
            box-shadow: 0 0 10px #ddd;
        }

        .invoice-logo {
            max-width: 200px;
            width: 100%;
        }

        .invoice-header table td {
            padding: 15px 30px;
        }

        .text-right {
            text-align: right;
        }

        .no-space tr td {
            padding: 0;
        }

        .vertical-align-top td {
            vertical-align: top;
        }

        .view-qrcode {
            max-width: 114px;
            height: 114px;
            margin-left: auto;
            margin-top: 15px;
            background: var(--white);
        }

        .view-qrcode img {
            width: 100%;
            height: 100%;
        }

        .invoice-body {
            padding: 30px 25px 0;
        }

        table.add-border tr {
            border-top: 1px solid var(--theme-color);
        }

        tfoot tr:first-of-type {
            border-bottom: 1px solid var(--theme-color);
        }

        .total-table tr:first-of-type td {
            padding-top: 0;
        }

        .total-table tr:first-of-type {
            border-top: 0;
        }

        .sub-total {
            padding-right: 0;
            padding-left: 0;
        }

        .border-0 {
            border: none !important;
        }

        .invoice-summary td,
        .invoice-summary th {
            font-size: 13px;
            font-weight: 600;
        }

        .total-table td:last-of-type {
            width: 146px;
        }

        .invoice-footer {
            padding: 15px 20px;
        }

        .itm-description td {
            padding-top: 0;
        }
        html[dir="rtl"] table tr td,
        html[dir="rtl"] table tr th{
            text-align: right;
        }
        html[dir="rtl"]  .text-right{
            text-align: left;
        }
        html[dir="rtl"] .view-qrcode{
            margin-left: 0;
            margin-right: auto;
        }
        p:not(:last-of-type){
            margin-bottom: 15px;
        }
        .invoice-summary p{
            margin-bottom: 0;
        }
    </style>
</head>

<body>
    <div class="invoice-preview-main">
        <div class="invoice-header" style="background: {{$color}};color:{{$font_color}}">
            <table>
                <tbody>
                    <tr>
                        <td>
                            <img class="invoice-logo"
                                src="{{ $img }}"
                                alt="">
                        </td>
                        <td class="text-right">
                            <h3 style="text-transform: uppercase; font-size: 40px; font-weight: bold;">{{__('INVOICE')}}</h3>
                        </td>
                    </tr>
                </tbody>
            </table>
            <table class="vertical-align-top">
                <tbody>
                    <tr>
                        <td>
                            <p>
                                @if($settings['company_name']){{$settings['company_name']}}@endif<br>
                                @if($settings['company_address']){{$settings['company_address']}}@endif
                                @if($settings['company_city']) <br> {{$settings['company_city']}}, @endif
                                @if($settings['company_state']){{$settings['company_state']}}@endif
                                @if($settings['company_zipcode']) - {{$settings['company_zipcode']}}@endif
                                @if($settings['company_country']) <br>{{$settings['company_country']}}@endif <br>
                            </p>
                            <p>
                                {{__('Registration Number')}} : {{$settings['registration_number']}} <br>
                                {{__('VAT Number')}} : {{$settings['vat_number']}} <br>
                            </p>
                        </td>
                        <td>
                            <table class="no-space">
                                <tbody>
                                    <tr>
                                        <td>{{ __('Number:') }} </td>
                                        <td class="text-right">{{\App\Models\Invoice::invoiceNumberFormat($settings,$invoice->invoice)}}</td>
                                    </tr>
                                    <tr>
                                        <td>{{__('Issue Date:') }}</td>
                                        <td class="text-right">{{ company_date_formate($invoice->issue_date) }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="2">
                                            <div class="view-qrcode">
                                                {{-- <img src="Qrcode_livre.png" alt=""> --}}
                                                <p> {!! DNS2D::getBarcodeHTML(route('pay.invoice',\Illuminate\Support\Facades\Crypt::encrypt($invoice->id)), "QRCODE",2,2) !!}</p>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="invoice-body">
            <table>
                <tbody>
                    <tr>
                        <td>
                            <strong style="margin-bottom: 10px; display:block;">Bill To:</strong>
                            <p> {{!empty($client->company_name)?$client->company_name:''}}<br>
                                {{!empty($client->name)?$client->name:''}}<br>
                                {{!empty($client->email)?$client->email:''}}<br>
                                {{!empty($client->mobile)?$client->mobile:''}}<br>
                                {{!empty($client->address)?$client->address:''}}<br>
                                {{!empty($client->zip)?$client->zip:''}}<br>
                                {{!empty($client->city)?$client->city:'' . ', '}} {{!empty($client->state)?$client->state:'' .', '}},{{!empty($client->country)?$client->country:''}}
                            </p>
                        </td>
                        <td class="text-right">
                            <strong style="margin-bottom: 10px; display:block;">Ship To:</strong>
                            <p> {{!empty($client->company_name)?$client->company_name:''}}<br>
                                {{!empty($client->name)?$client->name:''}}<br>
                                {{!empty($client->email)?$client->email:''}}<br>
                                {{!empty($client->mobile)?$client->mobile:''}}<br>
                                {{!empty($client->address)?$client->address:''}}<br>
                                {{!empty($client->zip)?$client->zip:''}}<br>
                                {{!empty($client->city)?$client->city:'' . ', '}} {{!empty($client->state)?$client->state:'' .', '}},{{!empty($client->country)?$client->country:''}}
                            </p>
                        </td>
                    </tr>
                </tbody>
            </table>
            <table class="add-border invoice-summary" style="margin-top: 30px;">
                <thead style="background-color: {{$color}};color:{{$font_color}} ">
                    <tr>
                        <th>{{__('Item')}}</th>
                        <th>{{__('Quantity')}}</th>
                        <th>{{__('Rate')}}</th>
                        <th>{{__('Tax')}}(%)</th>
                        <th>{{__('Discount')}}</th>
                        <th class="">{{__('Price')}} <small>{{__('before tax & discount')}}</small></th>
                        <th class="">{{__('Progress')}} <small>{{__('%')}}</small></th>
                        <th class="">{{__('Payable Amount')}}</th>
                    </tr>
                </thead>
                <tbody>
                    @if(isset($invoice->items) && count($invoice->items) > 0)
                    @foreach($invoice->items as $key => $item)
                            <tr>
                        <td>@if(!empty($item->items))
                                {{$item->items->name}}
                            @elseif($item->name !="")
                                {{$item->name}}
                            @else
                                No Item
                            @endif</td>
                        <td>{{$item->quantity}}</td>
                        <td>{{ priceFormat($settings,$item->price)}}</td>
                        <td>
                            @foreach($item->itemTax as $taxes)
                                @if(!empty($item->itemTax))
                                <p>
                                    <span>{{$taxes['name']}}</span>  <span>({{$taxes['rate']}})</span> <span>{{$taxes['price']}}</span>
                                </p>
                                @else
                                <p>-</p>
                                @endif
                            @endforeach
                        </td>
                        <td>{{($item->discount!=0)? priceFormat($settings,$item->discount):'-'}}</td>
                        <td>{{ priceFormat($settings,$item->price * $item->quantity)}}</td>
                        <td>{{ $item->progress}}</td>
                        <td>{{  priceFormat($settings, $item->payable)}}</td>
                    </tr>
                    @endforeach
                    @endif
                </tbody>
                <tfoot>
                    <tr>
                        <td>{{__('Total')}}</td>
                        <td>{{$invoice->totalQuantity}}</td>
                        <td>{{ priceFormat($settings,$invoice->totalRate)}}</td>
                        <td>{{ priceFormat($settings,$invoice->totalTaxPrice) }}</td>
                        <td></td>
                        <td>{{ priceFormat($settings,$invoice->getSubTotal())}}</td>
                        <td></td>
                        <td>{{ priceFormat($settings,$invoice->totalPayable)}}</td>
                    </tr>
                    <tr>
                        <td colspan="6"></td>
                        <td colspan="2" class="sub-total">
                            <table class="total-table">
                                @if($invoice->totalDiscount!=0)
                                    <tr>
                                        <td>{{__('Discount ').$invoice->discount}}%: </td>
                                        <td>{{ priceFormat($settings,$invoice->totalDiscount)}}</td>
                                    </tr>
                                @endif
                                @if(!empty($invoice->totalTax))
                                <tr>
                                    <td>{{__('Tax ').($invoice->tax==1?"19":"")}}% :</td>
                                    <td>{{  priceFormat($settings,$invoice->totalTax)  }}</td>
                                </tr>
                                @endif
                                 <tr>
                                    <td>{{__('Total')}}:</td>
                                    <td>{{ priceFormat($settings,$invoice->totalPayable-$invoice->totalDiscount+$invoice->totalTax)}}</td>
                                </tr>
                                <tr>
                                    <td>{{__('Credit Note')}}:</td>
                                    <td>{{ priceFormat($settings,$invoice->totalPayable-$invoice->totalDiscount+$invoice->totalTax)}}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </tfoot>
            </table>
            <div data-v-f2a183a6="" class="d-body1">
                <p data-v-f2a183a6="">
                    {{ $settings['footer_title'] }} <br>
                    {{ $settings['footer_notes'] }}
                </p>
            </div>
            <div data-v-4b3dcb8a="" class="break-25"></div>
            <div class="invoice-footer">
                @if(!isset($preview))
                    @include('invoice.script');
                @endif
                <p>Thanks!</p>
            </div>
        </div>
    </div>

</body>

</html>
