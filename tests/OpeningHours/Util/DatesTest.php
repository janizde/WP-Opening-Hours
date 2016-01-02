<?php

namespace OpeningHours\Test\Util;

use DateTime;
use DateTimeZone;
use OpeningHours\Util\Dates;

class DatesTest extends \WP_UnitTestCase {

	public function testOptions () {
		$this->assertEquals( 'H:i', get_option( 'time_format' ) );
		$this->assertEquals( 'd.m.Y', get_option( 'date_format' ) );
		$this->assertEquals( 'Europe/Berlin', get_option( 'timezone_string' ) );
	}

	public function testAttributes () {
		$this->assertEquals( 'H:i', Dates::getTimeFormat() );
		$this->assertEquals( 'd.m.Y', Dates::getDateFormat() );
		$this->assertEquals( 'Europe/Berlin', Dates::getTimezone()->getName() );
		$now = new DateTime( 'now' );
		$this->assertEquals( $now, Dates::getNow(), '', 10 );
	}

	public function testIsValidTime () {
		$this->assertTrue( Dates::isValidTime( '01:30' ) );
		$this->assertFalse( Dates::isValidTime( '01:348' ) );
	}

	public function testMergeDateIntoTime () {
		$date = new DateTime();
		$date->setDate( 2016, 1, 2 );
		$time = new DateTime();
		$time->setDate( 2014, 3, 1 );

		$time = Dates::mergeDateIntoTime( $date, $time );
		$format = 'Y-m-d';
		$this->assertEquals( $date->format($format), $time->format($format) );
	}

	public function testApplyTimezone () {
		$date = new DateTime( 'now', new DateTimeZone( 'America/Anchorage' ) );
		Dates::applyTimeZone( $date );
		$this->assertEquals( Dates::getTimezone(), $date->getTimezone() );
	}

	/**
	 * @todo      make work in weeks 52, 53 and 1
	 */
	public function testApplyWeekContext () {
		$now = new DateTime('now');

		if ( in_array( (int) $now->format('W'), array( 52, 53, 1 ) ) )
			return;

		for ( $i = 0; $i < 7; $i++ ) {
			$date = new DateTime('now');
			Dates::applyWeekContext( $date, $i );
			$this->assertEquals( $i + 1, (int) $date->format('N') );
			$week = (int) $now->format('W');

			if ( $i < (int) $now->format('N') )
				$week++;

			$this->assertEquals( $week, (int) $date->format('W') );
		}
	}

	public function testIsToday () {
		$now = new DateTime('now');
		$today = (int) $now->format('N') - 1;
		$this->assertTrue( Dates::isToday( $today ) );
		$this->assertFalse( Dates::isToday( $today + 1 ) );
	}

}