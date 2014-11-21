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
   *  Constructor
   *
   *  @access     public
   *  @param      array     $config
   *  @return     Period
   */
  public function __construct ( array $config ) {

    if ( $config !== null and count( $config ) )
      $this->setConfig( $config );

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

    extract( $config );

    if ( !isset( $weekday ) or !is_int( $weekday ) )
      throw new InvalidArgumentException( sprintf( 'No proper weekday set in configuration for Set %d', $this->getId() ) );

    $this->setWeekday( $weekday );

    if ( !isset( $timeStart ) or !is_string( $timeStart ) )
      throw new InvalidArgumentException( sprintf( 'No proper timeStart set in configuration for Set %d', $this->getId() ) );

    $this->setTimeStart( $timeStart );

    if ( !isset( $timeEnd ) or !is_string( $timeEnd ) )
      throw new InvalidArgumentException( sprintf( 'No proper timeEnd set in configuration for Set %d', $this->getId() ) );

    $this->setTimeEnd( $timeEnd );

    return $this;

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
      $this->timeStart = new DateTime( $timeStart );
    elseif ( $timeStart instanceof DateTime ) :
      $this->timeStart = $timeStart;
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
      $this->timeEnd = new DateTime( $timeEnd );
    elseif ( $timeEnd instanceof DateTime ) :
      $this->timeEnd = $timeEnd;
    endif;

    $this->updateTimeDifference();

    return $this;
  }

}
?>
