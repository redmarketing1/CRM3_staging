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

    @if ($estimation->status != 2 && $estimation->status != 3 && auth()->user()->type == 'company')
        <div class="button-wrapper-right">
            <button type="button" id="save-button" onclick="saveTableDataMultiple()"
                class="btn btn-primary">{{ __('Save') }}
            </button>
            @if (Auth::user()->type == 'company')
                <button type="button" id="" onclick="saveTableData('preview')"
                    class="btn btn-secondary mx-2">{{ __('Preview & Complete') }}</button>
            @endif
        </div>
    @elseif ($estimation->status = 2)
        <div class="button-wrapper-right">
            @if (Auth::user()->type == 'company')
                <button type="button" id="" onclick="saveTableData('preview')"
                    class="btn btn-secondary mx-2">{{ __('Save & Complete') }}</button>
            @endif
        </div>
    @endif
</div>
