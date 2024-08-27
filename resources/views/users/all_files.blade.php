@if(count($files) > 0)
@foreach ($files as $file)
	<div scope="row" class="mediaimg">
		<div class="media align-items-center">
			<div class="media-body user-group1">
				@php
					$is_image = 0;
					$ext = pathinfo($file->file, PATHINFO_EXTENSION);
					$supported_image = array('gif', 'jpg', 'jpeg', 'png');
					if(in_array($ext, $supported_image)){
						$is_image = 1;
					}
				@endphp
				<a class="lightbox-link" href="{{ get_file('uploads/files/') . '/' . $file->file }}" @if($is_image > 0) data-lightbox="gallery" @else target="_blank" @endif data-title="Image placeholder">
					<img alt="Image placeholder"
						 src="{{ get_file('uploads/files/') . '/' . $file->file }}"
						 class="img-thumbnail project_file_{{ $file->id }}" onerror="this.onerror=null; this.src='https://upload.wikimedia.org/wikipedia/commons/thumb/8/87/PDF_file_icon.svg/1667px-PDF_file_icon.svg.png';">
				</a>
				<br>
			</div>
		</div>
		<div class="text-end actionbuttons">
			<div class="action-btn bg-danger ms-2">
				<label class="container">
					<input type="checkbox" class="image_selection" value="{{ Crypt::encrypt($file->id) }}"  onchange="selected_images()" data-id="{{ $file->id }}">
					<span class="checkmark"></span>
				  </label>
			</div>
			<div class="action-btn bg-primary ms-2">
				<a href="{{ get_file('uploads/files/') . '/' . $file->file }}"
					class="mx-3 btn btn-sm d-inline-flex align-items-center"
					download="">
					<i data-bs-toggle="tooltip"
						data-bs-original-title="{{ __('Download') }}"
						class="ti ti-arrow-bar-to-down text-white"></i>
				</a>
			</div>
			<div class="action-btn bg-secondary ms-2">
				<a href="{{ get_file('uploads/files/') . '/' . $file->file }}"
					class="mx-3 btn btn-sm d-inline-flex align-items-center"
					data-bs-toggle="tooltip" target="_blank"
					data-original-title="{{ __('Preview') }}">
					<i class="ti ti-crosshair text-white"
						data-bs-toggle="tooltip"
						data-bs-original-title="{{ __('Preview') }}"></i>
				</a>
			</div>
		</div>
		<div class="mediainfo">
			<span class="filename">{{ $file->file }}</span>
			<span class="filedate">{{ \Auth::user()->dateFormat($file->created_at) }}</span>
		</div>
	</div>
@endforeach
@endif