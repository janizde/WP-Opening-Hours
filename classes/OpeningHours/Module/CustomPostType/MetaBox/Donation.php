<?php

namespace OpeningHours\Module\CustomPostType\MetaBox;

use OpeningHours\Module\I18n;
use WP_Post;

/**
 * Meta Box implementation for donate meta box
 *
 * @author      Jannik Portz
 * @package     OpeningHours\Module\CustomPostType\MetaBox
 */
class Donation extends AbstractMetaBox {

	const TEMPLATE_PATH = 'meta-box/donation.php';

	const WP_NONCE_NAME = 'op_meta_box_donation';
	const WP_NONCE_ACTION = 'donate';

	public function __construct () {
		parent::__construct( 'op_meta_box_donation', __('Please Donate', I18n::TEXTDOMAIN), self::CONTEXT_SIDE, self::PRIORITY_HIGH );
	}

	/** @inheritdoc */
	public function renderMetaBox ( WP_Post $post ) {
		echo self::renderTemplate( self::TEMPLATE_PATH );
	}

	/** @inheritdoc */
	protected function saveData ( $post_id, WP_Post $post, $update ) {
		// Silence is golden
	}
}