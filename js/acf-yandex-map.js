(function ($) {

    /**
     * Initialize admin interface
     *
     * @param {element} $el
     * @returns {boolean}
     */
    function initialize_field($el) {

        /**
         * Element for map init
         * @type {element}
         */
        var $element;

        /**
         * Hidden data input
         * @type {element}
         */
        var $input;

        /**
         * Saved data or default
         *
         * @param {float} $params.center_lat
         * @param {float} $params.center_lng
         * @param {string} $params.type
         * @param {int} $params.zoom
         * @param {Object[]} $params.marks
         *
         * @type {object}
         */
        var $params;

        /**
         * Yandex map object
         *
         * @type {Object}
         */
        var $map;

        /// Init fields

        $element = $($el).find('.map');
        $input = ($el).find('.map-input');

        if ($element == undefined || $input == undefined) {
            console.error(acf_yandex_locale.map_init_fail);
            return false;
        }

        /// Init params

        $params = $.parseJSON($($input).val());

        /// Init map

        ymaps.ready(function () {

            $map = new ymaps.Map($element[0], {
                zoom: $params.zoom,
                center: [$params.center_lat, $params.center_lng],
                type: $params.type
            }, {
                minZoom: 10
            });

            $map.controls.remove('trafficControl');
            $map.controls.remove('fullscreenControl');
            $map.copyrights.add('&copy; Const Lab. ');

            $map.events.add('click', function (e) {
                create_mark(e.get('coords'));
            });

            $map.events.add('typechange', function (e) {
                save_map();
            });

            $map.events.add('boundschange', function () {
                save_map();
            });

            /// Search Control

            var search_controll = $map.controls.get('searchControl');
            search_controll.options.set({
                noPlacemark: true,
                useMapBounds: false,
                noSelect: true,
                kind: 'locality',
                width: 250
            });

            search_controll.events.add('resultselect', function () {
                $map.geoObjects.removeAll();
                save_map();
            });

            /// Geo location button

            var geo_control = $map.controls.get('geolocationControl');
            geo_control.events.add('locationchange', function () {
                $map.geoObjects.removeAll();
                save_map();
            });

            /// Zoom Control

            var zoom_control = new ymaps.control.ZoomControl();
            zoom_control.events.add('zoomchange', function (event) {
                save_map();
            });

            $map.controls.add(zoom_control, {top: 75, left: 5});

            /// Clear all button

            var clear_button = new ymaps.control.Button({
                data: {
                    content: acf_yandex_locale.btn_clear_all,
                    title: acf_yandex_locale.btn_clear_all_hint
                },
                options: {
                    selectOnClick: false
                }
            });

            clear_button.events.add('click', function () {
                $map.geoObjects.removeAll();
                save_map();
            });

            $map.controls.add(clear_button, {top: 5, right: 5});

            /// Marks load

            $($params.marks).each(function (index, mark) {
                create_mark(mark.coords, mark.type, mark.circle_size);
            });

        });

        /**
         * Create geo mark
         *
         * @param {Array} coords
         * @param {string} type Point type, Point or Circle
         * @param {int} size Circle size in meters
         */
        function create_mark(coords, type, size) {

            var place_mark = null;
            var marker_type = (type != null) ? type.toLowerCase() : $($el).find('.marker-type').val();

            if (marker_type == 'point') { // create placemark

                place_mark = new ymaps.Placemark(
                    coords,
                    {
                        //balloonContent: 'test',
                        hintContent: acf_yandex_locale.mark_hint
                    }, {
                        draggable: true
                    }
                );

            } else { // if mark is circle

                var circle_size = (size != null) ? size : (parseInt($($el).find('.circle-size').val()) / 2);

                place_mark = new ymaps.Circle([
                    coords,
                    circle_size
                ], {
                    hintContent: acf_yandex_locale.mark_hint
                }, {
                    draggable: true,
                    opacity: 0.5,
                    fillOpacity: 0.1,
                    fillColor: "#DB709377",
                    strokeColor: "#990066",
                    strokeOpacity: 0.7,
                    strokeWidth: 5
                });

            }

            place_mark.events.add('contextmenu', function () {
                $map.geoObjects.remove(this);
                save_map();
            }, place_mark);

            place_mark.events.add('dragend', function () {
                save_map();
            });

            $map.geoObjects.add(place_mark);

            save_map();
        }

        /**
         * Write map data in hidden field
         */
        function save_map() {

            $params.zoom = $map.getZoom();

            var coords = $map.getCenter();
            $params.center_lat = coords[0];
            $params.center_lng = coords[1];

            var type = $map.getType().split('#');
            $params.type = (type[1]) ? type[1] : 'map';

            var marks = [];
            $map.geoObjects.each(function (mark) {
                var _type = mark.geometry.getType();
                marks.push({
                    type: _type,
                    coords: mark.geometry.getCoordinates(),
                    circle_size: (_type == 'Circle') ? mark.geometry.getRadius() : 0
                });
            });
            $params.marks = marks;

            $($input).val(JSON.stringify($params));
        }


    }

    $('.marker-type').on('change', function () {
        var label = $(this).parent().next('th').children(0);
        var select = $(this).parent().next('th').next('td').children(0);
        if (this.value == 'circle') {
            label.removeClass('hidden');
            select.removeClass('hidden');
        } else {
            label.addClass('hidden');
            select.addClass('hidden');
        }
    });


    if (typeof acf.add_action !== 'undefined') {

        /*
         *  ready append (ACF5)
         *
         *  These are 2 events which are fired during the page load
         *  ready = on page load similar to $(document).ready()
         *  append = on new DOM elements appended via repeater field
         *
         *  @type	event
         *  @date	20/07/13
         *
         *  @param	$el (jQuery selection) the jQuery element which contains the ACF fields
         *  @return	n/a
         */

        acf.add_action('ready append', function ($el) {

            // search $el for fields of type 'FIELD_NAME'
            acf.get_fields({type: 'yandex-map'}, $el).each(function () {

                initialize_field($(this));

            });

        });


    } else {


        /*
         *  acf/setup_fields (ACF4)
         *
         *  This event is triggered when ACF adds any new elements to the DOM.
         *
         *  @type	function
         *  @since	1.0.0
         *  @date	01/01/12
         *
         *  @param	event		e: an event object. This can be ignored
         *  @param	Element		postbox: An element which contains the new HTML
         *
         *  @return	n/a
         */

        $(document).on('acf/setup_fields', function (e, postbox) {

            $(postbox).find('.field[data-field_type="yandex-map"]').each(function () {

                initialize_field($(this));

            });

        });


    }


})(jQuery);