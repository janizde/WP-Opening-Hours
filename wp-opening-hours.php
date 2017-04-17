<?php
/*
 * Plugin Name: Opening Hours
 * Plugin URI: https://github.com/janizde/WP-Opening-Hours
 * Description: Manage your venue's Opening Hours, Holidays and Irregular Openings in WordPress and display them in many different Widgets and Shortcodes
 * Version: 2.0.5
 * Author: Jannik Portz
 * Author URI: http://jannikportz.de
 * Text Domain: wp-opening-hours
 * Domain Path: /language
 */

if (!defined('ABSPATH'))
  die('Access denied.');

define('OP_NAME', 'Opening Hours');
define('OP_REQUIRED_PHP_VERSION', '5.3');
define('OP_REQUIRED_WP_VERSION', '4.0');

require_once dirname(__FILE__) . '/includes/admin-notice-helper/admin-notice-helper.php';

function op_admin_notice_php () {
  $string = __('Plugin Opening Hours requires at least PHP Version %s. Your Installation of WordPress is currently running on PHP %s', 'wp-opening-hours');
  add_notice(sprintf($string, OP_REQUIRED_PHP_VERSION, PHP_VERSION));
}

function op_admin_notice_wp () {
  global $wp_version;
  $string = __('Plugin Opening Hours requires at least WordPress version %s. Your Installation of WordPress is running on WordPress %s', 'wp-opening-hours');
  add_notice(sprintf($string, OP_REQUIRED_WP_VERSION, $wp_version)
  );
}

/**
 * Checks if the system requirements are met
 *
 * @return      bool      Whether System requirements are met
 */
function op_requirements_met () {
  global $wp_version;

  if (version_compare(PHP_VERSION, OP_REQUIRED_PHP_VERSION, '<')) {
    add_action('admin_init', 'op_admin_notice_php');
    return false;
  }

  if (version_compare($wp_version, OP_REQUIRED_WP_VERSION, '<')) {
    add_action('admin_init', 'op_admin_notice_wp');
    return false;
  }

  return true;
}

/** Returns Plugin Directory Path */
function op_plugin_path () {
  return plugin_dir_path(__FILE__);
}

/**
 * Returns the absolute path of the specified view
 *
 * @param       string $view view path relative to views directory
 *
 * @return      string              absolute path to view
 */
function op_view_path ($view) {
  return op_plugin_path() . 'views/' . $view;
}

/** ReturnsBootstrap File Path */
function op_bootstrap_file () {
  return __FILE__;
}

/**
 * Autoloader for Plugin classes
 *
 * @param       string $className Name of the class that shall be loaded
 */
function op_autoload ($className) {
  $filepath = op_plugin_path() . 'classes/' . str_replace('\\', '/', $className) . '.php';

  if (file_exists($filepath))
    require_once($filepath);
}

spl_autoload_register('op_autoload');

/**
 * Check requirements and load main class
 * The main program needs to be in a separate file that only gets loaded if the plugin requirements are met. Otherwise
 * older PHP installations could crash when trying to parse it.
 */
if (op_requirements_met())
  require_once('run.php');