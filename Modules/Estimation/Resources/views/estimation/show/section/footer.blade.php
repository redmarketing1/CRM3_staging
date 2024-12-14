<div class="button-wrapper-fixed">
    @if (auth()->user()->type == 'company')
        <div class="button-wrapper-left gap-3 insert-row">
            @permission('estimation add item option')
                <button type="button" data-actionInsert="item">
                    <i class="fa-solid fa-plus"></i>
                    {{ __('Item') }}
                </button>
            @endpermission

            @permission('estimation add group option')
                <button type="button" data-actionInsert="group">
                    <i class="fa-solid fa-plus"></i>
                    {{ __('Group') }}
                </button>
            @endpermission

            @permission('estimation add comment option')
                <button type="button" data-actionInsert="comment">
                    <i class="fa-solid fa-plus"></i>
                    {{ __('Comment') }}
                </button>
            @endpermission

            @permission('estimation remove option')
                <button type="button" data-actionremove>
                    <i class="fa-solid fa-plus"></i>
                    {{ __('Remove') }}
                </button>
            @endpermission
        </div>
    @endif

    <div class="align-items-center d-inline-flex">
        <div class="align-items-center d-inline-flex gap-2 m-r-20 text-xl">
            <input type="checkbox" class="form-check-input" id="autoSaveEnabled" checked>
            <label class="form-check-label" for="autoSaveEnabled">Enable auto save</label>
            <div class="last-time lastSaveTimestamp"></div>
        </div>

        @if ($estimation->status != 2 && $estimation->status != 3 && auth()->user()->type == 'company')
            <button type="button" id="save-button" class="btn btn-primary">
                {{ __('Save') }}
            </button>
            @if (Auth::user()->type == 'company')
                <button type="button" id="" onclick="saveTableData('preview')"
                    class="btn btn-secondary mx-2">{{ __('Preview & Complete') }}</button>
            @endif
        @elseif ($estimation->status = 2)
            @if (Auth::user()->type == 'company')
                <button type="button" id="" onclick="saveTableData('preview')"
                    class="btn btn-secondary mx-2">{{ __('Save & Complete') }}</button>
            @endif
        @endif
    </div>
</div>
