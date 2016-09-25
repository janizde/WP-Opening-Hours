<?php

namespace OpeningHours\Entity;

use DateInterval;
use DateTime;
use InvalidArgumentException;
use OpeningHours\Util\Dates;
use OpeningHours\Util\Weekday;

/**
 * Represents a regular opening period
 *
 * @author      Jannik Portz
 * @package     OpeningHours\Entity
 * @todo        add interface to combine Period and IrregularOpening
 */
class Period {

  /**
   * weekdays represented by integer. Monday: 0 - Sunday: 7
   * @var       int
   * @todo      use class Weekday
   */
  protected $weekday;

  /**
   * DateTime object representing the period's start time in the current week
   * @var       DateTime
   */
  protected $timeStart;

  /**
   * DateTime object representing the period's end time in the current week
   * @var       DateTime
   */
  protected $timeEnd;

  /**
   * Whether the Period spans two days, so the endTime ist after midnight while the startTime is before midnight
   * @var       bool
   */
  protected $spansTwoDays;

  /**
   * Whether this Period is a dummy
   * @var       bool
   */
  protected $dummy;

  /**
   * Constructs a new Period with a config array
   *
   * @param     int    $weekday   Weekday represented by integer. Monday: 0 - Sunday: 7
   * @param     string $timeStart The start time in standard time format
   * @param     string $timeEnd   The end time in standard time format
   * @param     bool   $dummy     Whether this period is a dummy. default: false
   *
   * @throws    InvalidArgumentException  On validation error
   */
  public function __construct ( $weekday, $timeStart, $timeEnd, $dummy = false ) {
    if (!is_int($weekday) or $weekday < 0 or $weekday > 6)
      throw new InvalidArgumentException(sprintf('$weekday must be an integer between 0 and 6. got %s', (string)$weekday));

    if (!Dates::isValidTime($timeStart))
      throw new InvalidArgumentException(sprintf('$timeStart must be in standard time format %s. got %s', Dates::STD_TIME_FORMAT, $timeStart));

    if (!Dates::isValidTime($timeEnd))
      throw new InvalidArgumentException(sprintf('$timeEnd must be in standard time format %s. got %s', Dates::STD_TIME_FORMAT, $timeEnd));

    $this->weekday = $weekday;
    $this->timeStart = Dates::applyWeekContext(new DateTime($timeStart, Dates::getTimezone()), $weekday);
    $this->timeEnd = Dates::applyWeekContext(new DateTime($timeEnd, Dates::getTimezone()), $weekday);
    $this->dummy = $dummy;

    $this->spansTwoDays = Dates::compareTime($this->timeStart, $this->timeEnd) >= 0;
    if ($this->spansTwoDays)
      $this->timeEnd->add(new DateInterval('P1D'));
  }

  /**
   * Checks whether Period is currently open regardless of Holidays and IrregularOpenings
   *
   * @param     DateTime $now
   *
   * @return    bool      Whether Period is currently open regardless of Holidays and SpecialOpenings
   */
  public function isOpenStrict ( DateTime $now = null ) {
    if (!$now instanceof DateTime)
      $now = Dates::getNow();

    $today = (int)$now->format('w');
    $startDay = $this->weekday;
    $endDay = (int)$this->timeEnd->format('w');

    if ($today !== $startDay and $today !== $endDay)
      return false;

    $timeStart = (int)$this->timeStart->format('Hi');
    $timeEnd = (int)$this->timeEnd->format('Hi');
    $timeNow = (int)$now->format('Hi');

    if (!$this->spansTwoDays)
      return $timeStart <= $timeNow and $timeNow <= $timeEnd;

    if ($today == $startDay)
      return $timeStart <= $timeNow;

    return $timeNow <= $timeEnd;
  }

  /**
   * Checks if Period is currently open also regarding Holidays and SpecialOpenings
   *
   * @param     DateTime $now
   * @param     Set      $set The set in whose context to determine the opening status of this Period
   *
   * @return    bool
   */
  public function isOpen ($now, Set $set) {
    if ($set->isHolidayActive($now) or $set->isIrregularOpeningActive($now))
      return false;

    return $this->isOpenStrict($now);
  }

