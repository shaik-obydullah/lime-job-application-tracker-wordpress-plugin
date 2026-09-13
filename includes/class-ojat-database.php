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

	/**
	 * Get all applications with optional filters and pagination.
	 *
	 * @param array $args Query arguments: status, priority, search,
	 *                    per_page, page.
	 */
	public function get_applications( $args = array() ) {
		global $wpdb;

		$defaults = array(
			'status'   => '',
			'priority' => '',
			'search'   => '',
			'per_page' => 10,
			'page'     => 1,
		);

		$args = wp_parse_args( $args, $defaults );

		$status   = sanitize_key( $args['status'] );
		$priority = sanitize_key( $args['priority'] );
		$search   = sanitize_text_field( $args['search'] );
		$like     = ( '' !== $search ) ? '%' . $wpdb->esc_like( $search ) . '%' : '';

		$per_page = max( 1, (int) $args['per_page'] );
		$page     = max( 1, (int) $args['page'] );
		$offset   = ( $page - 1 ) * $per_page;

		$has_status   = ( '' !== $status );
		$has_priority = ( '' !== $priority );
		$has_search   = ( '' !== $like );

		if ( $has_status && $has_priority && $has_search ) {
			$total = (int) $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$wpdb->prefix}ojat_applications WHERE status = %s AND priority = %s AND (company LIKE %s OR role_title LIKE %s OR location LIKE %s)",
					$status,
					$priority,
					$like,
					$like,
					$like
				)
			);

			$items = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->prepare(
					"SELECT * FROM {$wpdb->prefix}ojat_applications WHERE status = %s AND priority = %s AND (company LIKE %s OR role_title LIKE %s OR location LIKE %s) ORDER BY created_at DESC LIMIT %d OFFSET %d",
					$status,
					$priority,
					$like,
					$like,
					$like,
					$per_page,
					$offset
				)
			);
		} elseif ( $has_status && $has_priority ) {
			$total = (int) $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$wpdb->prefix}ojat_applications WHERE status = %s AND priority = %s",
					$status,
					$priority
				)
			);

			$items = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->prepare(
					"SELECT * FROM {$wpdb->prefix}ojat_applications WHERE status = %s AND priority = %s ORDER BY created_at DESC LIMIT %d OFFSET %d",
					$status,
					$priority,
					$per_page,
					$offset
				)
			);
		} elseif ( $has_status && $has_search ) {
			$total = (int) $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$wpdb->prefix}ojat_applications WHERE status = %s AND (company LIKE %s OR role_title LIKE %s OR location LIKE %s)",
					$status,
					$like,
					$like,
					$like
				)
			);

			$items = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->prepare(
					"SELECT * FROM {$wpdb->prefix}ojat_applications WHERE status = %s AND (company LIKE %s OR role_title LIKE %s OR location LIKE %s) ORDER BY created_at DESC LIMIT %d OFFSET %d",
					$status,
					$like,
					$like,
					$like,
					$per_page,
					$offset
				)
			);
		} elseif ( $has_priority && $has_search ) {
			$total = (int) $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$wpdb->prefix}ojat_applications WHERE priority = %s AND (company LIKE %s OR role_title LIKE %s OR location LIKE %s)",
					$priority,
					$like,
					$like,
					$like
				)
			);

			$items = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->prepare(
					"SELECT * FROM {$wpdb->prefix}ojat_applications WHERE priority = %s AND (company LIKE %s OR role_title LIKE %s OR location LIKE %s) ORDER BY created_at DESC LIMIT %d OFFSET %d",
					$priority,
					$like,
					$like,
					$like,
					$per_page,
					$offset
				)
			);
		} elseif ( $has_status ) {
			$total = (int) $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$wpdb->prefix}ojat_applications WHERE status = %s",
					$status
				)
			);

			$items = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->prepare(
					"SELECT * FROM {$wpdb->prefix}ojat_applications WHERE status = %s ORDER BY created_at DESC LIMIT %d OFFSET %d",
					$status,
					$per_page,
					$offset
				)
			);
		} elseif ( $has_priority ) {
			$total = (int) $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$wpdb->prefix}ojat_applications WHERE priority = %s",
					$priority
				)
			);

			$items = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->prepare(
					"SELECT * FROM {$wpdb->prefix}ojat_applications WHERE priority = %s ORDER BY created_at DESC LIMIT %d OFFSET %d",
					$priority,
					$per_page,
					$offset
				)
			);
		} elseif ( $has_search ) {
			$total = (int) $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$wpdb->prefix}ojat_applications WHERE (company LIKE %s OR role_title LIKE %s OR location LIKE %s)",
					$like,
					$like,
					$like
				)
			);

			$items = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->prepare(
					"SELECT * FROM {$wpdb->prefix}ojat_applications WHERE (company LIKE %s OR role_title LIKE %s OR location LIKE %s) ORDER BY created_at DESC LIMIT %d OFFSET %d",
					$like,
					$like,
					$like,
					$per_page,
					$offset
				)
			);
		} else {
			$total = (int) $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				"SELECT COUNT(*) FROM {$wpdb->prefix}ojat_applications"
			);

			$items = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->prepare(
					"SELECT * FROM {$wpdb->prefix}ojat_applications ORDER BY created_at DESC LIMIT %d OFFSET %d",
					$per_page,
					$offset
				)
			);
		}

		return array(
			'items'       => $items,
			'total'       => $total,
			'per_page'    => $per_page,
			'total_pages' => (int) ceil( $total / $per_page ),
			'page'        => $page,
		);
	}

	/**
	 * Get a single application by ID.
	 *
	 * @param int $id Application ID.
	 */
	public function get_application( $id ) {
		global $wpdb;

		$id = absint( $id );
		if ( ! $id ) {
			return false;
		}

		return $wpdb->get_row( // phpcs:ignore
			$wpdb->prepare( "SELECT * FROM {$wpdb->prefix}ojat_applications WHERE id = %d", $id )
		);
	}

	/**
	 * Insert a new application.
	 *
	 * @param array $data Application data to insert.
	 */
	public function insert_application( $data ) {
		global $wpdb;

		$insert_data = $this->sanitize_data( $data );
		$format      = $this->get_format_array();

		$result = $wpdb->insert( $this->table_name, $insert_data, $format ); // phpcs:ignore

		if ( false === $result ) {
			return false;
		}

		return $wpdb->insert_id;
	}

	/**
	 * Update an existing application.
	 *
	 * @param int   $id   Application ID.
	 * @param array $data Application data to update.
	 */
	public function update_application( $id, $data ) {
		global $wpdb;

		$id = absint( $id );
		if ( ! $id ) {
			return false;
		}

		$update_data = $this->sanitize_data( $data );
		$format      = $this->get_format_array();

		$result = $wpdb->update( // phpcs:ignore
			$this->table_name,
			$update_data,
			array( 'id' => $id ),
			$format,
			array( '%d' )
		);

		return false !== $result;
	}

	/**
	 * Delete an application.
	 *
	 * @param int $id Application ID.
	 */
	public function delete_application( $id ) {
		global $wpdb;

		$id = absint( $id );
		if ( ! $id ) {
			return false;
		}

		$result = $wpdb->delete( // phpcs:ignore
			$this->table_name,
			array( 'id' => $id ),
			array( '%d' )
		);

		return false !== $result;
	}

	/**
	 * Get status counts for the dashboard stats.
	 */
	public function get_status_counts() {
		global $wpdb;

		$results = $wpdb->get_results( // phpcs:ignore
			"SELECT status, COUNT(*) as count FROM {$wpdb->prefix}ojat_applications GROUP BY status"
		);

		$counts = array(
			'total'     => 0,
			'saved'     => 0,
			'applied'   => 0,
			'interview' => 0,
			'offer'     => 0,
			'rejected'  => 0,
			'withdrawn' => 0,
		);

		if ( $results ) {
			foreach ( $results as $row ) {
				$counts['total'] += (int) $row->count;
				if ( isset( $counts[ $row->status ] ) ) {
					$counts[ $row->status ] = (int) $row->count;
				}
			}
		}

		return $counts;
	}

	/**
	 * Sanitize input data.
	 *
	 * @param array $data Raw application data.
	 */
	private function sanitize_data( $data ) {
		$sanitized = array();

		$allowed_fields = array(
			'company'       => 'sanitize_text_field',
			'role_title'    => 'sanitize_text_field',
			'location'      => 'sanitize_text_field',
			'job_url'       => 'esc_url_raw',
			'status'        => 'sanitize_text_field',
			'priority'      => 'sanitize_text_field',
			'date_applied'  => 'sanitize_text_field',
			'salary_range'  => 'sanitize_text_field',
			'contact_name'  => 'sanitize_text_field',
			'contact_email' => 'sanitize_email',
			'notes'         => 'wp_kses_post',
		);

		foreach ( $allowed_fields as $field => $sanitize_fn ) {
			if ( isset( $data[ $field ] ) ) {
				$sanitized[ $field ] = call_user_func( $sanitize_fn, $data[ $field ] );
			}
		}

		return $sanitized;
	}

	/**
	 * Get format array forwpdb->insert/update.
	 */
	private function get_format_array() {
		return array(
			'company'       => '%s',
			'role_title'    => '%s',
			'location'      => '%s',
			'job_url'       => '%s',
			'status'        => '%s',
			'priority'      => '%s',
			'date_applied'  => '%s',
			'salary_range'  => '%s',
			'contact_name'  => '%s',
			'contact_email' => '%s',
			'notes'         => '%s',
		);
	}
}
