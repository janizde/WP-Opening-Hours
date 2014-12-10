<?php
/**
 *  Opening Hours: Module: Widget: FieldRenderer
 *
 *  Module class with methods to render widget form fields.
 */

namespace OpeningHours\Module\Widget;

use OpeningHours\Module\AbstractModule;

use WP_Widget;
use InvalidArgumentException;

class FieldRenderer extends AbstractModule {

  /**
   *  Valid Field Types
   *  sequencial array of strings w/ valid field types
   *
   *  @access     protected
   *  @static
   *  @type       array
   */
  protected static $validFieldTypes = array( 'text', 'date', 'time', 'email', 'url', 'textarea', 'select', 'select-multi', 'checkbox' );

  /**
   *  Options Field Types
   *  sequencial array of strings w/ field types that support options attribute
   *
   *  @access     protected
   *  @static
   *  @type       array
   */
  protected static $optionsFieldTypes = array( 'select', 'select-multi' );

  /**
   *  Render Field
   *  renders the widget form field and returns markup as string
   *
   *  @access     public
   *  @static
   *  @param      WP_Widget   $widget
   *  @param      string      $field_name
   */
  public static function renderField ( WP_Widget $widget, $field_name ) {

    $field  = $widget->getField( $field_name );

    try {
      $field  = self::validateField( $field, $widget );

    } catch ( InvalidArgumentException $e ) {
      \add_admin_notice( $e->getMessage(), 'error' );
      return;

    }

    extract( $field );

    ob_start();

    /** Start of Field Element */
    echo '<p>';

      /** Field Label */
      if ( isset( $caption ) and !empty( $caption ) and $type != 'checkbox' )
        echo '<label for="'. $wp_id .'">' . $caption . '</label>';

      switch ( $type ) :

        /** Field Types: text, date, time, 'email', 'url' */
        case 'text' :
        case 'date' :
        case 'time' :
        case 'email' :
        case 'url' :
          echo '<input class="widefat" type="'. $type .'" id="'. $wp_id .'" name="'. $wp_name .'" value="'. $value .'" />';
          break;

        /** Field Type: textarea */
        case 'textarea' :
          echo '<textarea class="widefat" id="'. $wp_id .'" name="'. $wp_name .'">' . $value . '</textarea>';
          break;

        /** Field Types: select, select-multi */
        case 'select' :
        case 'select-multi' :
          $is_multi   = ( $type == 'select-multi' );

          $multi      = ( $is_multi ) ? 'multiple="multiple"' : null;
          $size       = ( $is_multi ) ? 5 : 1;
          $wp_name    = ( $is_multi ) ? $wp_name . '[]' : $wp_name;
          $style      = ( $is_multi ) ? 'style="height: 50px;"' : null;

          echo '<select class="widefat" id="'. $wp_id .'" name="'. $wp_name .'" size="'. $size .'" '. $style .'>';

          foreach ( $options as $key => $caption ) :

            $selected   = 'selected="selected"';
            $selected   = ( $is_multi and in_array( $key, (array) $value ) ) ? $selected : null;
            $selected   = ( !$is_multi and $key == $value ) ? $selected : null;

            echo '<option value="'. $key .'" '. $selected .'>'. $caption .'</option>';
          endforeach;

          echo '</select>';
          break;

          /** Field Type: checkbox (single) */
          case 'checkbox' :
            $checked  = ( $value !== null ) ? 'checked="checked"' : null;

            echo '<label for="'. $wp_id .'">';
            echo '<input type="checkbox" name="'. $wp_name .'" id="'. $wp_id .'" '. $checked .' />';
            echo $caption;
            echo '</label>';
            break;

      endswitch;

      if ( isset( $description ) and is_string( $description ) )
        echo '<span class="op-widget-description">'. $description .'</span>';

    echo '</p>';

    $output = ob_get_contents();

    ob_clean();

    return $output;

  }

  /**
   *  Validate Field
   *  validates and filters widget.
   *
   *  @access     public
   *  @static
   *  @param      array       $field
   *  @param      WP_Widget   $widget
   *  @throws     InvalidArgumentException
   *  @return     array
   */
  public static function validateField ( array $field, WP_Widget $widget ) {

    /**
     *  Validation
     */
    if ( !count( $field ) )
      self::terminate( sprintf( __( 'Field configuration has to be array. %s given', self::TEXTDOMAIN ), gettype( $field ) ), $widget );

    if ( empty( $field[ 'name' ] ) or !is_string( $field[ 'name' ] ) )
      self::terminate( __( 'Field name is empty or not a string.', self::TEXTDOMAIN ), $widget );

    if ( !isset( $field[ 'type' ] ) )
      self::terminate( sprintf( __( 'No Type option set for field %s.', self::TEXTDOMAIN ), '<b>' . $field[ 'name' ] . '</b>' ), $widget );

    if ( !in_array( $field[ 'type' ], self::getValidFieldTypes() ) )
      self::terminate( sprintf( __( 'Field type %s provided for field %s is not a valid type.', '<b>' . $field[ 'type' ] . '</b>', '<b>' . $field[ 'name' ] . '</b>' ) ), $widget );

    $supports_options   = in_array( $field[ 'type' ], self::getOptionsFieldTypes() );

    if ( $supports_options and ( !isset( $field[ 'options' ] ) or !is_array( $field[ 'options' ] ) ) )
      self::terminate( sprintf( __( 'Field %s with field type select, required the options array.', self::TEXTDOMAIN ), $field[ 'name' ] ), $widget );

    /**
     *  Filter
     */
    $instance   = $widget->getInstance();

    $field[ 'value' ]   = $instance[ $field[ 'name' ] ];
    $field[ 'wp_id' ]   = $widget->get_field_id( $field[ 'name' ] );
    $field[ 'wp_name' ] = $widget->get_field_name( $field[ 'name' ] );

    if ( $supports_options and isset( $field[ 'options_strategy' ] ) and $field[ 'options_strategy' ] == 'callback' and is_callable( $field[ 'options' ] ) )
      $field[ 'options' ]   = call_user_func( $field[ 'options' ] );

    $field      = apply_filters( 'op_widget_field', $field );
    $field      = apply_filters( 'op_widget_' . $widget->getWidgetId() . '_field', $field );

    return $field;

  }

  /**
   *  Terminate
   *  adds error admin notice and throws Exception
   *
   *  @access     protected
   *  @static
   *  @param      string      $message
   *  @param      WP_Widget   $widget
   *  @throws     InvalidArgumentException
   */
  public static function terminate ( $message, WP_Widget $widget ) {

    $notice   = '<b>' . $widget->getTitle() . ':</b>' . $message;

    throw new InvalidArgumentException( $notice );

  }

  /**
   *  Getter: Valid Field Types
   *
   *  @access     public
   *  @static
   *  @return     array
   */
  public static function getValidFieldTypes () {
    return self::$validFieldTypes;
  }

  /**
   *  Getter: Options Field Types
   *
   *  @access     public
   *  @static
   *  @return     array
   */
  public static function getOptionsFieldTypes () {
    return self::$optionsFieldTypes;
  }

}
?>
