<?php

namespace OpeningHours\Module\Widget;

use OpeningHours\Module\OpeningHours;
use OpeningHours\Module\Shortcode\IsOpen as IsOpenShortcode;

/**
 * Widget for IsOpen Shortcode
 *
 * @author      Jannik Portz
 * @package     OpeningHours\Module\Widget
 */
class IsOpen extends AbstractWidget {
  public function __construct() {
    $title = __('Opening Hours: Is Open Status', 'wp-opening-hours');
    $description = __(
      'Shows a box saying whether a specific set is currently open or closed based on Periods.',
      'wp-opening-hours'
    );
    parent::__construct('widget_op_is_open', $title, $description, IsOpenShortcode::getInstance());
  }

  /** @inheritdoc */
  protected function registerFields() {
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

    $this->addField('show_today', array(
      'type' => 'select',
      'caption' => __('Show todays opening hours', 'wp-opening-hours'),
      'options' => array(
        'never' => __('Never', 'wp-opening-hours'),
        'open' => __('When open', 'wp-opening-hours'),
        'always' => __('Always', 'wp-opening-hours')
      )
    ));

    $this->addField('show_closed_holidays', array(
      'type' => 'checkbox',
      'caption' => __('Show Holiday name(s) when closed', 'wp-opening-hours')
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

    $this->addField('closed_holiday_text', array(
      'type' => 'text',
      'caption' => __('Caption if closed and day has holiday(s)', 'wp-opening-hours'),
      'extended' => true,
      'default_placeholder' => true,
      'description' => sprintf('%s: %s', '<code>%1$s</code>', __('Formatted Holiday Names String', 'wp-opening-hours'))
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
      'description' => sprintf(
        '%s: %s<br />%s: %s<br />%s: %s<br />%s: %s',
        '<code>%1$s</code>',
        __('Formatted Date', 'wp-opening-hours'),
        '<code>%2$s</code>',
        __('Weekday', 'wp-opening-hours'),
        '<code>%3$s</code>',
        __('Formatted Start Time', 'wp-opening-hours'),
        '<code>%4$s</code>',
        __('Formatted End Time', 'wp-opening-hours')
      )
    ));

    $this->addField('today_format', array(
      'type' => 'text',
      'caption' => __('Todays opening hours format', 'wp-opening-hours'),
      'extended' => true,
      'default_placeholder' => true,
      'description' => sprintf(
        '%s: %s<br />%s: %s<br />%s: %s',
        '<code>%1$s</code>',
        __('Formatted time range of all periods', 'wp-opening-hours'),
        '<code>%2$s</code>',
        __('Formatted start time of first period on that day', 'wp-opening-hours'),
        '<code>%3$s</code>',
        __('Formatted end time of last period on that day', 'wp-opening-hours')
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
      'description' => self::getPhpDateFormatInfo()
    ));

    $this->addField('time_format', array(
      'type' => 'text',
      'caption' => __('PHP Time Format', 'wp-opening-hours'),
      'extended' => true,
      'default_placeholder' => true,
      'description' => self::getPhpDateFormatInfo()
    ));
  }
}
