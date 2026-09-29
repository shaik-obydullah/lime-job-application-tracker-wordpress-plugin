<?php
/**
 * Admin menu, assets, and page rendering.
 *
 * @package obydullah-job-application-tracker
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the plugin's admin pages and assets.
 */
class OJAT_Admin {

	/**
	 * Rows shown per page on the dashboard.
	 *
	 * @var int
	 */
	const PER_PAGE = OJAT_Database::DEFAULT_PER_PAGE;

	/**
	 * Singleton instance.
	 *
	 * @var OJAT_Admin|null
	 */
	private static $instance = null;

	/**
	 * Get the singleton instance.
	 *
	 * @return OJAT_Admin
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Hook into the admin.
	 */
	private function __construct() {
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Register admin menu pages.
	 */
	public function register_menu() {
		add_menu_page(
			__( 'Job Tracker', 'obydullah-job-application-tracker' ),
			__( 'Job Tracker', 'obydullah-job-application-tracker' ),
			OJAT_Ajax::CAPABILITY,
			'ojat-dashboard',
			array( $this, 'render_dashboard_page' ),
			'dashicons-portfolio',
			100
		);

		add_submenu_page(
			'ojat-dashboard',
			__( 'Add Application', 'obydullah-job-application-tracker' ),
			__( 'Add Application', 'obydullah-job-application-tracker' ),
			OJAT_Ajax::CAPABILITY,
			'ojat-add',
			array( $this, 'render_add_edit_page' )
		);
	}

	/**
	 * Enqueue admin assets on the plugin's own screens.
	 *
	 * @param string $hook Current admin page hook suffix.
	 */
	public function enqueue_assets( $hook ) {
		if ( false === strpos( $hook, 'ojat-' ) ) {
			return;
		}

		wp_enqueue_style(
			'ojat-base',
			OJAT_PLUGIN_URL . 'admin/css/ojat-base.css',
			array(),
			OJAT_VERSION
		);

		wp_enqueue_style(
			'ojat-plugin',
			OJAT_PLUGIN_URL . 'admin/css/ojat-plugin.css',
			array( 'ojat-base' ),
			OJAT_VERSION
		);

		wp_enqueue_script(
			'ojat-admin',
			OJAT_PLUGIN_URL . 'admin/js/ojat-admin.js',
			array( 'jquery', 'wp-i18n' ),
			OJAT_VERSION,
			true
		);

		// Pass the plugin's own languages directory, otherwise WP looks for the
		// script's .json in wp-content/languages and shipped translations are ignored.
		wp_set_script_translations( 'ojat-admin', OJAT_TEXT_DOMAIN, OJAT_PLUGIN_DIR . 'languages' );

		wp_localize_script(
			'ojat-admin',
			'ojatAdmin',
			array(
				'ajaxUrl'    => admin_url( 'admin-ajax.php' ),
				'adminUrl'   => admin_url( 'admin.php' ),
				'editUrl'    => admin_url( 'admin.php?page=ojat-add&id=' ),
				// One nonce per endpoint, matching the action each handler verifies.
				'nonces'     => array(
					'save'   => wp_create_nonce( 'ojat_save_application' ),
					'delete' => wp_create_nonce( 'ojat_delete_application' ),
					'get'    => wp_create_nonce( 'ojat_get_application' ),
					'list'   => wp_create_nonce( 'ojat_get_applications' ),
				),
				'perPage'    => self::PER_PAGE,
				'statuses'   => ojat_get_status_labels(),
				'priorities' => ojat_get_priority_labels(),
				'dateFormat' => get_option( 'date_format' ),
				'i18n'       => array(
					'loading'      => __( 'Loading...', 'obydullah-job-application-tracker' ),
					'genericError' => __( 'Something went wrong. Please try again.', 'obydullah-job-application-tracker' ),
					'saving'       => __( 'Saving...', 'obydullah-job-application-tracker' ),
				),
			)
		);
	}

	/**
	 * Get the current dashboard tab, constrained to known values.
	 *
	 * @return string
	 */
	private function get_current_tab() {
		$tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'all'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		return array_key_exists( $tab, ojat_get_dashboard_tabs() ) ? $tab : 'all';
	}

	/**
	 * Get the current page number for list queries.
	 *
	 * @return int
	 */
	private function get_current_page() {
		return isset( $_GET['paged'] ) ? max( 1, absint( $_GET['paged'] ) ) : 1; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	}

	/**
	 * Render the main dashboard page.
	 */
	public function render_dashboard_page() {
		$counts       = OJAT_Database::instance()->get_status_counts();
		$current_tab  = $this->get_current_tab();
		$current_page = $this->get_current_page();

		$applications = OJAT_Database::instance()->get_applications(
			array(
				'status'   => ojat_get_dashboard_tabs()[ $current_tab ],
				'per_page' => self::PER_PAGE,
				'page'     => $current_page,
			)
		);

		include OJAT_PLUGIN_DIR . 'includes/ojat-dashboard.php';
	}

	/**
	 * Render the add/edit application page.
	 */
	public function render_add_edit_page() {
		$edit = false;
		$item = null;

		$id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( $id ) {
			$item = OJAT_Database::instance()->get_application( $id );

			if ( $item ) {
				$edit = true;
			}
		}

		include OJAT_PLUGIN_DIR . 'includes/ojat-application-form.php';
	}
}
