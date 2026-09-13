<?php
/**
 * AJAX handlers for job applications.
 *
 * @package obydullah-job-application-tracker
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles all AJAX requests for the plugin.
 */
class OJAT_Ajax {

	/**
	 * Singleton instance.
	 *
	 * @var OJAT_Ajax|null
	 */
	private static $instance = null;

	/**
	 * Get the singleton instance.
	 *
	 * @return OJAT_Ajax
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Register AJAX actions.
	 */
	private function __construct() {
		add_action( 'wp_ajax_ojat_save_application', array( $this, 'save_application' ) );
		add_action( 'wp_ajax_ojat_delete_application', array( $this, 'delete_application' ) );
		add_action( 'wp_ajax_ojat_get_application', array( $this, 'get_application' ) );
		add_action( 'wp_ajax_ojat_get_applications', array( $this, 'get_applications' ) );
	}

	/**
	 * Verify nonce and send JSON response.
	 */
	private function verify_nonce() {
		if ( ! check_ajax_referer( 'ojat_nonce', 'nonce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'obydullah-job-application-tracker' ) ), 403 );
		}
	}

	/**
	 * Save (insert or update) an application.
	 */
	public function save_application() {
		$this->verify_nonce();

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized.', 'obydullah-job-application-tracker' ) ), 403 );
		}

		$db   = OJAT_Database::instance();
		$data = $_POST['data'] ?? array(); // phpcs:ignore

		if ( empty( $data['company'] ) || empty( $data['role_title'] ) ) {
			wp_send_json_error( array( 'message' => __( 'Company and role are required.', 'obydullah-job-application-tracker' ) ) );
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
			wp_send_json_success(
				array(
					'message' => __( 'Application saved.', 'obydullah-job-application-tracker' ),
					'item'    => $item,
				)
			);
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to save application.', 'obydullah-job-application-tracker' ) ) );
		}
	}

	/**
	 * Delete an application.
	 */
	public function delete_application() {
		$this->verify_nonce();

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized.', 'obydullah-job-application-tracker' ) ), 403 );
		}

		$db = OJAT_Database::instance();
		$id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0; // phpcs:ignore

		if ( ! $id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid ID.', 'obydullah-job-application-tracker' ) ) );
		}

		$result = $db->delete_application( $id );

		if ( $result ) {
			wp_send_json_success( array( 'message' => __( 'Application deleted.', 'obydullah-job-application-tracker' ) ) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to delete.', 'obydullah-job-application-tracker' ) ) );
		}
	}

	/**
	 * Get a single application.
	 */
	public function get_application() {
		$this->verify_nonce();

		$db = OJAT_Database::instance();
		$id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0; // phpcs:ignore

		if ( ! $id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid ID.', 'obydullah-job-application-tracker' ) ) );
		}

		$item = $db->get_application( $id );

		if ( $item ) {
			wp_send_json_success( array( 'item' => $item ) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Application not found.', 'obydullah-job-application-tracker' ) ) );
		}
	}

	/**
	 * Get applications list (for AJAX filtering/pagination).
	 */
	public function get_applications() {
		$this->verify_nonce();

		$db = OJAT_Database::instance();

		$args = array(
			'status'   => isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : '', // phpcs:ignore
			'priority' => isset( $_GET['priority'] ) ? sanitize_text_field( wp_unslash( $_GET['priority'] ) ) : '', // phpcs:ignore
			'search'   => isset( $_GET['search'] ) ? sanitize_text_field( wp_unslash( $_GET['search'] ) ) : '', // phpcs:ignore
			'per_page' => isset( $_GET['per_page'] ) ? absint( $_GET['per_page'] ) : 10, // phpcs:ignore
			'page'     => isset( $_GET['page'] ) ? absint( $_GET['page'] ) : 1, // phpcs:ignore
		);

		$result = $db->get_applications( $args );

		wp_send_json_success( $result );
	}
}
