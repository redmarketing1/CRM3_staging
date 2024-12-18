<div class="card">

    <div class="card-header d-flex justify-content-between align-items-center">
        <h2>{{ __('Project Progress') }} - {{ \Carbon\Carbon::now('Europe/Berlin')->format('d.m.Y') }}</h2>
		<label><input type="checkbox" class="form-check-input" id="show-payment-progress" /> <strong>{{ __('Show Finance') }}.</strong></label>
        @php
            if(count($estimation_quote_items) > 0){
                $total_items = $estimation_quote_items->count();
                $total_progress = 0;
                $total_invoice_progress = 0;
                $gross_total = $estimate_quote->gross_with_discount;
                foreach ($estimation_quote_items as $item){
                    $latest_progress = $item->projectEstimationProduct->progress()->latest()->first();
                    if($latest_progress){
                        $total_progress += $latest_progress->progress;
                        $total_invoice_progress += $latest_progress->progress_payment;
                    }
                }

                $project_progress = ($total_progress/ ($total_items * 100)) * 100;
                $project_progress_amount = ($project_progress/100) * $gross_total;
                $project_progress_remaing = $gross_total - $project_progress_amount;

                $project_invoice =  ($total_invoice_progress/ ($total_items * 100)) * 100;
                $project_invoice_amount = ($project_invoice/100) * $gross_total;
                $project_invoice_remaing = $gross_total - $project_invoice_amount;

            }   
        @endphp
        <div class="select-progress total-progress">
            <div class="select-progress-inner">
                <input class="progress" disabled  type="range" id="progress-slider-total" class="form-control"
                    min="0" value="{{ $project_progress }}"  max="100" step="5"
                    style="width: 100%;">
                <span id="slider-value-total" class="slider-value">{{ number_format($project_progress, 2) }}%</span>
            </div>
            <div class="progress-numbers finance-items">
                <span class="progress-numbers"><b>Total Progress: </b>&nbsp; {{ currency_format_with_sym($project_progress_amount) }} / {{ currency_format_with_sym($gross_total) }} (Remaining: {{ currency_format_with_sym($project_progress_remaing) }})</span>
                <span class="invoice-numbers"><b>Total Invoice: </b>&nbsp; {{ currency_format_with_sym($project_invoice_amount) }} / {{ currency_format_with_sym($gross_total) }} (Remaining: {{ currency_format_with_sym($project_invoice_remaing) }})</span>
            </div>
        </div>
    </div>

    <div class="card-body table-border-style">
        <div class="card-body table-responsive" id="progress-div">
            {{ Form::open(['route' => ['progress.sign.store'], 'enctype' => 'multipart/form-data', 'class' => 'project-progress-form']) }}
                <table class="table w-100 table-hover table-bordered" id="progress-table">
                    <thead>
                        <th data-dt-order="disable" style="width: 141.029px;">{{ __('Name') }}</th>
                        <th data-dt-order="disable" style="width: 85.4688px;">{{ __('Quantity') }}</th>
                        <th data-dt-order="disable" style="width: 459.258px;">{{ __('Progress') }}</th>
                        <th data-dt-order="disable" style="width: 422.305px;">{{ __('Description') }}</th>
                        <th data-dt-order="disable" style="width: 72.1224px;">{{ __('History') }}</th>
                    </thead>
                    <tbody>
                        @if(count($estimation_quote_items) > 0)
                            @php
                                $group = '';
                            @endphp
                            @foreach ($estimation_quote_items as $item)
                                @if ($group != $item->projectEstimationProduct->group->group_name)
                                    @php
                                        $group = $item->projectEstimationProduct->group->group_name; // Update the group variable here
                                    @endphp
                                    <tr class="group progress-group">
                                        <td colspan="6">{{ $group }}</td>
                                    </tr>
                                @endif
                                <tr>
                                    <td class="name"><div class="name-container"><div class="pos-prefix"><div class="div-inner">{{ $item->projectEstimationProduct->pos }}</div></div>{{ $item->projectEstimationProduct->name }}</div></td>
                                    <td class="quantity">{{ $item->projectEstimationProduct->quantity }} <div class="single-price finance-items"> x {{ currency_format_with_sym($item->price) }}</div></td>
                                    <td class="item-signature">
                                        @php
                                            $total_progress_price = $item->total_price;
                                            $latest_progress_price = 0;
                                            $done_progress_price = 0;
                                            $remaing_progress_price = $total_progress_price - $done_progress_price;;

					                        $progress_min_amount = 0;

                                            $latest_invoice_amount = 0;
                                            $paid_invoice_amount = 0;
                                            $remaing_invoice_amount = $total_progress_price - $paid_invoice_amount;

                                            $latest_progress = $item->projectEstimationProduct->progress()->latest()->first();
                                            if($latest_progress){
                                                $latest_progress_price = $latest_progress->progress ? $latest_progress->progress : 0;
                                                $done_progress_price = ($latest_progress_price / 100) * $item->price;
                                                $remaing_progress_price = $total_progress_price - $done_progress_price;

                                                //progress amount
    					                        $progress_min_amount = isset($latest_progress->progress_amount) ? $latest_progress->progress_amount : 0;

                                                //Progress Invoice Amount
                                                $latest_invoice_amount = $latest_progress->progress_payment ? $latest_progress->progress_payment : 0;
                                                $paid_invoice_amount = ($latest_invoice_amount / 100) * $item->price;
                                                $remaing_invoice_amount = $total_progress_price - $paid_invoice_amount;
                                            }
                                        @endphp
                                        <div class="select-progress">
                                            <div class="progress-numbers">
                                                <span class="invoice-numbers finance-items"><b>Invoice: </b>&nbsp; {{ currency_format_with_sym($paid_invoice_amount) }} / {{ currency_format_with_sym($total_progress_price) }} (Remaining: {{ currency_format_with_sym($remaing_invoice_amount) }})</span>
                                            </div>
                                            <div class="select-payment-progress finance-items" style="margin-bottom:5px!important;">
                                                <div class="select-payment-progress-inner">
                                                    <input class="payment-progress" name="payment_progress[{{ $item->projectEstimationProduct->id }}]" type="range" id="payment-progress-slider-{{ $item->projectEstimationProduct->id }}" class="form-control"
                                                        min="0" value="{{ $latest_invoice_amount }}" data-min="{{ $latest_invoice_amount }}" max="100" step="5" data-id="{{ $item->projectEstimationProduct->id }}"
                                                        style="width: 100%;">
                                                    <span id="payment-slider-value-{{ $item->projectEstimationProduct->id }}" class="payment-slider-value">{{ $latest_invoice_amount }}%</span>
                                                </div>
                                            </div>
                                            <div class="progress-numbers finance-items">
                                                <span class="progress-numbers"><b>Progress: </b>&nbsp; {{ currency_format_with_sym($done_progress_price) }} / {{ currency_format_with_sym($total_progress_price) }} (Remaining: {{ currency_format_with_sym($remaing_progress_price) }})</span>
                                            </div>
                                            <div class="select-progress-inner">
                                                <input class="progress" name="progress[{{ $item->projectEstimationProduct->id }}]" type="range" id="progress-slider-{{ $item->projectEstimationProduct->id }}" min="0" value="{{ $latest_progress_price }}" data-min="{{ $latest_progress_price }}" max="100" step="5" data-id="{{ $item->projectEstimationProduct->id }}" style="width: 100%;">
                                                <span id="slider-value-{{ $item->projectEstimationProduct->id }}" class="slider-value">{{ $latest_progress_price }}%</span>
                                            </div>
                                        </div>
                                        <div class="sign_btn_block">
                                            <div class="dropdown">
                                                <button type="button" class="btn btn-sm btn-sig-menu" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="fa-solid fa-caret-down"></i>
                                                </button>
                                                <ul class="dropdown-menu" style="">
                                                    <li><a class="dropdown-item clearSig" href="#" data-id="{{ $item->projectEstimationProduct->id }}"><i class="fa-regular fa-trash-can me-2"></i>Delete</a></li>
                                                    <li><a class="dropdown-item commentSig" href="#" data-id="{{ $item->projectEstimationProduct->id }}"><i class="fa-regular fa-comment-dots me-2"></i>Comment</a></li>
                                                    <li><a class="dropdown-item quantitySig" href="#" data-id="{{ $item->projectEstimationProduct->id }}"><i class="fa-solid fa-hashtag me-2"></i>Quantity</a></li>
                                                    <li><a class="dropdown-item uploadSig" href="#" data-id="{{ $item->projectEstimationProduct->id }}"><i class="fa-solid fa-camera me-2"></i>Upload</a></li>
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="signature-field form-control position-relative">
                                            <div class="signature-placeholder" id="signature-placeholder-{{ $item->projectEstimationProduct->id }}">Signature</div>
                                            <input type="hidden" name="signatures[{{ $item->projectEstimationProduct->id }}]" id="SignupImage{{ $item->projectEstimationProduct->id }}" value="">
                                            <canvas id="items-signature-pad-{{ $item->projectEstimationProduct->id }}" class="signature-pad" data-id="{{ $item->projectEstimationProduct->id }}" height="100" width="300"></canvas>
                                        </div>
                                        <input type="hidden" name="estimation_id" value="{{ $item->projectEstimationProduct->project_estimation_id }}">
                                        <input type="hidden" name="progress_product_id" value="{{ $item->projectEstimationProduct->id }}">
                                        <div class="input-fields mt-2">
                                            <textarea class="comment_text d-none" id="comment-{{ $item->projectEstimationProduct->id }}" name="comments[{{ $item->projectEstimationProduct->id }}]" placeholder="Comment ..." data-id="{{ $item->projectEstimationProduct->id }}"></textarea>
                                            <div class="d-flex align-items-center gap-2 progress_amount d-none" data-id="{{ $item->projectEstimationProduct->id }}">
                                                <input type="number" class="form-control" id="progress_amount_{{ $item->projectEstimationProduct->id }}" min="{{ $progress_min_amount }}" max="{{ $item->projectEstimationProduct->quantity }}" value="{{ $progress_min_amount > 0 ? $progress_min_amount : '' }}" name="progress_amount[{{ $item->projectEstimationProduct->id }}]" placeholder="Amount ...">
                                                <span class="text-muted">/ {{ $item->projectEstimationProduct->quantity }} </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="description"><div class="desc-inner">{{ $item->projectEstimationProduct->description }}</div></td>
                                    <td class="history">
                                        <div class="progress-steps-wrapper">
                                            <div class="progress-steps">
                                                <!-- Progress Step 1 -->
                                                @foreach ($item->projectEstimationProduct->progress()->orderBy('id')->get() as $progress)
                                                    <div class="progress-step">
                                                        <div class="status1 pstatus">
                                                            <div class="progress_wrapper">
                                                                <div class="progress_labels">
                                                                    <div class="total_progress">
                                                                        <div class="progress-history-item">
                                                                            <span class="progress-percent">{{ $progress->progress }}%</span>
                                                                            <span class="progress-date">{{ date('d.m.y', strtotime($progress->created_at)) }}</span>
                                                                            @php 
                                                                                $user_name = "";
                                                                                if (isset($progress->progress_id) && !empty($progress->progress_id)) {
                                                                                    $user_name = ($progress->project_progress[0]['name']) ? ' ' . $progress->project_progress[0]['name'] . '</i>' : '';
                                                                                }
                                                                            @endphp
                                                                            <span class="user-avatar">{!! $user_name !!}</span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                        
                                        <div class="progress_files_row progress-files-group-{{ $item->projectEstimationProduct->id }}">

                                            <div class="progress_files d-none" data-id="{{ $item->projectEstimationProduct->id }}">
                                                <div id="progressdropBox" ondrop="handleProgressDrop(event)" ondragover="handleProgressDragOver(event, this)" data-id="{{ $item->projectEstimationProduct->id }}" data-estimationid="{{ $item->projectEstimationProduct->project_estimation_id }}">
                                                    <p style="font-size:20px">Drag &amp; Drop files here or click to select</p>
                                                </div>
                                                <input type="file" id="progressfileInput{{ $item->projectEstimationProduct->id }}" class="progressfileInput" multiple onchange="handleProgressFileSelect(event, this)" data-id="{{ $item->projectEstimationProduct->id }}" data-estimationid="{{ $item->projectEstimationProduct->project_estimation_id }}">
                                            </div>
                                    
                                            <div class="progress_files_preview_{{ $item->projectEstimationProduct->id }}">
                                                <div id="ProgressFilesPreviewContainer{{ $item->projectEstimationProduct->id }}"></div>
                                            </div>

                                            @if($item->projectEstimationProduct->progress_files)
                                                <div class="table-responsive mediabox item_mediabox_{{ $item->projectEstimationProduct->id }}">
                                                    @foreach ($item->projectEstimationProduct->progress_files as $prow)
                                                        @include('taskly::project_progress.project_progress_files')
                                                    @endforeach
                                                </div>
                                            @endif
                                            <div class="float-start d-flex">
                                                <button type="button" 
                                                        class="btn btn-sm btn-primary btn-icon btn_progress_bulk_delete_files_{{ $item->projectEstimationProduct->id }} m-1 d-none"
                                                        data-product-id="{{ $item->projectEstimationProduct->id }}"
                                                        data-estimation-id="{{ $item->projectEstimationProduct->project_estimation_id }}"
                                                        onclick="triggerDeleteFiles(this)">
                                                    <i class="ti ti-trash text-white" data-bs-toggle="tooltip" data-bs-original-title="Delete Files"></i> Delete Files
                                                </button>
                                            </div>
                                        </div>
                                    </td>
                                    
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            {{ Form::close() }}
        </div>

        <div class="progress-footer">
            <div class="float-end confirm_div_top" data-id="">
                <div class="progress-text">
                    <label><input type="checkbox" class="form-check-input" name="progress_final_confirm_checkbox" id="progress_final_confirm_checkbox" /> {{ __('I confirm the Progress above') }}.</label><br>
                    <a href="javascript:void(0);" class="progress-final-comment-icon"><small>({{ __('add Comments') }})</small></a>
                    <div class="progress-final-comment d-none"><textarea name="progress_final_comment" id="progress_final_comment" placeholder="{{ __('Comments...') }}"></textarea></div>
                </div>
                <div class="progress-date">
                    <input type="text" id="progress-date-time-picker" name="progress-date-time" value="{{ \Carbon\Carbon::now('Europe/Berlin')->format('d.m.Y - H:i') }}" readonly>
                </div>
                <div class="progress-client">
                    <input type="text" name="progress_final_user_name" id="progress_final_user_name" placeholder="{{ __('Name') }}" value="{{isset(\Auth::user()->name) ? \Auth::user()->name : '' }}" required>
                </div>
                <div class="progress-signature">
                    <canvas id="signature-pad" class="signature-pad progress-final-signature" height="100" width="300"></canvas>
                    <div class="sign_btn_block">
                        <div class="sign_btn_block_small">
                            <button type="button" class="btn btn-sm btn-danger progress_final_clear_sig" id="progress_final_clear_sig"><i class="fa-regular fa-trash-can"></i></button>
                        </div>
                    </div>
                    <input type="hidden" id="progress_final_signature" name="progress_final_signature" value="" />
                </div>
            </div>
            <div class="confirm_div" data-id="{{ isset($item->projectEstimationProduct->project_estimation_id) ? $item->projectEstimationProduct->project_estimation_id : 0}}">
                <a href="javascript:void(0)" class="confirm-progress-btn btn btn-sm btn-primary btn-icon m-1" data-id="{{ isset($item->projectEstimationProduct->project_estimation_id) ? $item->projectEstimationProduct->project_estimation_id : 0}}">
                    <span class="text-white">
                        {{ __('Confirm Progress') }}
                    </span>
                </a>
            </div>
        </div>
    </div>

</div>