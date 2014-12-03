<?php
/**
 *  Opening Hours: Module: I18n
 */

namespace OpeningHours\Module;

use DateTime;
use DateTimeZone;

class I18n extends AbstractModule {

  /**
   *  Constants
   */
  const   LANGUAGE_PATH         = '/language/';
  const   STD_TIME_FORMAT       = 'H:i';
  const   STD_DATE_FORMAT       = 'Y-m-d';
  const   STD_TIME_FORMAT_REGEX = '([0-9]{1,2}:[0-9]{2})';
  const   STD_DATE_FORMAT_REGEX = '([0-9]{4}(-[0-9]{2}){2})';

  /**
   *  Date Format
   *
   *  @access     protected
   *  @static
   *  @type       string
   */
  protected static $dateFormat;

  /**
   *  Time Format
   *
   *  @access     protected
   *  @static
   *  @type       string
   */
  protected static $timeFormat;

  /**
   *  Date Time Zone
   *
   *  @access     protected
   *  @static
   *  @type       DateTimeZone;
   */
  protected static $dateTimeZone;

  /**
   *  Time Now
   *
   *  @access     protected
   *  @static
   *  @type       DateTime
   */
  protected static $timeNow;

  /**
   *  Constructor
   *
   *  @access       public
   */
  public function __construct() {

    self::setDateFormat( get_option( 'date_format' ) );
    self::setTimeFormat( get_option( 'time_format' ) );

    $this->registerHookCallbacks();

  }

  /**
   *  Register Hook Callbacks
   *
   *  @access       public
   */
  public function registerHookCallbacks() {

    add_action( 'plugins_loaded',       array( __CLASS__, 'registerTextdomain' ) );
    add_action( 'init',                 array( __CLASS__, 'init' ) );

  }

  /**
   *  Init
   *
   *  @access       public
   *  @static
   *  @wp_action    init
   */
  public static function init () {

    /**
     *  Get Timezone from wp_options.
     *  GMT offset timezone Settings are converted to string timezone identifiers
     *  n:30 GMT offset settings are floored to n:00!
     */
    if ( !empty( get_option( 'timezone_string' ) ) ) :
      $time_zone_string   = get_option( 'timezone_string' );

    elseif ( !empty( get_option( 'gmt_offset' ) ) ) :
      $offset             = floatval( floor( get_option( 'gmt_offset' ) ) ) * 3600;
      $time_zone_string   = timezone_name_from_abbr( null, $offset, 0 );

    endif;

    self::setDateTimeZone( new DateTimeZone( $time_zone_string ) );

    /**
     *  Save current time in property $timeNow
     */
    self::setTimeNow( new DateTime( 'now', self::getDateTimeZone() ) );

  }

  /**
   *  Register Textdomain
   *
   *  @access       public
   *  @static
   *  @wp_action    plugins_loaded
   */
  public static function registerTextdomain() {

    load_plugin_textdomain( self::TEXTDOMAIN, false, 'wp-opening-hours' . self::LANGUAGE_PATH );

  }

  /**
   *  Is Valid Time
   *
   *  @access       public
   *  @static
   *  @param        string    $time
   *  @return       bool
   */
  public static function isValidTime ( $time ) {
    return ( preg_match( self::STD_TIME_FORMAT_REGEX, $time ) === 1 );
  }

  /**
   *  Apply Time Zone
   *
   *  @access       public
   *  @static
   *  @param        DateTime  $dateTime
   *  @return       DateTime
   */
  public static function applyTimeZone ( DateTime $dateTime ) {

    $dateTime->setTimezone( self::getDateTimeZone() );
    return $dateTime;
    
  }

  /**
   *  Getter: Date Format
   *
   *  @access       public
   *  @static
   *  @return       string
   */
  public static function getDateFormat () {
    return self::$dateFormat;
  }

  /**
   *  Setter: Date Format
   *
   *  @access       public
   *  @static
   *  @param        string    $dateFormat
   *  @return       I18n
   */
  public static function setDateFormat ( $dateFormat ) {
    self::$dateFormat = $dateFormat;
  }

  /**
   *  Getter: Time Format
   *
   *  @access       public
   *  @static
   *  @return       string
   */
  public static function getTimeFormat () {
    return self::$timeFormat;
  }

  /**
   *  Setter: Time Format
   *
   *  @access       public
   *  @static
   *  @param        string    $timeFormat
   *  @return       I18n
   */
  public static function setTimeFormat ( $timeFormat ) {
    self::$timeFormat = $timeFormat;
  }

  /**
   *  Getter: Date Time Zone
   *
   *  @access       public
   *  @static
   *  @return       DateTimeZone
   */
  public static function getDateTimeZone () {
    return self::$dateTimeZone;
  }

  /**
   *  Setter: Date Time Zone
   *
   *  @access       protected
   *  @static
   *  @param        DateTimeZone  $dateTimeZone
   */
  protected static function setDateTimeZone ( DateTimeZone $dateTimeZone ) {
    self::$dateTimeZone = $dateTimeZone;
  }

  /**
   *  Getter: Time Now
   *
   *  @access       public
   *  @static
   *  @return       DateTime
   */
  public static function getTimeNow () {
    return self::$timeNow;
  }

  /**
   *  Setter: Time Now
   *
   *  @access       protected
   *  @static
   *  @param        DateTime    $timeNow
   */
  protected static function setTimeNow ( DateTime $timeNow ) {
    self::$timeNow = $timeNow;
  }

  /**
   *  Get Weekdays Array
   *
   *  @access       public
   *  @static
   *  @return       array
   */
  public static function getWeekdays () {
    return array(
      'monday'      => __( 'Monday', self::TEXTDOMAIN ),
      'tuesday'     => __( 'Tuesday', self::TEXTDOMAIN ),
      'wednesday'   => __( 'Wednesday', self::TEXTDOMAIN ),
      'thursday'    => __( 'Thursday', self::TEXTDOMAIN ),
      'friday'      => __( 'Friday', self::TEXTDOMAIN ),
      'saturday'    => __( 'Saturday', self::TEXTDOMAIN ),
      'sunday'      => __( 'Sunday', self::TEXTDOMAIN )
    );
  }

  /**
   *  Get Weekdays Numeric
   *
   *  @access       public
   *  @static
   *  @return       array
   */
  public static function getWeekdaysNumeric () {
    return array_values( self::getWeekdays() );
  }
}
?>
