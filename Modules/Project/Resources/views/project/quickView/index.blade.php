<link rel="stylesheet" href="{{ asset('assets/css/project.quickView.css') }}">

@include('project::project.show.utility.header')
@include('project::project.show.utility.dashboard')
@include('project::project.show.utility.if_project_types')
@include('project::project.show.utility.activity_log')


@permission('files manage')
    @include('project::project.show.section.files')
@endpermission
@include('project::project.show.section.estimations')

@permission('progress manage')
    @include('project::project.show.section.project_progress')
@endpermission

@include('project::project.show.section.delay')
@include('project::project.show.section.milestone')


<script src="{{ asset('assets/js/project.quickView.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/exif-js"></script>

<script>
    $(document).ready(function() {
        const projectID = '{{ $project->id }}';
        const csrfToken = $('meta[name="csrf-token"]').attr('content');
        const supportedFormats = '{{ getAdminAllSetting()['local_storage_validation'] }}';
    });
</script>
