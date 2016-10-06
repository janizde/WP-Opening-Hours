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

## <a name="sets">Sets</a>
Class `OpeningHours\Entity\Set`

Sets contain Periods, Holidays and Irregular Openings

### Methods

#### `public function getId () : int|string`
##### Return Value
`int|string`: The Set id

#### `public function getName () : string`
##### Return Value
`string`: The Set name

#### `public function getPeriods () : ArrayObject`
##### Return value
`OpeningHours\Util\ArrayObject`: ArrayObject containing all Periods in the Set numerically

#### `public function getHolidays () : ArrayObject`
##### Return value
`OpeningHours\Util\ArrayObject`: ArrayObject containing all Holidays in the Set numerically

#### `public function getPeriods () : ArrayObject`
##### Return value
`OpeningHours\Util\ArrayObject`: ArrayObject containing all IrregularOpenings in the Set numerically

#### `public function isOpen (\DateTime $now) : bool`
Checks whether the Set is currently open or not taking Periods, Holidays and Irregular Openings into consideration.
##### Parameters
* `$now` (`\DateTime`, optional): Date and time to check for, default is current time
##### Return Value
`bool`: Whether the Set is currently open or not

#### `public function getNextOpenPeriod (\DateTime $now) : Period`
Determines the next open Period
##### Parameters
* `$now` (`\DateTime`, optional): Date and time to check for, default is current time
##### Return Value
`OpeningHours\Entity\Periods`: The next open period. It will not be a currently running Period but the following one. 
Irregular Openings are converted to a Period when they appear before the next regular opening.

#### `public function getActiveIrregularOpening (\DateTime $now) : IrregularOpening`
Determines the currently active Irregular Opening on the specified day. It does not check whether the IrregularOpening is open but if it's scheduled on the specified day,
so it will always return the IrregularOpening for the whole day.
##### Parameters
* `$now` (`\DateTime`, optional): Date and time to check for, default is current time
##### Return Value
`OpeningHours\Entity\IrregularOpening|null`: The IrregularOpening active at `$now` or null if none is active.

#### `public function isIrregularOpeningActive (\DateTime $now) : bool`
Checks whether any Irregular Opening is active on the specified day. It does not check whether the IrregularOpening is open but if it's scheduled on the specified day,
so it will always return the IrregularOpening for the whole day.
##### Parameters
* `$now` (`\DateTime`, optional): Date and time to check for, default is current time
##### Return Value
`bool`: Whether any IrregularOpening is active ar `$now`

#### `public function getActiveHoliday (\DateTime $now) : Holiday`
Determines the currently active Holiday on the specified day.
##### Parameters
* `$now` (`\DateTime`, optional): Date and time to check for, default is current time
##### Return Value
`OpeningHours\Entity\Holiday|null`: The Holiday active at `$now` or null if none is active.

#### `public function isHolidayActive (\DateTime $now) : bool`
Checks whether any Holiday is active on the specified day.
##### Parameters
* `$now` (`\DateTime`, optional): Date and time to check for, default is current time
##### Return Value
`bool`: Whether any Holiday is active ar `$now`

[Table of contents](./../README.md)

