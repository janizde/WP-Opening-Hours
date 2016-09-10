<?php

namespace OpeningHours\Test\Util;

use OpeningHours\Test\OpeningHoursTestCase;
use OpeningHours\Util\Weekdays;

class WeekdaysTest extends OpeningHoursTestCase {

	protected static $slugs = array(
		'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'
	);

	protected static $captions = array(
		'short' => array( 'Mon.', 'Tue.', 'Wed.', 'Thu.', 'Fri.', 'Sat.', 'Sun.' ),
		'full' => array( 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday' )
	);

	public function testWeekdays () {
		$days = Weekdays::getWeekdays();

		$this->assertEquals( 7, count( $days ) );
		foreach ( self::$slugs as $i => $slug ) {
			$this->assertEquals( $slug, $days[$i]->getSlug() );
		}
	}

	public function testGetWeekday () {
		$w = Weekdays::getInstance();
		foreach ( self::$slugs as $i => $slug ) {
			$this->assertEquals( $slug, $w->getWeekday($i)->getSlug() );
		}
		$this->assertNull( $w->getWeekday(-1) );
		$this->assertNull( $w->getWeekday(7) );
	}

	public function testGetWeekdayBySlug () {
		foreach ( self::$slugs as $slug ) {
			$this->assertEquals( $slug, Weekdays::getWeekdayBySlug($slug)->getSlug() );
		}
		$this->assertNull( Weekdays::getWeekdayBySlug('marsday') );
	}

	public function testGetCaptions () {
		$this->assertEquals( self::$captions['full'], Weekdays::getCaptions() );
		$this->assertEquals( self::$captions['short'], Weekdays::getCaptions( true ) );
	}

	public function testGetDaysCaption () {
		$this->assertEquals( 'Tuesday', Weekdays::getDaysCaption(1) );
		$this->assertEquals( 'Tue.', Weekdays::getDaysCaption(1, true) );

		$this->assertEquals( 'Monday, Wednesday, Friday', Weekdays::getDaysCaption( array(0, 2, 4) ) );
		$this->assertEquals( 'Mon., Wed., Fri.', Weekdays::getDaysCaption( array(0, 2, 4), true ) );
		$this->assertEquals( 'Monday - Friday', Weekdays::getDaysCaption( range(0, 4) ) );
		$this->assertEquals( 'Mon. - Fri.', Weekdays::getDaysCaption( range(0, 4), true ) );
		$this->assertEquals( 'Monday, Tuesday, Wednesday, Friday', Weekdays::getDaysCaption( array(0, 1, 2, 4) ) );
		$this->assertEquals( 'Mon., Tue., Wed., Fri.', Weekdays::getDaysCaption( array(0, 1, 2, 4), true ) );
		$this->assertEquals( 'Monday - Friday', Weekdays::getDaysCaption( array(2, 0, 4, 1, 3) ) );

		$this->assertEquals( 'Monday, Wednesday, Friday', Weekdays::getDaysCaption( '0,2,4' ) );
		$this->assertEquals( 'Monday - Friday', Weekdays::getDaysCaption( '0,1,2,3,4' ) );
		$this->assertEquals( 'Monday, Tuesday, Wednesday, Friday', Weekdays::getDaysCaption( '0,1,2,4' ) );
		$this->assertEquals( 'Monday - Friday', Weekdays::getDaysCaption( '2,0,4,1,3' ) );
		$this->assertEquals( 'Monday - Friday', Weekdays::getDaysCaption( ' 2 ,0    , 4 ,  1 , 3 ' ) );
	}

	public function testGetDatePickerTranslations () {
	  $expected = array(
	    'full' => array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'),
      'short' => array('Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat')
    );

    $this->assertEquals($expected, Weekdays::getDatePickerTranslations());
  }
}