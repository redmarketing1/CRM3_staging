@php
    $subContractorId = null;
@endphp
<form action="{{ route('estimation.duplicateQuoteCard', request('id')) }}" method="POST" id="clone-form">
    @csrf
    <div class="modal-body">
        <div class="form-group">
            <label for="title">Title</label>
            <input type="text" name="title" id="title" class="form-control" placeholder="Enter Title">
        </div>
        <div class="form-group">
            <h3 class="text-center">OR</h3>
        </div>
        <div class="form-group selct2-custom">
            <label for="sub-contractor">Contact</label>
            <select name="subContractor" id="sub-contractor" class="form-control">
                <option value="" selected>Select Sub Contractor</option>
                @foreach (genericGetContacts() as $contractor)
                    <option value="{{ $contractor['id'] }}">
                        {{ $contractor['name'] }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">Create</button>
    </div>
</form>
