# WordPress Opening Hours
[![Build Status](https://travis-ci.org/janizde/WP-Opening-Hours.svg?branch=develop)](https://travis-ci.org/janizde/WP-Opening-Hours)

Opening Hours is a highly customizable WordPress plugin to set up your venue's opening hours and display them with Shortcodes and Widgets.

## <a name="contents"></a>Contents
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
	* [Contributing to Code](#contributing-to-code)
	* [Contributing to Translations](#contributing-to-translations)
* [Changelog](#changelog)
* [License](#license)

## <a name="features"></a>Features
* Supports multiple Sets of Opening Hours (e.g. one for your restaurant and one fpr your bar) that you can use independently.
* Supports Hollidays
* Supports Irregular Openings (e.g. different opening hours during Christmas)
* Supports child sets that overwrite your regular opening hours in a specific time period (e.g. seasonal opening hours or an extra day in every second week)
* Four highly customizable Widgets and Shortcodes also displaying contextual information (e.g. "We're currently closed but will be open again on Monday at 8am")

[↑ Table of Contents](#contents)

## <a name="installation"></a>Installation

**Please Note: The Opening Hours Plugin is currently in beta. If you want to use the beta version, you will have to clone the repository or download the .zip file and install it manually**

### <a name="wordpress-plugin-installer"></a>WordPress Plugin Installer
1. Go to your WordPress dashboard
1. Navigate to "Plugins"
1. Click "Install"
1. Search for "Opening Hours"
1. Click "Install" on the Plugin "Opening Hours" by Jannik Portz
1. Activate the Plugin

### <a name="manual-installation"></a>Manual Installation
1. Download the .zip-archive from <https://wordpress.org/plugins/wp-opening-hours/>
1. Unzip the archive
1. Upload the directory /opening-hours to your wp-content/plugins
1. In your Admin Panel go to Plugins and active the Opening Hours Plugin
1. Now you can edit your Opening Hours in the Settings-Section
1. Place the Widgets in your Sidebars or use the Shortcode in your posts and Pages

### <a name="composer"></a>Composer
If you are managing your WordPress Plugins via composer (e.g. when using [Bedrock](https://roots.io/bedrock/docs/composer/)) the Opening Hours Plugin is also available on [wpackagist](https://wpackagist.org/).

Make sure you have wpackagist registered as repository in your composer.json file

~~~json
"repositories": [
  {
    "type": "composer",
    "url": "https://wpackagist.org"
  }
],
~~~

Add the Opening Hours plugin as dependency

~~~json
"require": {
  "wpackagist-plugin/wp-opening-hours": "1.2"
}
~~~

[↑ Table of Contents](#contents)

## <a name="getting-started"></a>Getting Started
### <a name="set-up"></a>Setting up your Opening Hours

The first step to set up your Opening Hours is to create a Set.
A Set consists of Periods for all weekdays, Holidays and Irregular Openings.
If you only want to display the Opening Hours for one venue you're fine with a single Set but you can as well add multiple Sets, each representing individual Opening Hours. You can for example add one Set for your restaurant and one Set for your Bar if you use one website for them and specify the desired Set per Widget or Shortcode.

**Please Note: You will need to have administrator priviledges to manage Sets**

**Step 1:** Go to your admin Dashboard and navigate to "Opening Hours". You will see a list of all your Sets. To add a new Set click "Add New" next to the heading.

![Opening Hours Menu](./doc/screenshots/menu.png)

**Step 2:** Give your Set a name in the "Enter title here" input. The name is only used internally and you can specify individual titles per Widget or Shortcode.

![Specify Set name](./doc/screenshots/set-name.png)

**Step 3:** Set up Opening Hours. In the Opening Hours Section you can edit the time inputs for each weekday. When clicking the `+`-Button you can add more periods per day. When clicking the `x`-Button next to a period you can delete periods.

![Specify Opening Hours](./doc/screenshots/opening-hours.png)

**Step 4:** Set up Holidays. In the Holidays Section you can edit the name and the start and end dates. When clicking the "Add New Holiday" you can add more Holidays. You can also delete holidays when clicking the `x`-Button next to a Holiday.

![Specify Holidays](./doc/screenshots/holidays.png)

**Step 5:** Set up Irregular Openings. Irregular Openings specify irregular opening hours for a specific day. You would for example add an Irregular Opening for NYE when you are only open in the morning. You can edit the name, the date and start and end time.  
When clicking the "Add New Irregular Opening" you can add more Irregular Openings. You can also delete Irregular Openings when clicking the `x`-Button next to a row.

![Specify Irregular Openings](./doc/screenshots/irregular-openings.png)

**Step 6:** In the Set Details Section you can give your Set a description. This is optional but the description can be displayed in the Overview Widget/Shortcode.

![Specify Set name](./doc/screenshots/set-description.png)

**Step 7:** Save the data by clicking the "Save"/"Publish"-Button. **Any changes will not be saved without saving the whole Set!**

### <a name="child-sets"></a>Child Sets

You may also set up child Sets with different Opening Hours for a longer Period of time. You can define a date range or a week scheme (even/odd weeks) when the Opening Hours of the Child Set should be used. You can for example use Child Sets if you have different Opening Hours in winter.   
In Child Sets you can only set up Opening Hours but no Holidays or Irregular Openings.

**Step 1:** Make sure you have another Set which you can use as parent Set with the "regular" Opening Hours.

**Step 2:** Add a new Set by clicking the "Add New"-Button in the list of Sets.

**Step 3:** In the Attributes Section select the parent Set under "Parent".

![Specify parent Set](./doc/screenshots/child-set-parent.png)

**Step 4:** Click the "Save"/"Publish"-Button

**Step 5:** Set up the custom Opening Hours for the Child Set.

**Step 6:** Set the usage criteria in the Set Details Section. You can set a start and end date and/or a week scheme. Note that if you don't set start or end date and leave week scheme at "Every Week" the Child Set will never be used.

![Specify child Set criteria](./doc/screenshots/child-set-criteria.png)

**Step 7:** Save the Child Set.

The Plugin will now automatically use the Opening Hours of the Child Set when the usage criteria matches the current time.

[↑ Table of Contents](#contents)

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

#### Overview Widget in table view
![Overview Widget Table](./doc/screenshots/widget-overview-table.png)

#### Overview Widget in list view
![Overview Widget List](./doc/screenshots/widget-overview-list.png)

#### Overview Widget Options
![Overview Widget Options](./doc/screenshots/widget-overview-options.png)

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

#### Is Open Widget showing next open Period
![Is Open Widget](./doc/screenshots/widget-is-open.png)

#### Is Open Widget Options
![Is Open Widget Options](./doc/screenshots/widget-is-open-options.png)

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

#### Holidays Widget in table view
![Holidays Widget in table view](./doc/screenshots/widget-holidays-table.png)

#### Holidays Widget in list view
![Holidays Widget in list view](./doc/screenshots/widget-holidays-list.png)

#### Holidays Widget Options
![Holidays Widget options](./doc/screenshots/widget-holidays-options.png)

### <a name="irregular-openings-widget"></a>Irregular Openings Widget

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

#### Irregular Openings Widget in list view
![Irregular Openings Widget in list view](./doc/screenshots/widget-irregular-openings-list.png)

#### Irregular Openings Widget options
![Irregular Openings Widget options](./doc/screenshots/widget-irregular-openings-options.png)

[↑ Table of Contents](#contents)

## <a name="shortcodes"></a>Shortcodes
Shortcodes have exactly the same options as Widgets because every Widget is basically a representation of the corresponding Shortcode with a GUI for the Widget edit section.  
**The only required attribute for all Shortcodes is `set_id`. All other attributes are optional!**

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

[↑ Table of Contents](#contents)

## <a name="filters"></a>Filters
There are two filters for all Shortcodes that you can hook into to modify the data. Both filters are executed right before the HTML for the Shortcode is generated.  
Mind that every Widget internally uses the corresponding Shortcode **so these filters will work for both Widgets and Shortcodes.**

### `op_shortcode_attributes`
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

#### Example: Always use a custom date and time format for Irregular Openings
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

### `op_shortcode_template`
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

#### Example: Specify own shortcode template for Holidays
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

[↑ Table of Contents](#contents)
## <a name="contributing"></a>Contributing
### <a name="contributing-to-code"></a>Contribute to Code

The development of the Opening Hours Plugin takes place at [GitHub](https://github.com/janizde/WP-Opening-Hours).  
If you want to contribute feel free to fork the repository and send pull requests.

##### <a name="git-flow"></a>GitFlow 
The project uses GitFlow. You can get more information on GitFlow on the [GitFlow Cheat Sheet](http://danielkummer.github.io/git-flow-cheatsheet/).  
When forking the repository for contributions please fork from the `develop` branch. If the pull request will be accepted it will be released to the `master` branch for a new version of the Plugin.

##### Unit Testing
The core logic of the Plugin (classes that are not in the `OpeningHours\Module` namespace) is covered with [PHPUnit](https://phpunit.de/) tests (version 4.8).  
If you find an issue in the core logic please write one or more unit test which demonstrate this issue. If you add something to the core logic please also write a unit test. Also, before sending a pull request, run all unit tests to check whether your change has broken anything (it will be automatically tested by travis anyway).

### <a name="contributing-to-translations"></a>Contribute to Translations

If you want the Plugin to be compatible with your language, you can easily translate it and contribute to the project. There are two ways how you can translate the Plugin to your language.

##### Pull Request on GitHub
If you know how Plugin translations are made with gettext, the preferred way is to fork the repository on [GitHub](https://github.com/janizde/WP-Opening-Hours) (please mind to fork from the `develop` branch as explained in the [section above on GitFlow](#git-flow)), translate the Plugin and then send a pull request.

1. Fork the Plugin on [GitHub](https://github.com/janizde/WP-Opening-Hours) from the `develop` branch
1. In the `/translations` directory you will find all translations and the `opening-hours.pot` file which contains all strings that can be translated.
1. Create a new translation (a `.po` file) with [PoEdit](https://poedit.net/) and name it `opening-hours-{locale}.po`. [Click here](https://make.wordpress.org/polyglots/teams/) for a full list of locales supported by WordPress.
1. In the PoEdit-Menu go to **Catalog** -> **Update from POT-File** and select the `opening-hours.pot` file.
1. Translate all strings to your language.
1. Save the File
1. Commit **both**, the .po and .mo files of your translations
1. Send a pull request.

#### [translate.jannikportz.de](http://translate.jannikportz.de)
If you are not familiar with gettext and/or GitHub you can as well add your translations in the GlotPress System for the plugin. **You do not have to** fork the repository, send a pull request or use PoEdit.

1. Register at <http://wp.jannikportz.de/wp-signup.php>
1. Visit <http://translate.jannikportz.de>
1. Sign in with your Account
1. Select the Project `WP Opening Hours`
1. Search for your language. If your language is not in the list of languages please open an issue in the [GitHub repository](https://github.com/janizde/WP-Opening-Hours) and I will add a new translation set for you.
1. Translate the strings.

Before releasing a new version I will update the translatable string on GlotPress and integrate all translations made with the new release.

If you can't wait for a new release containing your translations you can directly add it to your installation of the plugin:

1. Select the translation set in the list of available languages.
1. Scroll to the bottom.
1. In the line below the legend, select `all current` as `Machine Object Message (.mo)` and click `Export`.
1. Rename the file to `opening-hours-{locale}.mo` (replace `{locale}` with the actual locale of the translation, e.g. `de_DE` for German).
1. Move the file to `/path/to/wordpress/wp-content/plugins/wp-opening-hours/translations`

**Important Note: When you update the Plugin and your translations are not yet included, your translation file will be lost, so before updating better check whether your translation has been added.**

[↑ Table of Contents](#contents)

## <a name="changelog"></a>Changelog
### v2.0.0
Completely new Plugin. When Updating you will have to set up your Opening Hours and Widgets / Shortcodes again!

[↑ Table of Contents](#contents)

## <a name="license"></a>License
Copyright &copy; 2016 Jannik Portz

This program is free software: you can redistribute it and/or modify  
it under the terms of the GNU General Public License as published by  
the Free Software Foundation, either version 3 of the License, or  
(at your option) any later version.

This program is distributed in the hope that it will be useful,  
but WITHOUT ANY WARRANTY; without even the implied warranty of  
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the  
GNU General Public License for more details.

You should have received a copy of the GNU General Public License  
along with this program.  If not, see <http://www.gnu.org/licenses/>.

[↑ Table of Contents](#contents)