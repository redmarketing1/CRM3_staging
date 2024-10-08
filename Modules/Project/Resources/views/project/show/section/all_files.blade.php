@if (count($files) > 0)
    @foreach ($files as $file)
        <div scope="row" class="mediaimg {{ $file->is_default == 1 ? 'default_file' : '' }}">
            <div class="media align-items-center">
                <div class="media-body user-group1">
                    @php
                        $file_extension = strtolower(pathinfo($file->file_name, PATHINFO_EXTENSION));
                        $is_image = in_array($file_extension, ['jpg', 'jpeg', 'png', 'gif']);
                    @endphp
                    @if ($is_image)
                        <a class="lightbox-link"
                            href="{{ get_file('uploads/projects/') . '/' . $file->file_name }}"
                            data-lightbox="gallery" data-title="{{ $file->file_name }}"
                            class="file-{{ strtoupper($file_extension) }}">
                            <img alt="Image placeholder"
                                src="{{ get_file('uploads/projects/') . '/' . $file->file_name }}"
                                class="img-thumbnail project_file_{{ $file->id }}"
                                onerror="this.onerror=null; this.src='{{ URL('assets/images/default_icon.png') }}';">
                        </a>
                    @else
                        <a href="{{ get_file('uploads/projects/') . '/' . $file->file_name }}"
                            target="_blank" data-title="{{ $file->file_name }}"
                            class="file-{{ strtoupper($file_extension) }}">
                            <div class="fileprev">
                                {{ strtoupper($file_extension) }}
                            </div>
                        </a>
                    @endif


                </div>
            </div>
            <div class="text-end actionbuttons">
                <div class="action-btn checkbox-btn ms-2 img-select">
                    <label class="container" title="{{ __('Select Image') }}">
                        <input type="checkbox" class="image_selection" value="{{ $file->id }}"
                            onchange="selected_images()" data-id="{{ $file->id }}">
                        <span class="checkmark"></span>
                    </label>
                </div>
                <div class="action-btn bg-primary ms-2 hide">
                    <a href="{{ get_file('uploads/projects/') . '/' . $file->file_name }}"
                        class="btn btn-sm d-inline-flex align-items-center" download="">
                        <i data-bs-toggle="tooltip" data-bs-original-title="{{ __('Download') }}"
                            class="ti ti-arrow-bar-to-down text-white"></i>
                    </a>
                </div>
                <div class="action-btn bg-secondary ms-2 hide">
                    <a href="{{ get_file('uploads/projects/') . '/' . $file->file_name }}"
                        class="btn btn-sm d-inline-flex align-items-center" data-bs-toggle="tooltip" target="_blank"
                        data-original-title="{{ __('Preview') }}">
                        <i class="ti ti-crosshair text-white" data-bs-toggle="tooltip"
                            data-bs-original-title="{{ __('Preview') }}"></i>
                    </a>
                </div>
                @if (\Auth::user()->type == 'company')
                    <div class="action-btn bg-info ms-2 hide">
                        <a href="#" class="btn btn-sm d-inline-flex align-items-center" data-bs-toggle="modal"
                            data-bs-target="#exampleModal"
                            data-url="{{ route('project.file.edit', [$project->id, $file->id]) }}"
                            data-bs-whatever="{{ __('Edit Files') }}"> <span class="text-white"> <i class="ti ti-edit"
                                    data-bs-toggle="tooltip"
                                    data-bs-original-title="{{ __('Edit') }}"></i></span></a>
                    </div>
                @endif
                <div class="action-btn checkbox-btn ms-2 default_image_selection_outer hide">
                    <label class="container" data-bs-toggle="tooltip" title="{{ __('Select as Project Thumbnail') }}">
                        <input type="checkbox" class="default_image_selection" value="{{ $file->id }}"
                            {{ $file->is_default == 1 ? 'checked' : '' }}>
                        <span class="checkmark"></span>
                    </label>
                </div>
            </div>
            <div class="mediainfo">
                <span class="filedate">{{ \Auth::user()->dateFormat($file->created_at) }}</span>
                <span class="filename">
                    <a href="{{ get_file('uploads/projects/') . '/' . $file->file_name }}" target="_blank"
                        data-title="{{ $file->file_name }}" class="file-{{ strtoupper($file_extension) }}">
                        {{ $file->file }}</span>
                </a>

            </div>
        </div>
    @endforeach
@endif
