 <tfoot>
     <tr id="estimation-edit-table-tfoot">
         <th class="column_reorder"></th>
         <th class="column_checkbox">
             <input type="checkbox" class="SelectAllCheckbox" name="" value="">
         </th>
         <th class="column_pos">{{ __('POS') }}</th>
         <th class="column_group">{{ __('Group Name') }}</th>
         <th class="column_name">{{ __('Name') }}</th>
         <th class="column_quantity">{{ __('Quantity') }}</th>
         <th class="column_unit">{{ __('Unit') }}</th>
         <th class="column_optional border-right">{{ __('Opt') }}</th>

         @if (isset($ai_description_field))
             <th class="column_ai_description border-left-right">
                 {{ __('Auto Description') }}</th>
         @endif

         @foreach ($allQuotes as $key => $quotes)
             <th class="column_single_price border-left" data-cardQuoteID="{{ $quotes->id }}">
                 {{ __('Single Price') }}
             </th>
             <th class="column_total_price border-right" data-cardQuoteID="{{ $quotes->id }}">
                 {{ __('Total Price') }}
             </th>
         @endforeach
     </tr>
 </tfoot>
