<?php

/**
 * Toolset Maps - Views Distance Filter
 *
 * @package ToolsetMaps
 *
 * @since 1.4
 */
class Toolset_Addon_Maps_Views_Distance_Filter {

	const DISTANCE_CENTER_DEFAULT_URL_PARAM = 'toolset_maps_distance_center';
	const DISTANCE_RADIUS_DEFAULT_URL_PARAM = 'toolset_maps_distance_radius';
	const DISTANCE_UNIT_DEFAULT_URL_PARAM = 'toolset_maps_distance_unit';

	const DISTANCE_CENTER_USER_COORDS_PARAM = 'toolset_maps_visitor_coords';

	const DISTANCE_FILTER_INPUTS_PLACEHOLDER = 'Show results within %%DISTANCE%% of %%CENTER%%';
    const DISTANCE_FILTER_VISITOR_LOCATION_BUTTON_TEXT = 'Use my location';

	public $frontend_js = array(
        'use_user_location' => false
    );
	public $use_frontend_script = false;

	static $distance_filter_options = array(
		'map_distance'                            => 5,
		'map_distance_center'                     => '',
		'map_distance_unit'                       => 'km',
		'map_center_lat'                          => '',
		'map_center_lng'                          => '',
		'map_center_source'                       => 'address',
		'map_distance_center_url_param_name'      => 'mapcenter',
		'map_distance_radius_url_param_name'      => '',
		'map_distance_unit_url_param_name'        => '',
		'map_distance_center_shortcode_attr_name' => 'mapcenter',
		'map_distance_compare_field'              => null
	);

	function __construct() {
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action( 'init', array( $this, 'init' ) );

		if ( is_admin() ) {
			//Add Views query filter and frontend filter
			add_filter( 'wpv_filters_add_filter', array( $this, 'toolset_maps_add_distance_views_filter' ), 1, 1 );
			//action for listing distance query filter
			add_action( 'wpv_add_filter_list_item', array(
				$this,
				'toolset_maps_distance_views_filter_list_items'
			), 1, 1 );
			//updating deleting distance query filter
			add_action( 'wp_ajax_toolset_maps_distance_views_filter_update', array(
				$this,
				'toolset_maps_distance_views_filter_update_callback'
			) );
			add_action( 'wp_ajax_wpv_filter_maps_distance_delete', array(
				$this,
				'toolset_maps_distance_views_filter_delete_callback'
			) );
			add_filter( 'wpv_filter_wpv_shortcodes_gui_data', array(
				$this,
				'toolset_maps_filter_distance_register_gui_data'
			) );
		}

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_script' ) );

		// We have multiple different stuff happening on wpv_filter_query
		add_filter( 'wpv_filter_query', array( $this, 'check_if_query_needed' ), 10, 3 );
		add_filter( 'wpv_filter_query', array( $this, 'make_paging_post_process_aware' ), 20, 3 );

		// Add action to filter the loop items with distance  before they're rendered
		add_filter( 'wpv_filter_query_post_process', array(
			$this,
			'toolset_maps_distance_views_filter_apply_rules'
		), 10, 3 );

		// Register distance search filter
		add_shortcode( 'wpv-control-distance', array(
			$this,
			'toolset_maps_shortcode_wpv_control_post_distance'
		) );
		add_filter( 'wpv_filter_wpv_register_form_filters_shortcodes', array(
			$this,
			'toolset_maps_distance_custom_search_filter'
		), 5 );

		add_action( 'toolset_maps_distance_use_frontend_script', array( $this, 'add_distance_filter_settings' ) );

		add_action( 'after_setup_theme', array( $this, 'load_translation_class' ) );

		add_filter( 'wpv_filter_register_shortcode_attributes_for_posts', array(
			$this,
			'shortcode_attributes'
		), 10, 2 );
		add_filter( 'wpv_filter_register_url_parameters_for_posts', array( $this, 'register_url_parameters' ), 10, 2 );
	}

	/**
	 * Register the filter to get URL parameters
     *
	 * @param array $attributes
	 * @param array $view_settings
	 *
	 * @return array
	 */
	public function register_url_parameters( array $attributes, array $view_settings ) {
		if (
			isset( $view_settings['map_distance_filter'] )
			&& isset( $view_settings['map_distance_filter']['map_center_source'] )
			&& $view_settings['map_distance_filter']['map_center_source'] == 'url_param'
		) {
			$fields = array(
				self::DISTANCE_CENTER_DEFAULT_URL_PARAM => $view_settings['map_distance_filter']['map_distance_center'],
                self::DISTANCE_RADIUS_DEFAULT_URL_PARAM => $view_settings['map_distance_filter']['map_distance'],
                self::DISTANCE_UNIT_DEFAULT_URL_PARAM => $view_settings['map_distance_filter']['map_distance_unit']
            );
			foreach ( $fields as $attribute => $value ) {
				$attributes[] = array(
					'query_type'   => $view_settings['query_type'][0],
					'filter_type'  => $attribute, // Filter type must be unique, because it's used as array key later on
					'filter_label' => '',
					'value'        => $value,
					'attribute'    => $attribute,
					'expected'     => 'string',
					'placeholder'  => '',
					'description'  => ''
				);
			}
		}
		return $attributes;
	}

	/**
	 * Requires translation class (late enough, so Views autoloader kicks in and the class it depends on is available).
	 */
	public function load_translation_class() {
		require_once 'wpv-maps-wpml-shortcodes-translation.php';
	}

	/**
     * Checks if query has a WP hack implemented to be skipped
     *
	 * @param array $query
	 *
	 * @return bool
	 */
	protected function is_query_skipped( array $query ) {
        return (
			isset($query['post__in'])
			&& isset($query['post__in'][0])
			&& $query['post__in'][0] === 0
        );
	}

