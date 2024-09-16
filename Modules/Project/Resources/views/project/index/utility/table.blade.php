<table id="projectsTable">
    <thead>
        <tr>
            <th colspan="2" data-orderable="true">Project Name</th>
            <th data-orderable="true">Status</th>
            {{-- <th data-orderable="false">Comments</th> --}}
            <th data-orderable="true">Priority</th>
            <th data-orderable="false">Construction</th>
            <th data-orderable="true">Project Net</th>
            <th data-orderable="true">Date</th>
            <th data-orderable="false">Action</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($dataTables as $data)
            <tr id="project-items" data-project-backgroundColor="{{ $data->status->background_color }}"
                data-project-fontColor="{{ $data->status->font_color }}">
                <td>
                    <div class="data-thubmnail">
                        <img src="https://neu-west.com/crm3_staging/assets/images/default_thumbnail3.png" alt=""
                            srcset="">
                    </div>
                </td>
                <td>
                    <div class="d-flex flex-column text-left">
                        <h2 class="data-name font-medium text-xl">{{ $data->name }}</h2>
                        {{-- <div class="d-flex data-sub-name flex-column font-normal">
                            <span class="construction-client-name">
                                <a href="#" class="text-sm text-black">
                                    Markus Hartwig
                                </a>
                            </span>
                            <span class="text-sm text-black">
                                Steinfurter Allee, 44
                            </span>
                        </div> --}}
                    </div>
                </td>
                <td>
                    <div class="data-project-status">
                        {{ $data->status->name }}
                    </div>
                </td>
                {{-- <td>{{ $data->comments ?? 'N/A' }}</td> --}}
                <td data-fontColor="{{ $data->priority->background_color ?? '#c3c3c3' }}">
                    {{ $data->priority->name ?? 'N/A' }}
                </td>
                <td>Building A</td>
                <td>{{ currency_format_with_sym($data->budget) }}</td>
                <td>{{ company_datetime_formate($data->created_at) }}</td>
                <td><button>View</button></td>
            </tr>
        @endforeach
    </tbody>
</table>
