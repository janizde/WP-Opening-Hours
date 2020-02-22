<?php

namespace OpeningHours\Core;

use OpeningHours\Test\OpeningHoursTestCase;

function dummyEntry() {
  return new RecurringPeriods(-INF, INF, [], []);
}

class ValiditySequenceTest extends OpeningHoursTestCase {
  /**
   * - `__construct` sets the `$period` attribute properly
   * - `getPeriods` returns the value of the `$periods` attribute
   */
  public function test__construct() {
    $entry = new RecurringPeriods(-INF, INF, [], []);
    $validityPeriods = array(
      new ValidityPeriod(new \DateTime('2018-04-01'), new \DateTime('2018-04-30'), $entry),
      new ValidityPeriod(new \DateTime('2018-05-01'), new \DateTime('2018-05-18'), $entry)
    );

    $vs = new ValiditySequence($validityPeriods);
    $this->assertEquals($validityPeriods, $vs->getPeriods());
  }

  /**
   * - `restrictedToInterval` keeps `ValidityPeriods` that are fully inside the date range
   */
  public function test__restrictedToInterval_OneFullyInside() {
    $vs = new ValiditySequence([
      new ValidityPeriod(new \DateTime('2018-04-01'), new \DateTime('2018-04-30'), dummyEntry())
    ]);

    $restricted = $vs->restrictedToInterval(new \DateTime('2018-04-01'), new \DateTime('2018-04-30'));
    $expected = array(new ValidityPeriod(new \DateTime('2018-04-01'), new \DateTime('2018-04-30'), dummyEntry()));
    $this->assertEquals($expected, $restricted->getPeriods());
  }

  /**
   * - `restrictedToInterval` restricts elements that exceed the data range in either direction
   */
  public function testRestrictedToDateRange_OneExceeds() {
    $vs = new ValiditySequence([
      new ValidityPeriod(new \DateTime('2018-03-31'), new \DateTime('2018-05-01'), dummyEntry())
    ]);

    $restricted = $vs->restrictedToInterval(new \DateTime('2018-04-01'), new \DateTime('2018-04-30'));
    $expected = array(new ValidityPeriod(new \DateTime('2018-04-01'), new \DateTime('2018-04-30'), dummyEntry()));
    $this->assertEquals($expected, $restricted->getPeriods());
  }

  /**
   * - `restrictedToInterval` removes `ValidityPeriods` that are fully outside the date range
   *   and re-indexes the `$periods` array
   */
  public function test__restrictedToInterval_Outside() {
    $vs = new ValiditySequence([
      new ValidityPeriod(new \DateTime('2018-03-30'), new \DateTime('2018-03-30'), dummyEntry()),
      new ValidityPeriod(new \DateTime('2018-05-01'), new \DateTime('2018-05-01'), dummyEntry()),
      new ValidityPeriod(new \DateTime('2018-04-01'), new \DateTime('2018-04-30'), dummyEntry())
    ]);

    $restricted = $vs->restrictedToInterval(new \DateTime('2018-04-01'), new \DateTime('2018-04-30'));
    $expected = [new ValidityPeriod(new \DateTime('2018-04-01'), new \DateTime('2018-04-30'), dummyEntry())];
    $this->assertEquals($expected, $restricted->getPeriods());
  }

  /**
   * - `restrictedToInterval` does not remove `ValidityPeriod`s in the past when `$min` is `-INF`
   *   and re-indexes the `$periods` array
   */
  public function test__restrictedToInterval_OutsideMinInfinity() {
    $vs = new ValiditySequence([
      new ValidityPeriod(new \DateTime('2018-03-20'), new \DateTime('2018-03-28'), dummyEntry()),
      new ValidityPeriod(new \DateTime('2018-03-29'), new \DateTime('2018-04-02'), dummyEntry()),
      new ValidityPeriod(new \DateTime('2018-04-01'), new \DateTime('2018-04-30'), dummyEntry())
    ]);

    $restricted = $vs->restrictedToInterval(-INF, new \DateTime('2018-04-29'));

    $expected = [
      new ValidityPeriod(new \DateTime('2018-03-20'), new \DateTime('2018-03-28'), dummyEntry()),
      new ValidityPeriod(new \DateTime('2018-03-29'), new \DateTime('2018-04-02'), dummyEntry()),
      new ValidityPeriod(new \DateTime('2018-04-01'), new \DateTime('2018-04-29'), dummyEntry())
    ];

    $this->assertEquals($expected, $restricted->getPeriods());
  }

