<?php

namespace OpeningHours\Module\Schema;

use OpeningHours\Entity\ChildSetWrapper;
use OpeningHours\Entity\Holiday;
use OpeningHours\Entity\IrregularOpening;
use OpeningHours\Entity\Period;
use OpeningHours\Entity\Set;
use OpeningHours\Util\Dates;

/**
 * Generator for schema.org `OpeningHoursSpec` objects from Opening Hours `Set`s
 *
 * @author  Jannik Portz <hello@jannikportz.de>
 * @package OpeningHours\Module\Schema
 */
class SchemaGenerator {
  const SCHEMA_TIME_FORMAT = 'H:i';
  const SCHEMA_DATE_FORMAT = 'Y-m-d';

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
   * Creates the complete Schema.org opening hours schema as an associative array corrsponding
   * to the JSON-LD format
   *
   * @return    array     Associative array containing JSON-LD schema
   */
  public function createOpeningHoursSpecificationEntries() {
    $sequence = $this->createSetValiditySequence();
    return $this->createOpeningHoursSpecDefinition($sequence);
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
      new ValidityPeriod($child->getSet(), $child->getStart(), $child->getEnd())
    ));

    /**
     * Compatibility as $this cannot be referenced in the closure in PHO 5.3
     * @todo                  Remove when requirement is PHP >= 5.4
     */
    $self = $this;

    /** @var ValiditySequence $sequenceWithChildren */
    $sequenceWithChildren = array_reduce(
      $child->getChildren(),
      function (ValiditySequence $sequence, ChildSetWrapper $childWrapper) use ($self) {
        $childSequence = $self->createChildSetValiditySequence($childWrapper);
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
   */
  public function createSetValiditySequence() {
    $mainWrapper = new ChildSetWrapper($this->mainSet, -INF, INF, null, $this->childSets);
    return $this->createChildSetValiditySequence($mainWrapper);
  }

  /**
   * Creates schema.org OpeningHoursSpecification objects for the whole ValiditySequence
   * contained in `$vs` for the current Set.
   *
   * @param     ValiditySequence    $vs     The ValiditySequence from which to create the spec items
   * @return    array                       Array of associative arrays representing the OpeningHoursSpecification
   *                                        objects of all child sets and the main set in `$vp`
   */
  public function createOpeningHoursSpecDefinition(ValiditySequence $vs) {
    $that = $this;
    return array_reduce(
      $vs->getPeriods(),
      function (array $specs, ValidityPeriod $p) use ($that) {
        return array_merge($specs, $that->createSpecItemsFromValidityPeriod($p));
      },
      array()
    );
  }

  /**
   * Creates OpeningHoursSpecification objects for the current Set's Holidays.
   * All past items will not be considered.
   *
   * @return      array[]         Sequence of OpeningHoursSpecification objects
   *                              representing the current Set's Holidays
   */
  public function createHolidaysOpeningHoursSpecification() {
    $now = Dates::getNow();

    $holidays = array_values(
      array_filter($this->mainSet->getHolidays()->getArrayCopy(), function (Holiday $holiday) use ($now) {
        $result = $holiday->getEnd() > $now;
        return $result;
      })
    );

    return array_map(function (Holiday $h) {
      return array(
        '@type' => 'OpeningHoursSpecification',
        'name' => $h->getName(),
        'validFrom' => $h->getStart()->format(SchemaGenerator::SCHEMA_DATE_FORMAT),
        'validThrough' => $h->getEnd()->format(SchemaGenerator::SCHEMA_DATE_FORMAT)
      );
    }, $holidays);
  }

  /**
   * Creates OpeningHoursSpecification objects for the current Set's Irregular Openings.
   * All past items will not be considered.
   *
   * @return      array[]         Sequence of OpeningHoursSpecification objects
   *                              representing the current Set's Irregular Openings
   */
  public function createIrregularOpeningHoursSpecification() {
    $now = Dates::getNow();

    $ios = array_values(
      array_filter($this->mainSet->getIrregularOpenings()->getArrayCopy(), function (IrregularOpening $io) use ($now) {
        return $io->getEnd() > $now;
      })
    );

    return array_map(function (IrregularOpening $io) {
      return array(
        '@type' => 'OpeningHoursSpecification',
        'name' => $io->getName(),
        'opens' => $io->getStart()->format(SchemaGenerator::SCHEMA_TIME_FORMAT),
        'closes' => $io->getEnd()->format(SchemaGenerator::SCHEMA_TIME_FORMAT),
        'validFrom' => $io->getDate()->format(SchemaGenerator::SCHEMA_DATE_FORMAT),
        'validThrough' => $io->getDate()->format(SchemaGenerator::SCHEMA_DATE_FORMAT)
      );
    }, $ios);
  }

  /**
   * Creates OpeningHoursSpec definition objects from a validity period
   * only taking the Sets' periods into consideration.
   *
   * When start or end of $vp are infinite the respective restrictional property,
   * i.e. `validFrom` and `validThrough` will be omitted.
   *
   * The OpeningHoursSpec objects are represented as associative arrays.
   *
   *
   * @param     ValidityPeriod    $vp     The ValidityPeriod from which to create
   *                                      the spec objects
   *
   * @return      array[]                 Sequence of associative arrays representing
   *                                      OpeningHoursSpec objects
   */
  public function createSpecItemsFromValidityPeriod(ValidityPeriod $vp) {
    $weekdays = SchemaGenerator::getSchemaWeekdays();

    return array_map(
      function (Period $p) use ($vp, $weekdays) {
        $spec = array(
          '@type' => 'OpeningHoursSpecification',
          'opens' => $p->getTimeStart()->format(SchemaGenerator::SCHEMA_TIME_FORMAT),
          'closes' => $p->getTimeEnd()->format(SchemaGenerator::SCHEMA_TIME_FORMAT),
          'dayOfWeek' => $weekdays[$p->getWeekday()]
        );

        if ($vp->getStart() instanceof \DateTime) {
          $spec['validFrom'] = $vp->getStart()->format(SchemaGenerator::SCHEMA_DATE_FORMAT);
        }

        if ($vp->getEnd() instanceof \DateTime) {
          $spec['validThrough'] = $vp->getEnd()->format(SchemaGenerator::SCHEMA_DATE_FORMAT);
        }

        return $spec;
      },
      $vp
        ->getSet()
        ->getPeriods()
        ->getArrayCopy()
    );
  }

  /**
   * Creates Schema.org URIs for all weekdays.
   * Array indices correspond to the week day numbers starting at Sunday
   *
   * @todo                  Move to class constant when requirement is PHP >= 5.6
   * @return      array     Weekdays for schema
   */
  public static function getSchemaWeekdays() {
    return array(
      'http://schema.org/Sunday',
      'http://schema.org/Monday',
      'http://schema.org/Tuesday',
      'http://schema.org/Wednesday',
      'http://schema.org/Thursday',
      'http://schema.org/Friday',
      'http://schema.org/Saturday'
    );
  }
}
