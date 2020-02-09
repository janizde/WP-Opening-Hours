<?php

namespace OpeningHours\Core;

use OpeningHours\Util\Dates;

class ValiditySequence {
  private $periods;

  public function __construct(array $periods) {
    $this->periods = $periods;
  }

  /**
   * Returns a new `ValiditySequence` whose `$periods` correspond to those of this `ValiditySequence`
   * but restricted to the date range specified by `$min` and `$max`
   *
   * If `$min` is `-INF` or `$max` is `INF` the new `ValiditySequence` will not be restricted
   * in the respective direction
   *
   * `ValidityPeriod`s that are fully out of the date range are removed
   * and those who reach out of the date range are restricted
   *
   * @param     \DateTime|float     $min    minimum validity period start
   * @param     \DateTime|float     $max    maximum validity period end
   * @return    ValiditySequence            sequence with restricted `ValidityPeriod`s
   */
  public function restrictedToDateRange($min, $max): ValiditySequence {
    $periodsInRange = array_filter($this->periods, function (ValidityPeriod $period) use ($min, $max) {
      return !(
        Dates::compareDateTime($period->getEnd(), $min) < 0 || Dates::compareDateTime($period->getStart(), $max) > 0
      );
    });

    $minFloat = Dates::getFloatFrom($min);
    $maxFloat = Dates::getFloatFrom($max);

    $restrictedPeriods = array_map(function (ValidityPeriod $period) use ($minFloat, $maxFloat) {
      return new ValidityPeriod(
        Dates::max($period->getStart(), $minFloat),
        Dates::min($period->getEnd(), $maxFloat),
        $period->getEntry()
      );
    }, $periodsInRange);

    return new ValiditySequence(array_values($restrictedPeriods));
  }

  public function coveredWith(ValidityPeriod $fgPeriod): ValiditySequence {
    $beforeSequence = $this->restrictedToDateRange(-INF, $fgPeriod->getStart());
    $afterSequence = $this->restrictedToDateRange($fgPeriod->getEnd(), INF);
    $nextPeriods = array_merge($beforeSequence->periods, $fgPeriod, $afterSequence->periods);
    return new ValiditySequence($nextPeriods);
  }

  public static function createFromSpecTree(SpecEntry $entry) {
    $seq = new ValiditySequence([]);
    return $seq->mergeIntoSequence($entry);
  }

  private function mergeIntoSequence(SpecEntry $entry) {
    $merged = $this->coveredWith($entry->getValidityPeriod());
    return array_reduce(
      $entry->getChildren(),
      function (ValiditySequence $accum, SpecEntry $child) {
        return $accum->mergeIntoSequence($child);
      },
      $merged
    );
  }

  /**
   * Returns the first `ValidityPeriod`s start date
   *
   * @return    \DateTime|float   The first `ValidityPeriods` start date or -INF if the sequence is empty
   */
  public function getStart() {
    if (count($this->periods) < 1) {
      return -INF;
    }

    $start = $this->periods[0]->getStart();
    return $start instanceof \DateTime ? clone $start : $start;
  }

  /**
   * Returns the last `ValidityPeriod`s end date
   *
   * @return    \DateTime|float   The last `ValidityPeriods` end date or INF if the sequence is empty
   */
  public function getEnd() {
    if (count($this->periods) < 1) {
      return INF;
    }

    $end = $this->periods[count($this->periods) - 1]->getEnd();
    return $end instanceof \DateTime ? clone $end : INF;
  }
}
