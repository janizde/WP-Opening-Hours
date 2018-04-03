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
   * @param     \DateTime         $defaultMin   Default start date of a child when it has no own start value
   * @param     \DateTime         $defaultMax   Default end date of a child when it has no own end value
   *
   * @return    ValiditySequence                Validity sequence containing `$child` and all its children recursively
   */
  public function createChildSetValiditySequence(ChildSetWrapper $child, \DateTime $defaultMin, \DateTime $defaultMax) {
    $childStart = $child->getStart() ?: $defaultMin;
    $childEnd = $child->getEnd() ?: $defaultMax;

    $ownValiditySequence = new ValiditySequence(array(
      new ValidityPeriod(
        $child->getSet(),
        $childStart,
        $childEnd
      ),
    ));

    /** @var ValiditySequence $sequenceWithChildren */
    $sequenceWithChildren = array_reduce(
      $child->getChildren(),
      function (ValiditySequence $sequence, ChildSetWrapper $childWrapper) use ($defaultMin, $defaultMax) {
        $childSequence = $this->createChildSetValiditySequence($childWrapper, $defaultMin, $defaultMax);
        return $sequence->coveredWith($childSequence);
      },
      $ownValiditySequence
    );

    return $sequenceWithChildren->restrictedToDateRange($childStart, $childEnd);
  }

  /**
   * Creates a `ValiditySequence` containing the `$mainSet` and all `$childSets`
   *
   * Gaps between child sets will be filled with `ValidityPeriod`s of the main set.
   * The `ValiditySequence`'s `start` date is always the current date (or `$referenceNow`)
   * The `ValiditySequence`'s `end` date is the last child set's `dateEnd` but at least `$referenceNow` plus one year
   *
   * @param       \DateTime       $referenceNow   Reference date time representing the current time
   *                                              Will be the first item's `start` value
   *
   * @return      ValiditySequence                sequence of `$mainSet` and `$childSets` validity
   *
   * @throws      \Exception                      If the `interval_spec` of a \DateInterval is invalid
   */
  public function createSetValiditySequence(\DateTime $referenceNow = null) {
    $now = $referenceNow === null ? Dates::getNow() : $referenceNow;
    $now->setTime(0,0,0);
    $maxEnd = clone $now;
    $maxEnd->add(new \DateInterval('P1Y'));

    $childSets = array_filter($this->childSets, function (ChildSetWrapper $child) use ($now) {
      return !$child->isPast($now);
    });

    $latestDefault = clone $maxEnd;
    $latestDefault->sub(new \DateInterval('P1D'));
    // Determine latest explicitly set end date or one year in future from the generated child partials
    $latestSetDate = array_reduce($childSets, function (\DateTime $latest, ChildSetWrapper $childWrapper) {
      return max($latest, $childWrapper->getEnd());
    }, $latestDefault);

    $mainWrapper = new ChildSetWrapper($this->mainSet, $now, $latestSetDate, null, $childSets);
    return $this->createChildSetValiditySequence($mainWrapper, $now, $latestSetDate);
  }
}