  /**
   * - `restrictedToInterval` does not remove `ValidityPeriod`s in the past when `$max` is `INF`
   *   and re-indexes the `$periods` array
   */
  public function test__restrictedToInterval_OutsideMaxInfinity() {
    $vs = new ValiditySequence([
      new ValidityPeriod(new \DateTime('2018-03-20'), new \DateTime('2018-03-28'), dummyEntry()),
      new ValidityPeriod(new \DateTime('2018-03-29'), new \DateTime('2018-04-02'), dummyEntry()),
      new ValidityPeriod(new \DateTime('2018-04-01'), new \DateTime('2018-04-30'), dummyEntry())
    ]);

    $restricted = $vs->restrictedToInterval(new \DateTime('2018-03-22'), INF);

    $expected = [
      new ValidityPeriod(new \DateTime('2018-03-22'), new \DateTime('2018-03-28'), dummyEntry()),
      new ValidityPeriod(new \DateTime('2018-03-29'), new \DateTime('2018-04-02'), dummyEntry()),
      new ValidityPeriod(new \DateTime('2018-04-01'), new \DateTime('2018-04-30'), dummyEntry())
    ];

    $this->assertEquals($expected, $restricted->getPeriods());
  }

  /**
   * - `restrictedToInterval` keeps `INF` as periods' end when `$max` is `INF`
   */
  public function test__restrictedToInterval_OutsideMaxInfinityPeriodInfinity() {
    $vs = new ValiditySequence([new ValidityPeriod(new \DateTime('2018-04-01'), INF, dummyEntry())]);
    $restricted = $vs->restrictedToInterval(new \DateTime('2018-04-02'), INF);
    $expected = [new ValidityPeriod(new \DateTime('2018-04-02'), INF, dummyEntry())];
    $this->assertEquals($expected, $restricted->getPeriods());
  }

  /**
   * - `restrictedToInterval` sets an end date to a period with with a `INF` end when `$max` is set
   */
  public function test__restrictedToInterval_OutsideMaxValuePeriodInfinity() {
    $vs = new ValiditySequence([new ValidityPeriod(new \DateTime('2018-04-01'), INF, dummyEntry())]);
    $restricted = $vs->restrictedToInterval(new \DateTime('2018-04-02'), new \DateTime('2018-05-01'));
    $expected = [new ValidityPeriod(new \DateTime('2018-04-02'), new \DateTime('2018-05-01'), dummyEntry())];
    $this->assertEquals($expected, $restricted->getPeriods());
  }

  /**
   * - `restrictedToInterval` keeps `INF` as periods' end when `$min` is `INF`
   */
  public function test__restrictedToInterval_OutsideMinInfinityPeriodInfinity() {
    $vs = new ValiditySequence([new ValidityPeriod(-INF, new \DateTime('2018-04-30'), dummyEntry())]);
    $restricted = $vs->restrictedToInterval(-INF, new \DateTime('2018-04-20'));
    $expected = [new ValidityPeriod(-INF, new \DateTime('2018-04-20'), dummyEntry())];
    $this->assertEquals($expected, $restricted->getPeriods());
  }

  /**
   * - `restrictedToInterval` keeps `-INF` as periods' start and end when `$min` and `$max` are [-INF, INF]
   *   and date range is from `-INF` to `INF`
   */
  public function test__restrictedToInterval_AllRangesInfinity() {
    $vs = new ValiditySequence([new ValidityPeriod(-INF, INF, dummyEntry())]);
    $restricted = $vs->restrictedToInterval(-INF, INF);
    $expected = array(new ValidityPeriod(-INF, INF, dummyEntry()));
    $this->assertEquals($expected, $restricted->getPeriods());
  }

  public function test__getStart__emptySequence() {
    $seq = new ValiditySequence([]);
    $this->assertEquals(-INF, $seq->getStart());
  }

  public function test__getStart__periodInfinite() {
    $seq = new ValiditySequence([new ValidityPeriod(-INF, INF, dummyEntry())]);
    $this->assertEquals(-INF, $seq->getStart());
  }

  public function test__getStart__periodFinite() {
    $seq = new ValiditySequence([new ValidityPeriod(new \DateTime('2020-02-01'), INF, dummyEntry())]);
    $this->assertEquals(new \DateTime('2020-02-01'), $seq->getStart());
  }

  public function test__getEnd__emptySequence() {
    $seq = new ValiditySequence([]);
    $this->assertEquals(INF, $seq->getEnd());
  }

  public function test__getEnd__periodInfinite() {
    $seq = new ValiditySequence([new ValidityPeriod(-INF, INF, dummyEntry())]);
    $this->assertEquals(INF, $seq->getEnd());
  }

  public function test__getEnd__periodFinite() {
    $seq = new ValiditySequence([new ValidityPeriod(-INF, new \DateTime('2020-02-01'), dummyEntry())]);
    $this->assertEquals(new \DateTime('2020-02-01'), $seq->getEnd());
  }

  /**
   * Verifies that the highest value of a $periods end date is returned, also in case the last period in the arra
   * is not the one that ends last
   */
  public function test__getEnd__lastNotEnd() {
    $periods = [
      new ValidityPeriod(new \DateTime('2020-02-01'), INF, dummyEntry()),
      new ValidityPeriod(new \DateTime('2020-02-02'), new \DateTime('2020-02-29'), dummyEntry())
    ];

    $seq = new ValiditySequence($periods);

    $this->assertEquals(INF, $seq->getEnd());
  }
}
