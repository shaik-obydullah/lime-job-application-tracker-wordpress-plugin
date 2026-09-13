<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class OJAT_Activator {

	public static function activate() {
		self::create_table();
		flush_rewrite_rules();
	}

	private static function create_table() {
		global $wpdb;

		$table_name      = $wpdb->prefix . 'ojat_applications';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table_name} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			company varchar(255) NOT NULL,
			role_title varchar(255) NOT NULL,
			location varchar(255) DEFAULT '',
			job_url text DEFAULT '',
			status varchar(50) NOT NULL DEFAULT 'saved',
			priority varchar(20) NOT NULL DEFAULT 'medium',
			date_applied date DEFAULT NULL,
			salary_range varchar(100) DEFAULT '',
			contact_name varchar(255) DEFAULT '',
			contact_email varchar(255) DEFAULT '',
			notes text DEFAULT '',
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY status (status),
			KEY priority (priority),
			KEY date_applied (date_applied)
		) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		update_option( 'ojat_db_version', OJAT_VERSION );
	}
}
