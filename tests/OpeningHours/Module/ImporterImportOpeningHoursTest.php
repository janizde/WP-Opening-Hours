<?php

namespace OpeningHours\Test\Module;

use OpeningHours\Module\CustomPostType\Set as SetCPT;
use OpeningHours\Module\Importer;
use OpeningHours\Test\OpeningHoursTestCase;
use OpeningHours\Util\Persistence;

class ImporterImportOpeningHoursTest extends OpeningHoursTestCase {
  public function testImportOpeningHours() {
    $oldPeriodData = array(
      'monday' => array(
        'times' => array(array('08', '00', '12', '00'), array('12', '30', '18', '00'))
      ),
      'tuesday' => array(
        'times' => array(array('08', '00', '12', '00'), array('12', '30', '18', '00'))
      ),
      'wednesday' => array(
        'times' => array(array('08', '00', '12', '00'), array('12', '30', '18', '00'))
      ),
      'thursday' => array(
        'times' => array(array('08', '00', '12', '00'))
      ),
      'sunday' => array(
        'times' => array(array('09', '00', '13', '00'))
      )
    );

    $expectedPeriodData = array(
      array('weekday' => 1, 'timeStart' => '08:00', 'timeEnd' => '12:00'),
      array('weekday' => 1, 'timeStart' => '12:30', 'timeEnd' => '18:00'),
      array('weekday' => 2, 'timeStart' => '08:00', 'timeEnd' => '12:00'),
      array('weekday' => 2, 'timeStart' => '12:30', 'timeEnd' => '18:00'),
      array('weekday' => 3, 'timeStart' => '08:00', 'timeEnd' => '12:00'),
      array('weekday' => 3, 'timeStart' => '12:30', 'timeEnd' => '18:00'),
      array('weekday' => 4, 'timeStart' => '08:00', 'timeEnd' => '12:00'),
      array('weekday' => 0, 'timeStart' => '09:00', 'timeEnd' => '13:00')
    );

    \WP_Mock::wpFunction('get_option', array(
      'times' => 1,
      'args' => array(Importer::OPTION_KEY_PERIODS),
      'return' => json_encode($oldPeriodData)
    ));

    $oldHolidayData = array(
      array(
        'name' => 'Holiday 1',
        'start' => '08/09/2016',
        'end' => '08/20/2016'
      ),
      array(
        'name' => 'Holiday 2',
        'start' => '08/21/2016',
        'end' => '08/27/2016'
      )
    );

    $expectedHolidayData = array(
      array(
        'name' => 'Holiday 1',
        'dateStart' => '2016-08-09',
        'dateEnd' => '2016-08-20'
      ),
      array(
        'name' => 'Holiday 2',
        'dateStart' => '2016-08-21',
        'dateEnd' => '2016-08-27'
      )
    );

    \WP_Mock::wpFunction('get_option', array(
      'times' => 1,
      'args' => array(Importer::OPTION_KEY_HOLIDAYS),
      'return' => json_encode($oldHolidayData)
    ));

    $oldIOData = array(
      array(
        'name' => 'IO 1',
        'date' => '08/30/2016',
        'start' => '08:30',
        'end' => '19:00'
      ),
      array(
        'name' => 'IO 2',
        'date' => '09/01/2016',
        'start' => '09:00',
        'end' => '21:00'
      )
    );

    $expectedIOData = array(
      array(
        'name' => 'IO 1',
        'date' => '2016-08-30',
        'timeStart' => '08:30',
        'timeEnd' => '19:00'
      ),
      array(
        'name' => 'IO 2',
        'date' => '2016-09-01',
        'timeStart' => '09:00',
        'timeEnd' => '21:00'
      )
    );

    \WP_Mock::wpFunction('get_option', array(
      'times' => 1,
      'args' => array(Importer::OPTION_KEY_IRREGULAR_OPENINGS),
      'return' => json_encode($oldIOData)
    ));

    $post = $this->getMockBuilder('WP_Post')->getMock();
    $post->ID = 64;
    $post->post_type = SetCPT::CPT_SLUG;
    $post->post_title = 'Opening Hours';
    $post->post_status = 'publish';

    \WP_Mock::wpFunction('wp_insert_post', array(
      'times' => 1,
      'args' => array(
        array(
          'post_type' => SetCPT::CPT_SLUG,
          'post_title' => 'Opening Hours',
          'post_status' => 'publish'
        )
      ),
      'return' => $post->ID
    ));

    \WP_Mock::wpFunction('get_post', array(
      'times' => 1,
      'args' => array($post->ID),
      'return' => $post
    ));

    \WP_Mock::wpPassthruFunction('__');

    \WP_Mock::wpFunction('update_post_meta', array(
      'times' => 1,
      'args' => array($post->ID, Persistence::PERIODS_META_KEY, $expectedPeriodData)
    ));

    \WP_Mock::wpFunction('update_post_meta', array(
      'times' => 1,
      'args' => array($post->ID, Persistence::HOLIDAYS_META_KEY, $expectedHolidayData)
    ));

    \WP_Mock::wpFunction('update_post_meta', array(
      'times' => 1,
      'args' => array($post->ID, Persistence::IRREGULAR_OPENINGS_META_KEY, $expectedIOData)
    ));

    foreach (
      array(
        Importer::OPTION_KEY_PERIODS,
        Importer::OPTION_KEY_HOLIDAYS,
        Importer::OPTION_KEY_IRREGULAR_OPENINGS,
        Importer::OPTION_KEY_SETTINGS
      )
      as $key
    ) {
      \WP_Mock::wpFunction('delete_option', array(
        'times' => 1,
        'args' => array($key)
      ));
    }

    $this->runImportOpeningHours();
  }

