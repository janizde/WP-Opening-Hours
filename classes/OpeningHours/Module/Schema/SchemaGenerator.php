<?php

namespace OpeningHours\Module\Schema;

use OpeningHours\Entity\ChildSetWrapper;
use OpeningHours\Entity\Set;
use OpeningHours\Util\Dates;

/**
 * Generator for schema.org `OpeningHoursSpec` objects from Opening Hours `Set`s
 *
 * @author  Jannik Portz <hello@jannikportz.de>
 * @package OpeningHours\Module\Schema
 */
class SchemaGenerator {

  /**
   * The Set representing the main set to generate schema.org spec for
   * @var   Set
   */
  protected $mainSet;

  /**
   * All child sets of `$mainSet` wrapped in a `ChildSetWrapper`
   * @var   ChildSetWrapper[]
   */
  protected $childSets;

  /**
   * Creates a new `SchemaGenerator` from the main set and child sets
   *
   * @param     Set                 $mainSet      The main `Set` containing the regular opening hours, holidays and irregular openings
   * @param     ChildSetWrapper[]   $childSets    All child sets of `$mainSet` wrapped in `ChildSetWrapper`s
   *                                              Every child set must contain all of their respective children
   *                                              for the generator to consider the whole set tree
   */
  public function __construct(Set $mainSet, array $childSets = array()) {
    $this->mainSet = $mainSet;
    $this->childSets = $childSets;
  }

  /**
   * Creates a `ValiditySequence` of the specified `$child` and all of its children
   * The `ValiditySequence` will contain a `ValidityPeriod` of `$child` in the background
   * which will then recursively be covered by the respective children of `$child`
   *
   * Children of higher index in the `$children` array might overwrite previously created `ValidityPeriod`s
   *
   * @param     ChildSetWrapper   $child        Current child
   *
   * @return    ValiditySequence                Validity sequence containing `$child` and all its children recursively
   */
  public function createChildSetValiditySequence(ChildSetWrapper $child) {
    $ownValiditySequence = new ValiditySequence(array(
      new ValidityPeriod(
        $child->getSet(),
        $child->getStart(),
        $child->getEnd()
      ),
    ));

    /** @var ValiditySequence $sequenceWithChildren */
    $sequenceWithChildren = array_reduce(
      $child->getChildren(),
      function (ValiditySequence $sequence, ChildSetWrapper $childWrapper) {
        $childSequence = $this->createChildSetValiditySequence($childWrapper);
        return $sequence->coveredWith($childSequence);
      },
      $ownValiditySequence
    );

    return $sequenceWithChildren->restrictedToDateRange($child->getStart(), $child->getEnd());
  }

  /**
   * Creates a `ValiditySequence` containing the `$mainSet` and all `$childSets`
   *
   * Gaps between child sets will be filled with `ValidityPeriod`s of the main set.
   * The first ValidityPeriod will start at -INF and the last end at INF.
   * If there are no child sets a single ValidityPeriod from -INF to INF will be created.
   *
   * @return      ValiditySequence                sequence of `$mainSet` and `$childSets` validity
   *
   * @throws      \Exception                      If the `interval_spec` of a \DateInterval is invalid
   */
  public function createSetValiditySequence() {
    $mainWrapper = new ChildSetWrapper($this->mainSet, -INF, INF, null, $this->childSets);
    return $this->createChildSetValiditySequence($mainWrapper);
  }
}
