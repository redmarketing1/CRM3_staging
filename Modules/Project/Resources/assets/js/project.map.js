function MapHandler(mapElementId, initialLatLng = { lat: 51.1657, lng: 10.4515 }, defaultZoom = 8) {
    var savedZoom = localStorage.getItem('mapZoomLevel') ?? defaultZoom;

    var savedLat = localStorage.getItem('mapCenterLat');
    var savedLng = localStorage.getItem('mapCenterLng');

    const mapCenter = savedLat && savedLng
        ? { lat: parseFloat(savedLat), lng: parseFloat(savedLng) }
        : initialLatLng;

    const mapElement = document.getElementById(mapElementId);
    const markerLocations = getMarkerLocationsFromElement();

    const hiddenPOIStyles = [
        {
            featureType: "poi",
            elementType: "labels",
            stylers: [{ visibility: "off" }]
        },
        { featureType: "poi.business", stylers: [{ visibility: "off" }] },
        { featureType: "poi.medical", stylers: [{ visibility: "off" }] },
        { featureType: "poi.school", stylers: [{ visibility: "off" }] },
        { featureType: "poi.sports_complex", stylers: [{ visibility: "off" }] },
        { featureType: "poi.park", stylers: [{ visibility: "off" }] },
        { featureType: "transit.station", stylers: [{ visibility: "off" }] },
        { featureType: "road", elementType: "labels", stylers: [{ visibility: "off" }] },
    ];

    const defaultStyles = [];

    const map = new google.maps.Map(mapElement, {
        center: mapCenter,
        zoom: parseInt(savedZoom),
        mapTypeId: 'terrain',
        streetViewControl: false,
        mapTypeControl: false,
        styles: hiddenPOIStyles,
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
            path: 'M33,13.924C33,6.893,27.594,1,20.51,1S8,6.897,8,13.93C8,16.25,8.324,18,9.423,20H9.402l10.695,20.621 c0.402,0.551,0.824-0.032,0.824-0.032C20.56,41.13,31.616,20,31.616,20h-0.009C32.695,18,33,16.246,33,13.924z M14.751,13.528 c0-3.317,2.579-6.004,5.759-6.004c3.179,0,5.76,2.687,5.76,6.004s-2.581,6.005-5.76,6.005C17.33,19.533,14.751,16.846,14.751,13.528 z',
            strokeColor: '#222',
            strokeWeight: 1,
            fillColor: backgroundColor,
            fillOpacity: 1,
            scale: 1,
            anchor: new google.maps.Point(21, 42),
            labelOrigin: new google.maps.Point(20, 60)
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

    function addPOIToggleButton(map, mapElement, hiddenPOIStyles, defaultStyles) {
        let poiHidden = true;

        const toggleButton = document.createElement('button');
        toggleButton.textContent = "POI Visibility";
        toggleButton.classList = "bg-white rounded px-2 shadow-sm py-sm-1 font-semibold";
        toggleButton.style.position = 'absolute';
        toggleButton.style.top = '10px';
        toggleButton.style.left = '10px';
        toggleButton.style.zIndex = '999';
        mapElement.appendChild(toggleButton);

        toggleButton.addEventListener('click', function () {
            poiHidden = !poiHidden;
            map.setOptions({ styles: poiHidden ? hiddenPOIStyles : defaultStyles });
        });
    }

    function addToggleLabelsButton(map, mapElement) {
        let labelsVisible = true;

        const toggleButton = document.createElement('button');
        toggleButton.textContent = "Label Visibility";
        toggleButton.classList = "bg-white rounded px-2 shadow-sm py-sm-1 font-semibold";
        toggleButton.style.position = 'absolute';
        toggleButton.style.top = '50px';
        toggleButton.style.left = '10px';
        toggleButton.style.zIndex = '999';
        mapElement.appendChild(toggleButton);


        toggleButton.addEventListener('click', function () {
            labelsVisible = !labelsVisible;
            markerLocations.forEach((location, index) => {
                const marker = mapMarkers[index];
                if (labelsVisible) {
                    marker.setLabel(null);
                } else {
                    marker.setLabel({
                        text: location.name,
                        color: "#222222",
                        fontSize: "15px",
                        fontWeight: 'bold',
                        className: "project-label",
                    });
                }
            });

            // toggleButton.textContent = labelsVisible ? 'Label Hide' : 'Label Visibility';
        });
    }

    addMarkersToMap();

    setTimeout(function () {
        addPOIToggleButton(map, mapElement, hiddenPOIStyles, defaultStyles);
        addToggleLabelsButton(map, mapElement);
    }, 2000);
}

document.addEventListener('DOMContentLoaded', () => {
    new MapHandler('map');
    $('footer').remove();
});
