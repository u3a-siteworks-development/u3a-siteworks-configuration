<?php

/**
 * Plugin Name: u3a SiteWorks WordPress Configuration
 * Description: This plugin disables the WordPress comments facility, removes unnecessary material from the HTML head section, disables access to the 'users' rest endpoint and makes some changes in the admin menus
 * Author: u3a SiteWorks team
 * Author URI: https://siteworks.u3a.org.uk/
 * Plugin URI: https://siteworks.u3a.org.uk/
 * Version: 1.0.1
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.en.html
 */

if (!defined('ABSPATH')) {
    exit;
}
define('SW_CONFIGURATION_VERSION', '1.0.1');  // Set to current plugin version number

/*
 * Use the plugin update service on SiteWorks update server
 */

require 'plugin-update-checker/plugin-update-checker.php';

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$cfgUpdateChecker = PucFactory::buildUpdateChecker(
    'https://siteworks.u3a.org.uk/wp-update-server/?action=get_metadata&slug=u3a-siteworks-configuration', //Metadata URL
    __FILE__, //Full path to the main plugin file or functions.php.
    'u3a-siteworks-configuration'
);


/**
 * Enforce Strong Passwords
 */
require 'inc/strongpwd.php';

/*
* Prevent usernames being accessed via json (to support attempts to brute force login)
* Ref: https://www.wp-tweaks.com/hackers-can-find-your-wordpress-username/
*/

function disable_rest_endpoints($endpoints)
{
    if (isset($endpoints['/wp/v2/users'])) {
        unset($endpoints['/wp/v2/users']);
    }
    if (isset($endpoints['/wp/v2/users/(?P<id>[\d]+)'])) {
        unset($endpoints['/wp/v2/users/(?P<id>[\d]+)']);
    }
    return $endpoints;
}
add_filter('rest_endpoints', 'disable_rest_endpoints');

/*
* Prevent usernames appearing in XML sitemap
* Ref: https://duaneblake.co.uk/wordpress/how-to-remove-author-sitemaps-from-wordpress/
*/

function remove_author_category_pages_from_sitemap($provider, $name)
{
    if ('users' === $name) {
        return false;
    }
    return $provider;
}
add_filter('wp_sitemaps_add_provider', 'remove_author_category_pages_from_sitemap', 10, 2);

/*
    Clean unwanted stuff from HTML head section to improve performance
    Ref: https://bhoover.com/remove-unnecessary-code-from-your-wordpress-blog-header/
    Ref: https://whatabouthtml.com/how-to-clean-up-unnecessary-code-from-wordpress-header-175
*/
remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('wp_print_styles', 'print_emoji_styles');
remove_action('wp_head', 'wp_generator');
remove_action('wp_head', 'wlwmanifest_link');
remove_action('wp_head', 'rsd_link');
remove_action('wp_head', 'feed_links', 2);
remove_action('wp_head', 'feed_links_extra', 3);
remove_action('wp_head', 'wp_oembed_add_discovery_links', 10);
remove_action('wp_head', 'wp_oembed_add_host_js');
remove_action('rest_api_init', 'wp_oembed_register_route');
remove_filter('oembed_dataparse', 'wp_filter_oembed_result', 10);

/*
    Globally disable support for Comments
    Ref: https://gist.github.com/mattclements/eab5ef656b2f946c4bfb
*/

add_action('admin_init', function () {
    // Redirect any user trying to access comments page
    global $pagenow;

    if ($pagenow === 'edit-comments.php') {
        wp_safe_redirect(admin_url());
        exit;
    }

    // Remove comments metabox from dashboard
    remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');

    // Disable support for comments and trackbacks in post types
    foreach (get_post_types() as $post_type) {
        if (post_type_supports($post_type, 'comments')) {
            remove_post_type_support($post_type, 'comments');
            remove_post_type_support($post_type, 'trackbacks');
        }
    }
});

// Close comments on the front-end
add_filter('comments_open', '__return_false', 20, 2);
add_filter('pings_open', '__return_false', 20, 2);

// Hide existing comments
add_filter('comments_array', '__return_empty_array', 10, 2);

// Remove comments page in menu
add_action('admin_menu', function () {
    remove_menu_page('edit-comments.php');
});

