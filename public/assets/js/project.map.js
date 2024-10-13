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
  var map = new google.maps.Map(mapElement, {
    center: mapCenter,
    zoom: parseInt(savedZoom),
    mapTypeId: 'terrain',
    streetViewControl: false,
    mapTypeControl: false,
    styles: [{
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
    }]
  });
  var mapMarkers = [];
  var currentInfoWindow = null;
  map.addListener('zoom_changed', function () {
    var currentZoom = map.getZoom();
    localStorage.setItem('mapZoomLevel', currentZoom);
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
      scale: 1.5
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
  addMarkersToMap();
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
    if (!searchTerm) {
      markerLocations.forEach(function (location, index) {
        var marker = mapMarkers[index];
        marker.setVisible(true);
      });
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
      if (isMatch) {
        var projectId = $(this).find('a').attr('id');
        markerLocations.forEach(function (location, index) {
          var marker = mapMarkers[index];
          if (location.id == projectId) {
            marker.setVisible(true);
            map.panTo(marker.getPosition());
            var infowindow = new google.maps.InfoWindow({
              content: marker.html.getContent()
            });
            closeCurrentInfoWindow();
            infowindow.open(map, marker);
            currentInfoWindow = infowindow;
            setTimeout(function () {
              $('#searchInput').focus();
            }, 20);
          } else {
            marker.setVisible(false);
          }
        });
      }
    });
  });
}
document.addEventListener('DOMContentLoaded', function () {
  new MapHandler('map');
  $('footer').remove();
});
/******/ })()
;