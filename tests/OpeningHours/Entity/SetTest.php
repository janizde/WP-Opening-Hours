<?php

namespace OpeningHours\Test\Entity;

use DateInterval;
use DateTime;
use OpeningHours\Entity\Period;
use OpeningHours\Entity\Set;
use OpeningHours\Module\CustomPostType\MetaBox\SetDetails;
use OpeningHours\Test\OpeningHoursTestCase;
use OpeningHours\Util\Dates;
use OpeningHours\Util\Persistence;
use WP_Mock\Functions;

class SetTest extends OpeningHoursTestCase {

  /**
   * Sets up post meta mocks for child set criteria
   * @param     int       $postId   The id of the post
   * @param     int       $dateStartOffset  Offset in days relative to current date
   * @param     int       $dateEndOffset    Offset in days relative to current date
   * @param     bool|string $weekSchemeMatches  whether the week scheme should match regarding the current date. 'all' is also possible
   */
  protected function setUpCriteria ($postId, $dateStartOffset = null, $dateEndOffset = null, $weekSchemeMatches = null) {
    $setDetails = SetDetails::getInstance()->getPersistence();
    $now = new DateTime();

    if ($dateStartOffset !== null) {
      $interval = new DateInterval('P'.abs($dateStartOffset).'D');
      if ($dateStartOffset < 0)
        $interval->invert = true;

      $key = $setDetails->generateMetaKey('dateStart');
      $val = (clone $now)->add($interval)->format(Dates::STD_DATE_FORMAT);
      \WP_Mock::wpFunction('get_post_meta', array(
        'times' => 1,
        'args' => array($postId, $key, true),
        'return' => $val
      ));
    }

    if ($dateEndOffset !== null) {
      $interval = new DateInterval('P'.abs($dateEndOffset).'D');
      if ($dateEndOffset < 0)
        $interval->invert = true;

      \WP_Mock::wpFunction('get_post_meta', array(
        'args' => array($postId, $setDetails->generateMetaKey('dateEnd'), true),
        'return' => (clone $now)->add($interval)->format(Dates::STD_DATE_FORMAT)
      ));
    }

    if ($weekSchemeMatches !== null) {
      if ($weekSchemeMatches === 'all') {
        $weekScheme = 'all';
      } else {
        $current = (int) $now->format('W');
        $even = $current % 2 == 0;
        if ($weekSchemeMatches == false)
          $even = !$even;

        $weekScheme = $even ? 'even' : 'odd';
      }

      \WP_Mock::wpFunction('get_post_meta', array(
        'args' => array($postId, $setDetails->generateMetaKey('weekScheme'), true),
        'return' => $weekScheme
      ));
    }
  }

  /**
   * Construct new set without any data
   */
  public function testConstructNoPeriods () {
		$post = $this->createPost(array('ID' => 64));
    $this->commonSetMocks();

		$set = new Set( $post );

		$this->assertEquals( $post->ID, $set->getId() );
		$this->assertEquals( $post->ID, $set->getParentId() );
		$this->assertEquals( $post, $set->getPost() );
		$this->assertEquals( $post, $set->getParentPost() );
		$this->assertEquals( 0, $set->getPeriods()->count() );
		$this->assertEquals( 0, $set->getIrregularOpenings()->count() );
		$this->assertEquals( 0, $set->getHolidays()->count() );
		$this->assertFalse( $set->hasParent() );
		$this->assertEquals( '', $set->getDescription() );
	}

  /**
   * Add description to Set
   */
	public function testConstructWithDescription () {
	  $post = $this->createPost(array('ID' => 64));
		$setDetails = SetDetails::getInstance()->getPersistence();

    \WP_Mock::wpFunction('get_post_meta', array(
      'times' => 1,
      'args' => array(64, $setDetails->generateMetaKey('description'), true),
      'return' => 'Test Description'
    ));

    $this->commonSetMocks();

    $set = new Set( $post );
		$this->assertEquals( 'Test Description', $set->getDescription() );
	}

  /**
   * Associate Periods with Set
   */
	public function testConstructLoadsPeriods () {
		$post = $this->createPost(array('ID' => 64));

    $this->setUpSetData(64, array(
      array('weekday' => 2, 'timeStart' => '13:00', 'timeEnd' => '17:00'),
      array('weekday' => 1, 'timeStart' => '13:00', 'timeEnd' => '17:00')
    ));

    $this->commonSetMocks();

    $set = new Set( $post );
		$periods = $set->getPeriods();
		$this->assertEquals( 2, count( $periods ) );
		$this->assertEquals( 2, $periods->offsetGet( 0 )->getWeekday() );
		$this->assertEquals( 1, $periods->offsetGet( 1 )->getWeekday() );
	}

