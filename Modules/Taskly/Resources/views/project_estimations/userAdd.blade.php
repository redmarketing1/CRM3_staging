<head>
    <link
        href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.css"
        rel="stylesheet"
    />
    <style>
        .tags {
            display: inline-block;
            background-color: #3498db;
            color: white;
            padding: 5px 10px;
            margin: 5px;
            border-radius: 5px;
            cursor: pointer;
        }
    </style>
</head>
{{ Form::open(array('route' => array('estimation.users.store',[$estimationId]))) }}
<div class="row">
	<div class="form-group col-md-12 selct2-custom">
		<select name="user_id[]" id="contacts-select2" class="form-control" required="required" multiple="multiple">
			<option value="" data-type="">{{ __('Select') }}
			</option>
			@foreach ($users as $contact_user)
			<option value="{{$contact_user['id']}}" data-type="{{ $contact_user['type'] }}"> {{$contact_user['name']}}</option>
			@endforeach
		</select>
	</div>
    <div class="modal-footer pr-0">
        <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{ __('Close') }}</button>
        {{Form::submit(__('Add'),array('class'=>'btn  btn-primary'))}}
    </div>
</div>

{{ Form::close() }}

<script src="https://cdn.jsdelivr.net/npm/tom-select/dist/js/tom-select.complete.min.js"></script>
<script>
    $(document).ready(function () {

    })
</script>
