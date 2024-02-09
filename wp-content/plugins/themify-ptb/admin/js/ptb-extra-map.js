var ptb_extra_geocoder;
function ptb_extra_map_init(e) {
    'use strict';

    function geocodeAddress(address,type,callback) {
        if(!ptb_extra_geocoder){
            ptb_extra_geocoder = new google.maps.Geocoder();
        }
        var method = {};
        method[type] = address;
        ptb_extra_geocoder.geocode(method, function(results, status) {
            if (status !== 'OK') {
                results[0] = null;
            }
            callback(results[0]);
        });
    }

    var $ = jQuery,
            $maps = $('.ptb_post_cmb_item_map');
    $maps.each(function(){
        var mapOptions = {
            center: new google.maps.LatLng(43.67023, -79.38676),
            zoom: 13,
			streetViewControl : false,
			scrollwheel : false
        },
        id = $(this).prop('id'),
                map = new google.maps.Map(document.getElementById('ptb_extra_' + id + '_canvas'), mapOptions),
                input = (document.getElementById('ptb_extra_' + id + '_location')),
                types = document.getElementById('ptb_extra_' + id + '_select');
        map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);
        map.controls[google.maps.ControlPosition.TOP_LEFT].push(types);
        var autocomplete = new google.maps.places.Autocomplete(input);
        autocomplete.bindTo('bounds', map);
        var infowindow = new google.maps.InfoWindow();
        var marker = new google.maps.Marker({
            map: map,
            anchorPoint: new google.maps.Point(0, -29),
            draggable:true,
            animation: google.maps.Animation.DROP
        });
        var $setplace = false,
                $place = $('#ptb_extra_' + id + '_place'),
                $info = $('#ptb_extra_' + id + '_info'),
                location = $('#ptb_extra_' + id + '_lat'),
                $v = $place.val();
        if ($v) {
            $v = $.parseJSON($v);
            if ($v) {
                if ($v.location) {
					if(!$v.location['lat']) $v.location['lat'] = $v.location[0];
					if(!$v.location['lng']) $v.location['lng'] = $v.location[1];
                    marker.setPosition({'lat': $v.location['lat'], 'lng': $v.location['lng']});
                    map.setCenter({'lat': $v.location['lat'], 'lng': $v.location['lng']});
                    if ($.trim($info.val())) {
                        infowindow.setContent($.trim($info.val()));
                        infowindow.open(map, marker);
                    }
                    location.val($v.location['lat']+', '+$v.location['lng']);
                }
                else if ($v.place) {
                    var service = new google.maps.places.PlacesService(map);
                    service.getDetails({placeId: $v.place}, function (place, status) {
                        if (status === google.maps.places.PlacesServiceStatus.OK) {
                            $setplace = place;
                            google.maps.event.trigger(autocomplete, "place_changed", e);
                            if (place.formatted_address) {
                                $(input).val(place.formatted_address);
                            }
                            else {
                                $(input).val(place.name);
                            }
                        }
                    });
                }
            }
        }
        google.maps.event.addListener(marker, 'dragend', function() {
             location.val( marker.getPosition().lat()+', '+ marker.getPosition().lng());
			 $place.val(JSON.stringify({'location': { 'lat': marker.getPosition().lat(), 'lng': marker.getPosition().lng() }}));
        });
        google.maps.event.addListener(autocomplete, 'place_changed', function (geocode) {
            infowindow.close();
            marker.setVisible(false);
            var address = '';
            if (!$setplace) {
                var val = $.trim($(input).val());
                if(val!==''){
                    var place = !geocode?autocomplete.getPlace():false;
                    if (!place || !place.geometry) {
                        geocodeAddress(val,'address',function(place){
                            if (!place || !place.geometry) {
                                alert("Autocomplete's returned place contains no geometry");
                            }
                            else{
                                callback(map,marker,$info,place,$place,address,infowindow,location,$setplace);
                            }

                        });
                        return;
                    }
                }
                else{
                    callback(map,marker,$info,'',$place,address,infowindow,location,$setplace);
                    return;
                }
            }
            else {
                var place = $setplace;
                address = $.trim($info.val());
                $setplace = false;
            }
            callback(map,marker,$info,place,$place,address,infowindow,location,$setplace);
        });

        google.maps.event.addListener(marker, 'click', function () {
            infowindow.open(map, marker);
        });

        google.maps.event.addDomListener( input, 'keydown', e=> {
            e.keyCode === 13 && e.preventDefault();
        });

        location.on('change',function(){
            var v = $.trim($(this).val());
            if(v!==''){
                v = v.split(',');
                if(v[1]){
                    v ={lat: parseFloat($.trim(v[0])), lng: parseFloat($.trim(v[1]))};
                    geocodeAddress(v,'location',place=>{
                        if (!place || !place.geometry) {
                            alert("Autocomplete's returned place contains no geometry");
                        }
                        else{
                            $(input).val(place.formatted_address);
                            callback(map,marker,$info,place,$place,'',infowindow,location,false,v);
                        }

                    });
                }
            }
            else{
                callback(map,marker,$info,'',$place,'',infowindow,location,false);
            }
        });
        // Sets a listener on a radio button to change the filter type on Places
        // Autocomplete.
        $(types).change(function () {
            autocomplete.setTypes([$(this).val()]);
        });


        $info.keyup(function () {
            var $v = $.trim($(this).val()).replace(/(?:\r\n|\r|\n)/ig, '<br />');
            infowindow.setContent($v);
        });

    });

    function callback(map,marker,$info,place,$place,address,infowindow,location,$setplace,v){
        if(place){
            // If the place has a geometry, then present it on a map.
            if (place.geometry.viewport) {
                map.fitBounds(place.geometry.viewport);
            } else {
                map.setCenter(place.geometry.location);
                map.setZoom(17);  // Why 17? Because it looks good.
            }
             if(place.icon){
                marker.setIcon({
                    url: place.icon,
                    size: new google.maps.Size(71, 71),
                    origin: new google.maps.Point(0, 0),
                    anchor: new google.maps.Point(17, 34),
                    scaledSize: new google.maps.Size(35, 35)
                });
            }
             else{
                marker.setIcon();
            }


            marker.setPosition(v?v:place.geometry.location);
            marker.setVisible(true);

            if (!$setplace) {

                if (!$.trim($info.val())) {
                    if (place.address_components) {
                        address = [
                            (place.address_components[0] && place.address_components[0].short_name || ''),
                            (place.address_components[1] && place.address_components[1].short_name || ''),
                            (place.address_components[2] && place.address_components[2].short_name || '')
                        ].join(' ');
                    }
                    $info.val(address);
                    address = '<div><strong>' + place.name + '</strong><br>' + address;
                }
                else {
                    address = $.trim($info.val()).replace(/(?:\r\n|\r|\n)/ig, '<br />');
                }
            }

            infowindow.setContent(address);
            infowindow.open(map, marker);
            if(!v){
                location.val( place.geometry.location.lat()+', '+ place.geometry.location.lng());
            }
            $place.val(JSON.stringify({'place': place.place_id, 'location':v?v:place.geometry.location}));
        }
        else{
            $place.val('');
            location.val('');
            marker.setVisible(false);
        }
    }
}
($=>{
    'use strict';

    /* Custom Meta Box Map*/

    function loadScript(src, callback) {
        var script = document.createElement("script");
        script.type = "text/javascript";
        if (callback) script.onload = callback;
        document.getElementsByTagName("head")[0].appendChild(script);
        script.async = true;
        script.src = src;
    }
    $(document).on('ptb_add_metabox_map', function (e) {
        return {};
    });
	window.addEventListener('DOMContentLoaded', function(){
      if($('.ptb_post_cmb_item_map').length>0){
            if (typeof google !== 'object' || typeof google.maps !== 'object' || typeof google.maps.places === 'undefined') {
                if (typeof google === 'object' && google !== null && typeof google.maps === 'object' && typeof google.maps.places === 'undefined') {
                    google.maps = null;
                }
            loadScript('//maps.googleapis.com/maps/api/js?v=3&libraries=places&callback=ptb_extra_map_init&language=' + ptb.lng+'&key='+ptb.map_key);
            } else {
                ptb_extra_map_init();
            }
            
        }
	}, {once:true, passive:true});

})(jQuery);