  /**
   * Add a child set matching the criteria
   */
	public function testConstructChildSet () {
		$parent = $this->createPost(array('ID' => 64));
    $child = $this->createPost(array(
      'ID' => 128,
      'post_parent' => 64
    ));

    $this->setUpCriteria(128, -2, 2, 'all');

    \WP_Mock::wpFunction('get_posts', array(
      'times' => 1,
      'args' => array(array(
        'post_type' => \OpeningHours\Module\CustomPostType\Set::CPT_SLUG,
        'post_parent' => 64
      )),
      'return' => array(
        $child
      )
    ));

    $this->commonSetMocks(array('get_posts'));

		$set = new Set( $parent );
		$this->assertFalse( $set->isParent() );
    $this->assertEquals($parent->ID, $set->getParentId());
    $this->assertEquals($parent, $set->getParentPost());
    $this->assertEquals($child->ID, $set->getId());
    $this->assertEquals($child, $set->getPost());
	}

	public function testPostMatchesCriteriaNothingSet () {
    $this->commonSetMocks();
		$set = new Set( $this->createPost(array('ID' => 64)) );
		
		$post = $this->createPost(array('ID' => 128));
    $this->setUpCriteria(128, null, null, null);
		$this->assertFalse( $set->postMatchesCriteria( $post ) );
	}

	public function testPostMatchesCriteriaDateStart () {
    $post128 = $this->createPost(array('ID' => 128));
    $this->setUpCriteria($post128->ID, -1, null, null);

    $post129 = $this->createPost(array('ID' => 129));
    $this->setUpCriteria($post129->ID, 0, null, null);

    $post130 = $this->createPost(array('ID' => 130));
    $this->setUpCriteria($post130->ID, 1, null, null);

    $this->commonSetMocks(array(Set::WP_ACTION_BEFORE_SETUP));
    \WP_Mock::expectAction(Set::WP_ACTION_BEFORE_SETUP, Functions::type('OpeningHours\Entity\Set'));

    $set = new Set( $this->createPost(array('ID' => 64)) );
    $this->assertTrue($set->postMatchesCriteria($post128));
    $this->assertTrue($set->postMatchesCriteria($post129));
    $this->assertFalse($set->postMatchesCriteria($post130));
	}

	public function testPostMatchesCriteriaDateEnd () {
    $post128 = $this->createPost(array('ID' => 128));
    $this->setUpCriteria($post128->ID, null, 1, null);

    $post129 = $this->createPost(array('ID' => 129));
    $this->setUpCriteria($post129->ID, null, 0, null);

    $post130 = $this->createPost(array('ID' => 130));
    $this->setUpCriteria($post130->ID, null, -1, null);

    $this->commonSetMocks(array(Set::WP_ACTION_BEFORE_SETUP));
    \WP_Mock::expectAction(Set::WP_ACTION_BEFORE_SETUP, Functions::type('OpeningHours\Entity\Set'));

    $set = new Set( $this->createPost(array('ID' => 64)) );
    $this->assertTrue($set->postMatchesCriteria($post128));
    $this->assertTrue($set->postMatchesCriteria($post129));
    $this->assertFalse($set->postMatchesCriteria($post130));
	}

	public function testPostMatchesCriteriaWeekScheme () {
		$post128 = $this->createPost(array('ID' => 128));
    $this->setUpCriteria($post128->ID, null, null, 'all');

		$post129 = $this->createPost(array('ID' => 129));
    $this->setUpCriteria($post129->ID, null, null, true);

		$post130 = $this->createPost(array('ID' => 130));
    $this->setUpCriteria($post130->ID, null, null, false);

    $this->commonSetMocks();

    $set = new Set($this->createPost(array('ID' => 64)));
    $set->postMatchesCriteria($post128);
    $this->assertFalse($set->postMatchesCriteria($post128));
    $this->assertTrue($set->postMatchesCriteria($post129));
    $this->assertFalse($set->postMatchesCriteria($post130));
	}

