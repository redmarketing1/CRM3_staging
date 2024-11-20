<tr class="total-line-top no-fixed-header no-sort" id="gross_with_discount_row">
    <th colspan="7" class="toplabel total-gross-discount">
        {{ __('Gross incl. Discount') }}
    </th>

    @foreach ($allQuotes as $quotes)
        <th colspan="2" class="totalnr toptotal total-gross-discount border-left-right"
            data-cardQuoteID="{{ $quotes->id }}">
            -
        </th>
    @endforeach
</tr>
