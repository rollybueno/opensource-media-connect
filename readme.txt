=== Open Source Media Connect ===
Contributors: rollybueno, buenoconsulting
Tags: openverse, images, media, open source, creative commons
Requires at least: 5.0
Requires PHP: 7.4
Tested up to: 6.8
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Search and embed open source images from Openverse directly in your WordPress posts and pages.

== Description ==

Open Source Media Connect is a powerful and user-friendly WordPress plugin designed to seamlessly integrates with the Openverse API to enable users to search and embed open-source images directly into their posts and pages. The plugin features a straightforward setup process with a single configuration option in Settings → Open Source Media for connecting to the Openverse service.

Once configured, users can easily add open-source images using the Open Source Media block. The block offers a comprehensive set of features:

– Keyword-based image search
– Customizable image settings including caption and alt text
– Flexible sizing options with size classes
– Adjustable media width
– Optional media attribution display

== External Services ==
This plugin uses the [Openverse API](https://api.openverse.org/) to obtain open-source media and place it inside your posts or pages as an image block. This service is provided by [Openverse.org](https://openverse.org/), which is part of the wider [WordPress.org](https://wordpress.org/) project, giving you a vast catalog of openly licensed visuals at your fingertips.

During setup, you may let the plugin create an Openverse account for you. If you select this option, the following data will be used to register through OAuth2:

1. Site Name
2. Admin Email Address
3. Site URL - concatenated with "WordPress integration for " string

This plugin uses these information to request your Openverse's Client ID and Client Secret, which in turn will be used to request for API token, valid for up to ten hours, and automatically renews it whenever you use an Open Source Media block. 

Your Client ID, Client Secret, and Token are kept on your site and used solely to search and retrieve open-source media through the [Openverse API](https://api.openverse.org/). This plugin does not track users or collect any personal data, except your Openverse's Client ID, Client Secret and Token that will be used to search for media.

For full details, see Openverse’s documentation, policies, terms and license:

* [Openverse Terms of Use](https://docs.openverse.org/terms_of_service.html)
* [Openverse Privacy](https://openverse.org/privacy)
* [Openverse API](https://api.openverse.org/)
* [About Openverse](https://openverse.org/about)
* [Creative Commons License](https://creativecommons.org/share-your-work/cclicenses/)

= Media Attribution =
When using media from Openverse, proper attribution may be required, depending on your selected image license. The plugin automatically handles attribution when the "Show Attribution" option is enabled.

== Installation ==

1. Upload the plugin to the `/wp-content/plugins/` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Use the Settings -> Open Source Media screen to configure the plugin
4. Use the Open Source Media block to search and embed images

== Screenshots ==

1. Open Source Media block in the editor
2. Openverse search interface
3. Media settings panel
4. Plugin settings page

== Frequently Asked Questions ==

= What is Openverse? =
Openverse is a search engine for openly-licensed media. It provides access to millions of freely-usable media files.

= Do I need an API key? =
Yes, you need an API key to use the Openverse API. You can obtain an API key by:

1. Creating an account at [Openverse](https://openverse.org), generate the API key and add in the plugin's settings page (Settings <span aria-hidden="true" class="wp-exclude-emoji">→</span> Open Source Media)
2. Or you can simply connect your site to Openverse in single click on Settings <span aria-hidden="true" class="wp-exclude-emoji">→</span> Open Source Media

= What types of media can I search for? =
Currently, the plugin only supports images. More media types will be added in future updates.

= Does this plugin track users? =
No, this plugin does not track users or collect any personal data except storing the Client ID and Client Secret, that will be used to generate API Token, to be used to handle media search.

== Changelog ==

= 1.0.0 =
* Initial release

== Upgrade Notice ==

= 1.0.0 =
Initial release
