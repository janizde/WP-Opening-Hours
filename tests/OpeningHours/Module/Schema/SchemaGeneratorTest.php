<?php

namespace OpeningHours\Test\Module\Schema;

use OpeningHours\Entity\ChildSetWrapper;
use OpeningHours\Entity\Set;
use OpeningHours\Module\Schema\SchemaGenerator;
use OpeningHours\Test\OpeningHoursTestCase;

class SchemaGeneratorTest extends OpeningHoursTestCase {

  /**
   * Tests `SchemaGenerator::createSetValidityOrder`
   * with no child sets
   */
  public function testCreateSetValidityOrderNoChildren() {
    $set = new Set(0);
    $sg = new SchemaGenerator($set);

    $result = $sg->createSetValidityOrder(new \DateTime('2018-04-01'));

    $expected = array(
      array(
        'set' => $set,
        'start' => new \DateTime('2018-04-01'),
        'end' => new \DateTime('2019-03-31'),
      ),
    );

    $this->assertTrue(is_array($result));
    $this->assertEquals($expected, $result);
  }

  /**
   * Tests `SchemaGenerator::createSetValidityOrder`
   * with one child set in the middle of the whole period
   */
  public function testCreateSetValidityOrderOneChildMiddle() {
    $set = new Set(0);
    $childSet = new Set(1);
    $child = new ChildSetWrapper($childSet, new \DateTime('2018-04-05'), new \DateTime('2018-04-10'));
    $sg = new SchemaGenerator($set, array($child));

    $result = $sg->createSetValidityOrder(new \DateTime('2018-04-01'));

    $expected = array(
      array(
        'set' => $set,
        'start' => new \DateTime('2018-04-01'),
        'end' => new \DateTime('2018-04-04'),
      ),
      array(
        'set' => $childSet,
        'start' => new \DateTime('2018-04-05'),
        'end' => new \DateTime('2018-04-10'),
      ),
      array(
        'set' => $set,
        'start' => new \DateTime('2018-04-11'),
        'end' => new \DateTime('2019-03-31'),
      ),
    );

    $this->assertTrue(is_array($result));
    $this->assertEquals($expected, $result);
  }

  /**
   * Tests `SchemaGenerator::createSetValidityOrder`
   * with one child set at the beginning of the whole period
   */
  public function testCreateSetValidityOrderOneChildStart() {
    $set = new Set(0);
    $childSet = new Set(1);
    $child = new ChildSetWrapper($childSet, new \DateTime('2018-04-01'), new \DateTime('2018-04-10'));
    $sg = new SchemaGenerator($set, array($child));

    $result = $sg->createSetValidityOrder(new \DateTime('2018-04-01'));

    $expected = array(
      array(
        'set' => $childSet,
        'start' => new \DateTime('2018-04-01'),
        'end' => new \DateTime('2018-04-10'),
      ),
      array(
        'set' => $set,
        'start' => new \DateTime('2018-04-11'),
        'end' => new \DateTime('2019-03-31'),
      ),
    );

    $this->assertTrue(is_array($result));
    $this->assertEquals($expected, $result);
  }

  /**
   * Tests `SchemaGenerator::createSetValidityOrder`
   * with one child set at the end of the whole period
   */
  public function testCreateSetValidityOrderOneChildEnd() {
    $set = new Set(0);
    $childSet = new Set(1);
    $child = new ChildSetWrapper($childSet, new \DateTime('2018-04-30'), new \DateTime('2019-03-31'));
    $sg = new SchemaGenerator($set, array($child));

    $result = $sg->createSetValidityOrder(new \DateTime('2018-04-01'));

    $expected = array(
      array(
        'set' => $set,
        'start' => new \DateTime('2018-04-01'),
        'end' => new \DateTime('2018-04-29'),
      ),
      array(
        'set' => $childSet,
        'start' => new \DateTime('2018-04-30'),
        'end' => new \DateTime('2019-03-31'),
      ),
    );

    $this->assertTrue(is_array($result));
    $this->assertEquals($expected, $result);
  }

  /**
   * Tests `SchemaGenerator::createSetValidityOrder`
   * with one child set in the past
   */
  public function testCreateSetValidityOrderOneChildInPast() {
    $set = new Set(0);
    $childSet = new Set(1);
    $child = new ChildSetWrapper($childSet, new \DateTime('2018-02-30'), new \DateTime('2018-03-31'));
    $sg = new SchemaGenerator($set, array($child));

    $result = $sg->createSetValidityOrder(new \DateTime('2018-04-01'));

    $expected = array(
      array(
        'set' => $set,
        'start' => new \DateTime('2018-04-01'),
        'end' => new \DateTime('2019-03-31'),
      ),
    );

    $this->assertTrue(is_array($result));
    $this->assertEquals($expected, $result);
  }