	public function testAddDummyPeriodsNoPeriods () {
		$post = $this->createPost(array('ID' => 64));
    $this->commonSetMocks();

		$set = new Set( $post );
		$this->assertEquals( 0, $set->getPeriods()->count() );
		$set->addDummyPeriods();
		$this->assertEquals( 7, $set->getPeriods()->count() );

		for ( $i = 0; $i < 7; $i++ ) {
			$days = $set->getPeriodsByDay( $i );
			$this->assertEquals( 1, count( $days ) );
		}
	}

	public function testAddDummyPeriodsHasPeriods () {
		$post = $this->createPost(array('ID' => 64));

    $this->setUpSetData(64, array(
      array('weekday' => 1, 'timeStart' => '13:00', 'timeEnd' => '17:00'),
      array('weekday' => 1, 'timeStart' => '18:00', 'timeEnd' => '21:00'),
      array('weekday' => 2, 'timeStart' => '13:00', 'timeEnd' => '17:00')
    ));

    $this->commonSetMocks();

		$set = new Set( $post );
		$set->addDummyPeriods();

		for ( $i = 0; $i < 7; $i++ ) {
			$days = $set->getPeriodsByDay( $i );
			$this->assertEquals( $i == 1 ? 2 : 1, count($days) );
		}
	}

	public function testGetPeriodsByDay () {
		$post = $this->createPost(array('ID' => 64));

    $this->setUpSetData(64, array(
      array('weekday' => 1, 'timeStart' => '13:00', 'timeEnd' => '17:00'),
      array('weekday' => 1, 'timeStart' => '18:00', 'timeEnd' => '21:00'),
      array('weekday' => 2, 'timeStart' => '13:00', 'timeEnd' => '17:00')
    ));

    $this->commonSetMocks();

		$set = new Set( $post );
		$day1 = $set->getPeriodsByDay( 1 );
		$this->assertEquals( 2, count( $day1 ) );
		$this->assertEquals( 1, $day1[0]->getWeekday() );
		$this->assertEquals( 1, $day1[1]->getWeekday() );

		$day2 = $set->getPeriodsByDay( 2 );
		$this->assertTrue( is_array( $day2 ) );
		$this->assertEquals( 1, count( $day2 ) );
		$this->assertEquals( 2, $day2[0]->getWeekday() );

		$day3 = $set->getPeriodsByDay( 3 );
		$this->assertTrue( is_array( $day3 ) );
		$this->assertEquals( 0, count( $day3 ) );

		$days12 = $set->getPeriodsByDay( array(1,2) );
		$this->assertEquals( 3, count( $days12 ) );
	}

	public function testGetPeriodsGroupedByDay () {
    $post = $this->createPost(array('ID' => 64));

    $this->setUpSetData(64, array(
      array('weekday' => 0, 'timeStart' => '13:00', 'timeEnd' => '17:00'),
      array('weekday' => 2, 'timeStart' => '13:00', 'timeEnd' => '17:00'),
      array('weekday' => 2, 'timeStart' => '20:00', 'timeEnd' => '22:00'),
      array('weekday' => 3, 'timeStart' => '13:00', 'timeEnd' => '17:00'),
      array('weekday' => 5, 'timeStart' => '13:00', 'timeEnd' => '17:00'),
      array('weekday' => 4, 'timeStart' => '13:00', 'timeEnd' => '17:00'),
    ));

    $this->commonSetMocks();

		$set = new Set( $post );
		$periods = $set->getPeriodsGroupedByDay();

		for ( $i = 0; $i < 7; $i++ ) {
			$this->assertArrayHasKey( $i, $periods );
		}

		$this->assertEquals( 1, count( $periods[0] ) );
		$this->assertEquals( 0, count( $periods[1] ) );
		$this->assertEquals( 2, count( $periods[2] ) );
		$this->assertEquals( 1, count( $periods[3] ) );
		$this->assertEquals( 1, count( $periods[4] ) );
		$this->assertEquals( 1, count( $periods[5] ) );
		$this->assertEquals( 0, count( $periods[6] ) );
	}

