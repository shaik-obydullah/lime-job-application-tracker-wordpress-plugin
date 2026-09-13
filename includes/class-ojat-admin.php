<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class OJAT_Admin {

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
			__( 'Job Tracker', 'obydullah-job-application-tracker' ),
			__( 'Job Tracker', 'obydullah-job-application-tracker' ),
			'manage_options',
			'ojat-dashboard',
			array( $this, 'render_dashboard_page' ),
			'dashicons-portfolio',
			30
		);

		add_submenu_page(
			'ojat-dashboard',
			__( 'Add Application', 'obydullah-job-application-tracker' ),
			__( 'Add Application', 'obydullah-job-application-tracker' ),
			'manage_options',
			'ojat-add',
			array( $this, 'render_add_edit_page' )
		);
	}

	/**
	 * Enqueue admin assets.
	 */
	public function enqueue_assets( $hook ) {
		if ( false === strpos( $hook, 'ojat-' ) ) {
			return;
		}

		wp_enqueue_style(
			'ojat-base',
			OJAT_PLUGIN_URL . 'admin/css/base.css',
			array(),
			OJAT_VERSION
		);

		wp_enqueue_style(
			'ojat-plugin',
			OJAT_PLUGIN_URL . 'admin/css/plugin.css',
			array( 'ojat-base' ),
			OJAT_VERSION
		);

		wp_enqueue_script(
			'ojat-admin',
			OJAT_PLUGIN_URL . 'admin/js/admin.js',
			array( 'jquery', 'wp-i18n' ),
			OJAT_VERSION,
			true
		);

		wp_set_script_translations( 'ojat-admin', 'obydullah-job-application-tracker' );

		wp_localize_script( 'ojat-admin', 'ojatAdmin', array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'ojat_nonce' ),
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
		$db      = OJAT_Database::instance();
		$counts  = $db->get_status_counts();
		$current_tab = $this->get_current_tab();

		include OJAT_PLUGIN_DIR . 'admin/partials/dashboard.php';
	}

	/**
	 * Render the add/edit application page.
	 */
	public function render_add_edit_page() {
		$db   = OJAT_Database::instance();
		$edit = false;
		$item = null;

		if ( isset( $_GET['id'] ) ) { // phpcs:ignore
			$id   = absint( $_GET['id'] ); // phpcs:ignore
			$item = $db->get_application( $id );
			if ( $item ) {
				$edit = true;
			}
		}

		include OJAT_PLUGIN_DIR . 'admin/partials/application-form.php';
	}
}