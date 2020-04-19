<?php


namespace OpeningHours\Persistence;

use OpeningHours\Core\SpecEntry;
use OpeningHours\Core\SpecEntryParser;

/**
 * Class responsible for storing and retrieving opening hours specifications to and from WP_Post metadata
 * @package OpeningHours\Persistence
 */
class PostSpecStore {
  /** Meta key under which the opening hours specification is stores */
  const SPEC_META_KEY = '_op_set_specification';

  /**
   * ID of the post to save the data to or read from
   * @var int
   */
  private $postId;

  public function __construct(int $postId) {
    $this->postId = $postId;
  }

  /**
   * Loads the specification from post meta data, parses the serialized version and returns the root spec entry
   * @return    SpecEntry   Root of the specification
   */
  public function loadSpecification(): SpecEntry {
    $serialized = get_post_meta($this->postId, self::SPEC_META_KEY, true);
    return SpecEntryParser::fromSerializableArray($serialized);
  }

  /**
   * Serializes the passed in $rootEntry and stores it as the specification of a Set in the post meta data
   * @param     SpecEntry   $rootEntry    Root entry of the opening hours specification
   */
  public function storeSpecification(SpecEntry $rootEntry) {
    $serialized = $rootEntry->toSerializableArray();
    update_post_meta($this->postId, self::SPEC_META_KEY, $serialized);
  }
}
