<?php

namespace OpeningHours\Test\Module\Schema;

use OpeningHours\Entity\ChildSetWrapper;
use OpeningHours\Entity\Set;
use OpeningHours\Module\Schema\SchemaGenerator;
use OpeningHours\Module\Schema\ValidityPeriod;
use OpeningHours\Module\Schema\ValiditySequence;
use OpeningHours\Test\OpeningHoursTestCase;

class SchemaGeneratorTest extends OpeningHoursTestCase {

  /**
   * - `createSetValiditySequence` creates one `ValidityPeriod` containing the main set
   *   from `$referenceNow` through one year in future
   */
  public function testCreateSetValiditySequence_NoChildren() {
    $set = new Set(0);
    $sg = new SchemaGenerator($set);

    $result = $sg->createSetValiditySequence(new \DateTime('2018-04-01'));

    $expected = new ValiditySequence(array(
      new ValidityPeriod($set, new \DateTime('2018-04-01'), new \DateTime('2019-03-31')),
    ));

    $this->assertEquals($expected, $result);
  }

  /**
   * - `createSetValiditySequence` creates 3 `ValidityPeriods` when there is one child
   *   in the middle of the main set
   */
  public function testCreateSetValiditySequence_OneChildMiddle() {
    $set = new Set(0);
    $childSet = new Set(1);
    $child = new ChildSetWrapper($childSet, new \DateTime('2018-04-05'), new \DateTime('2018-04-10'));
    $sg = new SchemaGenerator($set, array($child));

    $result = $sg->createSetValiditySequence(new \DateTime('2018-04-01'));

    $expected = new ValiditySequence(array(
      new ValidityPeriod($set, new \DateTime('2018-04-01'), new \DateTime('2018-04-04')),
      new ValidityPeriod($childSet, new \DateTime('2018-04-05'), new \DateTime('2018-04-10')),
      new ValidityPeriod($set, new \DateTime('2018-04-11'), new \DateTime('2019-03-31')),
    ));

    $this->assertEquals($expected, $result);
  }

  /**
   * - `createSetValiditySequence` prepends a child period when the child is at the beginning of the whole sequence
   */
  public function testCreateSetValiditySequence_OneChildStart() {
    $set = new Set(0);
    $childSet = new Set(1);
    $child = new ChildSetWrapper($childSet, new \DateTime('2018-04-01'), new \DateTime('2018-04-10'));
    $sg = new SchemaGenerator($set, array($child));

    $result = $sg->createSetValiditySequence(new \DateTime('2018-04-01'));

    $expected = new ValiditySequence(array(
      new ValidityPeriod($childSet, new \DateTime('2018-04-01'), new \DateTime('2018-04-10')),
      new ValidityPeriod($set, new \DateTime('2018-04-11'), new \DateTime('2019-03-31')),
    ));

    $this->assertEquals($expected, $result);
  }

  /**
   * - `createSetValiditySequence` appends a child period when the child is at the end of the whole sequence
   */
  public function testCreateSetValiditySequence_OneChildEnd() {
    $set = new Set(0);
    $childSet = new Set(1);
    $child = new ChildSetWrapper($childSet, new \DateTime('2018-04-30'), new \DateTime('2019-03-31'));
    $sg = new SchemaGenerator($set, array($child));

    $result = $sg->createSetValiditySequence(new \DateTime('2018-04-01'));

    $expected = new ValiditySequence(array(
      new ValidityPeriod($set, new \DateTime('2018-04-01'), new \DateTime('2018-04-29')),
      new ValidityPeriod($childSet, new \DateTime('2018-04-30'), new \DateTime('2019-03-31')),
    ));

    $this->assertEquals($expected, $result);
  }

  /**
   * - `createSetValiditySequence` does not add periods for children that are fully in the past
   */
  public function testCreateSetValiditySequence_OneChildInPast() {
    $set = new Set(0);
    $childSet = new Set(1);
    $child = new ChildSetWrapper($childSet, new \DateTime('2018-02-30'), new \DateTime('2018-03-31'));
    $sg = new SchemaGenerator($set, array($child));

    $result = $sg->createSetValiditySequence(new \DateTime('2018-04-01'));

    $expected = new ValiditySequence(array(
      new ValidityPeriod($set, new \DateTime('2018-04-01'), new \DateTime('2019-03-31')),
    ));

    $this->assertEquals($expected, $result);
  }

