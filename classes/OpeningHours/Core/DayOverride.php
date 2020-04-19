<?php

namespace OpeningHours\Core;

use OpeningHours\Util\Dates;

/**
 * Specification entry overriding a whole day's specification with 0..n periods
 * This specification entry kind does not support children
 *
 * @package OpeningHours\Core
 */
class DayOverride implements SpecEntry {
  const SPEC_KIND = 'dayOverride';

  /**
   * Name of the day override
   * @var string
   */
  private $name;

  /**
   * Date of the day that is overridden
   * @var \DateTime
   */
  private $date;

  /**
   * Periods of the day override
   * @var Period[]
   */
  private $periods;

  public function __construct(string $name, \DateTime $date, array $periods) {
    $this->name = $name;
    $this->date = $date;
    $this->periods = $periods;
  }

  function getName() {
    return $this->name;
  }

  function getPeriods() {
    return $this->periods;
  }

  /** @inheritDoc */
  function getKind(): string {
    return DayOverride::SPEC_KIND;
  }

  /** @inheritDoc */
  function getStart() {
    return $this->date;
  }

  /** @inheritDoc */
  function getEnd() {
    $end = clone $this->date;
    $end->add(new \DateInterval('P1D'));
    return $end;
  }

  /** @inheritDoc */
  function getChildren(): array {
    return [];
  }

  /** @inheritDoc */
  function transformCoveringPeriod(ValidityPeriod $coveringPeriod): ValidityPeriod {
    $coveringStart = $coveringPeriod->getStart();
    if (!$coveringStart instanceof \DateTime) {
      return $coveringPeriod;
    }

    $periodAtStart = null;
    foreach ($this->periods as $period) {
      if ($period->getStart() <= $coveringStart && $period->getEnd() > $coveringStart) {
        $periodAtStart = $period;
        break;
      }
    }

    return $periodAtStart !== null
      ? new ValidityPeriod($periodAtStart->getEnd(), $coveringPeriod->getEnd(), $coveringPeriod->getEntry())
      : $coveringPeriod;
  }

  /**
   * Creates a ValidityPeriod that reaches from the beginning of `$date` until at least the beginning of the next day
   * or the most exceeding period's end date if it reaches outside of the start date
   *
   * @inheritDoc
   */
  public function getValidityPeriod(): ValidityPeriod {
    $periodEnds = array_map(function (Period $p) {
      return $p->getEnd();
    }, $this->periods);
    $nextDay = clone $this->date;
    $nextDay->add(new \DateInterval('P1D'));
    $end = array_reduce(
      $periodEnds,
      function (\DateTime $a, \DateTime $b) {
        return Dates::max($a, $b);
      },
      $nextDay
    );

    return new ValidityPeriod($this->getStart(), $end, $this);
  }

  /** @inheritDoc */
  function toSerializableArray(): array {
    return [
      'kind' => DayOverride::SPEC_KIND,
      'name' => $this->name,
      'date' => Dates::serialize($this->date),
      'periods' => array_map(function (Period $p) { return $p->toSerializableArray(); }, $this->periods),
    ];
  }

  /** @inheritDoc */
  static function fromSerializableArray(array $array): ArraySerializable {
    return new DayOverride(
      $array['name'],
      Dates::deserialize($array['date']),
      array_map(function (array $ser) { return Period::fromSerializableArray($ser); }, $array['periods'])
    );
  }
}
