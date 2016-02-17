<?php

/*
Plugin Name: Yandex Map Field for ACF
Plugin URI: https://github.com/constlab/acf-yandex-map
Description: Editing map on page, add geopoints and circles
Version: 1.2.2
Author: Const Lab
Author URI: https://constlab.ru
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

defined( 'YA_MAP_LANG_DOMAIN' ) or define( 'YA_MAP_LANG_DOMAIN', 'acf-yandex-map' );
defined( 'ACF_YA_MAP_VERSION' ) or define( 'ACF_YA_MAP_VERSION', '1.2.0' );

load_plugin_textdomain( YA_MAP_LANG_DOMAIN, false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );

function include_field_types_yandex_map( $version ) {
	include_once( __DIR__ . '/acf-yandex-map-v5.php' );
}

add_action( 'acf/include_field_types', 'include_field_types_yandex_map' );


/// Function for frontend

if ( ! function_exists( 'the_yandex_map' ) ) {

	/**
	 * @param string $selector
	 * @param int|bool $post_id
	 * @param null $data
	 */
	function the_yandex_map( $selector, $post_id = false, $data = null ) {

		$post_id = acf_get_valid_post_id( $post_id );
		$value   = ( $data !== null ) ? $data : get_field( $selector, $post_id, false );

		if ( ! $value ) {
			return;
		}

		$dir = plugin_dir_url( __FILE__ );
		wp_register_script( 'yandex-map-api', '//api-maps.yandex.ru/2.1/?lang=' . get_bloginfo( 'language' ), array( 'jquery' ), null );
		wp_register_script( 'yandex-map-frontend', "{$dir}js/yandex-map.min.js", array( 'yandex-map-api' ), ACF_YA_MAP_VERSION );
		wp_enqueue_script( 'yandex-map-frontend' );

		$map_id = uniqid( 'map-' );

		wp_localize_script( 'yandex-map-frontend', 'maps', array(
			array(
				'map_id' => $map_id,
				'params' => $value
			)
		) );

		/**
		 * Filter the map height for frontend.
		 *
		 * @since 1.2.0
		 *
		 * @param string $selector Field name
		 * @param int $post_id Current page id
		 * @param array $value Map field value
		 */
		$field        = get_field_object( $selector, $post_id );
		$field_height = ( $field ) ? $field['height'] : 200;
		$height_map   = apply_filters( 'acf-yandex-map/height', $field_height, $selector, $post_id, $value );

		echo sprintf( '<div class="yandex-map" id="%s" style="width:auto;height:%dpx"></div>', $map_id, $height_map );
	}

}