=== RHD Migration Posts, Pages & Custom Post Types ===
Contributors: pankajpragma
Tags: migrate, page, move, one click, rhd, migration, rhd migration
Requires at least: 5.0.1
Tested up to: 6.0.2
Stable tag: 1.0.1
Requires PHP: 5.2.4
License: GPLv2 or later

Migrate the content of pages, posts, or custom post types from one WordPress to another WordPress. If you want to migrate post/page content from the stage to the multiple production server, this plugin will be your best choice.
== Description ==

Migrate the content of pages, posts, or custom post types from one WordPress to another WordPress. If you want to migrate post/page content from the stage to multiple production servers, this plugin will be your best choice. 

If your client wants to prepare the page/post in a staging environment and migrate to production with fewer efforts then the plugin will do it easily for you. It automatically downloads media based on configuration. We are going to add more features soon...

= Special Features =
- Communicate with WordPress default API. So no special configuration is needed.
- Download Media Automatically.
- Add/Update post/page data based on configuration
- RTL Supported
- Comment Migration Supported
- Media Exclude Supported
- Multiple Destination Supported
- Overwrite the default setting during the migration

**More features will be added soon...**

== Installation ==

1. Upload the entire `rhd-migration` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Look at your admin bar and enjoy using the new links there...

== Frequently Asked Questions ==
= What's the use of Hash Key? =

It's used to secure communication between the source and destination websites. You can set up a custom value but it should be as complex as possible.
= Does it support page builder? =

Yes, It supports all possible page builders. If you see an incompatibility then please fill free to report the issue.
= Does REST API needs to be enabled? =

Yes, It's mandatory to keep JSON API enabled. So it can communicate with the server.

= How to enable the permalinks? =

On the WordPress dashboard, go to Settings → Permalinks Screen. You can choose one of the permalink structures or enter your own in the “Custom structure”.

== Screenshots ==


== Upgrade Notice ==
= 1.0.0 =
*Just released into the wild.

= 1.0.1 =
*Multiple Destination URL Support.
*Overwrite the default setting during the migration
*Added Debugging Help Tab

== Changelog ==
= 1.0.0 =
* Initial release

= 1.0.1 =
*Multiple Destination URL Support.
*Overwrite the default setting during the migration
*Added Debugging Help Tab
