function MapHandler(mapElementId, initialLatLng = { lat: 51.1657, lng: 10.4515 }, zoom = 5) {
    const mapElement = document.getElementById(mapElementId);
    const markerLocations = getMarkerLocationsFromElement();
    const map = new google.maps.Map(mapElement, {
        center: initialLatLng,
        zoom: zoom,
        mapTypeId: 'terrain',
        streetViewControl: false,
        mapTypeControl: false,
    });
    const mapMarkers = [];
    let currentInfoWindow = null;

    function getMarkerLocationsFromElement() {
        try {
            const dataAttribute = mapElement.getAttribute('data');
            mapElement.setAttribute('data', '');
            return dataAttribute ? JSON.parse(dataAttribute) : [];
        } catch (error) {
            console.error("Failed to parse marker locations from the data attribute.", error);
            return [];
        }
    }

    function addMarkersToMap() {
        const bounds = new google.maps.LatLngBounds();
        markerLocations.forEach((location, index) => {
            const markerPosition = setMarker(location, index);
            bounds.extend(markerPosition);
        });
        map.fitBounds(bounds);
    }

    function setMarker(location, index) {
        const infowindow = new google.maps.InfoWindow({ content: location.content });
        const marker = new google.maps.Marker({
            position: new google.maps.LatLng(location.lat, location.lng),
            map: map,
            icon: pinSymbol(location.backgrounColor),
            html: infowindow,
            animation: google.maps.Animation.DROP,
            locationIndex: index, // Custom property to track the marker index
        });
        setupMarkerEvents(marker, infowindow);
        mapMarkers.push(marker);
        return marker.getPosition();
    }

    function setupMarkerEvents(marker, infowindow) {
        let isInfoWindowOpenByClick = false;

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
            path: 'M384 192c0 87.4-117 243-168.3 307.2c-12.3 15.3-35.1 15.3-47.4 0C117 435 0 279.4 0 192C0 86 86 0 192 0S384 86 384 192z',
            strokeColor: 'none',
            strokeWeight: 0,
            fillColor: backgrounColor,
            fillOpacity: 1,
            scale: 0.09,
        };
    }

    function focusMap(lat, lng, index) {
        map.panTo({ lat, lng });
        // map.setZoom(12);
        closeCurrentInfoWindow();

        const marker = mapMarkers[index];
        if (marker) {
            const infowindow = new google.maps.InfoWindow({
                content: marker.html.getContent(),
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
        const id = $(this).attr('id');
        const lat = Number($(this).data('lat'));
        const lng = Number($(this).data('long'));

        const markerIndex = markerLocations.findIndex(location => location.id == id);
        if (markerIndex !== -1) {
            focusMap(lat, lng, markerIndex);
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    new MapHandler('map');
    $('footer').remove();
});
