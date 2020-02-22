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
}
