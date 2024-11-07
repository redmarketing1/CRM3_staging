@if ($estimation->status != 2 && $estimation->status != 3 && $user->type == 'company')
    <div class="button-wrapper-fixed">
        <div class="button-wrapper-left">

            @permission('estimation add item option')
                <button type="button" id="add_estimation_item_btn"><i class="fa-solid fa-plus"></i>
                    {{ __('Item') }}
                </button>
            @endpermission

            @permission('estimation add group option')
                <button type="button" id="add_estimation_group_btn"><i class="fa-solid fa-plus"></i>
                    {{ __('Group') }}
                </button>
            @endpermission

            @permission('estimation add comment option')
                <button type="button" id="add_estimation_comment_btn"><i class="fa-solid fa-plus"></i>
                    {{ __('Comment') }}
                </button>
            @endpermission

            <div class="buttons-top">
                <div class="dropdown options-dropdown">
                    <span>
                        <a class="dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button"
                            aria-haspopup="false" aria-expanded="false">
                            <i class="fa-solid fa-list"></i>
                            {{ __('Options') }}
                        </a>
                        <div class="dropdown-menu dash-h-dropdown">
                            @permission('estimation remove option')
                                <a id="remove_items_btn" class="remove_items_btn dropdown-item">
                                    <span>{{ __('Remove') }}</span>
                                </a>
                            @endpermission
                        </div>
                    </span>
                </div>
            </div>

            <span class="btn-separator"></span>

            <div class="select-smart-template-div">
                <select name="" id="smart_template_id" class="ai_fields d-none">
                    <option value="">{{ __('Select Smart Block') }}</option>
                    @if (isset($smart_templates) && count($smart_templates) > 0)
                        @foreach ($smart_templates as $template)
                            <option value="{{ $template->id }}">{{ $template->title }}</option>
                        @endforeach
                    @endif
                </select>
                <button id="smart_template_generate"
                    class="call-ai-smart-template sb-go-btn sb-go-selected ai_fields d-none">
                    {{ __('Go') }}
                </button>
            </div>
        </div>

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
