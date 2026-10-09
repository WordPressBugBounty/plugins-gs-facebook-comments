=== Genial Social Comments ===
Contributors: qsheeraz, genialsouls
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=YNF4H9FJY4HU4
Tags: comments, social comments, Disqus, Facebook comments
Requires at least: 4.7
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.8.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Adds an external social-comments area to selected post types and through the existing `[gs-fb-comments]` shortcode.

== Description ==

<blockquote>
    <p>
        <strong>Genial Social Comments</strong>
    </p>
</blockquote>

Genial Social Comments adds the selected external comments provider after selected
public post types. Existing installations continue using Facebook / Meta unless
another provider is selected. The `[gs-fb-comments]` shortcode remains
available for manual placement.

Disqus can load on click, near the viewport, or immediately. Click-to-load is
the recommended default because it avoids an external request until the visitor
chooses to load comments. Disqus Basic may display advertising. Advertising,
tracking, account features, and ad-free options are controlled by Disqus and the
publisher's Disqus account or plan; Genial Social Comments does not remove or alter
Disqus advertising.

A related commercial product is available from
[GenialSouls](https://genialsouls.com/product/wp-facebook-comments-pro/).

== Installation ==

Here are few steps to follow:

1. Upload `gs-facebook-comments` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Open the Social Comments settings page in the WordPress dashboard.
4. Choose Facebook / Meta or Disqus. New installations use Disqus; enter the shortname from your Disqus site settings before loading comments.
5. Select post types or use `[gs-fb-comments]` for manual placement.
6. That's it. You're ready to go!

== Frequently Asked Questions ==

= Can I show comments on pages? =

Yes. You can use the existing shortcode `[gs-fb-comments]` for pages or other manual placements.

= Does Disqus require an API key? =

No. Basic Disqus embedding requires a Disqus shortname, not an API key. API credentials and synchronization are not part of this plugin integration.

== External services ==

This plugin connects visitors' browsers to Meta's Facebook JavaScript SDK at
`connect.facebook.net` when a frontend page is displayed. Meta may receive the
visitor's IP address, browser information, page URL, referrer, cookies, account
state, locale, and the optional App ID. This connection supplies the external
comments interface configured by the plugin.

When the optional cache-refresh setting is enabled, the WordPress server sends
the public post URL to Meta's developer debugger endpoint so Meta can refresh
its cached page information.

Use of these services is subject to Meta's terms and privacy policy:

* [Meta Platform Terms](https://developers.facebook.com/terms/)
* [Meta Privacy Policy](https://www.facebook.com/privacy/policy/)

When Disqus is selected, this plugin loads the Disqus Universal Embed script
from the configured HTTPS Disqus shortname when comments are activated by the
selected loading mode. Disqus may receive information about the page and
visitor, and Disqus Basic may display advertising. Review the Disqus policies:

* [Disqus Terms](https://disqus.com/terms/)
* [Disqus Privacy Policy](https://disqus.com/privacy-policy/)

== Changelog ==

= 1.8.2 =

* Added: Disqus is the default provider for new installations when no provider is configured.
* Updated: Existing installations without an explicit provider retain their legacy Facebook settings and behavior.
* Added: Actionable admin guidance for missing Disqus configuration and invalid provider values.
* Improved: Provider resolution no longer silently falls back to Facebook for invalid values.
* Improved: Admin settings presentation, Facebook discontinuation disclosures, privacy-aware loading, security, and WordPress.org compliance.

= 1.8.0 =

* Added: Disqus as a selectable commenting provider using the Universal Embed.
* Added: Privacy-aware click, viewport, and immediate Disqus loading modes.
* Updated: Provider selection preserves existing Facebook settings and legacy identifiers.
* Improved: Security, input handling, output escaping, and WordPress.org compliance.
* Improved: Compatibility with validated current WordPress and PHP environments.
* Removed: Obsolete and insecure external resources from the legacy implementation.

= 1.7.5 - 04/04/2024 =

* Fix:    Shortcode comments issue fixed.

= 1.7.4 - 14/03/2024 =

* Update: Added nonce and general capability checks to the per-post setting.
* Update: Quality check done.

= 1.7.3 - 08/02/2023 =

* Fix:    Minor bugs fixed.
* Update: Quality check done.

= 1.7.2 - 04/04/2022 =

* Update: EU notice message is now optional.
* Update: FB crawler is now optional.
* Added:  Shortcode crawler added. (optional)

= 1.7.1 - 05/10/2021 =

* Update: Modified post types support.
* Fix:    Warning messages fixed.
* Fix:    Notice messages fixed.

= 1.7 - 28/09/2021 =

* New:    Option to allow comments on each post.
* New:    Notice for EU Facebook users.
* New:    Comments background color on shortcode.
* Update: Modified post types support.
* Update: Translations updated.
* Update: Latest Facebook API and scripts.
* Fix:    Bugs fixed.

= 1.6 - 18/09/2020 =

* New:    Comments background color option for dark themes.
* Update: Latest Facebook API and scripts.
* Fix:    Bugs fixed.

= 1.5 - 27/11/2019 =

* New:    Option to show comments before SSL.
* New:    Translation ready.
* Fix:    Minor bugs fixed.

= 1.4 - 22/08/2019 =

* New:    Completely redesigned.
* New:    WordPress standardized.
* New:    Security framework.

= 1.3 - 10/01/2019 =

* Update: Gutenberg ready.
* New:    Pro version launched.
* Modify: Quality standards applied.
* Fix:    Comments box size issue when logged in.

= 1.2 - 26/09/2018 =

* New:    Now available in multi languages.
* Update: Translation updated.
* Modify: Minor changes.
* Fix:    Bug fixed when update post.

= 1.1 - 31/07/2018 =

* New:    shortcode [gs-fb-comments] for pages or anywhere at your site.
* Update: Translation updated.
* Modify: Minor changes.

= 1.0 - 07/11/2017 =

* First release.
* Facebook comments

== Upgrade Notice ==

= 1.0 =
