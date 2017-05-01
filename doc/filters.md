# <a name="filters"></a>Filters
The Widget offers some Filters you can hook into in your custom theme or plugin.
Mind that every Widget internally uses the corresponding Shortcode **so these filters will work for both Widgets and Shortcodes.**

## `op_use_front_end_styles`
With the `op_use_front_end_styles` filter you can control whether the default plugin front end styles shall be registered.  
Use this filter if you want to completely use your own styles to disable the registration of the plugin styles.css

```php
add_filter('op_use_front_end_styles', function ($useFrontEndStyles) {
  return false;
});
```

**Note:** You custom filter must be hooked before the `wp_enqueue_scripts` resp. `admin_enqueue_scripts` actions are executed.

## `op_shortcode_attributes`
With the `op_shortcode_attributes` filter you can filter the associative array containing all Shortcode attributes.

Parameters passed to the filter callback:
<table>
	<thead>
		<th width="25%">Name</th>
		<th width="25%">Type</th>
		<th width="50%">Description</th>
	</thead>
	<tbody>
		<tr>
			<td><code>$attributes</code></td>
			<td><code>array</code></td>
			<td>Associative array containing all shortcode attributes including the Set object under the key `set`. You can see all attributes for the specfic Shortcodes in the section on [Shortcodes](#shortcodes).</td>
		</tr>
		<tr>
			<td><code>$shortcode</code></td>
			<td><code>AbstractShortcode</code></td>
			<td>The Shortcode singleton instance. You can for example check for the type of Shortcode with the `instanceof` operator.</td>
		</tr>
	</tbody>
</table>

### Example: Always use a custom date and time format for Irregular Openings
~~~php
use OpeningHours\Module\Shortcode\IrregularOpenings;

add_filter('op_shortcode_attributes', function (array $attributes, $shortcode) {
	// As this happens just before the HTML is generated
	// it will always override date and time format and ignore custom
	// Widget options.
	if ($shortcode instanceof IrregularOpenings) {
		$attributes['time_format'] = 'H:i:s';
		$attributes['date_format'] = 'd.m.Y';
	}
	
	return $attributes;
}, 10, 2);
~~~
## `op_shortcode_template`
With the `op_shortcode_template` filter you can specify your own shortcode template.

Parameters passed to the filter callback:
<table>
	<thead>
		<th width="25%">Name</th>
		<th width="25%">Type</th>
		<th width="50%">Description</th>
	</thead>
	<tbody>
		<tr>
			<td><code>$template</code></td>
			<td><code>string</code></td>
			<td>Absolute path to a `.php` template file.</td>
		</tr>
		<tr>
			<td><code>$shortcode</code></td>
			<td><code>AbstractShortcode</code></td>
			<td>The Shortcode singleton instance. You can for example check for the type of Shortcode with the `instanceof` operator.</td>
		</tr>
	</tbody>
</table>

### Example: Specify own shortcode template for Holidays
~~~php
use OpeningHours\Module\Shortcode\Holidays;

add_filter('op_shortcode_template', function ($template, $shortcode) {
	// If the Shortcode is a Holidays shortcode return you custom template
	if ($shortcode instanceof Holidays)
		return '/path/to/template.php';
	
	// If it is any other type of Shortcode keep $template unchanged
	return $template;
}, 10, 2);
~~~

## `op_shortcode_markup`
With the `op_shortcode_template` filter you can filter the final Shortcode output. It will be called right before the Plugin returns the Shortcode markup to WordPress.

Parameters passed to the filter callback:
<table>
	<thead>
		<th width="25%">Name</th>
		<th width="25%">Type</th>
		<th width="50%">Description</th>
	</thead>
	<tbody>
		<tr>
			<td><code>$markup</code></td>
			<td><code>string</code></td>
			<td>The final Shortcode markup as HTML string.</td>
		</tr>
		<tr>
			<td><code>$shortcode</code></td>
			<td><code>AbstractShortcode</code></td>
			<td>The Shortcode singleton instance. You can for example check for the type of Shortcode with the `instanceof` operator.</td>
		</tr>
	</tbody>
</table>

### Example: Wrapping the Shortcode markup in a `<section>` tag.

~~~php
add_filter('op_shortcode_markup', function ($markup, $shortcode) {
	// We don't need $shortcode here
	return '<section class="my-section">'.$markup.'</section>';
}, 10, 2);
~~~

**Note:** You can also achieve this by using the `op_shortcode_attributes` filter and modifying the attributes `before_widget` and `after_widget`.

## `op_is_open_format_next`
With this filter you can change the format of the next open period message within the op-is-open shortcode or widget. You can use this filter if the `next_format` shortcode attribute does not fit your needs.

> **Heads up**  
> This filter will only be applied when the `show_next` shortcode attribute / widget option is set to `true`.

Parameters passed to the filter callback:
<table>
	<thead>
		<th width="25%">Name</th>
		<th width="25%">Type</th>
		<th width="50%">Description</th>
	</thead>
	<tbody>
		<tr>
			<td><code>$string</code></td>
			<td><code>string</code></td>
			<td>The string formatted by the plugin. May be the formatted period string according to shortcode / widget settings or `null` if no next open period could be found.</td>
		</tr>
		<tr>
			<td><code>$nextPeriod</code></td>
			<td><code>Period|null</code></td>
			<td>The next open `Period` object or `null` if no next open period could be found.</td>
		</tr>
		<tr>
			<td><code>$attributes</code></td>
			<td><code>array</code></td>
			<td>Associtative array containing all shortcode attributes / widget options.</td>
		</tr>
		<tr>
			<td><code>$todayData</code></td>
			<td><code>array</code></td>
			<td>
				Associative array of arrays containing all data for today. The array has the following structure:
<pre>
[
	'periods' => Period[],
	'holidays' => Holiday[],
	'irregularOpenings' => IrregularOpening[]
]
</pre>
			</td>
		</tr>
	</tbody>
</table>

### Exmaple: showing current holiday name

~~~php
add_filter('op_is_open_format_next', function ($str, $period, $attributes, $todayData) => {
	// If there's no holiday in effect, return default message
	if (count($todayData['holidays']) < 1) {
		return $str;
	}
	
	return sprintf(
		'We\'re currently on %s but will be back on the %s from %s to %s',
		$todayData['holidays'][0]->getName(),
		$period->getTimeStart()->format($attributes['time_format']),
		$period->getTimeEnd()->format($attributes['time_format'])
	);
}, 10, 4);
~~~


## `op_is_open_format_today`
With this filter you can change the format of today's opening hours message within the op-is-open shortcode / widget. You can use this filter if the `today_format` shortcode attribute does not fit your needs.

> **Heads up**  
> This filter will only be applied when the `show_today` shortcode attribute / widget option is set to `true`.

Parameters passed to the filter callback:
<table>
	<thead>
		<th width="25%">Name</th>
		<th width="25%">Type</th>
		<th width="50%">Description</th>
	</thead>
	<tbody>
		<tr>
			<td><code>$string</code></td>
			<td><code>string</code></td>
			<td>The string formatted by the plugin. May be the formatted opening hours string according to the shortcode attributes or null if there are no periods for that day.</td>
		</tr>
		<tr>
			<td><code>$periods</code></td>
			<td><code>Period[]</code></td>
			<td>Array of today's periods. If an irregular opening is in effect it will be converted to a period with the correct weekday and passed as the one and only element of the array.</td>
		</tr>
		<tr>
			<td><code>$attributes</code></td>
			<td><code>array</code></td>
			<td>Associtative array containing all shortcode attributes / widget options.</td>
		</tr>
		<tr>
			<td><code>$todayData</code></td>
			<td><code>array</code></td>
			<td>
				Associative array of arrays containing all data for today. The array has the following structure:
<pre>
[
	'periods' => Period[],
	'holidays' => Holiday[],
	'irregularOpenings' => IrregularOpening[]
]
</pre>
			</td>
		</tr>
	</tbody>
</table>

## `op_overview_model`

With this filter you can filter the `OverviewModel` for the overview shortcode / widget.  
The `OverviewModel` constructor takes two parameters:

* `$periods`: `Period[]` of periods to use for the `OverviewModel`
* `$now`: `\DateTime` containing the current date context (The date context will translate to the week to show in the `OverviewModel`)

> **Heads up**
> 
> * For performance reason the filter callback is not initially populated with `null` instead of the `OverviewModel` created by the plugin.
> * Holidays and Irregular Openings are not merged into your custom `OverviewModel` according to the shortcode attributes. When using this filter, you will have to merge Holidays and Irregular Openings manually using the `mergeHolidays(Holiday[])` resp. `mergeIrregularOpenings(IrregularOpening[])` methods. You may take the shortcode attributes `include_holidays` and `include_irregular_openings` into consideration.

Parameters passed to the filter callback:
<table>
	<thead>
		<th width="25%">Name</th>
		<th width="25%">Type</th>
		<th width="50%">Description</th>
	</thead>
	<tbody>
		<tr>
			<td><code>$model</code></td>
			<td><code>OverviewModel|null</code></td>
			<td>The <code>OverviewModel</code> returned by the previous filter or <code>null</code> if no previous filter has been run.</td>
		</tr>
		<tr>
			<td><code>$set</code></td>
			<td><code>Set</code></td>
			<td>The current set the shortcode uses</td>
		</tr>
		<tr>
			<td><code>$attributes</code></td>
			<td><code>array</code></td>
			<td>Associtative array containing all shortcode attributes / widget options.</td>
		</tr>
	</tbody>
</table>

**Return value**  
The filter may return an instance of the `OverviewModel` or any derived class or `null`.

### Example: Showing shortcode contents for one week in future

~~~php
use OpenignHours\Module\Shortcode\OverviewModel;
use OpeningHours\Util\Dates;

add_filter('op_overview_model', function ($model, $set, $attributes) {
	// Create \DateTime object 1 week in future
	$then = Dates::getNow();
	$then->add(new \DateInterval('P1W'));
	
	// Create new OverviewModel with custom date
	$model = new OverviewModel($set->getPeriods()->getArrayCopy(), $then);
	
	// Merge Holidays and Irregular Openings according to shortcode attributes
	if ($attributes['include_holidays']) {
		$model->mergeHolidays($set->getHolidays()->getArrayCopy());
	}
	
	if ($attributes['include_irregular_openings']) {
		$model->mergeIrregularOpenings($set->getIrregularOpenings()->getArrayCopy());
	}
	
	return $model;
}, 10, 3);
~~~

## `op_set_providers`
With the `op_set_providers` filter you can modify the registered SetProviders of the OpeningHours Module, i.e. adding new SetProviders and removing previously registered ones.  
[Further reading on SetProviders](./set-providers.md)

## <a name="op_set_alias_presets"></a>`op_set_alias_presets`
With the `op_set_alias_presets` filter you can change the list of set aliases suggested to the user on the Set Alias input in the Set details.
You can add set alias presets in your theme or custom plugin to make entering the correct set alias easier for the users.  
E.g. if you use the set alias `main-set` in your theme you may add a preset for this one.

Parameters passed to the callback:

|Name|Type|Description|
|---|---|---|
|`$presets`|`array`|Current value of alias presets (usually empty array)|

### Example: Adding new presets

~~~php
add_filter('op_set_alias_presets', function (array $presets) {
	$presets[] = 'set-alias-1';
	$presets[] = 'set-alias-2';
	$presets[] = 'set-alias-3';
	return $presets;
});
~~~

**Note:** Set alias presets make use of HTML5 `datalist` which is currently not supported on all browsers (i.e. it won't work on Safari and iOS Safari).  
The below screenshot shows the above example in Chrome:

![Set Alias presets in Chrome](./screenshots/set-alias-presets.png)

## Need another filter?
Filters are a great way to give developers more control over the behavior of an external Plugin and are very easy to integrate.  
If you feel you would want to have another filter, open an [issue on GitHub](https://github.com/janizde/WP-Opening-Hours/issues).

[Table of Contents](../README.md#contents)