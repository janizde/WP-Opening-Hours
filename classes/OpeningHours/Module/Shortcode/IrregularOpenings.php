<?php
/**
 * Opening Hours: Module: Shortcode: Irregular Openings
 */

namespace OpeningHours\Module\Shortcode;

use OpeningHours\Entity\Set;
use OpeningHours\Module\I18n;
use OpeningHours\Module\OpeningHours;

class IrregularOpenings extends AbstractShortcode {

	/**
	 * Init
	 *
	 * @access          protected
	 */
	protected function init() {

		$this->setShortcodeTag( 'op-irregular-openings' );

		$default_attributes = array(
			'title'             => null,
			'set_id'            => null,
			'highlight'         => false,
			'before_widget'     => null,
			'after_widget'      => null,
			'before_title'      => null,
			'after_title'       => null,
			'class_io'          => 'op-irregular-opening',
			'class_highlighted' => 'highlighted',
			'date_format'       => I18n::getDateFormat(),
			'time_format'       => I18n::getTimeFormat()
		);

		$this->setDefaultAttributes( $default_attributes );

		$this->setTemplatePath( 'shortcode/irregular-openings.php' );

	}

	/**
	 * Shortcode
	 *
	 * @access          public
	 *
	 * @param           array $attributes
	 */
	public function shortcode( array $attributes ) {

		$set_id = $attributes['set_id'];

		if ( ! is_numeric( $set_id ) ) {
			return;
		}

		$set = OpeningHours::getSet( $set_id );

		if ( ! $set instanceof Set ) {
			return;
		}

		$attributes['set']                = $set;
		$attributes['irregular_openings'] = $set->getIrregularOpenings();

		echo $this->renderShortcodeTemplate( $attributes );

	}


}