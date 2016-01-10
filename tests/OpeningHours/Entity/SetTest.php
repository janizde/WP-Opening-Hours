<?php

namespace OpeningHours\Test\Entity;

use DateInterval;
use DateTime;
use OpeningHours\Entity\Holiday;
use OpeningHours\Entity\IrregularOpening;
use OpeningHours\Entity\Period;
use OpeningHours\Entity\Set;
use OpeningHours\Module\CustomPostType\Set as SetPostType;
use OpeningHours\Test\TestScenario;
use OpeningHours\Util\Dates;
use OpeningHours\Util\Persistence;
use WP_Post;

class SetTest extends \WP_UnitTestCase {

	public function testConstructNoPeriods () {
		$ts = new TestScenario( $this->factory );
		$post = $ts->setUpBasicSet();
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

	public function testConstructWithDescription () {
		$ts = new TestScenario( $this->factory );
		$post = $ts->setUpBasicSet();
		add_post_meta( $post->ID, get_meta_key( 'description', SetPostType::CPT_SLUG ), 'Test Description' );
		$set = new Set( $post );

		$this->assertEquals( 'Test Description', $set->getDescription() );
	}

	public function testConstructLoadsPeriods () {
		$ts = new TestScenario( $this->factory );
		$post = $ts->setUpBasicSet();
		$persistence = new Persistence( $post );
		$persistence->savePeriods( array(
			new Period( 2, '13:00', '17:00' ),
			new Period( 1, '13:00', '17:00' )
		) );

		$set = new Set( $post );
		$periods = $set->getPeriods();
		$this->assertEquals( 2, count( $periods ) );
		$this->assertEquals( 2, $periods->offsetGet( 0 )->getWeekday() );
		$this->assertEquals( 1, $periods->offsetGet( 1 )->getWeekday() );
	}

	public function testConstructChildSet () {
		$ts = new TestScenario( $this->factory );
		$post = $ts->setUpBasicSet();
		$parentSet = new Set( $post );
		$this->assertTrue( $parentSet->isParent() );

		$childPost = $this->setUpSetWithCriteria( -1, 1, true, array(
			'post_parent' => $post->ID
		) );

		$childSet = new Set( $post );
		$this->assertEquals( $post->ID, $childSet->getParentId() );
		$this->assertEquals( $post, $childSet->getParentPost() );
		$this->assertEquals( $childPost->ID, $childSet->getId() );
		$this->assertEquals( $childPost, $childSet->getPost() );
	}

	public function testPostMatchesCriteriaNotingSet () {
		$post = $this->setUpSetWithCriteria();
		$this->assertFalse( Set::postMatchesCriteria( $post ) );
	}

	public function testPostMatchesCriteriaDateStart () {
		$this->assertTrue( Set::postMatchesCriteria( $this->setUpSetWithCriteria( -1 ) ) );
		$this->assertTrue( Set::postMatchesCriteria( $this->setUpSetWithCriteria( 0 ) ) );
		$this->assertFalse( Set::postMatchesCriteria( $this->setUpSetWithCriteria( 1 ) ) );
	}

	public function testPostMatchesCriteriaDateEnd () {
		$this->assertTrue( Set::postMatchesCriteria( $this->setUpSetWithCriteria( null, 1 ) ) );
		$this->assertTrue( Set::postMatchesCriteria( $this->setUpSetWithCriteria( null, 0 ) ) );
		$this->assertFalse( Set::postMatchesCriteria( $this->setUpSetWithCriteria( null, -1 ) ) );
	}

	public function testPostMatchesCriteriaWeekScheme () {
		$this->assertTrue( Set::postMatchesCriteria( $this->setUpSetWithCriteria( null, null, true ) ) );
		$this->assertFalse( Set::postMatchesCriteria( $this->setUpSetWithCriteria( null, null, false ) ) );
	}

	public function testPostMatchesCriteriaCombined () {
		$this->assertTrue( Set::postMatchesCriteria( $this->setUpSetWithCriteria( -1, 1 ) ) );
		$this->assertFalse( Set::postMatchesCriteria( $this->setUpSetWithCriteria( 1, 1 ) ) );
		$this->assertTrue( Set::postMatchesCriteria( $this->setUpSetWithCriteria( -1, 1 ) ) );
		$this->assertFalse( Set::postMatchesCriteria( $this->setUpSetWithCriteria( -1, -1 ) ) );

		$this->assertTrue( Set::postMatchesCriteria( $this->setUpSetWithCriteria( -1, null, true ) ) );
		$this->assertFalse( Set::postMatchesCriteria( $this->setUpSetWithCriteria( -1, null, false ) ) );
		$this->assertFalse( Set::postMatchesCriteria( $this->setUpSetWithCriteria( 1, null, true ) ) );
		$this->assertFalse( Set::postMatchesCriteria( $this->setUpSetWithCriteria( 1, null, false ) ) );

		$this->assertTrue( Set::postMatchesCriteria( $this->setUpSetWithCriteria( null, 1, true ) ) );
		$this->assertFalse( Set::postMatchesCriteria( $this->setUpSetWithCriteria( null, -1, true ) ) );
		$this->assertFalse( Set::postMatchesCriteria( $this->setUpSetWithCriteria( null, 1, false ) ) );
		$this->assertFalse( Set::postMatchesCriteria( $this->setUpSetWithCriteria( null, -1, false ) ) );

		$this->assertTrue( Set::postMatchesCriteria( $this->setUpSetWithCriteria( -1, 1, true ) ) );
		$this->assertFalse( Set::postMatchesCriteria( $this->setUpSetWithCriteria( 1, 1, true ) ) );
		$this->assertFalse( Set::postMatchesCriteria( $this->setUpSetWithCriteria( -1, -1, true ) ) );
		$this->assertFalse( Set::postMatchesCriteria( $this->setUpSetWithCriteria( 1, -1, true ) ) );
		$this->assertFalse( Set::postMatchesCriteria( $this->setUpSetWithCriteria( -1, 1, false ) ) );
		$this->assertFalse( Set::postMatchesCriteria( $this->setUpSetWithCriteria( 1, 1, false ) ) );
		$this->assertFalse( Set::postMatchesCriteria( $this->setUpSetWithCriteria( -1, -1, false ) ) );
		$this->assertFalse( Set::postMatchesCriteria( $this->setUpSetWithCriteria( 1, -1, false ) ) );
	}

	public function testIsParent () {
		$ts = new TestScenario( $this->factory );
		$post = $ts->setUpBasicSet();
		$parentSet = new Set( $post );
		$this->assertTrue( $parentSet->isParent() );

		$this->setUpSetWithCriteria( -1, 1, true, array(
			'post_parent' => $post->ID
		) );

		$childSet = new Set( $post );
		$this->assertFalse( $childSet->isParent() );
	}

	public function testAddDummyPeriodsNoPeriods () {
		$ts = new TestScenario( $this->factory );
		$post = $ts->setUpBasicSet();
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
		$ts = new TestScenario( $this->factory );
		$post = $ts->setUpBasicSet();
		$persistence = new Persistence( $post );
		$persistence->savePeriods( array(
			new Period( 1, '13:00', '17:00' ),
			new Period( 1, '18:00', '21:00' ),
			new Period( 2, '13:00', '17:00' )
		) );

		$set = new Set( $post );
		$set->addDummyPeriods();

		for ( $i = 0; $i < 7; $i++ ) {
			$days = $set->getPeriodsByDay( $i );
			$this->assertEquals( $i == 1 ? 2 : 1, count($days) );
		}
	}

	public function testGetPeriodsByDay () {
		$ts = new TestScenario( $this->factory );
		$post = $ts->setUpBasicSet();
		$persistence = new Persistence( $post );
		$persistence->savePeriods( array(
			new Period( 1, '13:00', '17:00' ),
			new Period( 1, '18:00', '21:00' ),
			new Period( 2, '13:00', '17:00' )
		) );

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
		$ts = new TestScenario( $this->factory );
		$post = $ts->setUpSetWithData( array(), array(
			new Period( 0, '13:00', '17:00' ),
			new Period( 2, '13:00', '17:00' ),
			new Period( 2, '20:00', '22:00' ),
			new Period( 3, '13:00', '17:00' ),
			new Period( 5, '13:00', '17:00' ),
			new Period( 4, '13:00', '17:00' ),
		) );

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
		$ts = new TestScenario( $this->factory );
		$post = $ts->setUpSetWithData( array(), array(
			new Period( 0, '08:00', '12:00' ),
			new Period( 1, '09:00', '10:00' ),
			new Period( 1, '13:00', '14:00' ),
			new Period( 4, '13:00', '14:00' ),
			new Period( 6, '13:00', '14:00' )
		) );

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
		$ts = new TestScenario( $this->factory );
		$post = $ts->setUpSetWithData( array(), array(
			new Period( 1, '13:00', '17:00' ),
			new Period( 1, '18:00', '22:00' )
		) );

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

	public function testGetActiveHoliday () {
		$ts = new TestScenario( $this->factory );
		$post = $ts->setUpSetWithData( array(), array(), array(
			new Holiday('Holiday 1', new DateTime('2016-01-12'), new DateTime('2016-01-14') )
		) );

		$set = new Set( $post );
		$this->assertNull( $set->getActiveHoliday( new DateTime('2016-01-11 23:59') ) );
		$this->assertEquals( 'Holiday 1', $set->getActiveHoliday( new DateTime('2016-01-12') )->getName() );
		$this->assertEquals( 'Holiday 1', $set->getActiveHoliday( new DateTime('2016-01-13') )->getName() );
		$this->assertEquals( 'Holiday 1', $set->getActiveHoliday( new DateTime('2016-01-14 23:59') )->getName() );
		$this->assertNull( $set->getActiveHoliday( new DateTime('2016-01-15 00:01') ) );
	}

	public function testIsHolidayActive () {
		$ts = new TestScenario( $this->factory );
		$post = $ts->setUpSetWithData( array(), array(), array(
			new Holiday('Holiday 1', new DateTime('2016-01-12'), new DateTime('2016-01-14') )
		) );

		$set = new Set( $post );
		$this->assertFalse( $set->isHolidayActive( new DateTime('2016-01-11 23:59') ) );
		$this->assertTrue( $set->isHolidayActive( new DateTime('2016-01-12') ) );
		$this->assertTrue( $set->isHolidayActive( new DateTime('2016-01-13') ) );
		$this->assertTrue( $set->isHolidayActive( new DateTime('2016-01-14 23:59') ) );
		$this->assertFalse( $set->isHolidayActive( new DateTime('2016-01-15 00:01') ) );
	}

	public function testGetActiveHolidayOnWeekday () {
		$ts = new TestScenario( $this->factory );
		$post = $ts->setUpSetWithData( array(), array(), array(
			new Holiday('Holiday 1', new DateTime('2016-01-12'), new DateTime('2016-01-14') ), // Tue - Thu
			new Holiday('Holiday 2', new DateTime('2016-01-16'), new DateTime('2016-01-17') ) // Sat - Sun
		) );

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
		$ts = new TestScenario( $this->factory );
		$post = $ts->setUpSetWithData( array(), array(
			new Period( 1, '13:00', '19:00' ),
			new Period( 1, '20:00', '22:00' ),
			new Period( 4, '13:00', '19:00' ),
			new Period( 4, '20:00', '22:00' ),
			new Period( 5, '13:00', '19:00' ),
			new Period( 6, '20:00', '22:00' )
		) );

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
		$ts = new TestScenario( $this->factory );
		$post = $ts->setUpSetWithData( array(), array(), array(), array(
			new IrregularOpening( 'Irregular Opening', '2016-01-13', '13:00', '17:00' )
		) );

		$set = new Set( $post );
		$this->assertNull( $set->getActiveIrregularOpening( new DateTime('2016-01-12') ) );
		$this->assertNotNull( $set->getActiveIrregularOpening( new DateTime('2016-01-13') ) );
		$this->assertNull( $set->getActiveIrregularOpening( new DateTime('2016-01-14') ) );
	}

	public function testIsIrregularOpeningActive () {
		$ts = new TestScenario( $this->factory );
		$post = $ts->setUpSetWithData( array(), array(), array(), array(
			new IrregularOpening( 'Irregular Opening', '2016-01-13', '13:00', '17:00' )
		) );

		$set = new Set( $post );
		$this->assertFalse( $set->isIrregularOpeningActive( new DateTime('2016-01-12') ) );
		$this->assertTrue( $set->isIrregularOpeningActive( new DateTime('2016-01-13') ) );
		$this->assertFalse( $set->isIrregularOpeningActive( new DateTime('2016-01-14') ) );
	}

	public function testGetActiveIrregularOpeningOnWeekday () {
		$ts = new TestScenario( $this->factory );
		$post = $ts->setUpSetWithData( array(), array(), array(), array(
			new IrregularOpening( 'Irregular Opening 1', '2016-01-13', '13:00', '17:00' ),
			new IrregularOpening( 'Irregular Opening 2', '2016-01-18', '13:00', '17:00' )
		) );

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

	/**
	 * TODO: add test for isOpen
	 * TODO: add test for sortPeriods
	 * TODO: add test for sortHolidays
	 * TODO: add test for sortIrregularOpenings
	 * TODO: add test for getNextOpenPeriod
	 */

	/**
	 * Sets up test set with criteria to test postMatchesCriteria
	 * Any parameter can be null and no meta will be saved
	 *
	 * @param     int       $startOffset  The offset in days from today
	 * @param     int       $endOffset    The offset in days from today
	 * @param     bool      $weekScheme   Whether the week scheme shall match
	 * @param     array     $postArgs     Custom args for the new post
	 * @return    WP_Post                 The post representing the newly created set
	 */
	protected function setUpSetWithCriteria ( $startOffset = null, $endOffset = null, $weekScheme = null, $postArgs = array() ) {
		$ts = new TestScenario( $this->factory );
		$post = $ts->setUpBasicSet( $postArgs );

		if ( $startOffset !== null ) {
			$date     = new DateTime( '00:00' );
			$interval = new DateInterval( 'P' . abs($startOffset) . 'D' );
			if ( $startOffset < 0 )
				$interval->invert = 1;
			$date->add( $interval );
			update_post_meta( $post->ID, get_meta_key( 'date-start', SetPostType::CPT_SLUG ), $date->format( Dates::STD_DATE_FORMAT ) );
		}

		if ( $endOffset !== null ) {
			$date = new DateTime( '23:59:59' );
			$interval = new DateInterval( 'P'.abs($endOffset).'D' );
			if ( $endOffset < 0 )
				$interval->invert = 1;
			$date->add( $interval );
			update_post_meta( $post->ID, get_meta_key( 'date-end', SetPostType::CPT_SLUG ), $date->format( Dates::STD_DATE_FORMAT ) );
		}

		if ( $weekScheme === null ) {
			$wsm = 'all';
		} else {
			$now = new DateTime('now');
			$nowEven = (int) $now->format('W') % 2 === 0;

			if ( !$weekScheme )
				$nowEven = !$nowEven;

			$wsm = $nowEven ? 'even' : 'odd';
		}

		update_post_meta( $post->ID, get_meta_key( 'week-scheme', SetPostType::CPT_SLUG ), $wsm );

		return $post;
	}
}