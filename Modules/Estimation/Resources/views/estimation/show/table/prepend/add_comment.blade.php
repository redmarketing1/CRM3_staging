 <tr class="item_row comment_row" :data-id="index" data-type="comment">

     <td class="column_reorder">
         <i class="fa fa-bars reorder-item"></i>
     </td>

    <td class="column_checkbox">
        <input type="checkbox" class="item_selection">
    </td>

     <td class="column_pos">
         <div class="pos-inner" x-text="getPosNumber(index)"></div>
     </td>

     <td colspan="4" class="border-right column_name">
         <input type="text" name="[item][name]" class="form-control"
             value="{{ trans('Write your Comment') }}">
     </td>

     @foreach ($allQuotes as $quotes)
         <td class="column_single_price border-left">-</td>
         <td class="column_total_price border-right">-</td>
     @endforeach
 </tr>
