<tr class="group_row group grp_no{{ $estimationGroup->group_pos }}" data-group_pos="{{ $estimationGroup->group_pos }}"
    data-group="{{ $estimationGroup->group_name }}" data-group_id="{{ $estimationGroup->id }}"
    data-parent_id="{{ $estimationGroup->parent_id }}">

    <td class="column_reorder" data-dt-order="disable">
        <i class="fa-solid fa-up-down reorder-item reorder_group_btn"></i>
    </td> 

    <td class="column_checkbox">
        <input type="checkbox" class="item_selection">
    </td>

    <td class="column_pos grouppos">
        {{ $estimationGroup->group_pos }}
    </td>

    <td colspan="4" class="column_name grouptitle border-right">
        <div class="div-desc-toggle">
            <i class="desc_toggle fa fas fa-solid fa-caret-right grp-dt-control"></i>
            <input type="text" class="form-control grouptitle-input" value="{{ $estimationGroup->group_name }}">
        </div>
    </td>

    @if (isset($ai_description_field))
        <td class="column_ai_description border-left-right" data-dt-order="disable"></td>
    @endif

    @foreach ($allQuotes as $quote)
        <td class="text-right grouptotal border-left-right quote_th{{ $quote->id }}" colspan="2"
            data-quote_id="{{ $quote->id }}" data-group_total="0">
            10101
        </td>
    @endforeach
</tr>

{{-- <tr class="group_row group" data-groupID="{{ $estimationGroup->id }}" data-groupPosID="{{ $estimationGroup->group_pos }}"
    data-parentID="{{ $estimationGroup->parent_id }}">

    <td class="column_reorder" data-dt-order="disable">
        <i class="fa-solid fa-up-down reorder-item reorder_group_btn"></i>
    </td>

    <td class="column_checkbox grp_checkbox_td" data-dt-order="disable">
        <input type="checkbox" class="group_checkbox" data-group="Group" id="SelectGroupCheckbox"
            value="{{ $estimationGroup->id }}">
    </td>

    <td class="column_pos grouppos">
        {{ $estimationGroup->group_pos }}
    </td>

    <td colspan="4" class="column_name grouptitle border-right">
        <div class="div-desc-toggle">
            <i class="desc_toggle fa fas fa-solid fa-caret-right grp-dt-control"></i>
            {{ $estimationGroup->group_name }}
        </div>
    </td>

    @foreach ($allQuotes ?? [] as $quote)
        <td class="text-right grouptotal border-left-right quote_th{{ $quote->id }}" colspan="2"
            data-quote_id="{{ $quote->id }}" data-group_total="0">
            1212
        </td>
    @endforeach
</tr>
 --}}
