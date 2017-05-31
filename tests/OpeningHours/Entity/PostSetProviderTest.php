<?php

namespace OpeningHours\Test\Entity;

use OpeningHours\Entity\Holiday;
use OpeningHours\Entity\IrregularOpening;
use OpeningHours\Entity\Period;
use OpeningHours\Entity\PostSetProvider;
use OpeningHours\Module\CustomPostType\MetaBox\SetDetails;
use OpeningHours\Module\CustomPostType\Set;
use OpeningHours\OpeningHours;
use OpeningHours\Test\OpeningHoursTestCase;
use OpeningHours\Util\Dates;

class PostSetProviderTest extends OpeningHoursTestCase {

  public function testGetAvailableSetInfoAdmin () {
    $screen = $this->getMockBuilder('WP_Screen')->getMock();
    $screen->base = 'post';
    $screen->post_type = Set::CPT_SLUG;

    \WP_Mock::wpFunction('get_current_screen', array(
      'return' => $screen
    ));

    $post = $this->getMockBuilder('WP_Post')->getMock();
    $post->ID = 64;
    $post->post_title = 'My Set';

    $childPost = $this->getMockBuilder('WP_Post')->getMock();
    $childPost->ID = 128;
    $childPost->post_title = 'Child Set';

    \WP_Mock::wpFunction('get_posts', array(
      'times' => 1,
      'args' => array(array(
        'post_type' => Set::CPT_SLUG,
        'numberposts' => -1,
        'orderby' => 'menu_order',
        'order' => 'ASC'
      )),
      'return' => array($post, $childPost)
    ));

    \WP_Mock::wpFunction('get_post_meta', array(
      'return' => false
    ));

    $provider = new PostSetProvider();
    $expected = array(
      array(
        'id' => 64,
        'name' => 'My Set'
      ),
      array(
        'id' => 128,
        'name' => 'Child Set'
      )
    );

    $this->assertEquals($expected, $provider->getAvailableSetInfo());
    // Returns cached version and does not call get_posts again (mock constraint)
    $this->assertEquals($expected, $provider->getAvailableSetInfo());
  }

  public function testGetAvailableSetInfo () {
    $screen = $this->getMockBuilder('WP_Screen')->getMock();
    $screen->base = 'post';
    $screen->post_type = 'post';

    \WP_Mock::wpFunction('get_current_screen', array(
      'return' => $screen
    ));

    $post = $this->getMockBuilder('WP_Post')->getMock();
    $post->ID = 64;
    $post->post_title = 'My Set';

    \WP_Mock::wpFunction('get_posts', array(
      'times' => 1,
      'args' => array(array(
        'post_type' => Set::CPT_SLUG,
        'numberposts' => -1,
        'orderby' => 'menu_order',
        'order' => 'ASC',
        'post_parent' => 0
      )),
      'return' => array($post)
    ));

    \WP_Mock::wpFunction('get_post_meta', array(
      'return' => ''
    ));

    $provider = new PostSetProvider();
    $expected = array(
      array(
        'id' => 64,
        'name' => 'My Set'
      )
    );

    $this->assertEquals($expected, $provider->getAvailableSetInfo());
    // Returns cached version and does not call get_posts again (mock constraint)
    $this->assertEquals($expected, $provider->getAvailableSetInfo());
  }

  public function testChildSetCriteriaMatches () {
    $provider = new PostSetProvider();
    $dt = new \DateTime('2016-10-03 13:00:00');

    $this->assertFalse($provider->childSetCriteriaMatches(null, null, 'all'));
    $this->assertTrue($provider->childSetCriteriaMatches(new \DateTime('2016-10-02'), null, 'all', $dt));
    $this->assertTrue($provider->childSetCriteriaMatches(new \DateTime('2016-10-03'), null, 'all', $dt));
    $this->assertFalse($provider->childSetCriteriaMatches(new \DateTime('2016-10-04'), null, 'all', $dt));

    $this->assertTrue($provider->childSetCriteriaMatches(null, new \DateTime('2016-10-04'), 'all', $dt));
    $this->assertTrue($provider->childSetCriteriaMatches(null, new \DateTime('2016-10-03'), 'all', $dt));
    $this->assertFalse($provider->childSetCriteriaMatches(null, new \DateTime('2016-10-02'), 'all', $dt));

    $this->assertTrue($provider->childSetCriteriaMatches(null, null, 'even', $dt));
    $this->assertFalse($provider->childSetCriteriaMatches(null, null, 'odd', $dt));
  }

