<?php

namespace OpeningHours\Module\CustomPostType\MetaBox;

use OpeningHours\Entity\Holiday;
use OpeningHours\Module\I18n;
use OpeningHours\Module\OpeningHours as OpeningHoursModule;
use OpeningHours\Module\CustomPostType\Set;

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
		$config = $this->filterPostConfig( $config );

		if ( !is_array( $config ) or count( $config ) < 1 )
			return;

		global $post;
		update_post_meta( $post->ID, static::HOLIDAYS_META_KEY, $config );
	}

	/**
	 * Filters the Config for Holidays
	 *
	 * @param     array     $config   The config to filter
	 *
	 * @return    array               The filtered config
	 */
	public function filterPostConfig ( array $config ) {
		$newConfig = array();
		for ( $i = 0; $i < count( $config['name'] ); $i ++ ) {
			if ( !isset( $config['name'][$i] ) or empty( $config['name'][$i] ) )
				continue;

			if ( !isset( $config['dateStart'][$i] ) or !preg_match( I18n::STD_DATE_FORMAT_REGEX, $config['dateStart'][$i] ) )
				continue;

			if ( !isset( $config['dateEnd'][$i] ) or !preg_match( I18n::STD_DATE_FORMAT_REGEX, $config['dateEnd'][$i] ) )
				continue;

			$newConfig[] = array(
				'name'      => $config['name'][ $i ],
				'dateStart' => $config['dateStart'][ $i ],
				'dateEnd'   => $config['dateEnd'][ $i ]
			);
		}

		return $newConfig;
	}

}