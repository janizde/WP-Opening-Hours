<?php

namespace OpeningHours\Core;

use OpeningHours\Test\OpeningHoursTestCase;

class RecurringPeriodsTest extends OpeningHoursTestCase {
  public function test__getKind() {
    $rp = new RecurringPeriods(-INF, INF, [], []);
    $this->assertEquals(RecurringPeriods::SPEC_KIND, $rp->getKind());
  }

  public function getChildren() {
    $rp1 = new RecurringPeriods(new \DateTime('2020-02-10'), new \DateTime('2020-08-10'), [], []);
    $rp = new RecurringPeriods(-INF, INF, [], [$rp1]);
    $this->assertEquals([$rp1], $rp->getChildren());
  }
}
