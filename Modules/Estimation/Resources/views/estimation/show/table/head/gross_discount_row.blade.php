<tr class="total-line-top no-fixed-header no-sort" id="gross_with_discount_row">
    <th colspan="7" class="toplabel total-gross-discount">
        {{ __('Gross incl. Discount') }}
    </th>

    @foreach ($allQuotes as $quotes)
        <th colspan="2"
            @class([
                'totalnr toptotal total-gross-discount border-left-right cardQuote',
                'quote' => $quotes->is_final,
                'clientQuote' => $quotes->final_for_client,
                'subcontractor' => $quotes->final_for_sub_contractor
            ])
            data-cardQuoteID="{{ $quotes->id }}">
            {{ currency_format_with_sym($quotes->gross_with_discount) }}
        </th>
    @endforeach
</tr>
