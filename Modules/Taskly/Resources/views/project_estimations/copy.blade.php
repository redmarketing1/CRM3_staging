{{ Form::open(['route' => ['estimations.copy.store', $estimation_id], 'method' => 'POST']) }}
<div class="row">
    <div class="col-12">
        <div class="form-group m-2">
            <div class="form-check">
                {{ Form::checkbox('quotes', 'true', '', ['class' => 'form-check-input ', 'id' => 'quotes']) }}
                {{ Form::label('quotes', __('Include All Quotes'), ['class' => 'form-check-label']) }}
            </div>
        </div>
    </div>
<div class="modal-footer">
    <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{ __('Close') }}</button>
    <button type="submit" class="btn  btn-primary">{{ __('Copy') }}</button>
</div>

{{ Form::close() }}
