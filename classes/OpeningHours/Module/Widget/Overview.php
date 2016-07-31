<?php

namespace OpeningHours\Module\Widget;

use OpeningHours\Module\I18n;
use OpeningHours\Module\Shortcode\Overview as OverviewShortcode;

/**
 * Widget for Overview Shortcode
 *
 * @author      Jannik Portz
 * @package     OpeningHours\Module\Widget
 */
class Overview extends AbstractWidget {

	public function __construct () {
		$title = __( 'Opening Hours: Overview', I18n::TEXTDOMAIN );
		$description = __( 'Displays a Table with your Opening Hours. Alternatively use the op-overview Shortcode.', I18n::TEXTDOMAIN );
		parent::__construct( 'widget_op_overview', $title, $description, OverviewShortcode::getInstance() );
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
			'caption'          => __( 'Set to show', I18n::TEXTDOMAIN ),
			'options_callback' => array( 'OpeningHours\Module\OpeningHours', 'getSetsOptions' )
		) );

		$this->addField( 'highlight', array(
			'type'    => 'select',
			'caption' => __( 'Highlight', I18n::TEXTDOMAIN ),
			'options' => array(
				'nothing' => __( 'Nothing', I18n::TEXTDOMAIN ),
				'period'  => __( 'Running Period', I18n::TEXTDOMAIN ),
				'day'     => __( 'Current Weekday', I18n::TEXTDOMAIN )
			)
		) );

		$this->addField( 'show_closed_days', array(
			'type'    => 'checkbox',
			'caption' => __( 'Show closed days', I18n::TEXTDOMAIN )
		) );

		$this->addField( 'show_description', array(
			'type'    => 'checkbox',
			'caption' => __( 'Show Set Description', I18n::TEXTDOMAIN )
		) );

		$this->addField( 'compress', array(
			'type'    => 'checkbox',
			'caption' => __( 'Compress Opening Hours', I18n::TEXTDOMAIN )
		) );

		$this->addField( 'short', array(
			'type'    => 'checkbox',
			'caption' => __( 'Use short day captions', I18n::TEXTDOMAIN )
		) );

		$this->addField( 'include_io', array(
			'type'    => 'checkbox',
			'caption' => __( 'Include Irregular Openings', I18n::TEXTDOMAIN ),
		) );

		$this->addField( 'include_holidays', array(
			'type'      => 'checkbox',
			'caption'   => __( 'Include Holidays', I18n::TEXTDOMAIN )
		) );

		// Extended Fields
		$this->addField( 'caption_closed', array(
			'type'                => 'text',
			'caption'             => __( 'Closed Caption', I18n::TEXTDOMAIN ),
			'extended'            => true,
			'default_placeholder' => true
		) );

		$this->addField( 'table_classes', array(
			'type'                => 'text',
			'caption'             => __( 'Table class', I18n::TEXTDOMAIN ),
			'extended'            => true,
			'default_placeholder' => true
		) );

		$this->addField( 'row_classes', array(
			'type'                => 'text',
			'caption'             => __( 'Table Row class', I18n::TEXTDOMAIN ),
			'extended'            => true,
			'default_placeholder' => true
		) );

		$this->addField( 'cell_classes', array(
			'type'                => 'text',
			'caption'             => __( 'Table Cell class', I18n::TEXTDOMAIN ),
			'extended'            => true,
			'default_placeholder' => true
		) );

		$this->addField( 'cell_heading_classes', array(
			'type'                => 'text',
			'caption'             => __( 'Table Cell Heading class', I18n::TEXTDOMAIN ),
			'extended'            => true,
			'default_placeholder' => true
		) );

		$this->addField( 'cell_periods_classes', array(
			'type'                => 'text',
			'caption'             => __( 'Table Cell Periods class', I18n::TEXTDOMAIN ),
			'extended'            => true,
			'default_placeholder' => true
		) );

		$this->addField( 'highlighted_period_class', array(
			'type'                => 'text',
			'caption'             => __( 'Highlighted Period class', I18n::TEXTDOMAIN ),
			'extended'            => true,
			'default_placeholder' => true
		) );

		$this->addField( 'highlighted_day_class', array(
			'type'                => 'text',
			'caption'             => __( 'Highlighted Day class', I18n::TEXTDOMAIN ),
			'extended'            => true,
			'default_placeholder' => true
		) );

		$this->addField( 'table_id_prefix', array(
			'type'                => 'text',
			'caption'             => __( 'Table ID Prefix', I18n::TEXTDOMAIN ),
			'extended'            => true,
			'default_placeholder' => true
		) );

		$this->addField( 'time_format', array(
			'type'                => 'text',
			'caption'             => __( 'PHP Time Format', I18n::TEXTDOMAIN ),
			'extended'            => true,
			'description'         => self::getPhpDateFormatInfo(),
			'default_placeholder' => true
		) );

		$this->addField( 'hide_io_date', array(
			'type'                => 'checkbox',
			'caption'             => __( 'Hide date of Irregular Openings', I18n::TEXTDOMAIN ),
			'extended'            => true
		) );
	}
}