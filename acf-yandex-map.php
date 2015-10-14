<?php

/*
Plugin Name: Yandex Map Field for Advanced Custom Fields
Plugin URI: https://github.com/constlab/acf-yandex-map
Description: SHORT_DESCRIPTION
Version: 1.0.0
Author: Const Lab
Author URI: https://constlab.ru
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

defined( 'YA_MAP_LANG_DOMAIN' ) or define( 'YA_MAP_LANG_DOMAIN', 'acf-yandex-map' );

load_plugin_textdomain( YA_MAP_LANG_DOMAIN, false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );

function include_field_types_yandex_map( $version ) {
	include_once( __DIR__ . '/acf-yandex-map-v5.php' );
}

add_action( 'acf/include_field_types', 'include_field_types_yandex_map' );


/// Format field rendering on frontend

function yandex_map_render( $value, $post_id, $field ) {

	wp_register_script( 'yandex-map-api', '//api-maps.yandex.ru/2.1/?lang=' . get_bloginfo( 'language' ), array( 'jquery' ), null );
	wp_enqueue_script( 'yandex-map-api' );

	$saved = json_decode( $value, true );

	$data = array();

	$result = '<div class="yandex-map"></div>';


	print_r( $field );
}

add_filter( 'acf/format_value/type=yandex-map', 'yandex_map_render', 10, 3 );