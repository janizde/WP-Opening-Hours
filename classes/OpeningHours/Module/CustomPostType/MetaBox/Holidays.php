<?php

namespace OpeningHours\Module\CustomPostType\MetaBox;

use DateTime;
use OpeningHours\Entity\Holiday;
use OpeningHours\Module\I18n;
use OpeningHours\Module\OpeningHours as OpeningHoursModule;
use OpeningHours\Util\Persistence;
use OpeningHours\Util\ViewRenderer;
use WP_Post;

/**
 * Meta Box implementation for Holidays meta box
 *
 * @author      Jannik Portz
 * @package     OpeningHours\Module\CustomPostType\MetaBox
 */
class Holidays extends AbstractMetaBox {

	const TEMPLATE_PATH = 'views/meta-box/holidays.php';
	const TEMPLATE_PATH_SINGLE = 'views/ajax/op-set-holiday.php';

	const POST_KEY = 'opening-hours-holidays';

	public function __construct () {
		parent::__construct( 'op_meta_box_holidays', __('Holidays', I18n::TEXTDOMAIN), self::CONTEXT_ADVANCED, self::PRIORITY_HIGH );
	}

	/** @inheritdoc */
	public function registerMetaBox () {
		if ( !$this->currentSetIsParent() )
			return;

		parent::registerMetaBox();
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

		$vr = new ViewRenderer( op_plugin_path() . self::TEMPLATE_PATH, $variables );
		$vr->render();
	}

	/** @inheritdoc */
	protected function saveData ( $post_id, WP_Post $post, $update ) {
		$config = $_POST[ self::POST_KEY ];
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