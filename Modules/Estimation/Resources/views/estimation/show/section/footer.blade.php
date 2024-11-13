@if ($estimation->status != 2 && $estimation->status != 3 && auth()->user()->type == 'company')
    <div class="button-wrapper-fixed">
        <div class="button-wrapper-right">
            <button type="button" id="save-button" onclick="saveTableDataMultiple()"
                class="btn btn-primary">{{ __('Save') }}
            </button>
            @if (Auth::user()->type == 'company')
                <button type="button" id="" onclick="saveTableData('preview')"
                    class="btn btn-secondary mx-2">{{ __('Preview & Complete') }}</button>
            @endif
        </div>
    </div>
@elseif ($estimation->status = 2)
    <div class="button-wrapper-fixed">
        <div class="button-wrapper-left"></div>
        <div class="button-wrapper-right">
            @if (Auth::user()->type == 'company')
                <button type="button" id="" onclick="saveTableData('preview')"
                    class="btn btn-secondary mx-2">{{ __('Save & Complete') }}</button>
            @endif
        </div>
    </div>
@endif
