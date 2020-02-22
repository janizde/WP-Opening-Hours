<?php

namespace OpeningHours\Core;

/**
 * Specification entry describing recurring periods related to weekdays
 * @package OpeningHours\Core
 */
class RecurringPeriods implements SpecEntry {
  const SPEC_KIND = 'recurringPeriods';

  /**
   * Start of the recurring periods as \DateTime (inclusiv) or -INF if unlimited
   * @var \DateTime|float
   */
  private $start;

  /**
   * End of the recurring periods as \DateTime (exclusive) or INF if unlimite
   * @var \DateTime|float
   */
  private $end;

  /**
   * Array of periods for this set of recurring periods
   * @var RecurringPeriod[]
   */
  private $periods;

  /**
   * Children of the recurring periods
   * @var array
   */
  private $children;

  public function __construct($start, $end, array $periods, array $children) {
    $this->start = $start;
    $this->end = $end;
    $this->periods = $periods;
    $this->children = $children;
  }

  /** @inheritDoc */
  function getKind(): string {
    return RecurringPeriods::SPEC_KIND;
  }

  /** @inheritDoc */
  function getChildren(): array {
    return $this->children;
  }

  /**
   * Creates a ValidityPeriod with the same start and end dates of this `RecurringPeriods`
   * @return    ValidityPeriod
   */
  public function getValidityPeriod(): ValidityPeriod {
    return new ValidityPeriod($this->start, $this->end, $this);
  }
}