  public function testNoOldData() {
    foreach (
      array(Importer::OPTION_KEY_PERIODS, Importer::OPTION_KEY_HOLIDAYS, Importer::OPTION_KEY_IRREGULAR_OPENINGS)
      as $key
    ) {
      \WP_Mock::wpFunction('get_option', array(
        'times' => 1,
        'args' => array($key),
        'return' => null
      ));
    }

    \WP_Mock::wpFunction('wp_insert_post', array(
      'times' => 0
    ));

    $this->runImportOpeningHours();
  }

  public function testMalformedPeriodsString() {
    \WP_Mock::wpFunction('get_option', array(
      'times' => 1,
      'args' => array(Importer::OPTION_KEY_PERIODS),
      'return' => 'foo'
    ));

    \WP_Mock::wpFunction('get_option', array(
      'return' => null
    ));

    \WP_Mock::wpFunction('wp_insert_post', array(
      'times' => 0
    ));

    $this->runImportOpeningHours();
  }

  public function testMalformedPeriodsInvalidData() {
    $periodData = array(
      'invalidWeekday' => array(
        'times' => array(array('08', '00', '13', '00'))
      ),
      'monday' => array(
        'invalidKey' => array(array('08', '00', '13', '00'))
      ),
      'tuesday' => array(
        'times' => array(array('foo', 'bar', 'foo', 'baz'))
      )
    );

    \WP_Mock::wpFunction('get_option', array(
      'times' => 1,
      'args' => array(Importer::OPTION_KEY_PERIODS),
      'return' => json_encode($periodData)
    ));

    \WP_Mock::wpFunction('get_option', array(
      'return' => null
    ));

    \WP_Mock::wpFunction('wp_insert_post', array(
      'times' => 0
    ));

    $this->runImportOpeningHours();
  }

  public function testMalformedHolidaysString() {
    \WP_Mock::wpFunction('get_option', array(
      'times' => 1,
      'args' => array(Importer::OPTION_KEY_HOLIDAYS),
      'return' => 'foo'
    ));

    \WP_Mock::wpFunction('get_option', array(
      'return' => null
    ));

    \WP_Mock::wpFunction('wp_insert_post', array(
      'times' => 0
    ));

    $this->runImportOpeningHours();
  }

  public function testMalformedHolidaysInvalidData() {
    $holidayData = array(
      array(
        // name missing
        'start' => '08/09/2016',
        'end' => '08/20/2016'
      ),
      'foo' // not an array
    );

    \WP_Mock::wpFunction('get_option', array(
      'times' => 1,
      'args' => array(Importer::OPTION_KEY_HOLIDAYS),
      'return' => json_encode($holidayData)
    ));

    \WP_Mock::wpFunction('get_option', array(
      'return' => null
    ));

    \WP_Mock::wpFunction('wp_insert_post', array(
      'times' => 0
    ));

    $this->runImportOpeningHours();
  }

  public function testMalformedIrregularOpeningsString() {
    \WP_Mock::wpFunction('get_option', array(
      'times' => 1,
      'args' => array(Importer::OPTION_KEY_IRREGULAR_OPENINGS),
      'return' => 'foo'
    ));

    \WP_Mock::wpFunction('get_option', array(
      'return' => null
    ));

    \WP_Mock::wpFunction('wp_insert_post', array(
      'times' => 0
    ));

    $importer = Importer::getInstance();
    $importer->import();
  }

  public function testMalformedIrregularOpeningsInvalidData() {
    $ioData = array(
      array(
        // name missing
        'start' => '08:00',
        'end' => '20:00',
        'date' => '08/07/2016'
      ),
      'foo', // not an array
      array(
        // Invalid time format
        'start' => 'foo',
        'end' => 'bar',
        'date' => '08/07/2016',
        'name' => 'IO'
      )
    );

    \WP_Mock::wpFunction('get_option', array(
      'times' => 1,
      'args' => array(Importer::OPTION_KEY_IRREGULAR_OPENINGS),
      'return' => json_encode($ioData)
    ));

    \WP_Mock::wpFunction('get_option', array(
      'return' => null
    ));

    \WP_Mock::wpFunction('wp_insert_post', array(
      'times' => 0
    ));

    $this->runImportOpeningHours();
  }

  public function testParseDateString() {
    $importer = Importer::getInstance();
    $date = $importer->parseDateString('08/09/2016');
    $this->assertEquals(9, (int) $date->format('d'));
    $this->assertEquals(8, (int) $date->format('m'));
    $this->assertEquals(2016, (int) $date->format('Y'));
  }

  /**
   * @expectedException \InvalidArgumentException
   */
  public function testParseDateStringInvalidFormat() {
    $importer = Importer::getInstance();
    $importer->parseDateString('08\09');
  }

  /**
   * @expectedException \InvalidArgumentException
   */
  public function testParseDateStringEmpty() {
    $importer = Importer::getInstance();
    $importer->parseDateString('');
  }

  protected function runImportOpeningHours() {
    // Call protected method importOpeningHours via Reflection to split up into multiple tests
    $importer = Importer::getInstance();
    $importerReflection = new \ReflectionClass($importer);
    $method = $importerReflection->getMethod('importOpeningHours');
    $method->setAccessible(true);
    $method->invoke($importer);
  }
}
