<?php
/**
 *	Opening Hours: Module: Asbtract Module
 */

namespace OpeningHours\Module;

if ( class_exists( 'OpeningHours\Module\AbstractModule' ) )
	return;

abstract class AbstractModule {

	/**
	 *	Constants
	 */
	const 	TEXTDOMAIN 	= 'opening-hours';

	/**
	 *	Instances
	 *
	 *	@access 			private
	 *	@type 				array
	 */
	private static $instances = array();


	/**
	 *	Provides access to a single instance of a module using the singleton pattern
	 *
	 * 	@access 				public
	 *	@static
	 * 	@return  				OP_Module
	 */
	public static function getInstance() {
		$module = get_called_class();

		if ( !isset( self::$instances[ $module ] ) ) {
			self::$instances[ $module ] = new $module();
		}

		return self::$instances[ $module ];
	}

	/**
	 * Render a template
	 *
	 * Allows parent/child themes to override the markup by placing the a file named basename( $default_template_path ) in their root folder,
	 * and also allows plugins or themes to override the markup by a filter. Themes might prefer that method if they place their templates
	 * in sub-directories to avoid cluttering the root folder. In both cases, the theme/plugin will have access to the variables so they can
	 * fully customize the output.
	 *
	 * 	@param  				string 			$default_template_path
	 * 	@param  				array  			$variables
	 * 	@param  				string 			$require
	 * 	@return 				string
	 */
	protected static function renderTemplate( $default_template_path = false, $variables = array(), $require = 'once' ) {
		do_action( 'op_render_template_pre', $default_template_path, $variables );

		$template_path = locate_template( basename( $default_template_path ) );

		if ( !$template_path )
			$template_path = op_plugin_path() . 'views/' . $default_template_path;

		$template_path = apply_filters( 'op_template_path', $template_path );

		if ( is_file( $template_path ) ) {
			extract( $variables );
			ob_start();

			if ( 'always' == $require ) {
				require( $template_path );
			} else {
				require_once( $template_path );
			}

			$template_content = apply_filters( 'op_template_content', ob_get_clean(), $default_template_path, $template_path, $variables );
		} else {
			$template_content = '';
		}

		do_action( 'op_render_template_post', $default_template_path, $variables, $template_path, $template_content );

		return $template_content;
	}

}
