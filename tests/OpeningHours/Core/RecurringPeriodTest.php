<?php

namespace OpeningHours\Core;

use OpeningHours\Test\OpeningHoursTestCase;

class RecurringPeriodTest extends OpeningHoursTestCase {
  public function test__getPeriodOn_SameDay() {
    // Mon 12:00 - 18:00
    $rp = new RecurringPeriod('12:00', 21600, 1);
    $p = $rp->getPeriodOn(new \DateTime('2020-02-10'));
    $this->assertEquals(new \DateTime('2020-02-10 12:00:00'), $p->getStart());
    $this->assertEquals(new \DateTime('2020-02-10 18:00:00'), $p->getEnd());
    $this->assertEquals(1, $p->getWeekday());
  }

  public function test_getPeriodOn_EndsMidnight() {
    // Mon 12:00 – Tue 03:00
    $rp = new RecurringPeriod('12:00', 12 * 60 * 60, 1);
    $p = $rp->getPeriodOn(new \DateTime('2020-02-10'));
    $this->assertEquals(new \DateTime('2020-02-10 12:00:00'), $p->getStart());
    $this->assertEquals(new \DateTime('2020-02-11 00:00:00'), $p->getEnd());
  }

  public function test_getPeriodOn_PastMidnight() {
    // Mon 12:00 – Tue 03:00
    $rp = new RecurringPeriod('12:00', 15 * 60 * 60, 1);
    $p = $rp->getPeriodOn(new \DateTime('2020-02-10'));
    $this->assertEquals(new \DateTime('2020-02-10 12:00:00'), $p->getStart());
    $this->assertEquals(new \DateTime('2020-02-11 03:00:00'), $p->getEnd());
  }

  public function test_getPeriodOn_MultipleDays() {
    // Mon 22:00 – Thu 08:00
    $rp = new RecurringPeriod('22:00', 34 * 60 * 60, 1);
    $p = $rp->getPeriodOn(new \DateTime('2020-02-10'));
    $this->assertEquals(new \DateTime('2020-02-10 22:00:00'), $p->getStart());
    $this->assertEquals(new \DateTime('2020-02-12 08:00:00'), $p->getEnd());
  }

  /**
   * @expectedException \InvalidArgumentException
   */
  public function test_getPeriodOn_throwsWeekday() {
    // Mon 22:00 – Thu 08:00
    $rp = new RecurringPeriod('22:00', 15 * 60 * 60, 1);
    $rp->getPeriodOn(new \DateTime('2020-02-25'));
  }

  public function test_getWeekday() {
    $rp = new RecurringPeriod('12:00', 21600, 2);
    $this->assertEquals(2, $rp->getWeekday());
  }
}
