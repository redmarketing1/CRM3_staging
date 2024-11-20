 <div class="modal fade" id="duplicateModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
     <div class="modal-dialog  modal-dialog-centered">
         <div class="modal-content" id="modal-content">

         </div>
     </div>
 </div>

 <div class="modal fade" data-backdrop="static" id="group_reorder_Modal" tabindex="-1" role="dialog" aria-hidden="true">
     <div class="modal-dialog modal-lg" role="document">
         <div class="modal-content">
             <div class="modal-header">
                 <h5 class="modal-title">{{ __('Reorder Group') }} <i class="fa-regular fa-circle-question"
                         data-bs-toggle="tooltip"
                         title="{{ __('Reorder Groups by dragging and dropping each group up and down or left to right for Sublevels') }}"></i>
                 </h5>
                 <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
             </div>
             <div class="modal-body body">

             </div>
             <div class="modal-footer p-2">
                 <button type="button" class="btn btm-sm btn-primary"
                     onClick="store_group_reorder(true)">{{ __('Apply') }}</button>
             </div>
         </div>
     </div>
 </div>
