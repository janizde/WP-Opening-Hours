<?php

namespace OpeningHours\Entity;

use OpeningHours\Module\CustomPostType\MetaBox\SetDetails;
use OpeningHours\Module\CustomPostType\Set as SetPostType;
use OpeningHours\Util\ArrayObject;
use OpeningHours\Util\Dates;
use OpeningHours\Util\Persistence;

/**
 * SetProvider for Sets created from the Set Post Type
 *
 * @package OpeningHours\Entity
 */
class PostSetProvider extends SetProvider {
  /**
   * Creates a Set from a post id
   * @param     int|string  $id     Post id or Set alias
   * @param     int         $rootId The id of the top-level set being initially requested
   * @return    Set                 The Set created from the post
   * @throws    \InvalidArgumentException If no Set could be created from id
   */
  public function createSet($id, $rootId = null) {
    $post = $this->findPost($id);

    if ($rootId === null) {
      $rootId = $post->ID;
    }

    $details = SetDetails::getInstance()->getPersistence();

    if (!$this->isEditScreen()) {
      $children = get_posts(array(
        'post_type' => SetPostType::CPT_SLUG,
        'numberposts' => -1,
        'orderby' => 'menu_order',
        'order' => 'ASC',
        'post_parent' => $post->ID
      ));

      foreach ($children as $childPost) {
        $weekScheme = $details->getValue('weekScheme', $childPost->ID);
        $dateStart = $details->getValue('dateStart', $childPost->ID);
        $dateStart = empty($dateStart) ? null : new \DateTime($dateStart);
        $dateEnd = $details->getValue('dateEnd', $childPost->ID);
        $dateEnd = empty($dateEnd) ? null : new \DateTime($dateEnd);

        if ($this->childSetCriteriaMatches($dateStart, $dateEnd, $weekScheme)) {
          $childSet = $this->createSet($childPost->ID, $rootId);
          $childSet->setId($post->ID);
          $childSet->setName($post->post_title);
          return $childSet;
        }
      }
    }

    $persistence = new Persistence($post);

    $rootPost = $post;
    $rootPersistence = $persistence;

    if ($rootId !== $post->ID) {
      $rootPost = get_post($rootId);
      $rootPersistence = new Persistence($rootPost);
    }

    $set = new Set($rootPost->ID);
    $set->setName($rootPost->post_title);

    $set->setPeriods(ArrayObject::createFromArray($persistence->loadPeriods()));
    $set->setHolidays(ArrayObject::createFromArray($rootPersistence->loadHolidays()));
    $set->setIrregularOpenings(ArrayObject::createFromArray($rootPersistence->loadIrregularOpenings()));

    $description = $details->getValue('description', $post->ID);

    if (empty($description)) {
      $description = $details->getValue('description', $rootPost->ID);
    }

    $set->setDescription($description);
    return $set;
  }

  /**
   * Checks whether the child set criteria matches the current DateTime
   *
   * @param     \DateTime $dateStart  Start of child set period (default: null, no restriction)
   * @param     \DateTime $dateEnd    End of child set period (default: null, no restriction)
   * @param     string    $weekScheme Week scheme (any of even, odd or null)
   * @param     \DateTime $now        Custom current DateTime (default: null, current time)
   *
   * @return    bool                      Whether the criteria matches
   */
  public function childSetCriteriaMatches(
    \DateTime $dateStart = null,
    \DateTime $dateEnd = null,
    $weekScheme,
    \DateTime $now = null
  ) {
    if ($dateStart == null && $dateEnd == null && !in_array($weekScheme, array('even', 'odd'))) {
      return false;
    }

    if ($now == null) {
      $now = Dates::getNow();
    }

    $currentWeekOffset = ((int) $now->format('W')) % 2;
    if ($weekScheme === 'even' && $currentWeekOffset === 1) {
      return false;
    }

    if ($weekScheme === 'odd' && $currentWeekOffset === 0) {
      return false;
    }

    if ($dateStart !== null && $dateStart > $now) {
      return false;
    }

    if ($dateEnd !== null) {
      $dateEnd = clone $dateEnd;
      $dateEnd->setTime(23, 59, 59);

      if ($dateEnd < $now) {
        return false;
      }
    }

    return true;
  }

