 <tr class="total-line-top no-fixed-header" id="gross_row">
     <th colspan="7" class="toplabel total-gross no-sort">
         {{ __('Gross (incl. VAT)') }}
     </th>

     @foreach ($allQuotes as $key => $quotes1)
         @php
             $tax_key = 'tax_sc' . $quotes1->id;
             $gross_key = 'gross_sc' . $quotes1->id;
         @endphp
         <th colspan="2"
             class="toptotal total-gross border-left-right finalize_quote{{ $quotes1->id }} gross_tax_sc{{ $quotes1->id }}">
             <span class="dt-column-title">
                 <div id="{{ $tax_key }}_neuwest"
                     class="totalnr {{ $tax_key }} toptotal total-discount total-vat-input">
                     <select name="tax[{{ $tax_key }}]" id="{{ $tax_key }}">
                         <option value="19" {{ $quotes1->tax == 19 ? 'selected' : '' }}>
                             19%
                         </option>
                         <option value="0" {{ $quotes1->tax == 0 ? 'selected' : '' }}>0%
                         </option>
                     </select>
                 </div>
                 <div id="{{ $gross_key }}_neuwest"
                     class="totalnr {{ $gross_key }}totalsetting total-gross-total">
                     <div id="{{ $gross_key }}" class="">
                         {{ currency_format_with_sym($quotes1->gross_with_discount) }}
                     </div>
                     <input type="hidden" id="{{ $gross_key }}_value" name="{{ $gross_key }}"
                         value="{{ $quotes1->gross_with_discount }}">
                 </div>
             </span>
         </th>
     @endforeach
 </tr>
