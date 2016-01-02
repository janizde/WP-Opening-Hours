<?php
/*
Plugin Name: Opening Hours
Plugin URI:  http://www.jannikportz.de/wp-opening-hours-plugin/
Description: Manage your venue's Opening Hours in WordPress
Version:     2.0
Author:      Jannik Portz (@janizde)
Author URI:  http://jannikportz.de
*/

if ( !defined( 'ABSPATH' ) )
	die( 'Access denied.' );

define( 'OP_NAME', 'Opening Hours' );
define( 'OP_REQUIRED_PHP_VERSION', '5.3' );
define( 'OP_REQUIRED_WP_VERSION', '3.1' );

require_once( 'includes/admin-notice-helper/admin-notice-helper.php' );
require_once( 'includes/wp-detail-fields/detail-fields.php' );

function op_admin_notice_php () {
	$string = __( 'Plugin Opening Hours requires at least PHP Version %s. Your Installation of WordPress is currently running on PHP %s', 'opening-hours' );
	add_notice( sprintf( $string, OP_REQUIRED_PHP_VERSION, PHP_VERSION ) );
}

function op_admin_notice_wp() {
	global $wp_version;
	$string = __( 'Plugin Opening Hours requires at least WordPress version %s. Your Installation of WordPress is running on WordPress %s', 'opening-hours' );
	add_notice( sprintf( $string, OP_REQUIRED_WP_VERSION, $wp_version )
	);
}

/**
 * Checks if the system requirements are met
 * @return      bool      Whether System requirements are met
 */
function op_requirements_met () {
	global $wp_version;

	if ( version_compare( PHP_VERSION, OP_REQUIRED_PHP_VERSION, '<' ) ) {
		add_action( 'init', 'op_admin_notice_php' );
		return false;
	}

	if ( version_compare( $wp_version, OP_REQUIRED_WP_VERSION, '<' ) ) {
		add_action( 'init', 'op_admin_notice_wp' );
		return false;
	}

	return true;
}

/** Returns Plugin Directory Path */
function op_plugin_path() {
	return plugin_dir_path( __FILE__ );
}

/** ReturnsBootstrap File Path */
function op_bootstrap_file() {
	return __FILE__;
}

/**
 * Autoloader for Plugin classes
 * @param       string    $className  Name of the class that shall be loaded
 */
function op_autoload( $className ) {
	$filepath = op_plugin_path() . 'classes/' . str_replace( '\\', '/', $className ) . '.php';

	if ( file_exists( $filepath ) )
		require_once( $filepath );
}

spl_autoload_register( 'op_autoload' );

/**
 * Check requirements and load main class
 * The main program needs to be in a separate file that only gets loaded if the plugin requirements are met. Otherwise older PHP installations could crash when trying to parse it.
 */
if ( op_requirements_met() )
	require_once( 'run.php' );