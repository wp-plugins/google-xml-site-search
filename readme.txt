=== Google XML Site Search ===
Contributors: slackernrrd, swemaniac, jakobneander, Tenchu
Tags: search, google
Requires at least: 3.3 (may work with older versions, not tested)
Tested up to: 3.4.1
Stable tag: 1.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A search plugin that uses the XML API of Google Site Search (aka Google Custom Search).

== Description ==

This plugin provides search functionality for your WordPress installation, through Google Site Search (aka Google Custom Search). It uses Google's XML API, and thus requires an account that has access to it (ie you need to pay for it).

The plugin hooks in to the standard WordPress search form, so there is no need to change it (standard search widgets etc will still work). The plugin provides a basic template for the search results, but you can easily make your own if you wish to customize the output.

* Read more about Google Custom Search here: http://www.google.com/cse/
* Read more about the XML API here: https://developers.google.com/custom-search/docs/xml_results

This plugin is not developed or endorsed by Google.


== Installation ==

1. Upload the folder 'google-xml-site-search' to the '/wp-content/plugins/' directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Specify your Search Engine Unique ID through 'Settings' -> 'Google XML Site Search'
1. Done!

If you wish to customize the output or the layout of the search page, keep reading under "Customizing"


== Customizing ==

If you wish to customize the search result page, create a template named 'google-custom-search.php' in your theme. Refer to the plugin's 'default-template.php' for available template functions. The functions mimic the way that The Loop works, so it should feel familiar.

For advanced users who want even more control of the output of the plugin, it is also possible to transform the response from the XML API with an XSLT style sheet. To do this, create a file called 'google-custom-search.xsl' in your theme. Please refer to Google's documentation for the XML API (https://developers.google.com/custom-search/docs/xml_results) for the format of the response.

If you have a custom search form, you need to change the name-attribute of your search field to "gcs" (ie name="gcs") and make sure that the form action is set to the root of your site. Otherwise the search query will not be handled by this plugin.


== Frequently Asked Questions ==

= Where do I find the Search Engine Unique ID? =

You can find it in the Google Custom Search control panel for your search engine. Log in here: http://www.google.com/cse/

= I have a Google Custom Search account, can I use this plugin? =

To have access to the XML API you currently need a paid Google Custom Search account. If you have one, this plugin is for you!

== Changelog ==

= 1.1 (2012-08-27) =
* Fixed a bug that made the plugin not work in some (possibly all) cases. Sorry about that.
* Added localized Next/Previuos links in the default navigation HTML. Swedish translation is included in the plugin.
* Fixed some deprecated syntax that caused errors with newer versions of PHP

= 1.0 (2012-08-23) =
* The initial version of this plugin
