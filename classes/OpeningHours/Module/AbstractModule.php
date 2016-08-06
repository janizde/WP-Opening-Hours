<?php

namespace OpeningHours\Module;

/**
 * Abstraction for plugin module
 *
 * @author      Jannik Portz
 * @package     OpeningHours\Module
 */
abstract class AbstractModule {

  /**
   * Collection of all singleton instances
   * @var       AbstractModule[]
   */
  private static $instances = array();

  /**
   * Provides access to a single instance of a module using the singleton pattern
   * @return        static
   */
  public static function getInstance () {
    $class = get_called_class();

    if (!isset(self::$instances[$class]))
      self::$instances[$class] = new $class();

    return self::$instances[$class];
  }
}
