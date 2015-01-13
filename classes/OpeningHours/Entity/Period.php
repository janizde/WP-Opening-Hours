<?php
/**
 *  Opening Hours: Entity: Period
 */

namespace OpeningHours\Entity;

use OpeningHours\Module\I18n;

use DateTime;
use DateInterval;
use DateTimeZone;

class Period {

  /**
   * Config
   * sequential config array
   *
   * @access     protected
   * @type       array
   */
  protected $config;

  /**
   * Weekday
   * weekdays represented by integer. Monday: 0 - Sunday: 7
   *
   * @access     protected
   * @type       int
   */
  protected $weekday;

  /**
   * Time Start
   * DateTime object representing the period's start time in the current week
   *
   * @access     protected
   * @type       DateTime
   */
  protected $timeStart;

  /**
   * Time End
   * DateTime object representing the period's end time in the current week
   *
   * @access     protected
   * @type       DateTime
   */
  protected $timeEnd;

  /**
   * Time Difference
   * DateInterval representing the difference between timeStart and timeEnd
   * Automatically updated in setters for timeStart and timeEnd
   *
   * @access     protected
   * @type       DateInterval
   */
  protected $timeDifference;

  /**
   * Is Dummy
   * Flags Period as dummy
   *
   * @access     protected
   * @type       bool
   */
  protected $isDummy;

  /**
   * Constructor
   *
   * @access     public
   * @param      array     $config
   * @return     Period
   */
  public function __construct ( $config = array() ) {

    if ( $config !== null and count( $config ) ) :
      $this->setConfig( $config );
    else :
      $this->setConfig( array(
        'weekday'   => null,
        'timeStart' => null,
        'timeEnd'   => null,
        'dummy'     => true
      ) );
    endif;

    $this->setUp();

    add_action( I18n::WP_ACTION_TIMEZONE_LOADED, array(
      $this,
      'updateDateTimezone'
    ) );

    add_action( I18n::WP_ACTION_TIMEZONE_LOADED, array(
      $this,
      'updateWeekContext'
    ) );

  }

  /**
   * Set Up
   *
   * @access     public
   * @return     Period
   * @throws     InvalidArgumentException
   */
  public function setUp () {

    $config   = $this->getConfig();

    if ( !is_array( $config ) )
      throw new InvalidArgumentException( sprintf( '$config is not an array in Set %d', $this->getId() ) );

    $defaultConfig  = array(
      'weekday'   => null,
      'timeStart' => null,
      'timeEnd'   => null,
      'dummy'     => false
    );

    $config   = wp_parse_args( $config, $defaultConfig );

    extract( $config );

    /**
     * Variables defined by extract
     *
     * @var     $weekday      int representing weekday (1-7)
     * @var     $timeStart    string|DateTime w/ time that the period starts
     * @var     $timeEnd      string|DateTime w/ time that the period ends
     * @var     $dummy        bool whether period is a dummy or not
     */

    $this->setWeekday( $weekday );
    $this->setTimeStart( $timeStart );
    $this->setTimeEnd( $timeEnd );
    $this->setIsDummy( $dummy );

    $this->updateDateTimezone();

    return $this;

  }

  /**
   *  Is Open
   *
   *  @access     public
   *  @return     bool
   */
  public function isOpen () {

    $is_open  = ( $this->getTimeStart() <= I18n::getTimeNow() and I18n::getTimeNow() <= $this->getTimeEnd() );

    return $is_open;

  }

  /**
   *  Update Time Difference
   *
   *  @access     public
   */
  public function updateTimeDifference () {

    if ( !$this->getTimeStart() instanceof DateTime or !$this->getTimeEnd() instanceof DateTime )
      return;

    $timeDifference = $this->getTimeEnd()->diff( $this->getTimeStart() );

    $this->timeDifference = $timeDifference;

  }

  /**
   * Update Week Context
   * applies week context on start and end time
   * handles periods that exceed midnight
   *
   * @access      public
   */
  public function updateWeekContext () {

    if ( !$this->getTimeStart() instanceof DateTime or !$this->getTimeEnd() instanceof DateTime )
      return;

    I18n::applyWeekContext( $this->getTimeStart(), $this->getWeekday() );
    I18n::applyWeekContext( $this->getTimeEnd(), $this->getWeekday() );

    if ( $this->getTimeStart()->getTimestamp() >= $this->getTimeEnd()->getTimestamp() ) :

      // Add one day
      $this->getTimeEnd()->add( new DateInterval( 'P1D' ) );
    endif;

  }

