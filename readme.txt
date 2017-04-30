=== Opening Hours ===
Contributors: janizde
Tags: opening hours,business hours,hours,table,overview,date,time,widget,shortcode,status,currently open,bar,restaurant
Tested up to: 4.7.1
Stable tag: 2.0.5
Requires at least: 4.0.0
Donate link: https://github.com/janizde/WP-Opening-Hours#donate
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Opening Hours is a highly customizable WordPress plugin to set up your venue's opening hours and display them with Shortcodes and Widgets.

== Description ==

* Supports multiple Sets of Opening Hours (e.g. one for your restaurant and one for your bar) that you can use independently.
* Supports Holidays
* Supports Irregular Openings (e.g. different opening hours during Christmas)
* Supports child sets that overwrite your regular opening hours in a specific time period (e.g. seasonal opening hours or an extra day in every second week)
* Four highly customizable Widgets and Shortcodes also displaying contextual information (e.g. "We're currently closed but will be open again on Monday at 8am")

= Widgets =

* Overview Widget: Lists up all Opening Hours with contextual information in a table or list
* Is Open Widget: Indicates whether the selected venue is currently open or closed and optionally shows when it will be open again
* Holidays Widget: Lists up all Holidays in a table or list
* Irregular Openings Widget: Lists up all Irregular Openings in a table or list

[More on Widgets](https://github.com/janizde/WP-Opening-Hours#widgets)

= Shortcodes =
All of the widgets listed up above are also available as shortcodes.

[More on Shortcodes](https://github.com/janizde/WP-Opening-Hours#shortcodes)

= Further Documentation =
**Further documentation is available on [GitHub](https://github.com/janizde/WP-Opening-Hours).**

* [Features](https://github.com/janizde/WP-Opening-Hours#features)
* [Installation](https://github.com/janizde/WP-Opening-Hours#installation)
	* [WordPress Plugin Installer](https://github.com/janizde/WP-Opening-Hours#wordpress-plugin-installer)
	* [Manual Installation](https://github.com/janizde/WP-Opening-Hours#manual-installation)
	* [Composer](https://github.com/janizde/WP-Opening-Hours#composer)
	* [Clone GitHub Repository](https://github.com/janizde/WP-Opening-Hours#clone-repository)
* [Getting Started](https://github.com/janizde/WP-Opening-Hours#getting-started)
	* [Setting up your Opening Hours](https://github.com/janizde/WP-Opening-Hours#set-up)
	* [Child Sets](https://github.com/janizde/WP-Opening-Hours#child-sets)
* [Widgets](https://github.com/janizde/WP-Opening-Hours#widgets)
	* [Overview Widget](https://github.com/janizde/WP-Opening-Hours#overview-widget)
	* [Is Open Widget](https://github.com/janizde/WP-Opening-Hours#is-open-widget)
	* [Holidays Widget](https://github.com/janizde/WP-Opening-Hours#holidays-widget)
	* [Irregular Openings Widget](https://github.com/janizde/WP-Opening-Hours#irregular-openings-widget)
* [Shortcodes](https://github.com/janizde/WP-Opening-Hours#shortcodes)
	* [Common Attributes](https://github.com/janizde/WP-Opening-Hours#common-attributes)
	* [[op-overview] Shortcode](https://github.com/janizde/WP-Opening-Hours#op-overview-shortcode)
	* [[op-is-open] Shortcode](https://github.com/janizde/WP-Opening-Hours#op-is-open-shortcode)
	* [[op-holidays] Shortcode](https://github.com/janizde/WP-Opening-Hours#op-holidays-shortcode)
	* [[op-irregular-openings] Shortcode](https://github.com/janizde/WP-Opening-Hours#op-irregular-openings-shortcode)
* [Filters](https://github.com/janizde/WP-Opening-Hours#filters)
* [Troubleshooting / FAQ](https://github.com/janizde/WP-Opening-Hours#troubleshooting)
* [Contributing](https://github.com/janizde/WP-Opening-Hours#contributing)
	* [Contributing to Code](https://github.com/janizde/WP-Opening-Hours#contributing-to-code)
	* [Contributing to Translations](https://github.com/janizde/WP-Opening-Hours#contributing-to-translations)
* [Changelog](https://github.com/janizde/WP-Opening-Hours#changelog)
* [License](https://github.com/janizde/WP-Opening-Hours#license)

== Installation ==

There are multiple ways to install the Opening Hours Plugin

1. [WordPress Plugin Installer](https://github.com/janizde/WP-Opening-Hours#wordpress-plugin-installer)
1. [Manual Installation](https://github.com/janizde/WP-Opening-Hours#manual-installation)
1. [Composer](https://github.com/janizde/WP-Opening-Hours#composer)
1. [Clone GitHub Repository](https://github.com/janizde/WP-Opening-Hours#clone-repository)

== Frequently Asked Questions ==

= My language is not provided in the Plugin =

You can participate to Plugin translations to make it available in more languages.
Please read the section on [contributing to translations](https://github.com/janizde/WP-Opening-Hours#contributing-to-translations)

= I found a bug and I would like to fix it =

If you found a bug you would like to fix feel free to [contribute to the project on GitHub](https://github.com/janizde/WP-Opening-Hours#contributing-to-code).

== Changelog ==

= 2.0.5 =

* fixed bug concerning child set initialization. thanks to @nikomuse

= 2.0.4 =

* Added support for UTC offset timezones

= 2.0.3 =

* Fixed timezone bug in WordPress 4.7

= 2.0.2 =

* Fixed a bug that didn't show next open Period when there are no regular Periods but Irregular Openings in the current Set

= 2.0.1 =

* Added SetAlias functionality
* Minor fixes including:
    * Fixed mixed content error (@foomep)
    * Fixed auto convert issue
    * Fixed PHP 5.3 incompatibility issues

= 2.0 =
Completely new version of the Opening Hours plugin.
Supports multiple Sets of Opening Hours and adds more flexibility to the Widgets and Shortcodes.
Offers Developer APIs to easily integrate your custom sources.

= 1.2 =
Read this article:
http://www.jannikportz.de/2014/01/19/update-1-2-for-opening-hours-plugin/

= 1.1.1 =
Read this article:
http://www.jannikportz.de/2013/12/04/opening-hours-update-1-1-1/

= 1.1 =
Read this article:
http://www.jannikportz.de/2013/11/03/opening-hours-update-version-1-2/

= 1.0.1 =
fixed a bug that displayed saturday instead of friday
ATTENTION: REINSTALL NECESSARY!

= 1.0 =
initial version

== Upgrade Notice ==

= 2.0 =
The plugin has been rewritten from scratch and a lot has changed. Old data should be converted automatically but a lot of the CSS classes have changed. Take some time to update it and maybe test it in a development environment to make sure it works as expected. Requires PHP >= 5.3, WordPress >= 4