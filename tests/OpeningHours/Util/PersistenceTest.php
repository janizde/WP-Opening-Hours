<?php

namespace OpeningHours\Test\Util;

use DateTime;
use OpeningHours\Entity\Holiday;
use OpeningHours\Entity\IrregularOpening;
use OpeningHours\Entity\Period;
use OpeningHours\Test\OpeningHoursTestCase;
use OpeningHours\Util\Dates;
use OpeningHours\Util\Persistence;

class PersistenceTest extends OpeningHoursTestCase {
  protected function createPersistence() {
    $post = $this->getMockBuilder('WP_Post')->getMock();
    $post->ID = 64;
    return new Persistence($post);
  }

  public function testSavePeriods() {
    $persistence = $this->createPersistence();

    $periods = array(new Period(1, '13:00', '17:00'), new Period(2, '16:30', '19:00'));

    $data = array(
      array(
        'weekday' => 1,
        'timeStart' => '13:00',
        'timeEnd' => '17:00'
      ),
      array(
        'weekday' => 2,
        'timeStart' => '16:30',
        'timeEnd' => '19:00'
      )
    );

    \WP_Mock::wpFunction('update_post_meta', array(
      'times' => '1',
      'args' => array(64, Persistence::PERIODS_META_KEY, $data)
    ));

    $persistence->savePeriods($periods);
  }

  public function testLoadPeriods() {
    $persistence = $this->createPersistence();

    $data = array(
      array(
        'weekday' => 1,
        'timeStart' => '13:00',
        'timeEnd' => '17:00'
      ),
      array(
        'weekday' => 2,
        'timeStart' => '16:30',
        'timeEnd' => '19:00'
      )
    );

    \WP_Mock::wpFunction('get_post_meta', array(
      'times' => 1,
      'args' => array(64, Persistence::PERIODS_META_KEY, true),
      'return' => $data
    ));

    $periods = $persistence->loadPeriods();
    $this->assertTrue(is_array($periods));
    $this->assertEquals(2, count($periods));

    $p1 = $periods[0];
    $p2 = $periods[1];

    $this->assertEquals(1, $p1->getWeekday());
    $this->assertEquals('13:00', $p1->getTimeStart()->format(Dates::STD_TIME_FORMAT));
    $this->assertEquals('17:00', $p1->getTimeEnd()->format(Dates::STD_TIME_FORMAT));

    $this->assertEquals(2, $p2->getWeekday());
    $this->assertEquals('16:30', $p2->getTimeStart()->format(Dates::STD_TIME_FORMAT));
    $this->assertEquals('19:00', $p2->getTimeEnd()->format(Dates::STD_TIME_FORMAT));
  }

  public function testSaveHolidays() {
    $persistence = $this->createPersistence();

    $holidays = array(
      new Holiday('Holiday1', new DateTime('2016-02-03'), new DateTime('2016-02-07')),
      new Holiday('Holiday2', new DateTime('2016-03-03'), new DateTime('2016-03-07'))
    );

    $data = array(
      array(
        'name' => 'Holiday1',
        'dateStart' => '2016-02-03',
        'dateEnd' => '2016-02-07'
      ),
      array(
        'name' => 'Holiday2',
        'dateStart' => '2016-03-03',
        'dateEnd' => '2016-03-07'
      )
    );

    \WP_Mock::wpFunction('update_post_meta', array(
      'times' => 1,
      'args' => array(64, Persistence::HOLIDAYS_META_KEY, $data)
    ));
    $persistence->saveHolidays($holidays);
  }

  public function testLoadHolidays() {
    $persistence = $this->createPersistence();

    $data = array(
      array(
        'name' => 'Holiday1',
        'dateStart' => '2016-02-03',
        'dateEnd' => '2016-02-07'
      ),
      array(
        'name' => 'Holiday2',
        'dateStart' => '2016-03-03',
        'dateEnd' => '2016-03-07'
      )
    );

    \WP_Mock::wpFunction('get_post_meta', array(
      'times' => 1,
      'args' => array(64, Persistence::HOLIDAYS_META_KEY, true),
      'return' => $data
    ));

    $holidays = $persistence->loadHolidays();
    $this->assertTrue(is_array($holidays));
    $this->assertEquals(2, count($holidays));
    $h1 = $holidays[0];
    $h2 = $holidays[1];

    $this->assertEquals('Holiday1', $h1->getName());
    $this->assertEquals(new DateTime('2016-02-03'), $h1->getStart());
    $this->assertEquals(new DateTime('2016-02-07 23:59:59', Dates::getTimezone()), $h1->getEnd());

    $this->assertEquals('Holiday2', $h2->getName());
    $this->assertEquals(new DateTime('2016-03-03'), $h2->getStart());
    $this->assertEquals(new DateTime('2016-03-07 23:59:59', Dates::getTimezone()), $h2->getEnd());
  }

  public function testSaveIrregularOpenings() {
    $persistence = $this->createPersistence();

    $ios = array(
      new IrregularOpening('IO1', '2016-02-03', '13:00', '17:00'),
      new IrregularOpening('IO2', '2016-03-03', '16:30', '19:00')
    );

    $data = array(
      array(
        'name' => 'IO1',
        'date' => '2016-02-03',
        'timeStart' => '13:00',
        'timeEnd' => '17:00'
      ),
      array(
        'name' => 'IO2',
        'date' => '2016-03-03',
        'timeStart' => '16:30',
        'timeEnd' => '19:00'
      )
    );

    \WP_Mock::wpFunction('update_post_meta', array(
      'times' => 1,
      'args' => array(64, Persistence::IRREGULAR_OPENINGS_META_KEY, $data)
    ));

    $persistence->saveIrregularOpenings($ios);
  }

  public function testLoadIrregularOpenings() {
    $persistence = $this->createPersistence();

    $data = array(
      array(
        'name' => 'IO1',
        'date' => '2016-02-03',
        'timeStart' => '13:00',
        'timeEnd' => '17:00'
      ),
      array(
        'name' => 'IO2',
        'date' => '2016-03-03',
        'timeStart' => '16:30',
        'timeEnd' => '19:00'
      )
    );

    \WP_Mock::wpFunction('get_post_meta', array(
      'times' => 1,
      'args' => array(64, Persistence::IRREGULAR_OPENINGS_META_KEY, true),
      'return' => $data
    ));

    $ios = $persistence->loadIrregularOpenings();

    $io1 = $ios[0];
    $io2 = $ios[1];

    $this->assertEquals('IO1', $io1->getName());
    $this->assertEquals(new DateTime('2016-02-03'), $io1->getDate());
    $this->assertEquals(new DateTime('2016-02-03 13:00'), $io1->getStart());
    $this->assertEquals(new DateTime('2016-02-03 17:00'), $io1->getEnd());

    $this->assertEquals('IO2', $io2->getName());
    $this->assertEquals(new DateTime('2016-03-03'), $io2->getDate());
    $this->assertEquals(new DateTime('2016-03-03 16:30'), $io2->getStart());
    $this->assertEquals(new DateTime('2016-03-03 19:00'), $io2->getEnd());
  }
}
