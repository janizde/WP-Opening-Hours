<?php

namespace OpeningHours\Module\Shortcode;
use OpeningHours\Module\CustomPostType\Set;
use OpeningHours\Module\OpeningHours;
use OpeningHours\Module\Schema\SchemaGenerator;

/**
 * Shortcode rendering the selected Set's Schema.org data
 * in JSON-LD format
 *
 * @author      Jannik Portz <hello@jannikportz.de>
 * @package     OpeningHours\Module\Shortcode
 */
class Schema extends AbstractShortcode {

  protected function init() {
    $this->setShortcodeTag('op-schema');

    $this->defaultAttributes = array(
      'set_id' => null,
      'exclude_holidays' => false,
      'exclude_irregular_openings' => false,
    );

    $this->validAttributeValues = array(
      'exclude_holidays' => array(false, true),
      'exclude_irregular_openings' => array(false, true),
    );
  }

  /**
   * Creates a SchemaGenerator for the specified `$setId`.
   * If the set is served by the `PostSetProvider` the child
   * sets are fetched, otherwise only the main set will be
   * used.
   *
   * If the set could not be determined ,`null` is returned.
   *
   * @param     int|string    $setId    Id of the Set for which to create the Generator
   * @return    SchemaGenerator|null    SchemaGenerator containing the main set and possibly child sets
   *                                    or null if the set could not be found
   */
  protected function createSchemaGenerator($setId) {
    $postSetProvider = OpeningHours::getInstance()->getSetProviders();


  }

  public function shortcode(array $attributes) {
    $setId = $attributes['set_id'];


  }
}