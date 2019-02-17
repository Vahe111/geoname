$(document).ready(function() {
    $('#city_name').keyup(function () {
        var query = $(this).val();
        if(query != '') {
            var _token = $('input[name="_token"]').val();
        } else {
            return;
        }
        $.ajax({
            url:"/search",
            method: "POST",
            data:{query:query, _token:_token},
            success:function(data) {
                if(data.status == 'error') {
                    $('#errors').text('City name is not valid');
                    return;
                } else {
                    $('#errors').text('');
                }
                var autocompleteData = '<div class="dropdown-menu" style="display: block; position: relative;">';
                for (i = 0; i < data.length; i++) {
                    autocompleteData += '<button class="dropdown-item" value="'+ data[i].geonameid +'">' + data[i].asciiname + '</button>';
                }
                autocompleteData += '</div>';
                $('#cityList').fadeIn();
                $('#cityList').html(autocompleteData);
            }
        })
    });

    $(document).on('click', 'button', function () {

        $('#city_name').val($(this).text());
        $('#cityList').fadeOut();
        var query =  $(this).val();
        var _token = $('input[name="_token"]').val();
        $.ajax({
            url:"/search-one",
            method: "POST",
            data:{query:query,_token:_token},
            success:function(data) {
                if(data.status == 'error') {
                    $('#errors').text('City ID is not valid');
                    return;
                } else {
                    $('#errors').text('');
                }
                console.log(data)
                var map = new google.maps.Map(document.getElementById('map'), {
                    center: {lat: data.cities[0].latitude, lng: data.cities[0].longitude},
                    zoom: 14
                });
                var infowindow = new google.maps.InfoWindow();

                var marker, i;
                for (i = 0; i < data.cities.length; i++) {
                    marker = new google.maps.Marker({
                        position: new google.maps.LatLng(data.cities[i].latitude, data.cities[i].longitude),
                        map: map
                    });
                    google.maps.event.addListener(marker, 'click', (function(marker, i) {
                        return function() {
                            infowindow.setContent(data.cities[i].asciiname);
                            infowindow.open(map, marker);
                        }
                    })(marker, i));
                }
            }
        })
    })
});