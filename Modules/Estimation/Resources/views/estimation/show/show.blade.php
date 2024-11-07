@extends('layouts.main')

@section('content')
    <div class="row">
        <div class="row">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-12 order-lg-2">
                        <div class="card repeater table-card-full">
                            {{ Form::open(['route' => 'estimations.importdata', 'files' => true, 'id' => 'quote_form']) }}

                            <div class="card-body pb-0">
                                @include('estimation::estimation.show.section.header')
                                @include('estimation::estimation.show.table.index')
                            </div>

                            @include('estimation::estimation.show.section.description')
                            @include('estimation::estimation.show.section.footer')

                            {{ Form::close() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('estimation::estimation.show.Modal.index')
@endsection
