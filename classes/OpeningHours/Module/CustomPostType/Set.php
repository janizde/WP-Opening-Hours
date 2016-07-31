<?php

namespace OpeningHours\Module\CustomPostType;

use OpeningHours\Module\AbstractModule;
use OpeningHours\Module\I18n;

/**
 * Set Custom Post Type
 *
 * @author      Jannik Portz
 * @package     OpeningHours\Module\CustomPostType
 */
class Set extends AbstractModule {

  const CPT_SLUG = 'op-set';
  const META_BOX_ID = 'op-set-periods';
  const META_BOX_CONTEXT = 'advanced';
  const META_BOX_PRIORITY = 'high';
  const PERIODS_META_KEY = '_op_set_periods';
  const TEMPLATE_META_BOX = 'op-set-meta-box.php';
  const NONCE_NAME = 'op-update-set-nonce';
  const NONCE_VALUE = 'op-set-opening-hours';

  /**
   * Meta Boxes
   * associative array of MetaBox modules with:
   *  key:      string w/ MetaBox identifier
   *  value:    MetaBox singleton object
   *
   * @var       array
   */
  protected static $metaBoxes;

  /** Constructor */
  public function __construct () {
    $this->registerHookCallbacks();

    static::$metaBoxes = array(
      'OpeningHours' => MetaBox\OpeningHours::getInstance(),
      'Holidays' => MetaBox\Holidays::getInstance(),
      'IrregularOpenings' => MetaBox\IrregularOpenings::getInstance(),
      'SetDetails' => MetaBox\SetDetails::getInstance()
    );
  }

  /** Register Hook Callbacks */
  public function registerHookCallbacks () {
    add_action('init', array($this, 'registerPostType'));
    add_action('admin_menu', array($this, 'cleanUpMenu'));
  }

  /** Registers Post Type */
  public function registerPostType () {
    register_post_type(self::CPT_SLUG, $this->getArguments());
  }

  /** Clean Up Menu */
  public function cleanUpMenu () {
    global $submenu;

    /** Top Level: Registered via post_type op-set: Remove "Add New" Item */
    unset($submenu['edit.php?post_type=op-set'][10]);
  }

  /**
   * Getter: Labels
   * @return    array
   */
  public function getLabels () {
    return array(
      'name' => __('Sets', I18n::TEXTDOMAIN),
      'singular_name' => __('Set', I18n::TEXTDOMAIN),
      'menu_name' => __('Opening Hours', I18n::TEXTDOMAIN),
      'name_admin_bar' => __('Set', I18n::TEXTDOMAIN),
      'add_new' => __('Add New', I18n::TEXTDOMAIN),
      'add_new_item' => __('Add New Set', I18n::TEXTDOMAIN),
      'new_item' => __('New Set', I18n::TEXTDOMAIN),
      'edit_item' => __('Edit Set', I18n::TEXTDOMAIN),
      'view_item' => __('View Set', I18n::TEXTDOMAIN),
      'all_items' => __('All Sets', I18n::TEXTDOMAIN),
      'search_items' => __('Search Sets', I18n::TEXTDOMAIN),
      'parent_item_colon' => __('Parent Sets:', I18n::TEXTDOMAIN),
      'not_found' => __('No sets found.', I18n::TEXTDOMAIN),
      'not_found_in_trash' => __('No sets found in Trash.', I18n::TEXTDOMAIN)
    );
  }

  /**
   * Getter: Arguments
   * @return    array
   */
  public function getArguments () {
    return array(
      'labels' => $this->getLabels(),
      'public' => false,
      'publicly_queryable' => true,
      'show_ui' => true,
      'show_in_menu' => true,
      'query_var' => true,
      'capability_type' => 'page',
      'has_archive' => true,
      'hierarchical' => true,
      'menu_position' => 400,
      'menu_icon' => 'dashicons-clock',
      'supports' => array('title', 'page-attributes')
    );
  }
}