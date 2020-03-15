<?php

namespace OpeningHours\Core;

use OpeningHours\Test\OpeningHoursTestCase;

class RecurringPeriodsTest extends OpeningHoursTestCase {
  public function test_getKind() {
    $rp = new RecurringPeriods(-INF, INF, [], []);
    $this->assertEquals(RecurringPeriods::SPEC_KIND, $rp->getKind());
  }

  public function test_getChildren() {
    $rp1 = new RecurringPeriods(new \DateTime('2020-02-10'), new \DateTime('2020-08-10'), [], []);
    $rp = new RecurringPeriods(-INF, INF, [], [$rp1]);
    $this->assertEquals([$rp1], $rp->getChildren());
  }

  public function test_getValidityPeriod() {
    $rp = new RecurringPeriods(-INF, new \DateTime('2020-02-25'), [], []);
    $vp = $rp->getValidityPeriod();
    $expected = new ValidityPeriod(-INF, new \DateTime('2020-02-25'), $rp);
    $this->assertEquals($expected, $vp);
  }

  /**
   * - `getValidityPeriod` will enhance the end date of the `RecurringPeriods` when a period starts before the end
   *  of the `RecurringPeriods` and ends after it
   */
  public function test_getValidityPeriod_lastPeriodExceeds() {
    $rp = new RecurringPeriods(
      new \DateTime('2020-03-02'),
      new \DateTime('2020-10-01'),
      [
        new RecurringPeriod('12:00', 6 * 60 * 60, 3),
        new RecurringPeriod('22:00', 6 * 60 * 60, 3),
        new RecurringPeriod('06:00', 6 * 60 * 60, 4)
      ],
      []
    );

    $vp = $rp->getValidityPeriod();
    $expected = new ValidityPeriod(new \DateTime('2020-03-02'), new \DateTime('2020-10-01T04:00:00Z'), $rp);
    $this->assertEquals($expected, $vp);
  }
}
