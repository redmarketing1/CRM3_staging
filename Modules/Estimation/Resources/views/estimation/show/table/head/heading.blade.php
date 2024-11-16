<tr id="estimation-edit-table-thead">
    <th class="column_reorder">
    </th>

    <th class="column_checkbox">
        <input type="checkbox" class="SelectAllCheckbox" x-model="selectAll">
    </th>

    <th class="column_pos">
        {{ __('Pos') }}
    </th>

    <th class="column_group">
        {{ __('Group Name') }}
    </th>

    <th class="column_name">
        <i class="fa-solid fa-indent expand_more show_all"></i>
        {{ __('Name') }}
    </th>

    <th class="column_quantity">
        {{ __('Quantity') }}
    </th>

    <th class="column_unit">
        {{ __('Unit') }}
    </th>

    <th class="column_optional border-right">
        {{ __('Opt') }}
    </th>

    @if (isset($ai_description_field))
        <th class="column_ai_description border-left-right">
            {{ __('Auto Description') }}
        </th>
    @endif

    @foreach ($allQuotes as $key => $quotes)
        <th class="column_single_price border-left quote_th{{ $quotes->id }}">
            {{ __('Single Price') }}
        </th>
        <th class="column_total_price border-right quote_th{{ $quotes->id }}">
            {{ __('Total Price') }}
        </th>
    @endforeach
</tr>
