<?php

namespace OpeningHours\Module\Schema;

/**
 * Represents a sequence of `ValidityPeriods`
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
   * Restricts the `ValidityPeriod`s in this `ValiditySequence` to the range specified by `$min` and `$max`
   * Removes `ValidityPeriod`s that are fully out of range and restricts the ones who reach out of the range.
   *
   * @param     \DateTime         $min                minimum validity period start
   * @param     \DateTime         $max                maximum validity period end
   */
  public function restrictToDateRange(\DateTime $min, \DateTime $max) {
    $periodsInRange = array_filter($this->periods, function (ValidityPeriod $period) use ($min, $max) {
      return !($period->getEnd() < $min || $period->getStart() > $max);
    });

    $restrictedPeriods = array_map(function (ValidityPeriod $period) use ($min, $max) {
      return new ValidityPeriod($period->getSet(), max($period->getStart(), $min), min($period->getEnd(), $max));
    }, $periodsInRange);

    $this->periods = array_values($restrictedPeriods);
  }

  /**
   * Returns current sequence of `ValidityPeriod`s as array
   * @return    ValidityPeriod[]
   */
  public function getPeriods() {
    return $this->periods;
  }
}
