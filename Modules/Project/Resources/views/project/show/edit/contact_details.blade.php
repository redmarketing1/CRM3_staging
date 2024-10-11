{{ Form::open(['route' => ['project.update_details', [$project_id, $form_field]], 'method' => 'post', 'id' => 'title_form', 'class' => 'project_contact_details']) }}

<div class="card-body p-0">
    <div class="row">
        <div class="col-md-6">
            <div class="form-group col-md-12 selct2-custom">
                {{ Form::label('construction_details', __('Construction Details'), ['class' => 'form-label']) }}
                <select name="construction_user_id" id="construction-select" class="form-control">
                    <option value="" data-type="">{{ __('Select') }}</option>
                    @foreach ($clients as $user)
                        @php
                            $selected_construction_user =
                                isset($project->construction_detail_id) &&
                                $project->construction_detail_id == $user['id']
                                    ? 'selected'
                                    : '';
                        @endphp
                        <option value="{{ $user['id'] }}" data-type="{{ $user['type'] }}"
                            {{ $selected_construction_user }}> {{ $user['name'] }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-12">
                @php
                    $checked = 'checked';
                    if (isset($project->is_same_invoice_address) && $project->is_same_invoice_address == 0) {
                        $checked = '';
                    }
                @endphp
                <input type="checkbox" name="same_invoice_address" id="same_invoice_address" {{ $checked }}
                    value="1">
                <label class="custom-control-label" for="same_invoice_address">{{ __('Same Invoice Address') }}</label>
            </div>
            <input type="hidden" id='construction_detail_id' name="construction_detail_id">
            <input type="hidden" id='client_type1' name="client_type1">

            <!-- Construction Details Column -->
            <div class="col-md-12 row d-none" id='construction-details'></div>
        </div>
        <div class="col-md-6">
            <div class="client-details different-invoice-address-block @if (isset($project->is_same_invoice_address) && $project->is_same_invoice_address == 1) d-none @endif">
                <div class="form-group col-md-12 selct2-custom">
                    {{ Form::label('client', __('Contact'), ['class' => 'form-label']) }}
                    <select name="client" id="client-select" class="form-control">
                        <option value="" data-type="">{{ __('Select') }}
                        </option>
                        @foreach ($clients as $client)
                            @php
                                $selected_client_user =
                                    isset($project->client) && $project->client == $client['id'] ? 'selected' : '';
                            @endphp
                            <option value="{{ $client['id'] }}" {{ $selected_client_user }}
                                data-type="{{ $client['type'] }}"> {{ $client['name'] }}</option>
                        @endforeach
                    </select>

                </div>
                <div class="col-md-12">
                    <label class="custom-control-label" for=""></label>
                </div>
                <input type="hidden" id='client_id' name="client_id">
                <input type="hidden" id='client_type' name="client_type">
                <div class="contact-details-columns row ">
                    <!-- Client Details Column -->
                    <div class="col-md-12 row d-none" id="client-details"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer pr-0">
    <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{ __('Close') }}</button>
    {{ Form::submit(__('Save'), ['class' => 'btn  btn-primary btn-create']) }}
</div>
{{ Form::close() }}


<script>
    var projectClientId = `{{ isset($project->client_data->id) ? $project->client_data->id : '' }}`;
    var construction_detail_user =
        `{{ isset($project->construction_detail->id) ? $project->construction_detail->id : '' }}`;
    var constructionDetailId = "<?php echo $project->construction_detail_id; ?>";


    if (construction_detail_user != '') {
        $('#construction-select').trigger('change');
    }

    var selectedOption = $('#client-select option[value="' + projectClientId + '"]');
    if (selectedOption.length > 0) {
        selectedOption.prop('selected', true);
        $('#client-select').trigger('change');
    }


    if (projectClientId != '') {
        //	$('#client-select').trigger('change');
    }

    /*
    var selectedConstruction = $('#construction-select option[value="' + constructionDetailId + '"]');
    if (selectedConstruction.length > 0) {
    	selectedConstruction.prop('selected', true);
    	$('#construction-select').trigger('change');
    }
    */
</script>
