=== Application Deadline ===
Contributors: mikejolley, drawmyface, jakeom, kraftbj
Requires at least: 6.1
Tested up to: 6.4
Stable tag: 1.2.8
Requires PHP: 7.4
License: GNU General Public License v3.0

Allows job listers to set a deadline via a new field on the job submission form. Once the deadline passes, the job listing is automatically ended (if enabled in settings)

= Documentation =

Usage instructions for this plugin can be found here: [https://wpjobmanager.com/document/application-deadline/](https://wpjobmanager.com/document/application-deadline/).

= Support Policy =

For support, please visit [https://wpjobmanager.com/support/](https://wpjobmanager.com/support/).

We will not offer support for:

1. Customisations of this plugin or any plugins it relies upon
2. Conflicts with "premium" themes from ThemeForest and similar marketplaces (due to bad practice and not being readily available to test)
3. CSS Styling (this is customisation work)

If you need help with customisation you will need to find and hire a developer capable of making the changes.

== Installation ==

To install this plugin, please refer to the guide here: [http://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation](http://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation)

== Changelog ==

2023-11-17 - version 1.2.8
* Update supported versions.

2023-10-10 - version 1.2.7
* New: Allow application deadline to be renewed. (Requires WP Job Manager 1.42.0)

2023-05-05 - version 1.2.6
Fix: Add check for post_type array index
Fix: Change column title from "Closing Date" to "Closing"

2021-04-14 - version 1.2.5
Fix: Issue with ordering of job listings

2021-04-06 - version 1.2.4
* Fix sorting by closing date.
* Run the cronjob at midnight in all timezones.
* Change the calculation of closing date on the listings page.
* Switch to webpack via wordpress-scripts and simplify build.
