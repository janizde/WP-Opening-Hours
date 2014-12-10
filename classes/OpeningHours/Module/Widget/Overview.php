<?php
/**
 *  Opening Hours: Module: Widget: Overview
 */

namespace OpeningHours\Module\Widget;

use OpeningHours\Module\I18n;
use OpeningHours\Module\OpeningHours;

class Overview extends AbstractWidget {

  const SHORTCODE   = 'op-overview';

  /**
   *  Init
   *
   *  @access     protected
   */
  protected function init () {

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

    /** Field: Title */
    $this->addField( 'title', array(
      'type'    => 'text',
      'caption' => __( 'Title', I18n::TEXTDOMAIN )
    ) );

    /** Field: Sets */
    $this->addField( 'set_ids', array(
      'type'    => 'select-multi',
      'caption' => __( 'Sets to show', I18n::TEXTDOMAIN ),
      'options' => array( 'OpeningHours\Module\OpeningHours', 'getSetsOptions' ),

      'options_strategy'  => 'callback',
      'description'       => __( 'You may select multiple Sets by holding down the cmd/ctrl key.', I18n::TEXTDOMAIN )
    ) );

    /** Field: Show Closed Days */
    $this->addField( 'show_closed_days', array(
      'type'    => 'checkbox',
      'caption' => __( 'Show closed days.', I18n::TEXTDOMAIN )
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

    $this->renderShortcode( self::SHORTCODE, $args, $instance );

  }



}
?>
