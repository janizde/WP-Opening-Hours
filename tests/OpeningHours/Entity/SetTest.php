<?php

namespace OpeningHours\Test\Entity;

use DateTime;
use OpeningHours\Entity\Holiday;
use OpeningHours\Entity\IrregularOpening;
use OpeningHours\Entity\Period;
use OpeningHours\Entity\Set;
use OpeningHours\Test\OpeningHoursTestCase;
use OpeningHours\Util\ArrayObject;
use OpeningHours\Util\Dates;

class SetTest extends OpeningHoursTestCase {

	public function testIsHolidayActive () {
		$set = $this->createSet(64, array(), array(
		  new Holiday('Holiday 1', new DateTime('2016-01-12', Dates::getTimezone()), new DateTime('2016-01-14', Dates::getTimezone()))
    ));

		$this->assertFalse( $set->isHolidayActive( new DateTime('2016-01-11 23:59', Dates::getTimezone())) );
		$this->assertTrue( $set->isHolidayActive( new DateTime('2016-01-12', Dates::getTimezone())) );
		$this->assertTrue( $set->isHolidayActive( new DateTime('2016-01-13', Dates::getTimezone())) );
		$this->assertTrue( $set->isHolidayActive( new DateTime('2016-01-14 23:59', Dates::getTimezone()) ) );
		$this->assertFalse( $set->isHolidayActive( new DateTime('2016-01-15 00:01', Dates::getTimezone())) );
	}

	public function testGetIrregularOpeningInEffect () {
    $io = new IrregularOpening('Irregular Opening', '2016-01-13', '13:00', '17:00');
    $set = $this->createSet(64, array(), array(), array($io));

		$this->assertNull($set->getIrregularOpeningInEffect(new DateTime('2016-01-12')));
		$this->assertEquals($io, $set->getIrregularOpeningInEffect(new DateTime('2016-01-13')));
		$this->assertNull($set->getIrregularOpeningInEffect( new DateTime('2016-01-14')));
	}

	public function testIsIrregularOpeningInEffect () {
		$set = $this->createSet(64, array(), array(), array(
      new IrregularOpening('Irregular Opening', '2016-01-13', '13:00', '17:00')
    ));

		$this->assertFalse($set->isIrregularOpeningInEffect(new DateTime('2016-01-12')));
		$this->assertTrue($set->isIrregularOpeningInEffect(new DateTime('2016-01-13')));
		$this->assertFalse($set->isIrregularOpeningInEffect(new DateTime('2016-01-14')));
	}

	public function testGetNextOpenPeriodOnlyPeriods () {
    /** @var Period[] $periods */
    $periods = array(
      new Period(1, '13:00', '18:00'),
      new Period(1, '19:00', '21:00'),
      new Period(1, '20:00', '22:00'),
      new Period(3, '13:00', '18:00'),
      new Period(6, '13:00', '03:00')
    );

    $set = $this->createSet(64, array(
      new Period(1, '13:00', '18:00'),
      new Period(1, '19:00', '21:00'),
      new Period(1, '20:00', '22:00'),
      new Period(3, '13:00', '18:00'),
      new Period(6, '13:00', '03:00')
    ));

    $this->assertEquals($periods[0]->getCopyInDateContext(new DateTime('2016-01-25', Dates::getTimezone())), $set->getNextOpenPeriod(new DateTime('2016-01-25 12:59', Dates::getTimezone())));
    $this->assertEquals($periods[1]->getCopyInDateContext(new DateTime('2016-01-25', Dates::getTimezone())), $set->getNextOpenPeriod(new DateTime('2016-01-25 13:00', Dates::getTimezone())));
    $this->assertEquals($periods[1]->getCopyInDateContext(new DateTime('2016-01-25', Dates::getTimezone())), $set->getNextOpenPeriod(new DateTime('2016-01-25 18:00', Dates::getTimezone())));
    $this->assertEquals($periods[1]->getCopyInDateContext(new DateTime('2016-01-25', Dates::getTimezone())), $set->getNextOpenPeriod(new DateTime('2016-01-25 18:01', Dates::getTimezone())));
    $this->assertEquals($periods[1]->getCopyInDateContext(new DateTime('2016-01-25', Dates::getTimezone())), $set->getNextOpenPeriod(new DateTime('2016-01-25 18:59', Dates::getTimezone())));
    $this->assertEquals($periods[2]->getCopyInDateContext(new DateTime('2016-01-25', Dates::getTimezone())), $set->getNextOpenPeriod(new DateTime('2016-01-25 19:00', Dates::getTimezone())));
    $this->assertEquals($periods[2]->getCopyInDateContext(new DateTime('2016-01-25', Dates::getTimezone())), $set->getNextOpenPeriod(new DateTime('2016-01-25 19:59', Dates::getTimezone())));
    $this->assertEquals($periods[3]->getCopyInDateContext(new DateTime('2016-01-27', Dates::getTimezone())), $set->getNextOpenPeriod(new DateTime('2016-01-25 20:00', Dates::getTimezone())));
    $this->assertEquals($periods[3]->getCopyInDateContext(new DateTime('2016-01-27', Dates::getTimezone())), $set->getNextOpenPeriod(new DateTime('2016-01-27 12:59', Dates::getTimezone())));
    $this->assertEquals($periods[4]->getCopyInDateContext(new DateTime('2016-01-30', Dates::getTimezone())), $set->getNextOpenPeriod(new DateTime('2016-01-27 13:00', Dates::getTimezone())));
    $this->assertEquals($periods[4]->getCopyInDateContext(new DateTime('2016-01-30', Dates::getTimezone())), $set->getNextOpenPeriod(new DateTime('2016-01-30 12:59', Dates::getTimezone())));
    $this->assertEquals($periods[0]->getCopyInDateContext(new DateTime('2016-01-31', Dates::getTimezone())), $set->getNextOpenPeriod(new DateTime('2016-01-30 13:00', Dates::getTimezone())));
    $this->assertEquals($periods[0]->getCopyInDateContext(new DateTime('2016-01-31', Dates::getTimezone())), $set->getNextOpenPeriod(new DateTime('2016-01-31 03:00', Dates::getTimezone())));
    $this->assertEquals($periods[0]->getCopyInDateContext(new DateTime('2016-01-31', Dates::getTimezone())), $set->getNextOpenPeriod(new DateTime('2016-01-31 03:01', Dates::getTimezone())));
  }