  /**
   * Update DateTimeZone
   * updates the DateTimeZone on timeStart and timeEnd
   *
   * @access      public
   */
  public function updateDateTimezone () {

    $timezone   = I18n::getDateTimeZone();

    if ( !$timezone instanceof DateTimeZone )
      return;

    /**
     * Instantiate new DateTime objects to keep time
     * otherwise time would be converted
     */

    $timeStart  = new DateTime(
      $this->getTimeStart()->format( I18n::STD_DATE_TIME_FORMAT ),
      $timezone
    );

    $this->setTimeStart( $timeStart );

    $timeEnd    = new DateTime(
      $this->getTimeEnd()->format( I18n::STD_DATE_TIME_FORMAT ),
      $timezone
    );

    $this->setTimeEnd( $timeEnd );

  }

  /**
   * Sort Strategy
   * sorts period by day and time
   *
   * @access        public
   * @static
   * @param         Period      $period_1
   * @param         Period      $period_2
   * @return        int
   */
  public static function sortStrategy ( Period $period_1, Period $period_2 ) {

    if ( $period_1->getTimeStart() < $period_2->getTimeStart() ) :
      return -1;

    elseif ( $period_1->getTimeStart() > $period_2->getTimeStart() ) :
      return 1;

    else :
      return 0;

    endif;

  }

  /**
   *  Get Formatted Time Range
   *
   *  @access     public
   *  @return     string
   */
  public function getFormattedTimeRange () {

    return $this->getTimeStart( true ) . ' – ' . $this->getTimeEnd( true );

  }

  /**
   *  Getter: Config
   *
   *  @access     public
   *  @return     array
   */
  public function getConfig () {
    return $this->config;
  }

  /**
   *  Setter: Config
   *
   *  @access     public
   *  @param      array   $config
   *  @return     Period
   */
  public function setConfig ( array $config ) {
    $this->config = $config;
    return $this;
  }

  /**
   *  Getter: Weekday
   *
   *  @access     public
   *  @return     int
   */
  public function getWeekday () {
    return $this->weekday;
  }

  /**
   *  Setter: Weekday
   *
   *  @access     public
   *  @param      int     $weekday
   *  @return     Period
   */
  public function setWeekday ( $weekday ) {
    $this->weekday = $weekday;
    return $this;
  }

  /**
   *  Getter: Time Start
   *
   *  @access     public
   *  @param      bool    $formatted
   *  @return     DateTime|string
   */
  public function getTimeStart ( $formatted = false ) {
    return ( $formatted and $this->timeStart instanceof DateTime )
      ? $this->timeStart->format( I18n::getTimeFormat() )
      : $this->timeStart;
  }

  /**
   *  Setter: Time Start
   *
   *  @access     public
   *  @param      DateTime|string   $timeStart
   *  @return     Set
   */
  public function setTimeStart ( $timeStart ) {
    if ( is_string( $timeStart ) ) :
      $date_time = new DateTime( $timeStart, I18n::getDateTimeZone() );
    elseif ( $timeStart instanceof DateTime ) :
      $date_time = I18n::applyTimeZone( $timeStart );
    endif;

    $this->timeStart = $date_time;

    $this->updateTimeDifference();
    $this->updateWeekContext();

    return $this;
  }

  /**
   *  Getter: Time End
   *
   *  @access     public
   *  @param      bool    $formatted
   *  @return     DateTime|string
   */
  public function getTimeEnd ( $formatted = false ) {
    return ( $formatted and $this->timeEnd instanceof DateTime )
      ? $this->timeEnd->format( I18n::getTimeFormat() )
      : $this->timeEnd;
  }

  /**
   *  Setter: Time End
   *
   *  @access     public
   *  @param      DateTime|string   $timeEnd
   *  @return     Set
   */
  public function setTimeEnd ( $timeEnd ) {

    if ( is_string( $timeEnd ) ) :
      $date_time = new DateTime( $timeEnd, I18n::getDateTimeZone() );
    elseif ( $timeEnd instanceof DateTime ) :
      $date_time = I18n::applyTimeZone( $timeEnd );
    endif;

    $this->timeEnd  = $date_time;

    $this->updateTimeDifference();
    $this->updateWeekContext();

    return $this;
  }

  /**
   *  Getter: Is Dummy
   *
   *  @access     public
   *  @return     bool
   */
  public function isDummy () {
    return $this->isDummy;
  }

  /**
   *  Setter: Is Dummy
   *
   *  @access     public
   *  @param      bool    $isDummy
   *  @return     Period
   */
  public function setIsDummy ( $isDummy ) {
    $this->isDummy = $isDummy;
  }

}
?>
