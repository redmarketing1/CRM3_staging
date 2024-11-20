<div class="row estimation-header">
    <div class="col-md-4">
        <fieldset @if ($estimation->status == 2) disabled @endif>
            <div class="form-group">
                <label for="title" class="form-label">{{ trans('Estimation Title') }}</label>
                <input class="form-control" name="title" id="title" type="text" value="{{ $estimation->title }}">
            </div>
        </fieldset>
    </div>

    <div class="col-md-3">
        <fieldset @if ($estimation->status == 2) disabled @endif>
            <div class="form-group">
                <label for="project" class="form-label">{{ trans('Project title') }}</label>
                <input type="text" class="form-control" value="{{ $estimation->project->name }}" disabled readonly>
            </div>
        </fieldset>
    </div>

    <div class="col-md-3">
        <fieldset @if ($estimation->status == 2) disabled @endif>
            <div class="form-group">
                <label for="issue_date" class="form-label">{{ trans('Issue Date') }}</label>
                <input class="form-control" required="required" name="issue_date" type="date"
                    value="{{ $estimation->issue_date }}" id="issue_date">
            </div>
        </fieldset>
    </div>

    <div class="col-md-2">
        <div class="form-group not-disable">
            <label for="status" class="d-block form-label">{{ trans('Status') }}</label>
            <button
                class="btn btn-xs d-block badge estimation-statusName bg {{ $estimation->AllStatus[$estimation->status] }} text-white btn-icon-only width-auto dropdown-toggle"
                type="button" name="status" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                {{ $estimation->AllStatus[$estimation->status] }}
            </button>

            <div class="dropdown-menu estimation-dropdown-menu">
                @foreach ($estimation->AllStatus as $key => $status)
                    <a href="{{ route('estimations.changeStatus', [$estimation->id, $key]) }}"
                        class="dropdown-item {{ $status }}">
                        {{ $status }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    @if (empty($estimation->products))
        <div class="align-items-center col-md-3 d-flex import-excel-box w-auto">
            <div class="form-group">
                <label for="import_file" class="form-label">
                    {{ trans('Import Excel File') }}
                    <a href="{{ asset('public/estimation_import_format.xlsx') }}" class="small ms-2">
                        ({{ __('Sample File Download') }})
                    </a>
                </label>
                <input class="form-control" name="import_file" type="file" id="import_file">
            </div>
            <div class="">
                <button class="btn mx-3 btn-primary" type="submit" value="Import">
                    {{ trans('Import') }}
                </button>
            </div>
        </div>
    @endif
</div>
