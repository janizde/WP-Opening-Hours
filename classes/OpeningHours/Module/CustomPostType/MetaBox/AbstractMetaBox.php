<?php

namespace OpeningHours\Module\CustomPostType\MetaBox;

use OpeningHours\Module\AbstractModule;
use OpeningHours\Module\CustomPostType\Set;
use WP_Post;

/**
 * Abstraction for a Meta Box
 *
 * @author      Jannik Portz
 * @package     OpeningHours\Module\CustomPostType\MetaBox
 */
abstract class AbstractMetaBox extends AbstractModule {

	const WP_ACTION_ADD_META_BOXES = 'add_meta_boxes';
	const WP_ACTION_SAVE_POST = 'save_post';

	const WP_NONCE_NAME = 'op_custom_meta_box_name';
	const WP_NONCE_ACTION = 'op_custom_meta_box_action';

	const POST_TYPE = Set::CPT_SLUG;

	const PRIORITY_DEFAULT = 'default';
	const PRIORITY_HIGH = 'high';
	const PRIORITY_LOW = 'low';

	const CONTEXT_NORMAL = 'normal';
	const CONTEXT_SIDE = 'side';
	const CONTEXT_ADVANCED = 'advanced';

	/**
	 * The meta box id
	 * @var       string
	 */
	protected $id;

	/**
	 * The meta box name / title
	 * @var       string
	 */
	protected $name;

	/**
	 * The meta box context
	 * @var       string
	 */
	protected $context;

	/**
	 * The meta box priority
	 * @var       string
	 */
	protected $priority;

	public function __construct ( $id, $name, $context = self::CONTEXT_NORMAL, $priority = self::PRIORITY_DEFAULT ) {
		$this->id = $id;
		$this->name = $name;
		$this->context = $context;
		$this->priority = $priority;

		$this->registerHookCallbacks();
	}

	/** Registers Hook Callbacks */
	protected function registerHookCallbacks() {
		add_action( static::WP_ACTION_ADD_META_BOXES, array( $this, 'registerMetaBox' ), 10, 2 );
		add_action( static::WP_ACTION_SAVE_POST, array( $this, 'saveDataCallback' ), 10, 3 );
	}

	/**
	 * Callback for saving the meta box data.
	 *
	 * @param     int       $post_id  The current post's id
	 * @param     WP_Post   $post     The current post
	 * @param     bool      $update   Whether an existing post is updated (false if new post is created)
	 */
	public function saveDataCallback ( $post_id, WP_Post $post, $update ) {
		if ( $this->verifyNonce() === false )
			return;

		$this->saveData( $post_id, $post, $update );
	}

	/**
	 * Verifies WordPress nonce
	 * @return    bool      Whether the nonce is valid
	 */
	protected function verifyNonce () {
		if ( !array_key_exists( self::WP_NONCE_NAME, $_POST ) )
			return false;

		$nonceValue = $_POST[ static::WP_NONCE_NAME ];
		return wp_verify_nonce( $nonceValue, static::WP_NONCE_ACTION );
	}

	/** Prints the nonce field for the meta box */
	public static function nonceField() {
		wp_nonce_field( static::WP_NONCE_ACTION, static::WP_NONCE_NAME );
	}

	/**
	 * Determines current set and checks if it is a parent set
	 * @return    bool
	 * @todo      move somewhere else
	 */
	public function currentSetIsParent () {
		global $post;
		return !(bool) $post->post_parent;
	}

	/** Registers meta box with add_meta_box */
	public function registerMetaBox () {
		add_meta_box(
			$this->id,
			$this->name,
			array( $this, 'renderMetaBox' ),
			self::POST_TYPE,
			$this->context,
			$this->priority
		);
	}

	/**
	 * Renders the meta box content
	 * @param     WP_Post   $post     The current post
	 */
	abstract public function renderMetaBox ( WP_Post $post );

	/**
	 * Processes data when post ist updated or saved
	 *
	 * @param     int       $post_id  The current post's id
	 * @param     WP_Post   $post     The current post
	 * @param     bool      $update   Whether an existing post is updated (false if new post is created)
	 */
	abstract protected function saveData ( $post_id, WP_Post $post, $update );

}