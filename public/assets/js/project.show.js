/******/ (() => { // webpackBootstrap
/*!*************************************************************!*\
  !*** ./Modules/Project/Resources/assets/js/project.show.js ***!
  \*************************************************************/
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _slicedToArray(r, e) { return _arrayWithHoles(r) || _iterableToArrayLimit(r, e) || _unsupportedIterableToArray(r, e) || _nonIterableRest(); }
function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function _iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t["return"] && (u = t["return"](), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function _arrayWithHoles(r) { if (Array.isArray(r)) return r; }
function _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
$(document).on("click", ".status", function (event) {
  event.preventDefault();
  var projectID = $(this).attr('data-id');
  var statusID = $(this).attr('data-status');
  var backgroundColor = $(this).attr('data-background');
  var fontColor = $(this).attr('data-font');
  var statusName = $(this).text();
  $('.project-statusName').text(statusName).attr('style', "background-color: ".concat(backgroundColor, " !important; color: ").concat(fontColor, " !important;"));
  $.ajax({
    url: route('project.update', projectID),
    type: 'PUT',
    data: {
      ids: projectID,
      statusID: statusID,
      type: "changeStatus"
    },
    success: function success(response) {
      toastrs('Success', response.success, 'success');
    }
  });
});
$(document).on('change', '#construction-select', function () {
  var selectedOption = $('#construction-select option:selected');
  var selectedType = selectedOption.data('type');
  var clientTypeInput = document.getElementById('client_type1');
  if (selectedType !== undefined && selectedType !== null) {
    clientTypeInput.value = selectedType;
  } else {
    clientTypeInput.value = 'new';
  }
  var url = route('users.get_user');
  var user_id = this.value;

  // Get the selected values
  if (user_id) {
    axios.post(url, {
      'user_id': user_id,
      'from': 'construction'
    }).then(function (response) {
      var clientDetailsElement = document.getElementById('construction-details');
      $('#construction-details').html(response.data.html_data);
      $('#construction_detail_id').val(response.data.user_id);
      if ($('#construction_detail-company_notes').length > 0) {
        init_tiny_mce('#construction_detail-company_notes');
      }

      // Remove the d-none class if the element is found
      if (clientDetailsElement) {
        clientDetailsElement.classList.remove('d-none');
      }
      initGoogleMapPlaced('construction_detail-autocomplete', 'construction_detail');
      $(".country_select2").select2(_defineProperty(_defineProperty(_defineProperty({
        placeholder: "Country",
        multiple: false,
        dropdownParent: $("#title_form")
      }, "placeholder", "Select an country"), "allowClear", true), "dropdownAutoWidth", true));
    });
  } else {
    var clientDetailsElement = document.getElementById('construction-details');
    // Remove the d-none class if the element is found
    if (clientDetailsElement) {
      clientDetailsElement.classList.add('d-none');
    }
  }
});
$(document).on('change', '#client-select', function () {
  var selectedOption = $('#client-select option:selected');
  var selectedType = selectedOption.data('type');
  var clientTypeInput = document.getElementById('client_type');
  if (selectedType !== undefined && selectedType !== null) {
    clientTypeInput.value = selectedType;
  } else {
    clientTypeInput.value = 'new';
  }
  var url;
  var url = route('users.get_user');
  init_tiny_mce('.client-company_notes');

  // Get the selected values
  if (this.value) {
    axios.post(url, {
      'user_id': this.value,
      'from': 'client'
    }).then(function (response) {
      var clientDetailsElement = document.getElementById('client-details');
      $('#client-details').html(response.data.html_data);
      $('#client_id').val(response.data.user_id);
      // initialize();

      if ($('#client-company_notes').length > 0) {
        init_tiny_mce('#client-company_notes');
      }

      // Remove the d-none class if the element is found
      if (clientDetailsElement) {
        clientDetailsElement.classList.remove('d-none');
      }
      initGoogleMapPlaced('invoice-autocomplete', 'invoice');
    });
  } else {
    var clientDetailsElement = document.getElementById('client-details');
    // Remove the d-none class if the element is found
    if (clientDetailsElement) {
      clientDetailsElement.classList.add('d-none');
    }
  }
});
$(document).on('click', '.change-archive', function (event) {
  event.preventDefault();
  var id = $(this).data('id');
  var title = $(this).data('title');
  var text = $(this).data('text');
  var type = $(this).data('type');
  Swal.fire({
    title: title,
    text: text,
    showCancelButton: true,
    confirmButtonText: "Yes, ".concat(type, " it"),
    cancelButtonText: "No, cancel"
  }).then(function (result) {
    if (result.isConfirmed) {
      $.ajax({
        url: route('project.update', 1),
        type: "PUT",
        data: {
          type: type,
          ids: [id]
        },
        success: function success(response) {
          console.log(response);
          window.location.reload();
        }
      });
      Swal.fire({
        icon: 'success',
        title: "".concat(type.charAt(0).toUpperCase() + type.slice(1), " Successful!"),
        html: "Project have been moved to ".concat(type),
        timer: 2000,
        timerProgressBar: true,
        showConfirmButton: false
      });
    }
  });
});
$(document).on('click', '#copyProjectShareLinks', function () {
  var _this = this;
  var copyText = document.getElementById('copyText');
  copyText.select();
  copyText.setSelectionRange(0, 99999);
  document.execCommand('copy');
  this.textContent = 'Copied!';
  this.style.backgroundColor = '#6fd943';
  setTimeout(function () {
    _this.textContent = 'Copy';
    _this.style.backgroundColor = '';
  }, 2000);
  toastrs('success', 'Project\'s shared links has copied to clipboard', 'success');
});
function loadTabMenuPagination() {
  var projectsPerPage = 25;
  function initPaginationForTab(tabContentId) {
    var totalProjects = document.querySelectorAll("#".concat(tabContentId, " a.tab-link")).length;
    var currentVisibleProjects = projectsPerPage;
    function hideProjects(fromIndex) {
      var projectItems = document.querySelectorAll("#".concat(tabContentId, " .tab-item"));
      for (var i = fromIndex; i < totalProjects; i++) {
        projectItems[i].style.display = 'none';
      }
    }
    function showProjects(fromIndex, toIndex) {
      var projectItems = document.querySelectorAll("#".concat(tabContentId, " .tab-item"));
      for (var i = fromIndex; i < toIndex && i < totalProjects; i++) {
        projectItems[i].style.display = 'list-item';
      }
    }
    function updateButtonLabel(number, isLess) {
      var btn = document.querySelector("#".concat(tabContentId, " .pagination-btn"));
      if (isLess) {
        btn.innerText = "Show Less Projects";
      } else {
        btn.innerText = "Show More ".concat(number, " Projects");
      }
    }

    // Create the button dynamically if totalProjects exceeds projectsPerPage
    if (totalProjects > projectsPerPage) {
      var tabContent = document.getElementById(tabContentId);
      var button = document.createElement('div');
      button.classList = 'pagination-btn font-semibold mb-3 mt-3 pointer text-center';
      button.innerText = "Show More ".concat(projectsPerPage, " Projects");
      tabContent.appendChild(button); // Append the button to the tab content

      button.addEventListener('click', function (event) {
        event.preventDefault();
        if (currentVisibleProjects >= totalProjects) {
          currentVisibleProjects = projectsPerPage;
          hideProjects(currentVisibleProjects);
          updateButtonLabel(projectsPerPage, false);
        } else {
          var nextVisible = currentVisibleProjects + projectsPerPage;
          nextVisible = Math.min(nextVisible, totalProjects);
          showProjects(currentVisibleProjects, nextVisible);
          currentVisibleProjects = nextVisible;
          if (currentVisibleProjects >= totalProjects) {
            updateButtonLabel(0, true);
          } else {
            updateButtonLabel(projectsPerPage, false);
          }
        }
      });
      hideProjects(currentVisibleProjects); // Hide projects exceeding projectsPerPage
      updateButtonLabel(projectsPerPage, false);
    } else {
      // If totalProjects is less than or equal to projectsPerPage, show all projects
      var projectItems = document.querySelectorAll("#".concat(tabContentId, " .tab-item"));
      projectItems.forEach(function (item) {
        item.style.display = 'list-item';
      });
    }
  }
  $('#project a.nav-link').each(function () {
    var tabContentId = $(this).attr('id').replace('tab-', '');
    initPaginationForTab(tabContentId);
  });
}
loadTabMenuPagination();
$('#searchInput').on('input', function () {
  var searchTerm = $(this).val().toLowerCase();
  if (searchTerm.length <= 0) {
    $('.pagination-btn').show();
    return;
  }
  ;
  if (!$('#allprojects').hasClass('active')) {
    $('.tab-pane.fade').removeClass('active show');
    $('#allprojects').addClass('active show');
  }
  $('#allprojects li.tab-item').each(function () {
    var projectName = $(this).find('a').text().toLowerCase();
    var isMatch = projectName.includes(searchTerm);
    $(this).toggle(isMatch);
    $('.pagination-btn').hide();
  });
});
function initGoogleMapPlaced(inputSelector, fieldInput) {
  var input = document.getElementById(inputSelector);
  var autocomplete = new google.maps.places.Autocomplete(input);
  autocomplete.addListener('place_changed', function () {
    var place = autocomplete.getPlace();
    if (place.geometry) {
      setGoogleMapsPlaced(place, fieldInput);
    }
  });
  function setGoogleMapsPlaced(place) {
    var selector = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : "";
    var result = {};
    if (!place || !place.geometry || !place.geometry.location) return result;
    result['latitude'] = place.geometry.location.lat();
    result['longitude'] = place.geometry.location.lng();
    var street_number = '';
    place.address_components.forEach(function (component) {
      var componentType = component.types[0];
      switch (componentType) {
        case "street_number":
          street_number = component.long_name;
          break;
        case "route":
          result['address_1'] = "".concat(component.long_name).concat(street_number ? ', ' + street_number : '');
          break;
        case "locality":
          result['city'] = component.long_name;
          break;
        case "sublocality_level_1":
          result['district_1'] = component.long_name;
          break;
        case "administrative_area_level_3":
          result['district_2'] = component.long_name;
          break;
        case "administrative_area_level_1":
          result['state'] = component.long_name;
          break;
        case "postal_code":
        case "postal_code_suffix":
          result['zip_code'] = component.long_name;
          break;
        case "country":
          result['country'] = component.short_name;
          break;
        default:
          result[componentType] = component.long_name;
          break;
      }
    });
    if (selector !== '') {
      var addressFields = ['address_1', 'address_2', 'city', 'district_1', 'district_2', 'state', 'zip_code', 'country'];
      var setFieldValue = function setFieldValue(key) {
        var value = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : '';
        var itemSelector = $("#".concat(selector, "-").concat(key));
        if (itemSelector.length) {
          if (key === 'country') {
            var selectedOption = itemSelector.find("option[data-iso=\"".concat(value, "\"]"));
            if (selectedOption.length) {
              selectedOption.prop('selected', true);
              itemSelector.trigger('change');
            }
          } else {
            itemSelector.val(value);
          }
        }
      };
      addressFields.forEach(function (field) {
        return setFieldValue(field);
      });
      Object.entries(result).forEach(function (_ref) {
        var _ref2 = _slicedToArray(_ref, 2),
          key = _ref2[0],
          value = _ref2[1];
        return setFieldValue(key, value);
      });
    }
    return result;
  }
}
/******/ })()
;