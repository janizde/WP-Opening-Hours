<?php

namespace OpeningHours\Module\Widget;

use OpeningHours\Module\I18n;
use OpeningHours\Module\Shortcode\IrregularOpenings as IrregularOpeningsShortcode;

/**
 * Widget for IrregularOpenings Shortcode
 *
 * @author      Jannik Portz
 * @package     OpeningHours\Module\Widget
 */
class IrregularOpenings extends AbstractWidget {

  public function __construct () {
    $title = __('Opening Hours: Irregular Openings', I18n::TEXTDOMAIN);
    $description = __('Lists up all Irregular Openings in the selected Set.', I18n::TEXTDOMAIN);
    parent::__construct('widget_op_irregular_openings', $title, $description, IrregularOpeningsShortcode::getInstance());
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
      'caption' => __('Highlight active Irregular Opening', I18n::TEXTDOMAIN)
    ));

    $this->addField('template', array(
      'type' => 'select',
      'caption' => __('Template', I18n::TEXTDOMAIN),
      'options' => array(
        'table' => __('Table', I18n::TEXTDOMAIN),
        'list' => __('List', I18n::TEXTDOMAIN)
      )
    ));

    // Extended Fields
    $this->addField('class_highlighted', array(
      'type' => 'text',
      'caption' => __('class for highlighted Irregular Opening', I18n::TEXTDOMAIN),
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

    $this->addField('time_format', array(
      'type' => 'text',
      'caption' => __('PHP Time Format', I18n::TEXTDOMAIN),
      'extended' => true,
      'description' => self::getPhpDateFormatInfo(),
      'default_placeholder' => true
    ));
  }
}