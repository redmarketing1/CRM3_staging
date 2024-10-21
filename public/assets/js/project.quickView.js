(()=>{function e(t){return e="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e},e(t)}function t(t,o,n){return(o=function(t){var o=function(t,o){if("object"!=e(t)||!t)return t;var n=t[Symbol.toPrimitive];if(void 0!==n){var s=n.call(t,o||"default");if("object"!=e(s))return s;throw new TypeError("@@toPrimitive must return a primitive value.")}return("string"===o?String:Number)(t)}(t,"string");return"symbol"==e(o)?o:o+""}(o))in t?Object.defineProperty(t,o,{value:n,enumerable:!0,configurable:!0,writable:!0}):t[o]=n,t}var o,n;function s(){$.ajax({url:route("project.all_files",projectID),type:"POST",data:{html:!0},success:function(e){$(".mediabox").html(e),$("img.preview").remove(),function(){var e=0,t=[];$(".image_selection").each((function(){var o=$(this).data("id");if(1==$(this).prop("checked")){e++;var n=$(this).val();t.push(n),$(".project_file_"+o).parents(".mediaimg").addClass("selected_image")}else $(".project_file_"+o).parents(".mediaimg").removeClass("selected_image")})),e>0?$(".btn_bulk_delete_files").removeClass("d-none"):$(".btn_bulk_delete_files").addClass("d-none");$("#remove_files_ids").val(JSON.stringify(t))}()}})}$(document).on("click",".status",(function(e){e.preventDefault();var t=$(this).attr("data-id"),o=$(this).attr("data-status"),n=$(this).attr("data-background"),s=$(this).attr("data-font"),c=$(this).text();$(".project-statusName").text(c).attr("style","background-color: ".concat(n," !important; color: ").concat(s," !important;")),$.ajax({url:route("project.update",t),type:"PUT",data:{ids:t,statusID:o,type:"changeStatus"},success:function(e){toastrs("Success",e.success,"success")}})})),$(document).on("click",".change-archive",(function(e){e.preventDefault();var t=$(this).data("id"),o=$(this).data("title"),n=$(this).data("text"),s=$(this).data("type");Swal.fire({title:o,text:n,showCancelButton:!0,confirmButtonText:"Yes, ".concat(s," it"),cancelButtonText:"No, cancel"}).then((function(e){e.isConfirmed&&($.ajax({url:route("project.update",1),type:"PUT",data:{type:s,ids:[t]},success:function(e){console.log(e),window.location.reload()}}),Swal.fire({icon:"success",title:"".concat(s.charAt(0).toUpperCase()+s.slice(1)," Successful!"),html:"Project have been moved to ".concat(s),timer:2e3,timerProgressBar:!0,showConfirmButton:!1}))}))})),$(document).on("click","#copyProjectShareLinks",(function(){var e=this,t=document.getElementById("copyText");t.select(),t.setSelectionRange(0,99999),document.execCommand("copy"),this.textContent="Copied!",this.style.backgroundColor="#6fd943",setTimeout((function(){e.textContent="Copy",e.style.backgroundColor=""}),2e3),toastrs("success","Project's shared links has copied to clipboard","success")})),function(e){$("#changeProjectMember").select2({placeholder:"Nutzer wählen",tags:!0,allowHtml:!0,templateSelection:function(e,t){return $(t).css("background-color",$(e.element).data("background_color")),e.element&&$(t).css("color",$(e.element).data("font_color")),e.text}}).on("select2:open",(function(){$(".select2-container.select2-container--open").css({zIndex:99999999})})).on("change",(function(e){var t=$(this).data("projectid"),o=$(this).val();$.ajax({url:route("project.member.add",t),type:"POST",data:{users:o},success:function(e){e.is_success?($(".projectteamcount").html(e.count),toastrs("Success",e.message,"success")):toastrs("Error",e.message,"error")},error:function(e,t,o){toastrs("Error","Something went wrong: "+t,"error")}})})),$.ajax({url:route("project.get_all_address",e),type:"POST",data:{html:!0},success:function(e){1==e.status&&$(".project_all_address").html(e.html_data)}}),$(".filter_select2").select2({placeholder:"Select",multiple:!0,tags:!0,templateSelection:function(e,t){return $(t).css("background-color",$(e.element).data("background_color")),e.element&&$(t).css("color",$(e.element).data("font_color")),e.text}}).on("select2:open",(function(){$(".select2-container.select2-container--open").css({zIndex:99999999})})),$(document).on("change",".filter_select2",(function(t){var o=$(this).data("labeltype"),n=$(this).val().join(", ");(o||n)&&$.ajax({url:route("project.add.status_data",e),type:"POST",data:{field:o,field_value:n},success:function(e){e.is_success?toastrs("Success",e.message,"success"):toastrs("Error",e.message,"error")}})})),$(document).on("change","#construction-select",(function(){var e=$("#construction-select option:selected").data("type"),o=document.getElementById("client_type1");o.value=null!=e?e:"new";var n=route("users.get_user"),s=this.value;if(s)axios.post(n,{user_id:s,from:"construction"}).then((function(e){var o=document.getElementById("construction-details");$("#construction-details").html(e.data.html_data),$("#construction_detail_id").val(e.data.user_id),$("#construction_detail-company_notes").length>0&&init_tiny_mce("#construction_detail-company_notes"),o&&o.classList.remove("d-none"),initGoogleMapPlaced("construction_detail-autocomplete","construction_detail"),$(".country_select2").select2(t(t(t({placeholder:"Country",multiple:!1,dropdownParent:$("#title_form")},"placeholder","Select an country"),"allowClear",!0),"dropdownAutoWidth",!0))}));else{var c=document.getElementById("construction-details");c&&c.classList.add("d-none")}})),$(document).on("change","#client-select",(function(){var e=$("#client-select option:selected").data("type"),t=document.getElementById("client_type");t.value=null!=e?e:"new";var o=route("users.get_user");if(init_tiny_mce(".client-company_notes"),this.value)axios.post(o,{user_id:this.value,from:"client"}).then((function(e){var t=document.getElementById("client-details");$("#client-details").html(e.data.html_data),$("#client_id").val(e.data.user_id),$("#client-company_notes").length>0&&init_tiny_mce("#client-company_notes"),t&&t.classList.remove("d-none"),initGoogleMapPlaced("invoice-autocomplete","invoice")}));else{var n=document.getElementById("client-details");n&&n.classList.add("d-none")}}))}(projectID),o=document.querySelectorAll(".dropdown-premsg .dropdown-menu .dropdown-item"),n=document.querySelector(".dropdown-premsg .dropdown-toggle .drp-text"),o.forEach((function(e){e.addEventListener("click",(function(e){e.preventDefault();var t=this.getAttribute("data-content"),o=this.textContent.trim();tinymce.get("premsg")?tinymce.get("premsg").setContent(t):$("#premsg").val(t),n.textContent=o}))})),$(document).on("click",".projectusers img",(function(){var e=$(this).data("user_id"),t=$(this).data("estimation_id");Swal.mixin({customClass:{confirmButton:"btn btn-success",cancelButton:"btn btn-danger"},buttonsStyling:!1}).fire({title:"Are you sure to Remove this User from this Estimation?",text:"This action can not be undone. Do you want to continue?",icon:"warning",showCancelButton:!0,confirmButtonText:"Yes",cancelButtonText:"No",reverseButtons:!0}).then((function(o){o.isConfirmed&&$.ajax({url:route("estimation.remove_estimation_user"),type:"POST",data:{estimation_id:t,user_id:e},beforeSend:function(){showHideLoader("visible")},success:function(e){1==e.status?(showHideLoader("hidden"),toastrs("Success",e.message,"success"),setTimeout((function(){location.reload()}),1e3)):toastrs("Error",e.message)}})}))})),$(document).on("change","#same_invoice_address",(function(){$(".different-invoice-address-block").toggleClass("d-none")})),$(document).on("click",".client_feedback_edit",(function(e){e.preventDefault();var t=$(this).data("id");""!=t&&$.ajax({url:route("get.project.client.feedback",projectID),type:"POST",data:{feedback_id:t},beforeSend:function(){showHideLoader("visible")},success:function(e){1==e.status?(showHideLoader("hidden"),null!=e.data.feedback&&tinymce.get("feedbackEditor").setContent(e.data.feedback),$("#feedback_id").val(e.data.id),null!=e.data.file&&$("#feedback_old_file").val(e.data.file),$(".feedback_old_file_link").html(e.file_link),$(".feedback_collapse"+t).collapse("hide"),$("#collapseFeedback").collapse("show"),$("html, body").animate({scrollTop:$("#feedbackAccordion").offset().top},200)):toastrs("Error",e.message)}})})),$(document).on("click",".client_feedback_delete",(function(e){e.preventDefault();var t=$(this).data("id");Swal.mixin({customClass:{confirmButton:"btn btn-success",cancelButton:"btn btn-danger"},buttonsStyling:!1}).fire({title:"{{ __('Are you sure to remove this client message?') }}",text:"{{ __('This action can not be undone. Do you want to continue?') }}",icon:"warning",showCancelButton:!0,confirmButtonText:"{{ __('Yes') }}",cancelButtonText:"{{ __('No') }}",reverseButtons:!0}).then((function(e){e.isConfirmed&&$.ajax({url:route("project.client.feedback.delete",projectID),type:"POST",data:{feedback_id:t},beforeSend:function(){showHideLoader("visible")},success:function(e){1==e.status?(showHideLoader("hidden"),$(".feedback_heading"+t).remove(),$(".feedback_collapse"+t).remove(),toastrs("Success",e.message,"success"),setTimeout((function(){window.location.reload()}),1e3)):toastrs("Error",e.message)}})}))})),$(document).on("click",".project_comments_edit",(function(e){e.preventDefault();var t=$(this).data("id");""!=t&&$.ajax({url:route("get.project.comment",projectID),type:"POST",data:{comment_id:t},beforeSend:function(){showHideLoader("visible")},success:function(e){1==e.status?(showHideLoader("hidden"),null!=e.data.comment&&tinymce.get("commentEditor").setContent(e.data.comment),$("#project_comment_id").val(e.data.id),$("#project_comment_old_file").val(e.data.file),$(".project_comment_old_file_link").html(e.file_link),$(".comment_collapse"+t).collapse("hide"),$("#collapseComment").collapse("show"),$("html, body").animate({scrollTop:$("#commentAccordion").offset().top},200)):toastrs("Error",e.message)}})})),$(document).on("click",".project_comments_delete",(function(e){e.preventDefault();var t=$(this).data("id");Swal.mixin({customClass:{confirmButton:"btn btn-success",cancelButton:"btn btn-danger"},buttonsStyling:!1}).fire({title:"{{ __('Are you sure to remove this comment?') }}",text:"{{ __('This action can not be undone. Do you want to continue?') }}",icon:"warning",showCancelButton:!0,confirmButtonText:"{{ __('Yes') }}",cancelButtonText:"{{ __('No') }}",reverseButtons:!0}).then((function(e){e.isConfirmed&&$.ajax({url:route("project.comment.delete",projectID),type:"POST",data:{comment_id:t},beforeSend:function(){showHideLoader("visible")},success:function(e){1==e.status?(showHideLoader("hidden"),$(".comment_heading"+t).remove(),$(".comment_collapse"+t).remove(),toastrs("Success",e.message,"success"),setTimeout((function(){window.location.reload()}),1e3)):toastrs("Error",e.message)}})}))})),$(document).on("submit",".project_detail_form",(function(e){e.preventDefault();var t=$(this).serialize(),o=$(this).attr("action");$.ajax({type:"post",url:o,data:t,cache:!1,beforeSend:function(){$(this).find(".btn-create").attr("disabled","disabled"),$("#commonModal #project-description").length>0&&tinymce.get("project-description").remove(),$("#commonModal #event_description").length>0&&tinymce.get("event_description").remove(),$("#commonModal #technical-description").length>0&&tinymce.get("technical-description").remove()},success:function(e){if(e.is_success&&(toastrs("Success",e.message,"success"),$("#commonModal").modal("hide"),$(".project_title").html(e.project.title),$(".project-description").html(e.project.description),$(".technical-description").html(e.project.technical_description),$(".invoice_address").addClass("d-none"),$(".invoice_address2").addClass("d-none"),1==e.status_changed&&location.reload(),set_construction_address()),e.user_details){var t="",o="";null!=e.user_details.first_name&&(t=e.user_details.first_name),null!=e.user_details.last_name&&(o=e.user_details.last_name);var n=t+" "+o;$(".client_full_name").html(n)}else toastrs("Error",e.message,"error")},complete:function(){$(this).find(".btn-create").removeAttr("disabled")}})})),$("#progress-table").DataTable({lengthMenu:[[10,25,50,100,200,-1],[10,25,50,100,200,"All"]],pageLength:200,dom:"lrt",bPaginate:!1,bFilter:!1,bInfo:!1,destroy:!0,processing:!0,serverSide:!0,order:[[0,"DESC"]],bSort:!1,ajax:{url:route("progress.list"),type:"POST",data:{project_id:projectID}},columns:[{data:"id",className:"id",orderable:!1},{data:"client_name",className:"client_name",orderable:!1},{data:"comment",className:"comment",orderable:!1},{data:"name",className:"history",orderable:!1},{data:"date",className:"date",orderable:!1},{data:"action",className:"action",orderable:!1}]}),$(document).ready((function(){s()})),$(document).on("click","#dropBox",(function(e){e.preventDefault(),$("#fileInput").trigger("click")})),$(document).on("click",".default_image_selection",(function(e){e.preventDefault();var t=$(this).val();Swal.mixin({customClass:{confirmButton:"btn btn-success",cancelButton:"btn btn-danger"},buttonsStyling:!1}).fire({title:"Are you sure?",text:"This action can not be undone. Do you want to continue?",icon:"warning",showCancelButton:!0,confirmButtonText:"Yes",cancelButtonText:"No",reverseButtons:!0}).then((function(e){e.isConfirmed&&$.ajax({url:route("project.files.set_default_file",projectID),type:"POST",data:{file:t},beforeSend:function(){showHideLoader("visible")},success:function(e){1==e.is_success?(showHideLoader("hidden"),toastrs("Success",e.message,"success"),s()):toastrs("Error",e.message)}})}))})),$(document).on("submit","#bulk_delete_form",(function(e){e.preventDefault();var t=new FormData(this);$.ajax({url:route("project.files.delete",projectID),type:"POST",data:t,contentType:!1,processData:!1,beforeSend:function(){showHideLoader("visible")},success:function(e){1==e.is_success?(showHideLoader("hidden"),toastrs("Success",e.message,"success"),s()):toastrs("Error",e.message)}})})),$(document).on("click",".delete_single_file_p",(function(e){e.preventDefault();var t=$(this).data("url");Swal.mixin({customClass:{confirmButton:"btn btn-success",cancelButton:"btn btn-danger"},buttonsStyling:!1}).fire({title:"Are you sure?",text:"This action can not be undone. Do you want to continue?",icon:"warning",showCancelButton:!0,confirmButtonText:"Yes",cancelButtonText:"No",reverseButtons:!0}).then((function(e){e.isConfirmed&&$.ajax({url:t,type:"GET",beforeSend:function(){showHideLoader("visible")},success:function(e){1==e.is_success?(showHideLoader("hidden"),toastrs("Success",e.message,"success"),s()):toastrs("Error",e.message)}})}))}))})();