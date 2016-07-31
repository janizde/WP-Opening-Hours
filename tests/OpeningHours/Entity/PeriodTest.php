<?php

namespace OpeningHours\Test\Entity;

use DateInterval;
use DateTime;
use OpeningHours\Entity\Period;
use OpeningHours\Entity\Set;
use OpeningHours\Test\OpeningHoursTestCase;
use OpeningHours\Util\Dates;

class PeriodTest extends OpeningHoursTestCase {

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testConstructExceptionWeekdayNotInt () {
		new Period( 'hello', '12:00', '17:00' );
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testConstructExceptionWeekdayLessThan0 () {
		new Period( -1, '12:00', '17:00' );
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testConstructExceptionWeekdayGreaterThan6 () {
		new Period( 7, '12:00', '17:00' );
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testConstructExceptionTimeStartInvalid () {
		new Period( 0, '123:00', '17:00' );
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testConstructExceptionTimeEndInvalid () {
		new Period( 0, '12:00', '137:00' );
	}

	public function testConstruct () {
		$p = new Period( 2, '17:00', '01:00' );
		$date = Dates::applyWeekContext( new DateTime( '00:00' ), 2 );
		$expectedStart = clone $date;
		$expectedStart->setTime(17, 0);
		$expectedEnd = clone $date;
		$expectedEnd->setTime(1, 0);
		$expectedEnd->add( new DateInterval('P1D') );

		$this->assertEquals( 2, $p->getWeekday() );
		$this->assertEquals( $expectedStart, $p->getTimeStart() );
		$this->assertEquals( $expectedEnd, $p->getTimeEnd() );
		$this->assertFalse( $p->isDummy() );
	}

	public function testIsOpenStrict () {
		$p = new Period( 2, '17:00', '01:00' );
		$date = Dates::applyWeekContext( new DateTime('now'), 2 );

		$before = clone $date;
		$before->setTime(16, 59);
		$first = clone $date;
		$first->setTime(17, 0);
		$mid = clone $date;
		$mid->setTime(22, 0);
		$last = clone $date;
		$last->setTime(1, 0);
		$last->add( new DateInterval('P1D') );
		$after = clone $date;
		$after->setTime(1, 1);
		$after->add( new DateInterval('P1D') );

		$this->assertFalse( $p->isOpenStrict( $before ) );
		$this->assertTrue( $p->isOpenStrict( $first ) );
		$this->assertTrue( $p->isOpenStrict( $mid ) );
		$this->assertTrue( $p->isOpenStrict( $last ) );
		$this->assertFalse( $p->isOpenStrict( $after ) );
	}

	public function testIsOpen () {
		$p1 = new Period( 1, '12:00', '18:00' );
		$p2 = new Period( 5, '12:00', '18:00' );

    $post = $this->createPost(array('ID' => 64));
    $this->setUpSetData(64, array(), array(
      array('name' => 'Holiday 1', 'dateStart' => '2016-01-16', 'dateEnd' => '2016-01-17') // Sat - Sun
    ), array(
      array('name' => 'IO1', 'date' => '2016-01-19', 'timeStart' => '13:00', 'timeEnd' => '17:00') // Tue
    ));
    $this->commonSetMocks();

		$set = new Set( $post );

		$this->assertFalse( $p1->isOpen( new DateTime('2016-01-18 13:00'), $set ) );
		$this->assertTrue( $p1->isOpen( new DateTime('2016-01-12 13:00'), $set ) );
		$this->assertFalse( $p1->isOpen( new DateTime('2016-01-20 13:00'), $set ) );
		$this->assertFalse( $p1->isOpen( new DateTime('2016-01-19 13:00'), $set ) );

		$this->assertTrue( $p2->isOpen( new DateTime('2016-01-09 12:30'), $set ) );
		$this->assertFalse( $p2->isOpen( new DateTime('2016-01-09 11:30'), $set ) );
		$this->assertFalse( $p2->isOpen( new DateTime('2016-01-16 12:30'), $set ) );
		$this->assertFalse( $p2->isOpen( new DateTime('2016-01-16 11:30'), $set ) );
	}

	public function testWillBeOpen () {
		$hStart = Dates::applyWeekContext( new DateTime('00:00:00'), 2 );
		$hEnd = clone $hStart;
		$hEnd->add( new DateInterval('P1D') );

    $post = $this->createPost(array('ID' => 64));
    $this->setUpSetData(64, array(), array(
      array('name' => 'Holiday', 'dateStart' => $hStart->format(Dates::STD_DATE_FORMAT), 'dateEnd' => $hEnd->format(Dates::STD_DATE_FORMAT))
    ));
    $this->commonSetMocks();

		$set = new Set( $post );
		$p1 = new Period( 2, '13:00', '18:00' );
		$p2 = new Period( 4, '13:00', '18:00' );

		$this->assertFalse( $p1->willBeOpen( $set ) );
		$this->assertTrue( $p2->willBeOpen( $set ) );
	}

	public function testSortStrategy () {
		$p1 = new Period( 3, '09:00', '13:00' );
		$p2 = new Period( 2, '10:00', '11:00' );
		$p3 = new Period( 1, '15:00', '17:00' );
		$p4 = new Period( 2, '12:00', '13:00' );
		$p5 = new Period( 2, '12:00', '12:00' );

		/** @var Period[] $periods */
		$periods = array( $p1, $p2, $p3, $p4 );
		usort( $periods, array( get_class($p1), 'sortStrategy' ) );

		for ( $i = 1; $i < count( $periods ); ++$i ) {
			$this->assertGreaterThanOrEqual( $periods[ $i - 1 ]->getTimeStart(), $periods[ $i ]->getTimeStart() );
		}

		$this->assertEquals( 0, Period::sortStrategy( $p4, $p5 ) );
	}

	public function testGetCopyInDateContext () {
		$period = new Period( 1, '13:00', '01:00' );
		$copy = $period->getCopyInDateContext( new DateTime('2016-01-25') );

		$this->assertEquals( 1, $copy->getWeekday() );
		$this->assertEquals( new DateTime('2016-01-26 13:00'), $copy->getTimeStart() );
		$this->assertEquals( new DateTime('2016-01-27 01:00'), $copy->getTimeEnd() );
	}

	public function testEquals () {
		$p = new Period( 2, '13:00', '17:00' );

		$this->assertTrue( $p->equals( new Period( 2, '13:00', '17:00' ) ) );
		$this->assertFalse( $p->equals( new Period( 1, '13:00', '17:00' ) ) );
		$this->assertTrue( $p->equals( new Period( 1, '13:00', '17:00' ), true ) );
		$this->assertFalse( $p->equals( new Period( 2, '13:30', '17:00' ) ) );
		$this->assertFalse( $p->equals( new Period( 2, '13:30', '17:00' ), true ) );
		$this->assertFalse( $p->equals( new Period( 2, '13:00', '17:01' ) ) );
		$this->assertFalse( $p->equals( new Period( 2, '13:00', '17:01' ), true ) );
	}

	public function testCreateDummy () {
		$p = Period::createDummy();
		$expectedStart = Dates::applyWeekContext( new DateTime('now'), 0 );
		$expectedStart->setTime(0,0,0);

		$this->assertEquals( 0, $p->getWeekday() );
		$this->assertEquals( $expectedStart, $p->getTimeStart() );
		$this->assertEquals( $expectedStart->add( new DateInterval('P1D') ), $p->getTimeEnd() );
		$this->assertTrue( $p->isDummy() );
	}

	public function testGetFormattedTimeRange () {
		$p = new Period( 0, '13:00', '17:00' );

		$this->assertEquals( '13:00 - 17:00', $p->getFormattedTimeRange() );
		$this->assertEquals( '0013 - 0017', $p->getFormattedTimeRange('iH') );
	}
}