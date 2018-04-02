<?php

namespace OpeningHours\Test\Module\Schema;
use OpeningHours\Entity\Set;
use OpeningHours\Module\Schema\ValidityPeriod;
use OpeningHours\Module\Schema\ValiditySequence;
use OpeningHours\Test\OpeningHoursTestCase;

/**
 * Class ValiditySequenceTest
 *
 * @author  Jannik Portz <hello@jannikportz.de>
 * @package OpeningHours\Test\Module\Schema
 */
class ValiditySequenceTest extends OpeningHoursTestCase {

  /**
   * - `__construct` sets the `$period` attribute properly
   * - `getPeriods` returns the value of the `$periods` attribute
   */
  public function test__construct() {
    $set = new Set(0);
    $validityPeriods = array(
      new ValidityPeriod($set, new \DateTime('2018-04-01'), new \DateTime('2018-04-30')),
      new ValidityPeriod($set, new \DateTime('2018-05-01'), new \DateTime('2018-05-18')),
    );

    $vs = new ValiditySequence($validityPeriods);

    $this->assertEquals($validityPeriods, $vs->getPeriods());
  }

  /**
   * - `restrictToDateRange` keeps `ValidityPeriods` that are fully inside the date range
   */
  public function testRestrictToDateRangeOneFullyInside() {
    $set = new Set(0);
    $vs = new ValiditySequence(array(
      new ValidityPeriod($set, new \DateTime('2018-04-01'), new \DateTime('2018-04-30')),
    ));

    $vs->restrictToDateRange(new \DateTime('2018-04-01'), new \DateTime('2018-04-30'));

    $expected = array(
      new ValidityPeriod($set, new \DateTime('2018-04-01'), new \DateTime('2018-04-30')),
    );

    $this->assertEquals($expected, $vs->getPeriods());
  }

  /**
   * - `restrictToDateRange` restricts elements that exceed the data range in either direction
   */
  public function testRestrictToDateRangeOneExceeds() {
    $set = new Set(0);
    $vs = new ValiditySequence(array(
      new ValidityPeriod($set, new \DateTime('2018-03-31'), new \DateTime('2018-05-01')),
    ));

    $vs->restrictToDateRange(new \DateTime('2018-04-01'), new \DateTime('2018-04-30'));

    $expected = array(
      new ValidityPeriod($set, new \DateTime('2018-04-01'), new \DateTime('2018-04-30')),
    );

    $this->assertEquals($expected, $vs->getPeriods());
  }

  /**
   * - `restrictToDateRange` removes `ValidityPeriods` that are fully outside the date range
   *   and updates the array indices of `$periods`
   */
  public function testRestrictToDateRangeOutside() {
    $set = new Set(0);
    $vs = new ValiditySequence(array(
      new ValidityPeriod($set, new \DateTime('2018-03-30'), new \DateTime('2018-03-30')),
      new ValidityPeriod($set, new \DateTime('2018-05-01'), new \DateTime('2018-05-01')),
      new ValidityPeriod($set, new \DateTime('2018-04-01'), new \DateTime('2018-04-30')),
    ));

    $vs->restrictToDateRange(new \DateTime('2018-04-01'), new \DateTime('2018-04-30'));

    $expected = array(
      new ValidityPeriod($set, new \DateTime('2018-04-01'), new \DateTime('2018-04-30')),
    );

    $this->assertEquals($expected, $vs->getPeriods());
  }
}