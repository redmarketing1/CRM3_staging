@extends('layouts.main')

@section('content')
    <div class="row">
        <div class="col-xl-12 mb-3 map-wrapper">
            <div class="card map-card no-padding">
                <div class="card-body text-center">
                    <div id="map" data="{{ json_encode($locations, JSON_UNESCAPED_UNICODE) }}"></div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script
        src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBbTqlUNbqPssvetzvRl4n65HB2g_-o9tE&callback=initMap&libraries=places&v=weekly"
        defer></script>

    <script>
        class MapHandler {
            constructor(mapElementId, initialLatLng = {
                lat: 51.1657,
                lng: 10.4515
            }, zoom = 5) {
                this.mapElementId = mapElementId;
                this.mapElement = document.getElementById(mapElementId);
                this.markerLocations = this.getMarkerLocationsFromElement();
                this.initialLatLng = initialLatLng;
                this.zoom = zoom;
                this.map = null;
                this.mapMarkers = [];
                this.initMap();
            }

            getMarkerLocationsFromElement() {
                try {
                    const dataAttribute = this.mapElement.getAttribute('data');
                    return dataAttribute ? JSON.parse(dataAttribute) : [];
                } catch (error) {
                    console.error("Failed to parse marker locations from the data attribute.", error);
                    return [];
                }
            }

            initMap() {
                this.map = new google.maps.Map(this.mapElement, {
                    center: this.initialLatLng,
                    zoom: this.zoom,
                    mapTypeId: 'terrain'
                });

                this.addMarkersToMap();
            }

            addMarkersToMap() {
                const bounds = new google.maps.LatLngBounds();

                this.markerLocations.forEach(location => {
                    const markerPosition = this.setMarker(location);
                    bounds.extend(markerPosition);
                });

                this.map.fitBounds(bounds);
            }

            // Set a single marker on the map
            setMarker(location) {

                const infowindow = new google.maps.InfoWindow({
                    content: location.content
                });

                const marker = new google.maps.Marker({
                    position: new google.maps.LatLng(location.lat, location.lng),
                    map: this.map,
                    icon: this.pinSymbol(location.color),
                    html: infowindow,
                    animation: google.maps.Animation.DROP
                });

                this.setupMarkerEvents(marker, infowindow);

                this.mapMarkers.push(marker);
                return marker.getPosition();
            }

            // Setup mouseover, mouseout, and click events for markers
            setupMarkerEvents(marker, infowindow) {
                let isInfoWindowOpenByClick = false;

                marker.addListener('mouseover', function(event) {
                    if (!isInfoWindowOpenByClick) {
                        infowindow.setContent(this.html);
                        infowindow.setPosition(event.latLng);
                        infowindow.open(this.map, this);
                    }
                });

                marker.addListener('mouseout', function() {
                    if (!isInfoWindowOpenByClick) {
                        infowindow.close();
                    }
                });

                marker.addListener('click', function(event) {
                    if (isInfoWindowOpenByClick) {
                        infowindow.close();
                        isInfoWindowOpenByClick = false;
                    } else {
                        infowindow.setContent(this.html);
                        infowindow.setPosition(event.latLng);
                        infowindow.open(this.map, this);
                        isInfoWindowOpenByClick = true;
                    }
                });
            }

            pinSymbol(color) {
                return {
                    path: 'M 0,0 C -2,-20 -10,-22 -10,-30 A 10,10 0 1,1 10,-30 C 10,-22 2,-20 0,0 z',
                    strokeColor: '#000',
                    strokeWeight: 1,
                    fillColor: color,
                    fillOpacity: 1,
                    scale: 1
                };
            }
        }

        function initMap() {
            const mapHandler = new MapHandler('map');

            $(document).on('change', '.map_address_type', function() {
                mapHandler.initMap();
            });
        }
    </script>
@endpush
