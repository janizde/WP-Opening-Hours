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
});
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
});
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
});
~~~

**Note:** You can also achieve this by using the `op_shortcode_attributes` filter and modifying the attributes `before_widget` and `after_widget`.

## `op_set_providers`
With the `op_set_providers` filter you can modify the registered SetProviders of the OpeningHours Module, i.e. adding new SetProviders and removing previously registered ones.  
[Further reading on SetProviders](./set-providers.md)

## Need another filter?
Filters are a great way to give developers more control over the behavior of an external Plugin and are very easy to integrate.  
If you feel you would want to have another filter, open an [issue on GitHub](https://github.com/janizde/WP-Opening-Hours/issues).

[Table of Contents](../README.md#contents)