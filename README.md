# u3a-wp-configuration

A plugin to configure WordPress for u3a use.

This plugin is intended to be used for any initialisation or configuration of WordPress that is not related to a specific plugin and would commonly be added to a theme's functions.php file.

This is code is being maintained as a separate plugin to keep it independent from the theme, allowing the theme to be changed without losing these settings.

# features

Security: Prevent a list of usernames being accessed via either rest endpoints or the xml sitemap fuctionality

Clean unwanted stuff from HTML head section to reduce bloat

Globally disable support for Comments

Disable WordPress Admin Bar for all logged in users except Admins

Prevent WordPress generating previews of PDF documents

Customise login screen:
- disable login by email address - username only
- customise text in and around the login box
- use u3a logo and custom background


# contributing

If you want to update the code, please start a new branch and when ready to merge issue a pull request.
