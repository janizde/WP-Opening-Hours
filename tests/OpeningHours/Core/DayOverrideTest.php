<?php

namespace OpeningHours\Core;

use OpeningHours\Test\OpeningHoursTestCase;

class DayOverrideTest extends OpeningHoursTestCase {
  public function test__getKind() {
    $do = new DayOverride('Foo Override', new \DateTime('2020-02-10'), []);
    $this->assertEquals(DayOverride::SPEC_KIND, $do->getKind());
  }

  public function test__getName() {
    $do = new DayOverride('Foo Override', new \DateTime('2020-02-10'), []);
    $this->assertEquals('Foo Override', $do->getName());
  }

  public function test__getPeriods() {
    $periods = [
      new Period(new \DateTime('2020-02-10 12:00'), new \DateTime('2020-02-10 18:00')),
      new Period(new \DateTime('2020-02-10 22:00'), new \DateTime('2020-02-11 05:00'))
    ];

    $do = new DayOverride('Foo Override', new \DateTime('2020-02-10'), $periods);
    $this->assertEquals($periods, $do->getPeriods());
  }

  public function test__getValidityPeriod__insideDay() {
    $periods = [new Period(new \DateTime('2020-02-10 12:00'), new \DateTime('2020-02-10 18:00'))];

    $do = new DayOverride('Foo Override', new \DateTime('2020-02-10'), $periods);
    $vp = $do->getValidityPeriod();
    $expected = new ValidityPeriod(new \DateTime('2020-02-10 00:00:00'), new \DateTime('2020-02-11 00:00:00'), $do);
    $this->assertEquals($expected, $vp);
  }

  public function test__getValidityPeriod__untilMidnight() {
    $periods = [new Period(new \DateTime('2020-02-10 12:00'), new \DateTime('2020-02-11 00:00'))];

    $do = new DayOverride('Foo Override', new \DateTime('2020-02-10'), $periods);
    $vp = $do->getValidityPeriod();
    $expected = new ValidityPeriod(new \DateTime('2020-02-10 00:00:00'), new \DateTime('2020-02-11 00:00:00'), $do);
    $this->assertEquals($expected, $vp);
  }

  public function test__getValidityPeriod__pastMidnight() {
    $periods = [
      new Period(new \DateTime('2020-02-10 12:00'), new \DateTime('2020-02-10 18:00')),
      new Period(new \DateTime('2020-02-10 22:00'), new \DateTime('2020-02-11 05:00'))
    ];

    $do = new DayOverride('Foo Override', new \DateTime('2020-02-10'), $periods);
    $vp = $do->getValidityPeriod();
    $expected = new ValidityPeriod(new \DateTime('2020-02-10 00:00:00'), new \DateTime('2020-02-11 05:00:00'), $do);
    $this->assertEquals($expected, $vp);
  }

  public function test__toSerializableArray() {
    $periods = [new Period(new \DateTime('2020-02-10 12:00'), new \DateTime('2020-02-11 00:00'))];
    $do = new DayOverride('Foo Override', new \DateTime('2020-02-10'), $periods);
    $expected = [
      'kind' => DayOverride::SPEC_KIND,
      'name' => 'Foo Override',
      'date' => '2020-02-10T00:00:00+00:00',
      'periods' => [
        [
          'start' => '2020-02-10T12:00:00+00:00',
          'end' => '2020-02-11T00:00:00+00:00'
        ]
      ]
    ];

    $this->assertEquals($expected, $do->toSerializableArray());
  }

  public function test__fromSerializableArray() {
    $serialized = [
      'kind' => DayOverride::SPEC_KIND,
      'name' => 'Foo Override',
      'date' => '2020-02-10T00:00:00+00:00',
      'periods' => [
        [
          'start' => '2020-02-10T12:00:00+00:00',
          'end' => '2020-02-11T00:00:00+00:00'
        ]
      ]
    ];

    $periods = [new Period(new \DateTime('2020-02-10 12:00'), new \DateTime('2020-02-11 00:00'))];
    $expected = new DayOverride('Foo Override', new \DateTime('2020-02-10'), $periods);

    $this->assertEquals($expected, DayOverride::fromSerializableArray($serialized));
  }
}
