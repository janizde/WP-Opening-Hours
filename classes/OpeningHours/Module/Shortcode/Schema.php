<?php

namespace OpeningHours\Module\Shortcode;

use OpeningHours\Entity\PostSetProvider;
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
   * If the set could not be determined, `null` is returned.
   *
   * @param     int|string    $setId    Id of the Set for which to create the Generator
   * @return    SchemaGenerator|null    SchemaGenerator containing the main set and possibly child sets
   *                                    or null if the set could not be found
   */
  protected function createSchemaGenerator($setId) {
    $setProviders = OpeningHours::getInstance()->getSetProviders();
    $providingProvider = null;

    foreach ($setProviders as $provider) {
      $availableSets = $provider->getAvailableSetInfo();

      foreach ($availableSets as $setSpec) {
        if ($setSpec['id'] == $setId) {
          $providingProvider = $provider;
          break 2;
        }
      }
    }

    if ($providingProvider == null) {
      return null;
    }

    if ($providingProvider instanceof PostSetProvider) {
      $parentPost = $providingProvider->findPost($setId);
      $parentAndChildren = $providingProvider->createSetAndChildrenFromPost($parentPost);
      return new SchemaGenerator($parentAndChildren['parent'], $parentAndChildren['children']);
    }

    $set = $providingProvider->createSet($setId);
    return new SchemaGenerator($set, array());
  }

  /** @inheritdoc */
  public function shortcode(array $attributes) {
    $setId = $attributes['set_id'];
    $generator = $this->createSchemaGenerator($setId);

    if ($generator == null) {
      return;
    }

    $attributes['schema'] = $generator->createSchema();
    echo $this->renderShortcodeTemplate($attributes, 'shortcode/schema.php');
  }
}