  /**
   * Tests `SchemaGenerator::createSetValidityOrder`
   * with one child set taking up the whole period
   */
  public function testCreateSetValidityOrderOneChildWholePeriod() {
    $set = new Set(0);
    $childSet = new Set(1);
    $child = new ChildSetWrapper($childSet, new \DateTime('2018-04-01'), new \DateTime('2019-03-31'));
    $sg = new SchemaGenerator($set, array($child));

    $result = $sg->createSetValidityOrder(new \DateTime('2018-04-01'));

    $expected = array(
      array(
        'set' => $childSet,
        'start' => new \DateTime('2018-04-01'),
        'end' => new \DateTime('2019-03-31'),
      ),
    );

    $this->assertTrue(is_array($result));
    $this->assertEquals($expected, $result);
  }

  /**
   * Tests `SchemaGenerator::createSetValidityOrder`
   * with one child set taking up the whole period and beyond (before and after)
   */
  public function testCreateSetValidityOrderOneChildWholeAndBeyond() {
    $set = new Set(0);
    $childSet = new Set(1);
    $child = new ChildSetWrapper($childSet, new \DateTime('2018-02-31'), new \DateTime('2019-04-30'));
    $sg = new SchemaGenerator($set, array($child));

    $result = $sg->createSetValidityOrder(new \DateTime('2018-04-01'));

    $expected = array(
      array(
        'set' => $childSet,
        'start' => new \DateTime('2018-04-01'), // Starts now
        'end' => new \DateTime('2019-04-30'), // Ends with child set
      ),
    );

    $this->assertTrue(is_array($result));
    $this->assertEquals($expected, $result);
  }

  /**
   * Tests `SchemaGenerator::createSetValidityOrder`
   * with two child sets with a gap between them
   */
  public function testCreateSetValidityOrderTwoChildrenWithGap() {
    $set = new Set(0);
    $childSet1 = new Set(1);
    $child1 = new ChildSetWrapper($childSet1, new \DateTime('2018-04-05'), new \DateTime('2018-04-10'));
    $childSet2 = new Set(2);
    $child2 = new ChildSetWrapper($childSet2, new \DateTime('2018-04-12'), new \DateTime('2018-04-14'));
    $sg = new SchemaGenerator($set, array($child2, $child1));

    $result = $sg->createSetValidityOrder(new \DateTime('2018-04-01'));

    $expected = array(
      array(
        'set' => $set,
        'start' => new \DateTime('2018-04-01'),
        'end' => new \DateTime('2018-04-04'),
      ),
      array(
        'set' => $childSet1,
        'start' => new \DateTime('2018-04-05'),
        'end' => new \DateTime('2018-04-10'),
      ),
      array(
        'set' => $set,
        'start' => new \DateTime('2018-04-11'),
        'end' => new \DateTime('2018-04-11'),
      ),
      array(
        'set' => $childSet2,
        'start' => new \DateTime('2018-04-12'),
        'end' => new \DateTime('2018-04-14'),
      ),
      array(
        'set' => $set,
        'start' => new \DateTime('2018-04-15'),
        'end' => new \DateTime('2019-03-31'),
      ),
    );

    $this->assertTrue(is_array($result));
    $this->assertEquals($expected, $result);
  }

  /**
   * Tests `SchemaGenerator::createSetValidityOrder`
   * with two child sets with no gap between them
   */
  public function testCreateSetValidityOrderTwoChildrenWithoutGap() {
    $set = new Set(0);
    $childSet1 = new Set(1);
    $child1 = new ChildSetWrapper($childSet1, new \DateTime('2018-04-05'), new \DateTime('2018-04-11'));
    $childSet2 = new Set(2);
    $child2 = new ChildSetWrapper($childSet2, new \DateTime('2018-04-12'), new \DateTime('2018-04-14'));
    $sg = new SchemaGenerator($set, array($child2, $child1));

    $result = $sg->createSetValidityOrder(new \DateTime('2018-04-01'));

    $expected = array(
      array(
        'set' => $set,
        'start' => new \DateTime('2018-04-01'),
        'end' => new \DateTime('2018-04-04'),
      ),
      array(
        'set' => $childSet1,
        'start' => new \DateTime('2018-04-05'),
        'end' => new \DateTime('2018-04-11'),
      ),
      array(
        'set' => $childSet2,
        'start' => new \DateTime('2018-04-12'),
        'end' => new \DateTime('2018-04-14'),
      ),
      array(
        'set' => $set,
        'start' => new \DateTime('2018-04-15'),
        'end' => new \DateTime('2019-03-31'),
      ),
    );

    $this->assertTrue(is_array($result));
    $this->assertEquals($expected, $result);
  }

