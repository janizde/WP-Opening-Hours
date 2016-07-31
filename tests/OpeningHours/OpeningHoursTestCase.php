<?php

namespace OpeningHours\Test;

use OpeningHours\Util\Persistence;

class OpeningHoursTestCase extends \PHPUnit_Framework_TestCase {

  protected function setUp () {
    parent::setUp();
    \WP_Mock::setUp();

    $defaultOptions = array(
      'date_format' => 'Y-m-d',
      'time_format' => 'H:i',
      'timezone_string' => 'Europe/Berlin',
      'gmt_offset' => ''
    );

    $this->applyOptionsMap($defaultOptions);

    \WP_Mock::wpPassthruFunction('__');
    \WP_Mock::wpPassthruFunction('_e');
    \WP_Mock::wpPassthruFunction('_x');
    \WP_Mock::wpPassthruFunction('_n');
  }

  protected function tearDown () {
    parent::tearDown();
    \WP_Mock::tearDown();
  }

  /**
   * Sets option values
   * @param     array     $map      Associative array containing key-value pairs representing an option
   */
  protected function applyOptionsMap ( array $map ) {
    foreach ($map as $key => $value) {
      \WP_Mock::wpFunction('get_option', array(
        'times' => '0+',
        'args' => array($key),
        'return' => $value
      ));
    }
  }

  /**
   * Creates a Mock for a WP_Post object
   * @param     array     $data     Associative array containing data for the post
   * @return    \WP_Post            The post mock
   */
  protected function createPost (array $data = array()) {
    $post = $this->getMockBuilder('WP_Post')->getMock();
    foreach ($data as $key => $value) {
      $post->$key = $value;
    }
    return $post;
  }

  /**
   * Sets up data for a Set
   * @param     int       $postId   The id of the set/post
   * @param     array     $periods  Array of period data to set
   * @param     array     $holidays Array of holiday data to set
   * @param     array     $ios      Array of irregular opening data to set
   */
  protected function setUpSetData ($postId, array $periods = array(), array $holidays = array(), array $ios = array()) {
    if (count($periods) > 0) {
      \WP_Mock::wpFunction('get_post_meta', array(
        'times' => 1,
        'args' => array($postId, Persistence::PERIODS_META_KEY, true),
        'return' => $periods
      ));
    }

    if (count($holidays) > 0) {
      \WP_Mock::wpFunction('get_post_meta', array(
        'times' => 1,
        'args' => array($postId, Persistence::HOLIDAYS_META_KEY, true),
        'return' => $holidays
      ));
    }

    if (count($ios) > 0) {
      \WP_Mock::wpFunction('get_post_meta', array(
        'times' => 1,
        'args' => array($postId, Persistence::IRREGULAR_OPENINGS_META_KEY, true),
        'return' => $ios
      ));
    }
  }
}