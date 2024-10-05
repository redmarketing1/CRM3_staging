@extends('layouts.main')

@section('content')
    <div class="row map-wrapper">
        <div class="col-2">
            <div id="projectContainer"></div>
        </div>
        <div class="col-10 p-0">
            <div id="map" data="{{ json_encode($locations, JSON_UNESCAPED_UNICODE) }}">
            </div>
        </div>
    </div>
@endsection
