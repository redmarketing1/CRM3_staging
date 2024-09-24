(()=>{var e,t={190:()=>{$(document).ready((function(){var e=$("#projectsTable").DataTable({lengthChange:!1,ordering:!1,searching:!0,layout:{topEnd:null},select:{style:"multi"},pagingType:"simple",language:{paginate:{previous:"Previous",next:"Next"}},processing:!1,serverSide:!1,ajax:{url:route("project.index"),type:"GET",dataType:"json"},columns:[{data:null,orderable:!1,className:"dt-body-center",render:function(e,t,a,o){return'<input type="checkbox" class="row-select-checkbox" value="'+e.id+'">'}},{data:"thumbnail",name:"thumbnail"},{data:"name",name:"name",orderable:!0},{data:"is_archive",name:"is_archive",visible:!1},{data:"status",name:"status",defaultContent:"N/A",orderable:!0},{data:"comments",name:"comments",defaultContent:"N/A",orderable:!1},{data:"priority",name:"priority",defaultContent:"N/A",orderable:!1},{data:"construction",name:"construction",defaultContent:"N/A",orderable:!1},{data:"budget",name:"budget",orderable:!0},{data:"created_at",name:"created_at",orderable:!0},{data:"action",name:"action",orderable:!1,searchable:!1}],initComplete:function(t,a){var o=a.filterableStatusList,r=a.filterablePriorityList;if($("#projectsTable colgroup").remove(),o.html){var n=$.parseHTML(o.html);$("#projectsTable").parents(".projectsTableContainter").parents("div.col-xl-12").before(n)}if(o.data){var c=$.map(o.data,(function(e,t){return{id:removeWhitespace(e.name).toLowerCase(),text:e.name,backgroundColor:e.background_color,fontColor:e.font_color}}));$("#filterableStatusDropdown").select2({data:c,multiple:!0})}if(r){var l=$.map(r,(function(e,t){return{id:e.name,text:e.name,backgroundColor:e.background_color,fontColor:e.font_color}}));$("#filterablePriorityDropdown").select2({data:l})}var i=0;e.rows().every((function(e,t,a){var o=this.data(),r=parseFloat(o.budget);!isNaN(r)&&r>i&&(i=r)})),$(".range-input-selector,#filter_budget_from,#filter_budget_to").attr("max",i),$(".range-input-selector,#filter_budget_to").val(i),$("#projectsTable tr:first-child.hide").fadeIn(),$(".daterange").daterangepicker({autoUpdateInput:!1,locale:{cancelLabel:"clear"},ranges:{Today:[moment(),moment()],Yesterday:[moment().subtract(1,"days"),moment().subtract(1,"days")],"This Week":[moment().subtract(6,"days"),moment()],"This Month":[moment().startOf("month"),moment().endOf("month")],"Last 30 Days":[moment().subtract(29,"days"),moment()],"Last Month":[moment().subtract(1,"month").startOf("month"),moment().subtract(1,"month").endOf("month")]}}).on("apply.daterangepicker",(function(t,a){$(this).val(a.startDate.format("MM/DD/YYYY")+" - "+a.endDate.format("MM/DD/YYYY")),e.draw()})).on("cancel.daterangepicker",(function(t,a){$(this).val(""),e.draw()})),$("#projectsTable thead tr:nth-child(2) th:first-child").html('<input type="checkbox" id="select-all">'),$("#projectsTable tbody").on("change",".row-select-checkbox",(function(){$("input.row-select-checkbox:checked").length>0?$("#bulk-action-selector").fadeIn():$("#bulk-action-selector").fadeOut()})),$("#select-all").on("change",(function(){var e=this.checked;$("input.row-select-checkbox").prop("checked",e),$("input.row-select-checkbox:checked").length>0?$("#bulk-action-selector").fadeIn():$("#bulk-action-selector").fadeOut()}))}});$(document).on("click","#status-tabs a",(function(t){t.preventDefault(),$("#status-tabs a").removeClass("active"),$(this).addClass("active"),e.draw()})),$(document).on("input","#searchByProjectName, #searchByComment, #filter_budget_from, #filter_budget_to",(function(t){e.draw()})),$(document).on("mouseup",".range-input-selector",(function(t){$(this).removeClass("increased-width"),e.draw(),e.order([8,"desc"]).draw()})),$(".range-input-selector").on("mousedown",(function(){$(this).addClass("increased-width")})),$("#filterableStatusDropdown, #filterablePriorityDropdown, #filterableDaterange").on("change",(function(){e.draw()})),$.fn.dataTable.ext.search.push((function(e,t,a){var o=removeWhitespace($("#status-tabs .active").data("status-name")).toLowerCase(),r=$("#filterableStatusDropdown").val(),n=removeWhitespace($("#filterablePriorityDropdown").val()).toLowerCase(),c=$("#filterableDaterange").val(),l=$(".range-input-selector").val(),i=parseFloat($("#filter_budget_from").val()),s=parseFloat($("#filter_budget_to").val()),d=removeWhitespace(t[2]).toLowerCase(),u=removeWhitespace(t[5]).toLowerCase(),m=parseInt(t[3],10),f=removeWhitespace(t[4]).toLowerCase(),p=removeWhitespace(t[6]).toLowerCase(),h=parseFloat(t[8]),b=t[9],v=removeWhitespace($("#searchByProjectName").val()).toLowerCase(),g=removeWhitespace($("#searchByComment").val()).toLowerCase();if(l<=h)return!1;if(i&&h<=i||s&&h>=s)return!1;if(c){var w=c.split(" - "),y=moment(w[0],"MM/DD/YYYY"),k=moment(w[1],"MM/DD/YYYY");if(!moment(b,"DD-MM-YYYY HH:mm").isBetween(y,k,void 0,"[]"))return!1}return"archivedprojects"===o&&1===m||!(o&&f!==o||r.length&&!r.includes(f)||n&&p!==n||""!==v&&-1===d.indexOf(v)||""!==g&&-1===u.indexOf(g))})),$(document).on("change","#bulk-action-selector",(function(){var t=$(this).find("option:selected"),a=t.val();if(a&&"Bulk actions"!==a){for(var o=t.data("title"),r=t.data("text"),n=t.data("type"),c=$("input.row-select-checkbox:checked"),l=[],i=0;i<c.length;i++)l.push(c[i].value);Swal.fire({title:o,text:r,showCancelButton:!0,confirmButtonText:"Yes, ".concat(n," it"),cancelButtonText:"No, cancel"}).then((function(t){if(t.isConfirmed){$.ajax({url:route("project.update",1),type:"PUT",data:{type:n,ids:l},success:function(e){console.log(e)}});var a,o=1,r=l.length;Swal.fire({icon:"success",title:"".concat(n.charAt(0).toUpperCase()+n.slice(1)," Successful!"),html:"<b>".concat(o,"</b> project").concat(r>1?"s":""," have been moved to ").concat(n),timer:2e3,timerProgressBar:!0,showConfirmButton:!1,didOpen:function(){Swal.showLoading();var e=Swal.getHtmlContainer().querySelector("b");a=setInterval((function(){o<r?(o++,e.textContent="".concat(o)):clearInterval(a)}),100)},willClose:function(){clearInterval(a)}}).then((function(){"delete"===n&&(c.each((function(){var t=$(this).closest("tr");e.row(t).remove()})),e.draw()),$("input#select-all").prop("checked",!1),$(".bulk_action").fadeOut()}))}}))}}))}))},822:()=>{}},a={};function o(e){var r=a[e];if(void 0!==r)return r.exports;var n=a[e]={exports:{}};return t[e](n,n.exports,o),n.exports}o.m=t,e=[],o.O=(t,a,r,n)=>{if(!a){var c=1/0;for(d=0;d<e.length;d++){for(var[a,r,n]=e[d],l=!0,i=0;i<a.length;i++)(!1&n||c>=n)&&Object.keys(o.O).every((e=>o.O[e](a[i])))?a.splice(i--,1):(l=!1,n<c&&(c=n));if(l){e.splice(d--,1);var s=r();void 0!==s&&(t=s)}}return t}n=n||0;for(var d=e.length;d>0&&e[d-1][2]>n;d--)e[d]=e[d-1];e[d]=[a,r,n]},o.o=(e,t)=>Object.prototype.hasOwnProperty.call(e,t),(()=>{var e={393:0,380:0};o.O.j=t=>0===e[t];var t=(t,a)=>{var r,n,[c,l,i]=a,s=0;if(c.some((t=>0!==e[t]))){for(r in l)o.o(l,r)&&(o.m[r]=l[r]);if(i)var d=i(o)}for(t&&t(a);s<c.length;s++)n=c[s],o.o(e,n)&&e[n]&&e[n][0](),e[n]=0;return o.O(d)},a=self.webpackChunk=self.webpackChunk||[];a.forEach(t.bind(null,0)),a.push=t.bind(null,a.push.bind(a))})(),o.O(void 0,[380],(()=>o(190)));var r=o.O(void 0,[380],(()=>o(822)));r=o.O(r)})();