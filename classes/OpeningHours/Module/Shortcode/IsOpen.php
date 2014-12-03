<?php
/**
 *  Opening Hours: Module: Shortcode: IsOpen
 */

namespace OpeningHours\Module\Shortcode;

use OpeningHours\Module\OpeningHours;
use OpeningHours\Entity\Set;

class IsOpen extends AbstractShortcode {

  /**
   *  Init
   *
   *  @access     protected
   */
  protected function init() {

    $this->setShortcodeTag( 'op-is-open' );

    $default_attributes   = array(
      'set_id'        => null,
      'open_text'     => __( 'We\'re currently open.', self::TEXTDOMAIN ),
      'closed_text'   => __( 'We\'re currently closed.', self::TEXTDOMAIN ),
      'before_widget' => null,
      'after_widget'  => null,
      'before_title'  => null,
      'after_title'   => null,
      'title'         => null
    );

    $this->setDefaultAttributes( $default_attributes );

    $this->setTemplatePath( 'shortcode/is-open.php' );

  }

  /**
   *  Shortcode
   *
   *  @access     public
   *  @param      array     $attributes
   */
  public function shortcode ( array $attributes ) {

    $set_id   = $attributes[ 'set_id' ];

    if ( $set_id === null or !is_numeric( $set_id ) or $set_id <= 0 )
      return;

    $set  = OpeningHours::getSet( $set_id );

    if ( !$set instanceof Set )
      return;

    $is_open    = $set->isOpen();

    $attributes[ 'is-open' ]    = $is_open;
    $attributes[ 'classes' ]    = ( $is_open ) ? 'op-open' : 'op-closed';
    $attributes[ 'text' ]       = ( $is_open ) ? $attributes[ 'open_text' ] : $attributes[ 'closed_text' ];

    echo $this->renderShortcodeTemplate( $attributes );

  }

}
