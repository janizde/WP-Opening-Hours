<?php

namespace OpeningHours\Module\Widget;

use OpeningHours\Module\AbstractModule;
use OpeningHours\Module\Shortcode\AbstractShortcode;

use WP_Widget;
use InvalidArgumentException;

/**
 * Class responsible for field markup in Widgets
 *
 * @author      Jannik Portz
 * @package     OpeningHours\Module\Widget
 */
class FieldRenderer extends AbstractModule {

	/**
	 * Valid Field Types
	 * sequential array of strings w/ valid field types
	 *
	 * @var       array
	 */
	protected static $validFieldTypes = array(
		'text',
		'date',
		'time',
		'email',
		'url',
		'textarea',
		'select',
		'select-multi',
		'checkbox'
	);

	/**
	 * Options Field Types
	 * sequential array of strings w/ field types that support options attribute
	 *
	 * @var       array
	 */
	protected static $optionsFieldTypes = array( 'select', 'select-multi' );

	/**
	 * Renders the widget form field and returns markup as string
	 *
	 * @param     AbstractWidget $widget The widget to render the field for
	 * @param     array $instance The current widget instance
	 * @param     string $fieldName The name of the field to render
	 *
	 * @return    string                      The field markup
	 */
	public static function renderField( AbstractWidget $widget, array $instance, $fieldName ) {
		$field          = $widget->getField( $fieldName );
		$field['value'] = $instance[ $fieldName ];

		try {
			$field = self::validateField( $field, $widget );
		} catch ( InvalidArgumentException $e ) {
			add_notice( $e->getMessage(), 'error' );

			return '';
		}

		extract( $field );

		/**
		 * Variables defined by extract( $field )
		 *
		 * @var     $name     string
		 * @var     $type     string
		 * @var     $wp_id    string
		 * @var     $wp_name  string
		 * @var     $value    mixed
		 * @var     $options  array
		 * @var     $caption  string
		 */
		$placeholder = ( isset( $default_placeholder ) and $default_placeholder === true and $widget->getShortcode() instanceof AbstractShortcode )
			? 'placeholder="' . $widget->getShortcode()->getDefaultAttribute( $name ) . '"'
			: null;

		ob_start();

		/** Start of Field Element */
		echo '<p>';

		/** Field Label */
		if ( isset( $caption ) and ! empty( $caption ) and $type != 'checkbox' ) {
			echo '<label for="' . $wp_id . '">' . $caption . '</label>';
		}

		switch ( $type ) {

			/** Field Types: text, date, time, 'email', 'url' */
			case 'text' :
			case 'date' :
			case 'time' :
			case 'email' :
			case 'url' :
				echo '<input class="widefat" type="' . $type . '" id="' . $wp_id . '" name="' . $wp_name . '" value="' . $value . '" ' . $placeholder . ' />';
				break;

			/** Field Type: textarea */
			case 'textarea' :
				echo '<textarea class="widefat" id="' . $wp_id . '" name="' . $wp_name . '" ' . $placeholder . '>' . $value . '</textarea>';
				break;

			/** Field Types: select, select-multi */
			case 'select' :
			case 'select-multi' :
				$is_multi = ( $type == 'select-multi' );

				$multi   = ( $is_multi ) ? 'multiple="multiple"' : null;
				$size    = ( $is_multi ) ? 5 : 1;
				$wp_name = ( $is_multi ) ? $wp_name . '[]' : $wp_name;
				$style   = ( $is_multi ) ? 'style="height: 50px;"' : null;

				echo '<select class="widefat" id="' . $wp_id . '" name="' . $wp_name . '" size="' . $size . '" ' . $style . ' ' . $multi . '>';

				foreach ( $options as $key => $caption ) :

					$selected = 'selected="selected"';

					if ( $is_multi ) :
						$selected = ( in_array( $key, (array) $value ) ) ? $selected : null;

					else :
						$selected = ( $key == $value ) ? $selected : null;

					endif;

					echo '<option value="' . $key . '" ' . $selected . '>' . $caption . '</option>';
				endforeach;

				echo '</select>';
				break;

			/** Field Type: checkbox (single) */
			case 'checkbox' :
				$checked = ( $value !== null ) ? 'checked="checked"' : null;

				echo '<label for="' . $wp_id . '">';
				echo '<input type="checkbox" name="' . $wp_name . '" id="' . $wp_id . '" ' . $checked . ' />';
				echo $caption;
				echo '</label>';
				break;
		}

		if ( isset( $description ) and is_string( $description ) ) {
			echo '<span class="op-widget-description">' . $description . '</span>';
		}

		echo '</p>';

		$output = ob_get_contents();
		ob_clean();

		return $output;
	}

	/**
	 * Validates the field and filters widget
	 *
	 * @param     array $field The field options
	 * @param     AbstractWidget $widget The widget object
	 * @param     array $instance The current widget instance
	 *
	 * @return    array                     The filtered field options
	 *
	 * @throws    InvalidArgumentException  On validation error
	 */
	public static function validateField( array $field, AbstractWidget $widget, array $instance ) {
		if ( count( $field ) < 1 ) {
			self::terminate( sprintf( __( 'Field configuration has to be array. %s given', self::TEXTDOMAIN ), gettype( $field ) ), $widget );
		}

		if ( empty( $field['name'] ) or ! is_string( $field['name'] ) ) {
			self::terminate( __( 'Field name is empty or not a string.', self::TEXTDOMAIN ), $widget );
		}

		if ( ! isset( $field['type'] ) ) {
			self::terminate( sprintf( __( 'No Type option set for field %s.', self::TEXTDOMAIN ), '<b>' . $field['name'] . '</b>' ), $widget );
		}

		if ( ! in_array( $field['type'], self::$validFieldTypes ) ) {
			self::terminate( sprintf( __( 'Field type %s provided for field %s is not a valid type.', '<b>' . $field['type'] . '</b>', '<b>' . $field['name'] . '</b>' ) ), $widget );
		}

		$supports_options = in_array( $field['type'], self::$optionsFieldTypes );

		if ( $supports_options and ( ! isset( $field['options'] ) or ! is_array( $field['options'] ) ) ) {
			self::terminate( sprintf( __( 'Field %s with field type select, required the options array.', self::TEXTDOMAIN ), $field['name'] ), $widget );
		}

		$field['value']   = $instance[ $field['name'] ];
		$field['wp_id']   = $widget->get_field_id( $field['name'] );
		$field['wp_name'] = $widget->get_field_name( $field['name'] );

		if ( $supports_options and isset( $field['options_strategy'] ) and $field['options_strategy'] == 'callback' and is_callable( $field['options'] ) ) {
			$field['options'] = call_user_func( $field['options'] );
		}

		$field = apply_filters( 'op_widget_field', $field );
		$field = apply_filters( 'op_widget_' . $widget->getWidgetId() . '_field', $field );

		return $field;
	}

	/**
	 * Adds error notice and throws exception
	 *
	 * @param     string $message The message to display
	 * @param     AbstractWidget $widget The widget object
	 *
	 * @throws    InvalidArgumentException
	 */
	public static function terminate( $message, AbstractWidget $widget ) {
		$notice = '<b>' . $widget->getTitle() . ':</b>' . $message;
		throw new InvalidArgumentException( $notice );
	}
}