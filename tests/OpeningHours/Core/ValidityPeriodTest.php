<?php

namespace OpeningHours\Core;

use OpeningHours\Test\OpeningHoursTestCase;

class ValidityPeriodTest extends OpeningHoursTestCase {
  public function test__construct__concrete() {
    $specEntry = new Holiday('Foo', new \DateTime('2020-03-01'), new \DateTime('2020-11-01'));
    $vp = new ValidityPeriod(new \DateTime('2020-02-01'), new \DateTime('2020-10-01'), $specEntry);
    $this->assertEquals(new \DateTime('2020-02-01'), $vp->getStart());
    $this->assertEquals(new \DateTime('2020-10-01'), $vp->getEnd());
    $this->assertEquals($specEntry, $vp->getEntry());
  }

  public function test__construct__infinite() {
    $specEntry = new Holiday('Foo', new \DateTime('2020-03-01'), new \DateTime('2020-11-01'));
    $vp = new ValidityPeriod(-INF, INF, $specEntry);
    $this->assertEquals(-INF, $vp->getStart());
    $this->assertEquals(INF, $vp->getEnd());
    $this->assertEquals($specEntry, $vp->getEntry());
  }
}