	/**
     * When user location is center and not yet available, skip query. We'll get location on second load.
     *
	 * @param array $query
	 * @param array $view_settings
	 * @param int $id
     *
	 * @return array
	 */
	public function check_if_query_needed( array $query, array $view_settings, $id ) {
	    // If this is not a distance filter query, do nothing.
	    if ( !isset( $view_settings['map_distance_filter'] ) ) return $query;

        $map_distance_filter = $view_settings['map_distance_filter'];
		$distance_filter_options = array();

		if ( isset( $_GET[ self::DISTANCE_CENTER_USER_COORDS_PARAM ] ) ) {
			$coords_array = self::get_coords_array_from_input( $_GET[ self::DISTANCE_CENTER_USER_COORDS_PARAM ] );
			if ( self::is_valid_coords_array( $coords_array ) ) {
				$distance_filter_options['map_center_lat'] = $coords_array['lat'];
				$distance_filter_options['map_center_lng'] = $coords_array['lon'];
			}
		}

		if (
            empty( $distance_filter_options['map_center_lat'] )
            && empty( $distance_filter_options['map_center_lng'] )
			&& $map_distance_filter['map_center_source'] === 'user_location'
        ) {
            $query['post__in'] = array(0); // WP hack to skip querying
		}

		return $query;
	}

	/**
     * Remove paging from query, as we need the whole result set. Postprocess will add paging again.
     *
	 * @param array $query
	 * @param array $view_settings
	 * @param int $id
	 *
	 * @return array
	 */
	public function make_paging_post_process_aware( array $query, array $view_settings, $id ) {
	    // Nothing to do if query is skipped, pagination disabled, or we are not in map distance filter
	    if (
            $this->is_query_skipped( $query )
            || $view_settings['pagination']['type'] === 'disabled'
            || !isset( $view_settings['map_distance_filter'] )
        ) {
	        return $query;
		}

        $query['posts_per_page'] = -1;

		return $query;
	}

	static function shortcode_attributes( $attributes, $view_settings ) {
		if (
			isset( $view_settings['map_distance_filter'] )
			&& isset( $view_settings['map_distance_filter']['map_center_source'] )
			&& $view_settings['map_distance_filter']['map_center_source'] == 'shortcode_attr'
		) {
			$distance_filter_settings = $view_settings['map_distance_filter'];
			$attributes[]             = array(
				'query_type'   => $view_settings['query_type'][0],
				'filter_type'  => 'maps_distance',
				'filter_label' => __( 'Map Distance Center', 'wpv-views' ),
				'value'        => '',
				'attribute'    => $distance_filter_settings['map_distance_center_shortcode_attr_name'],
				'expected'     => 'string',
				'placeholder'  => '30.013056, 31.208853',
				'description'  => __( 'Please type a comma separated latitude, longitude', 'wpv-views' )
			);
		}

		return $attributes;
	}

