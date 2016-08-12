<?php

namespace OpeningHours\Test\Module;

use OpeningHours\Module\CustomPostType\Set;
use OpeningHours\Module\Importer;
use OpeningHours\Test\OpeningHoursTestCase;

class ImporterUpgradeWidgetsTest extends OpeningHoursTestCase {

  public function testUpgradeWidgets () {
    $oldWidgetOverviewData = array(
      2 => array(
        'title' => 'My Opening Hours',
        'show-closed' => 'on',
        'caption-closed' => 'We are closed',
        'highlight' => 'period'
      )
    );

    $expectedOverview = array(
      2 => array(
        'title' => 'My Opening Hours',
        'show_closed_days' => 'on',
        'caption_closed' => 'We are closed',
        'highlight' => 'period',
        'set_id' => 64
      )
    );

    $oldWidgetIsOpenData = array(
      3 => array(
        'title' => 'Current Status',
        'caption-closed' => 'We are closed',
        'caption-open' => 'We are open',
        'caption-closed-holiday' => 'We are on holiday',
      )
    );

    $expectedIsOpen = array(
      3 => array(
        'title' => 'Current Status',
        'closed_text' => 'We are closed',
        'open_text' => 'We are open',
        'set_id' => 64
      )
    );

    $oldWidgetHolidaysData = array(
      4 => array(
        'title' => 'Our Holidays',
        'highlighted' => 'on'
      )
    );

    $expectedHolidays = array(
      4 => array(
        'title' => 'Our Holidays',
        'highlight' => 'on',
        'set_id' => 64
      )
    );

    $oldWidgetIOData = array(
      5 => array(
        'title' => 'Irregular Openings',
        // highlighted missing
        'label-by' => 'both'
      )
    );

    $expectedIO = array(
      5 => array(
        'title' => 'Irregular Openings',
        'set_id' => 64
      )
    );

    // Expect old widgets to be retrieved
    \WP_Mock::wpFunction('get_option', array(
      'times' => 1,
      'args' => array('widget_widget_op_overview'),
      'return' => $oldWidgetOverviewData
    ));

    \WP_Mock::wpFunction('get_option', array(
      'times' => 1,
      'args' => array('widget_widget_op_status'),
      'return' => $oldWidgetIsOpenData
    ));

    \WP_Mock::wpFunction('get_option', array(
      'times' => 1,
      'args' => array('widget_widget_op_holidays'),
      'return' => $oldWidgetHolidaysData
    ));

    \WP_Mock::wpFunction('get_option', array(
      'times' => 1,
      'args' => array('widget_widget_op_special_openings'),
      'return' => $oldWidgetIOData
    ));

    $oldSidebarWidgets = array(
      'wp_inactive_widgets' => array(),
      'sidebar-1' => array(
        'widget_op_overview-2',
        'widget_op_status-3'
      ),
      'sidebar-2' => array(
        'widget_op_holidays-4',
        'widget_op_special_openings-5'
      ),
      'array_version' => 3
    );

    $expectedSidebarWidgets = array(
      'wp_inactive_widgets' => array(),
      'sidebar-1' => array(
        'widget_op_overview-2',
        'widget_op_is_open-3'
      ),
      'sidebar-2' => array(
        'widget_op_holidays-4',
        'widget_op_irregular_openings-5'
      ),
      'array_version' => 3
    );

    // Expect old sidebar widgets to be retrieved
    \WP_Mock::wpFunction('get_option', array(
      'times' => 1,
      'args' => array(Importer::OPTION_KEY_SIDEBARS),
      'return' => $oldSidebarWidgets
    ));

    // Delete old widgets
    \WP_Mock::wpFunction('delete_option', array(
      'times' => 1,
      'args' => array('widget_widget_op_overview')
    ));

    \WP_Mock::wpFunction('delete_option', array(
      'times' => 1,
      'args' => array('widget_widget_op_status')
    ));

    \WP_Mock::wpFunction('delete_option', array(
      'times' => 1,
      'args' => array('widget_widget_op_holidays')
    ));

    \WP_Mock::wpFunction('delete_option', array(
      'times' => 1,
      'args' => array('widget_widget_op_special_openings')
    ));

    // Expect add new widget options
    \WP_Mock::wpFunction('add_option', array(
      'times' => 1,
      'args' => array('widget_widget_op_overview', $expectedOverview)
    ));

    \WP_Mock::wpFunction('add_option', array(
      'times' => 1,
      'args' => array('widget_widget_op_is_open', $expectedIsOpen)
    ));

    \WP_Mock::wpFunction('add_option', array(
      'times' => 1,
      'args' => array('widget_widget_op_holidays', $expectedHolidays)
    ));

    \WP_Mock::wpFunction('add_option', array(
      'times' => 1,
      'args' => array('widget_widget_op_irregular_openings', $expectedIO)
    ));

    // Expect new sidebar widgets
    \WP_Mock::wpFunction('update_option', array(
      'times' => 1,
      'args' => array(Importer::OPTION_KEY_SIDEBARS, $expectedSidebarWidgets)
    ));

    $post = $this->getMockBuilder('WP_Post')->getMock();
    $post->ID = 64;
    $post->post_type = Set::CPT_SLUG;
    $post->post_title = 'Opening Hours';

    $importer = Importer::getInstance();
    $reflectedImporter = new \ReflectionClass($importer);
    $postAttribute = $reflectedImporter->getProperty('post');
    $postAttribute->setAccessible(true);
    $postAttribute->setValue($importer, $post);
    $upgradeMethod = $reflectedImporter->getMethod('upgradeWidgets');
    $upgradeMethod->setAccessible(true);
    $upgradeMethod->invoke($importer);
  }
}