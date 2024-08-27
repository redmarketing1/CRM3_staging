<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Progress ({{ $invoice->project_name }})</title>
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
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">


</head>
@php
    $sr=1;
@endphp
<body style="font-family: Arial; font-size: 16px;">
<div class="mainframe">
    <div class="maintext">

        <!-------------------- NOVA AVA CONTENT -------------->

        <div style="width:100%;padding:24px 0 16px 0;background-color: #ffffff;text-align:center;">
            <div style="display:inline-block;width: 100%;font-family:Roboto,Helvetica,Arial,sans-serif;color: #000000;text-align: left;">
                <div style="">
                    <p>{{ isset($content['client']->name) ? $content['client']->name : '' }}</p>
                </div>

            </div>
        </div>


		<table style="width: 100%;margin:0 0 100px 0; " class="table calctable estimation-preview-table">
			<thead >
			<tr class="tr" style="border-bottom: 2px solid #FFF;">
				<th class="th">{{ __('POS') }}</th>
				<th class="th">{{ __('Name') }}</th>
				<th class="th">{{ __('Description') }}</th>
				<th class="th">{{ __('Quantity') }}</th>
				<th class="th">{{ __('Progress') }}</th>
				<th class="th">{{ __('Comments') }}</th>
				<th class="th">{{ __('Signature') }}</th>
			</tr>
			</thead>
			<tbody>
				@if(isset($invoice->items) && count($invoice->items) > 0)
					@php
						$group = '';
					@endphp
                    @foreach($invoice->items as $key => $item)
						@if ($group != $item->group_name)
							<tr class="tr" style="border-bottom: none;">
								<td class="th" style="text-align:right;">{{ get_group_pos($item->pos) }}</td>
								<td class="th" style="text-align: left;margin-left: 150px" colspan="6">
									<div style="width:100%;font-size: 14px; padding: 5px 0;"> {{ $item->group_name }}</div>
								</td>
							</tr>
						@endif
                        <tr class="tr">
							<td class="td">{{ $item->pos }}</td>
							<td class="td">{{ $item->name }}</td>
							<td class="td">{{ $item->description }}</td>
							<td class="td" style="text-align:right;">{{ $item->quantity }} {{ $item->unit }}</td>
							<td class="td">{{ $item->progress }}%</td>
							<td class="td">
                                @foreach ($item->progress_list as $progress)
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
                                        @if(isset($progress->progress))
                                            <span style="width: 15%; background-color: #77777721;">{{ $progress->progress.'%' }}</span>
                                        @endif
                                    </div>
                                    @if(isset($user_name))
                                        <b>- {{ $user_name }}</b>
                                    @endif
                                    @if(isset($progress->remarks))
                                        <br>
                                        <div class="th">{{ $progress->remarks }}</div>
                                    @endif
                                @endforeach
							</td>
                            <td>
                                @if(isset($item->last_signature))
                                    <img src="{{$item->last_signature}}" class="img-thumbnail" width="100px" />
                                @endif
                            </td>
						</tr>
                        @if (!empty($item->progress_files))
                            <tr class="tr">
                                <td class="td" colspan="7">
                                    @foreach ($item->progress_files as $prow)
                                        @if(isset($prow['file']))
                                        <a href="{{ get_file('uploads/progress_files/') . '/' . $prow['file'] }}" target="_blank"><img src="{{ get_file('uploads/progress_files/') . '/' . $prow['file'] }}" width="100px" style="margin: 5px; padding: 15px;" /></a>
                                        @endif
                                    @endforeach
                                </td>
                            </tr>
                        @endif
						@php
							$group = $item->group_name;
						@endphp
                    @endforeach
                @endif
			</tbody>
		</table>
		


<!-------------------- NOVA AVA CONTENT END -------------->


    
    </div>



</body>
</html>
