<tr class="total-line-top no-fixed-header no-sort">
    <th colspan="7" class="toplabel total-net">
        {{ __('Net') }}
    </th>

    @foreach ($allQuotes as $quotes)
        <th colspan="2" class="totalnr toptotal total-net border-left-right" data-cardQuoteID="{{ $quotes->id }}">
            -
        </th>
    @endforeach
</tr>