  /**
   * - `createSetValiditySequence` creates only one period when the child has exactly the same date range as the parent
   */
  public function testCreateSetValiditySequence_OneChildWholePeriod() {
    $set = new Set(0);
    $childSet = new Set(1);
    $child = new ChildSetWrapper($childSet, new \DateTime('2018-04-01'), new \DateTime('2019-03-31'));
    $sg = new SchemaGenerator($set, array($child));

    $result = $sg->createSetValiditySequence(new \DateTime('2018-04-01'));

    $expected = new ValiditySequence(array(
      new ValidityPeriod($childSet, new \DateTime('2018-04-01'), new \DateTime('2019-03-31')),
    ));

    $this->assertEquals($expected, $result);
  }

  /**
   * - `createSetValiditySequence` restricts the period of the child set its parent's date range
   */
  public function testCreateSetValiditySequence_OneChildWholeAndBeyond() {
    $set = new Set(0);
    $childSet = new Set(1);
    $child = new ChildSetWrapper($childSet, new \DateTime('2018-03-03'), new \DateTime('2019-04-30'));
    $sg = new SchemaGenerator($set, array($child));

    $result = $sg->createSetValiditySequence(new \DateTime('2018-04-01'));

    $expected = new ValiditySequence(array(
      new ValidityPeriod($childSet, new \DateTime('2018-04-01'), new \DateTime('2019-04-30')),
    ));

    $this->assertEquals($expected, $result);
  }

  /**
   * - `createSetValiditySequence` adds a gap period when the gap between two child periods is at least one day long
   */
  public function testCreateSetValiditySequence_TwoChildrenWithGap() {
    $set = new Set(0);
    $childSet1 = new Set(1);
    $child1 = new ChildSetWrapper($childSet1, new \DateTime('2018-04-05'), new \DateTime('2018-04-10'));
    $childSet2 = new Set(2);
    $child2 = new ChildSetWrapper($childSet2, new \DateTime('2018-04-12'), new \DateTime('2018-04-14'));
    $sg = new SchemaGenerator($set, array($child2, $child1));

    $result = $sg->createSetValiditySequence(new \DateTime('2018-04-01'));

    $expected = new ValiditySequence(array(
      new ValidityPeriod($set, new \DateTime('2018-04-01'), new \DateTime('2018-04-04')),
      new ValidityPeriod($childSet1, new \DateTime('2018-04-05'), new \DateTime('2018-04-10')),
      new ValidityPeriod($set, new \DateTime('2018-04-11'), new \DateTime('2018-04-11')),
      new ValidityPeriod($childSet2, new \DateTime('2018-04-12'), new \DateTime('2018-04-14')),
      new ValidityPeriod($set, new \DateTime('2018-04-15'), new \DateTime('2019-03-31')),
    ));

    $this->assertEquals($expected, $result);
  }

  /**
   * - `createSetValiditySequence` does not add gap periods when there is no gap between the child periods
   */
  public function testCreateSetValiditySequence_TwoChildrenWithoutGap() {
    $set = new Set(0);
    $childSet1 = new Set(1);
    $child1 = new ChildSetWrapper($childSet1, new \DateTime('2018-04-05'), new \DateTime('2018-04-11'));
    $childSet2 = new Set(2);
    $child2 = new ChildSetWrapper($childSet2, new \DateTime('2018-04-12'), new \DateTime('2018-04-14'));
    $sg = new SchemaGenerator($set, array($child2, $child1));

    $result = $sg->createSetValiditySequence(new \DateTime('2018-04-01'));

    $expected = new ValiditySequence(array(
      new ValidityPeriod($set, new \DateTime('2018-04-01'), new \DateTime('2018-04-04')),
      new ValidityPeriod($childSet1, new \DateTime('2018-04-05'), new \DateTime('2018-04-11')),
      new ValidityPeriod($childSet2, new \DateTime('2018-04-12'), new \DateTime('2018-04-14')),
      new ValidityPeriod($set, new \DateTime('2018-04-15'), new \DateTime('2019-03-31')),
    ));

    $this->assertEquals($expected, $result);
  }

