# Developer Guide
This document is intended for theme / plugin developers who want to access the Opening Hours data programatically
to develop extension plugins or special functionality like custom widgets or shortcodes in their own themes.

The document contains textual description and code samples showing the basic functionality of the components. For detailed documentation please have a look at the method documentation in the respective source files.

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
Class [`OpeningHours\Module\OpeningHours`](./../classes/OpeningHours/Module/OpeningHours.php)

The OpeningHours module Singleton instance is the entry point of the Plugin. You can retrieve it via the static `getInstance` method.  
You will probably want to use it to retrieve a specific Set to get its Periods, Holidays and Irregular OpeningHours

```php
use OpeningHours\Module\OpeningHours;

$module = OpeningHours::getInstance();
```

You can retrieve a single Set by its id. Any of the registered [`SetProviders`](./set-providers.md) must offer a Set with the specified id, otherwise you will be returned `null`.

```php
use OpeningHours\Module\OpeningHours;
use OpeningHours\Entity\Set;

$openingHours = OpeningHours::getInstance();

// Retrieve Set with id 'my-set'
$set = $openingHours->getSet('my-set'); // $set instanceof Set

// Trying to retrieve non-existing Set
$anotherSet = $openingHours->getSet('non-existing-set'); // $set == null

// Retrieve all loaded Sets
$loadedSets = $openingHours->getSets(); // $loadedSets instanceof ArrayObject; Only contains already initialized Sets
```

You also have to use the OpeningHours module to add new SetProviders. [(read more)](./set-providers.md)

## <a name="sets"></a> Sets
Class [`OpeningHours\Entity\Set`](./../classes/OpeningHours/Entity/Set.php)

Sets contain Periods, Holidays and IrregularOpenings and offser methods to determine the opening status or the next open Period.

```php
use OpeningHours\Util\ArrayObject;

// Check whether Set is open
// Checks for Periods, Holidays and Irregular Openings
$set->isOpen(); // Is the Set currently open?
$set->isOpen(new \DateTime('2016-10-09 13:59:59')); // Will the Set be open on 2016-10-09 at 13:59:59?

// Find the next open Period
// Also takes Holidays and IrregularOpenings into consideration
// Does not include currently running Periods
$period = $set->getNextOpenPeriod();
$period = $set->getNextOpenPeriod(new \DateTime('2016-10-09'));

$id = $set->getId();
$name = $set->getName();
$description = $set->getDescription();

$periods = $set->getPeriods();
$holidays = $set->getHolidays();
$irregularOpenings = $set->getIrregularOpenings();
// $periods, $holidays, $irregularOpenings instanceof ArrayObject
```

## <a name="periods"></a> Periods
Class [`OpeningHours\Entity\Periods`](./../classes/OpeningHours/Entity/Period.php)

Periods consist of a weekday, a start time and and end time. Start time and end time are \DateTime objects
whose date is by default the date in the current week-context.
Week-context is aware your `start_of_week` WordPress setting.

```php
use OpeningHours\Entity\Period;

// Now is 2016-10-06 12:00:00
// Start of Week is Monday
$now = new DateTime('2016-10-06 12:00:00');

$period = new Period(1, '13:00', '17:00');
$period->getWeekday(); // 1 (Monday)
$period->getTimeStart(); // DateTime('2016-10-02 13:00:00')
$period->getTimeEnd(); // DateTime('2016-01-02 17:00:00')

$periodFri = new Period(5, '13:00', '17:00');
$periodFri->getWeekday(); // 5 (Friday)
$periodFri->getTimeStart(); // DateTime('2016-10-07 13:00:00')
$periodFri->getTimeEnd(); // DateTime('2016-01-07 17:00:00')

// Get copy for next week
$nextWeek = (clone $now)->add(new \DateInterval('P1W')); // DateTime('2016-10-13 12:00:00')
$nwPeriod = $period->getCopyInDateContext($nextWeek);
$nwPeriod->getWeekday(); // 1 (Monday)
$nwPeriod->getTimeStart(); // DateTime('2016-10-10 13:00:00')
$nwPeriod->getTimeEnd(); // DateTime('2016-01-10 17:00:00')

// Is this specific Period open in the context of $set?
$period->isOpen($now, $set); // bool

$period->compareToDateTime(new \DateTime('2016-10-06 17:00:01')); // -1; period is in past
$period->compareToDateTime(new \DateTime('2016-10-06 15:00:00')); // 0; period is currently running
$period->compareToDateTime(new \DateTime('2016-10-06 12:59:59')); // 1; period in in future

// Will the Period be open to the scheduled time or overridden by Holiday or IrregularOpening in $set?
$period->willBeOpen($set);

// Compare Periods without date context
$period->equals($nwPeriod); // true
$p1 = new Period(1, '13:00', '17:00');
$p2 = new Period(5, '13:00', '17:00');
$p1->equals($p2); // false
// Ignore Weekday (second parameter)
$p1->equals($p2, true); // true
```

## <a name="holidays"></a> Holidays
Class [`OpeningHours\Entity\Holidays`](./../classes/OpeningHours/Entity/Holiday.php)

```php
use OpeningHours\Entity\Holiday;

$holiday = new Holiday('My Holidays', new \DateTime('2016-10-02'), new \DateTime('2016-10-07'));
$holiday->getName(); // 'My Holidays'
$holiday->getDateStart(); // DateTime('2016-10-02 00:00:00')
$holiday->getDateEnd(); // DateTime('2016-10-06 23:59:59')

$holiday->isActive(); // true
$holiday->isActive(new \DateTime('2016-10-03 13:04:00')); // true
$holiday->isActive(new \DateTime('2016-10-08')); // false
```

## <a name="irregular-openings"></a> Irregular Openings
Class [`OpeningHours\Entity\IrregularOpening`](./../classes/OpeningHours/Entity/IrregularOpening.php)

```php
use OpeningHours\Entity\IrregularOpening;

$irregularOpening = new IrregularOpening('IO', '2016-10-03', '13:00', '17:00');
$irregularOpening->getName(); // 'IO'
$irregularOpening->getTimeStart(); // DateTime('2016-10-03 13:00:00')
$irregularOpening->getTimeEnd(); // DateTime('2016-10-03 17:00:00')
$irregularOpening->getDate(); // DateTime('2016-10-03 00:00:00')

// Check if Irregular Opening is active on a specific day
$irregularOpening->isActiveOnDay(new DateTime('2016-10-02 23:59:59')); // false
$irregularOpening->isActiveOnDay(new DateTime('2016-10-03 00:00:00')); // true
$irregularOpening->isActiveOnDay(new DateTime('2016-10-03 23:59:59')); // true
$irregularOpening->isActiveOnDay(new DateTime('2016-10-04 00:00:00')); // false

// Check whether Irregular Opening is open
$irregularOpening->isOpen(new DateTime('2016-10-03 12:59:59')); // false
$irregularOpening->isOpen(new DateTime('2016-10-03 13:00:00')); // true
$irregularOpening->isOpen(new DateTime('2016-10-03 17:00:00')); // true
$irregularOpening->isOpen(new DateTime('2016-10-03 00:00:01')); // false

// Create Period from IrregularOpening
$period = $irregularOpening->createPeriod();
$period->getWeekday(); // 2 (Tuesday)
$period->getTimeStart(); // DateTime('2016-10-03 13:00:00')
$period->getTimeEnd(); // DateTime('2016-10-03 17:00:00')
```

[Table of contents](./../README.md)

