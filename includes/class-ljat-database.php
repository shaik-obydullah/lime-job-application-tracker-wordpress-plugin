<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LJAT_Database {

	private static $instance = null;
	private $table_name;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		global $wpdb;
		$this->table_name = $wpdb->prefix . 'ljat_applications';
	}

	public function get_table_name() {
		return $this->table_name;
	}

	/**
	 * Get all applications with optional filters and pagination.
	 */
	public function get_applications( $args = array() ) {
		global $wpdb;

		$defaults = array(
			'status'   => '',
			'priority' => '',
			'search'   => '',
			'orderby'  => 'created_at',
			'order'    => 'DESC',
			'per_page' => 10,
			'page'     => 1,
		);

		$args  = wp_parse_args( $args, $defaults );
		$where = array( '1=1' );
		$values = array();

		if ( ! empty( $args['status'] ) ) {
			$where[]  = 'status = %s';
			$values[] = $args['status'];
		}

		if ( ! empty( $args['priority'] ) ) {
			$where[]  = 'priority = %s';
			$values[] = $args['priority'];
		}

		if ( ! empty( $args['search'] ) ) {
			$where[]  = '(company LIKE %s OR role_title LIKE %s OR location LIKE %s)';
			$like     = '%' . $wpdb->esc_like( $args['search'] ) . '%';
			$values[] = $like;
			$values[] = $like;
			$values[] = $like;
		}

		$where_clause = implode( ' AND ', $where );

		$allowed_orderby = array( 'company', 'role_title', 'location', 'date_applied', 'created_at', 'status', 'priority' );
		$orderby         = in_array( $args['orderby'], $allowed_orderby, true ) ? $args['orderby'] : 'created_at';
		$order           = 'ASC' === strtoupper( $args['order'] ) ? 'ASC' : 'DESC';

		$offset = ( max( 1, (int) $args['page'] ) - 1 ) * (int) $args['per_page'];

		// Total count query.
		if ( ! empty( $values ) ) {
			$count_query = $wpdb->prepare( "SELECT COUNT(*) FROM {$this->table_name} WHERE {$where_clause}", $values ); // phpcs:ignore
		} else {
			$count_query = "SELECT COUNT(*) FROM {$this->table_name} WHERE {$where_clause}";
		}
		$total = (int) $wpdb->get_var( $count_query ); // phpcs:ignore

		// Data query.
		$query = $wpdb->prepare(
			"SELECT * FROM {$this->table_name} WHERE {$where_clause} ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d",
			array_merge( $values, array( (int) $args['per_page'], $offset ) )
		); // phpcs:ignore

		$results = $wpdb->get_results( $query ); // phics:ignore

		return array(
			'items'       => $results ? $results : array(),
			'total'       => $total,
			'per_page'    => (int) $args['per_page'],
			'total_pages' => (int) ceil( $total / (int) $args['per_page'] ),
			'page'        => (int) $args['page'],
		);
	}

	/**
	 * Get a single application by ID.
	 */
	public function get_application( $id ) {
		global $wpdb;

		$id = absint( $id );
		if ( ! $id ) {
			return false;
		}

		return $wpdb->get_row( // phpcs:ignore
			$wpdb->prepare( "SELECT * FROM {$this->table_name} WHERE id = %d", $id )
		);
	}

	/**
	 * Insert a new application.
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
			"SELECT status, COUNT(*) as count FROM {$this->table_name} GROUP BY status"
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
