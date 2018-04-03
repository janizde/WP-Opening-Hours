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
   * Sequence of `ValidityPeriod`s as array sorted by their start date
   * @var       ValidityPeriod[]
   */
  protected $periods;

  /**
   * Creates a new validity sequence
   * @param     array     $periods  Initial sequence of `ValidityPeriod`s
   *                                `$periods` are expected to be sorted by their start date
   */
  public function __construct(array $periods) {
    $this->periods = $periods;
  }

  /**
   * Returns a new `ValiditySequence` whose `$periods` correnspong to those of this `ValiditySequence`
   * but restricted to the date range specified by `$min` and `$max`
   *
   * If `$min` or `$max` is not specified the new `ValiditySequence` will not be restricted
   * in the respective direction
   *
   * `ValidityPeriod`s that are fully out of the date range are removed
   * and those who reach out of the date range are restricted
   *
   * @param     \DateTime         $min                minimum validity period start
   * @param     \DateTime         $max                maximum validity period end
   * @return    ValiditySequence                      sequence with restricted `ValidityPeriod`s
   */
  public function restrictedToDateRange(\DateTime $min = null, \DateTime $max = null) {
    $periodsInRange = array_filter($this->periods, function (ValidityPeriod $period) use ($min, $max) {
      return !(
        // `$min` is set and period is before `$min`
        ($min !== null && $period->getEnd() < $min)
        // `$max` ist set and period is after `$max`
        || ($max !== null && $period->getStart() > $max)
      );
    });

    $restrictedPeriods = array_map(function (ValidityPeriod $period) use ($min, $max) {
      return new ValidityPeriod(
        $period->getSet(),
        $min !== null ? max($period->getStart(), $min) : $period->getStart(),
        $max !== null ? min($period->getEnd(), $max) : $period->getEnd()
      );
    }, $periodsInRange);

    return new ValiditySequence(array_values($restrictedPeriods));
  }

  /**
   * Returns a new `ValiditySequence` containing all `ValidityPeriods` of the specified `$foreGroundsequence`
   * and fills the gaps between `$foregroundSequence`'s periods with the periods from this `ValiditySequence`
   *
   * The new `ValiditySequence`'s start and end are specified by the most exceeding periods in this `ValiditySequence`
   * as well as `$foregroundSequence`
   *
   * @param     ValiditySequence  $foregroundSequence   The sequence used as foreground sequence
   * @return    ValiditySequence                        The merged `ValiditySequence
   * @throws    \Exception                              If the `interval_spec` of a `DateInterval` is not parsable
   */
  public function coveredWith(ValiditySequence $foregroundSequence) {
    $fgPeriods = $foregroundSequence->periods;
    $bgPeriods = $this->periods;

    if (count($fgPeriods) < 1) {
      return $this;
    }

    if (count($bgPeriods) < 1) {
      return $foregroundSequence;
    }

    /** @var  ValidityPeriod[]  $totalSequence    Sequence of new `ValiditySequence` as array */
    $totalSequence = array();

    // If the background sequence exceeds the foreground sequence
    // add the restricted background sequence to the total sequence
    if ($this->getStart() < $foregroundSequence->getStart()) {
      $gapStart = $this->getStart();
      $gapEnd = clone $foregroundSequence->getStart();
      $gapEnd->sub(new \DateInterval('P1D'));

      $startSequence = $this->restrictedToDateRange($gapStart, $gapEnd);
      $totalSequence = array_merge($totalSequence, $startSequence->getPeriods());
    }

    // For each period of the foreground sequence
    // add the period to the total sequence
    for ($i = 0; $i < count($fgPeriods); ++$i) {
      $period = $fgPeriods[$i];
      $totalSequence[] = $period;

      // If the current sequence is not the last one
      // fill the gap to the next sequence with a restricted sequence of the background sequence
      if ($i < count($fgPeriods) - 1) {
        $nextPeriod = $fgPeriods[$i + 1];
        $gapStart = clone $period->getEnd();
        $gapStart->add(new \DateInterval('P1D'));
        $gapEnd = clone $nextPeriod->getStart();
        $gapEnd->sub(new \DateInterval('P1D'));

        if ($gapStart <= $gapEnd) {
          // Only add the gap sequence if it contains at least one day
          $gapBackgroundSequence = $this->restrictedToDateRange($gapStart, $gapEnd);
          $totalSequence = array_merge($totalSequence, $gapBackgroundSequence->getPeriods());
        }
      }
    }

    // If the background sequence exceeds the foreground sequence
    // add the restricted background sequence to the total sequence
    if ($this->getEnd() > $foregroundSequence->getEnd()) {
      $gapStart = $foregroundSequence->getEnd();
      $gapStart->add(new \DateInterval('P1D'));
      $gapEnd = $this->getEnd();

      $endSequence = $this->restrictedToDateRange($gapStart, $gapEnd);
      $totalSequence = array_merge($totalSequence, $endSequence->getPeriods());
    }

    return new ValiditySequence($totalSequence);
  }

  /**
   * Returns current sequence of `ValidityPeriod`s as array
   * @return    ValidityPeriod[]
   */
  public function getPeriods() {
    return $this->periods;
  }

  /**
   * Returns the first `ValidityPeriod`s start date
   *
   * @return    \DateTime|null    The first `ValidityPeriods` start date or null if the sequence is empty
   */
  public function getStart() {
    return count($this->periods) > 0 ? (clone $this->periods[0]->getStart()) : null;
  }

  /**
   * Returns the last `ValidityPeriod`s end date
   *
   * @return    \DateTime|null    The last `ValidityPeriods` end date or null if the sequence is empty
   */
  public function getEnd() {
    return count($this->periods) > 0 ? (clone $this->periods[count($this->periods) - 1]->getEnd()) : null;
  }
}
