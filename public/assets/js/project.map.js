/******/ (() => { // webpackBootstrap
/*!************************************************************!*\
  !*** ./Modules/Project/Resources/assets/js/project.map.js ***!
  \************************************************************/
function MapHandler(mapElementId) {
  var initialLatLng = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {
    lat: 51.1657,
    lng: 10.4515
  };
  var zoom = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : 5;
  var mapElement = document.getElementById(mapElementId);
  var markerLocations = getMarkerLocationsFromElement();
  var map = new google.maps.Map(mapElement, {
    center: initialLatLng,
    zoom: zoom,
    mapTypeId: 'terrain',
    streetViewControl: false,
    mapTypeControl: false
  });
  var mapMarkers = [];
  var currentInfoWindow = null;
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
    map.fitBounds(bounds);
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
      locationIndex: index // Custom property to track the marker index
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
  function pinSymbol(backgrounColor) {
    return {
      path: 'M120-120v-560h240v-80l120-120 120 120v240h240v400H120Zm80-80h80v-80h-80v80Zm0-160h80v-80h-80v80Zm0-160h80v-80h-80v80Zm240 320h80v-80h-80v80Zm0-160h80v-80h-80v80Zm0-160h80v-80h-80v80Zm0-160h80v-80h-80v80Zm240 480h80v-80h-80v80Zm0-160h80v-80h-80v80Z',
      strokeColor: 'none',
      strokeWeight: 0,
      fillColor: backgrounColor,
      fillOpacity: 1,
      scale: 0.06
    };
  }
  function focusMap(lat, lng, index) {
    map.panTo({
      lat: lat,
      lng: lng
    });
    // map.setZoom(12);
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
}
document.addEventListener('DOMContentLoaded', function () {
  new MapHandler('map');
  $('footer').remove();
});
/******/ })()
;