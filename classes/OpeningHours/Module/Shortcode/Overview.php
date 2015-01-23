<?php
/**
 *  Opening Hours: Module: Shortcode: Overview
 */

namespace OpeningHours\Module\Shortcode;

use OpeningHours\Module\I18n;
use OpeningHours\Entity\Set as SetEntity;

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
      'set_ids'                   => array(),
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
      'table_id_prefix'           => 'op-table-set-'
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

    if ( is_string( $attributes[ 'set_ids' ] ) ) :
      $set_ids = explode( ',', $attributes[ 'set_ids' ] );

    elseif ( is_numeric( $attributes ) ) :
      $set_ids  = array( (int) $attributes[ 'set_ids' ] );

    elseif ( !is_array( $attributes[ 'set_ids' ] ) ) :
      add_admin_notice( sprintf( '<b>%s:</b> %s',
        __( 'Shortcode Opening Hours Overview', I18n::TEXTDOMAIN ),
        sprintf( __( 'Property %s not properly set.', I18n::TEXTDOMAIN ), 'set_ids' )
      ) );

      return;

    elseif ( is_array( $attributes[ 'set_ids' ] ) ) :
      $set_ids  = $attributes[ 'set_ids' ];

    endif;

    if ( !count( $set_ids ) or !is_array( $set_ids ) )
      return;

    foreach ( (array) $set_ids as $key => $id )
      $set_ids[ $key ]   = (int) $id;

    $sets   = SetEntity::getSetsFromPosts( $set_ids );

    if ( !count( $sets ) )
      return;

    $attributes[ 'sets' ]     = $sets;
    $attributes[ 'weekdays' ] = I18n::getWeekdaysNumeric();

    echo $this->renderShortcodeTemplate( $attributes );

  }

}
?>
