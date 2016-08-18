<?php

namespace OpeningHours\Module\Widget;

use OpeningHours\Module\Shortcode\Overview as OverviewShortcode;

/**
 * Widget for Overview Shortcode
 *
 * @author      Jannik Portz
 * @package     OpeningHours\Module\Widget
 */
class Overview extends AbstractWidget {

  public function __construct () {
    $title = __('Opening Hours: Overview', 'opening-hours');
    $description = __('Displays a Table with your Opening Hours. Alternatively use the op-overview Shortcode.', 'opening-hours');
    parent::__construct('widget_op_overview', $title, $description, OverviewShortcode::getInstance());
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
      'caption' => __('Set to show', 'opening-hours'),
      'options_callback' => array('OpeningHours\Module\OpeningHours', 'getSetsOptions')
    ));

    $this->addField('highlight', array(
      'type' => 'select',
      'caption' => __('Highlight', 'opening-hours'),
      'options' => array(
        'nothing' => __('Nothing', 'opening-hours'),
        'period' => __('Running Period', 'opening-hours'),
        'day' => __('Current Weekday', 'opening-hours')
      )
    ));

    $this->addField('show_closed_days', array(
      'type' => 'checkbox',
      'caption' => __('Show closed days', 'opening-hours')
    ));

    $this->addField('show_description', array(
      'type' => 'checkbox',
      'caption' => __('Show Set Description', 'opening-hours')
    ));

    $this->addField('compress', array(
      'type' => 'checkbox',
      'caption' => __('Compress Opening Hours', 'opening-hours')
    ));

    $this->addField('short', array(
      'type' => 'checkbox',
      'caption' => __('Use short day captions', 'opening-hours')
    ));

    $this->addField('include_io', array(
      'type' => 'checkbox',
      'caption' => __('Include Irregular Openings', 'opening-hours'),
    ));

    $this->addField('include_holidays', array(
      'type' => 'checkbox',
      'caption' => __('Include Holidays', 'opening-hours')
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
    $this->addField('caption_closed', array(
      'type' => 'text',
      'caption' => __('Closed Caption', 'opening-hours'),
      'extended' => true,
      'default_placeholder' => true
    ));

    $this->addField('highlighted_period_class', array(
      'type' => 'text',
      'caption' => __('Highlighted Period class', 'opening-hours'),
      'extended' => true,
      'default_placeholder' => true
    ));

    $this->addField('highlighted_day_class', array(
      'type' => 'text',
      'caption' => __('Highlighted Day class', 'opening-hours'),
      'extended' => true,
      'default_placeholder' => true
    ));

    $this->addField('time_format', array(
      'type' => 'text',
      'caption' => __('PHP Time Format', 'opening-hours'),
      'extended' => true,
      'description' => self::getPhpDateFormatInfo(),
      'default_placeholder' => true
    ));

    $this->addField('hide_io_date', array(
      'type' => 'checkbox',
      'caption' => __('Hide date of Irregular Openings', 'opening-hours'),
      'extended' => true
    ));
  }
}