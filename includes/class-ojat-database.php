<?php
/**
 * Database abstraction for job applications.
 *
 * @package obydullah-job-application-tracker
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles all database operations for the plugin.
 */
class OJAT_Database {

	/**
	 * Object cache group used for every read.
	 *
	 * @var string
	 */
	const CACHE_GROUP = 'ojat_applications';

	/**
	 * Rows per page when the caller does not say otherwise.
	 *
	 * Single source of truth: the admin screens and the AJAX list endpoint
	 * both read this rather than hardcoding their own number.
	 *
	 * @var int
	 */
	const DEFAULT_PER_PAGE = 20;

	/**
	 * Hard ceiling on rows per page, whatever the caller asks for.
	 *
	 * @var int
	 */
	const MAX_PER_PAGE = 200;

	/**
	 * Lifetime for cached read results, in seconds.
	 *
	 * @var int
	 */
	const CACHE_TTL = HOUR_IN_SECONDS;

	/**
	 * Singleton instance.
	 *
	 * @var OJAT_Database|null
	 */
	private static $instance = null;

	/**
	 * Database table name (prefixed).
	 *
	 * @var string
	 */
	private $table_name;

	/**
	 * Current cache version, used to invalidate every read at once.
	 *
	 * @var string|null
	 */
	private $cache_version = null;

	/**
	 * Get the singleton instance.
	 *
	 * @return OJAT_Database
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Private constructor to prevent direct instantiation.
	 */
	private function __construct() {
		global $wpdb;

		$this->table_name = $wpdb->prefix . 'ojat_applications';
	}

	/**
	 * Get the database table name.
	 *
	 * @return string
	 */
	public function get_table_name() {
		return $this->table_name;
	}

	/*
	 * Field definitions
	 * ---------------------------------------------------------------------
	 */

	/**
	 * The writable fields and the sanitizer each one uses.
	 *
	 * Single source of truth for sanitizing, the $wpdb format array, and the
	 * defaults applied when a field is absent. The column list in
	 * OJAT_Activator::install_table() must stay in sync with these keys.
	 *
	 * @return array<string, string>
	 */
	private function get_field_map() {
		return array(
			'company'       => 'sanitize_text_field',
			'role_title'    => 'sanitize_text_field',
			'location'      => 'sanitize_text_field',
			'job_url'       => 'esc_url_raw',
			'status'        => 'ojat_sanitize_status',
			'priority'      => 'ojat_sanitize_priority',
			'date_applied'  => 'ojat_sanitize_date',
			'salary_range'  => 'sanitize_text_field',
			'contact_name'  => 'sanitize_text_field',
			'contact_email' => 'sanitize_email',
			'notes'         => 'wp_kses_post',
		);
	}

	/**
	 * Get the default value used when a field is absent on insert.
	 *
	 * The date_applied field defaults to null, not '', so the DATE column stores NULL
	 * rather than MySQL's '0000-00-00' sentinel.
	 *
	 * @return array<string, string|null>
	 */
	private function get_field_defaults() {
		return array(
			'company'       => '',
			'role_title'    => '',
			'location'      => '',
			'job_url'       => '',
			'status'        => 'saved',
			'priority'      => 'medium',
			'date_applied'  => null,
			'salary_range'  => '',
			'contact_name'  => '',
			'contact_email' => '',
			'notes'         => '',
		);
	}

	/**
	 * The $wpdb format string for every field.
	 *
	 * Derived from the field map so the two can never drift apart.
	 *
	 * @return array<string, string>
	 */
	/**
	 * Build the positional $format array for $wpdb->insert() / $wpdb->update().
	 *
	 * Wpdb consumes $format positionally: process_field_formats() does
	 * array_shift( $formats ) once per data field, so entries line up with the
	 * order of the $data keys and any column names in the array are ignored.
	 * The array therefore has to hold exactly one entry per field.
	 *
	 * Deriving the length from the data means the two can never drift apart.
	 *
	 * @param array  $data   Data about to be passed to wpdb.
	 * @param string $format Specifier to use for each field.
	 * @return array Positional format array.
	 */
	private function get_formats( $data, $format = '%s' ) {
		return array_fill( 0, count( $data ), $format );
	}

	/**
	 * The explicit column list used by every SELECT.
	 *
	 * Reads name their columns rather than using SELECT *, so the query
	 * contract is visible at the call site and the two audit columns
	 * (created_at, updated_at) stay out of the result set — nothing in the
	 * admin UI displays them. created_at is still used for ORDER BY, which
	 * does not require selecting it.
	 *
	 * The list is derived from the field map so a newly added field is
	 * returned automatically. That coupling is deliberate: every field in
	 * the map is both writable and readable.
	 *
	 * @return string Comma-separated column names, prefixed with id.
	 */
	private function get_select_columns() {
		return 'id, ' . implode( ', ', array_keys( $this->get_field_map() ) );
	}

	/*
	 * Caching
	 * ---------------------------------------------------------------------
	 */

