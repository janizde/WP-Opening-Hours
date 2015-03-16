<?php
/**
 *  Opening Hours: Module: Widget: Overview
 */

namespace OpeningHours\Module\Widget;

use OpeningHours\Module\I18n;
use OpeningHours\Module\Shortcode\Overview as OverviewShortcode;

class Overview extends AbstractWidget {

  const SHORTCODE   = 'op-overview';

  /**
   *  Init
   *
   *  @access     protected
   */
  protected function init () {

    $this->setShortcode( OverviewShortcode::getInstance() );

    $this->setWidgetId( 'widget_op_overview' );

    $this->setTitle( __( 'Opening Hours: Overview', I18n::TEXTDOMAIN ) );

    $this->setDescription( __( 'Displays a Table with your Opening Hours. Alternatively use the op-overview Shortcode.', I18n::TEXTDOMAIN ) );

  }

  /**
   *  Register Fields
   *
   *  @access     protected
   */
  protected function registerFields () {

    /**
     *  Standard Fields
     */

    /** Field: Title */
    $this->addField( 'title', array(
      'type'    => 'text',
      'caption' => __( 'Title', I18n::TEXTDOMAIN )
    ) );

    /** Field: Sets */
    $this->addField( 'set_id', array(
      'type'    => 'select',
      'caption' => __( 'Set to show', I18n::TEXTDOMAIN ),
      'options' => array( 'OpeningHours\Module\OpeningHours', 'getSetsOptions' ),

      'options_strategy'  => 'callback'
    ) );

    /** Field: Highlight */
    $this->addField( 'highlight', array(
      'type'    => 'select',
      'caption' => __( 'Highlight', I18n::TEXTDOMAIN ),
      'options' => array(
        'nothing' => __( 'Nothing', I18n::TEXTDOMAIN ),
        'period'  => __( 'Running Period', I18n::TEXTDOMAIN ),
        'day'     => __( 'Current Weekday', I18n::TEXTDOMAIN )
      )
    ) );

    /** Field: Show Closed Days */
    $this->addField( 'show_closed_days', array(
      'type'    => 'checkbox',
      'caption' => __( 'Show closed days', I18n::TEXTDOMAIN )
    ) );

    /** Field: Show Description */
    $this->addField( 'show_description', array(
      'type'    => 'checkbox',
      'caption' => __( 'Show Set Description', I18n::TEXTDOMAIN )
    ) );

    /** Field: Compress */
    $this->addField( 'compress', array(
      'type'    => 'checkbox',
      'caption' => __( 'Compress Opening Hours', I18n::TEXTDOMAIN )
    ) );

    /** Field: Short */
    $this->addField( 'short', array(
      'type'    => 'checkbox',
      'caption' => __( 'Use hort day captions', I18n::TEXTDOMAIN )
    ) );

    /**
     *  Extended Fields
     */

    /** Field: Closed Caption */
    $this->addField( 'caption_closed', array(
      'type'      => 'text',
      'caption'   => __( 'Closed Caption', I18n::TEXTDOMAIN ),
      'extended'  => true,

      'default_placeholder' => true
    ) );

    /** Field: Table Classes */
    $this->addField( 'table_classes', array(
      'type'      => 'text',
      'caption'   => __( 'Table class', I18n::TEXTDOMAIN ),
      'extended'  => true,

      'default_placeholder' => true
    ) );

    /** Field: Row Classes */
    $this->addField( 'row_classes', array(
      'type'      => 'text',
      'caption'   => __( 'Table Row class', I18n::TEXTDOMAIN ),
      'extended'  => true,

      'default_placeholder' => true
    ) );

    /** Field: Cell Classes */
    $this->addField( 'cell_classes', array(
      'type'      => 'text',
      'caption'   => __( 'Table Cell class', I18n::TEXTDOMAIN ),
      'extended'  => true,

      'default_placeholder' => true
    ) );

    /** Field: Cell Heading Classes */
    $this->addField( 'cell_heading_classes', array(
      'type'      => 'text',
      'caption'   => __( 'Table Cell Heading class', I18n::TEXTDOMAIN ),
      'extended'  => true,

      'default_placeholder' => true
    ) );

    /** Field: Cell Periods Classes */
    $this->addField( 'cell_periods_classe', array(
      'type'      => 'text',
      'caption'   => __( 'Table Cell Periods class', I18n::TEXTDOMAIN ),
      'extended'  => true,

      'default_placeholder' => true
    ) );

    /** Field: Highlighted Period Class */
    $this->addField( 'highlighted_period_class', array(
      'type'      => 'text',
      'caption'   => __( 'Highlighted Period class', I18n::TEXTDOMAIN ),
      'extended'  => true,

      'default_placeholder' => true
    ) );

    /** Field: Highlighted Day Class */
    $this->addField( 'highlighted_day_class', array(
      'type'      => 'text',
      'caption'   => __( 'Highlighted Day class', I18n::TEXTDOMAIN ),
      'extended'  => true,

      'default_placeholder' => true
    ) );

    /** Field: Table Id Prefix */
    $this->addField( 'table_id_prefix', array(
      'type'      => 'text',
      'caption'   => __( 'Table ID Prefix', I18n::TEXTDOMAIN ),
      'extended'  => true,

      'default_placeholder' => true
    ) );

	  /** Field: Time Format */
	  $this->addField( 'time_format', array(
		  'type'      => 'text',
		  'caption'   => __( 'PHP Time Format', I18n::TEXTDOMAIN ),
		  'extended'  => true,
		  'description'   => sprintf( '<a href="http://bit.ly/16Wsegh" target="blank">%s</a>', __( 'More about PHP date and time formats.', I18n::TEXTDOMAIN ) ),

		  'default_placeholder'   => true
	  ) );

  }

  /**
   *  Widget Content
   *
   *  @access     protected
   *  @param      array     $args
   *  @param      array     $instance
   */
  protected function widgetContent ( array $args, array $instance ) {

    echo OverviewShortcode::getInstance()->renderShortcode( array_merge( $args, $instance ) );

  }
}
?>
