 <div class="card">
     <div class="card-header ">
         <div class="d-flex justify-content-between align-items-center">
             <div>
                 <h5 class="mb-0">{{ __('Contact') }}
                 </h5>
             </div>
                @if (\Auth::user()->isAbleTo('client manage'))
                        <div class="float-end">
                            <p class="text-muted d-none d-sm-flex align-items-center mb-0">
                                <a href="javascript:;" class="btn btn-sm btn-primary" data-ajax-popup="true"
                                    data-title="{{ __('Contact Details') }}" data-toggle="tooltip"
                                    title="{{ __('Contact Details') }}" data-size="lg"
                                    data-url="{{ route('projects.edit_form', [$project->id, 'ConstructionDetails']) }}"><i
                                        class="ti ti-edit"></i></a>
                            </p>
                        </div>
                @endif
         </div>
     </div>
     <div class="card-body top-10-scroll project_all_address">

     </div>
 </div>
