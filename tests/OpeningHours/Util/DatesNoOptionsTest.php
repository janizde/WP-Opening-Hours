<?php

namespace OpeningHours\Test\Util;

use OpeningHours\Test\OpeningHoursTestCase;
use OpeningHours\Util\Dates;

/**
 * Extra class for test with no default options
 * @package OpeningHours\Test\Util
 */
class DatesNoOptionsTest extends OpeningHoursTestCase {

  protected function setUp () {
    \WP_Mock::setUp();
  }

  public function testNoOptionsSet () {
    \WP_Mock::wpFunction('get_option', array(
      'times' => 1,
      'args' => array('date_format', Dates::STD_DATE_FORMAT),
      'return' => Dates::STD_DATE_FORMAT
    ));

    \WP_Mock::wpFunction('get_option', array(
      'times' => 1,
      'args' => array('time_format', Dates::STD_TIME_FORMAT),
      'return' => Dates::STD_TIME_FORMAT
    ));

    foreach (array('gmt_offset', 'timezone_string') as $key) {
      \WP_Mock::wpFunction('get_option', array(
        'times' => 1,
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