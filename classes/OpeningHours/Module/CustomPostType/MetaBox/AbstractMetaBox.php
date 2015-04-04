<?php
/**
 * OpeningHours: Module: CustomPostType: MetaBox: AbstractMetaBox
 */

namespace OpeningHours\Module\CustomPostType\MetaBox;

use OpeningHours\Module\AbstractModule;

use WP_Post;

abstract class AbstractMetaBox extends AbstractModule {

	/**
	 * Constants
	 */
	const POST_TYPE = 'post';
	const TEMPLATE_PATH = null;
	const WP_ACTION_ADD_META_BOXES = 'add_meta_boxes';
	const WP_ACTION_SAVE_POST = 'save_post';

	const WP_NONCE_NAME = 'op_custom_meta_box_name';
	const WP_NONCE_ACTION = 'op_custom_meta_box_action';

	/**
	 * Constructor
	 *
	 * @access          public
	 */
	public function __construct() {

		static::registerHookCallbacks();

	}

	/**
	 * Register Hook Callbacks
	 * registers wp action hooks
	 *
	 * @access          protected
	 * @static
	 */
	protected static function registerHookCallbacks() {

		add_action( static::WP_ACTION_ADD_META_BOXES, array( get_called_class(), 'registerMetaBox' ) );
		add_action( static::WP_ACTION_SAVE_POST, array( get_called_class(), 'saveDataWrap' ), 10, 3 );

	}

	/**
	 * Save Data Wrap
	 * verifies WordPress nonce and calls child saveData method
	 *
	 * @access          public
	 * @static
	 *
	 * @param           int $post_id
	 * @param           WP_Post $post
	 * @param           bool $update
	 *
	 * @wp_hook         save_post
	 */
	public static function saveDataWrap( $post_id, WP_Post $post, $update ) {

		if ( static::verifyNonce() === false ) {
			return;
		}

		static::saveData( $post_id, $post, $update );

	}

	/**
	 * Verify Nonce
	 * verifies WordPress nonce
	 *
	 * @access          protected
	 * @static
	 * @return          bool
	 */
	protected static function verifyNonce() {

		global $_POST;

		return wp_verify_nonce(
			$_POST[ static::WP_NONCE_NAME ],
			static::WP_NONCE_ACTION
		);

	}

	/**
	 * Nonce Field
	 * echoes the nonce form input
	 *
	 * @access          public
	 * @static
	 */
	public static function nonceField() {

		wp_nonce_field(
			static::WP_NONCE_ACTION,
			static::WP_NONCE_NAME
		);

	}

	/**
	 * Register Meta Box
	 * registers meta box with add_meta_box
	 *
	 * @access          public
	 * @abstract
	 * @static
	 * @wp_action       add_meta_boxes
	 */
	abstract public static function registerMetaBox();

	/**
	 * Render Meta Box
	 * renders the meta box template/content
	 *
	 * @access          public
	 * @abstract
	 * @static
	 *
	 * @param           WP_Post $post
	 */
	abstract public static function renderMetaBox( WP_Post $post );

	/**
	 * Save Data
	 * processes data when post ist updated or saved
	 *
	 * @access          protected
	 * @abstract
	 * @static
	 *
	 * @param           int $post_id
	 * @param           WP_Post $post
	 * @param           bool $update
	 */
	abstract protected static function saveData( $post_id, WP_Post $post, $update );

}