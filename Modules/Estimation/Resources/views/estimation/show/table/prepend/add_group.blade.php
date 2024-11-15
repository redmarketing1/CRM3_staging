 <tr class="group group_row" data-group_pos="02" data-group="Group"
     data-group_id="7142" data-parent_id="">

     <td class="column_reorder">
         <i class="fa-solid fa-up-down reorder-item reorder_group_btn"></i>
     </td>

    <td class="column_checkbox">
        <input type="checkbox" class="item_selection">
    </td>

     <td class="column_pos grouppos" x-text="getPosNumber(index)"></td>

     <td colspan="4" class="column_name grouptitle border-right">
         <div class="div-desc-toggle">
             <i class="fa fas fa-solid fa-caret-right"></i>
             <input type="text" class="form-control grouptitle-input"
                 value="{{ trans('Group Name') }}" autocomplete="off">
         </div>
     </td>

     @foreach ($allQuotes as $quotes)
         <td colspan="2" class="text-right grouptotal border-left-right">
             {{ currency_format_with_sym(00) }}
         </td>
     @endforeach
 </tr>
