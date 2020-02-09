<?php

namespace OpeningHours\Core;

/**
 * Concrete instance of a Period with start and end dates
 * @package OpeningHours\Core
 */
class Period {
  /**
   * Start date and time of this period (inclusive)
   * @var \DateTime
   */
  private $start;

  /**
   * End date and time of this period (exclusive)
   * @var \DateTime
   */
  private $end;

  /**
   * Weekday of the start of the period
   * @var int
   */
  private $weekday;

  public function __construct(\DateTime $start, \DateTime $end) {
    $this->start = $start;
    $this->end = $end;
    $this->weekday = (int) $this->start->format('w');
  }

  public function getStart(): \DateTime {
    return $this->start;
  }

  public function getEnd(): \DateTime {
    return $this->end;
  }

  public function getWeekday(): int {
    return $this->weekday;
  }
}