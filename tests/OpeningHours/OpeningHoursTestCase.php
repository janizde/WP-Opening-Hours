<?php

namespace OpeningHours\Test;

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

  protected function applyOptionsMap ( array $map ) {
    foreach ($map as $key => $value) {
      \WP_Mock::wpFunction('get_option', array(
        'times' => '0+',
        'args' => array($key),
        'return' => $value
      ));
    }
  }

  protected function createPost (array $data = array()) {
    $post = $this->getMockBuilder('WP_Post')->getMock();
    foreach ($data as $key => $value) {
      $post->$key = $value;
    }
    return $post;
  }
}