<?php
/**
 * Uninstall routine: removes the plugin's table and options.
 *
 * @package obydullah-job-application-tracker
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * Remove the applications table and stored options for the current site.
 */
function ojat_uninstall_site() {
	global $wpdb;

	$table_name = $wpdb->prefix . 'ojat_applications';

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- A table name cannot be a placeholder in prepare().
	$wpdb->query( "DROP TABLE IF EXISTS {$table_name}" );

	delete_option( 'ojat_db_version' );
}

if ( is_multisite() ) {
	$ojat_site_ids = get_sites(
		array(
			'fields' => 'ids',
			'number' => 0,
		)
	);

	foreach ( $ojat_site_ids as $ojat_site_id ) {
		switch_to_blog( $ojat_site_id );
		ojat_uninstall_site();
		restore_current_blog();
	}
} else {
	ojat_uninstall_site();
}
