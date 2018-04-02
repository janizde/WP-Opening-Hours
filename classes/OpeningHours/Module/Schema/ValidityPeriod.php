<?php

namespace OpeningHours\Module\Schema;

use OpeningHours\Entity\Set;

/**
 * Represents a period in time in which the specified `$set` serves
 * the regular opening hours for the schema.org specification.
 * Instances of `ValidityPeriod` are immutable.
 *
 * @author  Jannik Portz <hello@jannikportz.de>
 * @package OpeningHours\Module\Schema
 */
class ValidityPeriod {

  /**
   * The Set serving the regular opening hours from `$start` through `$end`
   * @var     Set
   */
  protected $set;

  /**
   * The first date at which `$set` serves the regular opening hours
   * The time component must always be 00:00:00
   * @var     \DateTime
   */
  protected $start;

  /**
   * The last date at which `$set` serves the regular opening hours.
   * The time component must always be 00:00:00
   * @var     \DateTime
   */
  protected $end;

  /**
   * Creates a new `ValidityPeriod` with set, start and end
   *
   * @param     Set       $set
   * @param     \DateTime $start
   * @param     \DateTime $end
   *
   * @throws    \InvalidArgumentException   if `$start` is after `$end`
   */
  public function __construct(Set $set, \DateTime $start, \DateTime $end) {
    if ($end < $start) {
      throw new \InvalidArgumentException('$start must be before $end');
    }

    $this->set = $set;
    $this->start = $start;
    $this->end = $end;
  }

  /**
   * @return Set
   */
  public function getSet() {
    return $this->set;
  }

  /**
   * @return \DateTime
   */
  public function getStart() {
    return $this->start;
  }

  /**
   * @return \DateTime
   */
  public function getEnd() {
    return $this->end;
  }
}
