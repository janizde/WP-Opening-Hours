<?php

namespace OpeningHours\Module\CustomPostType\MetaBox;

use OpeningHours\Entity\Set;
use OpeningHours\Module\CustomPostType\Set as SetPostType;
use OpeningHours\Module\AbstractModule;
use OpeningHours\Module\OpeningHours;
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

  const POST_TYPE = SetPostType::CPT_SLUG;

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
  protected function registerHookCallbacks () {
    add_action(static::WP_ACTION_ADD_META_BOXES, array($this, 'registerMetaBox'), 10, 2);
    add_action(static::WP_ACTION_SAVE_POST, array($this, 'saveDataCallback'), 10, 3);
  }

  /**
   * Callback for saving the meta box data.
   *
   * @param     int     $post_id The current post's id
   * @param     WP_Post $post    The current post
   * @param     bool    $update  Whether an existing post is updated (false if new post is created)
   */
  public function saveDataCallback ( $post_id, WP_Post $post, $update ) {
    if ($this->verifyNonce() === false)
      return;

    $this->saveData($post_id, $post, $update);
  }

  /**
   * Verifies WordPress nonce
   * @return    bool      Whether the nonce is valid
   */
  protected function verifyNonce () {
    $values = $this->generateNonceValues();
    if (!array_key_exists($values['name'], $_POST))
      return false;

    $nonceValue = $_POST[$values['name']];
    return wp_verify_nonce($nonceValue, $values['action']);
  }

  /** Prints the nonce field for the meta box */
  public function nonceField () {
    $values = $this->generateNonceValues();
    wp_nonce_field($values['action'], $values['name']);
  }

  public function generateNonceValues () {
    return array(
      'name' => $this->id . '_nonce',
      'action' => $this->id . '_edit'
    );
  }

  /**
   * Determines current set and checks if it is a parent set
   * @return    bool
   * @todo      move somewhere else
   */
  public function currentSetIsParent () {
    global $post;
    return !(bool)$post->post_parent;
  }

  /** Registers meta box with add_meta_box */
  public function registerMetaBox () {
    add_meta_box(
      $this->id,
      $this->name,
      array($this, 'renderMetaBox'),
      self::POST_TYPE,
      $this->context,
      $this->priority
    );
  }

  /**
   * Retrieves the Set with the specified id or creates a new empty one
   * @param     string|int  $setId    The id of the set
   * @return    Set                   The Set instance
   */
  protected function getSet ($setId) {
    $set = OpeningHours::getInstance()->getSet($setId);
    if ($set instanceof Set)
      return $set;

    return new Set($setId);
  }

  /**
   * Renders the meta box content
   *
   * @param     WP_Post $post The current post
   */
  abstract public function renderMetaBox ( WP_Post $post );

  /**
   * Processes data when post ist updated or saved
   *
   * @param     int     $post_id The current post's id
   * @param     WP_Post $post    The current post
   * @param     bool    $update  Whether an existing post is updated (false if new post is created)
   */
  abstract protected function saveData ( $post_id, WP_Post $post, $update );

}