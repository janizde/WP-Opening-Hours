<?php

namespace OpeningHours\Test;

use OpeningHours\Entity\Period;
use OpeningHours\Module\CustomPostType\Set;
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

	/**
	 * @param Period[] $periods
	 */
	public function addPeriods ( array $periods ) {
		$config = array();
		foreach ( $periods as $period ) {

		}
	}

}