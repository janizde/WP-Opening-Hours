<?php

namespace OpeningHours\Test\Util;

use DateTime;
use DateTimeZone;
use OpeningHours\Test\OpeningHoursTestCase;
use OpeningHours\Util\Dates;

class DatesTest extends OpeningHoursTestCase {

	public function testIsValidTime () {
		$this->assertTrue( Dates::isValidTime('01:30') );
		$this->assertFalse( Dates::isValidTime('01:348') );
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

	public function testApplyWeekContext () {
		$now = new DateTime('2016-01-13'); // Wed
		$date = new DateTime('2016-03-12');

		$this->assertEquals( new DateTime('2016-01-18'), Dates::applyWeekContext( clone $date, 0, $now ) );
		$this->assertEquals( new DateTime('2016-01-19'), Dates::applyWeekContext( clone $date, 1, $now ) );
		$this->assertEquals( new DateTime('2016-01-13'), Dates::applyWeekContext( clone $date, 2, $now ) );
		$this->assertEquals( new DateTime('2016-01-14'), Dates::applyWeekContext( clone $date, 3, $now ) );
		$this->assertEquals( new DateTime('2016-01-15'), Dates::applyWeekContext( clone $date, 4, $now ) );
		$this->assertEquals( new DateTime('2016-01-16'), Dates::applyWeekContext( clone $date, 5, $now ) );
		$this->assertEquals( new DateTime('2016-01-17'), Dates::applyWeekContext( clone $date, 6, $now ) );
	}

	public function testIsToday () {
		$now = new DateTime('now');
		$today = (int) $now->format('N') - 1;
		$this->assertTrue( Dates::isToday( $today ) );
		$this->assertFalse( Dates::isToday( $today + 1 ) );
	}

	public function testIsTodayMultiple () {
	  $now = new DateTime('now');
    $today = $today = (int) $now->format('N') - 1;
    $days = array((int)$today, 3, 4, 2, 1, 5);
    $this->assertTrue(Dates::isToday($days));

    foreach ($days as $i => $day) {
      if ($day === $today)
        unset($days[$i]);
    }

    $this->assertFalse(Dates::isToday($days));
  }

	public function testIsTodayMultipleAsString () {
	  $now = new DateTime('now');
    $today = $today = (int) $now->format('N') - 1;
    $days = array((int)$today, 3, 4, 2, 1, 5);
    $this->assertTrue(Dates::isToday(implode(',', $days)));

    foreach ($days as $i => $day) {
      if ($day === $today)
        unset($days[$i]);
    }

    $this->assertFalse(Dates::isToday(implode(',',$days)));
  }

	public function testCompareTime () {
		$d1 = new DateTime('2016-02-03 12:30');
		$d2 = new DateTime('2016-12-23 01:45');

		$this->assertEquals( -1, Dates::compareTime( $d2, $d1 ) );
		$this->assertEquals( 0, Dates::compareTime( $d1, $d1 ) );
		$this->assertEquals( 1, Dates::compareTime( $d1, $d2 ) );
	}

	public function testCompareDate () {
		$d1 = new DateTime('2016-02-03 12:30');
		$d2 = new DateTime('2016-04-02 11:30');
		$d3 = new DateTime('2016-02-03 13:30');

		$this->assertEquals( -1, Dates::compareDate( $d1, $d2 ) );
		$this->assertEquals( 0, Dates::compareDate( $d1, $d3 ) );
		$this->assertEquals( 1, Dates::compareDate( $d2, $d1 ) );
	}
}