<div class="row">
    <div class="col-sm-12">
        <div class="card shadow-none">
            <div class="card-header card-body table-border-style border-0 py-0 px-5">
                <div class="card-body table-responsive p-0">
                    {{ Form::open(['route' => ['project.progress.store', $project->id], 'enctype' => 'multipart/form-data', 'id' => 'progressForm', 'class' => 'project-progress-form']) }}
                    <div class="progress-footer px-5">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="progress-text">
                                    <label class="text-xl" for="progress_confirm">
                                        <input type="checkbox" class="form-check-input" name="progress_confirm"
                                            id="progress_confirm" required />
                                        {{ __('I confirm the Progress above') }}.
                                    </label>
                                    <div class="mt-4 progress-final-comment">
                                        <textarea name="progress_comment" id="progress_comment" placeholder="{{ __('Write comments...') }}" class="w-75"
                                            required></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="progress-date mb-4">
                                    <input type="text" class="w-100" id="progress-date-time-picker"
                                        name="progress-date-time"
                                        value="{{ \Carbon\Carbon::now('Europe/Berlin')->format('d.m.Y - H:i') }}"
                                        readonly>
                                </div>
                                <div class="progress-client mb-4">
                                    <input type="text" class="w-100" name="progress_final_user_name"
                                        id="progress_final_user_name" placeholder="{{ __('Name') }}"
                                        value="{{ Auth::user()->name ?? '' }}">
                                </div>
                                <div class="mb-4" id="signaturePad">
                                    <canvas id="signatureCanvas" class="signature-pad w-100" height="100"></canvas>
                                    <div class="sign_btn_block">
                                        <div class="sign_btn_block_small">
                                            <button type="button" class="btn btn-default my-1"
                                                id="signatureCanvasClear" title="clear signature">
                                                <i class="fa-regular fa-trash-can"></i> clear signature
                                            </button>
                                        </div>
                                    </div>
                                    <input type="hidden" class="d-none" name="progress_signature" id="signatureImage">
                                </div>
                            </div>
                        </div>
                        <div class="my-3">
                            <button type="submit" class="btn btn-primary">
                                <span class="text-white">
                                    {{ __('Confirm Progress') }}
                                </span>
                            </button>
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>
