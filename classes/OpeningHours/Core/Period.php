<?php

namespace OpeningHours\Core;

use OpeningHours\Util\Dates;

/**
 * Concrete instance of a Period with start and end dates
 * @package OpeningHours\Core
 */
class Period implements ArraySerializable {
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

  /** @inheritDoc */
  function toSerializableArray(): array {
    return [
      'start' => Dates::serialize($this->start),
      'end' => Dates::serialize($this->end),
    ];
  }

  /** @inheritDoc */
  static function fromSerializableArray(array $array): ArraySerializable {
    return new Period(Dates::deserialize($array['start']), Dates::deserialize($array['end']));
  }
}
