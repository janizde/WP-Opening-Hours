<?php

namespace OpeningHours\Entity;

use DateInterval;
use DateTime;
use OpeningHours\Util\ArrayObject;
use OpeningHours\Util\Dates;

/**
 * Abstraction for a Set
 * @package OpeningHours\Entity
 */
class Set {

  /**
   * The Id of the set
   * @var       string|int
   */
  protected $id;

  /**
   * The name of the set
   * @var       string
   */
  protected $name;

  /**
   * Collection of all Periods in the Set
   * @var       ArrayObject
   */
  protected $periods;

  /**
   * Collection of all Holidays in the Set
   * @var       ArrayObject
   */
  protected $holidays;

  /**
   * Collection of all Irregular Openings in the Set
   * @var       ArrayObject
   */
  protected $irregularOpenings;

  /**
   * The set description
   * @var       string
   */
  protected $description;

  public function __construct ($id) {
    $this->id = $id;
    $this->name = __('Untitled Set', 'wp-opening-hours');
    $this->periods = new ArrayObject();
    $this->holidays = new ArrayObject();
    $this->irregularOpenings = new ArrayObject();
  }

  /**
   * Checks if any holiday in set is currently active
   *
   * @param     DateTime $now Custom time
   *
   * @return    bool                Whether any holiday in the set is currently active
   */
  public function isHolidayActive ( $now = null ) {
    return $this->getActiveHoliday($now) instanceof Holiday;
  }

  /**
   * Returns the first active holiday or null if none is active
   *
   * @param     DateTime $now Custom Time
   *
   * @return    Holiday             The first active Holiday or null if none is active
   */
  public function getActiveHoliday ( DateTime $now = null ) {
    foreach ($this->holidays as $holiday)
      if ($holiday->isActive($now))
        return $holiday;

    return null;
  }

  /**
   * Checks whether any irregular opening is currently active (based on the date)
   *
   * @param     DateTime $now Custom time
   *
   * @return    bool                whether any irregular opening is currently active
   */
  public function isIrregularOpeningActive ( DateTime $now = null ) {
    return $this->getActiveIrregularOpening($now) instanceof IrregularOpening;
  }

  /**
   * Evaluates all aspects determining whether the venue is currently open or not
   *
   * @param     DateTime $now Custom time
   *
   * @return    bool                Whether venue is currently open or not
   */
  public function isOpen ( DateTime $now = null ) {
    if ($this->isHolidayActive($now))
      return false;

    if ($this->isIrregularOpeningActive($now)) {
      $io = $this->getActiveIrregularOpening($now);
      return $io->isOpen($now);
    }

    foreach ($this->periods as $period)
      if ($period->isOpen($now, $this))
        return true;

    return false;
  }

  /**
   * Returns the first open Period after $now
   *
   * @param     DateTime $now The date context for the Periods. default: current datetime
   *
   * @return    Period    The next open period or null if no period has been found
   */
  public function getNextOpenPeriod (DateTime $now = null) {
    if ($now === null) {
      $now = Dates::getNow();
    }

    /** @var Period|null $closestPeriod */
    $closestPeriod = null;

    foreach ($this->irregularOpenings as $io) {
      /** @var IrregularOpening $io */
      if ($io->getTimeStart() <= $now) {
        continue;
      }

      if ($closestPeriod === null || $io->getTimeStart() < $closestPeriod->getTimeStart()) {
        $closestPeriod = $io->createPeriod();
      }
    }

    if ($this->periods->count() < 1) {
      return $closestPeriod;
    }

    /** @var Period[] $periods */
    $periods = new ArrayObject();
    foreach ($this->periods as $period) {
      $periods->append($now !== null ? $period->getCopyInDateContext($now) : clone $period);
    }

    $periods->uasort(array('\OpeningHours\Entity\Period', 'sortStrategy'));

    if (count($periods) < 1 && count($this->irregularOpenings) < 1)
      return null;

    // For each period in future: check if it will actually be open
    foreach ($periods as $period) {
      if ($period->compareToDateTime($now) <= 0)
        continue;

      if (!$period->willBeOpen($this))
        continue;

      return $closestPeriod === null || $period->getTimeStart() <= $closestPeriod->getTimeStart()
        ? $period
        : $closestPeriod;
    }

    $interval = new DateInterval('P7D');
    for ($weekOffset = 1; $weekOffset < 52; $weekOffset++) {

      foreach ($periods as $period) {
        $period->getTimeStart()->add($interval);
        $period->getTimeEnd()->add($interval);

        if (!$period->willBeOpen($this))
          continue;

        return $closestPeriod === null || $period->getTimeStart() <= $closestPeriod->getTimeStart()
          ? $period
          : $closestPeriod;
      }
    }

    return $closestPeriod;
  }

  /**
   * Returns first active irregular opening on that day
   * Only evaluates the date of $now and not the time
   *
   * @param     DateTime $now Custom time
   *
   * @return    IrregularOpening
   */
  public function getActiveIrregularOpening ( DateTime $now = null ) {
    /** @var IrregularOpening $io */
    foreach ($this->irregularOpenings as $io)
      if ($io->isActiveOnDay($now))
        return $io;

    return null;
  }

  public function getId () {
    return $this->id;
  }

  public function setId ($id) {
    $this->id = $id;
  }

  public function getName () {
    return $this->name;
  }

  public function setName ($name) {
    $this->name = $name;
  }

  public function getPeriods () {
    return $this->periods;
  }

  public function setPeriods (ArrayObject $periods) {
    $this->periods = $periods;
  }

  public function getHolidays () {
    return $this->holidays;
  }

  public function setHolidays (ArrayObject $holidays) {
    $this->holidays = $holidays;
  }

  public function getIrregularOpenings () {
    return $this->irregularOpenings;
  }

  public function setIrregularOpenings (ArrayObject $irregularOpenings) {
    $this->irregularOpenings = $irregularOpenings;
  }

  public function getDescription () {
    return $this->description;
  }

  public function setDescription ($description) {
    $this->description = $description;
  }
}