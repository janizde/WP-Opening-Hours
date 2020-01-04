<?php

namespace OpeningHours\Test\Module\CustomPostType\MetaBox;

use OpeningHours\Entity\Period;
use OpeningHours\Module\CustomPostType\MetaBox\OpeningHours;
use OpeningHours\Test\OpeningHoursTestCase;
use OpeningHours\Util\Weekday;

class OpeningHoursTest extends OpeningHoursTestCase {
  public function testGroupPeriodsWithDummyNoPeriods() {
    $data = OpeningHours::getInstance()->groupPeriodsWithDummy(array());

    $this->assertEquals(7, count($data));
    foreach ($data as $day) {
      $this->assertEquals(1, count($day['periods']));
      /** @var Period $period */
      $period = $day['periods'][0];
      /** @var Weekday $weekday */
      $weekday = $day['day'];
      $this->assertEquals($weekday->getIndex(), $period->getWeekday());
      $this->assertEquals('00:00', $period->getTimeStart()->format('H:i'));
      $this->assertEquals('00:00', $period->getTimeEnd()->format('H:i'));
    }
  }

  public function testGroupPeriodsWithDummyHasPeriods() {
    $periods = array(new Period(1, '13:00', '17:00'), new Period(1, '18:00', '21:00'), new Period(2, '13:00', '17:00'));

    $data = OpeningHours::getInstance()->groupPeriodsWithDummy($periods);
    $this->assertEquals(7, count($data));

    $this->assertEquals(1, count($data[0]['periods']));
    $this->assertEquals(2, count($data[1]['periods']));
    $this->assertEquals(1, count($data[2]['periods']));
    $this->assertEquals(1, count($data[3]['periods']));
    $this->assertEquals(1, count($data[4]['periods']));
    $this->assertEquals(1, count($data[5]['periods']));
    $this->assertEquals(1, count($data[6]['periods']));
  }
}
