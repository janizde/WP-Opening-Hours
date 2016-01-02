<?php

namespace OpeningHours\Module\Shortcode;

use OpeningHours\Entity\Set;
use OpeningHours\Module\I18n;
use OpeningHours\Module\OpeningHours;
use OpeningHours\Util\Dates;

/**
 * Shortcode implementation for a list of Holidays
 *
 * @author      Jannik Portz
 * @package     OpeningHours\Module\Shortcode
 */
class Holidays extends AbstractShortcode {

	/** @inheritdoc */
	protected function init() {
		$this->setShortcodeTag( 'op-holidays' );

		$this->defaultAttributes = array(
			'title'             => null,
			'set_id'            => null,
			'highlight'         => false,
			'before_widget'     => null,
			'after_widget'      => null,
			'before_title'      => null,
			'after_title'       => null,
			'class_holiday'     => 'op-holiday',
			'class_highlighted' => 'highlighted',
			'date_format'       => Dates::getDateFormat()
		);

		$this->templatePath = 'shortcode/holidays.php';
	}

	/** @inheritdoc */
	public function shortcode( array $attributes ) {
		$setId = $attributes['set_id'];

		if ( !is_numeric( $setId ) )
			return;

		$set = OpeningHours::getSet( $setId );

		if ( !$set instanceof Set )
			return;

		$attributes['set'] = $set;
		$attributes['holidays'] = $set->getHolidays();
		echo $this->renderShortcodeTemplate( $attributes );
	}

}