	public function testGetPeriodsGroupedByDayCompressed () {
	  $post = $this->createPost(array('ID' => 64));

    $this->setUpSetData(64, array(
      array('weekday' => 0, 'timeStart' => '08:00', 'timeEnd' => '12:00'),
      array('weekday' => 1, 'timeStart' => '09:00', 'timeEnd' => '10:00'),
      array('weekday' => 1, 'timeStart' => '13:00', 'timeEnd' => '14:00'),
      array('weekday' => 4, 'timeStart' => '13:00', 'timeEnd' => '14:00'),
      array('weekday' => 6, 'timeStart' => '13:00', 'timeEnd' => '14:00'),
    ));

    $this->commonSetMocks();

		$set = new Set( $post );
		$periods = $set->getPeriodsGroupedByDayCompressed();

		$this->assertEquals( 4, count( $periods ) );
		$this->assertArrayHasKey( '0', $periods );
		$this->assertArrayHasKey( '1', $periods );
		$this->assertArrayHasKey( '4,6', $periods );
		$this->assertArrayHasKey( '2,3,5', $periods );

		$this->assertEquals( 1, count( $periods['0'] ) );
		$this->assertEquals( 2, count( $periods['1'] ) );
		$this->assertEquals( 1, count( $periods['4,6'] ) );
		$this->assertEquals( 0, count( $periods['2,3,5'] ) );
	}

	public function testIsOpenOpeningHours () {
    $post = $this->createPost(array('ID' => 64));

    $this->setUpSetData(64, array(
      array('weekday' => 1, 'timeStart' => '13:00', 'timeEnd' => '17:00'),
      array('weekday' => 1, 'timeStart' => '18:00', 'timeEnd' => '22:00')
    ));

    $this->commonSetMocks();

		$set = new Set( $post );
		$this->assertFalse( $set->isOpenOpeningHours( new DateTime('2016-01-12 12:59') ) );
		$this->assertTrue( $set->isOpenOpeningHours( new DateTime('2016-01-12 13:00') ) );
		$this->assertTrue( $set->isOpenOpeningHours( new DateTime('2016-01-12 16:00') ) );
		$this->assertTrue( $set->isOpenOpeningHours( new DateTime('2016-01-12 17:00') ) );
		$this->assertFalse( $set->isOpenOpeningHours( new DateTime('2016-01-12 17:01') ) );

		$this->assertFalse( $set->isOpenOpeningHours( new DateTime('2016-01-12 17:59') ) );
		$this->assertTrue( $set->isOpenOpeningHours( new DateTime('2016-01-12 18:00') ) );
		$this->assertTrue( $set->isOpenOpeningHours( new DateTime('2016-01-12 20:00') ) );
		$this->assertTrue( $set->isOpenOpeningHours( new DateTime('2016-01-12 22:00') ) );
		$this->assertFalse( $set->isOpenOpeningHours( new DateTime('2016-01-12 22:01') ) );
	}

	public function XtestGetActiveHoliday () {
    $post = $this->createPost(array('ID' => 64));

    $this->setUpSetData(64, array(), array(
      array('name' => 'Holiday 1', 'dateStart' => '2016-01-12', 'dateEnd' => '2016-01-14')
    ));

    $this->commonSetMocks();

		$set = new Set( $post );
		$this->assertNull( $set->getActiveHoliday( new DateTime('2016-01-11 23:59') ) );
		$this->assertEquals( 'Holiday 1', $set->getActiveHoliday( new DateTime('2016-01-12') )->getName() );
		$this->assertEquals( 'Holiday 1', $set->getActiveHoliday( new DateTime('2016-01-13') )->getName() );
		$this->assertEquals( 'Holiday 1', $set->getActiveHoliday( new DateTime('2016-01-14 23:59') )->getName() );
		$this->assertNull( $set->getActiveHoliday( new DateTime('2016-01-15 00:01') ) );
	}

	public function testIsHolidayActive () {
    $post = $this->createPost(array('ID' => 64));

    $this->setUpSetData(64, array(), array(
      array('name' => 'Holiday 1', 'dateStart' => '2016-01-12', 'dateEnd' => '2016-01-14')
    ));

    $this->commonSetMocks();

		$set = new Set( $post );
		$this->assertFalse( $set->isHolidayActive( new DateTime('2016-01-11 23:59') ) );
		$this->assertTrue( $set->isHolidayActive( new DateTime('2016-01-12') ) );
		$this->assertTrue( $set->isHolidayActive( new DateTime('2016-01-13') ) );
		$this->assertTrue( $set->isHolidayActive( new DateTime('2016-01-14 23:59') ) );
		$this->assertFalse( $set->isHolidayActive( new DateTime('2016-01-15 00:01') ) );
	}

