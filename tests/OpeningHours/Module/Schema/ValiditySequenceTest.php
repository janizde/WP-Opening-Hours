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
      new ValidityPeriod($set, new \DateTime('2018-05-01'), new \DateTime('2018-05-18'))
    );

    $vs = new ValiditySequence($validityPeriods);

    $this->assertEquals($validityPeriods, $vs->getPeriods());
  }

  /**
   * - `restrictedToDateRange` keeps `ValidityPeriods` that are fully inside the date range
   */
  public function testRestrictToDateRange_OneFullyInside() {
    $set = new Set(0);
    $vs = new ValiditySequence(array(
      new ValidityPeriod($set, new \DateTime('2018-04-01'), new \DateTime('2018-04-30'))
    ));

    $restricted = $vs->restrictedToDateRange(new \DateTime('2018-04-01'), new \DateTime('2018-04-30'));

    $expected = array(new ValidityPeriod($set, new \DateTime('2018-04-01'), new \DateTime('2018-04-30')));

    $this->assertEquals($expected, $restricted->getPeriods());
  }

  /**
   * - `restrictedToDateRange` restricts elements that exceed the data range in either direction
   */
  public function testRestrictedToDateRange_OneExceeds() {
    $set = new Set(0);
    $vs = new ValiditySequence(array(
      new ValidityPeriod($set, new \DateTime('2018-03-31'), new \DateTime('2018-05-01'))
    ));

    $restricted = $vs->restrictedToDateRange(new \DateTime('2018-04-01'), new \DateTime('2018-04-30'));

    $expected = array(new ValidityPeriod($set, new \DateTime('2018-04-01'), new \DateTime('2018-04-30')));

    $this->assertEquals($expected, $restricted->getPeriods());
  }

  /**
   * - `restrictedToDateRange` removes `ValidityPeriods` that are fully outside the date range
   *   and re-indexes the `$periods` array
   */
  public function testRestrictToDateRange_Outside() {
    $set = new Set(0);
    $vs = new ValiditySequence(array(
      new ValidityPeriod($set, new \DateTime('2018-03-30'), new \DateTime('2018-03-30')),
      new ValidityPeriod($set, new \DateTime('2018-05-01'), new \DateTime('2018-05-01')),
      new ValidityPeriod($set, new \DateTime('2018-04-01'), new \DateTime('2018-04-30'))
    ));

    $restricted = $vs->restrictedToDateRange(new \DateTime('2018-04-01'), new \DateTime('2018-04-30'));

    $expected = array(new ValidityPeriod($set, new \DateTime('2018-04-01'), new \DateTime('2018-04-30')));

    $this->assertEquals($expected, $restricted->getPeriods());
  }

  /**
   * - `restrictedToDateRange` does not remove `ValidityPeriod`s in the past when `$min` is `null`
   *   and re-indexes the `$periods` array
   */
  public function testRestrictToDateRange_OutsideMinInfinity() {
    $set = new Set(0);
    $vs = new ValiditySequence(array(
      new ValidityPeriod($set, new \DateTime('2018-03-20'), new \DateTime('2018-03-28')),
      new ValidityPeriod($set, new \DateTime('2018-03-29'), new \DateTime('2018-04-02')),
      new ValidityPeriod($set, new \DateTime('2018-04-01'), new \DateTime('2018-04-30'))
    ));

    $restricted = $vs->restrictedToDateRange(-INF, new \DateTime('2018-04-29'));

    $expected = array(
      new ValidityPeriod($set, new \DateTime('2018-03-20'), new \DateTime('2018-03-28')),
      new ValidityPeriod($set, new \DateTime('2018-03-29'), new \DateTime('2018-04-02')),
      new ValidityPeriod($set, new \DateTime('2018-04-01'), new \DateTime('2018-04-29'))
    );

    $this->assertEquals($expected, $restricted->getPeriods());
  }

  /**
   * - `restrictedToDateRange` does not remove `ValidityPeriod`s in the past when `$max` is `null`
   *   and re-indexes the `$periods` array
   */
  public function testRestrictToDateRange_OutsideMaxInfinity() {
    $set = new Set(0);
    $vs = new ValiditySequence(array(
      new ValidityPeriod($set, new \DateTime('2018-03-20'), new \DateTime('2018-03-28')),
      new ValidityPeriod($set, new \DateTime('2018-03-29'), new \DateTime('2018-04-02')),
      new ValidityPeriod($set, new \DateTime('2018-04-01'), new \DateTime('2018-04-30'))
    ));

    $restricted = $vs->restrictedToDateRange(new \DateTime('2018-03-22'), INF);

    $expected = array(
      new ValidityPeriod($set, new \DateTime('2018-03-22'), new \DateTime('2018-03-28')),
      new ValidityPeriod($set, new \DateTime('2018-03-29'), new \DateTime('2018-04-02')),
      new ValidityPeriod($set, new \DateTime('2018-04-01'), new \DateTime('2018-04-30'))
    );

    $this->assertEquals($expected, $restricted->getPeriods());
  }

  /**
   * - `restrictedToDateRange` keeps `null` as periods' end when `$max` is `null`
   */
  public function testRestrictToDateRange_OutsideMaxInfinityeriodInfinity() {
    $set = new Set(0);
    $vs = new ValiditySequence(array(new ValidityPeriod($set, new \DateTime('2018-04-01'), INF)));

    $restricted = $vs->restrictedToDateRange(new \DateTime('2018-04-02'), INF);

    $expected = array(new ValidityPeriod($set, new \DateTime('2018-04-02'), INF));

    $this->assertEquals($expected, $restricted->getPeriods());
  }

  /**
   * - `restrictedToDateRange` sets an end date to a period with with a `null` end when `$max` is set
   */
  public function testRestrictToDateRange_OutsideMaxValuePeriodInfinity() {
    $set = new Set(0);
    $vs = new ValiditySequence(array(new ValidityPeriod($set, new \DateTime('2018-04-01'), INF)));

    $restricted = $vs->restrictedToDateRange(new \DateTime('2018-04-02'), new \DateTime('2018-05-01'));

    $expected = array(new ValidityPeriod($set, new \DateTime('2018-04-02'), new \DateTime('2018-05-01')));

    $this->assertEquals($expected, $restricted->getPeriods());
  }

  /**
   * - `restrictedToDateRange` keeps `null` as periods' end when `$min` is `null`
   */
  public function testRestrictToDateRange_OutsideMinInfinityPeriodInfinity() {
    $set = new Set(0);
    $vs = new ValiditySequence(array(new ValidityPeriod($set, -INF, new \DateTime('2018-04-30'))));

    $restricted = $vs->restrictedToDateRange(-INF, new \DateTime('2018-04-20'));

    $expected = array(new ValidityPeriod($set, -INF, new \DateTime('2018-04-20')));

    $this->assertEquals($expected, $restricted->getPeriods());
  }

  /**
   * - `restrictedToDateRange` keeps `null` as periods' start and end when `$min` and `$max` are `null`
   *   and date range is from `null` to `null`
   */
  public function testRestrictToDateRange_AllRangesInfinity() {
    $set = new Set(0);
    $vs = new ValiditySequence(array(new ValidityPeriod($set, -INF, INF)));

    $restricted = $vs->restrictedToDateRange(-INF, INF);

    $expected = array(new ValidityPeriod($set, -INF, INF));

    $this->assertEquals($expected, $restricted->getPeriods());
  }

  /**
   * - `coveredWith` returns a sequence equal to the foreground sequence if the background sequence is empty
   */
  public function testCoveredWith_EmptyBackgroundSequence() {
    $set = new Set(0);
    $fg = new ValiditySequence(array(
      new ValidityPeriod($set, new \DateTime('2018-03-30'), new \DateTime('2018-03-30')),
      new ValidityPeriod($set, new \DateTime('2018-05-01'), new \DateTime('2018-05-01')),
      new ValidityPeriod($set, new \DateTime('2018-04-01'), new \DateTime('2018-04-30'))
    ));

    $bg = new ValiditySequence(array());

    $result = $bg->coveredWith($fg);
    $this->assertEquals($fg, $result);
  }

  /**
   * - `coveredWith` returns a sequence equal to the background sequence if the foreground sequence is empty
   */
  public function testCoveredWith_EmptyForegroundSequence() {
    $set = new Set(0);
    $fg = new ValiditySequence(array());
    $bg = new ValiditySequence(array(
      new ValidityPeriod($set, new \DateTime('2018-03-30'), new \DateTime('2018-03-30')),
      new ValidityPeriod($set, new \DateTime('2018-05-01'), new \DateTime('2018-05-01')),
      new ValidityPeriod($set, new \DateTime('2018-04-01'), new \DateTime('2018-04-30'))
    ));

    $result = $bg->coveredWith($fg);
    $this->assertEquals($bg, $result);
  }

  /**
   * - `coveredWith` takes background periods at the start and end that do not intersect with the background
   * - `coveredWith` fills the gaps between background range and foreground range with partial background periods
   * - `coveredWith` takes all periods of the foreground sequence as is
   * - `coveredWith` fills gaps between foreground periods with full or partial periods of the background sequence
   */
  public function testCoveredWith_Total() {
    $sets = array(
      new Set(0),
      new Set(1),
      new Set(2),
      new Set(3),
      new Set(4),
      new Set(5),
      new Set(6),
      new Set(7),
      new Set(8)
    );

    $fg = new ValiditySequence(array(
      new ValidityPeriod($sets[0], new \DateTime('2018-04-01'), new \DateTime('2018-04-13')),
      new ValidityPeriod($sets[1], new \DateTime('2018-04-15'), new \DateTime('2018-04-15')),
      new ValidityPeriod($sets[2], new \DateTime('2018-04-19'), new \DateTime('2018-04-20'))
    ));

    $bg = new ValiditySequence(array(
      new ValidityPeriod($sets[3], new \DateTime('2018-03-28'), new \DateTime('2018-03-28')),
      new ValidityPeriod($sets[4], new \DateTime('2018-03-29'), new \DateTime('2018-04-02')),
      new ValidityPeriod($sets[5], new \DateTime('2018-04-14'), new \DateTime('2018-04-14')),
      new ValidityPeriod($sets[6], new \DateTime('2018-04-15'), new \DateTime('2018-04-16')),
      new ValidityPeriod($sets[7], new \DateTime('2018-04-20'), new \DateTime('2018-04-22')),
      new ValidityPeriod($sets[8], new \DateTime('2018-04-23'), new \DateTime('2018-04-25'))
    ));

    $expected = new ValiditySequence(array(
      new ValidityPeriod($sets[3], new \DateTime('2018-03-28'), new \DateTime('2018-03-28')),
      new ValidityPeriod($sets[4], new \DateTime('2018-03-29'), new \DateTime('2018-03-31')),
      new ValidityPeriod($sets[0], new \DateTime('2018-04-01'), new \DateTime('2018-04-13')),
      new ValidityPeriod($sets[5], new \DateTime('2018-04-14'), new \DateTime('2018-04-14')),
      new ValidityPeriod($sets[1], new \DateTime('2018-04-15'), new \DateTime('2018-04-15')),
      new ValidityPeriod($sets[6], new \DateTime('2018-04-16'), new \DateTime('2018-04-16')),
      new ValidityPeriod($sets[2], new \DateTime('2018-04-19'), new \DateTime('2018-04-20')),
      new ValidityPeriod($sets[7], new \DateTime('2018-04-21'), new \DateTime('2018-04-22')),
      new ValidityPeriod($sets[8], new \DateTime('2018-04-23'), new \DateTime('2018-04-25'))
    ));

    $result = $bg->coveredWith($fg);
    $this->assertEquals($expected, $result);
  }
}
