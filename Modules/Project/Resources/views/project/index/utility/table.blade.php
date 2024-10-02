<table id="projectsTable">
    <thead>
        @include('project::project.index.utility.table_filter')
        <tr class="theader">
            <th class="checkbox"><input id="select-all" type="checkbox"></th> <!-- Checkbox Column -->
            <th class="empty" data-orderable="false"></th>
            <th class="project-name" data-orderable="false">{{ trans('Project Name') }}</th>
            <th class="comments" data-orderable="false">{{ trans('Comments') }}</th>
            <th class="archive" style="display: none">{{ trans('Archive') }}</th>
            <th class="status" data-orderable="false">{{ trans('Status') }}</th>
            <th class="priority" data-orderable="false">{{ trans('Priority') }}</th>
            <th class="construction" data-orderable="false">{{ trans('Construction') }}</th>
            <th class="project-net" data-orderable="true">{{ trans('Project Net') }}</th>
            <th class="date" data-orderable="true">{{ trans('Date') }}</th>
            <th class="action" data-orderable="false">{{ trans('Action') }}</th>
        </tr>
    </thead>
    <tbody>
    </tbody>
</table>
