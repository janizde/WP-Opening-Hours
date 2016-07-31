<?php

namespace OpeningHours\Util;

/**
 * Persistence util for meta box values
 *
 * @author      Jannik Portz
 * @package     OpeningHours\Util
 */
class MetaBoxPersistence {

  /**
   * The namespace in which to store the values.
   * Will be used as meta key prefix
   * @var       string
   */
  protected $namespace;

  public function __construct ( $namespace ) {
    $this->namespace = $namespace;
  }

  /**
   * Returns the value for the specified key from post meta
   *
   * @param     string $key    The nice key of the value
   * @param     int    $postId The id of the post whose meta data to retrieve
   *
   * @return    mixed               The meta value for the specified key and post id
   */
  public function getValue ( $key, $postId ) {
    return get_post_meta($postId, $this->generateMetaKey($key), true);
  }

  /**
   * Puts a value in post meta
   *
   * @param     string $key    The nice key under which the meta value shall be stored
   * @param     mixed  $value  The value to store in post meta
   * @param     int    $postId The id of the post in whose meta data to store the data
   */
  public function putValue ( $key, $value, $postId ) {
    update_post_meta($postId, $this->generateMetaKey($key), $value);
  }

  /**
   * Generates a key for the post meta entry
   *
   * @param     string $key The nice key to use in the application
   *
   * @return    string              The key for post meta
   */
  public function generateMetaKey ( $key ) {
    return sprintf("_%s_%s", $this->namespace, $key);
  }
}