<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LJAT_Ajax {

	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'wp_ajax_ljat_save_application', array( $this, 'save_application' ) );
		add_action( 'wp_ajax_ljat_delete_application', array( $this, 'delete_application' ) );
		add_action( 'wp_ajax_ljat_get_application', array( $this, 'get_application' ) );
		add_action( 'wp_ajax_ljat_get_applications', array( $this, 'get_applications' ) );
	}

	/**
	 * Verify nonce and send JSON response.
	 */
	private function verify_nonce() {
		if ( ! check_ajax_referer( 'ljat_nonce', 'nonce' ) ) {
			wp_send_json_error( array( 'message' => 'Security check failed.' ), 403 );
		}
	}

	/**
	 * Save (insert or update) an application.
	 */
	public function save_application() {
		$this->verify_nonce();

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'Unauthorized.' ), 403 );
		}

		$db   = LJAT_Database::instance();
		$data = $_POST['data'] ?? array(); // phpcs:ignore

		if ( empty( $data['company'] ) || empty( $data['role_title'] ) ) {
			wp_send_json_error( array( 'message' => 'Company and role are required.' ) );
		}

		$id = isset( $data['id'] ) ? absint( $data['id'] ) : 0;

		if ( $id ) {
			$result = $db->update_application( $id, $data );
		} else {
			$result = $db->insert_application( $data );
			$id     = $result;
		}

		if ( $result ) {
			$item = $db->get_application( $id );
			wp_send_json_success( array( 'message' => 'Application saved.', 'item' => $item ) );
		} else {
			wp_send_json_error( array( 'message' => 'Failed to save application.' ) );
		}
	}

	/**
	 * Delete an application.
	 */
	public function delete_application() {
		$this->verify_nonce();

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'Unauthorized.' ), 403 );
		}

		$db = LJAT_Database::instance();
		$id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0; // phpcs:ignore

		if ( ! $id ) {
			wp_send_json_error( array( 'message' => 'Invalid ID.' ) );
		}

		$result = $db->delete_application( $id );

		if ( $result ) {
			wp_send_json_success( array( 'message' => 'Application deleted.' ) );
		} else {
			wp_send_json_error( array( 'message' => 'Failed to delete.' ) );
		}
	}

	/**
	 * Get a single application.
	 */
	public function get_application() {
		$this->verify_nonce();

		$db = LJAT_Database::instance();
		$id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0; // phpcs:ignore

		if ( ! $id ) {
			wp_send_json_error( array( 'message' => 'Invalid ID.' ) );
		}

		$item = $db->get_application( $id );

		if ( $item ) {
			wp_send_json_success( array( 'item' => $item ) );
		} else {
			wp_send_json_error( array( 'message' => 'Application not found.' ) );
		}
	}

	/**
	 * Get applications list (for AJAX filtering/pagination).
	 */
	public function get_applications() {
		$this->verify_nonce();

		$db = LJAT_Database::instance();

		$args = array(
			'status'   => isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : '', // phpcs:ignore
			'priority' => isset( $_GET['priority'] ) ? sanitize_text_field( wp_unslash( $_GET['priority'] ) ) : '', // phpcs:ignore
			'search'   => isset( $_GET['search'] ) ? sanitize_text_field( wp_unslash( $_GET['search'] ) ) : '', // phpcs:ignore
			'orderby'  => isset( $_GET['orderby'] ) ? sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) : 'created_at', // phpcs:ignore
			'order'    => isset( $_GET['order'] ) ? sanitize_text_field( wp_unslash( $_GET['order'] ) ) : 'DESC', // phpcs:ignore
			'per_page' => isset( $_GET['per_page'] ) ? absint( $_GET['per_page'] ) : 10, // phpcs:ignore
			'page'     => isset( $_GET['page'] ) ? absint( $_GET['page'] ) : 1, // phpcs:ignore
		);

		$result = $db->get_applications( $args );

		wp_send_json_success( $result );
	}
}
