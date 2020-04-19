<?php

namespace OpeningHours\Core;

use OpeningHours\Util\Dates;

/**
 * Specification entry describing a holiday during which a venue is considered closed
 * @package OpeningHours\Core
 */
class Holiday implements SpecEntry {
  const SPEC_KIND = 'holiday';

  /**
   * Start date of the holiday (inclusive)
   * @var \DateTime
   */
  private $start;

  /**
   * End date of the holiday (exclusive)
   * @var \DateTime
   */
  private $end;

  /**
   * Display name of the holiday
   * @var string
   */
  private $name;

  public function __construct(string $name, \DateTime $start, \DateTime $end) {
    $this->name = $name;
    $this->start = $start;
    $this->end = $end;
  }

  function getName(): string {
    return $this->name;
  }

  /** @inheritDoc */
  function getKind(): string {
    return Holiday::SPEC_KIND;
  }

  /** @inheritDoc */
  function getChildren(): array {
    return array();
  }

  /** @inheritDoc */
  function getValidityPeriod(): ValidityPeriod {
    return new ValidityPeriod($this->start, $this->end, $this);
  }

  /** @inheritDoc */
  function transformCoveringPeriod(ValidityPeriod $period): ValidityPeriod {
    return $period;
  }

  /** @inheritDoc */
  function toSerializableArray(): array {
    return [
      'kind' => Holiday::SPEC_KIND,
      'name' => $this->name,
      'start' => Dates::serialize($this->start),
      'end' => Dates::serialize($this->end),
    ];
  }

  /** @inheritDoc */
  static function fromSerializableArray(array $array): ArraySerializable {
    return new Holiday(
      $array['name'],
      Dates::deserialize($array['start']),
      Dates::deserialize($array['end'])
    );
  }
}
