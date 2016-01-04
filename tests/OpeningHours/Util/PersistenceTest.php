<?php

namespace OpeningHours\Test\Util;

use DateTime;
use OpeningHours\Entity\Holiday;
use OpeningHours\Entity\IrregularOpening;
use OpeningHours\Entity\Period;
use OpeningHours\Test\TestScenario;
use OpeningHours\Util\Dates;
use OpeningHours\Util\Persistence;

class PersistenceTest extends \WP_UnitTestCase {

	public function testPeriodPersistence () {
		$ts = new TestScenario( $this->factory );
		$post = $ts->setUpBasicSet();
		$persistence = new Persistence( $post );

		$periods = array(
			new Period( 1, '13:00', '17:00' ),
			new Period( 2, '16:30', '19:00' )
		);

		$persistence->savePeriods( $periods );

		// Check meta
		$meta = get_post_meta( $post->ID, Persistence::PERIODS_META_KEY, true );
		$this->assertTrue( is_array( $meta ) );
		$this->assertEquals( 2, count( $meta ) );
		$this->assertTrue( is_array( $meta[0] ) );
		$this->assertTrue( is_array( $meta[1] ) );

		$p1 = $meta[0];
		$p2 = $meta[1];

		$this->assertEquals( 1, $p1['weekday'] );
		$this->assertEquals( '13:00', $p1['timeStart'] );
		$this->assertEquals( '17:00', $p1['timeEnd'] );

		$this->assertEquals( 2, $p2['weekday'] );
		$this->assertEquals( '16:30', $p2['timeStart'] );
		$this->assertEquals( '19:00', $p2['timeEnd'] );

		// Load Periods
		$periods = $persistence->loadPeriods();
		$this->assertTrue( is_array( $periods ) );
		$this->assertEquals( 2, count( $periods ) );
		$p1 = $periods[0];
		$p2 = $periods[1];
		$format = Dates::STD_TIME_FORMAT;

		$this->assertEquals( 1, $p1->getWeekday() );
		$this->assertEquals( '13:00', $p1->getTimeStart()->format($format) );
		$this->assertEquals( '17:00', $p1->getTimeEnd()->format($format) );

		$this->assertEquals( 2, $p2->getWeekday() );
		$this->assertEquals( '16:30', $p2->getTimeStart()->format($format) );
		$this->assertEquals( '19:00', $p2->getTimeEnd()->format($format) );
	}

	public function testHolidayPersistence () {
		$ts = new TestScenario( $this->factory );
		$post = $ts->setUpBasicSet();
		$persistence = new Persistence( $post );

		$holidays = array(
			new Holiday( 'Holiday1', new DateTime('2016-02-03'), new DateTime('2016-02-07') ),
			new Holiday( 'Holiday2', new DateTime('2016-03-03'), new DateTime('2016-03-07') )
		);

		$persistence->saveHolidays( $holidays );

		// Check meta
		$meta = get_post_meta( $post->ID, Persistence::HOLIDAYS_META_KEY, true );
		$this->assertTrue( is_array( $meta ) );
		$this->assertEquals( 2, count( $meta ) );

		$h1 = $meta[0];
		$h2 = $meta[1];

		$this->assertEquals( 'Holiday1', $h1['name'] );
		$this->assertEquals( '2016-02-03', $h1['dateStart'] );
		$this->assertEquals( '2016-02-07', $h1['dateEnd'] );

		$this->assertEquals( 'Holiday2', $h2['name'] );
		$this->assertEquals( '2016-03-03', $h2['dateStart'] );
		$this->assertEquals( '2016-03-07', $h2['dateEnd'] );

		// Load Holidays
		$holidays = $persistence->loadHolidays();
		$this->assertTrue( is_array( $holidays ) );
		$this->assertEquals( 2, count( $holidays ) );
		$h1 = $holidays[0];
		$h2 = $holidays[1];

		$this->assertEquals( 'Holiday1', $h1->getName() );
		$this->assertEquals( new DateTime('2016-02-03'), $h1->getDateStart() );
		$this->assertEquals( new DateTime('2016-02-07 23:59:59'), $h1->getDateEnd() );

		$this->assertEquals( 'Holiday2', $h2->getName() );
		$this->assertEquals( new DateTime('2016-03-03'), $h2->getDateStart() );
		$this->assertEquals( new DateTime('2016-03-07 23:59:59'), $h2->getDateEnd() );
	}

	public function testIrregularOpeningPersistence () {
		$ts = new TestScenario( $this->factory );
		$post = $ts->setUpBasicSet();
		$persistence = new Persistence( $post );

		$ios = array(
			new IrregularOpening( 'IO1', '2016-02-03', '13:00', '17:00' ),
			new IrregularOpening( 'IO2', '2016-03-03', '16:30', '19:00' )
		);

		$persistence->saveIrregularOpenings( $ios );

		// Check meta
		$meta = get_post_meta( $post->ID, Persistence::IRREGULAR_OPENINGS_META_KEY, true );
		$this->assertTrue( is_array( $meta ) );
		$this->assertEquals( 2, count( $meta ) );

		$io1 = $meta[0];
		$io2 = $meta[1];

		$this->assertEquals( 'IO1', $io1['name'] );
		$this->assertEquals( '2016-02-03', $io1['date'] );
		$this->assertEquals( '13:00', $io1['timeStart'] );
		$this->assertEquals( '17:00', $io1['timeEnd'] );

		$this->assertEquals( 'IO2', $io2['name'] );
		$this->assertEquals( '2016-03-03', $io2['date'] );
		$this->assertEquals( '16:30', $io2['timeStart'] );
		$this->assertEquals( '19:00', $io2['timeEnd'] );

		// Load Irregular Openings
		$ios = $persistence->loadIrregularOpenings();
		$this->assertTrue( is_array( $ios ) );
		$this->assertEquals( 2, count( $ios ) );

		$io1 = $ios[0];
		$io2 = $ios[1];

		$this->assertEquals( 'IO1', $io1->getName() );
		$this->assertEquals( new DateTime('2016-02-03'), $io1->getDate() );
		$this->assertEquals( new DateTime('2016-02-03 13:00'), $io1->getTimeStart() );
		$this->assertEquals( new DateTime('2016-02-03 17:00'), $io1->getTimeEnd() );

		$this->assertEquals( 'IO2', $io2->getName() );
		$this->assertEquals( new DateTime('2016-03-03'), $io2->getDate() );
		$this->assertEquals( new DateTime('2016-03-03 16:30'), $io2->getTimeStart() );
		$this->assertEquals( new DateTime('2016-03-03 19:00'), $io2->getTimeEnd() );
	}

}