	public function testGetActiveHolidayOnWeekday () {
    $post = $this->createPost(array('ID' => 64));

    $this->setUpSetData(64, array(), array(
      array('name' => 'Holiday 1', 'dateStart' => '2016-01-12', 'dateEnd' => '2016-01-14'), // Tue - Thu
      array('name' => 'Holiday 2', 'dateStart' => '2016-01-16', 'dateEnd' => '2016-01-17') // Sat - Sun
    ));

    $this->commonSetMocks();

    $set = new Set( $post );
		$date = new DateTime('2016-01-11');

		$this->assertNull( $set->getActiveHolidayOnWeekday( 0, $date ) );
		$this->assertEquals( 'Holiday 1', $set->getActiveHolidayOnWeekday( 1, $date )->getName() );
		$this->assertEquals( 'Holiday 1', $set->getActiveHolidayOnWeekday( 2, $date )->getName() );
		$this->assertEquals( 'Holiday 1', $set->getActiveHolidayOnWeekday( 3, $date )->getName() );
		$this->assertNull( $set->getActiveHolidayOnWeekday( 4, $date ) );
		$this->assertEquals( 'Holiday 2', $set->getActiveHolidayOnWeekday( 5, $date )->getName() );
		$this->assertEquals( 'Holiday 2', $set->getActiveHolidayOnWeekday( 6, $date )->getName() );
	}

	public function testDaysEqual () {
    $post = $this->createPost(array('ID' => 64));

    $this->setUpSetData(64, array(
      array('weekday' => 1, 'timeStart' => '13:00', 'timeEnd' => '19:00'),
      array('weekday' => 1, 'timeStart' => '20:00', 'timeEnd' => '22:00'),
      array('weekday' => 4, 'timeStart' => '13:00', 'timeEnd' => '19:00'),
      array('weekday' => 4, 'timeStart' => '20:00', 'timeEnd' => '22:00'),
      array('weekday' => 5, 'timeStart' => '13:00', 'timeEnd' => '19:00'),
      array('weekday' => 6, 'timeStart' => '20:00', 'timeEnd' => '22:00')
    ));

    $this->commonSetMocks();

    $set = new Set( $post );
		$this->assertTrue( $set->daysEqual( 1, 4 ) );
		$this->assertTrue( $set->daysEqual( 4, 1 ) );
		$this->assertFalse( $set->daysEqual( 1, 5 ) );
		$this->assertFalse( $set->daysEqual( 5, 1 ) );
		$this->assertFalse( $set->daysEqual( 1, 6 ) );
		$this->assertFalse( $set->daysEqual( 6, 1 ) );
		$this->assertTrue( $set->daysEqual( 6, 6 ) );
	}

	public function testGetActiveIrregularOpening () {
    $post = $this->createPost(array('ID' => 64));

    $this->setUpSetData(64, array(), array(), array(
      array('name' => 'Irregular Opening', 'date' => '2016-01-13', 'timeStart' => '13:00', 'timeEnd' => '17:00')
    ));

    $this->commonSetMocks();

    $set = new Set( $post );
		$this->assertNull( $set->getActiveIrregularOpening( new DateTime('2016-01-12') ) );
		$this->assertNotNull( $set->getActiveIrregularOpening( new DateTime('2016-01-13') ) );
		$this->assertNull( $set->getActiveIrregularOpening( new DateTime('2016-01-14') ) );
	}

	public function testIsIrregularOpeningActive () {
    $post = $this->createPost(array('ID' => 64));

    $this->setUpSetData(64, array(), array(), array(
      array('name' => 'Irregular Opening', 'date' => '2016-01-13', 'timeStart' => '13:00', 'timeEnd' => '17:00')
    ));

    $this->commonSetMocks();

		$set = new Set( $post );
		$this->assertFalse( $set->isIrregularOpeningActive( new DateTime('2016-01-12') ) );
		$this->assertTrue( $set->isIrregularOpeningActive( new DateTime('2016-01-13') ) );
		$this->assertFalse( $set->isIrregularOpeningActive( new DateTime('2016-01-14') ) );
	}

