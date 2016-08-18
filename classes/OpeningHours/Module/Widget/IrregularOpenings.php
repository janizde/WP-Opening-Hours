<?php

namespace OpeningHours\Module\Widget;

use OpeningHours\Module\Shortcode\IrregularOpenings as IrregularOpeningsShortcode;

/**
 * Widget for IrregularOpenings Shortcode
 *
 * @author      Jannik Portz
 * @package     OpeningHours\Module\Widget
 */
class IrregularOpenings extends AbstractWidget {

  public function __construct () {
    $title = __('Opening Hours: Irregular Openings', 'opening-hours');
    $description = __('Lists up all Irregular Openings in the selected Set.', 'opening-hours');
    parent::__construct('widget_op_irregular_openings', $title, $description, IrregularOpeningsShortcode::getInstance());
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
      'caption' => __('Highlight active Irregular Opening', 'opening-hours')
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
    $this->addField('class_highlighted', array(
      'type' => 'text',
      'caption' => __('class for highlighted Irregular Opening', 'opening-hours'),
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

    $this->addField('time_format', array(
      'type' => 'text',
      'caption' => __('PHP Time Format', 'opening-hours'),
      'extended' => true,
      'description' => self::getPhpDateFormatInfo(),
      'default_placeholder' => true
    ));
  }
}