<?php
/**
 *  Opening Hours: Entity: Period
 */

namespace OpeningHours\Entity;

use OpeningHours\Module\I18n;
use OpeningHours\Module\OpeningHours;

use DateTime;
use DateInterval;
use DateTimeZone;
use InvalidArgumentException;

class Period {

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

    if ( $config === null or !count( $config ) ) :
      $config = array(
        'weekday'   => null,
        'timeStart' => null,
        'timeEnd'   => null,
        'dummy'     => true
      );
    endif;

    $this->setUp( $config );

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
   * @access      public
   * @param       array     $config
   * @throws      InvalidArgumentException
   */
  public function setUp ( array $config ) {

    if ( !is_array( $config ) )
      throw new InvalidArgumentException( '$config is not an array in Period' );

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

  }

  /**
   * Is Open Strict
   * checks if Period is currently open regardless of Holidays and SpecialOpenings
   *
   * @access      public
   * @param       DateTime      $now
   * @return      bool
   */
  public function isOpenStrict ( $now = null ) {

    if ( !$now instanceof DateTime or $now === null )
      $now  = I18n::getTimeNow();

    $is_open  = ( $this->getTimeStart() <= $now and $now <= $this->getTimeEnd() );

    return $is_open;

  }

  /**
   * Is Open
   * checks if Period is currently open also regarding Holidays and SpecialOpenings
   *
   * @access      public
   * @param       DateTime  $now
   * @param       int       $set_id
   * @return      bool
   */
  public function isOpen ( $now = null, $set_id = null ) {

    $set  = ( $set_id === null ) ? OpeningHours::getCurrentSet() : OpeningHours::getSet( $set_id );

    if ( $set instanceof Set and $set->isHolidayActive( $now ) )
      return false;

    return $this->isOpenStrict( $now );

  }

  /**
   * Will Be Open
   * checks whether Period will be regularly open and not overridden due to Holidays or Special Openings
   *
   * @access      public
   * @param       int       $set_id
   * @return      bool
   */
  public function willBeOpen ( $set_id = null ) {

    return $this->isOpen( $this->getTimeStart(), $set_id );

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
   * To String
   * returns json string from Period config
   *
   * @access      public
   * @return      string
   */
  public function __toString () {
    return json_encode( $this->getConfig() );
  }

  /**
   * Equals
   * compares this Period to another Period
   *
   * @access      public
   * @param       Period      $other
   * @param       bool        $ignore_day
   * @return      bool
   */
  public function equals ( Period $other, $ignore_day = false ) {

    $time_format  = 'Hi';

    if ( !$ignore_day and $this->getWeekday() != $other->getWeekday() )
      return false;

    if ( $this->getTimeStart()->format( $time_format ) != $other->getTimeStart()->format( $time_format ) )
      return false;

    if ( $this->getTimeEnd()->format( $time_format ) != $other->getTimeEnd()->format( $time_format ) )
      return false;

    return true;

  }

  /**
   * Compare Two ( static equals() )
   *
   * @access      public
   * @static
   * @param       Period      $period_1
   * @param       Period      $period_2
   * @param       bool        $ignore_day
   * @return      bool
   */
  public static function compareTwo ( Period $period_1, Period $period_2, $ignore_day = false ) {

    return $period_1->equals( $period_2, $ignore_day );

  }

  /**
   * Get Copy
   * returns a copy of the current Period and adds up a DateInterval
   *
   * @access      public
   * @param       DateInterval    $offset
   * @return      Period
   */
  public function getCopy ( DateInterval $offset ) {

    $period     = &$this;
    $period->getTimeStart()->add( $offset );
    $period->getTimeEnd()->add( $offset );

    return $period;

  }

  /**
   * Factory: Dummy Period
   *
   * @access      public
   * @static
   * @return      Period
   */
  public static function getDummyPeriod () {
    return new Period( array(
      'dummy'   => true
    ) );
  }

  /**
   * Getter: Config
   * generates config array
   *
   * @access      public
   * @return      array
   */
  public function getConfig () {

    $config   = array(
      'weekday'   => $this->getWeekday(),
      'timeStart' => $this->getTimeStart()->format( I18n::STD_DATE_TIME_FORMAT ),
      'timeEnd'   => $this->getTimeEnd()->format( I18n::STD_DATE_TIME_FORMAT ),
      'dummy'     => $this->isDummy()
    );

    return $config;

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