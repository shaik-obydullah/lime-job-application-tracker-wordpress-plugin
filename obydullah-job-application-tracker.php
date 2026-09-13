<?php
/**
 * Plugin Name:       Obydullah Job Application Tracker
 * Plugin URI:        https://obydullah.com/project/obydullah-job-application-tracker-wordpress-plugin/
 * Description:       Track and manage your job applications from the WordPress admin dashboard.
 * Version:           1.0.0
 * Requires at least: 7.0
 * Requires PHP:      8.0
 * Author:            Shaik Obydullah
 * Author URI:        https://obydullah.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       obydullah-job-application-tracker
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'OJAT_VERSION', '1.0.0' );
define( 'OJAT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'OJAT_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'OJAT_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

require_once OJAT_PLUGIN_DIR . 'includes/class-ojat-activator.php';
require_once OJAT_PLUGIN_DIR . 'includes/class-ojat-deactivator.php';
require_once OJAT_PLUGIN_DIR . 'includes/class-ojat-database.php';
require_once OJAT_PLUGIN_DIR . 'includes/class-ojat-admin.php';
require_once OJAT_PLUGIN_DIR . 'includes/class-ojat-ajax.php';

register_activation_hook( __FILE__, array( 'OJAT_Activator', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'OJAT_Deactivator', 'deactivate' ) );

add_action( 'plugins_loaded', 'ojat_init' );

function ojat_init() {
	OJAT_Admin::instance();
	OJAT_Ajax::instance();
}