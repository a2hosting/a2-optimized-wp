=== A2 Optimized WP - Turbocharge and secure your WordPress site ===
Contributors: a2hosting, supersoju
Tags: a2 hosting, cache, caching, speed, fast, optimize, site performance, image optimization, image compression, site security, seo, gzip compression, minify code, code minification
Requires at least: 5.1
Tested up to: 6.2
Stable tag: 3.0.6.3.2
Requires PHP: 5.6
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Make your site faster and more secure with the click of a few buttons

== Description ==
Boosting performance and securing your WordPress site has never been easier with the A2 Optimized WP plugin from [A2 Hosting](https://www.a2hosting.com/). Get customized desktop and mobile performance scores for your site, including:

* Server speed (Time to First Byte)
* Page load speed (Largest Contentful Paint)
* User perception (First Contentful Paint)
* Visual stability (Cumulative Layout Shift)
* Website browser speed (First Input Delay)

Using these scores, A2 Optimized WP generates specific, personalized recommendations for improving your site:

* Performance
* Security
* Best practices compliance

With just a few clicks you’ll give your WordPress site the boost it needs with stable, industry-proven optimizations and improvements.

[Vulnerability disclosure program](https://patchstack.com/database/vdp/a2-optimized-wp/)

= Performance optimizations =

Our plugin is optimized to work best in the A2 Hosting environment, so items marked with an asterisk (*) are only available for sites hosted at A2 Hosting. If you are not an A2 Hosting customer, [join today](https://www.a2hosting.com)!

**Page caching**
* Allows site visitors to save copies of your web pages on their device or browser. When they return to your website in the future, your site files load faster.
* This optimization improves Time to First Byte (TTFB) and reduces bandwidth usage.

**Gzip compression**
* Turns on Gzip compression to make text files smaller.
* This optimization improves Time to First Byte (TTFB) and reduces bandwidth usage.

**Redis object caching (*)**
* Stores commonly used elements such as menus, widgets, and database sets in memory.
* This optimization improves Time to First Byte (TTFB).

**Minify HTML pages**
* Removes extra spaces, tabs, comments, and line breaks from HTML pages.
* This optimization improves First Contentful Paint (FCP) and First Input Delay (FID).

**Automatic database optimizations**
* Periodically cleans MySQL databases of expired transients (a type of cached data used in WordPress) as well as trashed and spam comments. Also optimizes database tables.
* This optimization improves Time to First Byte (TTFB) for uncached pages.

**Compress images on upload (*)**
* Automatically compresses images when they are uploaded to your site.
* This optimization improves First Contentful Paint (FCP), Largest Contentful Paint (LCP), and First Input Delay (FID).

**Turbo Web Hosting (*)**
*Takes advantage of A2 Hosting’s Turbo Web Hosting platform to provide faster serving of static files, pre-compiled .htaccess files for improved performance, PHP opcode caching, and more.
*This optimization can improve multiple benchmarks.

**Use system cron instead of WordPress cron (*)**
* Replaces the WordPress virtual "cron job" with a genuine, system-defined cron job.
* This optimization reduces the load on WordPress and ensures scheduled tasks run at precise, correct intervals.

**Minify inline CSS and JavaScript**
* Removes extra spaces, tabs, comments, and line breaks from inline CSS and JavaScript.
* This optimization improves First Contentful Paint (FCP) and First Input Delay (FID).

**Disable WooCommerce AJAX Cart Fragments**
* Disables WooCommerce AJAX Cart Fragments on your homepage and enables the "redirect to cart page" option.
* This optimization improves WooCommerce performance.

= Security optimizations =

Our plugin is optimized to work best in the A2 Hosting environment, so items marked with an asterisk (*) are only available for sites hosted at A2 Hosting. If you are not an A2 Hosting customer, [join today](https://www.a2hosting.com)!

**Lock editing of plugins and themes from wp-admin**
* Prevents misuse of built-in editing capabilities for the WordPress admin.

**Change login URL (*)**
* Changes the login page URL from the default wp-login.php to a random URL.
* Helps prevent bots from brute-force attacking your login page.

**Add CAPTCHA for comments and login (*)**
* Adds a CAPTCHA to comment forms and login pages to deter bots from posting spam comments and running brute-force attacks..

**Block unauthorized XML-RPC requests**
* Rejects XML-RPC requests except for whitelisted services, such as Jetpack.

**Deny direct access to configuration files**
* Displays a “403 Forbidden” error when visitors or bots try to access WordPress configuration files.

= Best practices recommendations =
**Regenerate wp-config salts**
* Generates new values for wp-config.php salts and security keys for increased security.

**Recent post limit**
*Checks the number of recent posts per page, which should be less than 15 for most sites.

**RSS post limit**
* Checks the number of posts in RSS feeds, which should be less than 20 for most sites.

**Recent posts showing on home page**
* Checks whether the home page displays recent posts, and offers to use a static page instead for faster performance.

**Permalink structure**
* Checks that the permalink structure is configured to fully optimize page caching and get additional SEO benefits.

**Unused themes**
* Checks if there are any non-default, unused themes that should be deleted.

**Inactive plugins**
* Checks if there are any inactive plugins that should be deleted.

**Hosted with A2 Hosting**
* Checks if your site is hosted with [A2 Hosting](https://www.a2hosting.com/) for faster page load times and more optimizations.


== Installation ==

1. Upload a2-optimized into the wp-content/plugins/ directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Click 'A2 Optimized' in the admin menu
4. Click buttons to enable optimizations
5. Check out warnings that A2 Optimized has detected that may be slowing down your site
6. Check out Best practices recommendations that may help you identify issues that may be slowing down your site.


== Frequently Asked Questions ==

= How does A2 Optimized speed up my site? =

Caching is the fastest and easiest way to speed up a dynamic website.
A2 Optimized will install and configure the best available caching method with the click of a few buttons.

= Does A2 Optimized make all sites faster? =

A2 Optimized will speed up most sites; however, not all plugins and themes are compatible with all optimizations.
If your site is slower after enabling caching in A2 Optimized, talk to a developer about finding better solutions for the plugins that you are using.

= Can I use A2 Optimized with WordFence? =

A2 Optimized is compatible with most of the features in WordFence, however you should disable caching and logging in WordFence. Also, is page caching in A2 Optimized is enabled, some WordFence features that required WordPress to be fully loaded may not trigger.

= Can I use A2 Optimized on any host? =

Yes.  A2 Optimized works on any host that supports WordPress; however, A2 Hosting provides a few more tools for speeding up your site when hosted on an A2 Hosting server.


== Changelog ==

= 3.0.6 =
* Tested with WordPress 6.2
* Feature to use bcrypt for storing password hashes
* Feature to remove old backups of wp-config

= 3.0.5.2 =
* Small quality of life changes

= 3.0.5 =
* More information provided on page regarding requirements for Redis caching
* Added feature to lock Litespeed Cache settings once optimized
* Will attempt to clean up files left by previous versions of the plugin

= 3.0.4 =
* Small QoL improvements.

= 3.0.3 =
* Users may now supply their own Google Pagespeed Insights API key to run benchmarks. This is available through the "Advanced Settings" panel under the "Website and Server Performance" tab.
* If the "Login URL Change" feature is enabled, the new login URL is displayed by the toggle.
* Other small QoL improvements.

= 3.0.2.2 =
* Small update for A2 hosting customers

= 3.0.2.2 =
* Small QoL improvements

= 3.0.2 =
* Fixes issues with some optimizations not displaying status properly

= 3.0.0 =
* Fresh new UI
* Front-end and Back-end benchmarks
* Compare your performance against A2 Hosting's Turbo and Managed WordPress plans
* Find new recommendations on areas where you could further improve your performance

= 2.1.4.6 =
* Better compatibility with LiteSpeed Cache

= 2.1.4.5.1 =
* Optimize database tables was not being called correctly in some cases.

= 2.1.4.5 =
* Fix for issue where TurboCache was not being correctly identified.

= 2.1.4 =
* Added Scheduled Database Optimizations.
* Added support for redis object caching on A2 Hosting.

= 2.1.3.10 =
* Add information related to A2 Optimized to the Site Health panel. 

= 2.1.3.3 =
* Patches a cross-site-scripting vulnerablity. This is a recommended upgrade.

= 2.1.3.1 =
* Small improvments to disk caching 

= 2.1.3.1 =
* Fixed issue with handling of some regex formulas in the advanced cache settings exclusion list 
* Additional checks when adding memcached server for object caching 

= 2.1 =
* Removed requirement for W3 Total Cache. A2 Optimized now contains it's own caching engine. Don't worry if you still want to use W3 Total Cache, it will continue to work as it always has with this and future updates.
* Memcached object caching is now available on any host that supports it.
* Small UI refresh. 

= 2.0.11.1.4 =
* Fixed an issue where server GZIP capabilities were not always detected 

= 2.0.11.1.2 =
* No longer count default themes on Warnings tab

= 2.0.11.1.1 =
* Added Divi specific optimizations

= 2.0.11 =
* All admin notices are now dismissable

= 2.0.10.9.6 =
* Adjustments to Cloudflare detection

= 2.0.10.9.4 =
* Fixed compatibility issue with X Theme

= 2.0.10.9.4 =
* Added feature to regenerate wp-config salts

= 2.0.10.9.3 = 
* Added helpful information on extra plugins installed along with A2 Optimized on A2 Hosting accounts

= 2.0.10.8 =
* Added option to block unauthorized XML-RPC calls

= 2.0.10.7.10 =
* Added option to dequeue WooCommerce cart fragment calls

= 2.0.10.7.8 =
* Wordfence is no longer marked as an incompatible plugin

= 2.0.10.7.3 =
* Changed code to reduce notices for users with debug enabled

= 2.0.10.7.2 =
* Removed P3 Profiler from list of optional plugins

= 2.0.10.7 =
* Changed protect config files to an optional optimization

= 2.0.10.5 =
* Fixed error with KB search

= 2.0.10.4 =
* reCaptcha for comments enabled for mangaged hosting

= 2.0.10.3 =
* Emails site admin if login URL changes

= 2.0.19 =
* Settings page for custom reCaptcha options

= 2.0.9.8 =
* Fixed issue with some plugins causing slowdowns on dashboard screen

= 2.0.9.7 =
* Fixed issue with getting current user for some operations
* Removed incorrect KB link

= 2.0.9.3 =
* Clearer information when incompatible plugins are detected
* Fixed issue with gzipping being enabled inadvertantly while changing other settings

= 2.0.9.2 =
* Clearer information regarding W3 Total Cache upgrade
* Fixed issue where sites installed in subdirectories may not function as expected

= 2.0.9 =
* Updated reCAPTCHA to API v2
* Better support and triage for unsupported versions of W3 Total Cache
* Better support for mobile and tablet screen sizes on our optimizations page

= 2.0.8.5 =
* Added plugin update notices to ensure users are aware of any important update details

= 2.0.8.2 =
* Additional check for compatible W3TC
* Added check to see if your server is already has compression enabled

= 2.0.8 =
* Enable SSL caching by default

= 2.0.7 =
* Revert use of PHP short arrays

= 2.0.6 =
* Removed Clef and tested against WordPress 4.8

= 2.0.3 =
* Added dmatteson as a plugin author

= 2.0.2 =
* added namespace class to css to prevent collision with other plugins

= 2.0.1 =
* minor bug fixes
* check for PHP version >= 5.3

= 2.0 =
* Move from SaaS to Full GPL


== Upgrade Notice ==

= 2.0.10.9 =
Important security update. Please upgrade immediately.

= 2.0 =
New GPL plugin, now updates are through the wordpress.org repository


== Screenshots ==
1. A2 Optimized Performance Dashboard
2. A2 Optimized Front End Performance Recommendations
3. A2 Optimized Backend Benchmark Results
4. A2 Optimized Optimizations panel
