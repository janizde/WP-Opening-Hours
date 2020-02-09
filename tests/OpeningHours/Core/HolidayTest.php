<?php

namespace OpeningHours\Core;

use OpeningHours\Test\OpeningHoursTestCase;

class HolidayTest extends OpeningHoursTestCase {
  public function test_getName() {
    $holiday = new Holiday('Foo Holiday', new \DateTime('2020-02-10'), new \DateTime('2020-02-17'));
    $this->assertEquals('Foo Holiday', $holiday);
  }

  public function test_getKind() {
    $holiday = new Holiday('Foo Holiday', new \DateTime('2020-02-10'), new \DateTime('2020-02-17'));
    $this->assertEquals(Holiday::ENTRY_KIND, $holiday->getKind());
  }

  public function test__getChildren() {
    $holiday = new Holiday('Foo Holiday', new \DateTime('2020-02-10'), new \DateTime('2020-02-17'));
    $this->assertEquals([], $holiday->getChildren());
  }

  public function test__getValidityPeriod() {
    $holiday = new Holiday('Foo Holiday', new \DateTime('2020-02-10'), new \DateTime('2020-02-17'));
    $expected = new ValidityPeriod(
      new \DateTime('2020-02-10 00:00:00'),
      new \DateTime('2020-02-17 00:00:00'),
      $holiday
    );
    $this->assertEquals($expected, $holiday->getValidityPeriod());
  }
}
