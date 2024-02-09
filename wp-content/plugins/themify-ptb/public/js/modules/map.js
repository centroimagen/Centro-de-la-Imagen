var PTB_MapInit = ()=> {
	'use strict';
	var $ = jQuery,
		$maps = $('.ptb_extra_map'),
		mobile = PTB.is_mobile(),
		geocoder  = new google.maps.Geocoder,
		ptb_default =[],
		ptb_geocode = function (opt,callback){
		   geocoder.geocode(opt, function(results, status) {
			 if (status === 'OK' && results[0]) {
				   if(callback){
					   callback(results);
				   }
			 }
		   });
	   },
		Initialize = function (map, data) {
			if(!data.place){
				if(!data.default){
					return;
				}
				if(ptb_default[data.default]===undefined){
					ptb_geocode({'address': data.default},function(results){
						var loc = results[0].geometry.location;
						ptb_default[data.default] = {};
						ptb_default[data.default]['lat'] = loc.lat();
						ptb_default[data.default]['lng'] = loc.lng();
						data.place = {};
						data.place.location = ptb_default[data.default];
						setMap(map,data);
					});
				}
				else{
					data.place.location = ptb_default[data.default];
					setMap(map,data);
				}
			}
			else{
				setMap(map,data);
			}
		function setMap(map,options){
				
				if( options.display === 'text'){

						var opt;
						if(options.place.location){
							var lat = options.place.location['lat']?options.place.location['lat']:options.place.location[0],
								lng = options.place.location['lng']?options.place.location['lng']:options.place.location[1];
						   options.place.location['lat'] = parseFloat(lat);
						   options.place.location['lng'] = parseFloat(lng);
						   opt = {'location': options.place.location};
						} else if(options.place.place) {
								opt = {'place_id': options.place.place};
						} else {
								return false;
						}
						ptb_geocode(opt,function(results){
							 $(map).text(results[0].formatted_address);
						});
				} 
				else if (options.place.place || options.place.location) {

					$(map).css('height', options.height).closest('.ptb_map').css('width', options.width + (options.width_t === '%' ? '%' : ''));
					  var road = options.mapTypeId;
					if (road === 'ROADMAP') {
						  road = google.maps.MapTypeId.ROADMAP;
					} else if (road === 'SATELLITE') {
						  road = google.maps.MapTypeId.SATELLITE;
					} else if (road === 'HYBRID') {
						  road = google.maps.MapTypeId.HYBRID;
					} else if (road === 'TERRAIN') {
						  road = google.maps.MapTypeId.TERRAIN;
					  }
					if (mobile && options.drag_m) {
						  options.drag = false;
					  }
					var mapOptions = {
						center: new google.maps.LatLng(-34.397, 150.644),
						zoom: options.zoom,
						mapTypeId: road,
						scrollwheel: !!options.scroll,
						draggable: !!options.drag
						},
						map = new google.maps.Map(map, mapOptions),
						$content = options.info ? options.info.replace(/(?:\r\n|\r|\n)/ig, '<br />') : '',
						marker = new google.maps.Marker({
								map: map,
								anchorPoint: new google.maps.Point(0, -29)
						});
					  marker.setVisible(false);
					if(options.place.location){
						options.place.location['lat'] = options.place.location['lat'] ? parseFloat(options.place.location['lat']) : parseFloat(options.place.location[0]);
						options.place.location['lng'] = options.place.location['lng'] ? parseFloat(options.place.location['lng']) : parseFloat(options.place.location[1]);
						marker.setPosition(options.place.location);
						map.setCenter(options.place.location);
						marker.setVisible(true);
					}
					else if (options.place.place) {
							var service = new google.maps.places.PlacesService(map);
						service.getDetails({
							placeId: options.place.place
						}, (place, status) =>{
								if (status === google.maps.places.PlacesServiceStatus.OK) {
									map.setCenter(place.geometry.location);
									marker.setIcon(({
									  url: place.icon,
									  size: new google.maps.Size(71, 71),
									  origin: new google.maps.Point(0, 0),
									  anchor: new google.maps.Point(17, 34),
									  scaledSize: new google.maps.Size(35, 35)
									}));
									marker.setPosition(place.geometry.location);
									if (place.geometry.viewport) {
										map.fitBounds(place.geometry.viewport);
									} else {
										map.setCenter(place.geometry.location);
									}
									map.setZoom(mapOptions.zoom);
									marker.setVisible(true);
							} else {
									return false;
								}
						  });
					}
					if ($content) {
						var infowindow = new google.maps.InfoWindow({
							content: $content
						});
						infowindow.open(map, marker);
						google.maps.event.addListener(marker, 'click', ()=> {
							infowindow.open(map, marker);
						});
				  }
			}
		}
	};
	$maps.each(function() {
		var $data = $(this).data('map');
		Initialize(this, $data);
	});
};

( ( document, $ ) => {
	'use strict';
	document.body.addEventListener( 'ptb_map_init', function( e ) {
		const context = e.detail.context;
		if ( $( '.ptb_extra_map', context ).length > 0) {
			if (typeof google !== 'object' || typeof google.maps !== 'object' || typeof google.maps.places === 'undefined') {
				if (typeof google === 'object' && google !== null && typeof google.maps === 'object' && typeof google.maps.places === 'undefined') {
					google.maps = null;
				}
				PTB.LoadAsync('//maps.googleapis.com/maps/api/js?v=3.exp&libraries=places&callback=PTB_MapInit&language=' + ptb.lng + '&key=' + ptb.map_key, null, false, function(){
					return typeof google === 'object' && typeof google.maps === 'object';
				});
			} else {
				PTB_MapInit();
			}
		}
	} );

} )( document, jQuery );