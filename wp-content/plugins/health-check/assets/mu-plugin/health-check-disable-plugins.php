<?php
/*
	Plugin Name: Health Check Disable Plugins
	Description: Conditionally disabled plugins on your site for a given session, used to rule out plugin interactions during troubleshooting.
	Version: 1.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'We\'re sorry, but you can not directly access this file.' );
}

class Health_Check_Troubleshooting_MU {
	private $override_active = true;
	private $default_theme   = true;
	private $active_plugins  = array();

	private $available_query_args = array(
		'health-check-disable-plugins',
		'health-check-disable-plugins-hash',
		'health-check-disable-troubleshooting',
		'health-check-toggle-default-theme',
		'health-check-troubleshoot-enable-plugin',
		'health-check-troubleshoot-disable-plugin',
	);

	/**
	 * Health_Check_Troubleshooting_MU constructor.
	 */
	public function __construct() {
		$this->init();
	}

	/**
	 * Actually initiation of the plugin.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'admin_bar_menu', array( $this, 'health_check_troubleshoot_menu_bar' ), 999 );

		add_filter( 'option_active_plugins', array( $this, 'health_check_loopback_test_disable_plugins' ) );
		add_filter( 'option_active_sitewide_plugins', array( $this, 'health_check_loopback_test_disable_plugins' ) );

		add_filter( 'stylesheet', array( $this, 'health_check_troubleshoot_theme' ) );
		add_filter( 'template', array( $this, 'health_check_troubleshoot_theme' ) );

		add_action( 'admin_notices', array( $this, 'plugin_list_admin_notice' ) );

		add_action( 'plugin_action_links', array( $this, 'plugin_actions' ), 50, 4 );

		add_action( 'wp_logout', array( $this, 'health_check_troubleshooter_mode_logout' ) );
		add_action( 'init', array( $this, 'health_check_troubleshoot_get_captures' ) );

		/*
		 * Plugin activations can be forced by other tools in things like themes, so let's
		 * attempt to work around that by forcing plugin lists back and forth.
		 *
		 * This is not an ideal scenario, but one we must accept as reality.
		 */
		add_action( 'activated_plugin', array( $this, 'plugin_activated' ) );

		$this->default_theme  = ( 'yes' === get_option( 'health-check-default-theme', 'yes' ) ? true : false );
		$this->active_plugins = $this->get_unfiltered_plugin_list();
	}

	/**
	 * Fire on plugin activation.
	 *
	 * When in Troubleshooting Mode, plugin activations
	 * will clear out the DB entry for `active_plugins`, this is bad.
	 *
	 * We fix this by re-setting the DB entry if anything tries
	 * to modify it during troubleshooting.
	 *
	 * @return void
	 */
	public function plugin_activated() {
		if ( ! $this->is_troubleshooting() ) {
			return;
		}

		// Force the database entry for active plugins if someone tried changing plugins while in Troubleshooting Mode.
		update_option( 'active_plugins', $this->active_plugins );
	}

	/**
	 * Add a notice to the plugins screen.
	 *
	 * Make sure users are informed that all plugin actions are
	 * stripped away when they are troubleshooting.
	 *
	 * @return void
	 */
	public function plugin_list_admin_notice() {
		if ( ! $this->is_troubleshooting() ) {
			return;
		}
		global $current_screen;

		// Only output our notice on the plugins screen.
		if ( 'plugins' !== $current_screen->base ) {
			return;
		}

		printf(
			'<div class="notice notice-warning"><p>%s</p></div>',
			esc_html__( 'Plugin actions are not available while in Troubleshooting Mode.', 'health-check' )
		);
	}

	/**
	 * Modify plugin actions.
	 *
	 * While in Troubleshooting Mode, weird things will happen if you start
	 * modifying your plugin list. Prevent this, but also add in the ability
	 * to enable or disable a plugin during troubleshooting from this screen.
	 *
	 * @param $actions
	 * @param $plugin_file
	 * @param $plugin_data
	 * @param $context
	 *
	 * @return array
	 */
	public function plugin_actions( $actions, $plugin_file, $plugin_data, $context ) {
		if ( ! $this->is_troubleshooting() ) {
			return $actions;
		}

		if ( 'mustuse' === $context ) {
			return $actions;
		}

		/*
		 * Disable all plugin actions when in Troubleshooting Mode.
		 *
		 * We intentionally remove all plugin actions to avoid accidental clicking, activating or deactivating plugins
		 * while our plugin is altering plugin data may lead to unexpected behaviors, so to keep things sane we do
		 * not allow users to perform any actions during this time.
		 */
		$actions = array();

		// This isn't an active plugin, so does not apply to our troubleshooting scenarios.
		if ( ! in_array( $plugin_file, $this->active_plugins ) ) {
			return $actions;
		}

		// Set a slug if the plugin lives in the plugins directory root.
		if ( ! stristr( $plugin_file, '/' ) ) {
			$plugin_data['slug'] = $plugin_file;
		}

		$allowed_plugins = get_option( 'health-check-allowed-plugins', array() );

		$plugin_slug = ( isset( $plugin_data['slug'] ) ? $plugin_data['slug'] : sanitize_title( $plugin_data['Name'] ) );

		if ( in_array( $plugin_slug, $allowed_plugins ) ) {
			$actions['troubleshoot-disable'] = sprintf(
				'<a href="%s">%s</a>',
				esc_url( add_query_arg( array(
					'health-check-troubleshoot-disable-plugin' => $plugin_slug
				), admin_url( 'plugins.php' ) ) ),
				esc_html__( 'Disable while troubleshooting', 'health-check' )
			);
		}
		else {
			$actions['troubleshoot-disable'] = sprintf(
				'<a href="%s">%s</a>',
				esc_url( add_query_arg( array(
					'health-check-troubleshoot-enable-plugin' => $plugin_slug
				), admin_url( 'plugins.php' ) ) ),
				esc_html__( 'Enable while troubleshooting', 'health-check' )
			);
		}

		return $actions;
	}

	/**
	 * Get the actual list of active plugins.
	 *
	 * When in Troubleshooting Mode we override the list of plugins,
	 * this function lets us grab the active plugins list without
	 * any interference.
	 *
	 * @return array Array of active plugins.
	 */
	private function get_unfiltered_plugin_list() {
		$this->override_active = false;
		$all_plugins           = get_option( 'active_plugins' );
		$this->override_active = true;

		return $all_plugins;
	}

	/**
	 * Check if the user is currently in Troubleshooting Mode or not.
	 *
	 * @return bool
	 */
	private function is_troubleshooting() {
		// Check if a session cookie to disable plugins has been set.
		if ( isset( $_COOKIE['health-check-disable-plugins'] ) ) {
			$_GET['health-check-disable-plugin-hash'] = $_COOKIE['health-check-disable-plugins'];
		}


		// If the disable hash isn't set, no need to interact with things.
		if ( ! isset( $_GET['health-check-disable-plugin-hash'] ) ) {
			return false;
		}

		// If the plugin hash is not valid, we also break out
		$disable_hash = get_option( 'health-check-disable-plugin-hash', '' );
		if ( $disable_hash !== $_GET['health-check-disable-plugin-hash'] ) {
			return false;
		}

		return true;
	}

	/**
	 * Filter the plugins that are activated in WordPress.
	 *
	 * @param array $plugins An array of plugins marked as active.
	 *
	 * @return array
	 */
	function health_check_loopback_test_disable_plugins( $plugins ) {
		if ( ! $this->is_troubleshooting() || ! $this->override_active ) {
			return $plugins;
		}

		$allowed_plugins = get_option( 'health-check-allowed-plugins', array() );

		// If we've received a comma-separated list of allowed plugins, we'll add them to the array of allowed plugins.
		if ( isset( $_GET['health-check-allowed-plugins'] ) ) {
			$allowed_plugins = explode( ',', $_GET['health-check-allowed-plugins'] );
		}

		foreach ( $plugins as $plugin_no => $plugin_path ) {
			// Split up the plugin path, [0] is the slug and [1] holds the primary plugin file.
			$plugin_parts = explode( '/', $plugin_path );

			// We may want to allow individual, or groups of plugins, so introduce a skip-mechanic for those scenarios.
			if ( in_array( $plugin_parts[0], $allowed_plugins ) ) {
				continue;
			}

			// Remove the reference to this plugin.
			unset( $plugins[ $plugin_no ] );
		}

		// Return a possibly modified list of activated plugins.
		return $plugins;
	}

	/**
	 * Use a default theme.
	 *
	 * Attempt to set one of the default themes as the active one
	 * during Troubleshooting Mode, if one exists, if not fall
	 * back to always showing the active theme.
	 *
	 * @param string $theme The users active theme slug.
	 *
	 * @return string Theme slug to be perceived as the active theme.
	 */
	function health_check_troubleshoot_theme( $theme ) {
		if ( ! $this->is_troubleshooting() || ! $this->override_active || ! $this->default_theme ) {
			return $theme;
		}

		$default_themes = array(
			'twentyseventeen',
			'twentysixteen',
			'twentyfifteen',
			'twentyfourteen',
			'twentythirteen',
			'twentytwelve',
			'twentyeleven',
			'twentyten'
		);

		foreach ( $default_themes AS $default_theme ) {
			if ( is_dir( WP_CONTENT_DIR . '/themes/' . $default_theme ) ) {
				return $default_theme;
			}
		}

		return $theme;
	}

	/**
	 * Disable Troubleshooting Mode on logout.
	 *
	 * If logged in, disable the Troubleshooting Mode when the logout
	 * event is fired, this ensures we start with a clean slate on
	 * the next login.
	 *
	 * @return void
	 */
	function health_check_troubleshooter_mode_logout() {
		if ( ! $this->is_troubleshooting() ) {
			return;
		}

		if ( isset( $_COOKIE['health-check-disable-plugins'] ) ) {
			unset( $_COOKIE['health-check-disable-plugins'] );
			setcookie( 'health-check-disable-plugins', null, 0, COOKIEPATH, COOKIE_DOMAIN );
			delete_option( 'health-check-allowed-plugins' );
		}
	}

	/**
	 * Catch query arguments.
	 *
	 * When in Troubleshooting Mode, look for various GET variables that trigger
	 * various plugin actions.
	 *
	 * @return void
	 */
	function health_check_troubleshoot_get_captures() {
		if ( ! $this->is_troubleshooting() ) {
			return;
		}

		// Disable Troubleshooting Mode.
		if ( isset( $_GET['health-check-disable-troubleshooting'] ) ) {
			unset( $_COOKIE['health-check-disable-plugins'] );
			setcookie( 'health-check-disable-plugins', null, 0, COOKIEPATH, COOKIE_DOMAIN );
			delete_option( 'health-check-allowed-plugins' );

			wp_redirect( remove_query_arg( $this->available_query_args ) );
			die();
		}

		// Enable an individual plugin.
		if ( isset( $_GET['health-check-troubleshoot-enable-plugin'] ) ) {
			$allowed_plugins                                                    = get_option( 'health-check-allowed-plugins', array() );
			$allowed_plugins[ $_GET['health-check-troubleshoot-enable-plugin'] ] = $_GET['health-check-troubleshoot-enable-plugin'];

			update_option( 'health-check-allowed-plugins', $allowed_plugins );

			wp_redirect( remove_query_arg( $this->available_query_args ) );
			die();
		}

		// Disable an individual plugin.
		if ( isset( $_GET['health-check-troubleshoot-disable-plugin'] ) ) {
			$allowed_plugins = get_option( 'health-check-allowed-plugins', array() );
			unset( $allowed_plugins[ $_GET['health-check-troubleshoot-disable-plugin'] ] );

			update_option( 'health-check-allowed-plugins', $allowed_plugins );

			wp_redirect( remove_query_arg( $this->available_query_args ) );
			die();
		}

		// Toggle between the active theme and a default theme.
		if ( isset( $_GET['health-check-toggle-default-theme'] ) ) {
			if ( $this->default_theme ) {
				update_option( 'health-check-default-theme', 'no' );
			}
			else {
				update_option( 'health-check-default-theme', 'yes' );
			}

			wp_redirect( remove_query_arg( $this->available_query_args ) );
			die();
		}
	}

	/**
	 * Extend the admin bar.
	 *
	 * When in Troubleshooting Mode, introduce a new element to the admin bar to show
	 * enabled and disabled plugins (if conditions are met), switch between themes
	 * and disable Troubleshooting Mode altogether.
	 *
	 * @param WP_Admin_Bar $wp_menu
	 *
	 * @return void
	 */
	function health_check_troubleshoot_menu_bar( $wp_menu ) {
		if ( ! $this->is_troubleshooting() ) {
			return;
		}

		// Add top-level menu item.
		$wp_menu->add_menu( array(
			'id'    => 'health-check',
			'title' => esc_html__( 'Troubleshooting Mode', 'health-check' )
		) );

		$allowed_plugins = get_option( 'health-check-allowed-plugins', array() );

		// Add a link to manage plugins if there are more than 20 set to be active.
		if ( count( $allowed_plugins ) > 20 ) {
			$wp_menu->add_node( array(
				'id'     => 'health-check-plugins',
				'title'  => esc_html__( 'Manage active plugins', 'health-check' ),
				'parent' => 'health-check',
				'href'   => admin_url( 'plugins.php' )
			) );
		}
		else {
			$wp_menu->add_node( array(
				'id'     => 'health-check-plugins',
				'title'  => esc_html__( 'Plugins', 'health-check' ),
				'parent' => 'health-check'
			) );

			$wp_menu->add_group( array(
				'id'     => 'health-check-plugins-enabled',
				'parent' => 'health-check-plugins'
			) );
			$wp_menu->add_group( array(
				'id'     => 'health-check-plugins-disabled',
				'parent' => 'health-check-plugins'
			) );


			foreach ( $this->active_plugins as $single_plugin ) {
				$plugin_slug = explode( '/', $single_plugin );
				$plugin_slug = $plugin_slug[0];

				$enabled = true;

				if ( in_array( $plugin_slug, $allowed_plugins ) ) {
					$label = sprintf(
					// Translators: %s: Plugin slug.
						esc_html__( 'Disable %s', 'health-check' ),
						sprintf(
							'<strong>%s</strong>',
							$plugin_slug
						)
					);
					$url   = add_query_arg( array( 'health-check-troubleshoot-disable-plugin' => $plugin_slug ) );
				} else {
					$enabled = false;
					$label   = sprintf(
					// Translators: %s: Plugin slug.
						esc_html__( 'Enable %s', 'health-check' ),
						sprintf(
							'<strong>%s</strong>',
							$plugin_slug
						)
					);
					$url     = add_query_arg( array( 'health-check-troubleshoot-enable-plugin' => $plugin_slug ) );
				}

				$wp_menu->add_node( array(
					'id'     => sprintf(
						'health-check-plugin-%s',
						$plugin_slug
					),
					'title'  => $label,
					'parent' => ( $enabled ? 'health-check-plugins-enabled' : 'health-check-plugins-disabled' ),
					'href'   => $url
				) );
			}
		}

		$wp_menu->add_group( array(
			'id' => 'health-check-theme',
			'parent' => 'health-check'
		) );

		// Add a link to switch between active themes.
		$wp_menu->add_node( array(
			'id'     => 'health-check-default-theme',
			'title'  => ( $this->default_theme ? esc_html__( 'Use your current theme', 'health-check' ) : esc_html__( 'Use a default theme', 'health-check' ) ),
			'parent' => 'health-check-theme',
			'href'   => add_query_arg( array( 'health-check-toggle-default-theme' => true ) )
		) );

		$wp_menu->add_group( array(
			'id' => 'health-check-status',
			'parent' => 'health-check'
		) );

		// Add a link to disable Troubleshooting Mode.
		$wp_menu->add_node( array(
			'id'     => 'health-check-disable',
			'title'  => esc_html__( 'Disable Troubleshooting Mode', 'health-check' ),
			'parent' => 'health-check-status',
			'href'   => add_query_arg( array( 'health-check-disable-troubleshooting' => true ) )
		) );
	}

}

new Health_Check_Troubleshooting_MU();
