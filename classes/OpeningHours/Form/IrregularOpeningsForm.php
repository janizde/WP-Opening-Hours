<?php

namespace OpeningHours\Form;

use OpeningHours\Module\OpeningHours;
use OpeningHours\Module\Widget\AbstractWidget;

/**
 * Widget form for Irregular Openings widget / shortcode
 *
 * @author    Jannik Portz <hello@jannikportz.de>
 * @package   OpeningHours\Form
 */
class IrregularOpeningssForm extends Form {

  public function __construct () {
    parent::__construct();

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
      'caption' => __('Highlight active Irregular Opening', 'wp-opening-hours')
    ));

    $this->addField('template', array(
      'type' => 'select',
      'caption' => __('Template', 'wp-opening-hours'),
      'options' => array(
        'table' => __('Table', 'wp-opening-hours'),
        'list' => __('List', 'wp-opening-hours')
      )
    ));

    $this->addField('include_past', array(
      'type' => 'checkbox',
      'caption' => __('Include past irregular openings', 'wp-opening-hours')
    ));

    // Extended Fields
    $this->addField('class_highlighted', array(
      'type' => 'text',
      'caption' => __('class for highlighted Irregular Opening', 'wp-opening-hours'),
      'extended' => true,
      'default_placeholder' => true
    ));

    $this->addField('date_format', array(
      'type' => 'text',
      'caption' => __('PHP Date Format', 'wp-opening-hours'),
      'extended' => true,
      'description' => AbstractWidget::getPhpDateFormatInfo(),
      'default_placeholder' => true
    ));

    $this->addField('time_format', array(
      'type' => 'text',
      'caption' => __('PHP Time Format', 'wp-opening-hours'),
      'extended' => true,
      'description' => AbstractWidget::getPhpDateFormatInfo(),
      'default_placeholder' => true
    ));
  }
}