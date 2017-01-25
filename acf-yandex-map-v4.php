<?php

// exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'acf_field_yandex_map' ) ):

	class acf_field_yandex_map extends acf_field {

		public $settings,
			$defaults;

		/*
		*  __construct
		*
		*  Set name / label needed for actions / filters
		*
		*  @since	3.6
		*  @date	23/01/13
		*/

		public function __construct( $settings ) {
			$this->name     = 'yandex-map';
			$this->label    = __( 'Yandex Map', YA_MAP_LANG_DOMAIN );
			$this->category = __( 'jQuery', 'acf' ); // Basic, Content, Choice, etc
			$this->defaults = array(
				'height'     => '400',
				'center_lat' => '55.7522200',
				'center_lng' => '37.6155600',
				'zoom'       => '14',
				'map_type'   => 'map'
			);

			// do not delete!
			parent::__construct();

			// settings
			$this->settings = $settings;
		}

		/*
		*  create_options()
		*
		*  Create extra options for your field. This is rendered when editing a field.
		*  The value of $field['name'] can be used (like below) to save extra data to the $field
		*
		*  @type	action
		*  @since	3.6
		*  @date	23/01/13
		*
		*  @param	$field	- an array holding all the field's data
		*/

		public function create_options( $field ) {
			// defaults?
			/*
			$field = array_merge($this->defaults, $field);
			*/

			// key is needed in the field names to correctly save the data
			$key = $field['name'];


			// Create Field Options HTML
			?>
            <tr class="field_option field_option_<?php echo $this->name; ?>">
                <td class="label">
                    <label><?php echo __( 'Height', YA_MAP_LANG_DOMAIN ) ?></label>
                    <p class="description"><?php echo __( 'Set map height', YA_MAP_LANG_DOMAIN ) ?></p>
                </td>
                <td>
					<?php

					do_action( 'acf/create_field', array(
						'type'   => 'number',
						'name'   => 'fields[' . $key . '][height]',
						'value'  => $field['height'],
						'layout' => 'horizontal',
						'append' => 'px'
					) );

					?>
                </td>
            </tr>
            <tr class="field_option field_option_<?php echo $this->name; ?>">
                <td class="label">
                    <label><?php echo __( 'Map type', YA_MAP_LANG_DOMAIN ) ?></label>
                </td>
                <td>
					<?php

					do_action( 'acf/create_field', array(
						'type'    => 'select',
						'name'    => 'fields[' . $key . '][map_type]',
						'value'   => $field['map_type'],
						'layout'  => 'horizontal',
						'choices' => array(
							'map'       => __( 'Map', YA_MAP_LANG_DOMAIN ),
							'satellite' => __( 'Satellite', YA_MAP_LANG_DOMAIN ),
							'hybrid'    => __( 'Hybrid', YA_MAP_LANG_DOMAIN ),
						)
					) );

					?>
                </td>
            </tr>
            <tr class="field_option field_option_<?php echo $this->name; ?>">
                <td class="label">
                    <label><?php echo __( 'Zoom', YA_MAP_LANG_DOMAIN ) ?></label>
                    <p class="description"><?php echo __( 'Set map zoom', YA_MAP_LANG_DOMAIN ) ?></p>
                </td>
                <td>
					<?php

					do_action( 'acf/create_field', array(
						'type'   => 'number',
						'name'   => 'fields[' . $key . '][zoom]',
						'value'  => $field['zoom'],
						'layout' => 'horizontal',
						'min'    => '10',
						'max'    => '18'
					) );

					?>
                </td>
            </tr>
            <tr class="field_option field_option_<?php echo $this->name; ?>">
                <td class="label">
                    <label><?php echo __( 'Center', YA_MAP_LANG_DOMAIN ) ?></label>
                    <p class="description"><?php echo __( 'Center the initial map', YA_MAP_LANG_DOMAIN ) ?></p>
                </td>
                <td>
					<?php

					do_action( 'acf/create_field', array(
						'type'        => 'text',
						'name'        => 'fields[' . $key . '][center_lat]',
						'value'       => $field['center_lat'],
						'layout'      => 'horizontal',
						'prepend'     => 'lat',
						'placeholder' => $this->defaults['center_lat']
					) );

					?>
                </td>
            </tr>
            <tr class="field_option field_option_<?php echo $this->name; ?>">
                <td class="label">
                    <label><?php echo __( 'Center', YA_MAP_LANG_DOMAIN ) ?></label>
                    <p class="description"><?php echo __( 'Center the initial map', YA_MAP_LANG_DOMAIN ) ?></p>
                </td>
                <td>
					<?php

					do_action( 'acf/create_field', array(
						'type'        => 'text',
						'name'        => 'fields[' . $key . '][center_lng]',
						'value'       => $field['center_lng'],
						'layout'      => 'horizontal',
						'prepend'     => 'lng',
						'placeholder' => $this->defaults['center_lng']
					) );

					?>
                </td>
            </tr>
			<?php
		}

		/*
		*  create_field()
		*
		*  Create the HTML interface for your field
		*
		*  @param	$field - an array holding all the field's data
		*
		*  @type	action
		*  @since	3.6
		*  @date	23/01/13
		*/

		public function create_field( $field ) {

			wp_enqueue_script( 'acf-yandex' );

			$saved = json_decode( $field['value'], true );

			$data               = array();
			$data['center_lat'] = ( $saved['center_lat'] ) ?: $field['center_lat'];
			$data['center_lng'] = ( $saved['center_lng'] ) ?: $field['center_lng'];
			$data['zoom']       = ( $saved['zoom'] ) ?: $field['zoom'];
			$data['type']       = $this->get_map_type( $saved['type'], $field );
			$data['marks']      = ( $saved['marks'] ) ?: array();

			?>
            <div>
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
            </div>
			<?php
		}

		/*
		*  input_admin_enqueue_scripts()
		*
		*  This action is called in the admin_enqueue_scripts action on the edit screen where your field is created.
		*  Use this action to add CSS + JavaScript to assist your create_field() action.
		*
		*  $info	http://codex.wordpress.org/Plugin_API/Action_Reference/admin_enqueue_scripts
		*  @type	action
		*  @since	3.6
		*  @date	23/01/13
		*/
		public function input_admin_enqueue_scripts() {
			// Note: This function can be removed if not used


			// vars
			$url     = $this->settings['url'];
			$version = $this->settings['version'];

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

			$result = in_array( trim( $value ), $allowed, true ) ? $value : $field['map_type'];

			if ( ! $result ) {
				$result = $this->defaults['map_type'];
			}

			return $result;

		}

	}

	new acf_field_yandex_map( [] );

endif;