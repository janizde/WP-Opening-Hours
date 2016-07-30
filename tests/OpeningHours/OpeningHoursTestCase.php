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

  protected function tearDown () {
    parent::tearDown();
    \WP_Mock::tearDown();
  }
}