=== u3a-wp-configuration ===
Requires at least: 5.9
Tested up to: 6.5
Stable tag: 5.9
Requires PHP: 7.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Disable the comments facility, modify Dashboard menus, provide Author access to Pages, and some security-related changes.

== Description ==

This plugin is part of the SiteWorks project.  It's purpose is to modify a number of the default WordPress settings to better meet the needs of u3a websites.

= WordPress functionality =
* Support for Comments is disabled globally
* Posts are sorted in alphabetic order of title rather than the default order of post date
* Add medium_large to the list of image sizes available when adding an image in the block editor.
* PDF document preview is disabled
* The login screen is replaced with a customised u3a branded version
* The capabilities to edit and publish pages is added to the 'Author' role
* Menu position of Independent Analytics plugin (if present) is moved from default
* A cautionary notice is shown on the Add New Plugin page if the constant U3A_SHOW_PLUGIN_CAUTION is defined

= Performance related =
* Removes unnecessary elements from the HTML head section

= User permissions =
* An Author is allowed to edit pages where they have been assigned as the author, but not create new pages
* An Author is allowed to edit a group post where they have been assigned as the author, but not delete the group post.

= Security related =
* Disables the users REST endpoint so that usernames are not exposed via this interface
* Prevents usernames appearing in the XML sitemap
* Enforces the use of strong passwords
* Adds Security Headers
* Optionally adds a content security policy (reporting only). This header is only
added if the following definition is supplied in wp-config.php

`define ( 'CSP_REPORT_URL' 'https://somesite.com/location_of_reporting_script);` The given
URL will be called with a JSON payload detailing any content security policy breach.

= SMTP Configuration =
The plugin includes a mechanism to configure the inbuilt WordPress PHP Mailer class to send all email to an SMTP server. 
For this to work, the following configuration settings MUST be made in the standard WordPress configuration file wp-config.php
The values are those needed if using the Siteworks hosting system.
 
`define( 'SMTP_USER', 'wordpress@website.u3asite.uk' );` SMTP Username
`define( 'SMTP_PASS', 'the-password' );` SMTP Password  
`define( 'SMTP_HOST', 'mail.website.u3asite.uk' );`  mail server hostname  
`define( 'SMTP_FROM', 'wordpress@website.u3asite.uk' );` from email address  
`define( 'SMTP_NAME', 'Website Name' );` Website Name plus " u3a" if needed  
`define( 'SMTP_PORT', '587' );` use SMTP port number  
`define( 'SMTP_SECURE', 'TLS' );` Encryption system to use  
`define( 'SMTP_AUTH', true );` to enable SMTP authentication use  

If these settings are not provided in wp-config.php then another mechanism must be used to configure how WordPress sends email.

== Frequently Asked Questions ==

Please refer to the documentation on the [SiteWorks website](https://siteworks.u3a.org.uk/u3a-siteworks-training/)

== Changelog ==
* Feature 1010 - Add facility for cautionary notice to the Add New Plugin page with link to SiteWorks help on plugins
* Add medium_large to the list of image sizes available when adding an image in the block editor. (March 2024)
* Feature 1025 (and others) - An author can not delete a group where they have been assigned as the author (March 2024)
= 1.0.1 =
* Bug 914 - Ensure assets to support 'Lightbox for Gallery & Image Block' plugin are loaded on all pages (Nov 2023)
= 1.0.0 =
* First production code release
* Tested up to WordPress 6.4
= 0.3.98 =
* Release candidate 1
* Update plugin update checker library to v5p2
= 0.3.6 =
* Security fix - address coding standard and security issues identified in external code review (strong password enforcement)
= 0.3.5 =
* Security fix - address coding standard and security issues identified in external code review
= 0.3.4 =
* Move the 'Independent Analytics' plugin menu entry (if present) to below the Tools menu entry.
= 0.3.3 =
* Setup phpmailer to use SMTP. See Description for details.
= 0.3.2 =
* Add filter on media file uploads to exclude video types
= 0.3.1 =
* Add module to enforce strong user passwords  
A password must be at least 8 characters long and include a number, an upper and lower case letter and a punctuation symbol
= 0.2.5 =
* Turn off the texturizer, so no changing to curly quotes, e,g, ' shows as '
= 0.2.4 =
* Bug 724 - Alter 'author' permission to remove ability to create new pages
= 0.2.3 =
* Change the name displayed by the plugin to 'u3a SiteWorks WordPress Configuration'

= 0.2.2 =
* Changed plugin name to u3a-siteworks-configuration

= 0.2.1 =
* Add support for plugin updates via the SiteWorks WP Update Server
* Modify Author Role to support page editing

= 0.1.2 =
* Original SiteWorks 'Alpha' release

