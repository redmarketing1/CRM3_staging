@extends('layouts.main')

@section('content')
    <div class="row map-wrapper">
        <div class="col-2">
            <div class="search mt-3 mb-2">
                <input type="search" class="w-100" id="searchInput" placeholder="{{ trans('Search projects...') }}" />
            </div>
            @include('project::project.map.projectTabs')
            @include('project::project.map.projectList')
        </div>

        <div class="col-10 p-0">
            <div id="map" data="{{ json_encode($mapsLocations, JSON_UNESCAPED_UNICODE) }}">
            </div>
        </div>
    </div>
@endsection
