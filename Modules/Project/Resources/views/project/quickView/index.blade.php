@include('project::project.show.utility.dashboard')

<div class="col-xxl-12 card-container mt-5 mb-5">
    @permission('team member view')
        @include('project::project.quickView.team')
    @endpermission

    @include('project::project.quickView.contact')
    @include('project::project.quickView.label')
</div>

{{-- @include('project::project.show.section.estimations') --}}

@permission('progress manage')
    @include('project::project.show.section.project_progress')
@endpermission

@include('project::project.show.section.delay')
@include('project::project.show.section.milestone')