  public function testCreateSetAdmin () {
    $screen = $this->getMockBuilder('WP_Screen')->getMock();
    $screen->base = 'post';
    $screen->post_type = Set::CPT_SLUG;

    \WP_Mock::wpFunction('get_current_screen', array(
      'return' => $screen
    ));

    $post = $this->getMockBuilder('WP_Post')->getMock();
    $post->ID = 64;
    $post->post_title = 'My Set';

    \WP_Mock::wpFunction('get_post', array(
      'times' => 1,
      'args' => array(64),
      'return' => $post
    ));

    \WP_Mock::wpFunction('get_post_meta', array(
      'times' => 1,
      'args' => array(64, '_op_set_periods', true),
      'return' => array(
        array('weekday' => 1, 'timeStart' => '13:00', 'timeEnd' => '14:00')
      )
    ));

    \WP_Mock::wpFunction('get_post_meta', array(
      'times' => 1,
      'args' => array(64, '_op_set_holidays', true),
      'return' => array(
        array('name' => 'Holiday', 'dateStart' => '2016-10-02', 'dateEnd' => '2016-10-03')
      )
    ));

    \WP_Mock::wpFunction('get_post_meta', array(
      'times' => 1,
      'args' => array(64, '_op_set_irregular_openings', true),
      'return' => array(
        array('name' => 'Irregular Opening', 'date' => '2016-10-03', 'timeStart' => '13:00', 'timeEnd' => '14:00')
      )
    ));

    \WP_Mock::wpFunction('get_post_meta', array(
      'times' => 1,
      'args' => array(64, '_op_meta_box_set_details_description', true),
      'return' => 'Set Description'
    ));

    $provider = new PostSetProvider();
    $set = $provider->createSet(64);

    $this->assertNotNull($set);
    $this->assertEquals(64, $set->getId());
    $this->assertEquals('My Set', $set->getName());

    $this->assertEquals(1, $set->getPeriods()->count());
    $this->assertEquals(1, $set->getHolidays()->count());
    $this->assertEquals(1, $set->getIrregularOpenings()->count());

    $this->assertEquals(new Period(1, '13:00', '14:00'), $set->getPeriods()->offsetGet(0));
    $this->assertEquals(new Holiday('Holiday', new \DateTime('2016-10-02'), new \DateTime('2016-10-03')), $set->getHolidays()->offsetGet(0));
    $this->assertEquals(new IrregularOpening('Irregular Opening', '2016-10-03', '13:00', '14:00'), $set->getIrregularOpenings()->offsetGet(0));
  }

  public function testCreateChildSet() {
    $parentPost = $this->getMockBuilder('WP_Post')->getMock();
    $parentPost->ID = 64;
    $parentPost->post_title = 'Parent';

    $childPost = $this->getMockBuilder('WP_Post')->getMock();
    $childPost->ID = 128;
    $childPost->post_parent = 64;
    $childPost->post_title = 'Child';

    // get_post
    \WP_Mock::wpFunction('get_post', array(
      'args' => array(64),
      'return' => $parentPost,
    ));

    \WP_Mock::wpFunction('get_post', array(
      'args' => array(128),
      'return' => $childPost,
    ));

    \WP_Mock::wpFunction('get_posts', array(
      'args' => array(array(
        'post_type' => Set::CPT_SLUG,
        'numberposts' => -1,
        'orderby' => 'menu_order',
        'order' => 'ASC',
        'post_parent' => 64,
      )),
      'return' => array($childPost)
    ));

    \WP_Mock::wpFunction('get_posts', array(
      'args' => array(array(
        'post_type' => Set::CPT_SLUG,
        'numberposts' => -1,
        'orderby' => 'menu_order',
        'order' => 'ASC',
        'post_parent' => 128,
      )),
      'return' => array()
    ));

    // Parent post meta
    \WP_Mock::wpFunction('get_post_meta', array(
      'args' => array(64, '_op_set_periods', true),
      'return' => array(
        array('weekday' => 1, 'timeStart' => '13:00', 'timeEnd' => '14:00')
      )
    ));

    \WP_Mock::wpFunction('get_post_meta', array(
      'args' => array(64, '_op_set_holidays', true),
      'return' => array(
        array('name' => 'Holiday', 'dateStart' => '2016-10-02', 'dateEnd' => '2016-10-03')
      )
    ));

    \WP_Mock::wpFunction('get_post_meta', array(
      'args' => array(64, '_op_set_irregular_openings', true),
      'return' => array(
        array('name' => 'Irregular Opening', 'date' => '2016-10-03', 'timeStart' => '13:00', 'timeEnd' => '14:00')
      )
    ));

    \WP_Mock::wpFunction('get_post_meta', array(
      'args' => array(64, '_op_meta_box_set_details_description', true),
      'return' => 'Set Description'
    ));

    // Child post meta
    \WP_Mock::wpFunction('get_post_meta', array(
      'args' => array(128, '_op_set_periods', true),
      'return' => array(
        array('weekday' => 2, 'timeStart' => '15:00', 'timeEnd' => '18:00')
      )
    ));

    \WP_Mock::wpFunction('get_post_meta', array(
      'args' => array(128, '_op_meta_box_set_details_description', true),
      'return' => 'Set Description'
    ));

    $details = SetDetails::getInstance();
    $now = Dates::getNow();
    $childStart = clone $now;
    $childStart->sub(new \DateInterval('P7D'));
    $childEnd = clone $now;
    $childEnd->add(new \DateInterval('P7D'));

    \WP_Mock::wpFunction('get_post_meta', array(
      'args' => array(128, $details->getPersistence()->generateMetaKey('dateStart'), true),
      'return' => $childStart->format(Dates::STD_DATE_FORMAT),
    ));

    \WP_Mock::wpFunction('get_post_meta', array(
      'args' => array(128, $details->getPersistence()->generateMetaKey('dateEnd'), true),
      'return' => $childEnd->format(Dates::STD_DATE_FORMAT)
    ));

    \WP_Mock::wpFunction('get_post_meta', array(
      'args' => array(128, $details->getPersistence()->generateMetaKey('weekScheme'), true),
      'return' => 'all'
    ));

    // Create set
    $provider = new PostSetProvider();
    $set = $provider->createSet(64);

    $self = $this;
    $makeAssertions = function (\OpeningHours\Entity\Set $set) use ($self) {
      $self->assertNotNull($set);
      $self->assertEquals(64, $set->getId());
      $self->assertEquals('Parent', $set->getName());

      $self->assertEquals(1, $set->getPeriods()->count());
      $self->assertEquals(1, $set->getHolidays()->count());
      $self->assertEquals(1, $set->getIrregularOpenings()->count());

      $self->assertEquals(new Period(2, '15:00', '18:00'), $set->getPeriods()->offsetGet(0));
      $self->assertEquals(new Holiday('Holiday', new \DateTime('2016-10-02'), new \DateTime('2016-10-03')), $set->getHolidays()->offsetGet(0));
      $self->assertEquals(new IrregularOpening('Irregular Opening', '2016-10-03', '13:00', '14:00'), $set->getIrregularOpenings()->offsetGet(0));
    };

    $makeAssertions($set);

    // Alias
    \WP_Mock::wpFunction('get_posts', array(
      'args' => array(array(
        'post_type' => Set::CPT_SLUG,
        'numberposts' => -1,
        'meta_key' => $details->getPersistence()->generateMetaKey('alias'),
        'meta_value' => 'parent-set-alias',
      )),
      'return' => array($parentPost),
    ));

    $aliasedSet = $provider->createSet('parent-set-alias');

    $makeAssertions($aliasedSet);
  }

