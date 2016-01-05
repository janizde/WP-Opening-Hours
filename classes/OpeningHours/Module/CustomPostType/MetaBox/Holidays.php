<?php

namespace OpeningHours\Module\CustomPostType\MetaBox;

use DateTime;
use OpeningHours\Entity\Holiday;
use OpeningHours\Module\I18n;
use OpeningHours\Module\OpeningHours as OpeningHoursModule;
use OpeningHours\Module\CustomPostType\Set;

use OpeningHours\Util\Persistence;
use WP_Post;

/**
 * Meta Box implementation for Holidays meta box
 *
 * @author      Jannik Portz
 * @package     OpeningHours\Module\CustomPostType\MetaBox
 */
class Holidays extends AbstractMetaBox {

	const ID = 'op_meta_box_holidays';
	const POST_TYPE = Set::CPT_SLUG;
	const TEMPLATE_PATH = 'meta-box/holidays.php';
	const TEMPLATE_PATH_SINGLE = 'ajax/op-set-holiday.php';
	const CONTEXT = 'advanced';
	const PRIORITY = 'core';

	const WP_NONCE_NAME = 'op-set-holidays-nonce';
	const WP_NONCE_ACTION = 'save_data';

	const HOLIDAYS_META_KEY = '_op_set_holidays';
	const GLOBAL_POST_KEY = 'opening-hours-holidays';

	/** @inheritdoc */
	public function registerMetaBox () {

		if ( !static::currentSetIsParent() )
			return;

		add_meta_box(
			static::ID,
			__( 'Holidays', I18n::TEXTDOMAIN ),
			array( get_called_class(), 'renderMetaBox' ),
			static::POST_TYPE,
			static::CONTEXT,
			static::PRIORITY
		);

	}

	/** @inheritdoc */
	public function renderMetaBox ( WP_Post $post ) {
		OpeningHoursModule::setCurrentSetId( $post->ID );
		$set = OpeningHoursModule::getCurrentSet();

		if ( count( $set->getHolidays() ) < 1 )
			$set->getHolidays()->append( Holiday::createDummyPeriod() );

		$variables = array(
			'holidays' => $set->getHolidays()
		);

		echo $this->renderTemplate( self::TEMPLATE_PATH, $variables, 'once' );
	}

	/** @inheritdoc */
	protected function saveData ( $post_id, WP_Post $post, $update ) {
		$config = $_POST[ static::GLOBAL_POST_KEY ];
		$holidays = $this->getHolidaysFromPostData( $config );
		$persistence = new Persistence( $post );
		$persistence->saveHolidays( $holidays );
	}

	/**
	 * Converts the post data to a Holiday array
	 *
	 * @param     array     $data     The POST data from the edit screen
	 *
	 * @return    Holiday[]           The Holiday array
	 */
	public function getHolidaysFromPostData ( array $data ) {
		$holidays = array();
		for ( $i = 0; $i < count( $data['name'] ); $i++ ) {
			try {
				$holiday = new Holiday( $data['name'][$i], new DateTime($data['dateStart'][$i]), new DateTime($data['dateEnd'][$i]) );
				$holidays[] = $holiday;
			} catch ( \InvalidArgumentException $e ) {
				trigger_error( sprintf( 'Holiday could not be saved due to: %s', $e->getMessage() ) );
			}
		}
		return $holidays;
	}
}