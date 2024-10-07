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
    markerLocations.forEach(function (location) {
      var markerPosition = setMarker(location);
      bounds.extend(markerPosition);
    });
    map.fitBounds(bounds);
  }
  function setMarker(location) {
    var infowindow = new google.maps.InfoWindow({
      content: location.content
    });
    var marker = new google.maps.Marker({
      position: new google.maps.LatLng(location.lat, location.lng),
      map: map,
      icon: pinSymbol(location.color),
      html: infowindow,
      animation: google.maps.Animation.DROP
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
  function pinSymbol(color) {
    return {
      path: 'M120-120v-560h240v-80l120-120 120 120v240h240v400H120Zm80-80h80v-80h-80v80Zm0-160h80v-80h-80v80Zm0-160h80v-80h-80v80Zm240 320h80v-80h-80v80Zm0-160h80v-80h-80v80Zm0-160h80v-80h-80v80Zm0-160h80v-80h-80v80Zm240 480h80v-80h-80v80Zm0-160h80v-80h-80v80Z',
      strokeColor: 'none',
      strokeWeight: 0,
      fillColor: color,
      fillOpacity: 1,
      scale: 0.06
    };
  }
  function createProjectList() {
    var projects = markerLocations;
    var projectDiv = document.createElement('div');
    projectDiv.classList.add('project-list');
    var ulElement = document.createElement('ul');
    projects.forEach(function (item, index) {
      var liElement = document.createElement('li');
      liElement.textContent = item.name;
      liElement.dataset.lat = item.lat;
      liElement.dataset["long"] = item.lng;
      liElement.id = "project-".concat(index);
      liElement.addEventListener('click', function () {
        focusMap(item.lat, item.lng, index);
      });
      ulElement.appendChild(liElement);
    });
    projectDiv.appendChild(ulElement);
    document.querySelector('#projectContainer').innerHTML = '';
    document.querySelector('#projectContainer').appendChild(projectDiv);
  }
  function focusMap(lat, lng, markerIndex) {
    map.panTo({
      lat: lat,
      lng: lng
    });
    closeCurrentInfoWindow();
    var marker = mapMarkers[markerIndex];
    if (marker) {
      var infowindow = new google.maps.InfoWindow({
        content: markerLocations[markerIndex].content || "No content available"
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
  createProjectList();
}
document.addEventListener('DOMContentLoaded', function () {
  var mapHandler = new MapHandler('map');
  $('footer').remove();
});
/******/ })()
;