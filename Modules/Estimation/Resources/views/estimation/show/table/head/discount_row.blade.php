<tr class="total-line-top totalsetting no-fixed-header" id="discount_row">
    <th colspan="7" class="toplabel total-discount">
        {{-- empty column --}}
    </th>

    @foreach ($allQuotes as $quote)
        <th colspan="2"
            @class([
                'total-settings border-left-right cardQuote',
                'quote' => $quote->is_final,
                'clientQuote' => $quote->final_for_client,
                'subcontractor' => $quote->final_for_sub_contractor
            ])
            data-cardQuoteID="{{ $quote->id }}">
            <span class="dt-column-title">

                <div class="totalnr toptotal total-markup">
                    <div class="total-setting-label">
                        {{ __('Markup') }}
                    </div>
                    <div class="total-markup-input">
                        <input type="text" name="item[{{ $quote->id }}][markup]"
                            value="{{ $quote->markup ?? 0 }}" id="quoteMarkup" class="form-control form-blur">
                    </div>
                </div>


                <div class="totalnr toptotal total-markup">
                    <div class="total-setting-label">
                        {{ __('Cash Discount') }}
                    </div>
                    <div class="total-discount-input">
                        <input type="text" name="item[{{ $quote->id }}][discount]"
                            class="form-control cash-discount form-blur"
                            min="0.1"
                            max="100"
                            value="{{ $quote->discount }}">
                    </div>
                </div>
            </span>
        </th>
    @endforeach
</tr>
