@extends('layouts.main')

@section('content')
    <div class="container">
        <h1>Take Manual Backup</h1>
        <form action="{{ route('backup.manual') }}" method="POST">
            @csrf
            <div class="form-group">
                <label>Backup Type</label>
                <select name="backup_type" class="form-control">
                    <option value="files">Files Only</option>
                    <option value="database">Database Only</option>
                    <option value="both">Files & Database</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Take Backup</button>
        </form>
    </div>
@endsection