	public function testGetActiveIrregularOpeningOnWeekday () {
    $post = $this->createPost(array('ID' => 64));

    $this->setUpSetData(64, array(), array(), array(
      array('name' => 'Irregular Opening 1', 'date' => '2016-01-13', 'timeStart' => '13:00', 'timeEnd' => '17:00'),
      array('name' => 'Irregular Opening 2', 'date' => '2016-01-18', 'timeStart' => '13:00', 'timeEnd' => '17:00')
    ));

    $this->commonSetMocks();

    $set = new Set( $post );
		$now = new DateTime( '2016-01-12' );

		$io0 = $set->getActiveIrregularOpeningOnWeekday( 0, $now );
		$this->assertNotNull( $io0 );
		$this->assertEquals( 'Irregular Opening 2', $io0->getName() );
		$this->assertNull( $set->getActiveIrregularOpeningOnWeekday( 1, $now ) );
		$io2 = $set->getActiveIrregularOpeningOnWeekday( 2, $now );
		$this->assertNotNull( $io2 );
		$this->assertEquals( 'Irregular Opening 1', $io2->getName() );
		$this->assertNull( $set->getActiveIrregularOpeningOnWeekday( 3, $now ) );
		$this->assertNull( $set->getActiveIrregularOpeningOnWeekday( 4, $now ) );
		$this->assertNull( $set->getActiveIrregularOpeningOnWeekday( 5, $now ) );
		$this->assertNull( $set->getActiveIrregularOpeningOnWeekday( 6, $now ) );
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

    $post = $this->createPost(array('ID' => 64));

    $this->setUpSetData(64, array(
      array('weekday' => 1, 'timeStart' => '13:00', 'timeEnd' => '18:00'),
      array('weekday' => 1, 'timeStart' => '19:00', 'timeEnd' => '21:00'),
      array('weekday' => 1, 'timeStart' => '20:00', 'timeEnd' => '22:00'),
      array('weekday' => 3, 'timeStart' => '13:00', 'timeEnd' => '18:00'),
      array('weekday' => 6, 'timeStart' => '13:00', 'timeEnd' => '03:00')
    ));

    $this->commonSetMocks();

    $set = new Set( $post );

		$this->assertEquals( $periods[0]->getCopyInDateContext( new DateTime('2016-01-26') ), $set->getNextOpenPeriod( new DateTime('2016-01-25 07:00') ) );
		$this->assertEquals( $periods[0]->getCopyInDateContext( new DateTime('2016-01-26') ), $set->getNextOpenPeriod( new DateTime('2016-01-26 12:00') ) );
		$this->assertEquals( $periods[1]->getCopyInDateContext( new DateTime('2016-01-26') ), $set->getNextOpenPeriod( new DateTime('2016-01-26 18:30') ) );
		$this->assertEquals( $periods[2]->getCopyInDateContext( new DateTime('2016-01-26') ), $set->getNextOpenPeriod( new DateTime('2016-01-26 19:30') ) );
		$this->assertEquals( $periods[3]->getCopyInDateContext( new DateTime('2016-01-28') ), $set->getNextOpenPeriod( new DateTime('2016-01-26 22:01') ) );
		$this->assertEquals( $periods[3]->getCopyInDateContext( new DateTime('2016-01-28') ), $set->getNextOpenPeriod( new DateTime('2016-01-28 12:59') ) );
		$this->assertEquals( $periods[4]->getCopyInDateContext( new DateTime('2016-01-31') ), $set->getNextOpenPeriod( new DateTime('2016-01-28 13:00') ) );
		$this->assertEquals( $periods[4]->getCopyInDateContext( new DateTime('2016-01-31') ), $set->getNextOpenPeriod( new DateTime('2016-01-28 18:00') ) );
		$this->assertEquals( $periods[4]->getCopyInDateContext( new DateTime('2016-01-31') ), $set->getNextOpenPeriod( new DateTime('2016-01-31 12:59') ) );
		$this->assertEquals( $periods[0]->getCopyInDateContext( new DateTime('2016-02-01') ), $set->getNextOpenPeriod( new DateTime('2016-01-31 13:00') ) );
	}

