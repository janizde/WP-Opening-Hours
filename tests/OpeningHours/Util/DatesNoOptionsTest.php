<?php

namespace OpeningHours\Test\Util;

use OpeningHours\Util\Dates;

/**
 * Extra class for test with no default options
 * @package OpeningHours\Test\Util
 */
class DatesNoOptionsTest extends \PHPUnit_Framework_TestCase {

  protected function setUp () {
    parent::setUp();
    \WP_Mock::setUp();
  }

  protected function tearDown () {
    parent::tearDown();
    \WP_Mock::tearDown();
  }

  public function testNoOptionsSet () {
    \WP_Mock::wpFunction('get_option', array(
      'args' => array('date_format', Dates::STD_DATE_FORMAT),
      'return' => Dates::STD_DATE_FORMAT
    ));

    \WP_Mock::wpFunction('get_option', array(
      'args' => array('time_format', Dates::STD_TIME_FORMAT),
      'return' => Dates::STD_TIME_FORMAT
    ));

    \WP_Mock::wpFunction('get_option', array(
      'args' => array('start_of_week', 0),
      'return' => 0
    ));

    foreach (array('gmt_offset', 'timezone_string') as $key) {
      \WP_Mock::wpFunction('get_option', array(
        'args' => array($key),
        'return' => ''
      ));
    }

    Dates::getInstance();
    $this->assertEquals(Dates::STD_DATE_FORMAT, Dates::getDateFormat());
    $this->assertEquals(Dates::STD_TIME_FORMAT, Dates::getTimeFormat());
    $this->assertInstanceOf('DateTimeZone', Dates::getTimezone());
  }
}