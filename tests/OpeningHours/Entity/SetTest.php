<?php

namespace OpeningHours\Test\Entity;

use DateInterval;
use DateTime;
use OpeningHours\Entity\Period;
use OpeningHours\Entity\Set;
use OpeningHours\Module\CustomPostType\MetaBox\SetDetails;
use OpeningHours\Test\OpeningHoursTestCase;
use OpeningHours\Util\Dates;
use OpeningHours\Util\Weekday;
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

    if ($dateStartOffset !== null) {
      $interval = new DateInterval('P'.abs($dateStartOffset).'D');
      if ($dateStartOffset < 0)
        $interval->invert = true;

      $now = new DateTime();
      \WP_Mock::wpFunction('get_post_meta', array(
        'times' => 1,
        'args' => array($postId, $setDetails->generateMetaKey('dateStart'), true),
        'return' => $now->add($interval)->format(Dates::STD_DATE_FORMAT)
      ));
    }

    if ($dateEndOffset !== null) {
      $interval = new DateInterval('P'.abs($dateEndOffset).'D');
      if ($dateEndOffset < 0)
        $interval->invert = true;

      $now = new DateTime();
      \WP_Mock::wpFunction('get_post_meta', array(
        'args' => array($postId, $setDetails->generateMetaKey('dateEnd'), true),
        'return' => $now->add($interval)->format(Dates::STD_DATE_FORMAT)
      ));
    }

    if ($weekSchemeMatches !== null) {
      if ($weekSchemeMatches === 'all') {
        $weekScheme = 'all';
      } else {
        $now = new DateTime();
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

	public function testGetPeriodsGroupedByDayWithDummyNoPeriods () {
		$post = $this->createPost(array('ID' => 64));
    $this->commonSetMocks();

		$set = new Set( $post );
    $periods = $set->getPeriodsGroupedByDayWithDummy();

    $this->assertEquals(7, count($periods));
    foreach ($periods as $day) {
      $this->assertEquals(1, count($day['days']));
      $this->assertEquals(1, count($day['periods']));
      /** @var Period $period */
      $period = $day['periods'][0];
      /** @var Weekday $weekday */
      $weekday = $day['days'][0];
      $this->assertEquals($weekday->getIndex(), $period->getWeekday());
      $this->assertEquals('00:00', $period->getTimeStart()->format('H:i'));
      $this->assertEquals('00:00', $period->getTimeEnd()->format('H:i'));
    }
	}

	public function testGetPeriodsGroupedByDayWithDummyHasPeriods () {
		$post = $this->createPost(array('ID' => 64));

    $this->setUpSetData(64, array(
      array('weekday' => 1, 'timeStart' => '13:00', 'timeEnd' => '17:00'),
      array('weekday' => 1, 'timeStart' => '18:00', 'timeEnd' => '21:00'),
      array('weekday' => 2, 'timeStart' => '13:00', 'timeEnd' => '17:00')
    ));

    $this->commonSetMocks();

		$set = new Set( $post );
    $periods = $set->getPeriodsGroupedByDayWithDummy();
    $this->assertEquals(7, count($periods));

    $this->assertEquals(1, count($periods[0]['periods']));
    $this->assertEquals(2, count($periods[1]['periods']));
    $this->assertEquals(1, count($periods[2]['periods']));
    $this->assertEquals(1, count($periods[3]['periods']));
    $this->assertEquals(1, count($periods[4]['periods']));
    $this->assertEquals(1, count($periods[5]['periods']));
    $this->assertEquals(1, count($periods[6]['periods']));
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

    Dates::setStartOfWeek(1);

		$set = new Set( $post );
		$periods = $set->getPeriodsGroupedByDay();

    $this->assertEquals(7, count($periods));
    for ($i = 0; $i < 7; ++$i) {
      $this->assertArrayHasKey('days', $periods[$i]);
      $this->assertEquals(1, count($periods[$i]['days']));
      $this->assertArrayHasKey('periods', $periods[$i]);
    }

    $this->assertEquals(1, $periods[0]['days'][0]->getIndex());
    $this->assertEquals(2, $periods[1]['days'][0]->getIndex());
    $this->assertEquals(3, $periods[2]['days'][0]->getIndex());
    $this->assertEquals(4, $periods[3]['days'][0]->getIndex());
    $this->assertEquals(5, $periods[4]['days'][0]->getIndex());
    $this->assertEquals(6, $periods[5]['days'][0]->getIndex());
    $this->assertEquals(0, $periods[6]['days'][0]->getIndex());

    $this->assertEquals(1, count($periods[6]['periods']));
    $this->assertEquals(0, count($periods[0]['periods']));
    $this->assertEquals(2, count($periods[1]['periods']));
    $this->assertEquals(1, count($periods[2]['periods']));
    $this->assertEquals(1, count($periods[3]['periods']));
    $this->assertEquals(1, count($periods[4]['periods']));
    $this->assertEquals(0, count($periods[5]['periods']));
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

    $this->assertEquals(1, count($periods[0]['days']));
    $this->assertEquals(1, $periods[0]['days'][0]->getIndex());
    $this->assertEquals(2, count($periods[0]['periods']));

    $this->assertEquals(3, count($periods[1]['days']));
    $this->assertEquals(2, $periods[1]['days'][0]->getIndex());
    $this->assertEquals(3, $periods[1]['days'][1]->getIndex());
    $this->assertEquals(5, $periods[1]['days'][2]->getIndex());
    $this->assertEquals(0, count($periods[1]['periods']));

    $this->assertEquals(2, count($periods[2]['days']));
    $this->assertEquals(4, $periods[2]['days'][0]->getIndex());
    $this->assertEquals(6, $periods[2]['days'][1]->getIndex());
    $this->assertEquals(1, count($periods[2]['periods']));

    $this->assertEquals(1, count($periods[3]['days']));
    $this->assertEquals(0, $periods[3]['days'][0]->getIndex());
    $this->assertEquals(1, count($periods[3]['periods']));
	}

	public function testIsOpenOpeningHours () {
    $post = $this->createPost(array('ID' => 64));

    $this->setUpSetData(64, array(
      array('weekday' => 2, 'timeStart' => '13:00', 'timeEnd' => '17:00'),
      array('weekday' => 2, 'timeStart' => '18:00', 'timeEnd' => '22:00')
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

		$this->assertNull( $set->getActiveHolidayOnWeekday( 1, $date ) );
		$this->assertEquals( 'Holiday 1', $set->getActiveHolidayOnWeekday( 2, $date )->getName() );
		$this->assertEquals( 'Holiday 1', $set->getActiveHolidayOnWeekday( 3, $date )->getName() );
		$this->assertEquals( 'Holiday 1', $set->getActiveHolidayOnWeekday( 4, $date )->getName() );
		$this->assertNull( $set->getActiveHolidayOnWeekday( 5, $date ) );
		$this->assertEquals( 'Holiday 2', $set->getActiveHolidayOnWeekday( 6, $date )->getName() );
		$this->assertEquals( 'Holiday 2', $set->getActiveHolidayOnWeekday( 0, $date )->getName() );
	}

	public function testPeriodsEqual () {
	  $post = $this->createPost(array('ID' => 64));
    $this->commonSetMocks();
    $set = new Set($post);

    $p1 = new Period(1, '13:00', '14:00');
    $p2 = new Period(2, '13:00', '14:00');
    $p3 = new Period(1, '12:00', '14:00');

    $this->assertTrue($set->periodsEqual(array(), array()));
    $this->assertTrue($set->periodsEqual(array($p1), array($p2)));
    $this->assertTrue($set->periodsEqual(array($p1, $p2), array($p2, $p1)));
    $this->assertFalse($set->periodsEqual(array($p1), array($p1, $p2)));
    $this->assertFalse($set->periodsEqual(array($p1), array($p3)));
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

		$io0 = $set->getActiveIrregularOpeningOnWeekday( 1, $now );
		$this->assertNotNull( $io0 );
		$this->assertEquals( 'Irregular Opening 2', $io0->getName() );
		$this->assertNull( $set->getActiveIrregularOpeningOnWeekday( 2, $now ) );
		$io2 = $set->getActiveIrregularOpeningOnWeekday( 3, $now );
		$this->assertNotNull( $io2 );
		$this->assertEquals( 'Irregular Opening 1', $io2->getName() );
		$this->assertNull( $set->getActiveIrregularOpeningOnWeekday( 4, $now ) );
		$this->assertNull( $set->getActiveIrregularOpeningOnWeekday( 5, $now ) );
		$this->assertNull( $set->getActiveIrregularOpeningOnWeekday( 6, $now ) );
		$this->assertNull( $set->getActiveIrregularOpeningOnWeekday( 0, $now ) );
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

    $this->assertEquals($periods[0]->getCopyInDateContext(new DateTime('2016-01-25')), $set->getNextOpenPeriod(new DateTime('2016-01-25 12:59')));
    $this->assertEquals($periods[1]->getCopyInDateContext(new DateTime('2016-01-25')), $set->getNextOpenPeriod(new DateTime('2016-01-25 13:00')));
    $this->assertEquals($periods[1]->getCopyInDateContext(new DateTime('2016-01-25')), $set->getNextOpenPeriod(new DateTime('2016-01-25 18:00')));
    $this->assertEquals($periods[1]->getCopyInDateContext(new DateTime('2016-01-25')), $set->getNextOpenPeriod(new DateTime('2016-01-25 18:01')));
    $this->assertEquals($periods[1]->getCopyInDateContext(new DateTime('2016-01-25')), $set->getNextOpenPeriod(new DateTime('2016-01-25 18:59')));
    $this->assertEquals($periods[2]->getCopyInDateContext(new DateTime('2016-01-25')), $set->getNextOpenPeriod(new DateTime('2016-01-25 19:00')));
    $this->assertEquals($periods[2]->getCopyInDateContext(new DateTime('2016-01-25')), $set->getNextOpenPeriod(new DateTime('2016-01-25 19:59')));
    $this->assertEquals($periods[3]->getCopyInDateContext(new DateTime('2016-01-27')), $set->getNextOpenPeriod(new DateTime('2016-01-25 20:00')));
    $this->assertEquals($periods[3]->getCopyInDateContext(new DateTime('2016-01-27')), $set->getNextOpenPeriod(new DateTime('2016-01-27 12:59')));
    $this->assertEquals($periods[4]->getCopyInDateContext(new DateTime('2016-01-30')), $set->getNextOpenPeriod(new DateTime('2016-01-27 13:00')));
    $this->assertEquals($periods[4]->getCopyInDateContext(new DateTime('2016-01-30')), $set->getNextOpenPeriod(new DateTime('2016-01-30 12:59')));
    $this->assertEquals($periods[0]->getCopyInDateContext(new DateTime('2016-01-31')), $set->getNextOpenPeriod(new DateTime('2016-01-30 13:00')));
    $this->assertEquals($periods[0]->getCopyInDateContext(new DateTime('2016-01-31')), $set->getNextOpenPeriod(new DateTime('2016-01-31 03:00')));
    $this->assertEquals($periods[0]->getCopyInDateContext(new DateTime('2016-01-31')), $set->getNextOpenPeriod(new DateTime('2016-01-31 03:01')));
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

    $set = new Set( $post );

    $this->assertEquals($periods[4]->getCopyInDateContext(new DateTime('2016-01-30')), $set->getNextOpenPeriod(new DateTime('2016-01-27 12:59')));
    $this->assertEquals($periods[4]->getCopyInDateContext(new DateTime('2016-01-30')), $set->getNextOpenPeriod(new DateTime('2016-01-27 13:00')));
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
      array('name' => 'IO1', 'date' => '2016-01-25', 'timeStart' => '14:00', 'timeEnd' => '19:30')
    ));

    $this->commonSetMocks();

    $set = new Set( $post );

    $this->assertEquals($periods[3]->getCopyInDateContext(new DateTime('2016-01-27')), $set->getNextOpenPeriod(new DateTime('2016-01-25 12:59')));
    $this->assertEquals($periods[3]->getCopyInDateContext(new DateTime('2016-01-27')), $set->getNextOpenPeriod(new DateTime('2016-01-25 13:00')));
  }

	public function testIsOpen () {
    $post = $this->createPost(array('ID' => 64));

    $this->setUpSetData(64, array(
      array('weekday' => 2, 'timeStart' => '13:00', 'timeEnd' => '18:00'),
      array('weekday' => 2, 'timeStart' => '19:00', 'timeEnd' => '21:00'),
      array('weekday' => 2, 'timeStart' => '20:00', 'timeEnd' => '22:00'),
      array('weekday' => 4, 'timeStart' => '13:00', 'timeEnd' => '18:00'),
      array('weekday' => 0, 'timeStart' => '13:00', 'timeEnd' => '03:00')
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