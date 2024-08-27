<div scope="row" class="mediaimg progress_mediaimg">
    <div class="media align-items-center">
        <div class="media-body user-group1">
            @php
            $file_extension = strtolower(pathinfo($prow['file'], PATHINFO_EXTENSION));
            $is_image = in_array($file_extension, ['jpg', 'jpeg', 'png', 'gif']);
            @endphp
            @if($is_image)
                <a class="lightbox-link" href="{{ get_file('uploads/progress_files/') . '/' . $prow['file'] }}" data-lightbox="gallery" data-title="{{ $prow['file'] }}" class="file-{{ strtoupper($file_extension) }}">
                    <img alt="Image placeholder" src="{{ get_file('uploads/progress_files/') . '/' . $prow['file'] }}" class="img-thumbnail project_progress_file_{{ $prow['id'] }}" onerror="this.onerror=null; this.src='{{ URL('assets/images/default_icon.png') }}';">
                </a>
            @else
                <a href="{{ get_file('uploads/progress_files/') . '/' . $prow['file'] }}" target="_blank" data-title="{{ $prow['file'] }}" class="file-{{ strtoupper($file_extension) }}">
                    <div class="fileprev">
                        {{ strtoupper($file_extension) }}
                    </div>
                </a>
            @endif
        </div>
    </div>
    <div class="text-end actionbuttons">
        <div class="action-btn checkbox-btn ms-2">
            <label class="container" title="{{ __('Select Image') }}">
                <input type="checkbox" class="progress_image_selection" value="{{ Crypt::encrypt($prow['id']) }}" onchange="selected_progress_images(this)" data-id="{{ $prow['id'] }}" data-item-id="{{ $prow['product_id'] }}">
                <span class="checkmark"></span>
            </label>
        </div>
        <div class="action-btn bg-primary ms-2">
            <a href="{{ get_file('uploads/progress_files/') . '/' . $prow['file'] }}" class="btn btn-sm d-inline-flex align-items-center" download="">
                <i data-bs-toggle="tooltip" data-bs-original-title="{{ __('Download') }}" class="ti ti-arrow-bar-to-down text-white"></i>
            </a>
        </div>
        <div class="action-btn bg-secondary ms-2">
            <a href="{{ get_file('uploads/progress_files/') . '/' . $prow['file'] }}" class="btn btn-sm d-inline-flex align-items-center" data-bs-toggle="tooltip" target="_blank" data-original-title="{{ __('Preview') }}">
                <i class="ti ti-crosshair text-white" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Preview') }}"></i>
            </a>
        </div>
    </div>
    <div class="mediainfo">
        <span class="filedate">{{ \Auth::user()->dateFormat($prow['created_at']) }}</span>
        <span class="filename">
            <a href="{{ get_file('uploads/progress_files/') . '/' . $prow['file'] }}" target="_blank" data-title="{{ $prow['file'] }}" class="file-{{ strtoupper($file_extension) }}">
                {{ $prow['file'] }}
            </a>
        </span>
    </div>
</div>