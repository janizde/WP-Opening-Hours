<?php

namespace OpeningHours\Test\Entity;

use DateInterval;
use DateTime;
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