<tr class="form-group mb-5 hide" role="row" data-orderable="false">
    <th data-orderable="false" colspan="2" class="filterable-bulk-action">
        <select name="action" id="bulk-action-selector" class="hide">
            <option>Bulk actions</option>
            <option value="delete" data-type="delete" data-text="This action can not be undone. Do you want to delete?"
                data-title="Are you sure delete ?">
                Delete Projects
            </option>
            <option value="archive" data-type="archive"
                data-text="The project will move to archive. You can revert it later"
                data-title="Are you sure archive ?">
                Move To Archive
            </option>
            <option value="duplicate" data-type="duplicate"
                data-text="The project will be duplicate. You can delete it after created"
                data-title="Are you sure duplicate this projects ?">
                Duplicate Projects
            </option>
        </select>
    </th>
    <th data-orderable="false">
        <div class="search-table">
            <input type="search" class="form-control text-xl" placeholder="Search project name"
                name="searchByProjectName" id="searchByProjectName" />
        </div>
    </th>
    <th data-orderable="false">archive</th>
    <th data-orderable="false" class="filterable-status-table">
        <div class="status-table">
            <select class="form-control" id="filterableStatusDropdown">
                <option value="">Select Status</option>
            </select>
        </div>
    </th>
    <th data-orderable="false">
        <div class="search-table">
            <input type="search" class="form-control text-xl" placeholder="Search comment..." name="searchByComment"
                id="searchByComment" />
        </div>
    </th>
    <th data-orderable="false" class="filterable-priority-table">
        <div class="priority-table">
            <select class="form-control" id="filterablePriorityDropdown">
                <option value="">Select priority</option>
            </select>
        </div>
    </th>
    <th data-orderable="false"></th>
    <th data-orderable="false" class="filterable-budget-net-table">
        <div class="input-items mt-2">
            <div class="field">
                <input type="number" id="filter_budget_from" class="input-min form_filter_field" min="0"
                    value="0" step="1">
            </div>
            <div class="separator">-</div>
            <div class="field">
                <input type="number" id="filter_budget_to" class="input-max form_filter_field" step="1">
            </div>
        </div>
        <div class="range-input">
            <input type="range" class="w-100 range-input-selector" min="0" value="1000" max="1000"
                step="1">
            <span class="range-output-value">0</span>
        </div>
    </th>
    <th data-orderable="false" class="filterable-daterange-table">
        <input type='text' class="form-control daterange form_filter_field" placeholder="{{ __('Date') }}"
            id="filterableDaterange" />
    </th>
    <th data-orderable="false"><!--- action ---></th>
</tr>
