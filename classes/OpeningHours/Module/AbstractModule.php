<?php

namespace OpeningHours\Module;

/**
 * Abstraction for plugin module
 *
 * @author      Jannik Portz
 * @package     OpeningHours\Module
 */
abstract class AbstractModule {

	/**
	 * The gettext text domain used for plugin translations
	 */
	const TEXTDOMAIN = 'opening-hours';

	/**
	 * Collection of all singleton instances
	 * @var       AbstractModule[]
	 */
	private static $instances = array();

	/**
	 * Provides access to a single instance of a module using the singleton pattern
	 * @return        static
	 */
	public static function getInstance() {
		$class = get_called_class();

		if ( !isset( self::$instances[ $class ] ) )
			self::$instances[ $class ] = new $class();

		return self::$instances[ $class ];
	}

	/**
	 * Renders a template
	 *
	 * @todo      make non-static
	 *
	 * @param     string    $templatePath Path to the template file relative to plugin directory
	 * @param     array     $variables    Associative array of variables to expose to template file
	 * @param     string    $require      'once' or 'always'. Whether to require the template only once per runtime
	 *
	 * @return    string    The template markup
	 */
	public static function renderTemplate ( $templatePath, $variables = array(), $require = 'once' ) {
		do_action( 'op_render_template_pre', $templatePath, $variables );

		$templatePath = op_plugin_path() . 'views/' . $templatePath;
		$templatePath = apply_filters( 'op_template_path', $templatePath, $variables );

		if ( is_file( $templatePath ) ) {
			extract( $variables );
			ob_start();

			if ( $require === 'always' ) {
				require( $templatePath );
			} else {
				require_once( $templatePath );
			}

			$template_content = apply_filters( 'op_template_content', ob_get_clean(), $templatePath, $templatePath, $variables );
		} else {
			$template_content = '';
		}

		do_action( 'op_render_template_post', $templatePath, $variables, $templatePath, $template_content );
		return $template_content;
	}

}
