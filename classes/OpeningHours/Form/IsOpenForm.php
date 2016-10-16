<?php

namespace OpeningHours\Form;
use OpeningHours\Module\Widget\AbstractWidget;
use OpeningHours\Module\OpeningHours;

/**
 * Widget form for Overview widget / shortcode
 *
 * @author    Jannik Portz <hello@jannikportz.de>
 * @package   OpeningHours\Form
 */
class IsOpenForm extends Form {

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
      'options_callback' => array(OpeningHours::getInstance(), 'getSetsOptions')
    ));

    $this->addField('show_next', array(
      'type' => 'checkbox',
      'caption' => __('Show next open Period', 'wp-opening-hours')
    ));

    // Extended Fields
    $this->addField('open_text', array(
      'type' => 'text',
      'caption' => __('Caption if open', 'wp-opening-hours'),
      'extended' => true,
      'default_placeholder' => true
    ));

    $this->addField('closed_text', array(
      'type' => 'text',
      'caption' => __('Caption if closed', 'wp-opening-hours'),
      'extended' => true,
      'default_placeholder' => true
    ));

    $this->addField('open_class', array(
      'type' => 'text',
      'caption' => __('Class if open (span)', 'wp-opening-hours'),
      'extended' => true,
      'default_placeholder' => true
    ));

    $this->addField('closed_class', array(
      'type' => 'text',
      'caption' => __('Class if closed (span)', 'wp-opening-hours'),
      'extended' => true,
      'default_placeholder' => true
    ));

    $this->addField('next_format', array(
      'type' => 'text',
      'caption' => __('Next Period String Format', 'wp-opening-hours'),
      'extended' => true,
      'default_placeholder' => true,
      'description' => sprintf('%s: %s<br />%s: %s<br />%s: %s<br />%s: %s',
        '%1$s', __('Formatted Date', 'wp-opening-hours'),
        '%2$s', __('Weekday', 'wp-opening-hours'),
        '%3$s', __('Formatted Start Time', 'wp-opening-hours'),
        '%4$s', __('Formatted End Time', 'wp-opening-hours')
      )
    ));

    $this->addField('classes', array(
      'type' => 'text',
      'caption' => __('Class for span', 'wp-opening-hours'),
      'extended' => true,
      'default_placeholder' => true
    ));

    $this->addField('date_format', array(
      'type' => 'text',
      'caption' => __('PHP Date Format', 'wp-opening-hours'),
      'extended' => true,
      'default_placeholder' => true,
      'description' => AbstractWidget::getPhpDateFormatInfo()
    ));

    $this->addField('time_format', array(
      'type' => 'text',
      'caption' => __('PHP Time Format', 'wp-opening-hours'),
      'extended' => true,
      'default_placeholder' => true,
      'description' => AbstractWidget::getPhpDateFormatInfo()
    ));
  }
}