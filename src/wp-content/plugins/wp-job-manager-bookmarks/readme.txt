=== Job Bookmarks ===
Contributors: mikejolley, automattic, jakeom
Requires at least: 6.1
Tested up to: 6.4
Stable tag: 1.4.4
License: GNU General Public License v3.0

Lets candidates star/bookmark jobs, and employers star/bookmark resumes (if using the Resume Manager addon).

= Documentation =

Usage instructions for this plugin can be found here: [https://wpjobmanager.com/document/bookmarks/](https://wpjobmanager.com/document/bookmarks/).

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

2023-11-17 - version 1.4.4
* Update supported versions.

2023-05-05 - version 1.4.3
Fix: Prevent bookmarking other candidate's posts
Enhancement: Add a template for the [my_bookmarks] shortcode for logged-out users

2021-03-08 - version 1.4.2
Enhancement: Add multi-site compatibility.
Enhancement: Remove user bookmarks on user deletion.
Enhancement: Add personal data exporter and eraser.

2018-05-15 - version 1.4.1
* Adds my_bookmarks shortcode to the wpjm shortcodes list

2018-05-09 - version 1.4.0
* Bookmark actions (adding, removing, and updating) now happen in the background for themes that support it.
* Adds support for `order` (`date`, `post_date`, and `post_title`) and `orderby` (`ASC` and `DESC`) arguments in `[my_bookmarks]` shortcode.
* Adds thumbnail for resume and job listings next to the bookmark in `[my_bookmarks]`.
* Fixes issue with pagination when there are expired or deleted listings bookmarked.
