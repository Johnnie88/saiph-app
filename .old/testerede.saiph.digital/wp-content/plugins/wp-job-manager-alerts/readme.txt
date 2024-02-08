=== Job Alerts ===
Contributors: mikejolley, adamkheckler, kraftbj, jakeom, alexsanford1
Requires at least: 6.1
Tested up to: 6.4
Stable tag: 2.1.1
Requires PHP: 7.4
License: GNU General Public License v3.0

Allow users to subscribe to job alerts for their searches. Once registered, users can access a 'My Alerts' page which you can create with the shortcode `[job_alerts]`.

Job alerts can be setup based on searches (by keyword, location keyword, category) which are delivered by email either daily, weekly or fortnightly.

= Documentation =

Usage instructions for this plugin can be found here: [https://wpjobmanager.com/document/job-alerts/](https://wpjobmanager.com/document/job-alerts/).

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

2023-11-17 - version 2.1.1
* Fix: Only run has_shortcode if content is not null (#148)

2023-10-05 - version 2.1.0
* Fix: Fix my-alerts.php template HTML
* Fix: Make 'Add alert' link relative
* Fix: Fix redirection after actions on My alerts page

2023-06-10 - version 2.0.0
* Enhancement: Replace my alerts table with list of cards #78
* Enhancement: Add Job Alerts admin table #80
* Fix: Fix PHP 8.2 deprecations #81
* Fix: Fix translation text domain #74
* Fix: Use Job Type IDs instead of slugs #72

2023-05-04 - version 1.6.0
* Fix: Fix reflected XSS.

2022-11-18 - version 1.5.6
* Enhancement: Add Job Types to the My Alerts table.
* Enhancement: Use multiselect UI for Job Types in the form.
* Fix: Job Type field missing in the form.
* Fix: Alert message displaying on `[job_alerts]` shortcode.
* Dev: Move the `[job_alerts]` sign-in message to a separate template file.