// Remove comments links from admin bar
add_action('init', function () {
    if (is_admin_bar_showing()) {
        remove_action('admin_bar_menu', 'wp_admin_bar_comments_menu', 60);
    }
});

/*
* Disable WordPress Admin Bar for all users except Admins
* unless disabled with option u3a_enable_toolbar = 9
*/

add_action('after_setup_theme', 'remove_admin_bar');
function remove_admin_bar()
{
    $enableToolbar = get_option('u3a_enable_toolbar', 1);
    if ($enableToolbar == 9) {
        if (!current_user_can('manage_options') && !is_admin()) {
            show_admin_bar(false);
        }
    }
}


/*
* Set default ordering of posts in the admin screens to sort by title
*/

function set_post_order_in_admin($wp_query)
{
    global $pagenow;
    if (is_admin() && 'edit.php' == $pagenow && !isset($_GET['orderby'])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- GET value not processed, only presence tested
        $wp_query->set('orderby', 'title');
        $wp_query->set('order', 'ASC');
    }
}
add_filter('pre_get_posts', 'set_post_order_in_admin');


/**
 * Prevent WordPress generating previews of PDF documents
 * Ref: https://www.wpbeginner.com/wp-tutorials/how-to-disable-pdf-thumbnail-previews-in-wordpress/
 */

function wpb_disable_pdf_previews()
{
    $fallbacksizes = array();
    return $fallbacksizes;
}
add_filter('fallback_intermediate_image_sizes', 'wpb_disable_pdf_previews');


/* Customise the login page
 *
 * ref https://addwebsolution.com/blog/change-your-wordpress-login-page-without-plugin
 * ref https://www.wpbeginner.com/wp-tutorials/25-extremely-useful-tricks-for-the-wordpress-functions-file/
 */
function u3a_custom_login_url()
{
    return home_url('/');
}
add_filter('login_headerurl', 'u3a_custom_login_url');

function u3a_custom_login_css()
{
    wp_enqueue_style('login-styles', plugin_dir_url(__FILE__) . 'u3a_custom_login.css', array(), SW_CONFIGURATION_VERSION);
}
add_action('login_enqueue_scripts', 'u3a_custom_login_css');

// don't say what is wrong with login credentials
add_filter('login_errors', function () {
    return 'Something is wrong with your username or password.';
});

// disable login using email address
remove_filter('authenticate', 'wp_authenticate_email_password', 20);

// prompt above login box
add_filter('login_message', function () {
    $site = get_bloginfo('name');
    return '<p style="text-align: center; font-size:150%"><strong>' . $site . '</strong><br>Access to member-only content</p>';
});

// change the label for the username
// Ref https://stackoverflow.com/questions/12825865/change-wordpresss-login-label-username
add_action('login_head', 'u3a_set_login_text');
function u3a_set_login_text()
{
    add_filter('gettext', 'u3a_change_login_text');
    add_filter('ngettext', 'u3a_change_login_text');
    function u3a_change_login_text($translated)
    {
        $translated = str_ireplace('Username or Email Address', 'Your Username', $translated);
        return $translated;
    }
}

// change 'back to blog' link
function u3a_back_to_blog_link()
{
    return '<a href="' . home_url() . '">Go to home page</a>';
}
add_filter('login_site_html_link', 'u3a_back_to_blog_link');


// change the default 'Author' role to allow editing pages
function u3a_change_author_role()
{
    $role = get_role('author');
    $role->add_cap('edit_pages');
    $role->add_cap('edit_published_pages');
    $role->add_cap('publish_pages');
    $role->add_cap('edit_published_pages');
    $role->add_cap('delete_pages');
    $role->add_cap('delete_published_pages');
}
add_action('admin_init', 'u3a_change_author_role');

// ... but prevent 'Author' from being able to create new pages
function u3a_change_author_page_capability($args, $post_type)
{
    if ('page' === $post_type) {
        $args['capabilities'] = [
            'create_posts' => 'delete_others_posts',
        ];
    }

    return $args;
}
add_filter('register_post_type_args', 'u3a_change_author_page_capability', 10, 2);

