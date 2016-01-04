<?php

namespace OpeningHours\Test\Entity;

use OpeningHours\Entity\Set;
use OpeningHours\Module\CustomPostType\Set as SetPostType;
use OpeningHours\Test\TestScenario;

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

}