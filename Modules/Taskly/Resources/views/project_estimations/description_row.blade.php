<tr class="description_row" data-id="{{$product->id}}" data-group_pos="{{$product->group->group_pos}}">
	@php
		$description_colspan = 0;
		if(isset($ai_description_field)) {
			$description_colspan++;
		}
		if(isset($quote_items[$product->id])) {
			foreach ($quote_items[$product->id] as $quoteItem) {
				$description_colspan += 2;
			}
		}
	@endphp
	<td colspan="3"></td>
	<td colspan="4" class="column_name desc_column">
		<div class="desc-div hide div-view">{{$product->description}}</div>
		<textarea style="width:100%" class="description_input description_input_{{$product->id}} edit-view" data-id="{{$product->id}}">{{$product->description}}</textarea>
	</td>
	<td colspan="{{$description_colspan}}"></td>
</tr>