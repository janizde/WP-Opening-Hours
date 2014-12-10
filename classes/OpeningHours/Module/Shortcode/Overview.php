<?php
/**
 *  Opening Hours: Module: Shortcode: Overview
 */

namespace OpeningHours\Module\Shortcode;

use OpeningHours\Module\I18n;
use OpeningHours\Module\CustomPostType\Set;
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
      'highlight'                 => 'nothing',
      'display_as'                => 'accordion',
      'table_classes'             => null,
      'row_classes'               => null,
      'cell_classes'              => null,
      'cell_heading_classes'      => null,
      'cell_periods_classes'      => null,
      'highlighted_period_class'  => 'highlighted',
      'highlighted_day_class'     => 'highlighted',
      'table_id_prefix'           => 'op-table-set-'
    );

    $this->setDefaultAttributes( $default_attributes );

    $valid_attribute_values = array(
      'highlight'         => array( 'nothing', 'period', 'day' ),
      'display_as'        => array( 'accordion', 'single-tables' ),
      'show_closed_day'   => array( false, true )
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

      foreach ( $set_ids as &$id )
        $id   = (int) $id;

      unset( $id );

    elseif ( is_numeric( $attributes ) ) :
      $set_ids  = array( (int) $attributes[ 'set_ids' ] );

    elseif ( !is_array( $attributes[ 'set_ids' ] ) ) :
      add_admin_notice( sprintf( '<b>%s:</b> %s',
        __( 'Shortcode Opening Hours Overview', I18n::TEXTDOMAIN ),
        sprintf( __( 'Property %s not properly set.', I18n::TEXTDOMAIN ), 'set_ids' )
      ) );

      return;

    endif;

    $sets   = SetEntity::getSetsFromPosts( $set_ids );

    if ( !count( $sets ) )
      return;

    $attributes[ 'sets' ]     = $sets;
    $attributes[ 'weekdays' ] = I18n::getWeekdaysNumeric();

    echo $this->renderShortcodeTemplate( $attributes );

  }

}
?>
