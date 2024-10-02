<tr class="form-group mb-5 hide" role="row" data-orderable="false">
    <th data-orderable="false" colspan="2" class="filterable-bulk-action">
        <select name="action" id="bulk-action-selector" class="hide">
            <option value="bulk">{{ trans('Bulk actions') }}</option>
            <option value="delete" data-type="delete"
                data-text="{{ trans('This action can not be undone. Do you want to delete?') }}"
                data-title="{{ trans('Are you sure delete ?') }}">
                {{ trans('Delete') }}
            </option>
            <option value="archive" data-type="archive"
                data-text="{{ trans('The project will move to archive. You can revert it later') }}"
                data-title="{{ trans('Are you sure archive ?') }}">
                {{ trans('Archive') }}
            </option>
            <option value="unarchive" class="hide" data-type="unarchive"
                data-text="{{ trans('The project will move to unarchive. You can revert it later') }}"
                data-title="{{ trans('Are you sure unarchive ?') }}">
                {{ trans('Unarchive') }}
            </option>
            <option value="duplicate" data-type="duplicate"
                data-text="{{ trans('The project will be duplicate. You can delete it after created') }}"
                data-title="{{ trans('Are you sure duplicate this projects ?') }}">
                {{ trans('Duplicate') }}
            </option>
        </select>
    </th>
    <th data-orderable="false" colspan="2" class="filterable-search">
        <div class="search-table">
            <input type="search" class="form-control text-xl" placeholder="{{ trans('Search') }}" name="searchProject"
                id="searchProject" />
        </div>
    </th>
    <th data-orderable="false">{{ trans('archive') }}</th>
    <th data-orderable="false" class="filterable-status-table">
        <div class="status-table">
            <select class="form-control" id="filterableStatusDropdown">
            </select>
        </div>
    </th>
    <th data-orderable="false" class="filterable-priority-table">
        <div class="priority-table">
            <select class="form-control" id="filterablePriorityDropdown">
            </select>
        </div>
    </th>
    <th data-orderable="false"></th>
    <th data-orderable="false" class="filterable-budget-net-table">

        <div class="price-input input-items">
            <div class="field">
                <input type="text" id="filter_price_from" class="input-min form_filter_field" value="0">
            </div>
            <div class="field">
                <input type="text" id="filter_price_to" class="input-max form_filter_field">
            </div>
        </div>

        <div class="my-3 net-range-slider">
            <div class="slider_filter price_slider range_slider">
                <div class="progress2"></div>
            </div>
            <div class="range-input price_range_input">
                <input type="range" class="range-min" id="filter_price_from" min="0" value="0"
                    step="1">
                <input type="range" class="range-max" id="filter_price_to" min="0" step="1">
            </div>
        </div>
    </th>
    <th data-orderable="false" class="filterable-daterange-table">
        <input type='text' class="form-control daterange form_filter_field" placeholder="{{ trans('Date') }}"
            id="filterableDaterange" />
    </th>
    <th data-orderable="false">
        <div class="buttons--right">
            <button type="button" id="clearFilter" class="clear_filter">
                {{ trans('clear all') }}
            </button>
        </div>
    </th>
</tr>
