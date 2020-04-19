<?php

namespace OpeningHours\Core;

use OpeningHours\Test\OpeningHoursTestCase;

class HolidayTest extends OpeningHoursTestCase {
  public function test_getName() {
    $holiday = new Holiday('Foo Holiday', new \DateTime('2020-02-10'), new \DateTime('2020-02-17'));
    $this->assertEquals('Foo Holiday', $holiday->getName());
  }

  public function test_getKind() {
    $holiday = new Holiday('Foo Holiday', new \DateTime('2020-02-10'), new \DateTime('2020-02-17'));
    $this->assertEquals(Holiday::SPEC_KIND, $holiday->getKind());
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

  public function test__toSerializableArray() {
    $holiday = new Holiday('Foo Holiday', new \DateTime('2020-02-10'), new \DateTime('2020-02-17'));
    $expected = [
      'kind' => Holiday::SPEC_KIND,
      'name' => 'Foo Holiday',
      'start' => '2020-02-10T00:00:00+00:00',
      'end' => '2020-02-17T00:00:00+00:00'
    ];

    $this->assertEquals($expected, $holiday->toSerializableArray());
  }

  public function test__fromSerializableArray() {
    $serialized = [
      'kind' => Holiday::SPEC_KIND,
      'name' => 'Foo Holiday',
      'start' => '2020-02-10T00:00:00+00:00',
      'end' => '2020-02-17T00:00:00+00:00'
    ];

    $expected = new Holiday('Foo Holiday', new \DateTime('2020-02-10'), new \DateTime('2020-02-17'));
    $this->assertEquals($expected, Holiday::fromSerializableArray($serialized));
  }
}
