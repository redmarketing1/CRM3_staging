@extends('layouts.main')


@section('page-breadcrumb')
    <a href="{{ route('project.index') }}">{{ __('All Project') }}</a>,
    <a href="{{ route('project.show', [$estimation->project_id]) }}">{{ $estimation->project->name }}</a>,{{ __('Edit') }}
@endsection

@section('content')
    <div class="row estimation-show" x-cloak x-data="estimationShow">
        <div class="row">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-12 order-lg-2">
                        <div class="card repeater table-card-full">
                            {{ Form::open(['route' => 'estimations.importdata', 'files' => true, 'id' => 'quote_form']) }}

                            <div class="card-body">
                                @include('estimation::estimation.show.section.header')
                                @include('estimation::estimation.show.table.index')
                            </div>

                            {{-- @include('estimation::estimation.show.section.description') --}}
                            @include('estimation::estimation.show.section.footer')

                            {{ Form::close() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @include('estimation::estimation.show.section.contextmenu')
    </div>

    {{-- @include('estimation::estimation.show.Modal.index') --}}
@endsection

@push('scripts')
    <script src="{{ asset('Modules/Taskly/Resources/assets/js/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('js/jquery.repeater.min.js') }}"></script>
    <script src="{{ asset('Modules/Taskly/Resources/assets/js/jquery.nestable.js') }}"></script>
@endpush
