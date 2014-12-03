<?php
/**
 *  Opening Hours: Entity: Period
 */

namespace OpeningHours\Entity;

use OpeningHours\Module\I18n;

use DateTime;
use DateInterval;

class Period {

  /**
   *  Config
   *
   *  @access     protected
   *  @type       array
   */
  protected $config;

  /**
   *  Weekday
   *
   *  @access     protected
   *  @type       int
   */
  protected $weekday;

  /**
   *  Time Start
   *
   *  @access     protected
   *  @type       DateTime
   */
  protected $timeStart;

  /**
   *  Time End
   *
   *  @access     protected
   *  @type       DateTime
   */
  protected $timeEnd;

  /**
   *  Time Difference
   *
   *  @access     protected
   *  @type       DateInterval
   */
  protected $timeDifference;

  /**
   *  Is Dummy
   *
   *  @access     protected
   *  @type       bool
   */
  protected $isDummy;

  /**
   *  Constructor
   *
   *  @access     public
   *  @param      array     $config
   *  @return     Period
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

    return $this;
  }

  /**
   *  Set Up
   *
   *  @access     public
   *  @return     Period
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

    $this->setWeekday( $weekday );
    $this->setTimeStart( $timeStart );
    $this->setTimeEnd( $timeEnd );
    $this->setIsDummy( $dummy );

    return $this;

  }

  /**
   *  Is Open
   *
   *  @access     public
   *  @return     bool
   */
  public function isOpen () {

    return ( $this->getTimeStart() <= I18n::getTimeNow() and I18n::getTimeNow() <= $this->getTimeEnd() );

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
      $this->timeStart = new DateTime( $timeStart, I18n::getDateTimeZone() );
    elseif ( $timeStart instanceof DateTime ) :
      $this->timeStart = I18n::applyTimeZone( $timeStart );
    endif;

    $this->updateTimeDifference();

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
      $this->timeEnd = new DateTime( $timeEnd, I18n::getDateTimeZone() );
    elseif ( $timeEnd instanceof DateTime ) :
      $this->timeEnd = I18n::applyTimeZone( $timeEnd );
    endif;

    $this->updateTimeDifference();

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
