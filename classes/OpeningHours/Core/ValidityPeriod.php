<?php

namespace OpeningHours\Core;

/**
 * Represents a period in which the given `SpecEntry` is valid in the context of an OpeningHours sequence.
 * A ValidityPeriod being in effect signalizes it is the "first responder" telling whether a set is open or not,
 * but does not have to mean that it's open.
 *
 * Start and end dates can either be concrete \DateTime or -INF (for $start) or INF (for $end)
 *
 * @package OpeningHours\Core
 */
class ValidityPeriod {
  /**
   * Start of the validity period
   * @var   \DateTime|float
   */
  private $start;

  /**
   * End of the validity period
   * @var   \DateTime|float
   */
  private $end;

  /**
   * Entry serving opening hours during `ValidityPeriod`
   * @var     SpecEntry
   */
  private $entry;

  public function __construct($start, $end, SpecEntry $entry) {
    $this->start = $start;
    $this->end = $end;
    $this->entry = $entry;
  }

  public function getStart() {
    return $this->start;
  }

  public function getEnd() {
    return $this->end;
  }

  public function getEntry() {
    return $this->entry;
  }
}
