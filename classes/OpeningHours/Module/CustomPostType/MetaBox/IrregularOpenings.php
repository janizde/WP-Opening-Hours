<?php

namespace OpeningHours\Module\CustomPostType\MetaBox;

use OpeningHours\Entity\IrregularOpening;
use OpeningHours\Module\CustomPostType\Set;
use OpeningHours\Module\I18n;
use OpeningHours\Module\OpeningHours as OpeningHoursModule;

use WP_Post;

/**
 * Meta Box implementation for Holidays meta box
 *
 * @author      Jannik Portz
 * @package     OpeningHours\Module\CustomPostType\MetaBox
 */
class IrregularOpenings extends AbstractMetaBox {

	const ID = 'op_meta_box_irregular_openings';
	const POST_TYPE = Set::CPT_SLUG;
	const TEMPLATE_PATH = 'meta-box/irregular-openings.php';
	const TEMPLATE_PATH_SINGLE = 'ajax/op-set-irregular-opening.php';
	const CONTEXT = 'advanced';
	const PRIORITY = 'core';

	const WP_NONCE_NAME = 'op-set-irregular-opening-nonce';
	const WP_NONCE_ACTION = 'save_data';

	const IRREGULAR_OPENINGS_META_KEY = '_op_set_irregular_openings';

	const GLOBAL_POST_KEY = 'opening-hours-irregular-openings';

	/** @inheritdoc */
	public function registerMetaBox () {
		if ( !$this->currentSetIsParent() )
			return;

		add_meta_box(
			static::ID,
			__( 'Irregular Openings', I18n::TEXTDOMAIN ),
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

		if ( count( $set->getIrregularOpenings() ) < 1 )
			$set->getIrregularOpenings()->append( IrregularOpening::createDummy() );

		$variables = array(
			'irregular_openings' => $set->getIrregularOpenings()
		);

		echo self::renderTemplate( static::TEMPLATE_PATH, $variables, 'once' );
	}

	/** @inheritdoc */
	protected function saveData ( $post_id, WP_Post $post, $update ) {
		$config = $_POST[ static::GLOBAL_POST_KEY ];
		$config = static::filterPostConfig( $config );

		if ( !is_array( $config ) or count( $config ) < 1 )
			return;

		global $post;

		update_post_meta( $post->ID, static::IRREGULAR_OPENINGS_META_KEY, $config );
	}

	/** @inheritdoc */
	public function filterPostConfig ( array $config ) {
		$newConfig = array();
		for ( $i = 0; $i < count( $config['name'] ); $i ++ ) {
			if ( !isset( $config['name'][$i] ) or empty( $config['name'][$i] ) )
				continue;

			if ( !isset( $config['date'][$i] ) or preg_match( I18n::STD_DATE_FORMAT_REGEX, $config['date'][$i] ) === false )
				continue;

			if ( !isset( $config['timeStart'][$i] ) or preg_match( I18n::STD_TIME_FORMAT_REGEX, $config['timeStart'][$i] ) === false )
				continue;

			if ( !isset( $config['timeEnd'][$i] ) or preg_match( I18n::STD_TIME_FORMAT_REGEX, $config['timeEnd'][$i] ) === false )
				continue;

			$newConfig[] = array(
				'name'      => $config['name'][ $i ],
				'date'      => $config['date'][ $i ],
				'timeStart' => $config['timeStart'][ $i ],
				'timeEnd'   => $config['timeEnd'][ $i ],
				'dummy'     => false
			);
		}

		return $newConfig;
	}
}