<?php

namespace OpeningHours\Module;

use OpeningHours\Entity\Set;
use OpeningHours\Util\ArrayObject;
use OpeningHours\Entity\Set as SetEntity;
use OpeningHours\Module\CustomPostType\Set as SetCpt;

use WP_Screen;
use WP_Post;

/**
 * OpeningHours Module
 *
 * @author      Jannik Portz
 * @package     OpeningHours\Module
 */
class OpeningHours extends AbstractModule {

	/**
	 * Collection of all sets in the system
	 * @type      ArrayObject
	 */
	protected static $sets;

	/**
	 * Id of the current Set
	 * @type      int
	 */
	protected static $currentSetId;

	/** Constructor */
	public function __construct() {
		self::$sets = new ArrayObject();
		self::registerHookCallbacks();
	}

	/** Register Hook Callbacks */
	public function registerHookCallbacks () {
		add_filter( 'detail_fields_metabox_context', array( $this, 'modifyDetailFieldContext' ) );
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'current_screen', array( $this, 'initAdmin' ) );
	}

	/** Initializes all parent posts and loads children */
	public function init () {
		// Get all parent op-set posts
		$posts = get_posts( array(
			'post_type'   => SetCpt::CPT_SLUG,
			'post_parent' => 0,
			'numberposts' => - 1
		) );

		foreach ( $posts as $singlePost )
			self::$sets->offsetSet( $singlePost->ID, new SetEntity( $singlePost ) );

		self::initCurrentSet();
	}

	/**
	 * Initializes all Set posts for post_type op_set admin screen
	 * Overwrites Sets that have been set in init()
	 */
	public function initAdmin () {
		$screen = get_current_screen();

		if ( !$screen instanceof WP_Screen ) {
			trigger_error( sprintf( '%s::%s(): get_current_screen() may be hooked too early. Return value is not an instance of WP_Screen.', __CLASS__, __METHOD__ ) );
			return;
		}

		// Skip if current screen is no op_set post edit screen
		if ( !$screen->base == 'post' or !$screen->post_type == SetCpt::CPT_SLUG )
			return;

		// Redo Child Set mechanism
		add_action( SetEntity::WP_ACTION_BEFORE_SETUP, function ( SetEntity $set ) {
			$parentPost = $set->getParentPost();
			$set->setId( $parentPost->ID );
			$set->setPost( $parentPost );
		} );

		self::$sets = new ArrayObject;

		$posts = get_posts( array(
			'post_type'   => SetCpt::CPT_SLUG,
			'numberposts' => - 1
		) );

		foreach ( $posts as $single_post )
			self::getSets()->offsetSet( $single_post->ID, new SetEntity( $single_post ) );

		self::initCurrentSet();
	}

	/** Checks global posts and sets current set */
	protected static function initCurrentSet() {
		global $post;

		if ( !$post instanceof WP_Post )
			return;

		if ( self::$sets->offsetGet( $post->ID ) instanceof SetEntity )
			self::$currentSetId = $post->ID;
	}

	/**
	 * Forces Detail Fields Meta Box to show up in sidebar
	 * @return    string
	 */
	public function modifyDetailFieldContext() {
		return 'side';
	}

	/**
	 * Getter: Sets
	 * @return    ArrayObject
	 */
	public static function getSets() {
		return self::$sets;
	}

	/**
	 * Returns a numeric array with:
	 *   key:     int with set id
	 *   value:   string with set name
	 *
	 * @return    array
	 */
	public static function getSetsOptions() {
		$sets = array();
		foreach ( self::getSets() as $set )
			$sets[ $set->getId() ] = $set->getPost()->post_title;

		return $sets;
	}

	/**
	 * Setter: Sets
	 *
	 * @param     ArrayObject $sets
	 */
	public static function setSets( ArrayObject $sets ) {
		self::$sets = $sets;
	}

	/**
	 * Getter: Current Set Id
	 * @return    int
	 */
	public static function getCurrentSetId() {
		return self::$currentSetId;
	}

	/**
	 * Setter: Current Set Id
	 * @param     int       $currentSetId
	 */
	public static function setCurrentSetId( $currentSetId ) {
		self::$currentSetId = (int) $currentSetId;
	}

	/**
	 * Getter: Set
	 * @param     int       $setId
	 * @return    Set
	 */
	public static function getSet ( $setId ) {
		return self::getSets()->offsetGet( $setId );
	}

	/**
	 * Getter: Current Set
	 * @return     Set
	 */
	public static function getCurrentSet() {
		$setId = self::getCurrentSetId();
		return self::getSet( $setId );
	}
}