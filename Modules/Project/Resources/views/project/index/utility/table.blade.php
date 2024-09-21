<div class="bulk_action">
    <div class="d-flex gap-4 mb-3">
        <a href="javascript:void(0)" class="btn btn-primary px-3 text-xl" data-title="Are you sure archive ?"
            data-text="The project will move to archive. You can revert it later" data-type="archive">
            <i class="ti ti-archive text-white"></i>
            Move to Archive
        </a>
        <a href="javascript:void(0)" class="btn btn-primary px-3 text-xl" data-title="Are you sure delete ?"
            data-text="This action can not be undone. Do you want to delete?" data-type="delete">
            <i class="ti ti-trash text-white"></i>
            Delete Projects
        </a>
    </div>
</div>

<table id="projectsTable">
    <thead>
        @include('project::project.index.utility.table_filter')
        <tr class="theader">
            <th><input type="checkbox"></th> <!-- Checkbox Column -->
            <th colspan="2" data-orderable="true">Project Name</th>
            <th style="display: none">Archive</th>
            <th data-orderable="false">Status</th>
            <th data-orderable="false">Comments</th>
            <th data-orderable="true">Priority</th>
            <th data-orderable="false">Construction</th>
            <th data-orderable="true">Project Net</th>
            <th data-orderable="true">Date</th>
            <th data-orderable="false">Action</th>
        </tr>
    </thead>
    <tbody>
    </tbody>
</table>
