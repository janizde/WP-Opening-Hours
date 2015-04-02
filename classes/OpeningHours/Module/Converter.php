<?php
/**
 * Opening Hours: Module: Converter
 *
 * converts data from older plugin versions to new one
 */

namespace OpeningHours\Module;

use OpeningHours\Module\CustomPostType\MetaBox\Holidays as HolidaysMetaBox;
use OpeningHours\Module\CustomPostType\MetaBox\IrregularOpenings as IrregularOpeningsMetaBox;
use OpeningHours\Module\CustomPostType\MetaBox\OpeningHours as PeriodsMetaBox;
use OpeningHours\Module\CustomPostType\Set as SetCpt;

use DateTime;

class Converter extends AbstractModule {

	/**
	 * Constants
	 */
	const OPTION_PERIODS            = 'wp_opening_hours';
	const OPTION_HOLIDAYS           = 'wp_opening_hours_holidays';
	const OPTION_IRREGULAR_OPENINGS = 'wp_opening_hours_special_openings';
	const OPTION_SETTINGS           = 'wp_opening_hours_settings';

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

		$post_id  = static::insertNewSet();

		static::insertPeriods( $post_id );
		static::insertHolidays( $post_id );
		static::insertIrregularOpenings( $post_id );

		delete_option( static::OPTION_SETTINGS );

		static::$converted = true;

	}

	/**
	 * Insert New Set
	 * inserts the set-post
	 *
	 * @access      protected
	 * @static
	 * @return      int
	 */
	protected static function insertNewSet () {

		$args   = array(
			'post_title'    => __( 'OpeningHours', I18n::TEXTDOMAIN ),
			'post_status'   => 'publish',
			'post_type'     => SetCpt::CPT_SLUG,
		);

		return wp_insert_post( $args );

	}

	/**
	 * Insert Periods
	 * inserts the period option into the meta data for periods on the inserted post
	 *
	 * @access      protected
	 * @static
	 * @param       int         $post_id
	 */
	protected static function insertPeriods ( $post_id ) {

		/**
		 * TODO: Fix mechanism
		 */

		if ( !static::hasOld( static::OPTION_PERIODS ) )
			return;

		$meta     = get_option( static::OPTION_PERIODS );

		if ( !is_array( $meta ) )
			$meta   = json_decode( $meta, true );

		$days   = array( 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday' );

		$config = array();

		foreach ( $days as $n => $k ) :

			if ( !array_key_exists( $k, $meta ) )
				continue;

			if ( !array_key_exists( 'times', $meta[ $k ] ) or !is_array( $meta[ $k ]['times'] ) or !count( $meta[ $k ]['times'] ) )
				continue;

			$times  = $meta[ $k ]['times'];

			if ( count( $times[0] ) != count( $times[1] ) )
				continue;

			print_r( $times );

			for ( $i = 0; $i < count( $times[0] ); $i = $i + 2 ) :

				$config[]   = array(
					'weekday'   => $n,
					'timeStart' => $times[0][ $i ] . ':' . $times[0][ $i + 1 ],
					'timeEnd'   => $times[1][ $i ] . ':' . $times[1][ $i + 1 ],
					'dummy'     => false
				);

			endfor;

		endforeach;

		if ( count( $config ) )
			update_post_meta( $post_id, PeriodsMetaBox::PERIODS_META_KEY, $config );

	}

	/**
	 * Insert Holidays
	 *
	 * @access      protected
	 * @static
	 * @param       int         $post_id
	 */
	protected static function insertHolidays ( $post_id ) {

		if ( !static::hasOld( static::OPTION_HOLIDAYS ) )
			return;

		$meta     = get_option( static::OPTION_HOLIDAYS );

		if ( !is_array( $meta ) )
			$meta   = json_decode( $meta, true );

		$config   = array();

		foreach ( $meta as $h ) :

			if ( !is_array( $h ) or !count( $h ) )
				continue;

			if ( !array_key_exists( 'name', $h ) or !array_key_exists( 'start', $h ) or !array_key_exists( 'end', $h ) )
				continue;

			$dateStart  = new DateTime( static::removeBackslashes( $h['start'] ) );
			$dateEnd    = new DateTime( static::removeBackslashes( $h['end'] ) );

			$config[]   = array(
				'name'      => $h['name'],
				'dateStart' => $dateStart->format( I18n::STD_DATE_FORMAT ),
				'dateEnd'   => $dateEnd->format( I18n::STD_DATE_FORMAT )
			);

		endforeach;

		if ( !count( $config ) )
			return;

		update_post_meta( $post_id, HolidaysMetaBox::HOLIDAYS_META_KEY, $config );

	}

	/**
	 * Insert Irregular Openings
	 *
	 * @access      protected
	 * @static
	 * @param       int         $post_id
	 */
	protected static function insertIrregularOpenings ( $post_id ) {

		/**
		 * TODO: Fix mechanism
		 */

		if ( !static::hasOld( static::OPTION_IRREGULAR_OPENINGS ) )
			return;

		$meta     = get_option( static::OPTION_IRREGULAR_OPENINGS );

		if ( !is_array( $meta ) )
			$meta     = json_decode( $meta, true );

		if ( !count( $meta ) )
			return;

		$config   = array();

		foreach ( $meta as $io ) :

			if ( !is_array( $io ) )
				continue;

			if ( !array_key_exists( 'name', $io ) or !array_key_exists( 'day', $io ) or !array_key_exists( 'start', $io ) or !array_key_exists( 'end', $io ) )
				continue;

			$date   = new DateTime( static::removeBackslashes( $io['date'] ) );

			$config[]   = array(
				'name'      => $io['name'],
				'date'      => $date->format( I18n::STD_DATE_FORMAT ),
				'timeStart' => $io['start'],
				'timeEnd'   => $io['end']
			);

		endforeach;

		if ( !count( $config ) )
			return;

		update_post_meta( $post_id, IrregularOpeningsMetaBox::IRREGULAR_OPENINGS_META_KEY, $config );

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
			static::OPTION_IRREGULAR_OPENINGS,
			static::OPTION_SETTINGS
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

		if ( !is_array( $meta ) )
			$meta     = json_decode( $meta, true );

		if ( !is_array( $meta ) or !count( $meta ) ) :
			delete_option( $meta_key );
			return false;
		endif;

		return true;

	}

	/**
	 * Remove Backslashes
	 * removes backslashes from date strings
	 *
	 * @access      protected
	 * @static
	 * @param       string      $string
	 * @return      string
	 */
	protected static function removeBackslashes ( $string ) {

		return str_replace( '\\', '', $string );

	}
}