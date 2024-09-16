<table id="projectsTable">
    <thead>
        <tr>
            <th colspan="2" data-orderable="false">Project Name</th>
            <th>Status</th>
            <th data-orderable="false">Comments</th>
            <th>Priority</th>
            <th data-orderable="false">Construction</th>
            <th data-orderable="false">Project Net</th>
            <th>Date</th>
            <th data-orderable="false">Action</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($dataTables as $data)
            <tr>
                <td>
                    <div class="data-thubmnail">
                        <img src="https://neu-west.com/crm3_staging/assets/images/default_thumbnail3.png" alt=""
                            srcset="">
                    </div>
                </td>
                <td>
                    <div class="d-flex flex-column">
                        <h2 class="data-name font-medium text-xl">{{ $data->name }}</h2>
                        <div class="d-flex data-sub-name flex-column font-normal">
                            <span class="construction-client-name">
                                <a href="#" class="text-sm text-black">Markus Hartwig</a>
                            </span>
                            <span class="text-sm text-black">
                                Steinfurter Allee, 44
                            </span>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="data-project-status">
                        {{ $data->status }}
                    </div>
                </td>
                <td>Initial phase completed</td>
                <td>High</td>
                <td>Building A</td>
                <td>50000</td>
                <td>2023-07-15</td>
                <td><button>View</button></td>
            </tr>
        @endforeach
    </tbody>
</table>
