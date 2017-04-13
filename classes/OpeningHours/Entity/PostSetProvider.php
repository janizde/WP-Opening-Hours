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
   * @return    Set                 The Set created from the post
   * @throws    \InvalidArgumentException If no Set could be created from id
   */
  public function createSet ($id) {
    $post = $this->findPost($id);

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
          $childSet = $this->createSet($childPost->ID);
          $childSet->setId($post->ID);
          $childSet->setName($post->post_title);
          return $childSet;
        }
      }
    }

    $set = new Set($post->ID);
    $set->setName($post->post_title);

    $persistence = new Persistence($post);
    $set->setPeriods(ArrayObject::createFromArray($persistence->loadPeriods()));
    $set->setHolidays(ArrayObject::createFromArray($persistence->loadHolidays()));
    $set->setIrregularOpenings(ArrayObject::createFromArray($persistence->loadIrregularOpenings()));

    $set->setDescription($details->getValue('description', $post->ID));
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
  public function childSetCriteriaMatches ( \DateTime $dateStart = null, \DateTime $dateEnd = null, $weekScheme, \DateTime $now = null) {
    if ($dateStart == null && $dateEnd == null && !in_array($weekScheme, array('even', 'odd')))
      return false;

    if ($now == null)
      $now = Dates::getNow();

    $currentWeekOffset = ((int) $now->format('W')) % 2;
    if ($weekScheme === 'even' && $currentWeekOffset === 1)
      return false;

    if ($weekScheme === 'odd' && $currentWeekOffset === 0)
      return false;

    if ($dateStart !== null && $dateStart > $now)
      return false;

    if ($dateEnd !== null) {
      $dateEnd = clone $dateEnd;
      $dateEnd->setTime(23,59,59);

      if ($dateEnd < $now)
        return false;
    }

    return true;
  }

  /** @inheritdoc */
  public function createAvailableSetInfo () {
    $details = SetDetails::getInstance()->getPersistence();

    $args = array(
      'post_type' => SetPostType::CPT_SLUG,
      'numberposts' => -1,
      'orderby' => 'menu_order',
      'order' => 'ASC',
    );

    if (!$this->isEditScreen())
      $args['post_parent'] = 0;

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
  protected function isEditScreen () {
    if (!function_exists('get_current_screen'))
      return false;

    $screen = get_current_screen();
    return $screen->base === 'post' && $screen->post_type === SetPostType::CPT_SLUG;
  }

  /**
   * Tries to find a post by the specified ID or alias
   * @param     int|string    $id   Post id or Set alias
   * @return    \WP_Post            The post with matching id or alias
   * @throws    \InvalidArgumentException  If no post could be found
   */
  protected function findPost ($id) {
    if (empty($id))
      throw new \InvalidArgumentException("Parameter \$id must not be empty.");

    if (is_numeric($id)) {
      $post = get_post($id);
      if ($post instanceof \WP_Post)
        return $post;
    }

    $persistence = SetDetails::getInstance()->getPersistence();
    $key = $persistence->generateMetaKey('alias');

    $posts = get_posts(array(
      'post_type' => SetPostType::CPT_SLUG,
      'numberposts' => -1,
      'meta_key' => $key,
      'meta_value' => $id
    ));

    if (count($posts) > 0)
      return $posts[0];

    throw new \InvalidArgumentException("A post set with id or alias '$id' does not exist.");
  }
}
