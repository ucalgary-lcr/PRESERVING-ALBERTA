<?php

/**
 * Main Views controller.
 *
 * @since 2.5.0
 */
class WPV_Main {

	public function init() {
		$this->add_hooks();
	}

	public function add_hooks() {
		add_action( 'toolset_common_loaded', array( $this, 'register_autoloaded_classes' ) );

		add_action( 'toolset_common_loaded', array( $this, 'initialize_classes' ) );
	}

	public function initialize_classes() {
		WPV_WPML_Integration::initialize();

		$admin_help = new WPV_Controller_Admin_Help();
		$admin_help->init();
	}

	/**
	 * Register Views classes with Toolset_Common_Autoloader.
	 *
	 * @since 2.5.0
	 */
	public function register_autoloaded_classes() {
		// It is possible to regenerate the classmap with Zend framework, for example:
		//
		// cd application
		// /srv/www/ZendFramework-2.4.9/bin/classmap_generator.php --overwrite
		$classmap = include( WPV_PATH . '/application/autoload_classmap.php' );

		do_action( 'toolset_register_classmap', $classmap );
	}
}
