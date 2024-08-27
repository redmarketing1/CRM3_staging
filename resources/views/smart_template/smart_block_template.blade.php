@foreach($smart_block_description as $row)
	@php
	$content = (isset($row['content']) && !empty($row['content'])) ? $row['content'] : null;
	if (!empty($content)) {
		$data = strip_tags($content);
		$content_data = str_replace("&nbsp;", "", $data);
		$new_content_data = html_entity_decode($content_data);
		
		$smart_block_name = isset($promt_old_data[$row['id']]) ? $promt_old_data[$row['id']]['title'] : '';
		$smart_block_slug = isset($promt_old_data[$row['id']]) ? $promt_old_data[$row['id']]['slug'] : '';
		$promt_old_data_desc = isset($promt_old_data[$row['id']]) ? $promt_old_data[$row['id']]['description'] : null;
		if(isset($promt_old_data_desc) && $promt_old_data_desc != ""){
			$data = strip_tags($promt_old_data_desc);
			$content_data = str_replace("&nbsp;", "", $data);
			$new_content_data = html_entity_decode($content_data);
		}
	}
	@endphp
	<div class="col-md-44 prompt_block" id="smart_block_{{$row['id']}}">
		<input type="hidden" class="form-control smart_block_id" name="smart_block_ids[]" value="{{$row['id']}}" />
		<div class="form-group mt-3">
			{{ Form::label('smart_block_name', __('Extracted Value'), ['class' => 'form-label']) }}
			<input type="text" class="form-control smart_block_name" name="smart_block_name[]" id="smart_block_name" value="{{ $smart_block_name }}" />
			<small class="smart_block_slug_label">{{ $smart_block_slug }}</small>
		</div>	
		<div class="form-group mt-3 d-none">
			{{ Form::label('smart_block_slug', __('Slug'), ['class' => 'form-label']) }}
			<input type="text" class="form-control smart_block_slug" name="smart_block_slug[]" id="smart_block_slug" value="{{ $smart_block_slug }}" readonly />
		</div>
		<div class="form-group mt-3">
			<textarea class="form-control smart_block_description" rows='10' name="smart_block_description[]" id="smart_block_description">{{$new_content_data}}</textarea>
		</div>
	</div>
@endforeach