<?php
/**
 *  Opening Hours: Module: Shortcode: IsOpen
 */

namespace OpeningHours\Module\Shortcode;

use OpeningHours\Module\OpeningHours;
use OpeningHours\Module\I18n;
use OpeningHours\Entity\Set;
use OpeningHours\Entity\Period;

class IsOpen extends AbstractShortcode {

	/**
	 *  Init
	 *
	 * @access     protected
	 */
	protected function init() {

		$this->setShortcodeTag( 'op-is-open' );

		$default_attributes = array(
			'set_id'              => null,
			'open_text'           => __( 'We\'re currently open.', self::TEXTDOMAIN ),
			'closed_text'         => __( 'We\'re currently closed.', self::TEXTDOMAIN ),
			'show_next'           => false,
			'next_format'         => __( 'We\'re open again on %2$s (%1$s) from %3$s to %4$s', self::TEXTDOMAIN ),
			'before_widget'       => null,
			'after_widget'        => null,
			'before_title'        => null,
			'after_title'         => null,
			'title'               => null,
			'classes'             => null,
			'next_period_classes' => null,
			'open_class'          => 'op-open',
			'closed_class'        => 'op-closed'
		);

		$this->setDefaultAttributes( $default_attributes );

		$valid_attribute_values = array(
			'show_next' => array( false, true )
		);

		$this->setValidAttributeValues( $valid_attribute_values );

		$this->setTemplatePath( 'shortcode/is-open.php' );

	}

	/**
	 *  Shortcode
	 *
	 * @access     public
	 *
	 * @param      array $attributes
	 */
	public function shortcode( array $attributes ) {

		$set_id = $attributes['set_id'];

		if ( $set_id === null or ! is_numeric( $set_id ) or $set_id <= 0 ) {
			return;
		}

		$set = OpeningHours::getSet( $set_id );

		if ( ! $set instanceof Set ) {
			return;
		}

		$is_open = $set->isOpen();

		$next_period = $set->getNextOpenPeriod();

		if ( $attributes['show_next'] and $next_period instanceof Period ) :

			$attributes['next_period'] = $next_period;

			$weekdays = I18n::getWeekdaysNumeric();

			$attributes['next_string'] = sprintf(
			// Format String
				$attributes['next_format'],

				// 1$: Formatted Date
				$next_period->getTimeStart()->format( I18n::getDateFormat() ),

				// 2$: Translated Weekday
				$weekdays[ $next_period->getWeekday() ],

				// 3%: Formatted Start Time
				$next_period->getTimeStart()->format( I18n::getTimeFormat() ),

				// 4%: Formatted End Time
				$next_period->getTimeEnd()->format( I18n::getTimeFormat() )
			);

		endif;

		$attributes['is_open'] = $is_open;
		$attributes['classes'] .= ( $is_open ) ? $attributes['open_class'] : $attributes['closed_class'];
		$attributes['text']        = ( $is_open ) ? $attributes['open_text'] : $attributes['closed_text'];
		$attributes['next_period'] = $set->getNextOpenPeriod();

		echo $this->renderShortcodeTemplate( $attributes );

	}

}
