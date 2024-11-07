 <tfoot>
     <tr id="estimation-edit-table-tfoot">
         <th class="column_reorder" data-dt-order="disable">
         </th>
         <th class="column_checkbox" data-dt-order="disable">
             <input type="checkbox" class="SelectAllCheckbox" name="" value="">
         </th>
         <th class="column_pos" data-dt-order="disable">{{ __('POS') }}</th>
         <th class="column_group" data-dt-order="disable">{{ __('Group Name') }}</th>
         <th class="column_name" data-dt-order="disable">{{ __('Name') }}</th>
         <th class="column_quantity" data-dt-order="disable">{{ __('Quantity') }}</th>
         <th class="column_unit" data-dt-order="disable">{{ __('Unit') }}</th>
         <th class="column_optional border-right" data-dt-order="disable">
             {{ __('Opt') }}
         </th>
         @if (isset($ai_description_field))
             <th class="column_ai_description border-left-right" data-dt-order="disable">
                 {{ __('Auto Description') }}</th>
         @endif
         @foreach ($allQuotes as $key => $quotes)
             <th class="column_single_price border-left quote_th{{ $quotes->id }}" data-dt-order="disable">
                 {{ __('Single Price') }}</th>
             <th class="column_total_price border-right quote_th{{ $quotes->id }}" data-dt-order="disable">
                 {{ __('Total Price') }}</th>
         @endforeach
     </tr>
 </tfoot>
