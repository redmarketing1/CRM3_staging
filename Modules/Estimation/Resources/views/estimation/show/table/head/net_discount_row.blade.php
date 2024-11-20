<tr class="total-line-top no-sort" id="net_with_discount_row">
    <th colspan="7" class="toplabel total-net-discount">
        {{ __('Net incl. Discount') }}
    </th>

    @if (isset($ai_description_field))
        <th rowspan="5" class="border-left-right toptotal ai-head column_ai_description">
            <button type="button" class="btn_replace_descriptions d-none"
                data-bs-whatever="{{ __('Replace with AI description') }}">
                <!-- <i class="fa-solid fa-retweet"></i><br> -->
                {{ __('Replace current Descriptions') }}
            </button>
        </th>
    @endif

    @foreach ($allQuotes as $quotes)
        <th colspan="2" class="totalnr toptotal total-net-discount border-left-right"
            data-cardQuoteID="{{ $quotes->id }}">
            -
        </th>
    @endforeach
</tr>
