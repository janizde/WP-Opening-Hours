<?php
/*
Plugin Name: Opening Hours
Plugin URI:  http://www.jannikportz.de/wp-opening-hours-plugin/
Description: Manage your venue's Opening Hours in WordPress
Version:     2.0
Author:      Jannik Portz (@janizde)
Author URI:  http://jannikportz.de
*/

/*
 * This plugin was built on top of WordPress-Plugin-Skeleton by Ian Dunn.
 * See https://github.com/iandunn/WordPress-Plugin-Skeleton for details.
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

define( 'OP_NAME',                 'Opening Hours' );
define( 'OP_REQUIRED_PHP_VERSION', '5.3' );                          // because of get_called_class()
define( 'OP_REQUIRED_WP_VERSION',  '3.1' );                          // because of esc_textarea()

/**
 * Checks if the system requirements are met
 *
 * @return bool True if system requirements are met, false if not
 */
function op_requirements_met() {
	global $wp_version;
	//require_once( ABSPATH . '/wp-admin/includes/plugin.php' );		// to get is_plugin_active() early

	if ( version_compare( PHP_VERSION, OP_REQUIRED_PHP_VERSION, '<' ) ) {
		return false;
	}

	if ( version_compare( $wp_version, OP_REQUIRED_WP_VERSION, '<' ) ) {
		return false;
	}

	/*
	if ( ! is_plugin_active( 'plugin-directory/plugin-file.php' ) ) {
		return false;
	}
	*/

	return true;
}

/**
 * Prints an error that the system requirements weren't met.
 */
function op_requirements_error() {
	global $wp_version;

	require_once( dirname( __FILE__ ) . '/views/requirements-error.php' );
}

/**
 *	Returns Plugin Directory Path
 */
function op_plugin_path () {
	return plugin_dir_path( __FILE__ );
}

/**
 *	Bootstrap File Path
 *
 *	@return 	string
 */
function op_bootstrap_file () {
	return __FILE__;
}

/**
 * OP Autoload
 *
 * @param			string			$class_name
 */
function op_autoload ( $class_name ) {

	$filepath 	= op_plugin_path() . 'classes/' . str_replace( '\\', '/', $class_name ) . '.php';

	if ( file_exists( $filepath ) ) :
		require_once( $filepath );
	endif;

}

spl_autoload_register( 'op_autoload' );

/*
 * Check requirements and load main class
 * The main program needs to be in a separate file that only gets loaded if the plugin requirements are met. Otherwise older PHP installations could crash when trying to parse it.
 */
if ( op_requirements_met() ) {

	require_once( __DIR__ . '/includes/admin-notice-helper/admin-notice-helper.php' );
	require_once( __DIR__ . '/includes/wp-detail-fields/detail-fields.php' );

	if ( class_exists( 'OpeningHours\OpeningHours' ) ) {
		$GLOBALS['op'] = OpeningHours\OpeningHours::getInstance();
		register_activation_hook( __FILE__, array( $GLOBALS['op'], 'activate' ) );
		register_deactivation_hook( __FILE__, array( $GLOBALS['op'], 'deactivate' ) );
	}
} else {
	add_action( 'admin_notices', 'op_requirements_error' );
}
