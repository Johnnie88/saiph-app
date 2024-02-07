=== Job Tags ===
Contributors: mikejolley
Requires at least: 6.1
Tested up to: 6.4
Stable tag: 1.4.5
License: GNU General Public License v3.0

Adds tags to Job Manager for tagging jobs with required Skills and Technologies. Also adds some extra shortcodes. Requires Job Manager 1.0.4+

= Documentation =

Usage instructions for this plugin can be found here: [https://wpjobmanager.com/document/job-tags/](https://wpjobmanager.com/document/job-tags/).

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

2023-11-17 - version 1.4.5
* Fix: Only run has_shortcode if content is not null (#148)

2023-05-08 - version 1.4.4
Tweak: Update plugin headers.

2023-02-01 - version 1.4.3
Tweak: Update uninstall logic to depend on Job Manager setting and remove tags

2021-03-08 - version 1.4.2
Fix: jQuery 3.x compatibility

2019-05-08 - version 1.4.1
* Fixes issue with WPJM CSS styles not loading on pages with `[jobs_by_tag]` and `[job_tag_cloud]` shortcodes.
* Fixes issue with tags not being editable in the block editor.
