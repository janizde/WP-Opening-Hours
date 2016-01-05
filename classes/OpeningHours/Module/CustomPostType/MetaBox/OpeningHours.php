<?php

namespace OpeningHours\Module\CustomPostType\MetaBox;

use OpeningHours\Entity\Period;
use OpeningHours\Entity\Set as SetEntity;
use OpeningHours\Module\I18n;
use OpeningHours\Module\CustomPostType\Set;
use OpeningHours\Module\OpeningHours as OpeningHoursModule;

use OpeningHours\Util\Persistence;
use WP_Post;

/**
 * Meta Box implementation for regular Opening Hours
 *
 * @author      Jannik Portz
 * @package     OpeningHours\Module\CustomPostType\MetaBox
 */
class OpeningHours extends AbstractMetaBox {

	const ID = 'op_meta_box_opening_hours';
	const POST_TYPE = Set::CPT_SLUG;
	const TEMPLATE_PATH = 'op-set-meta-box.php';
	const CONTEXT = 'advanced';
	const PRIORITY = 'high';

	const WP_NONCE_NAME = 'op-set-opening-hours';
	const WP_NONCE_ACTION = 'save_data';

	const PERIODS_META_KEY = '_op_set_periods';

	/** @inheritdoc */
	public function registerMetaBox () {
		add_meta_box(
			static::ID,
			__( 'Opening Hours', I18n::TEXTDOMAIN ),
			array( get_called_class(), 'renderMetaBox' ),
			static::POST_TYPE,
			static::CONTEXT,
			static::PRIORITY
		);
	}

	/** @inheritdoc */
	public function renderMetaBox ( WP_Post $post ) {
		if ( !OpeningHoursModule::getSets()->offsetExists( $post->ID ) )
			OpeningHoursModule::getSets()->offsetSet( $post->ID, new SetEntity( $post->ID ) );

		OpeningHoursModule::setCurrentSetId( $post->ID );
		$set = OpeningHoursModule::getCurrentSet();
		$set->addDummyPeriods();

		$variables = array(
			'post' => $post,
			'set'  => $set
		);

		echo static::renderTemplate( static::TEMPLATE_PATH, $variables, 'once' );
	}

	/** @inheritdoc */
	protected function saveData ( $post_id, WP_Post $post, $update ) {
		$config = $_POST['opening-hours'];
		$periods = $this->getPeriodsFromPostData( $config );
		$persistence = new Persistence( $post );
		$persistence->savePeriods( $periods );
	}

	public function getPeriodsFromPostData ( array $data ) {
		$periods = array();

		foreach ( $data as $weekday => $dayConfig ) {
			for ( $i = 0; $i <= count( $dayConfig['start'] ); $i ++ ) {
				if ( empty( $dayConfig['start'][$i] ) or empty( $dayConfig['end'][$i] ) )
					continue;

				if ( $dayConfig['start'][$i] === '00:00' and $dayConfig['end'][$i] === '00:00' )
					continue;

				try {
					$period = new Period( $weekday, $dayConfig['start'][$i], $dayConfig['end'][$i] );
					$periods[] = $period;
				} catch ( \InvalidArgumentException $e ) {
					trigger_error( sprintf( 'Period could not be saved due to: %s', $e->getMessage() ) );
				}
			}
		}

		return $periods;
	}

}