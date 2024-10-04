@extends('layouts.main')

@section('content')
    <div class="row">
        <div class="col-xl-12 mb-3 map-wrapper">
            <div class="card map-card no-padding">
                <div class="card-header map_filter p-3 text-end">
                    <div class="form-group">
                        <input type="checkbox" class="form-check-input map_address_type" id="construction_address"
                            value="construction" checked />
                        <label class="form-check-label f-w-600 pt-1 ms-1" for="construction_address">
                            Construction Address
                        </label>
                        <input type="checkbox" class="form-check-input map_address_type" id="invoice_address"
                            value="invoice" />
                        <label class="form-check-label f-w-600 pt-1 ms-1" for="invoice_address">Invoice Address</label>
                    </div>
                </div>
                <div class="card-body text-center">
                    <div id="map"></div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script
        src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBbTqlUNbqPssvetzvRl4n65HB2g_-o9tE&callback=initMap&libraries=places&v=weekly"
        defer></script>

    <script type="application/javascript">
		let map;
		let autocomplete;
		const mapMarkers = [];

		// Create an array of marker locations (latitude and longitude)
		var markerLocations = [];

		markerLocations = {!! json_encode($locations) !!};

		$(document).ready(function () {
			$(document).on('change', '.map_address_type', function() {
				initMap();
			});
		});

		function initMap() {
			// Initialize the map
			const myLatLng = { lat: 51.1657, lng: 10.4515 };
			map = new google.maps.Map(document.getElementById('map'), {
				center: myLatLng,
				zoom: 4
			});
			const bounds = new google.maps.LatLngBounds();

			for (var i = 0; i < markerLocations.length; i++) {
				$('.map_address_type').each(function() {
					if ($(this).is(":checked")) {
						if (markerLocations[i]['address_type'] == $(this).val()) {
							var value_position = setMarkerLocations(markerLocations[i]);
							bounds.extend(value_position);
						}
					}
				});
			}
			map.fitBounds(bounds);

			setTimeout(function() {
				showHideLoader('hidden');
			}, 600);
		}

		function setMarkerLocations(location){
			var contentString = '';
			contentString = '<div id="content">' + '<div id="siteNotice">' + "</div>" + '<h5><b>'+ location.project_name +'</b></h5>' + '<h6 id="firstHeading" class="firstHeading">'+location.title+'</h6>' + '<div id="bodyContent">' + '<p>'+ location.content +'</p>' + "</div>" + "</div>";
			const infowindow = new google.maps.InfoWindow({
				content: contentString,
			});
			randomPoint = new google.maps.LatLng(location.lat, location.lng);
			const marker = new google.maps.Marker({
				position: randomPoint,
				icon: pinSymbol(location.color),
				html : infowindow,
				labelVisible: true,
				map: map
			});
			var isInfoWindowOpenByClick = false;

			marker.addListener('mouseover', function(event) {
				if (!isInfoWindowOpenByClick) { 
					infowindow.setContent(this.html);
					infowindow.setPosition(event.latLng);
					infowindow.open(map, this);
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
					infowindow.open(map, this);
					isInfoWindowOpenByClick = true;
				}
			});
			mapMarkers.push(marker);
			return marker.getPosition();
		}

		function pinSymbol(color) {
			return {
				path: 'M 0,0 C -2,-20 -10,-22 -10,-30 A 10,10 0 1,1 10,-30 C 10,-22 2,-20 0,0 z',
				// path: 'M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z', // 'M -2,0 0,-2 2,0 0,2 z',
				strokeColor: '#000',
				strokeWeight: 1,
				fillColor: color,
				fillOpacity: 1,
				scale: 1
			};
		}
	</script>
@endpush
