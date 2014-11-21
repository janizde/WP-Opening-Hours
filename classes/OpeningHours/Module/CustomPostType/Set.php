<?php
/**
 *  Opening Hours: Module: CPT: Set
 */

namespace OpeningHours\Module\CustomPostType;

use OpeningHours\Module\AbstractModule;

use WP_Post;

class Set extends AbstractModule {

  /**
   *  Constants
   */
  const   CPT_SLUG          = 'op-set';
  const   META_BOX_ID       = 'op-set-periods';
  const   META_BOX_CONTEXT  = 'advanced';
  const   META_BOX_PRIORITY = 'high';
  const   PERIODS_META_KEY  = '_op-set-periods';
  const   TEMPLATE_META_BOX = 'op-set-meta-box.php';

  const   NONCE_NAME        = 'op-update-set-nonce';
  const   NONCE_VALUE       = 'op-set-opening-hours';

  /**
   *  Constructor
   *
   *  @access       public
   */
  public function __construct () {

    self::registerHookCallbacks();

  }

  /**
   *  Register Hook Callbacks
   *
   *  @access       public
   *  @static
   */
  public static function registerHookCallbacks () {

    add_action( 'init',               array( __CLASS__, 'registerPostType' ) );
    add_action( 'admin_menu',         array( __CLASS__, 'cleanUpMenu' ) );
    add_action( 'add_meta_boxes',     array( __CLASS__, 'registerMetaBox' ) );
    add_action( 'add_detail_fields',  array( __CLASS__, 'registerDetailFields' ) );

  }

  /**
   *  Register Post Type
   *
   *  @access       public
   *  @static
   *  @wp_action    init
   */
  public static function registerPostType () {

    register_post_type( self::CPT_SLUG, self::getArguments() );

  }

  /**
   *  Clean Up Menu
   *
   *  @access       public
   *  @static
   *  @wp_action    admin_menu
   */
  public static function cleanUpMenu () {

    global $submenu;

    /** Top Level: Registered via post_type op-set: Remove "Add New" Item */
    unset( $submenu['edit.php?post_type=op-set'][10] );

  }

  /**
   *  Register Meta Box
   *
   *  @access       public
   *  @static
   *  @wp_action    add_meta_boxes
   */
  public static function registerMetaBox () {

    add_meta_box(
      self::META_BOX_ID,
      __( 'Opening Hours', self::TEXTDOMAIN ),
      array( __CLASS__, 'renderMetaBox' ),
      self::CPT_SLUG,
      self::META_BOX_CONTEXT,
      self::META_BOX_PRIORITY
    );

  }

  /**
   *  Render Meta Box
   *
   *  @access       public
   *  @static
   *  @param        WP_Post   $post
   */
  public static function renderMetaBox ( WP_Post $post ) {

      $variables   = array(
        'post'    => $post
      );

      echo self::renderTemplate( self::TEMPLATE_META_BOX, $variables );

  }

  /**
   *  Register Detail Fields
   *
   *  @access       public
   *  @static
   *  @wp_action    add_detail_fields
   */
  public static function registerDetailFields () {

    /** Field: Description */
    register_detail_field( self::CPT_SLUG, array(
      'type'    => 'textarea',
      'slug'    => 'description',
      'caption' => __( 'Description', self::TEXTDOMAIN )
    ) );

    /** Field: Ignore Date Range */
    register_detail_field( self::CPT_SLUG, array(
      'type'    => 'checkbox',
      'slug'    => 'ignore-date-range',
      'caption' => __( 'Date Range', self::TEXTDOMAIN ),
      'options' => array(
        'ignore'    => __( 'Ignore Date Range', self::TEXTDOMAIN )
      )
    ) );

    /** Field: Date Start */
    register_detail_field( self::CPT_SLUG, array(
      'type'    => 'date',
      'slug'    => 'date-start',
      'caption' => __( 'Date Start', self::TEXTDOMAIN ),
    ) );

    /** Field: Date End */
    register_detail_field( self::CPT_SLUG, array(
      'type'    => 'date',
      'slug'    => 'date-end',
      'caption' => __( 'Date End', self::TEXTDOMAIN )
    ) );

    /** Field: Week Scheme */
    register_detail_field( self::CPT_SLUG, array(
      'type'        => 'radio',
      'slug'        => 'week-scheme',
      'caption'     => __( 'Week Scheme', self::TEXTDOMAIN ),
      'default-val' => 'all',
      'options'     => array(
        'all'         => __( 'Every week', self::TEXTDOMAIN ),
        'even'        => __( 'Even weeks only', self::TEXTDOMAIN ),
        'odd'         => __( 'Odd weeks only', self::TEXTDOMAIN )
      )
    ) );

  }

  /**
   *  Get Labels
   *
   *  @access       public
   *  @static
   *  @return       array
   */
  public static function getLabels () {

    return array(
      'name'               => __( 'Sets', self::TEXTDOMAIN ),
  		'singular_name'      => __( 'Set', self::TEXTDOMAIN ),
  		'menu_name'          => __( 'Opening Hours', self::TEXTDOMAIN ),
  		'name_admin_bar'     => __( 'Set', self::TEXTDOMAIN ),
  		'add_new'            => __( 'Add New', self::TEXTDOMAIN ),
  		'add_new_item'       => __( 'Add New Set', self::TEXTDOMAIN ),
  		'new_item'           => __( 'New Set', self::TEXTDOMAIN ),
  		'edit_item'          => __( 'Edit Set', self::TEXTDOMAIN ),
  		'view_item'          => __( 'View Set', self::TEXTDOMAIN ),
  		'all_items'          => __( 'All Sets', self::TEXTDOMAIN ),
  		'search_items'       => __( 'Search Sets', self::TEXTDOMAIN ),
  		'parent_item_colon'  => __( 'Parent Sets:', self::TEXTDOMAIN ),
  		'not_found'          => __( 'No sets found.', self::TEXTDOMAIN ),
  		'not_found_in_trash' => __( 'No sets found in Trash.', self::TEXTDOMAIN )
    );

  }

  /**
   *  Get Arguments
   *
   *  @access       public
   *  @static
   *  @return       array
   */
  public static function getArguments () {

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
      'supports'           => array( 'title', 'custom-fields' )
    );

  }

}
?>