	/**
	 * Get the current cache version.
	 *
	 * Every read is cached under a key prefixed with this token, so bumping the
	 * token orphans all previously cached reads in one step.
	 *
	 * @return string
	 */
	private function get_cache_version() {
		if ( null === $this->cache_version ) {
			$version = wp_cache_get( 'version', self::CACHE_GROUP );

			if ( ! is_string( $version ) || '' === $version ) {
				$version = $this->new_cache_version();
				wp_cache_set( 'version', $version, self::CACHE_GROUP );
			}

			$this->cache_version = $version;
		}

		return $this->cache_version;
	}

	/**
	 * Build a monotonically increasing cache version token.
	 *
	 * @return string
	 */
	private function new_cache_version() {
		return str_replace( array( ' ', '.' ), '-', microtime() );
	}

	/**
	 * Build a versioned cache key.
	 *
	 * @param string $suffix Key suffix describing the cached value.
	 * @return string
	 */
	private function get_cache_key( $suffix ) {
		return 'ojat_' . $this->get_cache_version() . '_' . $suffix;
	}

	/**
	 * Invalidate every cached read.
	 *
	 * Called after any write. Bumping the version is enough: all reads are
	 * keyed by version, so the previous entries simply stop being looked up.
	 */
	public function flush_cache() {
		$version             = $this->new_cache_version();
		$this->cache_version = $version;

		wp_cache_set( 'version', $version, self::CACHE_GROUP );
	}

	/*
	 * Reads
	 * ---------------------------------------------------------------------
	 */

	/**
	 * Get all applications with optional filters and pagination.
	 *
	 * @param array $args Query arguments: status, priority, search, per_page, page.
	 * @return array{items: array, total: int, per_page: int, total_pages: int, page: int}
	 */
	public function get_applications( $args = array() ) {
		global $wpdb;

		$defaults = array(
			'status'   => '',
			'priority' => '',
			'search'   => '',
			'per_page' => self::DEFAULT_PER_PAGE,
			'page'     => 1,
		);

		$args = wp_parse_args( $args, $defaults );

		$status   = ojat_sanitize_status( $args['status'] );
		$priority = ojat_sanitize_priority( $args['priority'] );
		$search   = sanitize_text_field( $args['search'] );

		// Clamp both ends: 0 or negative would skip the LIMIT entirely, and an
		// unbounded page size lets one request pull the whole table.
		$per_page = min( self::MAX_PER_PAGE, max( 1, (int) $args['per_page'] ) );
		$page     = max( 1, (int) $args['page'] );

		$cache_key = $this->get_cache_key(
			'list_' . md5(
				wp_json_encode(
					array(
						'status'   => $status,
						'priority' => $priority,
						'search'   => $search,
						'per_page' => $per_page,
						'page'     => $page,
					)
				)
			)
		);

		$cached = wp_cache_get( $cache_key, self::CACHE_GROUP );
		if ( is_array( $cached ) ) {
			return $cached;
		}

		$clauses = array();
		$params  = array();
		$columns = $this->get_select_columns();

		if ( '' !== $status ) {
			$clauses[] = 'status = %s';
			$params[]  = $status;
		}

		if ( '' !== $priority ) {
			$clauses[] = 'priority = %s';
			$params[]  = $priority;
		}

		if ( '' !== $search ) {
			$like      = '%' . $wpdb->esc_like( $search ) . '%';
			$clauses[] = '(company LIKE %s OR role_title LIKE %s OR location LIKE %s)';
			$params[]  = $like;
			$params[]  = $like;
			$params[]  = $like;
		}

		$where = $clauses ? 'WHERE ' . implode( ' AND ', $clauses ) : '';

		// $where holds only the fixed clause strings built above -- the LIKE
		// wildcards go through esc_like() and every user value travels in
		// $params, never in the SQL text. When there are no clauses there are
		// no placeholders either, and prepare() must not be called: since WP
		// 6.2 it raises a notice for a query with nothing to interpolate.
		$count_sql = "SELECT COUNT(*) FROM {$this->table_name} {$where}";

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table name cannot be a placeholder; see note above.
		$total = (int) ( $params
			? $wpdb->get_var( $wpdb->prepare( $count_sql, $params ) )
			: $wpdb->get_var( $count_sql ) );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter

		$list_sql    = "SELECT {$columns} FROM {$this->table_name} {$where} ORDER BY created_at DESC, id DESC LIMIT %d OFFSET %d";
		$list_params = array_merge( $params, array( $per_page, max( 0, ( $page - 1 ) * $per_page ) ) );

		/* phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table name and column list cannot be placeholders; $where is built from fixed clause strings above and all values travel in $list_params. */
		$items = $wpdb->get_results( $wpdb->prepare( $list_sql, $list_params ) );

		$result = array(
			'items'       => $items,
			'total'       => $total,
			'per_page'    => $per_page,
			'total_pages' => (int) ceil( $total / $per_page ),
			'page'        => $page,
		);

		wp_cache_set( $cache_key, $result, self::CACHE_GROUP, self::CACHE_TTL );

		return $result;
	}