// Prevent 'Author' from deleting a u3a group CPT
add_filter('map_meta_cap', function ($caps, $cap, $user_id, $args) {
    // Nothing to do if not delete_post capability
    if ('delete_post' !== $cap || empty($args[0]))
        return $caps;

    // If current user has 'author' role, disallow delete for u3a group CPT
    $user = get_userdata($user_id);
    if (in_array('author', $user->roles, true)) {
        if (in_array(get_post_type($args[0]), ['u3a_group'], true))
            $caps[] = 'do_not_allow';
    }
    return $caps;
}, 10, 4);

/**
 * Turn off the texturizer!
 */
add_filter('run_wptexturize', '__return_false');

// Prevent video file uploads
add_filter('upload_mimes', 'u3a_custom_mime_types');
function u3a_custom_mime_types($mimes)
{
    unset($mimes['asf|asx']);
    unset($mimes['wmv']);
    unset($mimes['wmx']);
    unset($mimes['wm']);
    unset($mimes['avi']);
    unset($mimes['divx']);
    unset($mimes['flv']);
    unset($mimes['mov|qt']);
    unset($mimes['mpeg|mpg|mpe']);
    unset($mimes['mp4|m4v']);
    unset($mimes['ogv']);
    unset($mimes['webm']);
    unset($mimes['mkv']);
    unset($mimes['3gp|3gpp']);
    unset($mimes['3g2|3gp2']);
    return $mimes;
}

// Use wp_config values (if present) to set up PHPMailer
// Note: these settings will be used by all email sent by WordPress
if (defined('SMTP_USER') && defined('SMTP_PASS')) {
    add_action('phpmailer_init', 'u3a_smtp_email_setup');
}
/**
 * Configure PHP Mailer
 * Constants should be defined in wp_config.php
 *
 * @param PHP Mailer instance $phpmailer
 * @return void
 */
function u3a_smtp_email_setup($phpmailer)
{
    $phpmailer->isSMTP();
    $phpmailer->Host       = SMTP_HOST;
    $phpmailer->SMTPAuth   = SMTP_AUTH;  // true
    $phpmailer->Port       = SMTP_PORT;
    $phpmailer->Username   = SMTP_USER;
    $phpmailer->Password   = SMTP_PASS;
    $phpmailer->SMTPSecure = SMTP_SECURE;  // 'tls'
    $phpmailer->From       = SMTP_FROM;
    $phpmailer->FromName   = SMTP_NAME;
}

// Move the 'Independent Analytics' plugin menu, if present, to below the Tools menu
add_action('admin_menu', 'u3a_move_analytics_menu');
function u3a_move_analytics_menu()
{
    global $menu;
    foreach ($menu as $ref => $menuitem) {
        if ($menuitem[0] == 'Analytics') {
            $menu[78] = $menu[$ref]; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- not aware of any other method to achieve this
            unset($menu[$ref]);
            return;
        }
    }
}

/* Configure the Lightbox for Gallery & Image Block to load assets on all pages
 * so that it works on content inside Query loops and other dynamic content blocks
 * ref: https://wordpress.org/plugins/gallery-block-lightbox/
 * Relates to bug #914
 */
add_filter( 'baguettebox_enqueue_assets', '__return_true' );


/* refer to the security section of https://developer.mozilla.org/en-US/docs/Web/HTTP
X-Frame-Options disallows embedding in <frame> so prevents our code from being
embedded in other sites (click-jacking).
nosniff prevents mime type sniffing, ensuring content-types are honoured.
Strict transport security ensures that HTTPS is always used, and no later HTTP
accesses are allowed.
*/

// default-src covers connect,style,script,object-src forcing them all to be 'self' - font should have data: for base64 encoded fonts
const DEFAULTSECURITY = "default-src 'unsafe-inline' 'unsafe-eval' 'self'; font-src 'self' data:; img-src * ; media-src *; frame-src * ;";

function add_security_headers()
{

    header("X-Frame-Options: DENY");

    header("X-Content-Type-Options: nosniff");

    header('Strict-Transport-Security: "max-age=31536000"');

    if (defined('CSP_REPORT_URL')) {
        $reporting_url = CSP_REPORT_URL;
        if (is_string($reporting_url) && (strlen($reporting_url) > 0)) {
            $securityheader = DEFAULTSECURITY . "report-uri " . $reporting_url;
            header("Content-Security-Policy-Report-Only: $securityheader");
        }
    }
}

add_action("send_headers", "add_security_headers");
