@extends('layouts.main')

@php
    $profile = asset(Storage::url('uploads/avatar'));
@endphp
@section('page-title')
    {{ __('Edit Estimation') }}
@endsection
@section('title')
    {{ __('Edit Estimation') }}
@endsection
@push('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('Modules/Taskly/Resources/assets/css/custom.css') }}" type="text/css" />
@endpush
@section('page-breadcrumb')
    <a href="{{ route('project.index') }}">{{ __('All Project') }}</a>,
    {{-- <a href="{{ route('project.show', [$estimation->project_id]) }}">{{ $estimation->getProjectDetail->name }}</a>,{{ __('Edit') }} --}}
@endsection

@section('content')
    <div class="row" x-cloak x-data="estimationShow">
        <div class="row">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-12 order-lg-2">
                        <div class="card repeater table-card-full">
                            {{ Form::open(['route' => 'estimations.importdata', 'files' => true, 'id' => 'quote_form']) }}

                            <div class="card-body">
                                @include('estimation::estimation.show.section.header')
                                @include('estimation::estimation.show.table.index')
                            </div>

                            {{-- @include('estimation::estimation.show.section.description') --}}
                            @include('estimation::estimation.show.section.footer')

                            {{ Form::close() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('estimation::estimation.show.Modal.index')
@endsection

@push('scripts')
    <script src="{{ asset('Modules/Taskly/Resources/assets/js/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('js/jquery.repeater.min.js') }}"></script>
    <script src="{{ asset('Modules/Taskly/Resources/assets/libs/select2/dist/js/select2.min.js') }}"></script>
    <script src="{{ asset('Modules/Taskly/Resources/assets/js/jquery.nestable.js') }}"></script>
    <script src="{{ asset('Modules/Taskly/Resources/assets/js/custom.js') }}"></script>
@endpush
{{-- 
@push('scripts')
    <script>
        var project_estimation_id =
            'eyJpdiI6IlhyNlN2ZFJBTEpPVTBzY2crajU2dmc9PSIsInZhbHVlIjoiUVMrbGlyVmlNMHNrS2lWR2pnMEYydz09IiwibWFjIjoiZDI5Mjg3NGFmOGU3MTM3ZWM0MGE0Nzk4OWI3ZTYwMWU1NzJmOTNiY2NhMWM3OWRiYmM3ZDllOWE5NGQ3NzU5YyIsInRhZyI6IiJ9';
        let estimation_id = 11;
        var project_id =
            'eyJpdiI6ImsyeTEySTZiN0t0MzdqNFh6d0FLZEE9PSIsInZhbHVlIjoiWjQvdkg1VDZ1emw4OEl6QlNDaHN3Zz09IiwibWFjIjoiM2JmNGJhNjNiNjY0NTg1NWI0YjlmODQxNWQ3MWVhNzM5ZThjMDRkMmE2Mjk3ZjJkMWE2MjFiNDhjNTkyMWY5ZSIsInRhZyI6IiJ9';
        let first_quote_id = 8;
        let ai_description_field = {{ isset($ai_description_field) ? 1 : 0 }};
        let all_quotes = {!! json_encode($allQuotes) !!};
        let all_contractors = {!! json_encode($all_contractors ?? []) !!};
        let final_id = {!! json_encode($final_id) !!};
        let client_final_quote_id = {!! json_encode($client_final_quote_id) !!};
        let sub_contractor_final_quote_id = {!! json_encode($sub_contractor_final_quote_id) !!};
        let moneyFormat = '{{ site_money_format() }}';
        var estimation_table;
        let counter = 1;
        var total_colspan = 3;
        let columns_data = [];
        let subContractorDiscountIds = '#discount';
        let subContractorTaxIds = '#tax';

        for (const quote of all_quotes) {
            let single_price = "single_price_sc" + quote.id;
            subContractorDiscountIds += ", #discount_sc" + quote.id;
            subContractorTaxIds += ", #tax_sc" + quote.id;
        }
        // Formatter to display price in German format
        var moneyFormatter = site_money_format(moneyFormat);

        var execute_request = true;

        $(document).ready(function() {

            $(document).on("keyup", subContractorDiscountIds + "," +
                subContractorTaxIds,
                function(e) {
                    saveTableDataMultiple();
                });

            $(document).on("change",
                subContractorDiscountIds + "," + subContractorTaxIds,
                function(e) {
                    saveTableDataMultiple();
                }
            );

            $(document).on("click", "#update_pos_btn", function(e) {
                e.preventDefault();
                updatePOS();
            });

        });

        function calculateMarkup(e, quote_id, type = 'quote') {
            var new_table_data = [];
            var quates_ids = [];
            $('.item_row').each(function(value) {
                var item_id = $(this).data('id');

                var tableData = {};
                tableData['item_id'] = item_id;

                $(this).find(".column_single_price").each(function() {
                    var quote_id = $(this).data('quote');
                    if (jQuery.inArray(quote_id, quates_ids) === -
                        1) {
                        quates_ids.push(quote_id);
                    }
                    var single_price_key = "single_price_sc" +
                        quote_id;
                    var total_price_key = "total_price_sc" +
                        quote_id;
                    //	tableData[i][single_price_key] = parseFloat($('.base_single_price_sc_input_'+item_id+'_'+quote_id).val());
                    var converte_input = $(
                        '.base_single_price_sc_input_' +
                        item_id + '_' + quote_id).val();
                    tableData[single_price_key] = converte_input;
                    var converte_total = $(
                        '.total_price_sc_input_' + item_id +
                        '_' + quote_id).val();
                    tableData[total_price_key] = converte_total;
                });
                new_table_data.push(tableData);
            });
            let markup = $(e).val();
            markup = parseLocaleNumber(moneyFormat, markup);
            let old = $(e).attr("data-old");
            if (quote_id > 0 && markup != old) {

                $.ajax({
                    url: "{{ route('estimations.markup.calculate') }}",
                    type: "POST",
                    contentType: "application/json",
                    data: JSON.stringify({
                        type: type,
                        markup: markup,
                        quote_id: quote_id,
                        estimation_id: estimation_id,
                        data: new_table_data
                    }, replacerFunc()),
                    success: function(response) {

                        for (let i = 0; i < response.length; i++) {
                            $.each(quates_ids, function(key, val) {
                                if (quote_id == val) {
                                    let key_class =
                                        'single_price_sc_input_' +
                                        response[i].item_id +
                                        '_' + val;
                                    let key =
                                        'single_price_sc' + val;
                                    if (typeof response[i][
                                            key
                                        ] != "undefined") {
                                        $('.' + key_class).val(
                                            response[i][key]
                                        );
                                        //	changePrice($('.'+key_class).closest("tr"), false);
                                    }
                                }
                            });
                        }
                    }
                });
            }
        }

        function removeCellColors(id, type = null) {
            if (id > 0) {
                if (type === 'sub_contractor') {
                    $(`.sc${id},.gross_sc${id},.gross_with_discount_sc${id},.net_sc${id},.net_with_discount_sc${id},.discount_sc${id},.tax_sc${id},.markup_sc${id},.finalize_quote${id},.finalize_quote_title${id}`)
                        .removeClass('finalized_sub_contractor_quote');
                } else if (type === 'client') {
                    $(`.sc${id},.gross_sc${id},.gross_with_discount_sc${id},.net_sc${id},.net_with_discount_sc${id},.discount_sc${id},.tax_sc${id},.markup_sc${id},.finalize_quote${id},.finalize_quote_title${id}`)
                        .removeClass('finalized_client_quote');
                } else {
                    $(`.sc${id},.gross_sc${id},.gross_with_discount_sc${id},.net_sc${id},.net_with_discount_sc${id},.discount_sc${id},.tax_sc${id},.markup_sc${id},.finalize_quote${id},.finalize_quote_title${id}`)
                        .removeClass('finalized_quote');
                    if (!$(`.finalize_quote_title${id}`).hasClass(
                            'total-main-title')) {
                        $(`.finalize_quote_title${id}`).addClass(
                            'total-main-title');
                    }
                }
            } else {
                $(`.1,.gross,.gross_with_discount,.net,.net_with_discount,.discount,.tax,.markup`)
                    .css("background",
                        '#f5f5f5');
            }
        }


        function updateCellColors(id, type = null, update = true) {

            if (update && id > 0) {
                var form_details = {
                    id: id
                };
                if (type && type != null) {
                    Object.assign(form_details, {
                        type: type
                    });
                }

                $.ajax({
                    url: "{{ route('estimations.quote.final') }}",
                    type: "POST",
                    contentType: "application/json",
                    data: JSON.stringify(form_details),
                    success: function(response) {
                        if (type === null) {
                            var new_final_id = response.id;
                            $(`.sc${final_id},.gross_sc${final_id},.gross_with_discount_sc${final_id},.net_sc${final_id},.net_with_discount_sc${final_id},.discount_sc${final_id},.tax_sc${final_id},.markup_sc${final_id},.finalize_quote${final_id},.finalize_quote_title${final_id}`)
                                .removeClass('finalized_quote');
                            if (!$(`.finalize_quote_title${final_id}`)
                                .hasClass('total-main-title')) {
                                $(`.finalize_quote_title${final_id}`)
                                    .addClass('total-main-title');
                            }
                            final_id = new_final_id;
                            updateCellColors(new_final_id, type, false);
                        } else {
                            if (type === 'sub_contractor') {
                                $(`.sc${sub_contractor_final_quote_id},.gross_sc${sub_contractor_final_quote_id},.gross_with_discount_sc${sub_contractor_final_quote_id},.net_sc${sub_contractor_final_quote_id},.net_with_discount_sc${sub_contractor_final_quote_id},.discount_sc${sub_contractor_final_quote_id},.tax_sc${sub_contractor_final_quote_id},.markup_sc${sub_contractor_final_quote_id},.finalize_quote${sub_contractor_final_quote_id},.finalize_quote_title${sub_contractor_final_quote_id}`)
                                    .removeClass(
                                        'finalized_sub_contractor_quote'
                                    );
                                sub_contractor_final_quote_id = id;
                            } else if (type === 'client') {
                                $(`.sc${client_final_quote_id},.gross_sc${client_final_quote_id},.gross_with_discount_sc${client_final_quote_id},.net_sc${client_final_quote_id},.net_with_discount_sc${client_final_quote_id},.discount_sc${client_final_quote_id},.tax_sc${client_final_quote_id},.markup_sc${client_final_quote_id},.finalize_quote${client_final_quote_id},.finalize_quote_title${client_final_quote_id}`)
                                    .removeClass(
                                        'finalized_client_quote');
                                client_final_quote_id = id;
                            }
                            updateCellColors(id, type, false);
                        }
                    },
                    error: function(error) {
                        // Handle any errors that occur during the Ajax request
                        console.error(
                            "Error sending data to the server:",
                            error);
                    }
                });
            } else {
                if (id > 0) {
                    if (type === 'sub_contractor') {
                        $(`.sc${id},.gross_sc${id},.gross_with_discount_sc${id},.net_sc${id},.net_with_discount_sc${id},.discount_sc${id},.tax_sc${id},.markup_sc${id},.finalize_quote${id},.finalize_quote_title${id}`)
                            .addClass('finalized_sub_contractor_quote');
                    } else if (type === 'client') {
                        $(`.sc${id},.gross_sc${id},.gross_with_discount_sc${id},.net_sc${id},.net_with_discount_sc${id},.discount_sc${id},.tax_sc${id},.markup_sc${id},.finalize_quote${id},.finalize_quote_title${id}`)
                            .addClass('finalized_client_quote');
                    } else {
                        // $(`.sc${id},.gross_sc${id},.gross_with_discount_sc${id},.net_sc${id},.net_with_discount_sc${id},.discount_sc${id},.tax_sc${id},.markup_sc${id},.finalize_quote${id}`).attr('style', 'background: #5eb839 !important');
                        $(`.sc${id},.gross_sc${id},.gross_with_discount_sc${id},.net_sc${id},.net_with_discount_sc${id},.discount_sc${id},.tax_sc${id},.markup_sc${id},.finalize_quote${id},.finalize_quote_title${id}`)
                            .addClass('finalized_quote');
                        if ($(`.finalize_quote_title${id}`).hasClass(
                                'total-main-title')) {
                            $(`.finalize_quote_title${id}`).removeClass(
                                'total-main-title');
                        }
                    }
                } else {
                    $(`.1,.gross,.gross_with_discount,.net,.net_with_discount,.discount,.tax,.markup`)
                        .css("background",
                            '#5eb839');
                }
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

        $(document).on('click', '.call-ai-smart-template', function(e) {
            e.preventDefault();

            var btnText = $(this).html();
            var currentV = $(this);
            currentV.prop('disabled', true);

            var $btn = currentV.html(
                '<div class="fa fa-spinner fa-spin"></div>');

            var inputLength = $('.item_selection:checked').length;
            if (inputLength == 0) {

                toastrs("Error", "Please select at least one record");
                currentV.prop('disabled', false);
                currentV.html(btnText);
                return false;
            }

            var item_ids = $('#remove_item_ids').val();
            var smart_template_id = $('#smart_template_id').val();
            var token = $('meta[name="csrf-token"]').attr('content');
            var ai_ajax_url =
                "{{ route('estimations.call-ai-smart-template-new') }}";

            // return false;

            $.ajax({
                url: ai_ajax_url,
                type: "POST",
                contentType: "application/json",
                data: JSON.stringify({
                    estimation_id: estimation_id,
                    item_ids: item_ids,
                    smart_template_id: smart_template_id,
                    _token: token
                }),
                success: function(response) {
                    if (response.status == true) {
                        toastrs("Success", response.message);
                        setTimeout(function() {
                            location.reload()
                        }, 1000);
                    } else {
                        toastrs("Error", response.message);
                        currentV.prop('disabled', false);
                        currentV.html(btnText);
                        // return false;
                    }
                },
                error: function(error) {
                    currentV.prop('disabled', false);
                    currentV.html(btnText);
                    toastrs("Error",
                        "Failed: something went wrong!");
                }
            });
        });

        $(document).on("click", ".btn_replace_descriptions", function(e) {
            e.preventDefault();
            var token = $('meta[name="csrf-token"]').attr('content');
            var replace_ai_desc_ids = $("#remove_item_ids").val();

            $.ajax({
                url: "{{ route('estimations.replace_ai_desc.estimate') }}",
                type: "POST",
                contentType: "application/json",
                data: JSON.stringify({
                    estimation_id: estimation_id,
                    replace_ai_desc_ids: replace_ai_desc_ids,
                    _token: token
                }),
                success: function(response) {
                    if (response.status == true) {
                        toastrs("Success", response.message);
                        setTimeout(function() {
                            location.reload();
                        }, 500);
                    } else {
                        toastrs("Error", response.message);
                    }
                },
                error: function(error) {
                    // Handle any errors that occur during the Ajax request
                    console.error(
                        "Error sending data to the server:",
                        error);
                }
            });
        });
    </script>
@endpush --}}
