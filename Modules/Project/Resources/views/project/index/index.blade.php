@extends('layouts.main')

@section('page-title')
    {{ __('Manage Projects') }}
@endsection

@section('page-breadcrumb')
    {{ __('Manage Projects') }}
@endsection

@section('page-action')
    <div>
        <a href="javascript:void(0)" class="toggle_filter btn btn-sm btn-primary btn-icon"
            title="{{ __('Show / Hide Filters') }}">
            <span class=""><i class="fa fa-filter"></i><i class="fa fa-arrow-down arrow_icon"></i></span>
        </a>
        @permission('project manage')
            <a href="{{ route('projects.map') }}" class="btn btn-sm btn-primary mx-1" data-toggle="tooltip"
                title="{{ __('Project Map') }}">
                <i class="fa-solid fa-map"></i>
            </a>
        @endpermission
        @permission('project import')
            <a href="#" class="btn btn-sm btn-primary" data-ajax-popup="true" data-title="{{ __('Project Import') }}"
                data-url="{{ route('project.file.import') }}" data-toggle="tooltip" title="{{ __('Import') }}"><i
                    class="ti ti-file-import"></i> </a>
        @endpermission
        <a href="{{ route('projects.grid') }}" class="btn btn-sm btn-primary"
            data-bs-toggle="tooltip"title="{{ __('Grid View') }}">
            <i class="ti ti-layout-grid text-white"></i>
        </a>

        @permission('project create')
            <a class="btn btn-sm btn-primary" data-ajax-popup="true" data-size="md" data-title="{{ __('Create New Project') }}"
                data-url="{{ route('projects.create') }}" data-toggle="tooltip" title="{{ __('Create') }}">
                <i class="ti ti-plus"></i>
            </a>
        @endpermission
    </div>
@endsection

@php
    //$profile = \App\Models\Utility::get_file('uploads/avatar/');
    $projectmaxprice = isset($projectmaxprice) && $projectmaxprice > 100 ? $projectmaxprice : 100;
    $half_price = 50;
    if ($projectmaxprice > 100) {
        $half_price = $projectmaxprice / 2;
    }
@endphp

@push('css')
    <style>
        .filter-wrapper {
            margin-bottom: 20px;
        }

        .filter-wrapper select,
        .filter-wrapper input {
            margin-right: 10px;
            padding: 5px;
        }

        th,
        td {
            text-align: center;
        }

        .image-column img {
            width: 50px;
            height: 50px;
        }
    </style>
@endpush



@section('content')
    <div class="row">
        @include('project::project.index.utility.tabs_filter_button')

        @include('project::project.index.utility.tables')


        <div class="filter-wrapper">
            <select id="country-filter">
                <option value="">Select Country</option>
                <option value="USA">USA</option>
                <option value="Canada">Canada</option>
            </select>

            <select id="state-filter">
                <option value="">Select State</option>
                <option value="California">California</option>
                <option value="Texas">Texas</option>
            </select>

            <select id="city-filter">
                <option value="">Select City</option>
                <option value="Los Angeles">Los Angeles</option>
                <option value="Houston">Houston</option>
            </select>

            <select id="archive-filter">
                <option value="">All Projects</option>
                <option value="Not Archived">Not Archived</option>
            </select>
        </div>

        <table id="projectsTable" class="display" style="width:100%">
            <thead>
                <tr>
                    <th>Status</th>
                    <th>Name</th>
                    <th>Comments</th>
                    <th>Priority</th>
                    <th>Construction</th>
                    <th>Project Net</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Active</td>
                    <td>Project Alpha</td>
                    <td>Initial phase completed</td>
                    <td>High</td>
                    <td>Building A</td>
                    <td>50000</td>
                    <td>2023-07-15</td>
                    <td><button>View</button></td>
                </tr>
                <tr>
                    <td>Inactive</td>
                    <td>Project Beta</td>
                    <td>Awaiting approval</td>
                    <td>Medium</td>
                    <td>Building B</td>
                    <td>75000</td>
                    <td>2023-08-20</td>
                    <td><button>View</button></td>
                </tr>
                <!-- Add more rows as needed -->
            </tbody>
        </table>


    </div>
@endsection


@push('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>

    <script>
        $(document).ready(function() {
            var table = $('#projectsTable').DataTable();

            // Custom filtering function for external filters
            $.fn.dataTable.ext.search.push(
                function(settings, data, dataIndex) {
                    var country = $('#country-filter').val();
                    var state = $('#state-filter').val();
                    var city = $('#city-filter').val();
                    var archive = $('#archive-filter').val();

                    var projectCountry = data[6]; // Country column (Example: "USA")
                    var projectState = data[6]; // State column (Example: "California")
                    var projectCity = data[6]; // City column (Example: "Los Angeles")
                    var projectArchive = data[1]; // Status column (Active/Inactive)

                    if (
                        (country === "" || projectCountry.includes(country)) &&
                        (state === "" || projectState.includes(state)) &&
                        (city === "" || projectCity.includes(city)) &&
                        (archive === "" || (archive === "Not Archived" && projectArchive === "Active"))
                    ) {
                        return true;
                    }
                    return false;
                }
            );

            // Event listeners for the filters
            $('#country-filter, #state-filter, #city-filter, #archive-filter').on('change', function() {
                table.draw();
            });
        });
    </script>
@endpush
