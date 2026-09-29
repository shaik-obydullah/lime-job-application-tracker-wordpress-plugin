<?php
/**
 * Activation and database upgrade routines.
 *
 * @package obydullah-job-application-tracker
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Creates the applications table and keeps the schema current.
 */
class OJAT_Activator {

	/**
	 * Run on plugin activation.
	 *
	 * The table is built on every activation rather than only when missing so
	 * that a fresh install always lands on the current schema.
	 */
	public static function activate() {
		self::install_table();

		flush_rewrite_rules();
	}

	/**
	 * Bring the table in line with the schema version the code expects.
	 *
	 * Runs on every load, so it is a no-op once the stored version matches.
	 */
	public static function maybe_upgrade() {
		$installed = get_option( 'ojat_db_version' );

		if ( OJAT_DB_VERSION === $installed ) {
			return;
		}

		self::install_table();
	}

	/**
	 * Create or update the applications table and record its version.
	 */
	private static function install_table() {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$table_name      = $wpdb->prefix . 'ojat_applications';
		$charset_collate = $wpdb->get_charset_collate();

		/*
		 * TEXT columns are left nullable without a DEFAULT: MySQL rejects
		 * DEFAULT '' on TEXT before 8.0.13, and most MariaDB releases do too.
		 * Values are always written explicitly, so the default is unused.
		 */
		$sql = "CREATE TABLE {$table_name} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			company varchar(255) NOT NULL DEFAULT '',
			role_title varchar(255) NOT NULL DEFAULT '',
			location varchar(255) NOT NULL DEFAULT '',
			job_url text NULL,
			status varchar(20) NOT NULL DEFAULT 'saved',
			priority varchar(20) NOT NULL DEFAULT 'medium',
			date_applied date NULL,
			salary_range varchar(100) NOT NULL DEFAULT '',
			contact_name varchar(255) NOT NULL DEFAULT '',
			contact_email varchar(255) NOT NULL DEFAULT '',
			notes text NULL,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY status (status),
			KEY priority (priority),
			KEY date_applied (date_applied),
			KEY created_at (created_at)
		) {$charset_collate};";

		dbDelta( $sql );

		OJAT_Database::instance()->flush_cache();

		update_option( 'ojat_db_version', OJAT_DB_VERSION );
	}
}
