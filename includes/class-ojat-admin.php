<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LJAT_Admin {

	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Register admin menu page.
	 */
	public function register_menu() {
		add_menu_page(
			__( 'Job Tracker', 'lime-job-tracker' ),
			__( 'Job Tracker', 'lime-job-tracker' ),
			'manage_options',
			'ljat-dashboard',
			array( $this, 'render_dashboard_page' ),
			'dashicons-portfolio',
			30
		);

		add_submenu_page(
			'ljat-dashboard',
			__( 'Add Application', 'lime-job-tracker' ),
			__( 'Add Application', 'lime-job-tracker' ),
			'manage_options',
			'ljat-add',
			array( $this, 'render_add_edit_page' )
		);
	}

	/**
	 * Enqueue admin assets.
	 */
	public function enqueue_assets( $hook ) {
		if ( false === strpos( $hook, 'ljat-' ) ) {
			return;
		}

		wp_enqueue_style(
			'ljat-base',
			LJAT_PLUGIN_URL . 'admin/css/base.css',
			array(),
			LJAT_VERSION
		);

		wp_enqueue_style(
			'ljat-plugin',
			LJAT_PLUGIN_URL . 'admin/css/plugin.css',
			array( 'ljat-base' ),
			LJAT_VERSION
		);

		wp_enqueue_script(
			'ljat-admin',
			LJAT_PLUGIN_URL . 'admin/js/admin.js',
			array( 'jquery' ),
			LJAT_VERSION,
			true
		);

		wp_localize_script( 'ljat-admin', 'ljatAdmin', array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'ljat_nonce' ),
			'i18n'    => array(
				'confirmDelete' => __( 'Are you sure you want to delete this application?', 'lime-job-tracker' ),
				'saved'         => __( 'Application saved successfully.', 'lime-job-tracker' ),
				'deleted'       => __( 'Application deleted.', 'lime-job-tracker' ),
				'error'         => __( 'Something went wrong. Please try again.', 'lime-job-tracker' ),
			),
		) );
	}

	/**
	 * Get the current active tab based on query param.
	 */
	private function get_current_tab() {
		return isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'all'; // phpcs:ignore
	}

	/**
	 * Render the main dashboard page.
	 */
	public function render_dashboard_page() {
		$db      = LJAT_Database::instance();
		$counts  = $db->get_status_counts();
		$current_tab = $this->get_current_tab();

		include LJAT_PLUGIN_DIR . 'admin/partials/dashboard.php';
	}

	/**
	 * Render the add/edit application page.
	 */
	public function render_add_edit_page() {
		$db   = LJAT_Database::instance();
		$edit = false;
		$item = null;

		if ( isset( $_GET['id'] ) ) { // phpcs:ignore
			$id   = absint( $_GET['id'] ); // phpcs:ignore
			$item = $db->get_application( $id );
			if ( $item ) {
				$edit = true;
			}
		}

		include LJAT_PLUGIN_DIR . 'admin/partials/application-form.php';
	}
}