  /**
   * Tests `SchemaGenerator::createSetValidityOrder`
   * with two child sets overlapping
   */
  public function testCreateSetValidityOrderTwoChildrenOverlapping() {
    $set = new Set(0);
    $childSet1 = new Set(1);
    $child1 = new ChildSetWrapper($childSet1, new \DateTime('2018-04-05'), new \DateTime('2018-04-13'));
    $childSet2 = new Set(2);
    $child2 = new ChildSetWrapper($childSet2, new \DateTime('2018-04-12'), new \DateTime('2018-04-14'));
    $sg = new SchemaGenerator($set, array($child2, $child1));

    $result = $sg->createSetValidityOrder(new \DateTime('2018-04-01'));

    $expected = array(
      array(
        'set' => $set,
        'start' => new \DateTime('2018-04-01'),
        'end' => new \DateTime('2018-04-04'),
      ),
      array(
        'set' => $childSet1,
        'start' => new \DateTime('2018-04-05'),
        'end' => new \DateTime('2018-04-13'),
      ),
      array(
        'set' => $childSet2,
        'start' => new \DateTime('2018-04-12'),
        'end' => new \DateTime('2018-04-14'),
      ),
      array(
        'set' => $set,
        'start' => new \DateTime('2018-04-15'),
        'end' => new \DateTime('2019-03-31'),
      ),
    );

    $this->assertTrue(is_array($result));
    $this->assertEquals($expected, $result);
  }

  /**
   * Tests `SchemaGenerator::createSetValidityOrder`
   * with one child set without dateStart
   */
  public function testCreateSetValidityOrderOneChildWithoutDateStart() {
    $set = new Set(0);
    $childSet = new Set(1);
    $child = new ChildSetWrapper($childSet, null, new \DateTime('2018-04-13'));
    $sg = new SchemaGenerator($set, array($child));

    $result = $sg->createSetValidityOrder(new \DateTime('2018-04-01'));

    $expected = array(
      array(
        'set' => $childSet,
        'start' => new \DateTime('2018-04-01'),
        'end' => new \DateTime('2018-04-13'),
      ),
      array(
        'set' => $set,
        'start' => new \DateTime('2018-04-14'),
        'end' => new \DateTime('2019-03-31'),
      ),
    );

    $this->assertTrue(is_array($result));
    $this->assertEquals($expected, $result);
  }

  /**
   * Tests `SchemaGenerator::createSetValidityOrder`
   * with one child set without dateEnd
   */
  public function testCreateSetValidityOrderOneChildWithoutDateEnd() {
    $set = new Set(0);
    $childSet = new Set(1);
    $child = new ChildSetWrapper($childSet, new \DateTime('2018-04-13'), null);
    $sg = new SchemaGenerator($set, array($child));

    $result = $sg->createSetValidityOrder(new \DateTime('2018-04-01'));

    $expected = array(
      array(
        'set' => $set,
        'start' => new \DateTime('2018-04-01'),
        'end' => new \DateTime('2018-04-12'),
      ),
      array(
        'set' => $childSet,
        'start' => new \DateTime('2018-04-13'),
        'end' => new \DateTime('2019-03-31'),
      ),
    );

    $this->assertTrue(is_array($result));
    $this->assertEquals($expected, $result);
  }

  /**
   * Tests `SchemaGenerator::createSetValidityOrder`
   * with one child set without any date
   */
  public function testCreateSetValidityOrderOneChildWithoutAnyDate() {
    $set = new Set(0);
    $childSet = new Set(1);
    $child = new ChildSetWrapper($childSet, null, null);
    $sg = new SchemaGenerator($set, array($child));

    $result = $sg->createSetValidityOrder(new \DateTime('2018-04-01'));

    $expected = array(
      array(
        'set' => $childSet,
        'start' => new \DateTime('2018-04-01'),
        'end' => new \DateTime('2019-03-31'),
      ),
    );

    $this->assertTrue(is_array($result));
    $this->assertEquals($expected, $result);
  }
}
