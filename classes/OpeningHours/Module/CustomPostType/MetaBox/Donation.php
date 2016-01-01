<?php

namespace OpeningHours\Module\CustomPostType\MetaBox;

use OpeningHours\Module\CustomPostType\Set;
use OpeningHours\Module\I18n;

use WP_Post;

/**
 * Meta Box implementation for donate meta box
 *
 * @author      Jannik Portz
 * @package     OpeningHours\Module\CustomPostType\MetaBox
 */
class Donation extends AbstractMetaBox {

	const ID = 'op_meta_box_donation';
	const CONTEXT = 'side';
	const PRIORITY = 'high';
	const POST_TYPE = Set::CPT_SLUG;

	const TEMPLATE_PATH = 'meta-box/donation.php';

	const WP_NONCE_NAME = 'op_meta_box_donation';
	const WP_NONCE_ACTION = 'donate';

	/** @inheritdoc */
	public function registerMetaBox() {
		add_meta_box(
			static::ID,
			__( 'Please Donate', I18n::TEXTDOMAIN ),
			array( get_called_class(), 'renderMetaBox' ),
			static::POST_TYPE,
			static::CONTEXT,
			static::PRIORITY
		);
	}

	/** @inheritdoc */
	public function renderMetaBox ( WP_Post $post ) {
		echo static::renderTemplate( static::TEMPLATE_PATH );
	}

	/** @inheritdoc */
	protected function saveData ( $post_id, WP_Post $post, $update ) {
		// Silence is golden
	}
}