// https://developers.google.com/maps/documentation/javascript/examples/
function initMap() {
	var customMapType = new google.maps.StyledMapType([
			{
				stylers: [
					{'saturation': -100},
					{'lightness': 51},
					{'visibility': 'simplified'}
				]
			},
			{
				elementType: 'labels',
				stylers: [{visibility: 'on'}]
			},
			{
				featureType: 'water',
				stylers: [{color: '#bac6cb'}]
			}
		], {
			name: 'Unify Style'
	});

	// var image = new google.maps.MarkerImage(
	// 	'assets/img/marker.png',
	// 	new google.maps.Size(48,54),
	// 	new google.maps.Point(0,0),
	// 	new google.maps.Point(24,54)
	// 	);

	var customMapTypeId = 'custom_style';

	var map = new google.maps.Map(document.getElementById('map'), {
		zoom: 14,
		scrollwheel: false,
		center: {lat: 20.625624, lng: -87.071972},  // .
		mapTypeControlOptions: {
			mapTypeIds: [google.maps.MapTypeId.ROADMAP, customMapTypeId]
		}
	});

	var infowindow = new google.maps.InfoWindow;
	infowindow.setContent('<b>Brooklyn</b>');

	var marker = new google.maps.Marker({
		map: map,
		clickable: false,
		// icon: image,
		// position: {lat: 40.674, lng: -73.946}
	});

	map.mapTypes.set(customMapTypeId, customMapType);
	map.setMapTypeId(customMapTypeId);
}
