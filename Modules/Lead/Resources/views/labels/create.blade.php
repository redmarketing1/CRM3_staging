{{ Form::open(array('url' => 'labels')) }}
    <div class="modal-body">
        <div class="row">
            <div class="form-group col-12">
                {{ Form::label('name', __('Label Name'),['class'=>'col-form-label']) }}
                {{ Form::text('name', '', array('class' => 'form-control','required'=>'required')) }}
            </div>
			<div class="form-group col-12">
				{{ Form::label('order', __('Label Order'),['class' => 'col-form-label']) }}
				{{ Form::number('order', null, array('class' => 'form-control','required'=>'required', 'min' => 0)) }}
			</div>
            <div class="form-group col-12">
                {{ Form::label('pipeline_id', __('Pipeline'),['class'=>'col-form-label']) }}
                {{ Form::select('pipeline_id', $pipelines,null, array('class' => 'form-control select2','required'=>'required')) }}
            </div>
			<div class="form-group">
				<label for="background_color">Background Color</label>
				<input type="text" name="background_color" id="background_color" class="form-control color_picker" data-control="hue" value="#000000">
			</div>
			<div class="form-group">
				<label for="font_color">Font Color</label>
				<input type="text" name="font_color" id="font_color" class="form-control color_picker" data-control="hue" value="#000000">
			</div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{__('Close')}}</button>
        <button type="submit" class="btn  btn-primary">{{__('Create')}}</button>
    </div>

{{ Form::close() }}

<script src="{{asset('assets/js/plugins/choices.min.js')}}"></script>
<script>


</script>
