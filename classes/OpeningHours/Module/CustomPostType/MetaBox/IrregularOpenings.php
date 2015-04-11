<?php
/**
 * Opening Hours: Module: Custom Post Type: Meta Box: Irregular Openings
 */

namespace OpeningHours\Module\CustomPostType\MetaBox;

use OpeningHours\Entity\IrregularOpening;
use OpeningHours\Module\CustomPostType\Set;
use OpeningHours\Module\I18n;
use OpeningHours\Module\OpeningHours as OpeningHoursModule;

use WP_Post;

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

	/**
	 * Register Meta Box
	 *
	 * @access          public
	 * @static
	 */
	public static function registerMetaBox() {

		if ( !static::currentSetIsParent() )
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

	/**
	 * Render Meta Box
	 *
	 * @access          public
	 * @static
	 *
	 * @param           WP_Post $post
	 */
	public static function renderMetaBox( WP_Post $post ) {

		OpeningHoursModule::setCurrentSetId( $post->ID );

		$set = OpeningHoursModule::getCurrentSet();

		if ( ! count( $set->getIrregularOpenings() ) ) {
			$set->getIrregularOpenings()->addElement( IrregularOpening::createDummy() );
		}

		$variables = array(
			'irregular_openings' => $set->getIrregularOpenings()
		);

		echo static::renderTemplate( static::TEMPLATE_PATH, $variables, 'once' );

	}

	/**
	 * Save Data
	 *
	 * @access          protected
	 * @static
	 *
	 * @param           int $post_id
	 * @param           WP_Post $post
	 * @param           bool $update
	 */
	protected static function saveData( $post_id, WP_Post $post, $update ) {

		$config = $_POST[ static::GLOBAL_POST_KEY ];

		$config = static::filterPostConfig( $config );

		global $post;

		if ( is_array( $config ) and count( $config ) ) {
			update_post_meta( $post->ID, static::IRREGULAR_OPENINGS_META_KEY, $config );
		}

	}

	/**
	 * Filter Post Config
	 * filters config array passed via $_POST
	 *
	 * @access          public
	 * @static
	 *
	 * @param           array $config
	 *
	 * @return          array
	 */
	public static function filterPostConfig( array $config ) {

		$new_config = array();

		for ( $i = 0; $i < count( $config['name'] ); $i ++ ) :

			if (
				! isset( $config['name'][ $i ] )
				or empty( $config['name'][ $i ] )

				or ! isset( $config['date'][ $i ] )
				or ! preg_match( I18n::STD_DATE_FORMAT_REGEX, $config['date'][ $i ] )

				or ! isset( $config['timeStart'][ $i ] )
				or ! preg_match( I18n::STD_TIME_FORMAT_REGEX, $config['timeStart'][ $i ] )

				or ! isset( $config['timeEnd'][ $i ] )
				or ! preg_match( I18n::STD_TIME_FORMAT_REGEX, $config['timeEnd'][ $i ] )

			) :
				continue;
			endif;

			$new_config[] = array(
				'name'      => $config['name'][ $i ],
				'date'      => $config['date'][ $i ],
				'timeStart' => $config['timeStart'][ $i ],
				'timeEnd'   => $config['timeEnd'][ $i ],
				'dummy'     => false
			);

		endfor;

		return $new_config;

	}


}