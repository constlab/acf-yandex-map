<?php

class acf_field_yandex_map extends acf_field {


	/*
	*  __construct
	*
	*  This function will setup the field type data
	*
	*  @type	function
	*  @date	5/03/2014
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/

	function __construct() {

		/*
		*  name (string) Single word, no spaces. Underscores allowed
		*/

		$this->name = 'yandex-map';


		/*
		*  label (string) Multiple words, can include spaces, visible when selecting a field type
		*/

		$this->label = __( 'Yandex Map', YA_MAP_LANG_DOMAIN );


		/*
		*  category (string) basic | content | choice | relational | jquery | layout | CUSTOM GROUP NAME
		*/

		$this->category = 'jquery';


		/*
		*  defaults (array) Array of default settings which are merged into the field object. These are used later in settings
		*/

		$this->defaults = array(
			'height'     => '400',
			'center_lat' => '55.7522200',
			'center_lng' => '37.6155600',
			'zoom'       => '14',
			'map_type'   => 'map'
		);


		/*
		*  l10n (array) Array of strings that are used in JavaScript. This allows JS strings to be translated in PHP and loaded via:
		*  var message = acf._e('FIELD_NAME', 'error');
		*/

		$this->l10n = array(//'error' => __( 'Error! Please enter a higher value', YA_MAP_LANG_DOMAIN ),
		);


