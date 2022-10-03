<?php

/**
 * Plugin Name:       RHD Migration
 * Description:       One click to migrate a page, post, or custom post type from one WordPress to another WordPress. If you want to migrate post/page content from the stage to the production server, this plugin will be your best choice. 
 * Version:           1.0.0
 * Author:            Pankaj Dadure
 * Author URI:        http://www.pragmasoftwares.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       rhd-migration
 * Domain Path:       /languages
 */

if (!defined('WPINC')) {
    die;
}
global $wpdb;
define('RHD_VERSION', '1.0.0');

/**
 * The code that runs during plugin activation.
 */
function activate_rhd()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-rhd-activator.php';
    RHD_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_rhd()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-rhd-deactivator.php';
    RHD_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_rhd');
register_deactivation_hook(__FILE__, 'deactivate_rhd');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-rhd.php';

/**
 * Begins execution of the plugin.
 *
 * @since    1.0.0
 */
function run_rhd()
{
    $plugin = new RHD();
    $plugin->run();
}
run_rhd();


/**
 * Register our settings
 *
 * @since    1.0.0
 */
function rhd_settings()
{
    global $rhd_admin;
    register_setting('rhd-settings-group', 'rhd_site_url', array($rhd_admin, 'rhd_site_url_validation'));
    register_setting('rhd-settings-group', 'rhd_website');
    register_setting('rhd-settings-group', 'rhd_shash_key');
    register_setting('rhd-settings-group', 'rhd_dhash_key');
    register_setting('rhd-settings-group', 'rhd_destination_url', array($rhd_admin,  'rhd_destination_url_validation'));
    register_setting('rhd-settings-group', 'rhd_media_exclude');
    register_setting('rhd-settings-group', 'rhd_operation');
    register_setting('rhd-settings-group', 'rhd_author');
    register_setting('rhd-settings-group', 'rhd_comment');
    register_setting('rhd-settings-group', 'rhd_posts');
    register_setting('rhd-settings-group', 'rhd_media_ext', array($rhd_admin,  'rhd_media_ext_validation'));
}


/**
 * Load Setting page
 *
 * @since    1.0.0
 */
function rhd_migration()
{
    require_once plugin_dir_path(__FILE__) . 'admin/partials/rhd-general.php';
}
