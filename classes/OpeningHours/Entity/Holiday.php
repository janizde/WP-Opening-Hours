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
class Holiday implements TimeContextEntity, DateTimeRange {
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
  public function __construct($name, DateTime $dateStart, DateTime $dateEnd, $dummy = false) {
    if (!$dummy && empty($name)) {
      throw new \InvalidArgumentException("\$name must not be empty when holiday is no dummy.");
    }

    $this->name = $name;
    $this->setStart($dateStart);
    $this->setEnd($dateEnd);
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
  public function isActive(DateTime $now = null) {
    if ($now === null) {
      $now = Dates::getNow();
    }

    return $this->dateStart <= $now and $this->dateEnd >= $now;
  }

  /**
   * Sorts Holiday objects by dateStart (ASC)
   *
   * @param     Holiday $holiday_1
   * @param     Holiday $holiday_2
   *
   * @return    int
   */
  public static function sortStrategy(Holiday $holiday_1, Holiday $holiday_2) {
    if ($holiday_1->dateStart > $holiday_2->dateStart):
      return 1;
    elseif ($holiday_1->dateStart < $holiday_2->dateStart):
      return -1;
    else:
      return 0;
    endif;
  }

  /**
   * Factory for dummy Holiday
   * @return    Holiday
   */
  public static function createDummyPeriod() {
    return new Holiday('', Dates::getNow(), Dates::getNow(), true);
  }

  /* @inheritdoc */
  public function isPast(\DateTime $reference) {
    return $this->dateEnd < $reference;
  }

  /**
   * Checks whether the period is active on that day.
   * Does not check for irregular opening or holidays overriding this period
   * @inheritdoc
   */
  public function happensOnDate(\DateTime $date) {
    return $this->dateStart <= $date && $this->dateEnd >= $date;
  }

  /**
   * Formats the date range of the holiday
   * @param     string    $dateFormat     The PHP date format to format every DateTime with
   * @param     string    $rangeFormat    printf template string combining dateStart (%1$s) and dateEnd (%2$2)
   * @return    string                    The formatted date range
   */
  public function getFormattedDateRange($dateFormat, $rangeFormat = '%s - %s') {
    if (Dates::compareDate($this->dateStart, $this->dateEnd) === 0) {
      return Dates::format($dateFormat, $this->dateStart);
    }

    return sprintf(
      $rangeFormat,
      Dates::format($dateFormat, $this->dateStart),
      Dates::format($dateFormat, $this->dateEnd)
    );
  }

  /**
   * Getter: Name
   * @return          string
   */
  public function getName() {
    return $this->name;
  }

  /** @inheritdoc */
  public function getStart() {
    return $this->dateStart;
  }

  /**
   * Setter: Date Start
   *
   * @param           DateTime|string $dateStart
   */
  protected function setStart($dateStart) {
    $this->setDateUniversal($dateStart, 'dateStart');
  }

  /** @inheritdoc */
  public function getEnd() {
    return $this->dateEnd;
  }

  /**
   * Setter: Date End
   *
   * @param           DateTime|string $dateEnd
   */
  protected function setEnd($dateEnd) {
    $this->setDateUniversal($dateEnd, 'dateEnd', true);
  }

  /**
   * @deprecated  Use getStart instead
   * @return      DateTime
   */
  public function getDateStart() {
    return $this->getStart();
  }

  /**
   * @deprecated  Use getEnd instead
   * @return      DateTime
   */
  public function getDateEnd() {
    return $this->getEnd();
  }

  /**
   * @deprecated  Use setStart instead
   * @return      DateTime
   */
  protected function setDateStart($dateStart) {
    return $this->setStart($dateStart);
  }

  /**
   * @deprecated  Use setEnd instead
   * @return      DateTime
   */
  protected function setDateEnd($dateEnd) {
    return $this->setEnd($dateEnd);
  }

  /**
   * Universal setter for dates
   *
   * @param     DateTime|string $date       The date to set, either as string or DateTime instance
   * @param     string          $property   The name of the property to set
   * @param     bool            $end_of_day Whether the time should be shifted to the end of the day
   */
  private function setDateUniversal($date, $property, $end_of_day = false) {
    if (
      is_string($date) and
      (preg_match(Dates::STD_DATE_FORMAT_REGEX, $date) or
        preg_match(Dates::STD_DATE_FORMAT_REGEX . ' ' . Dates::STD_TIME_FORMAT_REGEX, $date))
    ) {
      $date = new DateTime($date);
    }

    if (!$date instanceof DateTime) {
      add_notice(
        sprintf(
          'Argument one for %s has to be of type string or DateTime, %s given',
          __CLASS__ . '::' . __METHOD__,
          gettype($date)
        )
      );
    }

    $date = Dates::applyTimeZone($date);

    if ($end_of_day === true) {
      $date->setTime(23, 59, 59);
    }

    $this->$property = Dates::applyTimeZone($date);
  }

  /**
   * Getter: Dummy
   * @return    bool
   */
  public function isDummy() {
    return $this->dummy;
  }
}
