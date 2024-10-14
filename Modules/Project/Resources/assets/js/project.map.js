function MapHandler(mapElementId, initialLatLng = { lat: 51.1657, lng: 10.4515 }, defaultZoom = 8) {
    var savedZoom = localStorage.getItem('mapZoomLevel') ?? defaultZoom;

    var savedLat = localStorage.getItem('mapCenterLat');
    var savedLng = localStorage.getItem('mapCenterLng');

    const mapCenter = savedLat && savedLng
        ? { lat: parseFloat(savedLat), lng: parseFloat(savedLng) }
        : initialLatLng;

    const mapElement = document.getElementById(mapElementId);
    const markerLocations = getMarkerLocationsFromElement();
    const map = new google.maps.Map(mapElement, {
        center: mapCenter,
        zoom: parseInt(savedZoom),
        mapTypeId: 'terrain',
        streetViewControl: false,
        mapTypeControl: false,
        styles: [
            {
                featureType: "poi",
                elementType: "labels",
                stylers: [{ visibility: "off" }]
            },
            {
                featureType: "poi.business",
                stylers: [{ visibility: "off" }]
            },
            {
                featureType: "poi.medical",
                stylers: [{ visibility: "off" }]
            },
            {
                featureType: "poi.school",
                stylers: [{ visibility: "off" }]
            },
            {
                featureType: "poi.sports_complex",
                stylers: [{ visibility: "off" }]
            },
            {
                featureType: "poi.park",
                stylers: [{ visibility: "off" }]
            },
            {
                featureType: "transit.station",
                stylers: [{ visibility: "off" }]
            },
            {
                featureType: "road",
                elementType: "labels",
                stylers: [{ visibility: "off" }]
            },
        ]
    });
    const mapMarkers = [];
    let currentInfoWindow = null;

    map.addListener('zoom_changed', function () {
        var currentZoom = map.getZoom();
        localStorage.setItem('mapZoomLevel', currentZoom);
    });

    google.maps.event.addListener(map, 'zoom_changed', function () {
        mapMarkers.forEach(marker => {
            const position = marker.getPosition();
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
        if (!savedZoom) {
            map.fitBounds(bounds);
        }
    }

    function setMarker(location, index) {
        const infowindow = new google.maps.InfoWindow({ content: location.content });
        const marker = new google.maps.Marker({
            position: new google.maps.LatLng(location.lat, location.lng),
            map: map,
            icon: pinSymbol(location.backgrounColor),
            html: infowindow,
            animation: google.maps.Animation.DROP,
            locationIndex: index,
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
        map.panTo({ lat, lng });
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

    $('#searchInput').on('input', function () {
        const searchTerm = $(this).val().toLowerCase();
        const projectIds = [];

        if (!searchTerm) {
            markerLocations.forEach((location, index) => {
                const marker = mapMarkers[index];
                marker.setVisible(true);
            });
            return;
        }

        if (!$('#allprojects').hasClass('active')) {
            $('.tab-pane.fade').removeClass('active show');
            $('#allprojects').addClass('active show');
        }

        $('#allprojects li.tab-item').each(function () {
            const projectName = $(this).find('a').text().toLowerCase();
            const isMatch = projectName.includes(searchTerm);

            if (isMatch) {
                const id = parseInt($(this).find('a').attr('id'));
                projectIds.push(id);
            }

            const bounds = new google.maps.LatLngBounds();

            markerLocations.forEach((location, index) => {
                const marker = mapMarkers[index];
                const locationId = parseInt(location.id);

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

        const tabId = $(this).attr('id').replace('tab-', '');
        const projectId = [];

        $('#' + tabId).find('a').each(function () {
            let ids = parseInt($(this).attr('id'));
            projectId.push(ids);
        });

        const bounds = new google.maps.LatLngBounds();

        markerLocations.forEach((location, index) => {
            const marker = mapMarkers[index];
            const locationId = parseInt(location.id);

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

    addMarkersToMap();
}

document.addEventListener('DOMContentLoaded', () => {
    new MapHandler('map');
    $('footer').remove();
});