  /** @inheritdoc */
  public function createAvailableSetInfo() {
    $details = SetDetails::getInstance()->getPersistence();

    $args = array(
      'post_type' => SetPostType::CPT_SLUG,
      'numberposts' => -1,
      'orderby' => 'menu_order',
      'order' => 'ASC'
    );

    if (!$this->isEditScreen()) {
      $args['post_parent'] = 0;
    }

    $posts = get_posts($args);

    $setInfo = array();
    foreach ($posts as $post) {
      $setInfo[] = array(
        'id' => $post->ID,
        'name' => $post->post_title
      );

      $alias = $details->getValue('alias', $post->ID);
      if (!empty($alias)) {
        $setInfo[] = array(
          'id' => $alias,
          'name' => $post->post_title,
          'hidden' => true
        );
      }
    }

    return $setInfo;
  }

  /**
   * Checks whether the current screen is the edit screen for a Set Post
   */
  protected function isEditScreen() {
    if (!function_exists('get_current_screen')) {
      return false;
    }

    $screen = get_current_screen();

    return $screen !== null && $screen->base === 'post' && $screen->post_type === SetPostType::CPT_SLUG;
  }

  /**
   * Tries to find a post by the specified ID or alias
   * @param     int|string    $id   Post id or Set alias
   * @return    \WP_Post            The post with matching id or alias
   * @throws    \InvalidArgumentException  If no post could be found
   */
  public function findPost($id) {
    if (empty($id)) {
      throw new \InvalidArgumentException("Parameter \$id must not be empty.");
    }

    if (is_numeric($id)) {
      $post = get_post($id);
      if ($post instanceof \WP_Post) {
        return $post;
      }
    }

    $persistence = SetDetails::getInstance()->getPersistence();
    $key = $persistence->generateMetaKey('alias');

    $posts = get_posts(array(
      'post_type' => SetPostType::CPT_SLUG,
      'numberposts' => -1,
      'meta_key' => $key,
      'meta_value' => $id
    ));

    if (count($posts) > 0) {
      return $posts[0];
    }

    throw new \InvalidArgumentException("A post set with id or alias '$id' does not exist.");
  }

  /**
   * Creates an instance of Set from a Post object
   * and populates it with the post name and Periods, Holidays
   * and Irregular Openings which are saved for that specific Set.
   *
   * @param       \WP_Post    $post   The post from which to create the set
   * @return      Set                 The Set instance consisting of the post's meta data
   */
  protected function createSetFromPost(\WP_Post $post) {
    $set = new Set($post->ID);
    $set->setName($post->post_title);

    $persistence = new Persistence($post);
    $set->setPeriods(ArrayObject::createFromArray($persistence->loadPeriods()));
    $set->setHolidays(ArrayObject::createFromArray($persistence->loadHolidays()));
    $set->setIrregularOpenings(ArrayObject::createFromArray($persistence->loadIrregularOpenings()));
    return $set;
  }

  /**
   * Creates a ChildSetWrapper from a post which is considered a child set. The child set
   * criteria is read from the post SetDetails.
   *
   * @param     \WP_Post      $post   Post from which to create the set
   * @return    ChildSetWrapper       Wrapper around the child set
   */
  protected function createChildWrapperFromPost(\WP_Post $post) {
    $setAndChildren = $this->createSetAndChildrenFromPost($post);
    $details = SetDetails::getInstance()->getPersistence();

    $dateStart = $details->getValue('dateStart', $post->ID);
    $dateEnd = $details->getValue('dateEnd', $post->ID);
    $weekScheme = $details->getValue('weekScheme', $post->ID);

    return new ChildSetWrapper(
      $setAndChildren['parent'],
      empty($dateStart) ? -INF : new \DateTime($dateStart),
      empty($dateEnd) ? INF : new \DateTime($dateEnd),
      $weekScheme,
      $setAndChildren['children']
    );
  }

  /**
   * Creates an associative array containing the Set corresponding to `$post` under the key
   * `parent` and an array of Sets corresponding to the post children under the key `children`.
   *
   * @param     \WP_Post      $post   Post corresponding to parent set
   * @return    array
   */
  public function createSetAndChildrenFromPost(\WP_Post $post) {
    $parentSet = $this->createSetFromPost($post);
    $children = get_posts(array(
      'post_type' => SetPostType::CPT_SLUG,
      'numberposts' => -1,
      'orderby' => 'menu_order',
      'order' => 'ASC',
      'post_parent' => $post->ID
    ));

    $childSets = array_map(array($this, 'createChildWrapperFromPost'), $children);

    return array(
      'parent' => $parentSet,
      'children' => $childSets
    );
  }
}
