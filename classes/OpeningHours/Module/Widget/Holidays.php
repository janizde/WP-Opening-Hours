<?php

namespace OpeningHours\Module\Widget;

use OpeningHours\Module\I18n;
use OpeningHours\Module\Shortcode\Holidays as HolidaysShortcode;

/**
 * Widget for Holiday Shortcode
 *
 * @author      Jannik Portz
 * @package     OpeningHours\Module\Widget
 */
class Holidays extends AbstractWidget {

  public function __construct () {
    $title = __('Opening Hours: Holidays', I18n::TEXTDOMAIN);
    $description = __('Lists up all Holidays in the selected Set.', I18n::TEXTDOMAIN);
    parent::__construct('widget_op_holidays', $title, $description, HolidaysShortcode::getInstance());
  }

  /** @inheritdoc */
  protected function registerFields () {

    // Standard Fields
    $this->addField('title', array(
      'type' => 'text',
      'caption' => __('Title', I18n::TEXTDOMAIN)
    ));

    $this->addField('set_id', array(
      'type' => 'select',
      'caption' => __('Set', I18n::TEXTDOMAIN),
      'options_callback' => array('OpeningHours\Module\OpeningHours', 'getSetsOptions'),
    ));

    $this->addField('highlight', array(
      'type' => 'checkbox',
      'caption' => __('Highlight active Holiday', I18n::TEXTDOMAIN)
    ));

    // Extended Fields
    $this->addField('class_holiday', array(
      'type' => 'text',
      'caption' => __('Holiday <tr> class', I18n::TEXTDOMAIN),
      'extended' => true,
      'default_placeholder' => true
    ));

    $this->addField('class_highlighted', array(
      'type' => 'text',
      'caption' => __('class for highlighted Holiday', I18n::TEXTDOMAIN),
      'extended' => true,
      'default_placeholder' => true
    ));

    $this->addField('date_format', array(
      'type' => 'text',
      'caption' => __('PHP Date Format', I18n::TEXTDOMAIN),
      'extended' => true,
      'description' => self::getPhpDateFormatInfo(),
      'default_placeholder' => true
    ));
  }
}