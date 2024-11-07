@extends('layouts.main')

@section('content')
    <h1>Hello World</h1>

    <p>
        This view is loaded from module: {!! config('estimation.name') !!}
    </p>
@endsection
