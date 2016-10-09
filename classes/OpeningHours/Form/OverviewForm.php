<?php

namespace OpeningHours\Form;

use OpeningHours\Module\Widget\AbstractWidget;
use OpeningHours\Module\OpeningHours;

class OverviewForm extends Form {

  public function __construct () {
    parent::__construct();
    $this->addField('title', array(
      'type' => 'text',
      'caption' => __('Title', 'wp-opening-hours')
    ));

    $this->addField('set_id', array(
      'type' => 'select',
      'caption' => __('Set to show', 'wp-opening-hours'),
      'options_callback' => array(OpeningHours::getInstance(), 'getSetsOptions')
    ));

    $this->addField('highlight', array(
      'type' => 'select',
      'caption' => __('Highlight', 'wp-opening-hours'),
      'options' => array(
        'nothing' => __('Nothing', 'wp-opening-hours'),
        'period' => __('Running Period', 'wp-opening-hours'),
        'day' => __('Current Weekday', 'wp-opening-hours')
      )
    ));

    $this->addField('show_closed_days', array(
      'type' => 'checkbox',
      'caption' => __('Show closed days', 'wp-opening-hours')
    ));

    $this->addField('show_description', array(
      'type' => 'checkbox',
      'caption' => __('Show Set Description', 'wp-opening-hours')
    ));

    $this->addField('compress', array(
      'type' => 'checkbox',
      'caption' => __('Compress Opening Hours', 'wp-opening-hours')
    ));

    $this->addField('short', array(
      'type' => 'checkbox',
      'caption' => __('Use short day captions', 'wp-opening-hours')
    ));

    $this->addField('include_io', array(
      'type' => 'checkbox',
      'caption' => __('Include Irregular Openings', 'wp-opening-hours'),
    ));

    $this->addField('include_holidays', array(
      'type' => 'checkbox',
      'caption' => __('Include Holidays', 'wp-opening-hours')
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
    $this->addField('caption_closed', array(
      'type' => 'text',
      'caption' => __('Closed Caption', 'wp-opening-hours'),
      'extended' => true,
      'default_placeholder' => true
    ));

    $this->addField('highlighted_period_class', array(
      'type' => 'text',
      'caption' => __('Highlighted Period class', 'wp-opening-hours'),
      'extended' => true,
      'default_placeholder' => true
    ));

    $this->addField('highlighted_day_class', array(
      'type' => 'text',
      'caption' => __('Highlighted Day class', 'wp-opening-hours'),
      'extended' => true,
      'default_placeholder' => true
    ));

    $this->addField('time_format', array(
      'type' => 'text',
      'caption' => __('PHP Time Format', 'wp-opening-hours'),
      'extended' => true,
      'description' => AbstractWidget::getPhpDateFormatInfo(),
      'default_placeholder' => true
    ));

    $this->addField('hide_io_date', array(
      'type' => 'checkbox',
      'caption' => __('Hide date of Irregular Openings', 'wp-opening-hours'),
      'extended' => true
    ));
  }
}