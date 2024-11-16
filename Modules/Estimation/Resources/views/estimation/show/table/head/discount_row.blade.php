<tr class="total-line-top totalsetting no-fixed-header" id="discount_row">
    <th colspan="7" class="toplabel total-discount">
        {{-- empty column --}}
    </th>
 
    @foreach ($allQuotes as $key => $quotes1)
        @php
            $markup_key = 'markup_sc' . $quotes1->id;
            $discount_key = 'discount_sc' . $quotes1->id;
        @endphp

        <th colspan="2" class="total-settings border-left-right">
            <span class="dt-column-title">

                <div class="totalnr toptotal total-markup">
                    <div class="total-setting-label">
                        {{ __('Markup') }}
                    </div>
                    <div class="total-markup-input">
                        <input type="text" name="markup"
                            value="{{ currency_format_with_sym($quotes1->markup, '', '', false) }}"
                            class="form-control">
                    </div>
                </div>


                <div class="totalnr toptotal total-markup">
                    <div class="total-setting-label">
                        {{ __('Cash Discount') }}
                    </div>
                    <div class="total-discount-input">
                        <input type="text" name="discount" class="form-control"
                            value="{{ currency_format_with_sym($quotes1->discount, '', '', false) }}">
                    </div>
                </div>
            </span>
        </th>
    @endforeach
</tr>
