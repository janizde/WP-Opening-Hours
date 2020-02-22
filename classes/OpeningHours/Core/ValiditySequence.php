<?php

namespace OpeningHours\Core;

use OpeningHours\Util\Dates;

/**
 * A sequence of `ValidityPeriods` that can be restricted to a specific interval or covered by a new ValidityPeriod.
 * @package OpeningHours\Core
 */
class ValiditySequence {
  /**
   * Array of `ValidityPeriod` contained in this sequence
   * @var     ValidityPeriod[]
   */
  private $periods;

  /**
   * Creates a new ValiditySequence from periods
   * @param   ValidityPeriod[]    $periods      Periods for the sequence ordered by their start date and should
   *                                            not overlap
   */
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
   * @param     \DateTime|float     $start  minimum validity period start
   * @param     \DateTime|float     $end    maximum validity period end
   * @return    ValiditySequence            sequence with restricted `ValidityPeriod`s
   */
  public function restrictedToInterval($start, $end): ValiditySequence {
    $periodsInRange = array_filter($this->periods, function (ValidityPeriod $period) use ($start, $end) {
      return !(
        Dates::compareDateTime($period->getEnd(), $start) < 0 || Dates::compareDateTime($period->getStart(), $end) > 0
      );
    });

    $minFloat = Dates::getFloatFrom($start);
    $maxFloat = Dates::getFloatFrom($end);

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
    $beforeSequence = $this->restrictedToInterval(-INF, $fgPeriod->getStart());
    $afterSequence = $this->restrictedToInterval($fgPeriod->getEnd(), INF);
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

    $end = array_reduce($this->periods, function ($highest, ValidityPeriod $period) {
      return Dates::max($highest, $period->getEnd());
    }, -INF);

    return $end instanceof \DateTime ? clone $end : INF;
  }

  public function getPeriods() {
    return $this->periods;
  }
}
