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

  public function test__toSerializableArray() {
    $p = new Period(new \DateTime('2020-02-10 12:00:00'), new \DateTime('2020-02-10 18:00:00'), 1);
    $expected = [
      'start' => '2020-02-10T12:00:00+00:00',
      'end' => '2020-02-10T18:00:00+00:00'
    ];

    $this->assertEquals($expected, $p->toSerializableArray());
  }

  public function test__fromSerializableArray() {
    $serialized = [
      'start' => '2020-02-10T12:00:00+00:00',
      'end' => '2020-02-10T18:00:00+00:00'
    ];

    $expected = new Period(new \DateTime('2020-02-10 12:00:00'), new \DateTime('2020-02-10 18:00:00'), 1);

    $this->assertEquals($expected, Period::fromSerializableArray($serialized));
  }
}
