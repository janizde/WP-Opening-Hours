<?php

namespace OpeningHours\Core;

use OpeningHours\Util\Dates;

/**
 * Specification entry describing recurring periods related to weekdays
 * @package OpeningHours\Core
 */
class RecurringPeriods implements SpecEntry {
  const SPEC_KIND = 'recurringPeriods';

  /**
   * Start of the recurring periods as \DateTime (inclusive) or -INF if unlimited
   * @var \DateTime|float
   */
  private $start;

  /**
   * End of the recurring periods as \DateTime (exclusive) or INF if unlimited
   * @var \DateTime|float
   */
  private $end;

  /**
   * Array of periods for this set of recurring periods ordered by their (weekday, start) combination
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

  /**
   * Determines a concrete Period that is active at the $reference DateTime.
   * If none is active, null is returned.
   *
   * @param     \DateTime     $reference    Reference date at which to search.
   * @return    Period|null                 Period active at $reference or null
   */
  function getPeriodAt(\DateTime $reference) {
    $lastOccurrences = array_map(function (RecurringPeriod $rp) use ($reference) {
      $date = Dates::getWeekdayOccurrenceBefore($rp->getWeekday(), $reference);
      return $rp->getPeriodOn($date);
    }, $this->periods);

    foreach ($lastOccurrences as $period) {
      if ($period->getStart() <= $reference && $period->getEnd() > $reference) {
        return $period;
      }
    }

    return null;
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
   * Creates a ValidityPeriod with the same start and end dates of this `RecurringPeriods`.
   * When a recurring period is active during the `$end` of this spec entry, the end in the ValidityPeriod
   * is extended to the end of thar active Period.
   *
   * @return    ValidityPeriod
   */
  public function getValidityPeriod(): ValidityPeriod {
    $end = $this->end;

    if ($end instanceof \DateTime) {
      $periodAtEnd = $this->getPeriodAt($this->end);

      if ($periodAtEnd !== null) {
        $end = $periodAtEnd->getEnd();
      }
    }

    return new ValidityPeriod($this->start, $end, $this);
  }

  /**
   * Adjusts the start date of a covering ValidityPeriod if it is a concrete instance of \DateTime
   * and a period in this RecurringPeriods is happening during this date. In these cases the ValidityPeriod
   * is postponed until this Period has ended
   *
   * @param     ValidityPeriod    $coveringPeriod     Covering period
   * @return    ValidityPeriod                        Covering period with updated start if necessary
   */
  public function transformCoveringPeriod(ValidityPeriod $coveringPeriod): ValidityPeriod {
    $periodAtStart = $this->getPeriodAt($coveringPeriod->getStart());
    return $periodAtStart !== null
      ? new ValidityPeriod($periodAtStart->getEnd(), $coveringPeriod->getEnd(), $coveringPeriod->getEntry())
      : $coveringPeriod;
  }

  /** @inheritDoc */
  function toSerializableArray(): array {
    return [
      'kind' => self::SPEC_KIND,
      'start' => Dates::serialize($this->start),
      'end' => Dates::serialize($this->end),
      'periods' => array_map(function (RecurringPeriod $p) {
        return $p->toSerializableArray();
      }, $this->periods),
      'children' => array_map(function (SpecEntry $se) {
        return $se->toSerializableArray();
      }, $this->children)
    ];
  }

  /** @inheritDoc */
  static function fromSerializableArray(array $array): ArraySerializable {
    return new RecurringPeriods(
      Dates::deserialize($array['start']),
      Dates::deserialize($array['end']),
      array_map(function (array $ser) {
        return RecurringPeriod::fromSerializableArray($ser);
      }, $array['periods']),
      array_map(function (array $ser) {
        return SpecEntryParser::fromSerializableArray($ser);
      }, $array['children'])
    );
  }
}
