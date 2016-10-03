<?php

namespace OpeningHours\Test;

use OpeningHours\Entity\Set;
use OpeningHours\Util\ArrayObject;
use OpeningHours\Util\Persistence;
use WP_Mock\Functions;

abstract class OpeningHoursTestCase extends \PHPUnit_Framework_TestCase {

  protected function setUp () {
    parent::setUp();
    \WP_Mock::setUp();

    $defaultOptions = array(
      'date_format' => 'Y-m-d',
      'time_format' => 'H:i',
      'start_of_week' => 0,
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

      \WP_Mock::wpFunction('get_option', array(
        'times' => '0+',
        'args' => array($key, Functions::type('string')),
        'return' => $value
      ));
    }

    \WP_Mock::wpFunction('get_option', array(
      'times' => '0+',
      'args' => array('start_of_week', Functions::type('int'))
    ));
  }
  
  protected function createSet ($id, array $periods = array(), array $holidays = array(), array $irregularOpenings = array()) {
    $set = new Set($id);
    $set->setPeriods(ArrayObject::createFromArray($periods));
    $set->setHolidays(ArrayObject::createFromArray($holidays));
    $set->setIrregularOpenings(ArrayObject::createFromArray($irregularOpenings));
    return $set;
  }
}