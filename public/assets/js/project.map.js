/******/ (() => { // webpackBootstrap
/*!************************************************************!*\
  !*** ./Modules/Project/Resources/assets/js/project.map.js ***!
  \************************************************************/
function MapHandler(mapElementId) {
  var _localStorage$getItem;
  var initialLatLng = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {
    lat: 51.1657,
    lng: 10.4515
  };
  var defaultZoom = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : 8;
  var savedZoom = (_localStorage$getItem = localStorage.getItem('mapZoomLevel')) !== null && _localStorage$getItem !== void 0 ? _localStorage$getItem : defaultZoom;
  var savedLat = localStorage.getItem('mapCenterLat');
  var savedLng = localStorage.getItem('mapCenterLng');
  var mapCenter = savedLat && savedLng ? {
    lat: parseFloat(savedLat),
    lng: parseFloat(savedLng)
  } : initialLatLng;
  var mapElement = document.getElementById(mapElementId);
  var markerLocations = getMarkerLocationsFromElement();
  var hiddenPOIStyles = [{
    featureType: "poi",
    elementType: "labels",
    stylers: [{
      visibility: "off"
    }]
  }, {
    featureType: "poi.business",
    stylers: [{
      visibility: "off"
    }]
  }, {
    featureType: "poi.medical",
    stylers: [{
      visibility: "off"
    }]
  }, {
    featureType: "poi.school",
    stylers: [{
      visibility: "off"
    }]
  }, {
    featureType: "poi.sports_complex",
    stylers: [{
      visibility: "off"
    }]
  }, {
    featureType: "poi.park",
    stylers: [{
      visibility: "off"
    }]
  }, {
    featureType: "transit.station",
    stylers: [{
      visibility: "off"
    }]
  }, {
    featureType: "road",
    elementType: "labels",
    stylers: [{
      visibility: "off"
    }]
  }];
  var defaultStyles = [];
  var map = new google.maps.Map(mapElement, {
    center: mapCenter,
    zoom: parseInt(savedZoom),
    mapTypeId: 'terrain',
    streetViewControl: false,
    mapTypeControl: false,
    styles: hiddenPOIStyles
  });
  var mapMarkers = [];
  var currentInfoWindow = null;
  map.addListener('zoom_changed', function () {
    var currentZoom = map.getZoom();
    localStorage.setItem('mapZoomLevel', currentZoom);
  });
  google.maps.event.addListener(map, 'zoom_changed', function () {
    mapMarkers.forEach(function (marker) {
      var position = marker.getPosition();
      marker.setPosition(position); // Re-set the marker position on zoom change
    });
  });
  map.addListener('center_changed', function () {
    var center = map.getCenter();
    localStorage.setItem('mapCenterLat', center.lat());
    localStorage.setItem('mapCenterLng', center.lng());
  });
  function getMarkerLocationsFromElement() {
    try {
      var dataAttribute = mapElement.getAttribute('data');
      mapElement.setAttribute('data', '');
      return dataAttribute ? JSON.parse(dataAttribute) : [];
    } catch (error) {
      console.error("Failed to parse marker locations from the data attribute.", error);
      return [];
    }
  }
  function addMarkersToMap() {
    var bounds = new google.maps.LatLngBounds();
    markerLocations.forEach(function (location, index) {
      var markerPosition = setMarker(location, index);
      bounds.extend(markerPosition);
    });
    if (!savedZoom) {
      map.fitBounds(bounds);
    }
  }
  function setMarker(location, index) {
    var infowindow = new google.maps.InfoWindow({
      content: location.content
    });
    var marker = new google.maps.Marker({
      position: new google.maps.LatLng(location.lat, location.lng),
      map: map,
      icon: pinSymbol(location.backgrounColor),
      html: infowindow,
      animation: google.maps.Animation.DROP,
      locationIndex: index
      // label: {
      //     text: location.name,
      //     color: "#222222",
      //     fontSize: "13px",
      // }
    });
    setupMarkerEvents(marker, infowindow);
    mapMarkers.push(marker);
    return marker.getPosition();
  }
  function setupMarkerEvents(marker, infowindow) {
    var isInfoWindowOpenByClick = false;
    marker.addListener('mouseover', function (event) {
      if (!isInfoWindowOpenByClick) {
        infowindow.setContent(this.html);
        infowindow.setPosition(event.latLng);
        infowindow.open(map, this);
      }
    });
    marker.addListener('mouseout', function () {
      if (!isInfoWindowOpenByClick) {
        infowindow.close();
      }
    });
    marker.addListener('click', function (event) {
      if (isInfoWindowOpenByClick) {
        infowindow.close();
        isInfoWindowOpenByClick = false;
      } else {
        infowindow.setContent(this.html);
        infowindow.setPosition(event.latLng);
        infowindow.open(map, this);
        isInfoWindowOpenByClick = true;
      }
    });
  }
  function pinSymbol(backgroundColor) {
    return {
      path: 'M16.001 5c-4.216 0-7.714 3.418-7.634 7.634.029 1.578.719 2.824 1.351 4.024.242.461 6.264 10.332 6.264 10.332V27l.001-.007.002.007v-.01l6.531-10.377c.407-.703.793-1.771.793-1.771A7.631 7.631 0 0 0 16.001 5zM16 16.019a3.895 3.895 0 0 1-3.896-3.897A3.898 3.898 0 1 1 16 16.019z',
      strokeColor: '#000',
      strokeWeight: 1,
      fillColor: backgroundColor,
      fillOpacity: 1,
      scale: 1,
      anchor: new google.maps.Point(17, 40)
    };
  }
  function focusMap(lat, lng, index) {
    map.panTo({
      lat: lat,
      lng: lng
    });
    closeCurrentInfoWindow();
    var marker = mapMarkers[index];
    if (marker) {
      var infowindow = new google.maps.InfoWindow({
        content: marker.html.getContent()
      });
      infowindow.open(map, marker);
      currentInfoWindow = infowindow;
    }
  }
  function closeCurrentInfoWindow() {
    if (currentInfoWindow) {
      currentInfoWindow.close();
      currentInfoWindow = null;
    }
  }
  $(document).on('click', '.map-wrapper .tab-link', function (e) {
    e.preventDefault();
    var id = $(this).attr('id');
    var lat = Number($(this).data('lat'));
    var lng = Number($(this).data('long'));
    var markerIndex = markerLocations.findIndex(function (location) {
      return location.id == id;
    });
    if (markerIndex !== -1) {
      focusMap(lat, lng, markerIndex);
    }
  });
  $('#searchInput').on('input', function () {
    var searchTerm = $(this).val().toLowerCase();
    var projectIds = [];
    if (!searchTerm) {
      markerLocations.forEach(function (location, index) {
        var marker = mapMarkers[index];
        marker.setVisible(true);
      });
      return;
    }
    if (!$('#allprojects').hasClass('active')) {
      $('.tab-pane.fade').removeClass('active show');
      $('#allprojects').addClass('active show');
    }
    $('#allprojects li.tab-item').each(function () {
      var projectName = $(this).find('a').text().toLowerCase();
      var isMatch = projectName.includes(searchTerm);
      if (isMatch) {
        var id = parseInt($(this).find('a').attr('id'));
        projectIds.push(id);
      }
      var bounds = new google.maps.LatLngBounds();
      markerLocations.forEach(function (location, index) {
        var marker = mapMarkers[index];
        var locationId = parseInt(location.id);
        if (projectIds.includes(locationId)) {
          marker.setVisible(true);
          bounds.extend(new google.maps.LatLng(location.lat, location.lng));
        } else {
          marker.setVisible(false);
        }
      });
      if (!bounds.isEmpty()) {
        map.fitBounds(bounds);
      }
      $(this).toggle(isMatch);
    });
  });
  $(document).on('click', '.nav-link', function (e) {
    e.preventDefault();
    var tabId = $(this).attr('id').replace('tab-', '');
    var projectId = [];
    $('#' + tabId).find('a').each(function () {
      var ids = parseInt($(this).attr('id'));
      projectId.push(ids);
    });
    var bounds = new google.maps.LatLngBounds();
    markerLocations.forEach(function (location, index) {
      var marker = mapMarkers[index];
      var locationId = parseInt(location.id);
      if (projectId.includes(locationId)) {
        marker.setVisible(true);
        bounds.extend(new google.maps.LatLng(location.lat, location.lng));
      } else {
        marker.setVisible(false);
      }
    });
    if (!bounds.isEmpty()) {
      map.fitBounds(bounds);
    }
  });
  function addPOIToggleButton(map, mapElement, hiddenPOIStyles, defaultStyles) {
    var poiHidden = true;
    var toggleButton = document.createElement('button');
    toggleButton.textContent = "POI Visibility";
    toggleButton.classList = "bg-white rounded px-2 shadow-sm py-sm-1 font-semibold";
    toggleButton.style.position = 'absolute';
    toggleButton.style.top = '10px';
    toggleButton.style.left = '10px';
    toggleButton.style.zIndex = '999';
    mapElement.appendChild(toggleButton);
    toggleButton.addEventListener('click', function () {
      poiHidden = !poiHidden;
      map.setOptions({
        styles: poiHidden ? hiddenPOIStyles : defaultStyles
      });
    });
  }
  addMarkersToMap();
  addPOIToggleButton(map, mapElement, hiddenPOIStyles, defaultStyles);
}
document.addEventListener('DOMContentLoaded', function () {
  new MapHandler('map');
  $('footer').remove();
});
/******/ })()
;