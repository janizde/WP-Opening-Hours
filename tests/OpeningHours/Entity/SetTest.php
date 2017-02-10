<?php

namespace OpeningHours\Test\Entity;

use DateTime;
use OpeningHours\Entity\Holiday;
use OpeningHours\Entity\IrregularOpening;
use OpeningHours\Entity\Period;
use OpeningHours\Test\OpeningHoursTestCase;
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

	public function testGetActiveIrregularOpening () {
    $io = new IrregularOpening('Irregular Opening', '2016-01-13', '13:00', '17:00');
    $set = $this->createSet(64, array(), array(), array($io));

		$this->assertNull($set->getActiveIrregularOpening(new DateTime('2016-01-12')));
		$this->assertEquals($io, $set->getActiveIrregularOpening(new DateTime('2016-01-13')));
		$this->assertNull($set->getActiveIrregularOpening( new DateTime('2016-01-14')));
	}

	public function testIsIrregularOpeningActive () {
		$set = $this->createSet(64, array(), array(), array(
      new IrregularOpening('Irregular Opening', '2016-01-13', '13:00', '17:00')
    ));
		$this->assertFalse( $set->isIrregularOpeningActive( new DateTime('2016-01-12') ) );
		$this->assertTrue( $set->isIrregularOpeningActive( new DateTime('2016-01-13') ) );
		$this->assertFalse( $set->isIrregularOpeningActive( new DateTime('2016-01-14') ) );
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
}