  public function testSetAlias () {
    $screen = $this->getMockBuilder('WP_Screen')->getMock();
    $screen->base = 'post';
    $screen->post_type = 'post';

    \WP_Mock::wpFunction('get_current_screen', array(
      'return' => $screen
    ));

    $post64 = $this->getMockBuilder('WP_Post')->getMock();
    $post64->ID = 64;
    $post64->post_title = 'Post 64';

    $post128 = $this->getMockBuilder('WP_Post')->getMock();
    $post128->ID = 128;
    $post128->post_title = 'Post 128';

    \WP_Mock::wpFunction('get_posts', array(
      'return' => array($post64, $post128)
    ));

    \WP_Mock::wpFunction('get_post_meta', array(
      'times' => 1,
      'args' => array(64, '_op_meta_box_set_details_alias', true),
      'return' => ''
    ));

    \WP_Mock::wpFunction('get_post_meta', array(
      'times' => 1,
      'args' => array(128, '_op_meta_box_set_details_alias', true),
      'return' => 'custom-set'
    ));

    $setProvider = new PostSetProvider();

    $setInfo = $setProvider->getAvailableSetInfo();
    $this->assertEquals(array(
      array(
        'id' => 64,
        'name' => 'Post 64'
      ),
      array(
        'id' => 128,
        'name' => 'Post 128'
      ),
      array(
        'id' => 'custom-set',
        'name' => 'Post 128',
        'hidden' => true
      )
    ), $setInfo);
  }

  public function testSetAliasCreateSet () {
    $screen = $this->getMockBuilder('WP_Screen')->getMock();
    $screen->base = 'post';
    $screen->post_type = 'post';

    \WP_Mock::wpFunction('get_current_screen', array(
      'return' => $screen
    ));

    $post = $this->getMockBuilder('WP_Post')->getMock();
    $post->ID = 64;
    $post->post_title = 'Post 64';

    \WP_Mock::wpFunction('get_posts', array(
      'times' => 1,
      'args' => array(array(
        'post_type' => Set::CPT_SLUG,
        'numberposts' => -1,
        'meta_key' => '_op_meta_box_set_details_alias',
        'meta_value' => 'custom-set'
      )),
      'return' => array($post)
    ));

    \WP_Mock::wpFunction('get_posts', array(
      'times' => 1,
      'args' => array(array(
        'post_type' => Set::CPT_SLUG,
        'numberposts' => -1,
        'orderby' => 'menu_order',
        'order' => 'ASC',
        'post_parent' => 64
      )),
      'return' => array()
    ));

    \WP_Mock::wpFunction('get_post_meta', array(
      'return' => false
    ));

    $setProvider = new PostSetProvider();
    $set = $setProvider->createSet('custom-set');

    $this->assertEquals(64, $set->getId());
    $this->assertEquals('Post 64', $set->getName());
  }
}