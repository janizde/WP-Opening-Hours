<?php

namespace OpeningHours\Test;

use OpeningHours\Entity\Period;
use OpeningHours\Module\CustomPostType\Set;
use OpeningHours\Util\Persistence;
use WP_UnitTest_Factory;

class TestScenario {

	protected $factory;

	public function __construct ( WP_UnitTest_Factory $factory ) {
		$this->factory = $factory;
	}

	public function setUpBasicSet ( array $args = array() ) {
		$args = wp_parse_args( $args, array(
			'post_type' => Set::CPT_SLUG,
			'post_title' => 'Test Set'
		) );

		$post = $this->factory->post->create_and_get( $args );
		return $post;
	}

	public function setUpSetWithData ( array $args = array(), array $periods = array(), array $holidays = array(), array $irregularOpenings = array() ) {
		$post = $this->setUpBasicSet( $args );
		$persistence = new Persistence( $post );

		if ( count( $periods ) > 0 )
			$persistence->savePeriods( $periods );

		if ( count( $holidays ) > 0 )
			$persistence->saveHolidays( $holidays );

		if ( count( $irregularOpenings ) > 0 )
			$persistence->saveIrregularOpenings( $irregularOpenings );

		return $post;
	}
}