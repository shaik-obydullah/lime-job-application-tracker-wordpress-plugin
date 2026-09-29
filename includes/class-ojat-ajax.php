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
	 * The capability required for every action in this class.
	 *
	 * @var string
	 */
	const CAPABILITY = 'manage_options';

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
	 * Reject the request unless it carries a valid nonce.
	 *
	 * Never returns on failure: wp_send_json_error() ends the request.
	 */
	/**
	 * Read a request parameter, preferring POST over GET.
	 *
	 * Admin-ajax accepts either transport, so handlers should not care which
	 * one the client used. Non-scalar values (a crafted array, say) are
	 * discarded rather than passed on to a caller expecting a string.
	 *
	 * @param string $key     Parameter name.
	 * @param mixed  $fallback Value to return when the parameter is absent.
	 * @return mixed Unslashed value, or $fallback.
	 */
	private function request_param( $key, $fallback = '' ) {
		// Every caller runs authorize() (nonce + capability) before reading, and
		// each caller sanitizes for its own field type -- the accessor cannot
		// know whether a given key is an int, an email, or a LIKE term.
		// phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Missing, WordPress.Security.NonceVerification.Recommended
		$value = isset( $_POST[ $key ] ) ? wp_unslash( $_POST[ $key ] ) : null;

		if ( null === $value && isset( $_GET[ $key ] ) ) {
			$value = wp_unslash( $_GET[ $key ] );
		}
		// phpcs:enable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Missing, WordPress.Security.NonceVerification.Recommended

		return ( null !== $value && is_scalar( $value ) ) ? $value : $fallback;
	}

	/**
	 * Reject the request unless it carries a valid nonce for $action.
	 *
	 * Each endpoint verifies its own action name rather than sharing one
	 * nonce, so a token minted for (say) reading a list cannot be replayed
	 * to delete a record.
	 *
	 * Never returns on failure: wp_send_json_error() ends the request.
	 *
	 * @param string $action Nonce action for this endpoint.
	 */
	private function verify_nonce( $action ) {
		$nonce = sanitize_text_field( (string) $this->request_param( 'nonce', '' ) );

		if ( ! wp_verify_nonce( $nonce, $action ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'obydullah-job-application-tracker' ) ), 403 );
		}
	}

	/**
	 * Reject the request unless the current user may manage applications.
	 *
	 * Never returns on failure: wp_send_json_error() ends the request.
	 */
	private function verify_capability() {
		if ( ! current_user_can( self::CAPABILITY ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized.', 'obydullah-job-application-tracker' ) ), 403 );
		}
	}

	/**
	 * Run both guards, in nonce-then-capability order.
	 *
	 * @param string $action Nonce action for this endpoint.
	 */
	private function authorize( $action ) {
		$this->verify_nonce( $action );
		$this->verify_capability();
	}

	/**
	 * Save (insert or update) an application.
	 */
	public function save_application() {
		$this->authorize( 'ojat_save_application' );

		// $data is an array of submitted fields; each is sanitized per its own
		// type by the database layer's field map.
		// phpcs:disable WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Nonce and capability are verified in authorize() above; values are sanitized in the field map.
		$raw  = isset( $_POST['data'] ) ? wp_unslash( $_POST['data'] ) : array();
		$data = is_array( $raw ) ? $raw : array();
		// phpcs:enable WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		if ( empty( $data['company'] ) || empty( $data['role_title'] ) ) {
			wp_send_json_error( array( 'message' => __( 'Company and role are required.', 'obydullah-job-application-tracker' ) ), 400 );
		}

		$db = OJAT_Database::instance();
		$id = isset( $data['id'] ) ? absint( $data['id'] ) : 0;

		// Drop the routing id so it is never written as a field.
		unset( $data['id'] );

		if ( $id ) {
			$updated = $db->update_application( $id, $data );

			if ( ! $updated ) {
				wp_send_json_error( array( 'message' => __( 'Failed to save application.', 'obydullah-job-application-tracker' ) ) );
			}
		} else {
			$id = $db->insert_application( $data );

			if ( ! $id ) {
				wp_send_json_error( array( 'message' => __( 'Failed to save application.', 'obydullah-job-application-tracker' ) ) );
			}
		}

		wp_send_json_success(
			array(
				'message' => __( 'Application saved.', 'obydullah-job-application-tracker' ),
				'item'    => $db->get_application( $id ),
			)
		);
	}

	/**
	 * Delete an application.
	 */
	public function delete_application() {
		$this->authorize( 'ojat_delete_application' );

		$id = absint( $this->request_param( 'id', 0 ) );

		if ( ! $id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid ID.', 'obydullah-job-application-tracker' ) ), 400 );
		}

		if ( ! OJAT_Database::instance()->delete_application( $id ) ) {
			wp_send_json_error( array( 'message' => __( 'Failed to delete.', 'obydullah-job-application-tracker' ) ) );
		}

		wp_send_json_success( array( 'message' => __( 'Application deleted.', 'obydullah-job-application-tracker' ) ) );
	}

	/**
	 * Get a single application.
	 */
	public function get_application() {
		$this->authorize( 'ojat_get_application' );

		$id = absint( $this->request_param( 'id', 0 ) );

		if ( ! $id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid ID.', 'obydullah-job-application-tracker' ) ), 400 );
		}

		$item = OJAT_Database::instance()->get_application( $id );

		if ( ! $item ) {
			wp_send_json_error( array( 'message' => __( 'Application not found.', 'obydullah-job-application-tracker' ) ) );
		}

		wp_send_json_success( array( 'item' => $item ) );
	}

	/**
	 * Get applications list (for AJAX filtering/pagination).
	 */
	public function get_applications() {
		$this->authorize( 'ojat_get_applications' );

		$args = array(
			'status'   => $this->request_param( 'status', '' ),
			'priority' => $this->request_param( 'priority', '' ),
			'search'   => $this->request_param( 'search', '' ),
			'per_page' => absint( $this->request_param( 'per_page', OJAT_Database::DEFAULT_PER_PAGE ) ),
			'page'     => absint( $this->request_param( 'page', 1 ) ),
		);

		// Sanitize here rather than in the handler: the database layer applies
		// the same helpers to direct callers.
		$args['status']   = ojat_sanitize_status( $args['status'] );
		$args['priority'] = ojat_sanitize_priority( $args['priority'] );
		$args['search']   = sanitize_text_field( $args['search'] );

		wp_send_json_success( OJAT_Database::instance()->get_applications( $args ) );
	}
}
