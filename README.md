# Opening Hours #
**Contributors:** janizde  
**Tags:** opening hours,business hours,hours,table,overview,date,time,widget,shortcode,status,currently open  
**Tested up to:** 4.1.1  
**Stable tag:** 2.0  
**Required at least:** 3.0  
**Donate link:** http://jannikportz.de/donate <!-- the donate URL is not available -->  
**License:** GPLv2 or later  
**License URI:** http://www.gnu.org/licenses/gpl-2.0.html  

Manage your shop's, restaurant's etc. Opening Hours, Holidays and Irregular Openings and have them displayed and analysed by four powerful Widgets and Shortcodes.

## Description ##
This Plugin allows you to set up your venue's Opening Hours, Holidays and Irregular Openings in an easy to use user interface.
You have the opportunity to run multiple stores in only one WordPress installation. It is also possible to have a sub-set of Opening Hours, which will overwrite the actual Opening Hours based on different criteria e.g. date ranges (seasons) and even/odd week numbers. This is particularly useful if your bar has different Opening Hours in winter than in summer.
The Plugin analyzes the settings you have made and offers you the opportunity to add a message in the front end, telling whether your venue ist currently open or closed and when it will be open next.
Every Shortcode has its corresponding Widget so you are offered a number of ways to use them.

### Overview Widget / Shortcode ###

**Shortcode Tag:** op-overview  

The Overview widget / shortcode basically shows up all your Opening Hours of the selected set in a table.
There are several features that you can use with this widget / shortcode

*   Highlight currently running period or current weekday
*   Show / hide days on which the venue is closed
*   Compress table: combine days with mutual periods in one table row
*   Use weekday shortcuts instead of fully qualified names
*   Include Irregular Openings: show irregular openings in the current week in the table and overwrite corresponding weekday (You may also show/hide the date.)
*   Include Holidays: show holidays in the table if they are active on any of the weekdays (see Include Irregular Openings)
*   Advanced Options like custom captions, classes for table elements, time format, etc.

**More detailed documentation:** http://jannikportz.de/wp-opening-hours/widgets#overview  

### Is Open Status Widget / Shortcode ###

**Shortcode Tag:** op-is-open  

The Is Open Status widget / shortcode displays a message saying whether your venue belonging to the selected set is currently open or not.

*   Display information about when the venue will be open again
*   Advanced options like classes, custom messages, etc.

**More detailed documentation:** http://jannikportz.de/wp-opening-hours/widgets#is-open  

### Holidays Widget / Shortcode ###

**Shortcode Tag:** op-holidays  

The Holidays widget / shortcode lists up all Holidays in the selected Set in a table

*   Highlight currently running Holiday
*   Custom CSS classes
*   Custom PHP date format

**More detailed documentation:** http://jannikportz.de/wp-opening-hours/widgets#holidays  

### Irregular Openings Widget / Shortcode ###

**Shortcode Tag:** op-irregular-openings  

The Irregular Openings widget / shortcode lists up all Irregular Openings in the selected Set in a table

*   Highlight currently running Irregular Openings
*   Custom CSS classes
*   Custom PHP date and time format

**More detailed documentation:** http://jannikportz.de/wp-opening-hours/widgets#irregular-opening  

## Installation ##

1. Download the .zip-archive
1. Unzip the archive
1. Upload the directory /opening-hours to your wp-content/plugins
1. In your Admin Panel go to Plugins and active the Opening Hours Plugin
1. Now you can edit your Opening Hours in the Settings-Section
1. Place the Widgets in your Sidebars or use the Shortcode in your posts and Pages

## Frequently Asked Questions ##

### My language is not provided in the Plugin ###

If your language is not provided in the language files, feel free to support the translation of the Opening Hours plugin.
Go to translate.jannikportz.de, sign up for an account and start translating the strings. I will then implement the new language with the net update.
If you're not capable of adding a new language set or you just want to let me know that you have added new translations, please drop me an E-Mail at hello[at]jannikportz.de

### I found a bug and I would like to fix it ###

You can find the development repository at https://github.com/janizde/WP-Opening-Hours. Feel free to fork and send pull requests.

## Changelog ##

### 1.0 ###
initial version

### 1.0.1 ###
fixed a bug that displayed saturday instead of friday
**ATTENTION:** REINSTALL NECESSARY!  

### 1.1 ###
Read this article:
http://www.jannikportz.de/2013/11/03/opening-hours-update-version-1-2/

### 1.1.1 ###
Read this article:
http://www.jannikportz.de/2013/12/04/opening-hours-update-1-1-1/

### 1.2 ###
Read this article:
http://www.jannikportz.de/2014/01/19/update-1-2-for-opening-hours-plugin/

### 2.0 ###
Completely new version of the Opening Hours plugin.
Note that this version requires at least PHP 5.3 and you will have to set up your data and the widgets / shortcodes as well.
