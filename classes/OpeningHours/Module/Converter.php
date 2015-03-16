<?php
/**
 * Opening Hours: Module: Converter
 *
 * converts data from older plugin versions to new one
 */

namespace OpeningHours\Module;

use OpeningHours\Module\CustomPostType\Set as SetCpt;

class Converter extends AbstractModule {

	/**
	 * Constants
	 */
	const OPTION_PERIODS            = 'wp_opening_hours';
	const OPTION_HOLIDAYS           = 'wp_opening_hours_holidays';
	const OPTION_IRREGULAR_OPENINGS = 'wp_opening_hours_special_openings';

	/**
	 * Converted
	 * variable to determine if data has been converted during current runtime to prevent admin notice from showing up
	 *
	 * @access      protected
	 * @static
	 */
	protected static $converted = false;

	/**
	 * Constructor
	 *
	 * @access      public
	 */
	public function __construct () {

		static::registerHookCallbacks();

	}

	/**
	 * Register Hook Callbacks
	 *
	 * @access      protected
	 * @static
	 */
	protected static function registerHookCallbacks () {

			add_action( 'admin_init', array( get_called_class(), 'detectConverterAction' ) );
			add_action( 'admin_init', array( get_called_class(), 'detectOldData' ) );

	}

	/**
	 * Register Admin Notice
	 *
	 * @access      protected
	 * @static
	 */
	protected static function registerAdminNotice () {

		$convert_url      = admin_url( sprintf( 'edit.php?post_type=%s&op-converter=%s', SetCpt::CPT_SLUG, 'convert' ) );

		$button_convert   = sprintf( '<a href="%s" class="button button-convert-data button-primary">%s</a>',
			$convert_url,
			__( 'Convert Data', I18n::TEXTDOMAIN )
		);

		$delete_url       = admin_url( sprintf( 'edit.php?post_type=%s&op-converter=%s', SetCpt::CPT_SLUG, 'delete' ) );

		$button_delete    = sprintf( '<a href="%s" class="button button-delete-data">%s</a>',
			$delete_url,
			__( 'Delete Data', I18n::TEXTDOMAIN )
		);

		add_notice( sprintf( '<b>%s:</b> %s<div class="op-admin-notice-buttons">%s %s</div>',
			__( 'Opening Hours', I18n::TEXTDOMAIN ),
			__( 'There is data from older versions of the Opening Hours Plugin in your WordPress installation that is not compatible with the currently installed version. Would you like to convert this data?', I18n::TEXTDOMAIN ),
			$button_convert,
			$button_delete
		) );

	}

	/**
	 * Detect Old Data
	 * detects whether old data is present and registers admin notice
	 *
	 * @access      public
	 * @static
	 * @wp_hook     admin_init
	 */
	public static function detectOldData () {

		if ( static::$converted )
			return;

		if ( !( static::hasOld( static::OPTION_PERIODS ) or static::hasOld( static::OPTION_HOLIDAYS ) or static::hasOld( static::OPTION_IRREGULAR_OPENINGS ) ) )
			return;

		static::registerAdminNotice();

	}

	/**
	 * Detect Converter Action
	 * detects whether URL contains converter-action parameter and calls corresponding methods
	 *
	 * @access      public
	 * @static
	 * @wp_hook     admin_init
	 */
	public static function detectConverterAction () {

		if ( !isset( $_GET['op-converter'] ) )
			return;

		switch ( $_GET['op-converter'] ) :

			case 'convert' :
				static::convertData();
				break;

			case 'delete' :
				static::deleteData();
				break;

			default :
				return;

		endswitch;

	}

	/**
	 * Convert Data
	 * converts old data to new data
	 *
	 * @access      protected
	 * @static
	 */
	protected static function convertData () {

		/**
		 * TODO: Write converter method
		 */

	}

	/**
	 * Delete Data
	 * delete old data
	 *
	 * @access      protected
	 * @static
	 */
	protected static function deleteData () {

		foreach ( array(
			static::OPTION_PERIODS,
			static::OPTION_HOLIDAYS,
			static::OPTION_IRREGULAR_OPENINGS
		) as $key )

			delete_option( $key );

		static::$converted = true;

		add_notice( __( 'The old data has successfully been deleted', I18n::TEXTDOMAIN ) );

	}

	/**
	 * Has Old
	 *
	 * @access      public
	 * @static
	 * @param       string      $meta_key
	 * @return      bool
	 */
	public static function hasOld ( $meta_key ) {

		$meta     = get_option( $meta_key );

		if ( $meta === false )
			return false;

		$meta     = json_decode( $meta, true );

		if ( !is_array( $meta ) or !count( $meta ) ) :
			delete_option( $key );
			return false;
		endif;

		return true;

	}
}