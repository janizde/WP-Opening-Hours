# WordPress Opening Hours
Opening Hours is a highly customizable WordPress plugin to set up your venue's opening hours and display them with Shortcodes and Widgets.

## Contents
* [Features](#features)
* [Installation](#installation)
	* [WordPress Plugin Installer](#wordpress-plugin-installer)
	* [Manual Installation](#manual-installation)
	* [Composer](#composer)
* [Getting Started](#getting-started)
	* [Setting up your Opening Hours](#set-up)
	* [Child Sets](#child-sets)
* [Widgets](#widgets)
	* [Overview Widget](#overview-widget)
	* [Is Open Widget](#is-open-widget)
	* [Holidays Widget](#holidays-widget)
	* [Irregular Openings Widget](#irregular-openings-widget)
* [Shortcodes](#shortcodes)
	* [Common Attributes](#common-attributes)
	* [[op-overview] Shortcode](#op-overview-shortcode)
	* [[op-is-open] Shortcode](#op-is-open-shortcode)
	* [[op-holidays] Shortcode](#op-holidays-shortcode)
	* [[op-irregular-openings] Shortcode](#op-irregular-openings-shortcode)
* [Filters](#filters)
* [Action Hooks](#actions)
* [Contributing](#contributing)
* [Changelog](#changelog)

## <a name="features"></a>Features
* Supports multiple Sets of Opening Hours (e.g. one for your restaurant and one fpr your bar) that you can use independently.
* Supports Hollidays
* Supports Irregular Openings (e.g. different opening hours during Christmas)
* Supports child sets that overwrite your regular opening hours in a specific time period (e.g. seasonal opening hours or an extra day in every second week)
* Four highly customizable Widgets and Shortcodes also displaying contextual information (e.g. "We're currently closed but will be open again on Monday at 8am")

## <a name="installation"></a>Installation
## <a name="getting-started"></a>Getting Started
### <a name="set-up"></a>Setting up your Opening Hours
### <a name="child-sets"></a>Child Sets
## <a name="widgets"></a>Widgets
### <a name="overview-widget"></a>Overview Widget
The Overview widget displays a table with all the opening hours in the speficied set.  
There are the following options:

<table>
	<thead>
		<th width="25%">Name</th>
		<th>Description</th>
	</thead>
	<tbody>
		<tr>
			<td>Title</td>
			<td>The title of the Widget. Will be displayed above the opening hours</td>
		</tr>
		<tr>
			<td>Set to show</td>
			<td>Select the set whose opening hours you want to show</td>
		</tr>
		<tr>
			<td>Highlight</td>
			<td>
			Select which type of information shall be highlighted.<br>
			Possible options are:
			<ul>
				<li>Nothing</li>
				<li>Running Period</li>
				<li>Current Weekday</li>
			</ul>
			</td>
		</tr>
		<tr>
			<td>Show closed days</td>
			<td>Whether to display a row for closed days with a "Closed"-caption</td>
		</tr>
		<tr>
			<td>Show description</td>
			<td>Whether to display the set description above the opening hours</td>
		</tr>
		<tr>
			<td>Compress opening hours</td>
			<td>Whether to compress the opening hours. This means that the plugin will search for days with mutual opening hours and then group those together to one row in the table with a title like "Monday - Wednesday".</td>
		</tr>
		<tr>
			<td>Use short day captions</td>
			<td>Whether to use abbreviations for weekdays. E.g. "Monday" becomes "Mon.". This feature is also available in all other supported languages.</td>
		</tr>
		<tr>
			<td>Include Irregular Openings</td>
			<td>If there is an irregular opening on any day in the table it will replace the regular opening hours with the irregular opening hours for that day.</td>
		</tr>
		<tr>
			<td>Include Holidays</td>
			<td>If there is a holiday during one or more days in the table it will replace the regular opening hours of those days with the name of the holiday.</td>
		</tr>
		<tr>
			<td>Template</td>
			<td>You can choose among two templates: Table and List. The list template will display all data in a vertical list. This is useful for narrow sidebars.</td>
		</tr>
	</tbody>
	<thead>
		<th colspan="2">Extended Settings</th>
	</thead>
	<tbody>
		<tr>
			<td>Caption closed</td>
			<td>Speficy a custom caption for closed days.</td>
		</tr>
		<tr>
			<td>Highlighted period class</td>
			<td>Custom CSS class for highlighted periods. default <code>highlighted</code></td>
		</tr>
		<tr>
			<td>Highlighted day class</td>
			<td>Custom CSS class for highlighted days. default: <code>highlighted</code></td>
		</tr>
		<tr>
			<td>PHP Time Format</td>
			<td>Custom format for times. The default is your standard WordPress setting. <a href="http://bit.ly/16Wsegh" target="_blank">More on PHP date and time formats</a></td>
		</tr>
		<tr>
			<td>Hide date of irregular openings</td>
			<td>Whether to hide the date of irregular openings if they are in the table.</td>
		</tr>
	</tbody>
</table>

### <a name="is-open-widget"></a>Is Open Widget
The Is Open Widget displays a message whether a venue (a Set) is currently open/active.  
There are the folliwing options:

<table>
	<thead>
		<th width="25%">Name</th>
		<th>Description</th>
	</thead>
	<tbody>
		<tr>
			<td>Title</td>
			<td>The Widget Title</td>
		</tr>
		<tr>
			<td>Set</td>
			<td>Select a set whose opening status you want to show</td>
		</tr>
		<tr>
			<td>Show next open period</td>
			<td>When select, a message telling the next open period will be displayed if the venue (set) is currently closed.</td>
		</tr>
	</tbody>
	<thead>
		<th colspan="2">Extended Settings</th>
	</thead>
	<tbody>
		<tr>
			<td>Caption if open</td>
			<td>Custom caption to show when the venue is open</td>
		</tr>
		<tr>
			<td>Cpation if closed</td>
			<td>Custom caption to show when the venue is closed</td>
		</tr>
		<tr>
			<td>Class if open</td>
			<td>Custom CSS class when the venue is open</td>
		</tr>
		<tr>
			<td>Class if closed</td>
			<td>Custom CSS class when the venue is closed</td>
		</tr>
		<tr>
			<td>Next Period string format</td>
			<td>A custom string format for the next open period message.<br />
			You can populate the string with the following placeholders:
			<ul>
				<li><code>%1$s</code> The formatted date of the next open period</li>
				<li><code>%2$s</code> The name of the weekday of the next open period</li>
				<li><code>%3$s</code> The formatted start time of the next open period</li>
				<li><code>%4$s</code> The formatted end time of the next open period</li>
			</ul>
			Example: <code>We're open again on %2$s (%1$s) from %3$s to %4$s</code>
			</td>
		</tr>
		<tr>
			<td>PHP Date Format</td>
			<td>Custom PHP date format for the date of the next open period. The default is your standard WordPress setting. <a href="http://bit.ly/16Wsegh" target="_blank">More on PHP date and time formats</a></td>
		</tr>
		<tr>
			<td>PHP Time Format</td>
			<td>Custom PHP date format for the start and end time of the next open period. The default is your standard WordPress setting. <a href="http://bit.ly/16Wsegh" target="_blank">More on PHP date and time formats</a></td>
		</tr>
	</tbody>
</table>

### <a name="holidays-widget"></a>Holidays Widget
The holiday widget displays all holidays in the specified set in a table or list.  
There are the following options:

<table>
	<thead>
		<th width="25%">Name</th>
		<th>Description</th>
	</thead>
	<tbody>
		<tr>
			<td>Title</td>
			<td>The Widget title</td>
		</tr>
		<tr>
			<td>Set</td>
			<td>Select a set whose holidays you want to display.</td>
		</tr>
		<tr>
			<td>Highlight active holidays</td>
			<td>Whether to highlight active holidays in the table</td>
		</tr>
		<tr>
			<td>Template</td>
			<td>You can choose among two templates: Table and List. The list template will display all data in a vertical list. This is useful for narrow sidebars.</td>
		</tr>
	</tbody>
	<thead>
		<th colspan="2">Extended Settings</th>
	</thead>
	<tbody>
		<tr>
			<td>Class for highlighted Holiday</td>
			<td>Custom CSS class for highlighted Holidays. default: <code>highlighted</code></td>
		</tr>
		<tr>
			<td>PHP Date Format</td>
			<td>Custom PHP date format for the start and end date of the holidays. The default is your standard WordPress setting. <a href="http://bit.ly/16Wsegh" target="_blank">More on PHP date and time formats</a></td>
		</tr>
	</tbody>
</table>

### <a name="irregular-openings-widget"></a>Irregular Openings

The Irregular Openings Widget displays all Irregular Openings in the specified Set in a table or list.  
There are the following options:

<table>
	<thead>
		<th width="25%">Name</th>
		<th>Description</th>
	</thead>
	<tbody>
		<tr>
			<td>Title</td>
			<td>The Widget title</td>
		</tr>
		<tr>
			<td>Set</td>
			<td>Select a Set whose Irregular Openings you want to show.</td>
		</tr>
		<tr>
			<td>Highlight active Irregular Opening</td>
			<td>Whether to highlight active irregular openings in the table or list</td>
		</tr>
		<tr>
			<td>Template</td>
			<td>You can choose among two templates: Table and List. The list template will display all data in a vertical list. This is useful for narrow sidebars.</td>
		</tr>
	</tbody>
	<thead>
		<th colspan="2">Extended Settings</th>
	</thead>
	<tbody>
		<tr>
			<td>Class for Highlighted Irregular Opening</td>
			<td>Custom CSS class for highlighted Irregular Openings in the table or list. default: <code>highlighted</code></td>
		</tr>
		<tr>
			<td>PHP Date Format</td>
			<td>Custom PHP date format for the date of the irregular openings. The default is your standard WordPress setting. <a href="http://bit.ly/16Wsegh" target="_blank">More on PHP date and time formats</a></td>
		</tr>
		<tr>
			<td>PHP Time Format</td>
			<td>Custom PHP date format for the start and end time of the irregular openings. The default is your standard WordPress setting. <a href="http://bit.ly/16Wsegh" target="_blank">More on PHP date and time formats</a></td>
		</tr>
	</tbody>
</table>

## <a name="shortcodes"></a>Shortcodes
Shortcodes have exactly the same options as Widgets because every Widget is basically a representation of the corresponding Shortcode with a GUI for the Widget edit section.  
**The only required attribute for all Shortcodes is set_id. All other attributes are optional!**

### <a name="common-attributes"></a>Common attributes for all Shortcodes
<table>
	<thead>
		<th width="25%">Name</th>
		<th width="15%">Type</th>
		<th width="15%">Default</th>
		<th width="45%">Description</th>
	</thead>
	<tbody>
		<tr>
			<td><code>set_id</code></td>
			<td><code>int</code></td>
			<td>–</td>
			<td><strong>(required)</strong> The id of the set whose data you want to show</td>
		</tr>
		<tr>
			<td><code>title</code></td>
			<td><code>string</code></td>
			<td>–</td>
			<td>The widget title</td>
		</tr>
		<tr>
			<td><code>before_title</code></td>
			<td><code>string</code></td>
			<td><code>&lt;h3 class="op-{name}-title"&gt;</code></td>
			<td>HTML before the title</td>
		</tr>
		<tr>
			<td><code>after_title</code></td>
			<td><code>string</code></td>
			<td><code>&lt;/h3&gt;</code></td>
			<td>HTML after the title</td>
		</tr>
		<tr>
			<td><code>before_widget</code></td>
			<td><code>string</code></td>
			<td><code>&lt;div class="op-{name}-shortcode"&gt;</code></td>
			<td>HTML before shortcode contents</td>
		</tr>
		<tr>
			<td><code>after_widget</code></td>
			<td><code>string</code></td>
			<td><code>&lt;/div&gt;</code></td>
			<td>HTML after shortcode contents</td>
		</tr>
	</tbody>
</table>

### <a name="op-overview-shortcode"></a>op-overview Shortcode
Corresponds to the Overview Widget.  
The **[op-overview]** shortcode displays the opening hours of the specified set.  
The following attributes are available (Also mind the **[Common Attributes](#common-attributes)**):

<table>
	<thead>
		<th width="25%">Name</th>
		<th width="15%">Type</th>
		<th width="15%">Default</th>
		<th width="45%">Description</th>
	</thead>
	<tbody>
		<tr>
			<td><code>show_closed_days</code></td>
			<td><code>bool</code></td>
			<td><code>false</code></td>
			<td>Whether to display a row for closed days with a "Closed"-caption</td>
		</tr>
		<tr>
			<td><code>show_description</code></td>
			<td><code>bool</code></td>
			<td><code>false</code></td>
			<td>Whether to display the set description above the opening hours</td>
		</tr>
		<tr>
			<td><code>highlight</code></td>
			<td><code>string</code></td>
			<td><code>noting</code></td>
			<td>What type of information to highlight. Possible values are: <code>noting</code>, <code>period</code> (currently active period), <code>day</code> (current weekday)</td>
		</tr>
		<tr>
			<td><code>compress</code></td>
			<td><code>bool</code></td>
			<td><code>false</code></td>
			<td>Whether to compress the opening hours. This means that the plugin will search for days with mutual opening hours and then group those together to one row in the table with a title like "Monday - Wednesday".</td>
		</tr>
		<tr>
			<td><code>short</code></td>
			<td><code>bool</code></td>
			<td><code>false</code></td>
			<td>Whether to use abbreviations for weekdays. E.g. "Monday" becomes "Mon.". This feature is also available in all other supported languages.</td>
		</tr>
		<tr>
			<td><code>include_io</code></td>
			<td><code>bool</code></td>
			<td><code>false</code></td>
			<td>If there is an irregular opening on any day in the table it will replace the regular opening hours with the irregular opening hours for that day.</td>
		</tr>
		<tr>
			<td><code>include_holidays</code></td>
			<td><code>bool</code></td>
			<td><code>false</code></td>
			<td>If there is a holiday during one or more days in the table it will replace the regular opening hours of those days with the name of the holiday.</td>
		</tr>
		<tr>
			<td><code>highlighted_period_class</code></td>
			<td><code>string</code></td>
			<td><code>highlighted</code></td>
			<td>CSS class for highlighted periods</td>
		</tr>
		<tr>
			<td><code>highlighted_day_class</code></td>
			<td><code>string</code></td>
			<td><code>highlighted</code></td>
			<td>CSS class for current weekday</td>
		</tr>
		<tr>
			<td><code>time_format</code></td>
			<td><code>string</code></td>
			<td>WordPress setting</td>
			<td>Custom format for times. The default is your standard WordPress setting. <a href="http://bit.ly/16Wsegh" target="_blank">More on PHP date and time formats</a></td>
		</tr>
		<tr>
			<td><code>hide_io_date</code></td>
			<td><code>bool</code></td>
			<td><code>false</code></td>
			<td>Whether to hide the date of irregular openings if they are in the table.</td>
		</tr>
		<tr>
			<td><code>template</code></td>
			<td><code>string</code></td>
			<td><code>table</code></td>
			<td>Identifier for the template to use. Possible values are <code>table</code> and <code>list</code></td>
		</tr>
	</tbody>
</table>

### <a name="op-is-open-shortcode"></a>op-is-open Shortcode
Corresponds to the Is Open Widget.  
The **[op-is-open]** shortcode displays a message whether the specified venue (set) is currently open or not.  
The following attributes are available (Also mind the **[Common Attributes](#common-attributes)**):

<table>
	<thead>
		<th width="25%">Name</th>
		<th width="15%">Type</th>
		<th width="15%">Default</th>
		<th width="45%">Description</th>
	</thead>
	<tbody>
		<tr>
			<td><code>open_text</code></td>
			<td><code>string</code></td>
			<td>We're currently open (translated)</td>
			<td>Caption to show when the venue is open</td>
		</tr>
		<tr>
			<td><code>closed_text</code></td>
			<td><code>string</code></td>
			<td>We're currently closed (translated)</td>
			<td>Caption to show when the venue is closed</td>
		</tr>
		<tr>
			<td><code>show_next</code></td>
			<td><code>bool</code></td>
			<td><code>false</code></td>
			<td>When <code>true</code>, a message telling the next open period will be displayed if the venue (set) is currently closed.</td>
		</tr>
		<tr>
			<td><code>next_format</code></td>
			<td><code>string</code></td>
			<td>We're open again on <code>%2$s</code> (<code>%1$s</code>) from <code>%3$s</code> to <code>%4$s</code></td>
			<td>A custom string format for the next open period message.<br />
			You can populate the string with the following placeholders:
			<ul>
				<li><code>%1$s</code> The formatted date of the next open period</li>
				<li><code>%2$s</code> The name of the weekday of the next open period (translated)</li>
				<li><code>%3$s</code> The formatted start time of the next open period</li>
				<li><code>%4$s</code> The formatted end time of the next open period</li>
			</ul></td>
		</tr>
		<tr>
			<td><code>open_class</code></td>
			<td><code>string</code></td>
			<td><code>op-open</code></td>
			<td>CSS class if the venue (set) is open</td>
		</tr>
		<tr>
			<td><code>closed_class</code></td>
			<td><code>string</code></td>
			<td><code>op-closed</code></td>
			<td>CSS class if the venue (set) is closed</td>
		</tr>
		<tr>
			<td><code>date_format</code></td>
			<td><code>string</code></td>
			<td>WordPress setting</td>
			<td>PHP date format for the date of the next open period. <a href="http://bit.ly/16Wsegh" target="_blank">More on PHP date and time formats</a></td>
		</tr>
		<tr>
			<td><code>time_format</code></td>
			<td><code>string</code></td>
			<td>WordPress setting</td>
			<td>PHP date format for the start and end time of the next open period. <a href="http://bit.ly/16Wsegh" target="_blank">More on PHP date and time formats</a></td>
		</tr>
	</tbody>
</table>

### <a name="op-holidays-shortcode"></a>op-holidays Shortcode
Corresponds to the Holidays Widget.  
The **[op-holidays]** shortcode displays all holidays in the specified set in a table or list.  
The following attributes are available (Also mind the **[Common Attributes](#common-attributes)**):

<table>
	<thead>
		<th width="25%">Name</th>
		<th width="15%">Type</th>
		<th width="15%">Default</th>
		<th width="45%">Description</th>
	</thead>
	<tbody>
		<tr>
			<td><code>highlight</code></td>
			<td><code>bool</code></td>
			<td><code>false</code></td>
			<td>Whether to highlight currently active holidays</td>
		</tr>
		<tr>
			<td><code>class_holiday</code></td>
			<td><code>string</code></td>
			<td><code>op-holiday</code></td>
			<td>CSS class for a single holiday</td>
		</tr>
		<tr>
			<td><code>class_highlighted</code></td>
			<td><code>string</code></td>
			<td><code>highlighted</code></td>
			<td>CSS class for highlighted holidays</td>
		</tr>
		<tr>
			<td><code>date_format</code></td>
			<td><code>string</code></td>
			<td>WordPress setting</td>
			<td>PHP date format for the start and end date of the holidays. <a href="http://bit.ly/16Wsegh" target="_blank">More on PHP date and time formats</a></td>
		</tr>
		<tr>
			<td><code>template</code></td>
			<td><code>string</code></td>
			<td><code>table</code></td>
			<td>Identifier for the template to use. Possible values are <code>table</code> and <code>list</code></td>
		</tr>
	</tbody>
</table>

### <a name="op-irregular-openings-shortcode"></a>op-irregular-openings Shortcode
Corresponds to the Irregular Openings Widget.  
The **[op-irregular-openings]** shortcode displays all irregular openings in the specified set in a table or list.  
The following attributes are available (Also mind the **[Common Attributes](#common-attributes)**):

<table>
	<thead>
		<th width="25%">Name</th>
		<th width="15%">Type</th>
		<th width="15%">Default</th>
		<th width="45%">Description</th>
	</thead>
	<tbody>
		<tr>
			<td><code>highlight</code></td>
			<td><code>bool</code></td>
			<td><code>false</code></td>
			<td>Whether to highlight currently active irregular openings.</td>
		</tr>
		<tr>
			<td><code>class_highlighted</code></td>
			<td><code>string</code></td>
			<td><code>highlighted</code></td>
			<td>CSS class for highlighted irregular openings</td>
		</tr>
		<tr>
			<td><code>date_format</code></td>
			<td><code>string</code></td>
			<td>WordPress setting</td>
			<td>PHP date format for the date of the irregular openings. <a href="http://bit.ly/16Wsegh" target="_blank">More on PHP date and time formats</a></td>
		</tr>
		<tr>
			<td><code>time_format</code></td>
			<td><code>string</code></td>
			<td>WordPress setting</td>
			<td>PHP date format for the start and end time of the irregular openings. <a href="http://bit.ly/16Wsegh" target="_blank">More on PHP date and time formats</a></td>
		</tr>
		<tr>
			<td><code>template</code></td>
			<td><code>string</code></td>
			<td><code>table</code></td>
			<td>Identifier for the template to use. Possible values are <code>table</code> and <code>list</code></td>
		</tr>
	</tbody>
</table>

## <a name="filters"></a>Filters
## <a name="actions"></a>Action Hooks
## <a name="contributing"></a>Contributing
## <a name="changelog"></a>Changelog
### v2.0.0
Completely new Plugin.
[php_date_formats]: http://bit.ly/16Wsegh "More on PHP date and time formats"