<ul class="nav nav-tabs" id="fileModalTab" role="tablist">
	<li class="nav-item">
		<a class="nav-link nav_fileModalTab active" id="upload-tab" data-toggle="tab" href="#upload" role="tab" aria-controls="upload" aria-selected="true">Upload</a>
	</li>
	@if(isset($project_files) && count($project_files) > 0)
		<li class="nav-item">
			<a class="nav-link nav_fileModalTab" id="project_files-tab" data-toggle="tab" href="#project_files" role="tab" aria-controls="select" aria-selected="false">Project Files</a>
		</li>
	@endif
</ul>
<div class="tab-content" id="fileModalTabContent">
	<div class="tab-pane fade show active" id="upload" role="tabpanel" aria-labelledby="upload-tab">
		<div class="form-group">
			<label for="smart-chat-file-input">{{ __('Choose files:') }}</label>
			<input id="smart-chat-file-input" type="file" class="form-control" name="files[]" accept="image/*" multiple>
		</div>
		<button type="button" class="btn btn-primary btn_chat_upload">{{ __('Upload') }}</button>
	</div>
	<div class="tab-pane fade show" id="project_files" role="tabpanel" aria-labelledby="project_files-tab">
		<div class="table-responsive mediabox">
			@if(isset($project_files) && count($project_files) > 0)
				@foreach ($project_files as $file)
					@php
						//dd($file);
					@endphp
					<div scope="row" class="mediaimg {{ ($file->is_default == 1) ? 'default_file' : '' }}">
						<div class="media align-items-center">
							<div class="media-body user-group1">
								@php
									$file_extension = strtolower(pathinfo($file->file_path, PATHINFO_EXTENSION));
									$is_image = in_array($file_extension, ['jpg', 'jpeg', 'png', 'gif']);
								@endphp
								@if($is_image)
									<a class="lightbox-link" href="{{ get_file($file->file_path) }}" data-lightbox="gallery" data-title="{{ $file->file }}" class="file-{{ strtoupper($file_extension) }}">
										<img alt="Image placeholder" src="{{ get_file($file->file_path) }}" class="img-thumbnail project_file_{{ $file->id }}" onerror="this.onerror=null; this.src='{{ URL('assets/images/default_icon.png') }}';">
									</a>
								@else
									<a href="{{ get_file($file->file_path) }}" target="_blank" data-title="{{ $file->file }}" class="file-{{ strtoupper($file_extension) }}">
										<div class="fileprev">
											{{ strtoupper($file_extension) }}
										</div>
									</a>
								@endif
							</div>
						</div>
						<div class="text-end actionbuttons">
							<div class="action-btn checkbox-btn ms-2 img-select">
								<label class="container" title="{{ __('Select Image') }}" >
									<input type="checkbox" class="image_selection" value="{{ Crypt::encrypt($file->id) }}"  data-id="{{ $file->id }}" data-image_src="{{ get_file($file->file_path) }}">
									<span class="checkmark"></span>
								</label>
							</div>
						</div>
						<div class="mediainfo">
							<span class="filedate">{{ \Auth::user()->dateFormat($file->created_at) }}</span>
							<span class="filename">
								<a href="{{ get_file($file->file_path) }}" target="_blank" data-title="{{ $file->file }}" class="file-{{ strtoupper($file_extension) }}">
								{{ $file->file }}
								</a>
							</span>
						</div>
					</div>
				@endforeach
			@endif
		</div>
		<button type="button" class="btn btn-primary btn_chat_attach">{{ __('Attach') }}</button>
	</div>
</div>