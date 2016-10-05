# Set Providers
Sets can not only be defined in the Opening Hours admin section, but rather the admin section and the corresponding custom post type is only one way of prividing the Plugin with Sets of Opening Hours, Holidays and Irregular Openings.

With the loosely coupled SetProvider API you can easily implement your own algorithms to retrieve Sets. This can for example be:

* Staticly configured Sets shipped with your custom theme
* Sets loaded from configuration files on your server
* Sets retrieved from an API e.g. the Google Places API Opening Hours

The PostSetProvider that retrieves Sets from the custom post type is currently the only one shipped with the Plugin but you can easily implement your own.

## What is a Set Provider
SetProviders are classes that can retrieve none, one or multiple Sets. So you would always implement one SetProvider for a specific source of Opening Hours.

SetProviders must extend the abstract `SetProvider` class and implement the following methods:

### `protected function createAvailableSetInfo () : array`
This method returns an array containing the IDs and names of all Sets that the SetProvider can serve.  
Try to avoid too much logic and especially database / API requests in this method as it will mostly be called when the actual data will not be used during the request (e.g. when determining all available Sets in the Widget forms)

The method must return an array of associative arrays, each containing `id`, a string or integer represesenting the ID of the set and `name` containing a string with the Set name. For example:

```php
[
	[
		'id' => 'first-set',
		'name' => 'First Set'
	],
	[
		'id' => 30,
		'name' => 'Second Set'
	]
]
```

Please be aware that integer IDs may collide with the post IDs from the custom post type, so it is recommended to use strings (e.g. using prefixes) in your own SetProviders.

**Heads up:**
To avoid extra database overhead, this method will be called not more than once per request and the result will be stored and cached in the attribute `$setInfo`. If you want to avoid this behavior for some reason, you will have to override the method `getAvailableSetInfo` in your SetProvider implementation like so:

```php
public function getAvailableSetInfo () {
	return $this->createAvailableSetInfo();
}
```

### `public function createSet ($id) : Set`
This method is the actual factory method for the Sets that gets the desired Set id as the parameter `$id`.
This method will only be called with Set IDs that have been returned by `createAvailableSetInfo`.

The method must return an instance of the `Set` class (or any sub-class) populated with Periods, Holidays and Irregular Openings.

### Registering your custom SetProvider
There are two ways to register your custom SetProviders

#### 1. Via method call
You can directly add your SetProvider to the OpeningHours singleton module. It is recommended to do this on the `init` action hook with a priority greater than 10.

```php
use OpeningHours\Module\OpeningHours;

add_action('init', function () {
	// Make sure the Plugin is active
	if (!class_exists('OpeningHours\ModuleOpeningHours'))
		return;

	$openingHours = OpeningHours::getInstance();
	$openingHours->addSetProvider(new MyCustomSetProvider());
}, 11);
```

#### 2. Via filter
You can also filter the array of registered SetProviders. This has the advantage that you don't need to make sure the Plugin is active. Furthermore you can also remove previously registered SetProviders.

```php
add_filter('op_set_providers', function (array $setProviders) {
	$setProviders[] = new MyCustomSetProvider();
	// Make sure to return the array again
	return $setProviders;
});
```

## Example: StaticSetProvider

### The StaticSetProvider class
```php
namespace My\Namespace;

use OpeningHours\Entity\Set;
use OpeningHours\Entity\SetProvider;
use OpeningHours\Entity\Period;
use OpeningHours\Entity\Holiday;
use OpeningHours\Entity\IrregularOpenings;
use OpeningHours\Util\ArrayObject;

class StaticSetProvider extends SetProvider {
	
	protected function createAvailableSetInfo () {
		// You can also return an empty array or more elements
		return array(
			array(
				'id' => 'static-set',
				'name' => 'Static Set'
			)
		);
	}
	
	public function createSet ($id) { // we can ignore $id here as we only provide one Set
		$set = new Set('static-set');
		$set->setName('Static Set');
		$set->setDescription('Static Set description');
		
		// Make sure this is an OpeningHours\Util\ArrayObject and not \ArrayObject
		$periods = new ArrayObject();
		$periods->append(new Period(0, '08:00', '12:00'));
		$periods->append(new Period(0, '13:00', '20:00'));
		// ...
		$set->setPeriods($periods);
		
		$holidays = new ArrayObject();
		$holidays->append(new Holiday('Static Holiday', new \DateTime('2016-10-18')));
		// ...
		$set->setHoliday($holidays);
		
		$irregularOpenings = new ArrayObject();
		$irregularOpenings->append(new IrregularOpening('Irregular Opening', '2016-10-03', '13:00', '19:00'));
		// ...
		$set->setIrregularOpenings($irregularOpenings);
		
		return $set;
	}
}

// ----------------------------------
// Register the SetProvider
// e.g. in your theme's functions.php
// ----------------------------------

use OpeningHours\Module\OpeningHours;
use My\Namespace\StaticSetProvider;

add_action('init', function () {
	if (!class_exists('OpeningHours\Module\OpeningHours'))
		return;
		
	$openingHours = OpeningHours::getInstance();
	$openingHours->addSetProvider(new StaticSetProvider());
});
```
The Static Set should now be available in the Widget form. You can then also use your custom id in a Shortcode e.g. `[op-overview set-id="static-set"]`

[Table of Contents](../README.md#contents)