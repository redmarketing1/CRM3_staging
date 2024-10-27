<div class="col-xxl-12 card-container mt-5 mb-5">
    @permission('team member view')
        @include('project::project.show.card.team_members')
    @endpermission
    {{-- @include('project::project.show.card.client_share') --}}

    @include('project::project.show.card.appointments')
    @include('project::project.show.card.description')

    @include('project::project.show.card.project_labels')
    @include('project::project.show.card.contact')
</div>