	public function testGetNextOpenPeriodHolidays () {
		/** @var Period[] $periods */
		$periods = array(
			new Period( 1, '13:00', '18:00' ),
			new Period( 1, '19:00', '21:00' ),
			new Period( 2, '20:00', '22:00' ),
			new Period( 3, '13:00', '18:00' ),
			new Period( 6, '13:00', '03:00' )
		);

    $post = $this->createPost(array('ID' => 64));

    $this->setUpSetData(64, array(
      array('weekday' => 1, 'timeStart' => '13:00', 'timeEnd' => '18:00'),
      array('weekday' => 1, 'timeStart' => '19:00', 'timeEnd' => '21:00'),
      array('weekday' => 1, 'timeStart' => '20:00', 'timeEnd' => '22:00'),
      array('weekday' => 3, 'timeStart' => '13:00', 'timeEnd' => '18:00'),
      array('weekday' => 6, 'timeStart' => '13:00', 'timeEnd' => '03:00')
    ), array(
      array('name' => 'Test Holiday', 'dateStart' => '2016-01-27', 'dateEnd' => '2016-01-28')
    ));

    $this->commonSetMocks();

    $set  = new Set( $post );
		$this->assertEquals( $periods[0]->getCopyInDateContext( new DateTime( '2016-01-26' ) ), $set->getNextOpenPeriod( new DateTime( '2016-01-25 07:00' ) ) );
		$this->assertEquals( $periods[0]->getCopyInDateContext( new DateTime( '2016-01-26' ) ), $set->getNextOpenPeriod( new DateTime( '2016-01-26 12:00' ) ) );
		$this->assertEquals( $periods[1]->getCopyInDateContext( new DateTime( '2016-01-26' ) ), $set->getNextOpenPeriod( new DateTime( '2016-01-26 18:30' ) ) );
		$this->assertEquals( $periods[4]->getCopyInDateContext( new DateTime( '2016-01-31' ) ), $set->getNextOpenPeriod( new DateTime( '2016-01-26 21:01' ) ) );
	}

	public function testGetNextOpenPeriodIrregularOpenings () {
		/** @var Period[] $periods */
		$periods = array(
			new Period(1, '13:00', '18:00'),
			new Period(1, '19:00', '21:00'),
			new Period(1, '20:00', '22:00'),
			new Period(3, '13:00', '18:00'),
			new Period(6, '13:00', '03:00')
		);

    $post = $this->createPost(array('ID' => 64));

    $this->setUpSetData(64, array(
      array('weekday' => 1, 'timeStart' => '13:00', 'timeEnd' => '18:00'),
      array('weekday' => 1, 'timeStart' => '19:00', 'timeEnd' => '21:00'),
      array('weekday' => 1, 'timeStart' => '20:00', 'timeEnd' => '22:00'),
      array('weekday' => 3, 'timeStart' => '13:00', 'timeEnd' => '18:00'),
      array('weekday' => 6, 'timeStart' => '13:00', 'timeEnd' => '03:00')
    ), array(), array(
      array('name' => 'IO 1', 'date' => '2016-01-26', 'timeStart' => '14:00', 'timeEnd' => '19:30')
    ));

    $this->commonSetMocks();

    $set = new Set( $post );

		$expected = $periods[3]->getCopyInDateContext( new DateTime('2016-01-27') );
		$times = array('12:59', '13:00', '18:00', '18:01', '18:59', '19:00', '21:00', '21:01', '19:59', '20:00', '22:00', '22:01');
		foreach ( $times as $time ) {
			$this->assertEquals( $expected, $set->getNextOpenPeriod( new DateTime('2016-01-26 ' . $time) ) );
		}
		$this->assertEquals( $periods[4]->getCopyInDateContext( new DateTime('2016-01-31') ), $set->getNextOpenPeriod( new DateTime('2016-01-30 15:00') ) );
	}

	public function testIsOpen () {
    $post = $this->createPost(array('ID' => 64));

    $this->setUpSetData(64, array(
      array('weekday' => 1, 'timeStart' => '13:00', 'timeEnd' => '18:00'),
      array('weekday' => 1, 'timeStart' => '19:00', 'timeEnd' => '21:00'),
      array('weekday' => 1, 'timeStart' => '20:00', 'timeEnd' => '22:00'),
      array('weekday' => 3, 'timeStart' => '13:00', 'timeEnd' => '18:00'),
      array('weekday' => 6, 'timeStart' => '13:00', 'timeEnd' => '03:00')
    ), array(
      array('name' => 'Test Holiday', 'dateStart' => '2016-01-25', 'dateEnd' => '2016-01-26')
    ), array(
      array('name' => 'IO', 'date' => '2016-01-28', 'timeStart' => '15:00', 'timeEnd' => '17:00')
    ));

    $this->commonSetMocks();

		$set = new Set( $post );

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