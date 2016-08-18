<?php

namespace OpeningHours\Module\Widget;

use OpeningHours\Module\Shortcode\IsOpen as IsOpenShortcode;

/**
 * Widget for IsOpen Shortcode
 *
 * @author      Jannik Portz
 * @package     OpeningHours\Module\Widget
 */
class IsOpen extends AbstractWidget {

  public function __construct () {
    $title = __('Opening Hours: Is Open Status', 'opening-hours');
    $description = __('Shows a box saying whether a specific set is currently open or closed based on Periods.', 'opening-hours');
    parent::__construct('widget_op_is_open', $title, $description, IsOpenShortcode::getInstance());
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
      'options_callback' => array('OpeningHours\Module\OpeningHours', 'getSetsOptions')
    ));

    $this->addField('show_next', array(
      'type' => 'checkbox',
      'caption' => __('Show next open Period', 'opening-hours')
    ));

    // Extended Fields
    $this->addField('open_text', array(
      'type' => 'text',
      'caption' => __('Caption if open', 'opening-hours'),
      'extended' => true,
      'default_placeholder' => true
    ));

    $this->addField('closed_text', array(
      'type' => 'text',
      'caption' => __('Caption if closed', 'opening-hours'),
      'extended' => true,
      'default_placeholder' => true
    ));

    $this->addField('open_class', array(
      'type' => 'text',
      'caption' => __('Class if open (span)', 'opening-hours'),
      'extended' => true,
      'default_placeholder' => true
    ));

    $this->addField('closed_class', array(
      'type' => 'text',
      'caption' => __('Class if closed (span)', 'opening-hours'),
      'extended' => true,
      'default_placeholder' => true
    ));

    $this->addField('next_format', array(
      'type' => 'text',
      'caption' => __('Next Period String Format', 'opening-hours'),
      'extended' => true,
      'default_placeholder' => true,
      'description' => sprintf('%s: %s<br />%s: %s<br />%s: %s<br />%s: %s',
        '%1$s', __('Formatted Date', 'opening-hours'),
        '%2$s', __('Weekday', 'opening-hours'),
        '%3$s', __('Formatted Start Time', 'opening-hours'),
        '%4$s', __('Formatted End Time', 'opening-hours')
      )
    ));

    $this->addField('classes', array(
      'type' => 'text',
      'caption' => __('Class for span', 'opening-hours'),
      'extended' => true,
      'default_placeholder' => true
    ));

    $this->addField('date_format', array(
      'type' => 'text',
      'caption' => __('PHP Date Format', 'opening-hours'),
      'extended' => true,
      'default_placeholder' => true,
      'description' => self::getPhpDateFormatInfo()
    ));

    $this->addField('time_format', array(
      'type' => 'text',
      'caption' => __('PHP Time Format', 'opening-hours'),
      'extended' => true,
      'default_placeholder' => true,
      'description' => self::getPhpDateFormatInfo()
    ));
  }
}