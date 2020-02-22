<?php

namespace OpeningHours\Core;

/**
 * Description of a period that is recurring every week.
 * Representation consists of a `H:i` string specifying the start time of the period, a numeric weekday
 * and the duration of the period in seconds. This allows recurring periods to span an arbitrary amount of days.
 *
 * @package OpeningHours\Core
 */
class RecurringPeriod {
  /**
   * Start time of the period as `H:i` time string
   * @var string
   */
  private $startTime;

  /**
   * Duration of the period in seconds
   * @var int
   */
  private $duration;

  /**
   * Numeric representation of the weekday that the period starts
   * @var int
   */
  private $weekday;

  public function __construct(string $startTime, int $duration, int $weekday) {
    $this->startTime = $startTime;
    $this->duration = $duration;
    $this->weekday = $weekday;
  }

  /**
   * Creates a concrete instance Period starting on the specified $date. The passed in date is expected to be
   * the same weekday as the RecurringPeriod
   *
   * @param     \DateTime     $date  Start date of the period
   * @return    Period               Period with concrete start and end dates
   * @throws    \Exception           If $date is a different weekday than the $weekday
   */
  public function getPeriodOn(\DateTime $date): Period {
    $dateWeekday = (int) $date->format('w');
    if ($dateWeekday !== $this->weekday) {
      throw new \InvalidArgumentException(
        sprintf("Argument \$date must represent a date with weekday %d. Weekday %d given.", $this->weekday, $dateWeekday)
      );
    }

    $start = clone $date;
    $components = [];
    preg_match('/^(\d{2}):(\d{2})$/', $this->startTime, $components);
    $start->setTime((int) $components[1], (int) $components[2], 0);
    $end = clone $start;
    $end->add(new \DateInterval(sprintf("PT%dS", $this->duration)));
    return new Period($start, $end);
  }

  /**
   * Returns the weekday at which the recurring period starts
   * @return    int
   */
  public function getWeekday() {
    return $this->weekday;
  }
}
