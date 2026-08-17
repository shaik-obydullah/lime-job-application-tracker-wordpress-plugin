<?php
/**
 * Plugin Name:       Lime Job Application Tracker
 * Plugin URI:        https://obydullah.com/project/lime-job-tracker-wordpress-plugin/
 * Description:       Track and manage your job applications from the WordPress admin dashboard.
 * Version:           1.0.0
 * Requires at least: 7.0
 * Requires PHP:      8.0
 * Author:            Your Name
 * Author URI:        https://obydullah.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       lime-job-tracker
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'LJAT_VERSION', '1.0.0' );
define( 'LJAT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'LJAT_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'LJAT_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

require_once LJAT_PLUGIN_DIR . 'includes/class-ljat-activator.php';
require_once LJAT_PLUGIN_DIR . 'includes/class-ljat-deactivator.php';
require_once LJAT_PLUGIN_DIR . 'includes/class-ljat-database.php';
require_once LJAT_PLUGIN_DIR . 'includes/class-ljat-admin.php';
require_once LJAT_PLUGIN_DIR . 'includes/class-ljat-ajax.php';

register_activation_hook( __FILE__, array( 'LJAT_Activator', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'LJAT_Deactivator', 'deactivate' ) );

add_action( 'plugins_loaded', 'ljat_init' );

function ljat_init() {
	LJAT_Admin::instance();
	LJAT_Ajax::instance();
}