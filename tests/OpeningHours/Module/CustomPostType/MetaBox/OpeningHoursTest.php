<?php

namespace OpeningHours\Test\Module\CustomPostType\MetaBox;

use OpeningHours\Entity\Period;
use OpeningHours\Module\CustomPostType\MetaBox\OpeningHours;

class OpeningHoursTest extends \WP_UnitTestCase {

	public function test_getPeriodsFromPostDataNoItems () {
		$mb = OpeningHours::getInstance();
		$this->assertEquals( array(), $mb->getPeriodsFromPostData( array() ) );
	}

	public function test_getPeriodsFromPostDataOneItem () {
		$mb = OpeningHours::getInstance();
		$expected = array( new Period(1, '13:00', '18:00') );
		$data = array(
			1 => array(
				'start' => array('13:00'),
				'end' => array('18:00')
			)
		);
		$this->assertEquals( $expected, $mb->getPeriodsFromPostData( $data ) );
	}

	public function test_getPeriodsFromPostDataManyItems () {
		$mb = OpeningHours::getInstance();
		$expected = array(
			new Period(0, '07:00', '18:00'),
			new Period(0, '09:00', '13:00'),
			new Period(6, '08:00', '19:00')
		);
		$data = array(
			0 => array(
				'start' => array('07:00', '09:00'),
				'end' => array('18:00', '13:00')
			),
			6 => array(
				'start' => array('08:00'),
				'end' => array('19:00')
			)
		);

		$this->assertEquals( $expected, $mb->getPeriodsFromPostData( $data ) );
	}

}