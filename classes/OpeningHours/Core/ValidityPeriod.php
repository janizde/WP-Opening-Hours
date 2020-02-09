<?php

namespace OpeningHours\Core;

class ValidityPeriod {
  private $start;
  private $end;
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
