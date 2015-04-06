<?php
/**
 *  Opening Hours: Module: CPT: Set
 */

namespace OpeningHours\Module\CustomPostType;

use OpeningHours\Module\AbstractModule;
use OpeningHours\Module\I18n;

class Set extends AbstractModule {

	/**
	 *  Constants
	 */
	const   CPT_SLUG = 'op-set';
	const   META_BOX_ID = 'op-set-periods';
	const   META_BOX_CONTEXT = 'advanced';
	const   META_BOX_PRIORITY = 'high';
	const   PERIODS_META_KEY = '_op_set_periods';
	const   TEMPLATE_META_BOX = 'op-set-meta-box.php';
	const   NONCE_NAME = 'op-update-set-nonce';
	const   NONCE_VALUE = 'op-set-opening-hours';

	/**
	 * Meta Boxes
	 * associative array of MetaBox modules with:
	 *  key:      string w/ MetaBox identifier
	 *  value:    MetaBox singleton object
	 *
	 * @access      protected
	 * @type        array
	 */
	protected static $metaBoxes;

	/**
	 *  Constructor
	 *
	 * @access       public
	 */
	public function __construct() {

		self::registerHookCallbacks();

		static::setMetaBoxes( array(
			'OpeningHours'      => MetaBox\OpeningHours::getInstance(),
			'Holidays'          => MetaBox\Holidays::getInstance(),
			'IrregularOpenings' => MetaBox\IrregularOpenings::getInstance(),
			'Donation'          => MetaBox\Donation::getInstance()
		) );

	}

	/**
	 *  Register Hook Callbacks
	 *
	 * @access       public
	 * @static
	 */
	public static function registerHookCallbacks() {

		add_action( 'init', array( __CLASS__, 'registerPostType' ) );
		add_action( 'admin_menu', array( __CLASS__, 'cleanUpMenu' ) );
		add_action( 'add_detail_fields', array( __CLASS__, 'registerDetailFields' ) );

	}

	/**
	 *  Register Post Type
	 *
	 * @access       public
	 * @static
	 * @wp_action    init
	 */
	public static function registerPostType() {

		register_post_type( self::CPT_SLUG, self::getArguments() );

	}

	/**
	 *  Clean Up Menu
	 *
	 * @access       public
	 * @static
	 * @wp_action    admin_menu
	 */
	public static function cleanUpMenu() {

		global $submenu;

		/** Top Level: Registered via post_type op-set: Remove "Add New" Item */
		unset( $submenu['edit.php?post_type=op-set'][10] );

	}

	/**
	 *  Register Detail Fields
	 *
	 * @access       public
	 * @static
	 * @wp_action    add_detail_fields
	 */
	public static function registerDetailFields() {

		/** Field: Description */
		register_detail_field( self::CPT_SLUG, array(
			'type'    => 'textarea',
			'slug'    => 'description',
			'caption' => __( 'Description', I18n::TEXTDOMAIN )
		) );

		/** Field: Date Start */
		register_detail_field( self::CPT_SLUG, array(
			'type'    => 'text',
			'slug'    => 'date-start',
			'caption' => __( 'Date Start', I18n::TEXTDOMAIN )
		) );

		/** Field: Date End */
		register_detail_field( self::CPT_SLUG, array(
			'type'    => 'text',
			'slug'    => 'date-end',
			'caption' => __( 'Date End', I18n::TEXTDOMAIN )
		) );

		/** Field: Week Scheme */
		register_detail_field( self::CPT_SLUG, array(
			'type'        => 'radio',
			'slug'        => 'week-scheme',
			'caption'     => __( 'Week Scheme', I18n::TEXTDOMAIN ),
			'default-val' => 'all',
			'options'     => array(
				'all'  => __( 'Every week', I18n::TEXTDOMAIN ),
				'even' => __( 'Even weeks only', I18n::TEXTDOMAIN ),
				'odd'  => __( 'Odd weeks only', I18n::TEXTDOMAIN )
			)
		) );

		/** Field: Child Set Notice */
		register_detail_field( self::CPT_SLUG, array(
			'type'        => 'heading',
			'slug'        => 'child-set-notice',
			'heading'     => __( 'Add a Child-Set', I18n::TEXTDOMAIN ),
			'description' => __( 'You may add a child set that overwrites the parent Opening Hours in specific time range. Use the post type hierarchy.', I18n::TEXTDOMAIN )
		) );

	}

	/**
	 *  Get Labels
	 *
	 * @access       public
	 * @static
	 * @return       array
	 */
	public static function getLabels() {

		return array(
			'name'               => __( 'Sets', I18n::TEXTDOMAIN ),
			'singular_name'      => __( 'Set', I18n::TEXTDOMAIN ),
			'menu_name'          => __( 'Opening Hours', I18n::TEXTDOMAIN ),
			'name_admin_bar'     => __( 'Set', I18n::TEXTDOMAIN ),
			'add_new'            => __( 'Add New', I18n::TEXTDOMAIN ),
			'add_new_item'       => __( 'Add New Set', I18n::TEXTDOMAIN ),
			'new_item'           => __( 'New Set', I18n::TEXTDOMAIN ),
			'edit_item'          => __( 'Edit Set', I18n::TEXTDOMAIN ),
			'view_item'          => __( 'View Set', I18n::TEXTDOMAIN ),
			'all_items'          => __( 'All Sets', I18n::TEXTDOMAIN ),
			'search_items'       => __( 'Search Sets', I18n::TEXTDOMAIN ),
			'parent_item_colon'  => __( 'Parent Sets:', I18n::TEXTDOMAIN ),
			'not_found'          => __( 'No sets found.', I18n::TEXTDOMAIN ),
			'not_found_in_trash' => __( 'No sets found in Trash.', I18n::TEXTDOMAIN )
		);

	}

	/**
	 *  Get Arguments
	 *
	 * @access       public
	 * @static
	 * @return       array
	 */
	public static function getArguments() {

		return array(
			'labels'             => self::getLabels(),
			'public'             => false,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'capability_type'    => 'page',
			'has_archive'        => true,
			'hierarchical'       => true,
			'menu_position'      => 400,
			'menu_icon'          => 'dashicons-clock',
			'supports'           => array( 'title', 'custom-fields', 'page-attributes' )
		);

	}

	/**
	 * Getter: Meta Boxes
	 *
	 * @access        public
	 * @return        array
	 */
	public static function getMetaBoxes() {
		return static::$metaBoxes;
	}

	/**
	 * Setter: Meta Boxes
	 *
	 * @access        protected
	 * @static
	 *
	 * @param         array $metaBoxes
	 */
	protected static function setMetaBoxes( array $metaBoxes ) {
		static::$metaBoxes = $metaBoxes;
	}

}