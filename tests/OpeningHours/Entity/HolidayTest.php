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

	public function testConstruct () {
		$config = self::$testConfig;
		$format = 'Y-m-d';

		$holiday = new Holiday( $config );
		$this->assertEquals( $config['name'], $holiday->getName() );
		$this->assertEquals( $config['dateStart'], $holiday->getDateStart()->format( $format ) );
		$this->assertEquals( $config['dateEnd'], $holiday->getDateEnd()->format( $format ) );
	}

	public function testIsActive () {
		$before = new DateTime('2016-01-06');
		$first = new DateTime('2016-01-07');
		$mid = new DateTime('2016-01-15');
		$last = new DateTime('2016-01-23');
		$after = new DateTime('2016-01-24');

		$holiday = new Holiday( self::$testConfig );
		$this->assertFalse( $holiday->isActive( $before ) );
		$this->assertTrue( $holiday->isActive( $first ) );
		$this->assertTrue( $holiday->isActive( $mid ) );
		$this->assertTrue( $holiday->isActive( $last ) );
		$this->assertFalse( $holiday->isActive( $after ) );
	}

	public function testValidateConfigDummy () {
		$expected = array(
			'name' => '',
			'dateStart' => 'now',
			'dateEnd' => 'now',
			'dummy' => true
		);

		$this->assertEquals( $expected, Holiday::validateConfig( array( 'dummy' => true ) ) );
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testValidateConfigDateStartNotSet () {
		$config = self::$testConfig;
		unset( $config['dateStart'] );
		Holiday::validateConfig( $config );
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testValidateConfigDateStartInvalid () {
		$config = self::$testConfig;
		$config['dateStart'] = 'Hello World';
		Holiday::validateConfig( $config );
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testValidateConfigDateEndNotSet () {
		$config = self::$testConfig;
		unset( $config['dateEnd'] );
		Holiday::validateConfig( $config );
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testValidateConfigDateEndInvalid () {
		$config = self::$testConfig;
		$config['dateEnd'] = 'Hello World';
		Holiday::validateConfig( $config );
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testValidateConfigNameNotSet () {
		$config = self::$testConfig;
		unset( $config['name'] );
		Holiday::validateConfig( $config );
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testValidateConfigNameEmpty () {
		$config = self::$testConfig;
		$config['name'] = '';
		Holiday::validateConfig( $config );
	}

	public function testValidateConfigSetsDummy () {
		$config = Holiday::validateConfig( self::$testConfig );
		$this->assertTrue( array_key_exists( 'dummy', $config ) );
		$this->assertFalse( $config['dummy'] );
	}

	public function testSortStrategy () {
		$h3 = new Holiday(array(
			'name' => 'Test1',
			'dateStart' => '2016-03-02',
			'dateEnd' => '2016-03-02',
			'dummy' => false
		));

		$h1 = new Holiday(array(
			'name' => 'Test2',
			'dateStart' => '2016-01-02',
			'dateEnd' => '2016-01-02',
			'dummy' => false
		));

		$h2 = new Holiday(array(
			'name' => 'Test3',
			'dateStart' => '2016-02-02',
			'dateEnd' => '2016-02-02',
			'dummy' => false
		));

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
		$config = array(
			'name' => 'Test Holiday',
			'dateStart' => '2016-01-02',
			'dateEnd' => '2016-01-03'
		);

		$holiday = new Holiday( $config );

		$this->assertEquals( new DateTime('2016-01-02 00:00:00'), $holiday->getDateStart() );
		$this->assertEquals( new DateTime('2016-01-03 23:59:59'), $holiday->getDateEnd() );
	}
}