		// do not delete!
		parent::__construct();

	}

	/*
	*  render_field_settings()
	*
	*  Create extra settings for your field. These are visible when editing a field
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field (array) the $field being edited
	*  @return	n/a
	*/

	function render_field_settings( $field ) {

		/*
		*  acf_render_field_setting
		*
		*  This function will create a setting for your field. Simply pass the $field parameter and an array of field settings.
		*  The array of settings does not require a `value` or `prefix`; These settings are found from the $field array.
		*
		*  More than one setting can be added by copy/paste the above code.
		*  Please note that you must also have a matching $defaults value for the field name (font_size)
		*/

		acf_render_field_setting( $field, array(
			'label'        => __( 'Height', YA_MAP_LANG_DOMAIN ),
			'instructions' => __( 'Set map height', YA_MAP_LANG_DOMAIN ),
			'type'         => 'number',
			'name'         => 'height',
			'append'       => 'px'
		) );

		acf_render_field_setting( $field, array(
			'label'       => __( 'Map type', YA_MAP_LANG_DOMAIN ),
			'type'        => 'select',
			'name'        => 'map_type',
			'placeholder' => $this->defaults['map_type'],
			'choices'     => array(
				'map'       => __( 'Map', YA_MAP_LANG_DOMAIN ),
				'satellite' => __( 'Satellite', YA_MAP_LANG_DOMAIN ),
				'hybrid'    => __( 'Hybrid', YA_MAP_LANG_DOMAIN ),
			)
		) );

		acf_render_field_setting( $field, array(
			'label'        => __( 'Zoom', YA_MAP_LANG_DOMAIN ),
			'instructions' => __( 'Set map zoom', YA_MAP_LANG_DOMAIN ),
			'type'         => 'number',
			'name'         => 'zoom',
			'min'          => '10',
			'max'          => '18'
		) );

		// center_lat
		acf_render_field_setting( $field, array(
			'label'        => __( 'Center', YA_MAP_LANG_DOMAIN ),
			'instructions' => __( 'Center the initial map', YA_MAP_LANG_DOMAIN ),
			'type'         => 'text',
			'name'         => 'center_lat',
			'prepend'      => 'lat',
			'placeholder'  => $this->defaults['center_lat']
		) );


		// center_lng
		acf_render_field_setting( $field, array(
			'label'        => __( 'Center', YA_MAP_LANG_DOMAIN ),
			'instructions' => __( 'Center the initial map', YA_MAP_LANG_DOMAIN ),
			'type'         => 'text',
			'name'         => 'center_lng',
			'prepend'      => 'lng',
			'placeholder'  => $this->defaults['center_lng'],
			'wrapper'      => array(
				'data-append' => 'center_lat'
			)
		) );
	}


	/*
	*  render_field()
	*
	*  Create the HTML interface for your field
	*
	*  @param	$field (array) the $field being rendered
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field (array) the $field being edited
	*  @return	n/a
	*/

	function render_field( $field ) {


		/*
		*  Review the data of $field.
		*  This will show what data is available
		*/

		wp_enqueue_script( 'acf-yandex' );

		$saved = json_decode( $field['value'], true );

		$data               = array();
		$data['center_lat'] = ( $saved['center_lat'] ) ?: $field['center_lat'];
		$data['center_lng'] = ( $saved['center_lng'] ) ?: $field['center_lng'];
		$data['zoom']       = ( $saved['zoom'] ) ?: $field['zoom'];
		$data['type']       = $this->get_map_type( $saved['type'], $field );
		$data['marks']      = ( $saved['marks'] ) ?: array();

		//print_r( $data );
		?>
		<input type="hidden" name="<?php echo esc_attr( $field['name'] ) ?>"
		       value="<?php echo esc_attr( wp_json_encode( $data ) ) ?>"
		       class="map-input"/>
		<table class="form-table">
			<tr>
				<th style="width: 20%"><?php echo __( 'Marker type', YA_MAP_LANG_DOMAIN ) ?></th>
				<td style="width: 30%">
					<select class="marker-type">
						<option value="point" selected><?php echo __( 'Point', YA_MAP_LANG_DOMAIN ) ?></option>
						<option value="circle"><?php echo __( 'Circle', YA_MAP_LANG_DOMAIN ) ?></option>
					</select>
				</td>
				<th><span class="circle hidden"><?php echo __( 'Circle radius', YA_MAP_LANG_DOMAIN ) ?></span>&nbsp;
				</th>
				<td>
					<?php
					$m_str  = __( 'm.', YA_MAP_LANG_DOMAIN );
					$km_str = __( 'km.', YA_MAP_LANG_DOMAIN );
					?>
					<select class="circle-size circle hidden">
						<option value="250" selected>250<?php echo $m_str ?></option>
						<option value="500">500<?php echo $m_str ?></option>
						<option value="1000">1<?php echo $km_str ?></option>
						<option value="4000">4<?php echo $km_str ?></option>
						<option value="10000">10<?php echo $km_str ?></option>
					</select>
				</td>
			</tr>
		</table>
		<div class="map"
		     style="width: auto;height:<?php echo ( esc_attr( $field['height'] ) ) ?: $this->defaults['height'] ?>px"></div>


	<?php
	}

	function input_admin_enqueue_scripts() {

		$dir = plugin_dir_url( __FILE__ );

		wp_register_script( 'yandex-map-api', '//api-maps.yandex.ru/2.1/?lang=' . get_bloginfo( 'language' ), array( 'jquery' ), null );
		wp_register_script( 'acf-yandex', "{$dir}js/acf-yandex-map.min.js", array( 'yandex-map-api' ), null, true );

		wp_localize_script( 'acf-yandex', 'acf_yandex_locale', array(
			'map_init_fail'      => __( 'Error init Yandex map! Field not found.', YA_MAP_LANG_DOMAIN ),
			'mark_hint'          => __( 'Drag mark. Right click for remove', YA_MAP_LANG_DOMAIN ),
			'btn_clear_all'      => __( 'Clear all', YA_MAP_LANG_DOMAIN ),
			'btn_clear_all_hint' => __( 'Remove all marks', YA_MAP_LANG_DOMAIN ),
			'btn_import'         => __( 'Import map from json', YA_MAP_LANG_DOMAIN ),
			'import_go'          => __( 'Import', YA_MAP_LANG_DOMAIN ),
			'import_desc'        => __( 'Paste exported json', YA_MAP_LANG_DOMAIN ),
			'btn_export'         => __( 'Export map to json string', YA_MAP_LANG_DOMAIN ),
			'export_desc'        => __( 'Copy it', YA_MAP_LANG_DOMAIN ),
			'mark_save'          => __( 'Save', YA_MAP_LANG_DOMAIN ),
			'mark_remove'        => __( 'Remove', YA_MAP_LANG_DOMAIN ),
		) );
	}

	/**
	 * Validate and return map type
	 *
	 * @param string $value
	 * @param array $field
	 *
	 * @return string
	 */
	private function get_map_type( $value, $field ) {

		$allowed = array(
			'map',
			'satellite',
			'hybrid'
		);

		$result = ( in_array( trim( $value ), $allowed ) ) ? $value : $field['map_type'];

		if ( ! $result ) {
			$result = $this->defaults['map_type'];
		}

		return $result;

	}


}

new acf_field_yandex_map();