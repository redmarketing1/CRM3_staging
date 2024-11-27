<div class="button-wrapper-fixed">
    @if (auth()->user()->type == 'company')
        <div class="button-wrapper-left gap-3">
            @permission('estimation add item option')
                <button type="button" @click="addItem('item')">
                    <i class="fa-solid fa-plus"></i>
                    {{ __('Item') }}
                </button>
            @endpermission

            @permission('estimation add group option')
                <button type="button" @click="addItem('group')">
                    <i class="fa-solid fa-plus"></i>
                    {{ __('Group') }}
                </button>
            @endpermission

            @permission('estimation add comment option')
                <button type="button" @click="addItem('comment')">
                    <i class="fa-solid fa-plus"></i>
                    {{ __('Comment') }}
                </button>
            @endpermission

            @permission('estimation remove option')
                <button type="button" @click="removeItem">
                    <i class="fa-solid fa-plus"></i>
                    {{ __('Remove') }}
                </button>
            @endpermission
        </div>
    @endif

    <div class="align-items-center d-inline-flex">
        <div class="align-items-center d-inline-flex gap-2 m-r-20 text-xl">
            <input type="checkbox" class="form-check-input" id="autoSaveEnabled" x-model="autoSaveEnabled"
                @input="autoSaveEnabled && saveTableData()">
            <label class="form-check-label" for="autoSaveEnabled">Enable auto save</label>
            <div class="last-time" x-text="lastSaveText"></div>
        </div>

        @if ($estimation->status != 2 && $estimation->status != 3 && auth()->user()->type == 'company')
            <button type="button" id="save-button" @click="saveTableData()" class="btn btn-primary">
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
