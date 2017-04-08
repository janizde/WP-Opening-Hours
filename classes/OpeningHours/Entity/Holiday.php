<?php

namespace OpeningHours\Entity;

use DateTime;
use OpeningHours\Util\Dates;

/**
 * Represents a single holiday
 *
 * @author      Jannik Portz
 * @package     OpeningHours\Entity
 */
class Holiday {

  /**
   * The holiday's name
   * @type            string
   */
  protected $name;

  /**
   * DateTime object representing the start of the holiday
   * @type            DateTime
   */
  protected $dateStart;

  /**
   * DateTime object representing the end of the holiday
   * @type            DateTime
   */
  protected $dateEnd;

  /**
   * Whether holiday is a dummy or not
   * @type            bool
   */
  protected $dummy;

  /**
   * Constructs a new Holiday
   *
   * @param     string   $name      The holiday's name
   * @param     DateTime $dateStart DateTime object representing the start of the holiday
   * @param     DateTime $dateEnd   DateTime object representing the end of the holiday
   * @param     bool     $dummy     Whether holiday is a dummy or not
   */
  public function __construct ( $name, DateTime $dateStart, DateTime $dateEnd, $dummy = false ) {
    if (!$dummy && empty($name))
      throw new \InvalidArgumentException("\$name must not be empty when holiday is no dummy.");

    $this->name = $name;
    $this->setDateStart($dateStart);
    $this->setDateEnd($dateEnd);
    $this->dummy = $dummy;
  }

  /**
   * Is Active
   * determines if current Holiday is active
   *
   * @param     DateTime $now     The DateTime representing the current time. Can be modified to check whether
   *                              the holiday will be active or has been active at a certain time.
   *                              Default is the current time
   *
   * @return    bool              Whether the holiday has been active, will be active, is active at the provided time
   */
  public function isActive ( DateTime $now = null ) {
    if ($now === null)
      $now = Dates::getNow();

    return ($this->dateStart <= $now and $this->dateEnd >= $now);
  }

  /**
   * Sorts Holiday objects by dateStart (ASC)
   *
   * @param     Holiday $holiday_1
   * @param     Holiday $holiday_2
   *
   * @return    int
   */
  public static function sortStrategy ( Holiday $holiday_1, Holiday $holiday_2 ) {
    if ($holiday_1->dateStart > $holiday_2->dateStart) :
      return 1;
    elseif ($holiday_1->dateStart < $holiday_2->dateStart) :
      return -1;
    else :
      return 0;
    endif;
  }

  /**
   * Factory for dummy Holiday
   * @return    Holiday
   */
  public static function createDummyPeriod () {
    return new Holiday('', Dates::getNow(), Dates::getNow(), true);
  }

  /**
   * Getter: Name
   * @return          string
   */
  public function getName () {
    return $this->name;
  }

  /**
   * Getter: Date Start
   * @return          DateTime
   */
  public function getDateStart () {
    return $this->dateStart;
  }

  /**
   * Setter: Date Start
   *
   * @param           DateTime|string $dateStart
   */
  protected function setDateStart ( $dateStart ) {
    $this->setDateUniversal($dateStart, 'dateStart');
  }

  /**
   * Getter: Date End
   * @return          DateTime
   */
  public function getDateEnd () {
    return $this->dateEnd;
  }

  /**
   * Setter: Date End
   *
   * @param           DateTime|string $dateEnd
   */
  protected function setDateEnd ( $dateEnd ) {
    $this->setDateUniversal($dateEnd, 'dateEnd', true);
  }

  /**
   * Universal setter for dates
   *
   * @param     DateTime|string $date       The date to set, either as string or DateTime instance
   * @param     string          $property   The name of the property to set
   * @param     bool            $end_of_day Whether the time should be shifted to the end of the day
   */
  private function setDateUniversal ( $date, $property, $end_of_day = false ) {
    if (is_string($date) and (preg_match(Dates::STD_DATE_FORMAT_REGEX, $date) or preg_match(Dates::STD_DATE_FORMAT_REGEX . ' ' . Dates::STD_TIME_FORMAT_REGEX, $date)))
      $date = new DateTime($date);

    if (!$date instanceof DateTime)
      add_notice(sprintf('Argument one for %s has to be of type string or DateTime, %s given', __CLASS__ . '::' . __METHOD__, gettype($date)));

    $date = Dates::applyTimeZone($date);

    if ($end_of_day === true)
      $date->setTime(23, 59, 59);

    $this->$property = Dates::applyTimeZone($date);
  }

  /**
   * Getter: Dummy
   * @return    bool
   */
  public function isDummy () {
    return $this->dummy;
  }

}