	function admin_init() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ), 20 );
	}

	function init() {
	    $this->frontend_js['geolocation_error'] = __(
            'Cannot do this search without user location. Error: ',
            'toolset-maps'
        );

		$this->register_assets();
	}

	function register_assets() {
		wp_register_script(
			'toolset-maps-views-filter-distance-backend-js',
			TOOLSET_ADDON_MAPS_URL . "/resources/js/views_filter_distance.js",
			array( 'jquery-geocomplete', 'toolset-google-map-editor-script' ),
			TOOLSET_ADDON_MAPS_VERSION,
			false
		);

		wp_register_script(
			'toolset-maps-views-filter-distance-frontend-js',
			TOOLSET_ADDON_MAPS_URL . "/resources/js/views_filter_distance_frontend.js",
			array( 'jquery-geocomplete' ),
			TOOLSET_ADDON_MAPS_VERSION,
			true
		);

		wp_register_style(
			'toolset-maps-views-filter-distance-frontend-css',
			TOOLSET_ADDON_MAPS_URL . "/resources/css/views_filter_distance_frontend.css",
			array(),
			TOOLSET_ADDON_MAPS_VERSION
		);
	}

	function enqueue_scripts() {
		if ( isset( $_GET['page'] ) && $_GET['page'] == 'views-editor' ) {
			wp_enqueue_script( 'toolset-maps-views-filter-distance-backend-js' );
		}
	}

	function enqueue_frontend_script() {
		wp_enqueue_script( 'toolset-maps-views-filter-distance-frontend-js' );
		wp_enqueue_style( 'toolset-maps-views-filter-distance-frontend-css' );
	}

	/**
	 * Adds localization late (on action call), so special settings for frontend JS can be filled in when needed
     * @action toolset_maps_distance_use_frontend_script
	 */
	public function add_distance_filter_settings() {
		wp_localize_script(
			'toolset-maps-views-filter-distance-frontend-js',
			'toolset_maps_distance_filter_settings',
			$this->frontend_js
		);
    }

	static function toolset_maps_add_distance_views_filter( $filters ) {
		if ( self::get_saved_option( 'api_key' ) ) {
			$filters['maps_distance'] = array(
				'name'     => __( 'Distance', 'toolset-maps' ),
				'present'  => 'map_distance_filter',
				'callback' => array(
					'Toolset_Addon_Maps_Views_Distance_Filter',
					'toolset_maps_add_new_distance_views_filter_list_items'
				),
				'group'    => __( 'Toolset Maps', 'toolset-maps' )
			);
		}

		return $filters;
	}

	static function toolset_maps_add_new_distance_views_filter_list_items() {
		$args = array(
			'view-query-mode'     => 'normal',
			'map_distance_filter' => Toolset_Addon_Maps_Views_Distance_Filter::$distance_filter_options
		);

		Toolset_Addon_Maps_Views_Distance_Filter::toolset_maps_distance_views_filter_list_items( $args );
	}

	static function toolset_maps_distance_views_filter_list_items( $view_settings ) {
		if ( isset( $view_settings['map_distance_filter'] ) ) {
			if ( class_exists( 'WPV_Filter_Item' ) ) {
				$li = Toolset_Addon_Maps_Views_Distance_Filter::toolset_maps_get_list_item_ui_distance_filter( $view_settings );
				WPV_Filter_Item::simple_filter_list_item( 'maps_distance', 'posts', 'maps-distance', __( 'Distance', 'toolset-maps' ), $li );
			}
		}
	}

	static function toolset_maps_get_list_item_ui_distance_filter( $view_settings = array() ) {
		if ( isset( $view_settings['map_distance_filter'] ) && is_array( $view_settings['map_distance_filter'] ) ) {
			$distance_settings = $view_settings['map_distance_filter'];
		}
		ob_start();
		?>
        <p class='wpv-filter-maps-distance-edit-summary js-wpv-filter-summary js-wpv-filter-maps-distance-summary'>
			<?php echo Toolset_Addon_Maps_Views_Distance_Filter::toolset_maps_distance_views_filter_get_summary_txt( $view_settings ); ?>
        </p>
		<?php
		WPV_Filter_Item::simple_filter_list_item_buttons( 'maps-distance', 'toolset_maps_distance_views_filter_update', wp_create_nonce( 'toolset_maps_distance_views_filter_update_nonce' ), 'toolset_maps_distance_views_filter_delete', wp_create_nonce( 'toolset_maps_distance_views_filter_delete_nonce' ) );
		?>
        <div id="wpv-filter-maps-distance-edit" class="wpv-filter-edit js-wpv-filter-edit" style="padding-bottom:28px;">
            <div id="wpv-filter-maps-distance" class="js-wpv-filter-options js-wpv-filter-maps-distance-options">
				<?php Toolset_Addon_Maps_Views_Distance_Filter::toolset_maps_render_distance_views_options( $view_settings ); ?>
            </div>
            <div class="js-wpv-filter-toolset-messages"></div>
            <span class="filter-doc-help">
				<?php echo sprintf( __( '%sLearn about filtering by Distance%s', 'wpv-views' ),
					'<a class="wpv-help-link" href="#" target="_blank">',
					' &raquo;</a>'
				); ?>
			</span>
</div>
		<?php
		$res = ob_get_clean();

		return $res;
	}

	static function toolset_maps_render_distance_views_options( array $view_settings = array() ) {
		$defaults = array( 'view-query-mode' => 'normal' );
		$defaults = array_merge( $defaults, array( 'map_distance_filter' => Toolset_Addon_Maps_Views_Distance_Filter::$distance_filter_options ) );

		$view_settings     = wp_parse_args( $view_settings, $defaults );
		$distance_settings = $view_settings['map_distance_filter'];
		?>
        <h4><?php _e( 'How to filter', 'toolset-maps' ); ?></h4>
        <ul class="wpv-filter-options-set">
			<?php
			self::render_map_distance_compare_field( $distance_settings );
			self::render_map_distance( $distance_settings );
			self::render_map_distance_center( $distance_settings );
			?>
</ul>
		<div class="filter-helper js-wpv-author-helper"></div>
		<?php
	}

	static function render_map_distance_compare_field( array $distance_settings ) {
		$args = array(
			'field_type' => array( 'google_address' ),
			'filter'     => 'types'
		);
		$address_fields = apply_filters( 'types_filter_query_field_definitions', array(), $args );
		?>
        <li>
            <label for="wpv-filter-maps-compare-field"><?php _e( 'Field to compare to', 'toolset-maps' ); ?></label>
            <select id="wpv-filter-maps-compare-field" name="map_distance_compare_field"
                    class="js-filter-maps-compare-field">
				<?php
				foreach ( $address_fields['posts'] as $field ) {
					echo '<option value="' . esc_attr( $field['slug'] ) . '" '
                         . selected( $distance_settings['map_distance_compare_field'], $field['slug'] ) . '>'
                         . stripslashes( $field['name'] ) . '</option>';
				}
				?>
            </select>
        </li>
		<?php
    }

	static function render_map_distance( array $distance_settings ) {
	    ?>
        <li>
            <label for="wpv-filter-maps-distance"><?php _e( 'Filter Radius', 'toolset-maps' ); ?></label>
            <input type="number"
                   class="js-wpv-filter-maps-distance wpt-form-textfield form-textfield textfield"
                   id="wpv-filter-maps-distance" name="map_distance"
                   value="<?php echo esc_attr( !empty( $distance_settings['map_distance'] ) ? $distance_settings['map_distance'] : self::$distance_filter_options['map_distance'] ); ?>"
                   autocomplete="off"/>
            <select class="js-wpv-filter-maps-distance-unit" id="wpv-filter-maps-distance-unit"
                    name="map_distance_unit">
                <option value="km" <?php selected( $distance_settings['map_distance_unit'], 'km' ); ?>>
                    km
                </option>
                <option value="mi" <?php selected( $distance_settings['map_distance_unit'], 'mi' ); ?>>
                    mi
                </option>
            </select>
        </li>
        <?php
    }

    static function render_map_distance_center( array $distance_settings ) {
		?>
        <li>
            <label>
                <input class="js-filter-center-source" type="radio" name="map_center_source"
                       value="address" <?php checked( 'address', $distance_settings['map_center_source'] ); ?>/>
                <?php _e('Distance center is set using a fixed location:', 'toolset-maps') ?></label>
        </li>
        <div class="wpv-filter-maps-distance-center-container"
             style="border-left: 3px solid #CCC; margin-left:25px; padding-left:10px;">
            <div class="toolset-google-map-container js-toolset-google-map-container">
                <div class="toolset-google-map-inputs-container js-toolset-google-map-inputs-container">
                    <label class="" for="js-wpv-filter-maps-address"><?php _e('Address', 'toolset-maps') ?></label>
                    <input type="text" id="js-wpv-filter-maps-address" name="map_center_address" value="<?php echo esc_attr( isset( $distance_settings['map_distance_center'] ) ? $distance_settings['map_distance_center'] : '' ); ?>" placeholder="<?php _e('Enter address', 'toolset-maps') ?>" class="toolset-google-map js-toolset-google-map textfield" data-coordinates="" autocomplete="off">
                </div>
            </div>
        </div>
        <li>
            <label>
                <input class="js-filter-center-source" type="radio" name="map_center_source"
                   value="url_param" <?php checked( 'url_param', $distance_settings['map_center_source'] ); ?>>
                <?php _e('Distance center is set using URL parameter:', 'toolset-maps') ?></label>
                <input type="text"
                    class="js-distance-center-url-param"
                    name="map_distance_center_url_param_name"
                    value="<?php echo( $distance_settings['map_distance_center_url_param_name'] != "" ? $distance_settings['map_distance_center_url_param_name'] : 'mapcenter' ); ?>"/>
        </li>
        <li>
            <label>
                <input class="js-filter-center-source" type="radio" name="map_center_source"
                   value="shortcode_attr" <?php checked( 'shortcode_attr', $distance_settings['map_center_source'] ); ?>>
                <?php _e('Distance center is set using shortcode attribute:', 'toolset-maps') ?></label>
            <input type="text"
                  class="js-distance-center-shortcode"
                  name="map_distance_center_shortcode_attr_name"
                  value="<?php echo( $distance_settings['map_distance_center_shortcode_attr_name'] != "" ? $distance_settings['map_distance_center_shortcode_attr_name'] : 'mapcenter' ); ?>"/>
        </li>
        <li>
            <label>
                <input class="js-filter-center-source" type="radio" name="map_center_source"
                   value="user_location" <?php checked( 'user_location', $distance_settings['map_center_source'] ); ?>>
                <?php _e('Distance center is set from user location.', 'toolset-maps') ?></label>
        </li>
		<?php
    }

	static function toolset_maps_distance_views_filter_update_callback() {
		if ( ! current_user_can( 'manage_options' ) ) {
			$data = array(
				'type'    => 'capability',
				'message' => __( 'You do not have permissions for that.', 'wpv-views' )
			);
			wp_send_json_error( $data );
		}
		if (
			! isset( $_POST["wpnonce"] )
			|| ! wp_verify_nonce( $_POST["wpnonce"], 'toolset_maps_distance_views_filter_update_nonce' )
		) {
			$data = array(
				'type'    => 'nonce',
				'message' => __( 'Your security credentials have expired. Please reload the page to get new ones.', 'wpv-views' )
			);
			wp_send_json_error( $data );
		}
		if (
			! isset( $_POST["id"] )
			|| ! is_numeric( $_POST["id"] )
			|| intval( $_POST['id'] ) < 1
		) {
			$data = array(
				'type'    => 'id',
				'message' => __( 'Wrong or missing ID.', 'wpv-views' )
			);
			wp_send_json_error( $data );
		}
		if ( empty( $_POST['filter_options'] ) ) {
			$data = array(
				'type'    => 'data_missing',
				'message' => __( 'Wrong or missing data.', 'wpv-views' )
			);
			wp_send_json_error( $data );
		}
		$view_id = intval( $_POST['id'] );
		parse_str( $_POST['filter_options'], $distance_filter );
		$change     = false;
		$view_array = get_post_meta( $view_id, '_wpv_settings', true );

		$settings_to_check = array_keys( Toolset_Addon_Maps_Views_Distance_Filter::$distance_filter_options );

		foreach ( $settings_to_check as $set ) {
			if (
				isset( $distance_filter[ $set ] )
				&& (
					! isset( $view_array[ $set ] )
					|| $distance_filter[ $set ] != $view_array[ $set ]
				)
			) {
				if ( is_array( $distance_filter[ $set ] ) ) {
					$distance_filter[ $set ] = array_map( 'sanitize_text_field', $distance_filter[ $set ] );
				} else {
					$distance_filter[ $set ] = sanitize_text_field( $distance_filter[ $set ] );
				}
				$change = true;
			}
		}
		if ( $change ) {
			$view_array['map_distance_filter'] = $distance_filter;
			$result                            = update_post_meta( $view_id, '_wpv_settings', $view_array );
			do_action( 'wpv_action_wpv_save_item', $view_id );
		}
		$data = array(
			'id'      => $view_id,
			'message' => __( 'Distance filter saved', 'wpv-views' ),
			'summary' => Toolset_Addon_Maps_Views_Distance_Filter::toolset_maps_distance_views_filter_get_summary_txt( array( "map_distance_filter" => $distance_filter ) )
		);
		wp_send_json_success( $data );
	}

	static function toolset_maps_distance_views_filter_get_summary_txt( $views_settings = array() ) {
		if ( !self::get_saved_option( 'api_key' ) ) {
		    return __( 'You need to set a valid Google Maps API key', 'toolset-maps' );
		}

		$distance_filter = $views_settings['map_distance_filter'];
		if ( ! isset( $distance_filter['map_distance'] ) || ! isset( $distance_filter['map_distance_center'] ) || ! isset( $distance_filter['map_distance_unit'] ) || ! isset( $distance_filter['map_center_source'] ) ) {
			return;
		}
		if ( $distance_filter['map_distance_unit'] == 0 ) {
			$distance_filter['map_distance_unit'] = 'km';
        }
        if ( !$distance_filter['map_distance'] ) {
			$distance_filter['map_distance'] = self::$distance_filter_options['map_distance'];
        }

		$distance_center_summary = '';
		$radius_summary          = $distance_filter['map_distance'] . $distance_filter['map_distance_unit'] . ' </strong> '.__( 'radius of', 'toolset-maps' ).' <strong>';

		switch ( $distance_filter['map_center_source'] ) {
			case 'address':
				$distance_center_summary = $radius_summary . $distance_filter['map_distance_center'];
				break;
			case 'url_param':
				$distance_center_summary = $radius_summary . __(' address/coordinates provided using <i>', 'toolset-maps' ) . $distance_filter['map_distance_center_url_param_name'] . __( '</i> URL parameter', 'toolset-maps' );
				break;
			case 'shortcode_attr':
				$distance_center_summary = $radius_summary . __(' address/coordinates provided using <i>', 'toolset-maps' ) . $distance_filter['map_distance_center_shortcode_attr_name'] . __( '</i> shortcode attribute', 'toolset-maps' );
				break;
			case 'user_location':
				$distance_center_summary = $radius_summary . __(' the viewing user\'s location', 'toolset-maps' );
				break;
		}

		ob_start();
		_e( 'Show posts within <strong>' . $distance_center_summary . '</strong>.', 'toolset-maps' );
		$data = ob_get_clean();

		return $data;
	}

	static function toolset_maps_distance_views_filter_delete_callback() {
		if ( ! current_user_can( 'manage_options' ) ) {
			$data = array(
				'type'    => 'capability',
				'message' => __( 'You do not have permissions for that.', 'wpv-views' )
			);
			wp_send_json_error( $data );
		}
		if (
			! isset( $_POST['wpnonce'] ) || ! wp_verify_nonce( $_POST['wpnonce'], 'toolset_maps_distance_views_filter_delete_nonce' )
		) {
			$data = array(
				'type'    => 'nonce',
				'message' => __( 'Your security credentials have expired. Please reload the page to get new ones.', 'toolset-maps' )
			);
			wp_send_json_error( $data );
		}
		if (
			! isset( $_POST['id'] ) || ! is_numeric( $_POST['id'] ) || intval( $_POST['id'] ) < 1
		) {
			$data = array(
				'type'    => 'id',
				'message' => __( 'Wrong or missing ID.', 'toolset-maps' )
			);
			wp_send_json_error( $data );
		}

		$view_array = get_post_meta( $_POST['id'], '_wpv_settings', true );



				unset( $view_array['map_distance_filter'] );

		update_post_meta( $_POST['id'], '_wpv_settings', $view_array );
		do_action( 'wpv_action_wpv_save_item', $_POST['id'] );
		$data = array(
			'id'      => $_POST['id'],
			'message' => __( 'Distance filter deleted', 'toolset-maps' )
		);
		wp_send_json_success( $data );
	}

	static function toolset_maps_distance_views_filter_calculate_distance_diff( $center_coords, $address_coords, $unit = 'km' ) {
		$earth_radius = ( $unit == 'mi' ? 3963.0 : 6371 );

		$lat_diff = deg2rad( $address_coords['lat'] - $center_coords['lat'] );
		$lon_diff = deg2rad( $address_coords['lon'] - $center_coords['lon'] );

		$lat_lon_delta = sin( $lat_diff / 2 ) * sin( $lat_diff / 2 ) + cos( deg2rad( $center_coords['lat'] ) ) * cos( deg2rad( $address_coords['lat'] ) ) * sin( $lon_diff / 2 ) * sin( $lon_diff / 2 );
		$lat_lon_angle = 2 * asin( sqrt( $lat_lon_delta ) );
		$distance      = $earth_radius * $lat_lon_angle;

		return $distance;
	}

	static function toolset_maps_coords_or_address( $input ) {
		if ( self::toolset_maps_validate_coords( $input ) ) {
			return 'coords';
		}

		if ( self::toolset_maps_validate_address( $input ) ) {
			return 'address';
		}

		return false;
	}

	static function toolset_maps_validate_coords( $coords ) {
		return preg_match( '/^[-]?(([0-8]?[0-9])\.(\d+))|(90(\.0+)?);[-]?((((1[0-7][0-9])|([0-9]?[0-9]))\.(\d+))|180(\.0+)?)$/', $coords );
	}

	static function toolset_maps_validate_address( $address ) {
		return preg_match( '/[^A-Za-z0-9\.\/\\\\]|\..*\.|\.$/', $address );
	}

	static function get_coords_array_from_input( $input ) {
		$provided_center_type = self::toolset_maps_coords_or_address( $input );
		$coords_array    = array();

		if ( $provided_center_type !== false ) {
			if ( $provided_center_type == 'address' ) {
				$address_coords = Toolset_Addon_Maps_Common::get_coordinates( $input );

				return $address_coords;
			}

			$exploded_coords = explode( ',', $input );

			if ( count( $exploded_coords ) > 1 ) {
				$coords_array['lat'] = $exploded_coords[0];
				$coords_array['lon'] = $exploded_coords[1];
			}
		}

		return $coords_array;
	}

	static function is_valid_coords_array( $coords_array ) {
		return ( array_key_exists( 'lat', $coords_array ) && array_key_exists( 'lon', $coords_array ) );
	}

	public function toolset_maps_set_coords_from_center_source( $view_settings, $shortcode_attrs = null ) {
		$distance_filter_options = $view_settings['map_distance_filter'];

		if ( array_key_exists( 'map_center_source', $distance_filter_options ) ) {
			switch ( $distance_filter_options['map_center_source'] ) {

				case 'address':
					$address_coords = self::get_coords_array_from_input( $distance_filter_options['map_distance_center'] );

					if ( ! empty( $address_coords ) && array_key_exists( 'lat', $address_coords ) && array_key_exists( 'lon', $address_coords ) ) {
						$distance_filter_options['map_center_lat'] = $address_coords['lat'];
						$distance_filter_options['map_center_lng'] = $address_coords['lon'];
					}
					break;

                case 'user_location':
					if ( isset( $_GET[ self::DISTANCE_CENTER_USER_COORDS_PARAM ] ) ) {
						$coords_array = self::get_coords_array_from_input( $_GET[ self::DISTANCE_CENTER_USER_COORDS_PARAM ] );
						if ( self::is_valid_coords_array( $coords_array ) ) {
							$distance_filter_options['map_center_lat'] = $coords_array['lat'];
							$distance_filter_options['map_center_lng'] = $coords_array['lon'];
						}
					} else {
						$this->frontend_js['use_user_location'] = true;
						$this->frontend_js['view_id']           = $view_settings['view_id'];
						do_action( 'toolset_maps_distance_use_frontend_script' );
					}
					break;

				case 'url_param':
					$distance_filter_options['map_center_lat'] = null;
					$distance_filter_options['map_center_lng'] = null;
					$url_param_value                           = null;

					if ( array_key_exists( 'map_distance_center_url_param_name', $distance_filter_options ) && isset( $_GET[ $distance_filter_options['map_distance_center_url_param_name'] ] ) ) {
						$url_param_value = $_GET[ $distance_filter_options['map_distance_center_url_param_name'] ];

					} elseif ( isset( $_GET[ self::DISTANCE_CENTER_DEFAULT_URL_PARAM ] ) ) {
						$url_param_value = $_GET[ $distance_filter_options['map_distance_center_url_param_name'] ];
					}

					if ( $url_param_value !== null ) {
						$coords_array = self::get_coords_array_from_input( $url_param_value );

						if ( self::is_valid_coords_array( $coords_array ) ) {
							$distance_filter_options['map_center_lat'] = $coords_array['lat'];
							$distance_filter_options['map_center_lng'] = $coords_array['lon'];
						}
					}
					break;

				case 'shortcode_attr':
					if ( is_array( $shortcode_attrs ) && array_key_exists( $distance_filter_options['map_distance_center_shortcode_attr_name'], $shortcode_attrs ) ) {
						$coords_array = self::get_coords_array_from_input( $shortcode_attrs[ $distance_filter_options['map_distance_center_shortcode_attr_name'] ] );

						if ( self::is_valid_coords_array( $coords_array ) ) {
							$distance_filter_options['map_center_lat'] = $coords_array['lat'];
							$distance_filter_options['map_center_lng'] = $coords_array['lon'];
						}
					}
					break;
			}
		}

		return $distance_filter_options;
	}

	public function toolset_maps_distance_views_filter_apply_rules( $post_query, $view_settings, $view_id ) {
		if ( ! array_key_exists( 'map_distance_filter', $view_settings ) ) {
			return $post_query;
		}

		//Replace view settings value with shortcode ones
		$view_shortcode_attrs     = apply_filters( 'wpv_filter_wpv_get_view_shortcodes_attributes', array() );
		$distance_filter_settings = $this->toolset_maps_set_coords_from_center_source( $view_settings, $view_shortcode_attrs );

		if ( empty( $distance_filter_settings['map_center_lat'] ) || empty( $distance_filter_settings['map_center_lng'] ) ) {
			$post_query = $this->bring_paging_back( $post_query );
			return $post_query;
		}

		if ( $distance_filter_settings['map_center_source'] == 'url_param' ) {
			if (
                isset( $distance_filter_settings['map_distance_radius_url_param_name'] )
                && $distance_filter_settings['map_distance_radius_url_param_name'] !== ''
                && isset( $_GET[ $distance_filter_settings['map_distance_radius_url_param_name'] ] )
            ) {
				$distance_filter_settings['map_distance'] = (double) $_GET[ $distance_filter_settings['map_distance_radius_url_param_name'] ];
			}

			if (
                isset ( $distance_filter_settings['map_distance_unit_url_param_name'] )
                && $distance_filter_settings['map_distance_unit_url_param_name'] !== ''
                && isset( $_GET[ $distance_filter_settings['map_distance_unit_url_param_name'] ] )
            ) {
				$distance_filter_settings['map_distance_unit'] = (string) sanitize_text_field( $_GET[ $distance_filter_settings['map_distance_unit_url_param_name'] ] );
			}

			if (
				isset ( $distance_filter_settings['map_distance_center_url_param_name'] )
				&& $distance_filter_settings['map_distance_center_url_param_name'] !== ''
				&& isset( $_GET[ $distance_filter_settings['map_distance_center_url_param_name'] ] )
			) {
				$distance_filter_settings['map_distance_center'] = (string) sanitize_text_field( $_GET[ $distance_filter_settings['map_distance_center_url_param_name'] ] );
			}
		}

		$posts_to_remove = array();

		foreach ( $post_query->posts as $post_index => $the_post ) {
			$args = array(
				'domain'     => 'posts',
				'field_type' => array( 'google_address' ),
				'filter'     => 'types'
			);

			if ( isset ( $distance_filter_settings['map_distance_compare_field'] ) && $distance_filter_settings['map_distance_compare_field'] != - 1 ) {
				$args['search'] = $distance_filter_settings['map_distance_compare_field'];
			}

			$address_fields = apply_filters( 'types_filter_query_field_definitions', array(), $args );

			//I'm leaving the logic for multiple address rule in case we use it in the future
			if ( count( $address_fields ) > 0 ) {
				$is_outside_radius = true;

				foreach ( $address_fields as $field ) {
					$post_address = get_post_meta( $the_post->ID, $field['meta_key'] );

					if ( is_array( $post_address ) && isset( $post_address[0] ) ) {
						$post_address = $post_address[0];
					}

					if ( ! empty( $post_address ) ) {
						$address_coords = self::get_coords_array_from_input( $post_address );

						$center_coords = array(
							'lat' => (double) $distance_filter_settings['map_center_lat'],
							'lon' => (double) $distance_filter_settings['map_center_lng']
						);

						$distance_diff = Toolset_Addon_Maps_Views_Distance_Filter::toolset_maps_distance_views_filter_calculate_distance_diff(
							$center_coords,
							$address_coords,
							$distance_filter_settings['map_distance_unit'] );

						if ( $distance_diff < (double) $distance_filter_settings['map_distance'] ) {
							$is_outside_radius = false;
							break;
						}
					}
				}

				if ( $is_outside_radius ) {
					$post_query->post_count  -= 1;
					$post_query->found_posts -= 1;
					$posts_to_remove[]       = $post_index;
				}
			}
		}

		$post_query->posts = array_values( array_diff_key( $post_query->posts, array_flip( $posts_to_remove ) ) );

		// If asked for, bring paging back now that we have the filtered result set
        $post_query = $this->bring_paging_back( $post_query );

		return $post_query;
	}

	/**
     * Checks if there was paging removed in order to allow post processing to do its thing, and brings it back.
     *
	 * @param WP_Query $post_query
	 *
	 * @return WP_Query
	 */
	protected function bring_paging_back( WP_Query $post_query ) {
		if ( $post_query->query['wpv_original_posts_per_page'] !== -1 ) {
			$post_query->query['posts_per_page'] = $post_query->query['wpv_original_posts_per_page'];
			$post_query->max_num_pages = ceil( count( $post_query->posts ) / $post_query->query['posts_per_page'] );
			$post_query->posts = array_slice(
				$post_query->posts,
				( $post_query->query['paged'] - 1 ) * $post_query->query['posts_per_page'],
				$post_query->query['posts_per_page']
			);
		}

		return $post_query;
	}

	static function toolset_maps_distance_custom_search_filter( $form_filters_shortcodes ) {
		if ( self::get_saved_option( 'api_key' ) ) {
			$form_filters_shortcodes['wpv-control-distance'] = array(
				'query_type_target'            => 'posts',
				'query_filter_define_callback' => array(
					'Toolset_Addon_Maps_Views_Distance_Filter',
					'query_filter_define_callback'
				),
				'custom_search_filter_group'   => __( 'Toolset Maps', 'toolset-maps' ),
				'custom_search_filter_items'   => array(
					'maps_distance' => array(
						'name'    => __( 'Distance', 'toolset-maps' ),
						'present' => 'map_distance_filter',
						'params'  => array()
					)
				)
			);
		}

		return $form_filters_shortcodes;
	}

	/**
     * Callback to display the custom search filter by distance
     *
	 * @param array $atts Shortcode attributes
	 *
	 * @return string Filter form HTML
	 */
	public function toolset_maps_shortcode_wpv_control_post_distance( array $atts ) {
		if ( !self::get_saved_option( 'api_key' ) ) {
		    return '';
		}

		// If a reqired att is missing, return early without content.
		$required_atts = array(
            'compare_field',
            'distance_center_url_param',
            'distance_radius_url_param',
            'distance_unit_url_param'
        );
		foreach ( $required_atts as $required_att ) {
		    if ( !array_key_exists( $required_att, $atts ) ) {
				return '';
			}
        }

		// Use defaults if atts are missing or empty
        $compare_fields = $this->get_comparison_address_fields();
		$default_compare_field = reset( $compare_fields );
		$atts = shortcode_atts( array(
			'default_distance' => self::$distance_filter_options['map_distance'],
			'default_unit' => self::$distance_filter_options['map_distance_unit'],
			'compare_field' => $default_compare_field,
			'distance_center_url_param' => self::DISTANCE_CENTER_DEFAULT_URL_PARAM,
			'distance_radius_url_param' => self::DISTANCE_RADIUS_DEFAULT_URL_PARAM,
			'distance_unit_url_param' => self::DISTANCE_UNIT_DEFAULT_URL_PARAM,
			'inputs_placeholder' => self::DISTANCE_FILTER_INPUTS_PLACEHOLDER,
			'visitor_location_button_text' => self::DISTANCE_FILTER_VISITOR_LOCATION_BUTTON_TEXT
        ), $atts);

		$distance_radius_url_param = esc_attr( $atts['distance_radius_url_param'] );
		$distance_center_url_param = esc_attr( $atts['distance_center_url_param'] );
		$distance_unit_url_param   = esc_attr( $atts['distance_unit_url_param'] );

		$defaults = array(
			$distance_radius_url_param => isset( $atts['default_distance'] )
                ? (double) $atts['default_distance']
                : self::$distance_filter_options['map_distance'],
			$distance_center_url_param => "",
			$distance_unit_url_param   => isset( $atts['default_unit'] ) ? $atts['default_unit'] : '',
		);

		if ( isset( $_GET[ $distance_radius_url_param ] ) ) {
			$defaults[ $distance_radius_url_param ] = (double) $_GET[ $distance_radius_url_param ];
		}

		if ( isset( $_GET[ $distance_center_url_param ] ) ) {
			$defaults[ $distance_center_url_param ] = sanitize_text_field( $_GET[ $distance_center_url_param ] );
		}

		if ( isset( $_GET[ $distance_unit_url_param ] ) ) {
			$defaults[ $distance_unit_url_param ] = sanitize_text_field( $_GET[ $distance_unit_url_param ] );
		}

		$defaults = wp_parse_args( $_GET, $defaults );

		$km_selected = selected( $defaults[ $distance_unit_url_param ], 'km', false );
		$mi_selected = selected( $defaults[ $distance_unit_url_param ], 'mi', false );

		// Use edited or translated string. Oh, and enable edited string to also be translated.
		$inputs_placeholder_translated = !empty( $atts['inputs_placeholder'] )
            ? wpv_translate( 'Inputs placeholder: '.$atts['inputs_placeholder'], $atts['inputs_placeholder'], false, 'toolset-maps' )
            : __( 'Show results within %%DISTANCE%% of %%CENTER%%', 'toolset-maps' );
		$use_my_location_translated = !empty( $atts['visitor_location_button_text'] )
            ? wpv_translate( 'Visitor location button text: '.$atts['visitor_location_button_text'], $atts['visitor_location_button_text'], false, 'toolset-maps' )
            : __( 'Use my location', 'toolset-maps' );

		$distance_input = <<<HTML
            <input type="number" min="0" id="toolset-maps-distance-value"
                name="$distance_radius_url_param"
                class="form-control js-toolset-maps-distance-value js-wpv-filter-trigger-delayed"
                value="{$defaults[ $distance_radius_url_param ]}"
                required>
            <select class="js-toolset-maps-distance-unit form-control js-wpv-filter-trigger"
                    name="$distance_unit_url_param"
                    id="toolset-maps-distance">
                <option value="km" $km_selected>km</option>
                <option value="mi" $mi_selected>mi</option>
            </select>		
HTML;
		$center_input = <<<HTML
            <input type="text" min="0" id="toolset-maps-distance-center"
                name="$distance_center_url_param"
                class="form-control js-toolset-maps-distance-center js-wpv-filter-trigger"
                value="{$defaults[ $distance_center_url_param ]}"
                required>
            <button class="fa fa-location-arrow btn btn-default js-toolset-maps-distance-current-location" style="display: none"> $use_my_location_translated</button>
HTML;

		$inputs_interpolated = str_replace(
            '%%DISTANCE%%', $distance_input, str_replace(
                '%%CENTER%%', $center_input, $inputs_placeholder_translated
            )
        );

		return <<<HTML
            <div class="form-group">
                <label>$inputs_interpolated</label>
            </div>
HTML;
	}

	public function toolset_maps_filter_distance_register_gui_data( $views_shortcodes ) {
		$views_shortcodes['wpv-control-distance'] = array(
			'callback' => array( $this, 'toolset_maps_filter_distance_get_gui_data' )
		);

		return $views_shortcodes;
	}

	/**
	 * Usually we need just one option from the saved options array
	 * @param string $key
	 * @return mixed
	 */
	protected static function get_saved_option( $key ) {
		$saved_options = apply_filters( 'toolset_filter_toolset_maps_get_options', array() );

		if ( isset( $saved_options[$key] ) ) {
			return $saved_options[ $key ];
		} else {
			return null;
		}
	}

	private function get_comparison_address_fields() {
		$args           = array(
			'field_type' => array( 'google_address' ),
			'filter'     => 'types'
		);
		$address_fields = apply_filters( 'types_filter_query_field_definitions', array(), $args );

		$fields_array = array();

		foreach ( $address_fields['posts'] as $field ) {
			$fields_array[ $field['slug'] ] = stripslashes( $field['name'] );
		}

		return $fields_array;
	}

	static function query_filter_define_callback( $view_id, $shortcode, $attributes = array(), $attributes_raw = array() ) {
		if ( ! isset( $attributes['distance_center_url_param'] ) || ! isset( $attributes['distance_radius_url_param'] ) || ! isset( $attributes['distance_unit_url_param'] ) ) {
			return;
		}

		$view_array = get_post_meta( $view_id, '_wpv_settings', true );

		$distance_options = array(
			'map_distance'                            => ( isset( $attributes['default_distance'] ) ? $attributes['default_distance'] : 0 ),
			'map_distance_center'                     => '',
			'map_distance_unit'                       => ( isset( $attributes['default_unit'] ) ? $attributes['default_unit'] : 0 ),
			'map_center_lat'                          => '',
			'map_center_lng'                          => '',
			'map_center_source'                       => 'url_param',
			'map_distance_center_url_param_name'      => $attributes['distance_center_url_param'],
			'map_distance_radius_url_param_name'      => $attributes['distance_radius_url_param'],
			'map_distance_unit_url_param_name'        => $attributes['distance_unit_url_param'],
			'map_distance_center_shortcode_attr_name' => 'mapcenter',
			'map_distance_compare_field'              => $attributes['compare_field']
		);

		$view_array['map_distance_filter'] = $distance_options;

		update_post_meta( $view_id, '_wpv_settings', $view_array );

		do_action( 'wpv_action_wpv_save_item', $view_id );
	}

	public function toolset_maps_filter_distance_get_gui_data( $parameters = array(), $overrides = array() ) {
		$data = array(
			'attributes' => array(
				'display-options' => array(
					'label'  => __( 'Display options', 'toolset-maps' ),
					'header' => 'Distance filter will allow users to search posts using map distance',
					'fields' => array(
						'default_distance'          => array(
							'label'   => __( 'Default filter radius', 'toolset-maps' ),
							'type'    => 'number',
							'default' => self::$distance_filter_options['map_distance'],
						),
						'default_unit'              => array(
							'label'   => __( 'Distance radius unit', 'toolset-maps' ),
							'type'    => 'select',
							'options' => array( 'km' => 'km', 'mi' => 'mi' ),
							'default' => self::$distance_filter_options['map_distance_unit'],
						),
						'compare_field'             => array(
							'label'    => __( 'Comparison Field', 'toolset-maps' ),
							'type'     => 'select',
							'options'  => $this->get_comparison_address_fields(),
							'required' => true
						),
						'distance_center_url_param' => array(
							'label'         => __( 'Distance Center URL parameter to use', 'toolset-maps' ),
							'type'          => 'text',
							'default_force' => self::DISTANCE_CENTER_DEFAULT_URL_PARAM,
							'required'      => true
						),
						'distance_radius_url_param' => array(
							'label'         => __( 'Distance radius URL parameter to use', 'toolset-maps' ),
							'type'          => 'text',
							'default_force' => self::DISTANCE_RADIUS_DEFAULT_URL_PARAM,
							'required'      => true
						),
						'distance_unit_url_param'   => array(
							'label'         => __( 'Distance unit URL parameter to use', 'toolset-maps' ),
							'type'          => 'text',
							'default_force' => self::DISTANCE_UNIT_DEFAULT_URL_PARAM,
							'required'      => true
						),
                        'inputs_placeholder'  => array (
                            'label'     => __( 'Text and placeholders for input fields', 'toolset-maps' ),
                            'type'      => 'text',
                            'default'   => self::DISTANCE_FILTER_INPUTS_PLACEHOLDER
                        )
					)
				),
			),
		);

		if ($this->is_frontend_served_over_https() ) {
		    $data['attributes']['display-options']['fields']['visitor_location_button_text'] = array (
				'label'     => __( 'Text for visitor location button', 'toolset-maps' ),
				'type'      => 'text',
				'default'   => self::DISTANCE_FILTER_VISITOR_LOCATION_BUTTON_TEXT
			);
        }

		$dialog_label = __( 'Distance filter', 'wpv-views' );

		$data['name']  = $dialog_label;
		$data['label'] = $dialog_label;

		return $data;
	}

	/**
	 * Under assumption that site settings are not wrong, answers if frontend is served over https
     * @todo: move to toolset-common
	 * @return bool
	 */
	public function is_frontend_served_over_https(){
		return ( parse_url( get_home_url(), PHP_URL_SCHEME ) === 'https' );
	}
}

$Toolset_Addon_Maps_Views_Distance_Filter = new Toolset_Addon_Maps_Views_Distance_Filter();