  /**
   * - `createSetValiditySequence` overwrites child periods that come from a child at the beginning of the children array
   */
  public function testCreateSetValiditySequence_TwoChildrenOverlapping() {
    $set = new Set(0);
    $childSet1 = new Set(1);
    $child1 = new ChildSetWrapper($childSet1, new \DateTime('2018-04-05'), new \DateTime('2018-04-13'));
    $childSet2 = new Set(2);
    $child2 = new ChildSetWrapper($childSet2, new \DateTime('2018-04-12'), new \DateTime('2018-04-14'));
    $sg = new SchemaGenerator($set, array($child2, $child1));

    $result = $sg->createSetValiditySequence(new \DateTime('2018-04-01'));

    $expected = new ValiditySequence(array(
      new ValidityPeriod($set, new \DateTime('2018-04-01'), new \DateTime('2018-04-04')),
      new ValidityPeriod($childSet1, new \DateTime('2018-04-05'), new \DateTime('2018-04-13')),
      new ValidityPeriod($childSet2, new \DateTime('2018-04-14'), new \DateTime('2018-04-14')),
      new ValidityPeriod($set, new \DateTime('2018-04-15'), new \DateTime('2019-03-31')),
    ));

    $this->assertEquals($expected, $result);
  }

  /**
   * - `createSetValiditySequence` lets the child set's start date go through the parent start date
   *   if no start date has been explicitly set
   */
  public function testCreateSetValiditySequence_OneChildWithoutDateStart() {
    $set = new Set(0);
    $childSet = new Set(1);
    $child = new ChildSetWrapper($childSet, null, new \DateTime('2018-04-13'));
    $sg = new SchemaGenerator($set, array($child));

    $result = $sg->createSetValiditySequence(new \DateTime('2018-04-01'));

    $expected = new ValiditySequence(array(
      new ValidityPeriod($childSet, new \DateTime('2018-04-01'), new \DateTime('2018-04-13')),
      new ValidityPeriod($set, new \DateTime('2018-04-14'), new \DateTime('2019-03-31')),
    ));

    $this->assertEquals($expected, $result);
  }

  /**
   * - `createSetValiditySequence` lets the child set's end date go through the parent end date
   *   if no end date has been explicitly set
   */
  public function testCreateSetValiditySequence_OneChildWithoutDateEnd() {
    $set = new Set(0);
    $childSet = new Set(1);
    $child = new ChildSetWrapper($childSet, new \DateTime('2018-04-13'), null);
    $sg = new SchemaGenerator($set, array($child));

    $result = $sg->createSetValiditySequence(new \DateTime('2018-04-01'));

    $expected = new ValiditySequence(array(
      new ValidityPeriod($set, new \DateTime('2018-04-01'), new \DateTime('2018-04-12')),
      new ValidityPeriod($childSet, new \DateTime('2018-04-13'), new \DateTime('2019-03-31')),
    ));

    $this->assertEquals($expected, $result);
  }

  /**
   * - `createSetValiditySequence` lets the child set's start and end date go through the parent start and end date
   *   if neither start nor end date have been explicitly set
   */
  public function testCreateSetValiditySequence_OneChildWithoutAnyDate() {
    $set = new Set(0);
    $childSet = new Set(1);
    $child = new ChildSetWrapper($childSet, null, null);
    $sg = new SchemaGenerator($set, array($child));

    $result = $sg->createSetValiditySequence(new \DateTime('2018-04-01'));

    $expected = new ValiditySequence(array(
      new ValidityPeriod($childSet, new \DateTime('2018-04-01'), new \DateTime('2019-03-31')),
    ));

    $this->assertEquals($expected, $result);
  }
}