	/**
	 * Get a single application by ID.
	 *
	 * @param int $id Application ID.
	 * @return object|null
	 */
	public function get_application( $id ) {
		global $wpdb;

		$id = absint( $id );
		if ( ! $id ) {
			return null;
		}

		$cache_key = $this->get_cache_key( 'row_' . $id );
		$cached    = wp_cache_get( $cache_key, self::CACHE_GROUP, false, $found );

		if ( $found ) {
			return $cached;
		}

		$columns = $this->get_select_columns();

		/* phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table name and column list cannot be placeholders; $id is bound via %d. */
		$row = $wpdb->get_row( $wpdb->prepare( "SELECT {$columns} FROM {$this->table_name} WHERE id = %d", $id ) );

		wp_cache_set( $cache_key, $row, self::CACHE_GROUP, self::CACHE_TTL );

		return $row;
	}

	/**
	 * Get status counts for the dashboard stats.
	 *
	 * @return array<string, int>
	 */
	public function get_status_counts() {
		global $wpdb;

		$cache_key = $this->get_cache_key( 'status_counts' );
		$cached    = wp_cache_get( $cache_key, self::CACHE_GROUP );

		if ( is_array( $cached ) ) {
			return $cached;
		}

		/* phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table name cannot be a placeholder, and this query takes no user input at all. */
		$results = $wpdb->get_results( "SELECT status, COUNT(*) AS count FROM {$this->table_name} GROUP BY status" );

		$counts = array( 'total' => 0 );

		foreach ( ojat_get_statuses() as $status ) {
			$counts[ $status ] = 0;
		}

		if ( $results ) {
			foreach ( $results as $row ) {
				$counts['total'] += (int) $row->count;

				if ( isset( $counts[ $row->status ] ) ) {
					$counts[ $row->status ] = (int) $row->count;
				}
			}
		}

		wp_cache_set( $cache_key, $counts, self::CACHE_GROUP, self::CACHE_TTL );

		return $counts;
	}

	/*
	 * Writes
	 * ---------------------------------------------------------------------
	 */

	/**
	 * Insert a new application.
	 *
	 * @param array $data Application data to insert.
	 * @return int New row ID, or 0 on failure.
	 */
	public function insert_application( $data ) {
		global $wpdb;

		$insert_data = $this->sanitize_data( $data, $this->get_field_defaults() );
		$formats     = $this->get_formats( $insert_data );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$result = $wpdb->insert( $this->table_name, $insert_data, $formats );

		if ( false === $result ) {
			return 0;
		}

		$this->flush_cache();

		return (int) $wpdb->insert_id;
	}

	/**
	 * Update an existing application.
	 *
	 * Only the fields present in $data are written, so a partial update never
	 * blanks columns the caller did not supply.
	 *
	 * @param int   $id   Application ID.
	 * @param array $data Application data to update.
	 * @return bool True when the row exists and was written, false on query
	 *              failure or when no row matches the given ID.
	 */
	public function update_application( $id, $data ) {
		global $wpdb;

		$id = absint( $id );
		if ( ! $id ) {
			return false;
		}

		$update_data = $this->sanitize_data( $data, array() );

		if ( ! $update_data ) {
			// Nothing to write; report success only if the row still exists.
			return null !== $this->get_application( $id );
		}

		$formats = $this->get_formats( $update_data );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$result = $wpdb->update(
			$this->table_name,
			$update_data,
			array( 'id' => $id ),
			$formats,
			array( '%d' )
		);

		if ( false === $result ) {
			return false;
		}

		if ( 0 === $result ) {
			// MySQL reports 0 both when every value was already identical and
			// when no row matched. Drop the cached row first so the existence
			// check cannot be answered by a stale hit.
			wp_cache_delete( $this->get_cache_key( 'row_' . $id ), self::CACHE_GROUP );

			return null !== $this->get_application( $id );
		}

		$this->flush_cache();

		return true;
	}

	/**
	 * Delete an application.
	 *
	 * @param int $id Application ID.
	 * @return bool True when a row was deleted, false when nothing matched
	 *              or the query failed.
	 */
	public function delete_application( $id ) {
		global $wpdb;

		$id = absint( $id );
		if ( ! $id ) {
			return false;
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$result = $wpdb->delete(
			$this->table_name,
			array( 'id' => $id ),
			array( '%d' )
		);

		if ( false === $result || 0 === $result ) {
			return false;
		}

		$this->flush_cache();

		return true;
	}

	/*
	 * Sanitizing
	 * ---------------------------------------------------------------------
	 */

	/**
	 * Sanitize an incoming field set.
	 *
	 * @param array $data     Raw data, already unslashed.
	 * @param array $defaults Values to use for absent fields.
	 * @return array<string, string|null> Only keys present in $data or $defaults.
	 */
	private function sanitize_data( $data, $defaults = array() ) {
		$sanitized = array();

		foreach ( $this->get_field_map() as $field => $callback ) {
			if ( isset( $data[ $field ] ) ) {
				$sanitized[ $field ] = call_user_func( $callback, $data[ $field ] );
			} elseif ( array_key_exists( $field, $defaults ) ) {
				$sanitized[ $field ] = $defaults[ $field ];
			}
		}

		return $sanitized;
	}
}
