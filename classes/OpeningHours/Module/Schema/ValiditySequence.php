<?php

namespace OpeningHours\Module\Schema;

/**
 * Represents a sequence of `ValidityPeriods`
 * Instances of `ValiditySequence` are immutable
 *
 * @author  Jannik Portz <hello@jannikportz.de>
 * @package OpeningHours\Module\Schema
 */
class ValiditySequence {

  /**
   * Sequence of `ValidityPeriod`s as array
   * @var       ValidityPeriod[]
   */
  protected $periods;

  /**
   * Creates a new validity sequence
   * @param     array     $periods  Initial sequence of `ValidityPeriod`s
   */
  public function __construct(array $periods) {
    $this->periods = $periods;
  }

  /**
   * Returns a new `ValiditySequence` whose `$periods` correnspong to those of this `ValiditySequence`
   * but restricted to the date range specified by `$min` and `$max`
   *
   * `ValidityPeriod`s that are fully out of the date range are removed
   * and those who reach out of the date range are restricted
   *
   * @param     \DateTime         $min                minimum validity period start
   * @param     \DateTime         $max                maximum validity period end
   * @return    ValiditySequence                      sequence with restricted `ValidityPeriod`s
   */
  public function restrictedToDateRange(\DateTime $min, \DateTime $max) {
    $periodsInRange = array_filter($this->periods, function (ValidityPeriod $period) use ($min, $max) {
      return !($period->getEnd() < $min || $period->getStart() > $max);
    });

    $restrictedPeriods = array_map(function (ValidityPeriod $period) use ($min, $max) {
      return new ValidityPeriod($period->getSet(), max($period->getStart(), $min), min($period->getEnd(), $max));
    }, $periodsInRange);

    return new ValiditySequence(array_values($restrictedPeriods));
  }

  /**
   * Returns current sequence of `ValidityPeriod`s as array
   * @return    ValidityPeriod[]
   */
  public function getPeriods() {
    return $this->periods;
  }
}
