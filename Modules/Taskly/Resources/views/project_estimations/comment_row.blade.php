@if (isset($with_group) && $with_group == true)
	<tr class="group_row group grp_no{{$product->group->group_pos}}" data-group_pos="{{$product->group->group_pos}}" data-group="{{$product->group->group_name}}" data-group_id="{{$product->group_id}}" data-parent_id="{{$product->group->parent_id}}">
		<td class="column_reorder" data-dt-order="disable">
			<i class="fa-solid fa-up-down reorder-item reorder_group_btn"></i>
		</td>
		<td class="column_checkbox grp_checkbox_td" data-dt-order="disable">
			<input type="checkbox" class="group_checkbox" data-group="Group" data-group_pos="{{$product->group->group_pos}}" id="SelectGroupCheckbox" name="" value="{{$product->group_id}}">
		</td>
		<td class="column_pos grouppos">
			{{$product->group->group_pos}}
		</td>
		<td colspan="4" class="column_name grouptitle border-right">
			<div class="div-desc-toggle">
				<i class="desc_toggle fa fas fa-solid fa-caret-right grp-dt-control"></i>
				<input type="text" class="form-control grouptitle-input" value="{{$product->group->group_name}}">
			</div>
		</td>
		@if(isset($ai_description_field))
			<td class="column_ai_description border-left-right">
				<div class="ai-result">{{$product->ai_description}}</div>
			</td>
		@endif
		@foreach ($quote_items[$product->id] as $quoteItem)
			<td class="text-right grouptotal border-left-right" colspan="2" data-quote_id="{{$quoteItem->estimate_quote_id}}" data-group_total="0">
				0
			</td>
		@endforeach
	</tr>
@endif
<tr class="item_row comment_row" data-id="{{$product->id}}" data-group_id="{{$product->group_id}}" data-group_pos="{{$product->group->group_pos}}" data-type="{{$product->type}}">
	<td class="column_reorder"><i class="fa fa-bars reorder-item"></i></td>
	<td class="column_checkbox"><input type="checkbox" name="multi_id" class="item_selection  grp_checkbox{{$product->group_id}}" value="{{$product->id}}" onchange="selected_quote_items()"></td>
	<td class="column_pos"><div class="pos-inner">{{$product->pos}}</div><input type="hidden" class="form-control pos_input_{{$product->id}}" value="{{$product->pos}}"></td>
	@php
		$padding_left = 0;
		$explode_pos = explode(".", $product->pos);
		$count_points = count($explode_pos);
		$new_count = $count_points - 2;
		$padding_left = $new_count * 20;
	@endphp
	<td colspan="4" class="border-right column_name" style="padding-left: {{ $padding_left }}px !important"><input type="text" class="form-control comment_input_box mr-2 comment_input_{{$product->id}}" value="{{$product->comment}}"></td>
	@if(isset($ai_description_field))
		<td class="column_ai_description ai-content border-left-right">
			<div class="ai-result">{{$product->ai_description}}</div>
		</td>
	@endif
	@if(isset($quote_items[$product->id]))
		@foreach ($quote_items[$product->id] as $quoteItem)
			<td class="column_single_price border-left">-</td>
			<td class="column_total_price border-right">-</td>
		@endforeach
	@endif
</tr>