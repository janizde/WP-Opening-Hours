<?php

namespace OpeningHours\Module\Widget;

use OpeningHours\Module\I18n;
use OpeningHours\Module\Shortcode\IsOpen as IsOpenShortcode;

/**
 * Widget for IsOpen Shortcode
 *
 * @author      Jannik Portz
 * @package     OpeningHours\Module\Widget
 */
class IsOpen extends AbstractWidget {

	public function __construct () {
		$title = __( 'Opening Hours: Is Open Status', I18n::TEXTDOMAIN );
		$description = __( 'Shows a box saying whether a specific set is currently open or closed based on Periods.', I18n::TEXTDOMAIN );
		parent::__construct( 'widget_op_is_open', $title, $description, IsOpenShortcode::getInstance() );
	}

	/** @inheritdoc */
	protected function registerFields() {

		// Standard Fields
		$this->addField( 'title', array(
			'type'    => 'text',
			'caption' => __( 'Title', I18n::TEXTDOMAIN )
		) );

		$this->addField( 'set_id', array(
			'type'             => 'select',
			'caption'          => __( 'Set', I18n::TEXTDOMAIN ),
			'options'          => array( 'OpeningHours\Module\OpeningHours', 'getSetsOptions' ),
			'options_strategy' => 'callback'
		) );

		$this->addField( 'show_next', array(
			'type'    => 'checkbox',
			'caption' => __( 'Show next open Period', I18n::TEXTDOMAIN )
		) );

		// Extended Fields
		$this->addField( 'open_text', array(
			'type'                => 'text',
			'caption'             => __( 'Caption if open', I18n::TEXTDOMAIN ),
			'extended'            => true,
			'default_placeholder' => true
		) );

		$this->addField( 'closed_text', array(
			'type'                => 'text',
			'caption'             => __( 'Caption if closed', I18n::TEXTDOMAIN ),
			'extended'            => true,
			'default_placeholder' => true
		) );

		$this->addField( 'open_class', array(
			'type'                => 'text',
			'caption'             => __( 'Class if open (span)', I18n::TEXTDOMAIN ),
			'extended'            => true,
			'default_placeholder' => true
		) );

		$this->addField( 'closed_class', array(
			'type'                => 'text',
			'caption'             => __( 'Class if closed (span)', I18n::TEXTDOMAIN ),
			'extended'            => true,
			'default_placeholder' => true
		) );

		$this->addField( 'next_format', array(
			'type'                => 'text',
			'caption'             => __( 'Next Period String Format', I18n::TEXTDOMAIN ),
			'extended'            => true,
			'default_placeholder' => true,
			'description'         => sprintf('%s: %s<br />%s: %s<br />%s: %s<br />%s: %s',
				'%1$s', __( 'Formatted Date', I18n::TEXTDOMAIN ),
				'%2$s', __( 'Weekday', I18n::TEXTDOMAIN ),
				'%3$s', __( 'Formatted Start Time', I18n::TEXTDOMAIN ),
				'%4$s', __( 'Formatted End Time', I18n::TEXTDOMAIN )
				)
		) );

		$this->addField( 'classes', array(
			'type'                => 'text',
			'caption'             => __( 'Class for span', I18n::TEXTDOMAIN ),
			'extended'            => true,
			'default_placeholder' => true
		) );

		$this->addField( 'date_format', array(
			'type'                => 'text',
			'caption'             => __( 'PHP Date Format', I18n::TEXTDOMAIN ),
			'extended'            => true,
			'default_placeholder' => true,
			'description'         => self::getPhpDateFormatInfo()
		) );

		$this->addField( 'time_format', array(
			'type'                => 'text',
			'caption'             => __( 'PHP Time Format', I18n::TEXTDOMAIN ),
			'extended'            => true,
			'default_placeholder' => true,
			'description'         => self::getPhpDateFormatInfo()
		) );
	}
}