  public function testGetNextOpenPeriodHolidays () {
    /** @var Period[] $periods */
    $periods = array(
      new Period(1, '13:00', '18:00'),
      new Period(1, '19:00', '21:00'),
      new Period(1, '20:00', '22:00'),
      new Period(3, '13:00', '18:00'),
      new Period(6, '13:00', '03:00')
    );

    $set = $this->createSet(64, array(
      new Period(1, '13:00', '18:00'),
      new Period(1, '19:00', '21:00'),
      new Period(1, '20:00', '22:00'),
      new Period(3, '13:00', '18:00'),
      new Period(6, '13:00', '03:00')
    ), array(
      new Holiday('Test Holiday', new DateTime('2016-01-27'), new DateTime('2016-01-28')),
      new Holiday('Long Holiday', new DateTime('2016-02-06'), new DateTime('2016-09-25'))
    ));

    $this->assertEquals($periods[4]->getCopyInDateContext(new DateTime('2016-01-30')), $set->getNextOpenPeriod(new DateTime('2016-01-27 12:59')));
    $this->assertEquals($periods[4]->getCopyInDateContext(new DateTime('2016-01-30')), $set->getNextOpenPeriod(new DateTime('2016-01-27 13:00')));
    $this->assertEquals($periods[0]->getCopyInDateContext(new DateTime('2016-09-26')), $set->getNextOpenPeriod(new DateTime('2016-02-05 13:00')));
  }

  public function testGetNextOpenPeriodIrregularOpenings () {
    $io = new IrregularOpening('IO1', '2016-01-25', '14:00', '19:30');
    $ioPeriod = $io->createPeriod();
    $set = $this->createSet(64, array(
      new Period(1, '13:00', '18:00'),
      new Period(1, '19:00', '21:00'),
      new Period(1, '20:00', '22:00'),
      new Period(3, '13:00', '18:00'),
      new Period(6, '13:00', '03:00')
    ), array(), array(
      $io
    ));

    $this->assertEquals($ioPeriod, $set->getNextOpenPeriod(new DateTime('2016-01-25 12:59')));
    $this->assertEquals($ioPeriod, $set->getNextOpenPeriod(new DateTime('2016-01-25 13:00')));
  }

  public function testGetNextOpenPeriodOnlyIrregularOpenings () {
    $ios = array(
      new IrregularOpening('IO1', '2016-01-20', '13:00', '14:00'),
      new IrregularOpening('IO2', '2016-01-22', '14:00', '17:00')
    );

    $set = $this->createSet(64, array(), array(), $ios);

    $this->assertEquals($ios[0]->createPeriod(), $set->getNextOpenPeriod(new DateTime('2016-01-20 12:59:59')));
    $this->assertEquals($ios[1]->createPeriod(), $set->getNextOpenPeriod(new DateTime('2016-01-20 13:00:00')));
    $this->assertEquals($ios[1]->createPeriod(), $set->getNextOpenPeriod(new DateTime('2016-01-22 13:59:59')));
    $this->assertNull($set->getNextOpenPeriod(new DateTime('2016-01-22 14:00:00')));
  }

