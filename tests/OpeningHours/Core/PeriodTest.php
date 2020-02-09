<?php

namespace OpeningHours\Core;

use OpeningHours\Test\OpeningHoursTestCase;

class PeriodTest extends OpeningHoursTestCase {
  public function test__construct() {
    $p = new Period(new \DateTime('2020-02-10 12:00:00'), new \DateTime('2020-02-10 18:00:00'), 1);
    $this->assertEquals(new \DateTime('2020-02-10 12:00:00'), $p->getStart());
    $this->assertEquals(new \DateTime('2020-02-10 18:00:00'), $p->getEnd());
    $this->assertEquals(1, $p->getWeekday());
  }
}
