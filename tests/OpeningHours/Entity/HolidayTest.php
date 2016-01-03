<?php

namespace OpeningHours\Test\Entity;

use DateTime;
use OpeningHours\Entity\Holiday;

class HolidayTest extends \WP_UnitTestCase {

	protected static $testConfig = array(
		'name' => 'Test Holiday',
		'dateStart' => '2016-01-07',
		'dateEnd' => '2016-01-23'
	);

	public function testIsActive () {
		$before = new DateTime('2016-01-06');
		$first = new DateTime('2016-01-07');
		$mid = new DateTime('2016-01-15');
		$last = new DateTime('2016-01-23');
		$after = new DateTime('2016-01-24');

		$holiday = new Holiday( 'Test Holiday', new DateTime('2016-01-07'), new DateTime('2016-01-23') );
		$this->assertFalse( $holiday->isActive( $before ) );
		$this->assertTrue( $holiday->isActive( $first ) );
		$this->assertTrue( $holiday->isActive( $mid ) );
		$this->assertTrue( $holiday->isActive( $last ) );
		$this->assertFalse( $holiday->isActive( $after ) );
	}

	public function testSortStrategy () {
		$h3 = new Holiday('Test3', new DateTime('2016-03-02'), new DateTime('2016-03-02'));
		$h1 = new Holiday('Test1', new DateTime('2016-01-02'), new DateTime('2016-01-02'));
		$h2 = new Holiday('Test2', new DateTime('2016-02-02'), new DateTime('2016-02-02'));

		$holidays = array( $h3, $h1, $h2 );
		usort( $holidays, array( get_class( $h1 ), 'sortStrategy' ) );
		$this->assertEquals( $h1, $holidays[0] );
		$this->assertEquals( $h2, $holidays[1] );
		$this->assertEquals( $h3, $holidays[2] );
	}

	public function testCreateDummyPeriod () {
		$holiday = Holiday::createDummyPeriod();
		$now = new DateTime('now');
		$format = 'Y-m-d';

		$this->assertEquals( '', $holiday->getName() );
		$this->assertEquals( $now->format($format), $holiday->getDateStart()->format($format) );
		$this->assertEquals( $now->format($format), $holiday->getDateEnd()->format($format) );
		$this->assertTrue( $holiday->isDummy() );
	}

	public function testDateSetters () {
		$holiday = new Holiday( 'Test Holiday', new DateTime('2016-01-02'), new DateTime('2016-01-03') );

		$this->assertEquals( new DateTime('2016-01-02 00:00:00'), $holiday->getDateStart() );
		$this->assertEquals( new DateTime('2016-01-03 23:59:59'), $holiday->getDateEnd() );
	}
}