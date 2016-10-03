<?php

namespace OpeningHours\Module\Widget;

use OpeningHours\Module\OpeningHours;
use OpeningHours\Module\Shortcode\Holidays as HolidaysShortcode;

/**
 * Widget for Holiday Shortcode
 *
 * @author      Jannik Portz
 * @package     OpeningHours\Module\Widget
 */
class Holidays extends AbstractWidget {

  public function __construct () {
    $title = __('Opening Hours: Holidays', 'wp-opening-hours');
    $description = __('Lists up all Holidays in the selected Set.', 'wp-opening-hours');
    parent::__construct('widget_op_holidays', $title, $description, HolidaysShortcode::getInstance());
  }

  /** @inheritdoc */
  protected function registerFields () {

    // Standard Fields
    $this->addField('title', array(
      'type' => 'text',
      'caption' => __('Title', 'wp-opening-hours')
    ));

    $this->addField('set_id', array(
      'type' => 'select',
      'caption' => __('Set', 'wp-opening-hours'),
      'options_callback' => array(OpeningHours::getInstance(), 'getSetsOptions'),
    ));

    $this->addField('highlight', array(
      'type' => 'checkbox',
      'caption' => __('Highlight active Holiday', 'wp-opening-hours')
    ));

    $this->addField('template', array(
      'type' => 'select',
      'caption' => __('Template', 'wp-opening-hours'),
      'options' => array(
        'table' => __('Table', 'wp-opening-hours'),
        'list' => __('List', 'wp-opening-hours')
      )
    ));

    // Extended Fields
    $this->addField('class_holiday', array(
      'type' => 'text',
      'caption' => __('Holiday <tr> class', 'wp-opening-hours'),
      'extended' => true,
      'default_placeholder' => true
    ));

    $this->addField('class_highlighted', array(
      'type' => 'text',
      'caption' => __('class for highlighted Holiday', 'wp-opening-hours'),
      'extended' => true,
      'default_placeholder' => true
    ));

    $this->addField('date_format', array(
      'type' => 'text',
      'caption' => __('PHP Date Format', 'wp-opening-hours'),
      'extended' => true,
      'description' => self::getPhpDateFormatInfo(),
      'default_placeholder' => true
    ));
  }
}