<?php

namespace OpeningHours\Test;

use OpeningHours\Module\CustomPostType\Set;
use OpeningHours\Util\Dates;

class FunctionsTest extends OpeningHoursTestCase {

  protected static $getPostsArgs = array(
    'post_type' => Set::CPT_SLUG,
    'numberposts' => 1,
    'post_parent' => 0,
    'orderby' => 'menu_order',
    'order' => 'ASC'
  );

  public function setUp () {
    parent::setUp();
    require_once __DIR__ . '/../../functions.php';
  }

  public function testIsOpenClosedNoSet () {
    \WP_Mock::wpFunction('get_posts', array(
      'times' => 4,
      'args' => array(self::$getPostsArgs),
      'return' => array()
    ));

    $this->assertFalse(is_open());
    $this->assertEquals(array(false, 'period'), is_open(true));
    $this->assertTrue(is_closed());
    $this->assertEquals(array(true, 'period'), is_closed(true));
  }

  public function testIsOpenClosed () {
    $post = $this->getMockBuilder('WP_Post')->getMock();
    $post->ID = 64;

    \WP_Mock::wpFunction('get_posts', array(
      'times' => '1+',
      'args' => array(self::$getPostsArgs),
      'return' => array($post)
    ));

    \WP_Mock::wpFunction('get_posts', array(
      'times' => '0+',
      'args' => array(array(
        'post_type' => Set::CPT_SLUG,
        'post_parent' => 64
      )),
      'return' => array()
    ));

    \WP_Mock::wpFunction('get_post', array(
      'times' => '1+',
      'args' => array(64),
      'return' => $post
    ));

    \WP_Mock::wpFunction('get_post_meta', array(
      'args' => array(64, '_op_meta_box_set_details_description', true),
      'return' => ''
    ));

    $this->setUpSetData(64, array(
      array('weekday' => 1, 'timeStart' => '13:00', 'timeEnd' => '15:00')
    ), array(
      array('name' => 'Holiday', 'dateStart' => '2016-09-22', 'dateEnd' => '2016-09-23')
    ), array(
      array('name' => 'IO', 'date' => '2016-09-24', 'timeStart' => '13:00', 'timeEnd' => '15:00')
    ));

    $oldDate = Dates::getNow();

    Dates::setNow(new \DateTime('2016-09-19 12:59'));
    $this->assertFalse(is_open());
    Dates::setNow(new \DateTime('2016-09-19 13:00'));
    $this->assertTrue(is_open());
    $this->assertEquals(array(true, 'period'), is_open(true));

    Dates::setNow(new \DateTime('2016-09-21 23:59:59'));
    $this->assertFalse(is_open());
    $this->assertEquals(array(false, 'period'), is_open(true));

    Dates::setNow(new \DateTime('2016-09-22 00:00:00'));
    $this->assertFalse(is_open());
    $this->assertEquals(array(false, 'holiday'), is_open(true));

    Dates::setNow(new \DateTime('2016-09-24 12:59:00'));
    $this->assertFalse(is_open());
    Dates::setNow(new \DateTime('2016-09-24 13:00:00'));
    $this->assertTrue(is_open());
    $this->assertEquals(array(true, 'special_opening'), is_open(true));

    Dates::setNow($oldDate);
  }
}