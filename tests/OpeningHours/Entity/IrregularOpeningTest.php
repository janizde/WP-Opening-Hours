<?php

namespace OpeningHours\Test\Entity;

use DateTime;
use OpeningHours\Entity\IrregularOpening;
use OpeningHours\Util\Dates;

class IrregularOpeningTest extends \WP_UnitTestCase {

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testConstructExceptionTimeStartInvalid () {
		new IrregularOpening( 'Test', '2016-01-03', '00:234', '23:59' );
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testConstructExceptionTimeEndInvalid () {
		new IrregularOpening( 'Test', '2016-01-03', '00:23', '23:579' );
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testConstructExceptionDateInvalid () {
		new IrregularOpening( 'Test', '2016-01-033', '00:23', '23:57' );
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testConstructExceptionNameEmpty () {
		new IrregularOpening( '', '2016-01-03', '00:23', '23:57' );
	}

	public function testConstruct () {
		$io = new IrregularOpening( 'Test', '2016-02-03', '12:15', '23:20' );
		$this->assertEquals( 'Test', $io->getName() );
		$this->assertEquals( new DateTime( '2016-02-03 00:00:00' ), $io->getDate() );
		$this->assertEquals( new DateTime( '2016-02-03 12:15' ), $io->getTimeStart() );
		$this->assertEquals( new DateTime( '2016-02-03 23:20' ), $io->getTimeEnd() );
	}

	public function testTimeEndNextDay () {
		$io = new IrregularOpening( 'Test', '2016-02-03', '13:00', '01:00' );
		$this->assertEquals( new DateTime('2016-02-03 13:00'), $io->getTimeStart() );
		$this->assertEquals( new DateTime('2016-02-04 01:00'), $io->getTimeEnd() );
	}
	
	public function testIsActiveOnDay () {
		$io = new IrregularOpening( 'Test', '2016-02-03', '13:00', '01:00' );
		$d1 = new DateTime('2016-02-02 13:00');
		$d2 = new DateTime('2016-02-03 13:00');
		$d3 = new DateTime('2016-02-04 13:00');
		$d4 = new DateTime('2016-02-05 13:00');

		$this->assertFalse( $io->isActiveOnDay( $d1 ) );
		$this->assertFalse( $io->isActiveOnDay( $d1, true ) );

		$this->assertTrue( $io->isActiveOnDay( $d2 ) );
		$this->assertTrue( $io->isActiveOnDay( $d2, true ) );

		$this->assertTrue( $io->isActiveOnDay( $d3 ) );
		$this->assertFalse( $io->isActiveOnDay( $d3, true ) );

		$this->assertFalse( $io->isActiveOnDay( $d4 ) );
		$this->assertFalse( $io->isActiveOnDay( $d4, true ) );
	}

	public function testIsOpen () {
		$io = new IrregularOpening( 'Test', '2016-02-03', '13:00', '01:00' );
		$before = new DateTime('2016-02-03 12:59');
		$first = new DateTime('2016-02-03 13:00');
		$mid = new DateTime('2016-02-03 17:00');
		$last = new DateTime('2016-02-04 00:59');
		$after = new DateTime('2016-02-04 01:01');

		$this->assertFalse( $io->isOpen( $before ) );
		$this->assertTrue( $io->isOpen( $first ) );
		$this->assertTrue( $io->isOpen( $mid ) );
		$this->assertTrue( $io->isOpen( $last ) );
		$this->assertFalse( $io->isOpen( $after ) );
	}

	public function testGetFormattedTimeRange () {
		$io = new IrregularOpening( 'Test', '2016-02-03', '13:00', '01:00' );

		$this->assertEquals( '13:00 - 01:00', $io->getFormattedTimeRange() );
		$this->assertEquals( '1300 - 0100', $io->getFormattedTimeRange( 'Hi' ) );
		$this->assertEquals( '01:00 // 13:00', $io->getFormattedTimeRange( null, '%2$s // %1$s' ) );
	}

	public function testToArray () {
		$io = new IrregularOpening( 'Test', '2016-02-03', '13:00', '01:00' );
		$expected = array(
			'name' => 'Test',
			'date' => '2016-02-03',
			'timeStart' => '13:00',
			'timeEnd' => '01:00'
		);

		$this->assertEquals( $expected, $io->toArray() );
	}

	public function testSortStratety () {
		$io1 = new IrregularOpening( 'Test', '2016-02-03', '13:00', '01:00' );
		$io2 = new IrregularOpening( 'Test', '2016-01-03', '14:00', '01:00' );
		$io3 = new IrregularOpening( 'Test', '2016-04-03', '01:00', '01:00' );

		$ios = array( $io1, $io2, $io3 );
		usort( $ios, array( get_class( $io1 ), 'sortStrategy' ) );

		$this->assertEquals( array( $io2, $io1, $io3 ), $ios );
	}

	public function testCreateDummy () {
		$io = IrregularOpening::createDummy();

		$expectedDate = new DateTime('now');
		$expectedDate->setTime( 0, 0, 0 );

		$this->assertEquals( '', $io->getName() );
		$this->assertEquals( $expectedDate, $io->getDate() );
		$this->assertEquals( $expectedDate, $io->getTimeStart() );
		$this->assertEquals( $expectedDate, $io->getTimeStart() );
	}

}