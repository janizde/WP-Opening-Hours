<?php

namespace OpeningHours\Module;

use OpeningHours\Entity\Set;
use OpeningHours\Entity\Set as SetEntity;
use OpeningHours\Module\CustomPostType\Set as SetCpt;
use OpeningHours\Util\ArrayObject;

/**
 * OpeningHours Module
 *
 * @author      Jannik Portz
 * @package     OpeningHours\Module
 */
class OpeningHours extends AbstractModule {

  /**
   * Collection of all loaded Sets
   * @type      ArrayObject
   */
  protected $sets;

  /** Constructor */
  public function __construct () {
    $this->sets = new ArrayObject();
    $this->registerHookCallbacks();
  }

  /** Register Hook Callbacks */
  public function registerHookCallbacks () {
    add_filter('detail_fields_metabox_context', function () {
      return 'side';
    });

    add_action('init', array($this, 'init'));
    add_action('current_screen', array($this, 'initAdmin'));
  }

  /** Initializes all parent posts and loads children */
  public function init () {
    // Get all parent op-set posts
    $posts = get_posts(array(
      'post_type' => SetCpt::CPT_SLUG,
      'post_parent' => 0,
      'numberposts' => -1
    ));

    foreach ($posts as $singlePost)
      $this->sets->offsetSet($singlePost->ID, new SetEntity($singlePost));
  }

  /**
   * Initializes all Set posts for post_type op_set admin screen
   * Overwrites Sets that have been set in init()
   */
  public function initAdmin () {
    $screen = get_current_screen();

    if (!$screen->base == 'post' or !$screen->post_type == SetCpt::CPT_SLUG)
      return;

    // Redo Child Set mechanism
    add_action(SetEntity::WP_ACTION_BEFORE_SETUP, function ( SetEntity $set ) {
      $parentPost = $set->getParentPost();
      $set->setId($parentPost->ID);
      $set->setPost($parentPost);
    });
  }

  /**
   * Getter: Sets
   * @return    ArrayObject
   */
  public static function getSets () {
    return self::getInstance()->sets;
  }

  /**
   * Returns a numeric array with:
   *   key:     int with set id
   *   value:   string with set name
   *
   * @return    array
   */
  public static function getSetsOptions () {
    $sets = array();
    foreach (self::getInstance()->sets as $set)
      $sets[$set->getId()] = $set->getPost()->post_title;

    return $sets;
  }

  /**
   * Retrieves a Set
   * @param     int       $setId    The id of the Set to retrieve
   * @return    Set|null            The Set with the specified id or null if not foudn
   */
  public static function getSet ($setId) {
    $instance = self::getInstance();
    if ($instance->sets->offsetExists($setId))
      return $instance->sets->offsetGet($setId);

    try {
      $set = new Set($setId);
      $instance->sets->offsetSet($set->getId(), $set);
      return $set;
    } catch (\InvalidArgumentException $e) {
      return null;
    }
  }

  public static function getPrimarySet () {

  }
}