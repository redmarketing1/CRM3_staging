<tr class="total-line-top no-fixed-header" id="gross_row">
    <th colspan="7" class="toplabel total-gross no-sort">
        {{ __('Gross (incl. VAT)') }}
    </th>

    @foreach ($allQuotes as $quote)
        <th colspan="2" class="toptotal total-gross border-left-right">
            <span class="dt-column-title">
                <div class="totalnr toptotal total-discount total-vat-input">
                    <select name="tax[]">
                        <option value="19" {{ $quote->tax == 19 ? 'selected' : '' }}>
                            19%
                        </option>
                        <option value="0" {{ $quote->tax == 0 ? 'selected' : '' }}>
                            0%
                        </option>
                    </select>
                </div>
                <div class="totalnr total-gross-total">
                    <div class="">
                        {{ currency_format_with_sym($quote->gross_with_discount) }}
                    </div>
                </div>
            </span>
        </th>
    @endforeach
</tr>