  /**
   * Checks whether the specified Period is open in different weekday contexts
   * @param     Weekday[]   $weekdays   The weekdays to check
   * @param     Set         $set        The Set containing holidays and irregular openings
   * @param     DateTime    $now        Custom current time
   * @return    bool                    Whether the Period is open in the context of any Weekday
   */
  public function isOpenOnAny (array $weekdays, Set $set, DateTime $now = null) {
    foreach ($weekdays as $weekday) {
      $period = new Period($weekday->getIndex(), $this->timeStart->format(Dates::STD_TIME_FORMAT), $this->timeEnd->format(Dates::STD_TIME_FORMAT));
      if ($period->isOpen($now, $set))
        return true;
    }

    return false;
  }

  /**
   * Returns -1 if the Period was in the past, 0 if the period is currently running or 1 if the Period will be in the
   * future.
   * (All relative to the specified DateTime)
   *
   * @param     DateTime $now Custom DateTime to compare to (default: current time)
   *
   * @return    int
   */
  public function compareToDateTime ( DateTime $now = null ) {
    if ($now == null)
      $now = Dates::getNow();

    if ($this->timeStart < $now && $this->timeEnd < $now) {
      return -1;
    } elseif ($this->timeStart <= $now && $this->timeEnd >= $now) {
      return 0;
    } else {
      return 1;
    }
  }

  /**
   * Checks whether this Period will be regularly open and not overridden due to Holidays or Special Openings
   *
   * @param       Set $set
   *
   * @return      bool
   */
  public function willBeOpen ( Set $set = null ) {
    return $this->isOpen($this->timeStart, $set);
  }

  /**
   * Sorts period by day and time
   *
   * @param     Period $period1
   * @param     Period $period2
   *
   * @return    int
   */
  public static function sortStrategy ( Period $period1, Period $period2 ) {
    if ($period1->timeStart < $period2->timeStart) {
      return -1;
    } elseif ($period1->timeStart > $period2->timeStart) {
      return 1;
    } else {
      return 0;
    }
  }

  /**
   * Compares this Period to another Period
   *
   * @param     Period $other
   * @param     bool   $ignoreDay
   *
   * @return    bool
   */
  public function equals ( Period $other, $ignoreDay = false ) {
    $timeFormat = 'Hi';

    if (!$ignoreDay and $this->weekday != $other->weekday)
      return false;

    if ($this->timeStart->format($timeFormat) != $other->timeStart->format($timeFormat))
      return false;

    if ($this->timeEnd->format($timeFormat) != $other->timeEnd->format($timeFormat))
      return false;

    return true;
  }

  /**
   * Factory for dummy Period
   *
   * @param     int $weekday The weekday to use for the dummy period
   *
   * @return    Period
   */
  public static function createDummy ( $weekday = 0 ) {
    return new Period($weekday, '00:00', '00:00', true);
  }

  /**
   * Returns the formatted string with start and end time for this Period
   *
   * @param     string $timeFormat Custom time format
   *
   * @return    string
   */
  public function getFormattedTimeRange ( $timeFormat = null ) {
    if ($timeFormat == null)
      $timeFormat = Dates::getTimeFormat();

    return $this->timeStart->format($timeFormat) . ' - ' . $this->timeEnd->format($timeFormat);
  }

  /**
   * Returns a copy of this Period in another time context meaning the dates of the start and end time may be
   * in another week depending on $date
   *
   * @param     DateTime $date The date context for the new Period
   *
   * @return    Period              The new Period in another date context
   */
  public function getCopyInDateContext ( DateTime $date ) {
    $period = clone $this;
    $period->timeStart = Dates::applyWeekContext(clone $this->timeStart, $this->weekday, $date);
    $period->timeEnd = Dates::applyWeekContext(clone $this->timeEnd, $this->weekday, $date);

    if ($period->spansTwoDays)
      $period->timeEnd->add(new DateInterval('P1D'));

    return $period;
  }

  /**
   * Getter: Weekday
   * @return    int
   */
  public function getWeekday () {
    return $this->weekday;
  }

  /**
   * Getter: Time Start
   * @return    DateTime
   */
  public function getTimeStart () {
    return $this->timeStart;
  }

  /**
   * Getter: Time End
   * @return    DateTime
   */
  public function getTimeEnd () {
    return $this->timeEnd;
  }

  /**
   * Getter: Is Dummy
   * @return     bool
   */
  public function isDummy () {
    return $this->dummy;
  }
}