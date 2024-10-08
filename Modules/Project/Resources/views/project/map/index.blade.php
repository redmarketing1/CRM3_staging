@extends('layouts.main')

@section('content')
    <div class="row map-wrapper">
        <div class="col-2">
            @include('project::project.map.projectTabs')
            @include('project::project.map.projectList')
        </div>

        <div class="col-10 p-0">
            <div id="map" data="{{ json_encode($mapsLocations, JSON_UNESCAPED_UNICODE) }}">
            </div>
        </div>
    </div>
@endsection
