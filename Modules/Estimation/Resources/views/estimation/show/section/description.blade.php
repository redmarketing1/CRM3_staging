<div class="mb-5 mt-5 px-4">
    <div class="form-group col-md-12">
        <label for="technical_description" class="form-label">{{ __('Technical Description') }}</label>
        <textarea rows="10" class="form-control border-0 tinyMCE" name="technical_description" id="technical_description"
            placeholder="{{ __('Technical Description') }}">{{ $estimation->init_status == 0 ? nl2br(e($estimation->technical_description)) : $estimation->technical_description }}</textarea>
    </div>
</div>
