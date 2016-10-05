<?php

/**
 * Template Tag determining whether the primary set is currently open
 * @param       bool      $return_type      Whether to return an array containing the type of the opening (any of 'period', 'holiday', 'special-opening')
 * @return      array|bool                  When $return_type === false the function returns only a boolean
 *                                          When $return_type === true returns an array opening status as first element and type as second element
 *
 * @deprecated  Use OpeningHours\Entity\Set::isOpen() instead
 */
function is_open ($return_type = false) {
  $posts = get_posts(array(
    'post_type' => \OpeningHours\Module\CustomPostType\Set::CPT_SLUG,
    'numberposts' => 1,
    'post_parent' => 0,
    'orderby' => 'menu_order',
    'order' => 'ASC'
  ));

  if (count($posts) < 1)
    return $return_type ? array(false, 'period') : false;

  $set = \OpeningHours\Module\OpeningHours::getInstance()->getSet($posts[0]->ID);

  $type = 'period';
  if ($return_type) {
    if ($set->isIrregularOpeningActive())
      $type = 'special_opening';
    elseif ($set->isHolidayActive())
      $type = 'holiday';
  }

  $isOpen = $set->isOpen();
  return $return_type ? array($isOpen, $type) : $isOpen;
}

/**
 * Template Tag determining whether the primary set is currently closed
 * @param       bool      $return_type      Whether to return an array containing the type of the opening (any of 'period', 'holiday', 'special-opening')
 * @return      array|bool                  When $return_type === false the function returns only a boolean
 *                                          When $return_type === true returns an array opening status as first element and type as second element
 *
 * @deprecated  Use OpeningHours\Entity\Set::isOpen() instead
 */
function is_closed ($return_type = false) {
  $isOpen = is_open($return_type);
  if ($return_type) {
    $isOpen[0] = !$isOpen[0];
    return $isOpen;
  }

  return !$isOpen;
}