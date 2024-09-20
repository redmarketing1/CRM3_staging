 <div class="col-md-auto col-sm-4">
     <a href="javascript:void" class="btn btn-xs btn-primary btn-icon-only col-12 cp_link"
         data-link="{{ route('project.shared.link', [\Illuminate\Support\Facades\Crypt::encrypt($project->id)]) }}"
         data-toggle="tooltip" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Copy') }}">
         <span class="btn-inner--text text-white">
             <i class="ti ti-copy"></i></span>
     </a>
 </div>
