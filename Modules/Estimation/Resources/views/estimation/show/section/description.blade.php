<div class="estimation-footer-box">
    <div class="form-group col-md-6">
        <label for="technical_description" class="form-label">{{ __('Technical Description') }}</label>
        <textarea rows="3" class="form-control border-0 tinyMCE" name="technical_description" id="technical_description"
            placeholder="{{ __('Technical Description') }}">{{ $estimation->init_status == 0 ? nl2br(e($estimation->technical_description)) : $estimation->technical_description }}</textarea>
    </div>
</div>
