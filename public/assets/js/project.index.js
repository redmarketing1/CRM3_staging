(()=>{var e,t={190:()=>{function e(t){return e="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e},e(t)}function t(t,a,o){return(a=function(t){var a=function(t,a){if("object"!=e(t)||!t)return t;var o=t[Symbol.toPrimitive];if(void 0!==o){var r=o.call(t,a||"default");if("object"!=e(r))return r;throw new TypeError("@@toPrimitive must return a primitive value.")}return("string"===a?String:Number)(t)}(t,"string");return"symbol"==e(a)?a:a+""}(a))in t?Object.defineProperty(t,a,{value:o,enumerable:!0,configurable:!0,writable:!0}):t[a]=o,t}$(document).ready((function(){!function(){var e=$("#projectsTable").DataTable(t(t(t(t(t(t(t(t(t(t({pageLength:50,lengthMenu:[[25,50,100,200],[25,50,100,200]],lengthChange:!0,ordering:!1,searching:!0},"ordering",!1),"layout",{topEnd:null}),"select",{style:"multi"}),"pagingType","simple"),"language",{paginate:{previous:"Previous",next:"Next"}}),"processing",!1),"serverSide",!1),"ajax",{url:route("project.index"),type:"GET",dataType:"json"}),"columns",[{data:null,orderable:!1,className:"dt-body-center input-checkbox",render:function(e,t,a,o){return'<input type="checkbox" class="row-select-checkbox" value="'+e.id+'">'}},{data:"thumbnail",name:"thumbnail",className:"thumbnail"},{data:"status",name:"status",visible:!1,orderable:!0,className:"status"},{data:"name",name:"name",orderable:!0,className:"name"},{data:"comments",name:"comments",orderable:!1,className:"comments"},{data:"is_archive",name:"is_archive",visible:!1,className:"is_archive"},{data:"priority",name:"priority",orderable:!1,className:"priority"},{data:"construction",name:"construction",orderable:!1,className:"construction"},{data:"budget",name:"budget",orderable:!0,className:"budget",createdCell:function(e,t,a,o,r){0==parseInt(t)&&$(e).addClass("zero")}},{data:"created_at",name:"created_at",orderable:!0,className:"created_at"},{data:"action",name:"action",orderable:!1,searchable:!1,className:"action"}]),"initComplete",(function(t,r){r.data;var n=r.filterableStatusList,i=r.filterablePriorityList,l=(r.minBudget,r.maxBudget);if($("#projectsTable colgroup").remove(),n.html){var s=$.parseHTML(n.html);$("#projectsTable").parents(".projectsTableContainter").parents("div.col-xl-12").before(s)}if(n.data)$.map(n.data,(function(e,t){return{id:removeWhitespace(e.name).toLowerCase(),text:e.name,backgroundColor:e.background_color,fontColor:e.font_color}}));if(i){var u=$.map(i,(function(e,t){return{id:e.name,text:e.name,backgroundColor:e.background_color,fontColor:e.font_color}}));$("#filterablePriorityDropdown").select2({data:u,placeholder:"Select Priority",multiple:!0,allowClear:!1,minimumResultsForSearch:1/0,templateResult:a,templateSelection:o})}$("#projectsTable tr:first-child.hide").fadeIn(),$(".daterange").daterangepicker({autoUpdateInput:!1,locale:{cancelLabel:"clear"},ranges:{Today:[moment(),moment()],Yesterday:[moment().subtract(1,"days"),moment().subtract(1,"days")],"This Week":[moment().subtract(6,"days"),moment()],"This Month":[moment().startOf("month"),moment().endOf("month")],"Last 30 Days":[moment().subtract(29,"days"),moment()],"Last Month":[moment().subtract(1,"month").startOf("month"),moment().subtract(1,"month").endOf("month")]}}).on("apply.daterangepicker",(function(t,a){$(this).val(a.startDate.format("MM/DD/YYYY")+" - "+a.endDate.format("MM/DD/YYYY")),e.draw()})).on("cancel.daterangepicker",(function(t,a){$(this).val(""),e.draw()})),$("#projectsTable thead tr:nth-child(2) th:first-child").html('<input type="checkbox" id="select-all">'),$("#projectsTable tbody").on("change",".row-select-checkbox",(function(){$("input.row-select-checkbox:checked").length>0?$("#bulk-action-selector").fadeIn():$("#bulk-action-selector").fadeOut()})),$("#select-all").on("change",(function(){var e=this.checked;$("input.row-select-checkbox").prop("checked",e),$("input.row-select-checkbox:checked").length>0?$("#bulk-action-selector").fadeIn():$("#bulk-action-selector").fadeOut()})),$(".projects-filters .select2").select2({minimumResultsForSearch:1/0}),$("#filter_price_from,#filter_price_to").attr("max",c(l)),$("#filter_price_to,.range-max").val(c(l))})));function a(e){return e.id?$("<span>").css({"background-color":e.backgroundColor,color:e.fontColor,padding:"5px","border-radius":"4px",display:"block"}).text(e.text):e.text}function o(e){return e.id?$("<span>").css({"background-color":e.backgroundColor,color:e.fontColor,padding:"5px","border-radius":"4px",display:"block"}).text(e.text):e.text}function r(t,a,o,r,n,c){Swal.fire({title:a,text:o,showCancelButton:!0,confirmButtonText:"Yes, ".concat(t," it"),cancelButtonText:"No, cancel"}).then((function(a){if(a.isConfirmed){$.ajax({url:route("project.update",1),type:"PUT",data:{type:t,ids:r},success:function(e){console.log(e)}});var o,i=1,l=r.length;Swal.fire({icon:"success",title:"".concat(t.charAt(0).toUpperCase()+t.slice(1)," Successful!"),html:"<b>".concat(i,"</b> project").concat(l>1?"s":""," have been moved to ").concat(t),timer:2e3,timerProgressBar:!0,showConfirmButton:!1,didOpen:function(){Swal.showLoading();var e=Swal.getHtmlContainer().querySelector("b");o=setInterval((function(){i<l?(i++,e.textContent="".concat(i)):clearInterval(o)}),100)},willClose:function(){clearInterval(o)}}).then((function(){"delete"===t&&n.each((function(){var t=$(this).val();$(t).remove(),e.row(t).remove()})),"archive"!==t&&"unarchive"!==t||n.each((function(){var a=$(this).val(),o=e.row("#"+a).data();o.is_archive="archive"===t?1:0,e.row("#"+a).data(o).draw()})),"duplicate"===t&&n.each((function(){var t=$(this).val(),a=e.row("#"+t).data();e.row.add(a).draw()})),"select"===c&&($("input#select-all,.row-select-checkbox").prop("checked",!1),$("#bulk-action-selector").val("bulk").fadeOut())}))}}))}function n(e){var t,a,o=$("#status-tabs .active"),r=removeWhitespace(e[2]||"").toLowerCase();if(t=r,a=new RegExp("(".concat(["lvprufen!","ruckruf"].join("|"),")"),"gi"),r=t.replace(a,"").replace(/\s+/g," ").trim(),removeWhitespace($("#searchProject").val()).toLowerCase().length>0)return!0;if(0===o.length)return $("#status-tabs a").removeClass("active"),$("#status-tabs a").first().addClass("active"),!0;var n=removeWhitespace(o.map((function(){return $(this).data("status-name")})).get().join(" ")).toLowerCase();return""===n||"all"===n||n===r}function c(e){return e.replace(/[€\s]/g,"")}$(document).on("click","#status-tabs a",(function(t){t.preventDefault(),$("#status-tabs a").removeClass("active"),$(this).addClass("active"),e.draw()})),$(document).on("input","#searchProject, #filter_price_from, #filter_price_to",(function(t){e.draw()})),$(document).on("change","#filterableStatusDropdown, #filterablePriorityDropdown, #filterableDaterange, #projectVisibality",(function(){e.draw()})),$(document).on("click","#projectStatus",(function(){$(".data-sub-name").toggle()})),$(document).on("change","#bulk-action-selector",(function(){var e=$(this).find("option:selected"),t=e.val();if(t&&"Bulk actions"!==t){var a=e.data("title"),o=e.data("text"),n=e.data("type"),c=$("input.row-select-checkbox:checked"),i=[];c.each((function(){i.push($(this).val())})),r(n,a,o,i,c,"select")}})),$(document).on("click",".action-btn button",(function(e){e.preventDefault();var t=$(this),a=$(this).val();r(t.data("type"),t.data("title"),t.data("text"),[a],t,"click")})),$(document).on("click","#clearFilter",(function(){$("input#select-all,.row-select-checkbox").prop("checked",!1),$("#bulk-action-selector").val("bulk"),$("#status-tabs a").removeClass("active"),$("#searchProject,#filterableStatusDropdown,#filterablePriorityDropdown,#filterableDaterange").val(null).trigger("change"),$("#filter_price_from").val(0),$("#filter_price_to,.range-max").val($("#filter_price_to").attr("max")),e.draw()})),$.fn.dataTable.ext.search.push((function(e,t,a){return n(t)&&function(e){var t=$("#projectVisibality").val()||"only-active",a=parseInt(e[5],10);return"only-archive"===t?($("#status-tabs").find("a").fadeOut().removeClass("active"),$("#bulk-action-selector").find("option[value=archive]").hide(),$("#bulk-action-selector").find("option[value=unarchive]").show()):($("#status-tabs").find("a").fadeIn(),$("#bulk-action-selector").find("option[value=archive]").show(),$("#bulk-action-selector").find("option[value=unarchive]").hide()),!("only-active"===t&&1===a||"only-archive"===t&&0===a)}(t)&&function(e){var t=removeWhitespace($("#searchProject").val()).toLowerCase(),a=removeWhitespace(e[3]).toLowerCase(),o=removeWhitespace(e[4]).toLowerCase();return t.length>0&&-1!==a.indexOf(t)||-1!==o.indexOf(t)}(t)&&function(e){var t=$("#filterablePriorityDropdown").val()||[],a=removeWhitespace(e[6]).toLowerCase();return 0===t.length||!!(t.length>0&&t.some((function(e){return e===a})))||void 0}(t)&&function(e){var t=parseFloat($("#filter_price_from").val()),a=parseFloat($("#filter_price_to").val()),o=parseFloat(e[8]);return!(t&&o<=t||a&&o>=a)}(t)&&function(e){var t=$("#filterableDaterange").val(),a=e[9];if(t){var o=t.split(" - "),r=moment(o[0],"MM/DD/YYYY"),n=moment(o[1],"MM/DD/YYYY");if(!moment(a,"DD-MM-YYYY HH:mm").isBetween(r,n,void 0,"[]"))return!1}return!0}(t)}))}(),$("#searchInput").on("input",(function(){var e=$(this).val().toLowerCase();e.length<=0?$(".pagination-btn").show():($("#allprojects").hasClass("active")||($(".tab-pane.fade").removeClass("active show"),$("#allprojects").addClass("active show")),$("#allprojects li.tab-item").each((function(){var t=$(this).find("a").text().toLowerCase().includes(e);$(this).toggle(t),$(".pagination-btn").hide()})))})),function(){var e=25;function t(t){var a=document.querySelectorAll("#".concat(t," a.tab-link")).length,o=e;function r(e){for(var o=document.querySelectorAll("#".concat(t," .tab-item")),r=e;r<a;r++)o[r].style.display="none"}function n(e,a){var o=document.querySelector("#".concat(t," .pagination-btn"));o.innerText=a?"Show Less Projects":"Show More ".concat(e," Projects")}if(a>e){var c=document.getElementById(t),i=document.createElement("div");i.classList="pagination-btn font-semibold mb-3 mt-3 pointer text-center",i.innerText="Show More ".concat(e," Projects"),c.appendChild(i),i.addEventListener("click",(function(c){if(c.preventDefault(),o>=a)r(o=e),n(e,!1);else{var i=o+e;i=Math.min(i,a),function(e,o){for(var r=document.querySelectorAll("#".concat(t," .tab-item")),n=e;n<o&&n<a;n++)r[n].style.display="list-item"}(o,i),(o=i)>=a?n(0,!0):n(e,!1)}})),r(o),n(e,!1)}else{document.querySelectorAll("#".concat(t," .tab-item")).forEach((function(e){e.style.display="list-item"}))}}$("#project a.nav-link").each((function(){t($(this).attr("id").replace("tab-",""))}))}()}))},822:()=>{},900:()=>{},663:()=>{},159:()=>{}},a={};function o(e){var r=a[e];if(void 0!==r)return r.exports;var n=a[e]={exports:{}};return t[e](n,n.exports,o),n.exports}o.m=t,e=[],o.O=(t,a,r,n)=>{if(!a){var c=1/0;for(u=0;u<e.length;u++){for(var[a,r,n]=e[u],i=!0,l=0;l<a.length;l++)(!1&n||c>=n)&&Object.keys(o.O).every((e=>o.O[e](a[l])))?a.splice(l--,1):(i=!1,n<c&&(c=n));if(i){e.splice(u--,1);var s=r();void 0!==s&&(t=s)}}return t}n=n||0;for(var u=e.length;u>0&&e[u-1][2]>n;u--)e[u]=e[u-1];e[u]=[a,r,n]},o.o=(e,t)=>Object.prototype.hasOwnProperty.call(e,t),(()=>{var e={393:0,718:0,941:0,819:0,380:0};o.O.j=t=>0===e[t];var t=(t,a)=>{var r,n,[c,i,l]=a,s=0;if(c.some((t=>0!==e[t]))){for(r in i)o.o(i,r)&&(o.m[r]=i[r]);if(l)var u=l(o)}for(t&&t(a);s<c.length;s++)n=c[s],o.o(e,n)&&e[n]&&e[n][0](),e[n]=0;return o.O(u)},a=self.webpackChunk=self.webpackChunk||[];a.forEach(t.bind(null,0)),a.push=t.bind(null,a.push.bind(a))})(),o.O(void 0,[718,941,819,380],(()=>o(190))),o.O(void 0,[718,941,819,380],(()=>o(822))),o.O(void 0,[718,941,819,380],(()=>o(900))),o.O(void 0,[718,941,819,380],(()=>o(663)));var r=o.O(void 0,[718,941,819,380],(()=>o(159)));r=o.O(r)})();