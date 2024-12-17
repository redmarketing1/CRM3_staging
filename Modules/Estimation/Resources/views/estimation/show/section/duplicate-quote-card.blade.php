@php
    $mode = request('mode', 'duplicate');   
    $titleLabel = $mode === 'edit' ? 'Edit Quote Title' : 'Clone Quote Title';
    $titlePlaceholder = $mode === 'edit' ? 'Edit Quote Title' : 'Enter a Quote Title';

    $buttonText = $mode === 'edit' ? 'Update Quote' : 'Create Clone Quote';
@endphp

<form action="{{ route('estimation.duplicateQuoteCard', request('id')) }}" method="POST" id="clone-form">
    @csrf
    @if($mode === 'edit')
        @method('PUT')
    @endif

    <div class="modal-body"> 
        <div class="form-group">
            <label for="title">
                {{ trans($titleLabel) }}
            </label>
            <input type="text" 
                name="title" 
                id="title" 
                class="form-control" 
                value="{{ request('title') }}"
                placeholder="{{ trans($titlePlaceholder) }}">
        </div>

        <div class="form-group">
            <h3 class="text-center">OR</h3>
        </div>

        <div class="form-group selct2-custom">
            <label for="sub-contractor">
                {{ trans('Contract user') }}
            </label>
            <select name="subContractor" id="sub-contractor" class="form-control">
                <option value="">Select Sub Contractor</option>
                @foreach (genericGetContacts() as $contractor)
                    <option value="{{ $contractor['id'] }}" {{ request('userID') == $contractor['id'] ? 'selected' : '' }}>
                        {{ $contractor['name'] }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            {{ trans('Close') }}
        </button>
        <button type="submit" class="btn btn-primary">
            {{ trans($buttonText) }}
        </button>
    </div>
</form>
