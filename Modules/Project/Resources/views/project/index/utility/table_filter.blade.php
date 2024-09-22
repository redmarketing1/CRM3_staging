<tr class="form-group mb-5 hide" role="row">
    <th colspan="2" class="filterable-bulk-action">
        <select name="action" id="bulk-action-selector" class="hide">
            <option>Bulk actions</option>
            <option value="delete" data-title="Are you sure delete ?"
                data-text="This action can not be undone. Do you want to delete?" data-type="delete">Delete Projects
            </option>
            <option value="archive" data-title="Are you sure archive ?"
                data-text="The project will move to archive. You can revert it later" data-type="archive">Move To
                Archive</option>
        </select>
    </th>
    <th>
        <div class="search-table">
            <input type="search" class="form-control text-xl" placeholder="Search project name"
                name="searchByProjectName" id="searchByProjectName" />
        </div>
    </th>
    <th>archive</th>
    <th class="filterable-status-table">
        <div class="status-table">
            <select class="form-control" id="filterableStatusDropdown">
                <option value="">Select Status</option>
            </select>
        </div>
    </th>
    <th><!-- comment ---></th>
    <th class="filterable-priority-table">
        <div class="priority-table">
            <select class="form-control" id="filterablePriorityDropdown">
                <option value="">Select priority</option>
            </select>
        </div>
    </th>
    <th></th>
    <th class="filterable-budget-net-table">
        {{-- <div class="input-items mt-2">
            <div class="field">
                <input type="number" id="filter_budget_from" class="input-min form_filter_field" min="0"
                    value="0" max="9999">
            </div>
            <div class="separator">-</div>
            <div class="field">
                <input type="number" id="filter_budget_to" class="input-max form_filter_field" max="1000"
                    value="1000">
            </div>
        </div> --}}
        <div class="range-input">
            <input type="range" id="budget_range" class="w-100" min="0" max="1000" value="1000"
                step="1">
        </div>
    </th>
    <th class="filterable-daterange-table">
        <input type='text' class="form-control daterange form_filter_field" placeholder="{{ __('Date') }}"
            id="filterableDaterange" />
    </th>
    <th><!--- action ---></th>
</tr>
