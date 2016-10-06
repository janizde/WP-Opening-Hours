# Developer Guide
This document is intended for theme / plugin developers who want to access the Opening Hours data programatically
to develop extension plugins or special functionality like custom widgets or shortcodes in their own themes.

## In this document
* [OpeningHours module](#opening-hours-module)
* [Sets](#sets)
* [Periods](#periods)
* [Holidays](#holidays)
* [Irregular Openings](#irregular-openings)

## In other documents
* [Filters](./filters.md) - to alter shortcode attributes, templates and more
* [SetProviders](./set-providers.md) - to implement custom sources of OpeningHours data

## <a name="opening-hours-module"></a> OpeningHours module
Class `OpeningHours\Module\OpeningHours`

The OpeningHours module Singleton instance is the entry point of the Plugin. You can retrieve it via the static `getInstance` method
```php
use OpeningHours\Module\OpeningHours;

$module = OpeningHours::getInstance();
```

### Methods

#### `public function getSet ($setId) : Set`
Retrieves an instance of [Set](#sets) by the specified `$setId`.
You will probably want to use this method to retrieve a Set to get the associated Periods, Holidays or Irregular Openings

##### Parameters
* `$setId` (`int|string`): The id of the Set to retrieve

##### Return value
`OpeningHours\Entity\Set|null` The Set returned by the first [`SetProvider`](./set-providers.md) which offers a Set with the specified id.
Returns null if no registered `SetProvider` offers a Set with `$setId` or was unable to retrieve the Set.

#### `public function getSetOptions () : array`
Retrieves all available Sets and returns an associative array containing their IDs and names.  
This method is intended to serve data for the widget select field.

##### Return value
`array`: Associative array with ID as key and name as value

[Table of contents](./../README.md)

