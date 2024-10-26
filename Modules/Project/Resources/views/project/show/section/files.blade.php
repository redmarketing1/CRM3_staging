<div id="useradd-7" class="project-files files-card">
    <div class="card files-card">
        <div class="card-header d-flex justify-content-between">
                    <h5 class="">{{ __('Files') }}</h5>
                    <div class="">
                        @if (\Auth::user()->type == 'company')
                            {!! Form::open(['method' => 'POST', 'id' => 'bulk_delete_form']) !!}
                                <input type="hidden" value="" name="remove_files_ids" id="remove_files_ids">
                                <button type="button" class="btn btn-sm btn-primary btn-icon show_confirm btn_bulk_delete_files m-1 d-none">
                                    <i class="ti ti-trash text-white" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Delete Files') }}"></i> {{ __('Delete Files') }}
                                </button>
                            {{ Form::close() }}
                        @endif
                    </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <div id="dropBox" ondrop="handleDrop(event)" ondragover="handleDragOver(event)">
                        <p style="font-size:20px ">{{ __('Drag & Drop files here or click to select') }}</p>
                    </div>
                    <input type="file" id="fileInput" multiple onchange="handleFileSelect(event)" />
                </div>
                <div class="col-md-12">
                    <div id="previewContainer"></div>
                </div>
            </div>

            <div class="table-responsive mediabox">
            </div>
        </div>
    </div>
</div>