	public function testIsOpen () {
		$set = $this->createSet(64, array(
		  new Period(2, '13:00', '18:00'),
		  new Period(2, '19:00', '21:00'),
		  new Period(2, '20:00', '22:00'),
		  new Period(4, '13:00', '18:00'),
		  new Period(0, '13:00', '03:00')
    ), array(
      new Holiday('Test Holiday', new DateTime('2016-01-25'), new DateTime('2016-01-26'))
    ), array(
      new IrregularOpening('IO', '2016-01-28', '15:00', '17:00')
    ));

		$this->assertFalse( $set->isOpen( new DateTime('2016-01-25 13:00') ) );
		$this->assertFalse( $set->isOpen( new DateTime('2016-01-26 12:59') ) );
		$this->assertFalse( $set->isOpen( new DateTime('2016-01-26 13:00') ) );
		$this->assertFalse( $set->isOpen( new DateTime('2016-01-27 13:00') ) );
		$this->assertFalse( $set->isOpen( new DateTime('2016-01-28 12:59') ) );
		$this->assertFalse( $set->isOpen( new DateTime('2016-01-28 13:00') ) );
		$this->assertFalse( $set->isOpen( new DateTime('2016-01-28 14:59') ) );
		$this->assertTrue( $set->isOpen( new DateTime('2016-01-28 15:00') ) );
		$this->assertTrue( $set->isOpen( new DateTime('2016-01-28 17:00') ) );
		$this->assertFalse( $set->isOpen( new DateTime('2016-01-28 17:01') ) );
		$this->assertFalse( $set->isOpen( new DateTime('2016-01-28 18:0') ) );
		$this->assertFalse( $set->isOpen( new DateTime('2016-01-28 18:01') ) );
		$this->assertFalse( $set->isOpen( new DateTime('2016-01-31 12:59') ) );
		$this->assertTrue( $set->isOpen( new DateTime('2016-01-31 13:00') ) );
		$this->assertTrue( $set->isOpen( new DateTime('2016-02-01 03:00') ) );
		$this->assertFalse( $set->isOpen( new DateTime('2016-02-01 03:01') ) );
	}

	public function testGetDataForDate() {
	  $periods = array(
      new Period(2, '13:00', '18:00'),
      new Period(2, '19:00', '21:00'),
      new Period(2, '20:00', '22:00'),
      new Period(4, '13:00', '18:00'),
      new Period(0, '13:00', '03:00')
    );

	  $holidays = array(
      new Holiday('Test Holiday 1', new DateTime('2017-04-24'), new DateTime('2017-04-25')),
      new Holiday('Test Holiday 2', new DateTime('2017-04-24'), new DateTime('2017-04-24')),
      new Holiday('Test Holiday 3', new DateTime('2017-04-26'), new DateTime('2017-04-27'))
    );

	  $irregularOpenings = array(
      new IrregularOpening('IO 1', '2017-04-24', '13:00', '17:00'),
      new IrregularOpening('IO 2', '2017-04-25', '13:00', '17:00'),
      new IrregularOpening('IO 3', '2017-04-26', '13:00', '17:00')
    );

	  $set = $this->createSet(64, $periods, $holidays, $irregularOpenings);

	  $expected = array(
	    'periods' => array(
	      $periods[0],
	      $periods[1],
	      $periods[2]
      ),
      'holidays' => array(
        $holidays[0]
      ),
      'irregularOpenings' => array(
        $irregularOpenings[1]
      )
    );

	  $result = $set->getDataForDate(new DateTime('2017-04-25 18:00:00'));

	  $this->assertEquals($expected, $result);
  }

  /**
   * Test satisfying requirements for issue #72 in GitHub
   * https://github.com/janizde/WP-Opening-Hours/issues/72
   */
  public function testIsOpenIOGitHub72 () {
	  $irregularOpenings = new ArrayObject();
	  $irregularOpenings->append(new IrregularOpening('My IO', '2017-05-29', '12:00', '00:00'));

	  $periods = new ArrayObject();
	  $periods->append(new Period(2, '07:30', '00:00'));

	  $set = new Set(64);
	  $set->setIrregularOpenings($irregularOpenings);
	  $set->setPeriods($periods);

	  $this->assertFalse($set->isOpen(new DateTime('2017-05-29 00:00:00')));
	  $this->assertFalse($set->isOpen(new DateTime('2017-05-29 11:59:59')));
	  $this->assertTrue($set->isOpen(new DateTime('2017-05-29 12:00:00')));
	  $this->assertTrue($set->isOpen(new DateTime('2017-05-29 23:59:59')));
	  $this->assertTrue($set->isOpen(new DateTime('2017-05-30 00:00:00')));
	  $this->assertFalse($set->isOpen(new DateTime('2017-05-30 00:00:01')));

	  $this->assertFalse($set->isOpen(new DateTime('2017-05-30 07:29:59')));
	  $this->assertTrue($set->isOpen(new DateTime('2017-05-30 07:30:00')));
	  $this->assertTrue($set->isOpen(new DateTime('2017-05-30 12:00:00')));
	  $this->assertTrue($set->isOpen(new DateTime('2017-05-30 23:59:59')));
	  $this->assertTrue($set->isOpen(new DateTime('2017-05-31 00:00:00')));
	  $this->assertFalse($set->isOpen(new DateTime('2017-05-31 00:01:00')));
  }
}
