<?php

namespace OpeningHours\Module;

use DateTime;
use DateTimeZone;
use DateInterval;

/**
 * I18n Module
 *
 * @author      Jannik Portz
 * @package     OpeningHours\Module
 * @todo        static attributes to singleton attributes
 */
class I18n extends AbstractModule {

	/** Path to the language directory */
	const LANGUAGE_PATH = '/language/';

	/** Standard time format */
	const STD_TIME_FORMAT = 'H:i';

	/** Standard date format */
	const STD_DATE_FORMAT = 'Y-m-d';

	/** Standard date-time format */
	const STD_DATE_TIME_FORMAT = 'Y-m-d H:i';

	/** Regular expression recognizing time in standard time format */
	const STD_TIME_FORMAT_REGEX = '([0-9]{1,2}:[0-9]{2})';

	/** Regular expression recognizing date in standard date format */
	const STD_DATE_FORMAT_REGEX = '([0-9]{4}(-[0-9]{2}){2})';

	/** Hook for action that is performed, when the timezone has been loaded */
	const WP_ACTION_TIMEZONE_LOADED = 'op_timezone_loaded';

	/**
	 * Custom date format
	 * @var       string
	 */
	protected static $dateFormat;

	/**
	 * Custom time format
	 * @var       string
	 */
	protected static $timeFormat;

	/**
	 * Current timezone
	 * @var       DateTimeZone
	 */
	protected static $dateTimeZone;

	/**
	 * Current DateTime
	 * @var       DateTime
	 */
	protected static $timeNow;

	/** Constructor */
	public function __construct () {
		self::$timeFormat = get_option('time_format');
		self::$dateFormat = get_option('date_format');

		$this->registerHookCallbacks();
	}

	/** Registers Hook Callbacks */
	public function registerHookCallbacks () {
		add_action( 'plugins_loaded', array( $this, 'registerTextdomain' ) );
		add_action( 'init', array( $this, 'init' ) );
	}

	/** Initializes time zone etc */
	public function init () {
		/**
		 * Get Timezone from wp_options.
		 * GMT offset timezone Settings are converted to string timezone identifiers
		 * n:30 GMT offset settings are floored to n:00!
		 */
		$timezone_string = get_option( 'timezone_string' );
		$gmt_offset = get_option( 'gmt_offset' );

		if ( !empty( $gmt_offset ) and empty( $timezone_string ) ) {
			$offset = floatval( floor( get_option( 'gmt_offset' ) ) ) * 3600;
			$timezone_string = timezone_name_from_abbr( null, $offset, 0 );
		}

		self::$dateTimeZone = new DateTimeZone( $timezone_string );
		date_default_timezone_set( $timezone_string );

		self::$timeNow = new DateTime( 'now', self::getDateTimeZone() );

		do_action( self::WP_ACTION_TIMEZONE_LOADED );
	}

	/** Registers Plugin Textdomain */
	public function registerTextdomain () {
		load_plugin_textdomain( self::TEXTDOMAIN, false, 'wp-opening-hours' . self::LANGUAGE_PATH );
	}

	/**
	 * Checks whether the provided time string is in standard time format
	 *
	 * @param     string    $time     The time string to be checked
	 *
	 * @return    bool                Whether $time is in standard time format or not
	 */
	public static function isValidTime( $time ) {
		return preg_match( self::STD_TIME_FORMAT_REGEX, $time ) === 1;
	}

	/**
	 * Merges the date of $date into the $time DateTime instance
	 *
	 * @param     DateTime  $date     The date to be merged into time
	 * @param     DateTime  $time     The time to merge the date into
	 *
	 * @return    DateTime            The $time with the date attributes from $date
	 */
	public static function mergeDateIntoTime ( DateTime $date, DateTime $time ) {
		$time->setDate(
			(int) $date->format( 'Y' ),
			(int) $date->format( 'm' ),
			(int) $date->format( 'd' )
		);

		return $time;
	}

	/**
	 * Applies the current time zone to a DateTime object
	 *
	 * @param     DateTime  $dateTime The date whose timezone to set
	 *
	 * @return    DateTime            $dateTime with the current timezone applied
	 */
	public static function applyTimeZone ( DateTime $dateTime ) {
		return $dateTime->setTimezone( self::getDateTimeZone() );
	}

	/**
	 * Sets the date of a DateTime object to a specific weekday in the current week
	 *
	 * @param     DateTime  $dateTime The DateTime whose date to update
	 * @param     int       $weekday  The numeric representation of the weekday
	 *
	 * @return    DateTime            $dateTime with updated date attributes
	 */
	public static function applyWeekContext( DateTime $dateTime, $weekday ) {
		if ( $weekday < 0 or $weekday > 6 )
			return $dateTime;

		$now = I18n::getTimeNow();
		$today = (int) $now->format( 'N' );
		$offset = ( $weekday + 8 - $today ) % 7;
		$interval = new DateInterval( 'P' . $offset . 'D' );

		$dateTime->setDate(
			(int) $now->format( 'Y' ),
			(int) $now->format( 'm' ),
			(int) $now->format( 'd' )
		);

		return $dateTime->add( $interval );
	}

	/**
	 * Checks whether the provided weekday is equal to today's weekday
	 *
	 * @param     int       $day      The weekday to check for in numeric representation
	 *
	 * @return    bool                Whether $day equals today's weekday
	 */
	public static function isToday ( $day ) {
		if ( !is_numeric( $day ) )
			return false;

		$dateTime = self::getTimeNow();
		return $dateTime->format( 'N' ) == $day ;
	}

	/**
	 * Getter: Date Format
	 * @return    string
	 */
	public static function getDateFormat () {
		return self::$dateFormat;
	}

	/**
	 * Setter: Date Format
	 * @param     string    $dateFormat
	 */
	public static function setDateFormat ( $dateFormat ) {
		self::$dateFormat = $dateFormat;
	}

	/**
	 * Getter: Time Format
	 * @return    string
	 */
	public static function getTimeFormat () {
		return self::$timeFormat;
	}

	/**
	 * Setter: Time Format
	 * @param     string    $timeFormat
	 */
	public static function setTimeFormat ( $timeFormat ) {
		self::$timeFormat = $timeFormat;
	}

	/**
	 * Getter: Date Time Zone
	 * @return    DateTimeZone
	 */
	public static function getDateTimeZone () {
		return self::$dateTimeZone;
	}

	/**
	 * Getter: Time Now
	 * @return    DateTime
	 */
	public static function getTimeNow () {
		return self::$timeNow;
	}

	/**
	 * Returns an associative array representing the variables for JS translations
	 * @return    array     Associative array of translations with:
	 *                        key:    string w/ translation key
	 *                        value:  string w/ actual translation
	 */
	public static function getJavascriptTranslations() {
		return array(
			'tp_hour'   => __( 'Hour', static::TEXTDOMAIN ),
			'tp_minute' => __( 'Minute', static::TEXTDOMAIN )
		);
	}
}
