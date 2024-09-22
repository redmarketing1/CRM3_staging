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
