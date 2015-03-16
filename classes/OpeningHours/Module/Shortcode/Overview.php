<?php
/**
 *  Opening Hours: Module: Shortcode: Overview
 */

namespace OpeningHours\Module\Shortcode;

use OpeningHours\Entity\Set;
use OpeningHours\Module\I18n;
use OpeningHours\Module\OpeningHours;

class Overview extends AbstractShortcode {

  /**
   *  Init
   *
   *  @access     protected
   */
  protected function init () {

    $this->setShortcodeTag( 'op-overview' );

    $default_attributes   = array(
      'before_title'              => '<h3 class="op-overview-title">',
      'after_title'               => '</h3>',
      'before_widget'             => '<div class="op-overview-shortcode">',
      'after_widget'              => '</div>',
      'set_id'                    => 0,
      'title'                     => null,
      'show_closed_days'          => false,
      'show_description'          => true,
      'highlight'                 => 'nothing',
      'compress'                  => false,
      'short'                     => false,
      'caption_closed'            => __( 'Closed', I18n::TEXTDOMAIN ),
      'table_classes'             => null,
      'row_classes'               => null,
      'cell_classes'              => null,
      'cell_heading_classes'      => null,
      'cell_periods_classes'      => null,
      'cell_description_classes'  => 'op-set-description',
      'highlighted_period_class'  => 'highlighted',
      'highlighted_day_class'     => 'highlighted',
      'table_id_prefix'           => 'op-table-set-',
	    'time_format'               => I18n::getTimeFormat()
    );

    $this->setDefaultAttributes( $default_attributes );

    $valid_attribute_values = array(
      'highlight'         => array( 'nothing', 'period', 'day' ),
      'show_closed_day'   => array( false, true ),
      'show_description'  => array( true, false )
    );

    $this->setValidAttributeValues( $valid_attribute_values );

    $this->setTemplatePath( 'shortcode/overview.php' );

  }

  /**
   *  Shortcode
   *
   *  @access       public
   *  @param        array     $attributes
   */
  public function shortcode ( array $attributes ) {

	  extract( $attributes );

	  if ( !isset( $set_id ) or !is_numeric( $set_id ) or $set_id == 0 ) :
		  trigger_error( "Set id not properly set in Opening Hours Overview shortcode" );
	    return;
	  endif;

    $set_id   = (int) $set_id;

    $set      = OpeningHours::getSet( $set_id );

	  if ( !$set instanceof Set ) :
		  trigger_error( sprintf( "Set with id %d does not exist", $set_id ) );
	    return;
	  endif;

    $attributes[ 'set' ]      = $set;
    $attributes[ 'weekdays' ] = I18n::getWeekdaysNumeric();

    echo $this->renderShortcodeTemplate( $attributes );

  }

}
?>
