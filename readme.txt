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
This plugin uses the [Openverse API](https://api.openverse.org/) to search open-source media and place it inside your posts or pages as an image block. This service is provided by [Openverse.org](https://openverse.org/), which is part of the wider [WordPress.org](https://wordpress.org/) project, giving you a vast catalog of openly licensed images at your fingertips.

The following information is required to interact with the Openverse API:

* **Client ID & Client Secret** - Used to request an access token.
* **Token** - Necessary for accessing the [image_search](https://api.openverse.org/v1/#tag/images/operation/images_search) endpoint.
* **Search query** - Required for searching images; can also serve as optional alt text.
* **Site Name** - Provided as the value for the _name_ field during [API registration](https://api.openverse.org/v1/#tag/auth/operation/register).
* **Admin Email Address** - Provided as the value for the _email_ field during [API registration](https://api.openverse.org/v1/#tag/auth/operation/register).
* **Site URL** - Combined with the phrase "WordPress integration for" to form the _description_ field during [API registration](https://api.openverse.org/v1/#tag/auth/operation/register).

This plugin uses the provided information to request your Openverse Client ID and Client Secret. These credentials are then used to obtain an API token, which is valid for up to ten hours and is automatically renewed whenever you perform an image search using the Open Source Media block. Your search query is used to find matching images on Openverse, and the token is required to connect to the Openverse API's media search endpoint.

This plugin does not track users or collect any personal data beyond the information listed above.

For full details, see Openverse’s terms, privacy, API documentation, about and license:

* [Openverse Terms of Use](https://docs.openverse.org/terms_of_service.html)
* [Openverse Privacy](https://openverse.org/privacy)
* [Openverse API](https://api.openverse.org/)
* [About Openverse](https://openverse.org/about)
* [Creative Commons License](https://creativecommons.org/share-your-work/cclicenses/)

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
No, this plugin does not track users or collect any personal data beyond the information listed on the **EXTERNAL SEVICES** section above.

== Changelog ==

= 1.0.0 =
* Initial release

== Upgrade Notice ==

= 1.0.0 =
Initial release
