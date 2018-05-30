<?php
/**
 * Extends WPV_WPML_Shortcodes_Translation in order to allow Maps submodule to use shortcode translation goodies.
 * @todo This is a stop-gap solution. Refactor to use filter on get_fake_shortcodes_array() instead of extending.
 * @since 1.4
 */
class WPV_Maps_WPML_Shortcodes_Translation extends WPV_WPML_Shortcodes_Translation
{
	/**
	 * Returns the array with the fake shortodes.
	 *
	 * @return array The array with the fake shortcodes.
	 */
	public function get_fake_shortcodes_array() {
		return array (
			'wpv-control-distance' => array( $this, 'fake_wpv_control_distance_shortcode_to_wpml_register_string')
		);
	}

	/**
	 * Register wpv-control-distance shortcode attributes for translation.
	 *
	 * @param array $atts The shotcode attributes
	 */
	public function fake_wpv_control_distance_shortcode_to_wpml_register_string( array $atts ) {
		if ( ! $this->is_wpml_active_and_configured() ) {
			return;
		}

		$url_param = isset( $atts['url_param'] ) ? $atts['url_param'] : '';

		if ( empty( $url_param ) ) {
			return;
		}

		$attributes_to_translate = array( 'radius_label', 'location_label', 'visitor_location_button_text' );

		foreach ( $attributes_to_translate as $att_to_translate ) {
			if ( isset( $atts[ $att_to_translate ] ) ) {
				do_action(
					'wpml_register_single_string',
					$this->_context,
					$url_param . '_' . $att_to_translate,
					$atts[ $att_to_translate ]
				);
			}
		}
	}
}

$WPV_Maps_WPML_Shortcodes_Translation = new WPV_Maps_WPML_Shortcodes_Translation();