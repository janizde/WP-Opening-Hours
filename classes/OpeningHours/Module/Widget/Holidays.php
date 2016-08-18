<?php

namespace OpeningHours\Module\Widget;

use OpeningHours\Module\Shortcode\Holidays as HolidaysShortcode;

/**
 * Widget for Holiday Shortcode
 *
 * @author      Jannik Portz
 * @package     OpeningHours\Module\Widget
 */
class Holidays extends AbstractWidget {

  public function __construct () {
    $title = __('Opening Hours: Holidays', 'opening-hours');
    $description = __('Lists up all Holidays in the selected Set.', 'opening-hours');
    parent::__construct('widget_op_holidays', $title, $description, HolidaysShortcode::getInstance());
  }

  /** @inheritdoc */
  protected function registerFields () {

    // Standard Fields
    $this->addField('title', array(
      'type' => 'text',
      'caption' => __('Title', 'opening-hours')
    ));

    $this->addField('set_id', array(
      'type' => 'select',
      'caption' => __('Set', 'opening-hours'),
      'options_callback' => array('OpeningHours\Module\OpeningHours', 'getSetsOptions'),
    ));

    $this->addField('highlight', array(
      'type' => 'checkbox',
      'caption' => __('Highlight active Holiday', 'opening-hours')
    ));

    $this->addField('template', array(
      'type' => 'select',
      'caption' => __('Template', 'opening-hours'),
      'options' => array(
        'table' => __('Table', 'opening-hours'),
        'list' => __('List', 'opening-hours')
      )
    ));

    // Extended Fields
    $this->addField('class_holiday', array(
      'type' => 'text',
      'caption' => __('Holiday <tr> class', 'opening-hours'),
      'extended' => true,
      'default_placeholder' => true
    ));

    $this->addField('class_highlighted', array(
      'type' => 'text',
      'caption' => __('class for highlighted Holiday', 'opening-hours'),
      'extended' => true,
      'default_placeholder' => true
    ));

    $this->addField('date_format', array(
      'type' => 'text',
      'caption' => __('PHP Date Format', 'opening-hours'),
      'extended' => true,
      'description' => self::getPhpDateFormatInfo(),
      'default_placeholder' => true
    ));
  }
}