<?php

namespace OpeningHours\Test\Util;

use OpeningHours\Test\OpeningHoursTestCase;
use OpeningHours\Util\Dates;
use OpeningHours\Util\Weekday;
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

  public function testGetWeekdaysInOrder () {
    Dates::setStartOfWeek(0);
    $days = Weekdays::getWeekdaysInOrder();
    $this->assertWeekdaysInOrder(array(0,1,2,3,4,5,6), $days);

    Dates::setStartOfWeek(1);
    $days = Weekdays::getWeekdaysInOrder();
    $this->assertWeekdaysInOrder(array(1,2,3,4,5,6,0), $days);

    Dates::setStartOfWeek(2);
    $days = Weekdays::getWeekdaysInOrder();
    $this->assertWeekdaysInOrder(array(2,3,4,5,6,0,1), $days);

    Dates::setStartOfWeek(3);
    $days = Weekdays::getWeekdaysInOrder();
    $this->assertWeekdaysInOrder(array(3,4,5,6,0,1,2), $days);

    Dates::setStartOfWeek(4);
    $days = Weekdays::getWeekdaysInOrder();
    $this->assertWeekdaysInOrder(array(4,5,6,0,1,2,3), $days);

    Dates::setStartOfWeek(5);
    $days = Weekdays::getWeekdaysInOrder();
    $this->assertWeekdaysInOrder(array(5,6,0,1,2,3,4), $days);

    Dates::setStartOfWeek(6);
    $days = Weekdays::getWeekdaysInOrder();
    $this->assertWeekdaysInOrder(array(6,0,1,2,3,4,5), $days);
  }

  /**
   * @param int[] $expected
   * @param Weekday[] $days
   */
  protected function assertWeekdaysInOrder (array $expected, array $days) {
    $this->assertEquals(count($expected), count($days));
    for ($i = 0; $i < count($expected); ++$i) {
      $this->assertEquals($expected[$i], $days[$i]->getIndex());
    }
  }
}