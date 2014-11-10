<?php
/**
 *  Opening Hours: Module: CPT: Set
 */

if ( class_exists( 'OP_CPT_Set' ) )
  return;

class OP_CPT_Set extends OP_AbstractModule {

  /**
   *  Constants
   */
  const   CPT_SLUG  = 'op-set';

  /**
   *  Constructor
   *
   *  @access       public
   */
  public function __construct () {

    $this->registerHookCallbacks();

  }

  /**
   *  Register Hook Callbacks
   *
   *  @access       public
   */
  public function registerHookCallbacks () {

    add_action( 'init',     array( __CLASS__, 'registerPostType' ) );

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
  		'publicly_queryable' => false,
  		'show_ui'            => true,
  		'show_in_menu'       => true,
  		'query_var'          => true,
  		'capability_type'    => 'page',
  		'has_archive'        => true,
  		'hierarchical'       => false,
  		'menu_position'      => null,
  		'supports'           => array( 'title' )
    );

  }

}
?>
