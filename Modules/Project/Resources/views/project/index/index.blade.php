@extends('layouts.main')

@section('page-title')
    {{ __('Manage Projects') }}
@endsection

@section('page-breadcrumb')
    {{ __('Manage Projects') }}
@endsection

@section('page-action')
    @include('project::project.index.utility.tools')
@endsection

@section('content')
    <div class="row">
        <div class="clearFilterContainer">
            <button type="button" id="clearFilter" class="clear_filter">
                {{ trans('clear all') }}
            </button>
        </div>
        <div class="col-xl-12">
            <div class="card projectsTableContainter">
                <div class="card-body table-border-style">
                    @include('project::project.index.utility.table')
                </div>
            </div>
        </div>
    </div>
@endsection
