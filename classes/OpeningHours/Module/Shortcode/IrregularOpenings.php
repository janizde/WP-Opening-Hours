<?php

namespace OpeningHours\Module\Shortcode;

use OpeningHours\Entity\Set;
use OpeningHours\Module\I18n;
use OpeningHours\Module\OpeningHours;

/**
 * Shortcode implementation for a list of Irregular Openings
 *
 * @author      Jannik Portz
 * @package     OpeningHours\Module\Shortcode
 */
class IrregularOpenings extends AbstractShortcode {

	/** @inheritdoc */
	protected function init () {

		$this->setShortcodeTag( 'op-irregular-openings' );

		$this->defaultAttributes = array(
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

		$this->templatePath = 'shortcode/irregular-openings.php';

	}

	/** @inheritdoc */
	public function shortcode ( array $attributes ) {
		$setId = $attributes['set_id'];

		if ( !is_numeric( $setId ) )
			return;

		$set = OpeningHours::getSet( $setId );

		if ( !$set instanceof Set )
			return;

		$attributes['set'] = $set;
		$attributes['irregular_openings'] = $set->getIrregularOpenings();

		echo $this->renderShortcodeTemplate( $attributes );
	}
}