<?php
/**
 * Deactivation routine.
 *
 * @package obydullah-job-application-tracker
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cleans up on deactivation.
 *
 * Application data is intentionally kept: the table and its rows survive
 * deactivation so nothing is lost if the plugin is reactivated. Use
 * uninstall.php to remove them.
 */
class OJAT_Deactivator {

	/**
	 * Run on plugin deactivation.
	 */
	public static function deactivate() {
		OJAT_Database::instance()->flush_cache();

		flush_rewrite_rules();
	}
}
