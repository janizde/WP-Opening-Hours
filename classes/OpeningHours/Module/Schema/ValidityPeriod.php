<?php

namespace OpeningHours\Module\Schema;

use OpeningHours\Entity\Set;
use OpeningHours\Util\Dates;

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
   * The first date at which `$set` serves the regular opening hours.
   * The time component must always be 00:00:00.
   * May also be `-INF` to indicate that the `ValidityPeriod` has no start
   *
   * @var     \DateTime|float
   */
  protected $start;

  /**
   * The last date at which `$set` serves the regular opening hours.
   * The time component must always be 00:00:00.
   * May also be `INF` to indicate that the `ValidityPeriod` has no end
   *
   * @var     \DateTime|null
   */
  protected $end;

  /**
   * Creates a new `ValidityPeriod` with set, start and end
   *
   * @param     Set       $set
   * @param     \DateTime|float   $start    Start as DateTime or -INF
   * @param     \DateTime|float   $end      Start as DateTime o INF
   *
   * @throws    \InvalidArgumentException   If $start or $end are incorrect or $start is after $end
   */
  public function __construct(Set $set, $start, $end) {
    if (!$start instanceof \DateTime && $start !== -INF) {
      throw new \InvalidArgumentException('$start must either be an instance of \\DateTime or -INF');
    }

    if (!$end instanceof \DateTime && $end !== INF) {
      throw new \InvalidArgumentException('\end must either be an instance of \\DateTime or INF');
    }

    if (Dates::compareDateTime($start, $end) > 0) {
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
   * @return \DateTime|float
   */
  public function getStart() {
    return $this->start;
  }

  /**
   * @return \DateTime|float
   */
  public function getEnd() {
    return $this->end;
  }
}
