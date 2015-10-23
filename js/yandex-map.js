(function ($) {

    ymaps.ready(function () {

        if (maps !== undefined) {

            $(maps).each(function (index, value) {

                var $params = $.parseJSON(value['params']);

                var $map = new ymaps.Map(value['map_id'], {
                    zoom: $params.zoom,
                    center: [$params.center_lat, $params.center_lng],
                    type: 'yandex#' + $params.type,
                    behaviors: ['dblClickZoom', 'multiTouch', 'drag']
                }, {
                    minZoom: 10
                });

                $map.controls.remove('trafficControl');
                $map.controls.remove('searchControl');
                $map.controls.remove('geolocationControl');

                $($params.marks).each(function (index, mark) {

                    var place_mark = null;

                    if (mark.type == 'Point') { // create placemark

                        place_mark = new ymaps.Placemark(mark.coords, {
                            balloonContent: mark.content
                        });

                    } else { // if mark is circle

                        place_mark = new ymaps.Circle([
                            mark.coords,
                            mark.circle_size
                        ], {
                            balloonContent: mark.content
                        }, {
                            opacity: 0.5,
                            fillOpacity: 0.1,
                            fillColor: "#DB709377",
                            strokeColor: "#990066",
                            strokeOpacity: 0.7,
                            strokeWidth: 5
                        });

                    }


                    $map.geoObjects.add(place_mark);

                });

            });

        }

    });

})(jQuery);