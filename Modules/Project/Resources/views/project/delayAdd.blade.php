{{ Form::open(array('route' => array('project.delay.announcement.store',$id),'method' => 'POST', 'enctype' => 'multipart/form-data')) }}
<div class="row">
    <div class="form-group col-md-12">
        {{ Form::label('new_deadline', __('New Deadline'),['class' => 'col-form-label']) }}
        {!! Form::date('new_deadline','',array('class' => 'form-control','required'=>'required')) !!}
    </div>
    <div class="form-group col-md-12">
        {{ Form::label('delay_in_weeks', __('Delay in Weeks'),['class' => 'col-form-label']) }}
        {!! Form::text('delay_in_weeks','',array('class' => 'form-control','required'=>'required')) !!}
    </div>
    <div class="form-group col-md-12">
        {{ Form::label('reason', __('Reason for Delay'),['class' => 'col-form-label']) }}
        {!! Form::textarea('reason','',array('class' => 'form-control','rows'=>2,'required'=>'required')) !!}
    </div>
    <div class="form-group col-md-12">
        {{ Form::label('internal_comment', __('Internal Comment'),['class' => 'col-form-label']) }}
        {!! Form::textarea('internal_comment','',array('class' => 'form-control','rows'=>2,'required'=>'required')) !!}
    </div>
    <div class="form-group col-md-12">
        {{ Form::label('media', __('Media'),['class' => 'col-form-label']) }}
        {!! Form::file('media[]',array('class' => 'form-control','multiple','required'=>'required')) !!}
    </div>
    <div class="modal-footer pr-0">
        <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{ __('Close') }}</button>
        {{Form::submit(__('Add'),array('class'=>'btn  btn-primary'))}}
    </div>
</div>

{{ Form::close() }}

<script src="{{asset('assets/js/plugins/choices.min.js')}}"></script>
<script>
    if ($(".multi-select").length > 0) {
              $( $(".multi-select") ).each(function( index,element ) {
                  var id = $(element).attr('id');
                     var multipleCancelButton = new Choices(
                          '#'+id, {
                              removeItemButton: true,
                          }
                      );
              });
         }
  </script>
