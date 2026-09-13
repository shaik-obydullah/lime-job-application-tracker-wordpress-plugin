<?php
/**
 * Dashboard template.
 *
 * @package obydullah-job-application-tracker
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$ojat_status_labels = array(
	'saved'     => __( 'Saved', 'obydullah-job-application-tracker' ),
	'applied'   => __( 'Applied', 'obydullah-job-application-tracker' ),
	'interview' => __( 'Interview', 'obydullah-job-application-tracker' ),
	'offer'     => __( 'Offer', 'obydullah-job-application-tracker' ),
	'rejected'  => __( 'Rejected', 'obydullah-job-application-tracker' ),
	'withdrawn' => __( 'Withdrawn', 'obydullah-job-application-tracker' ),
);

$ojat_tab_status_map = array(
	'all'       => '',
	'saved'     => 'saved',
	'interview' => 'interview',
	'offers'    => 'offer',
);
?>
<div class="ojat-app">
<div class="ojat-wrapper">

	<!-- Header -->
	<div class="ojat-header">
		<h1>
			<span class="ojat-icon dashicons dashicons-portfolio"></span>
			<?php esc_html_e( 'Obydullah Job Application Tracker', 'obydullah-job-application-tracker' ); ?>
		</h1>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=ojat-add' ) ); ?>" class="ojat-btn ojat-btn-primary">
			<span>+</span> <?php esc_html_e( 'Add Application', 'obydullah-job-application-tracker' ); ?>
		</a>
	</div>

	<!-- Stats -->
	<div class="ojat-stats">
		<div class="ojat-stat-card">
			<div class="ojat-stat-icon blue dashicons dashicons-portfolio"></div>
			<div class="ojat-stat-info">
				<h3><?php echo esc_html( $counts['total'] ); ?></h3>
				<p><?php esc_html_e( 'Total Applications', 'obydullah-job-application-tracker' ); ?></p>
			</div>
		</div>
		<div class="ojat-stat-card">
			<div class="ojat-stat-icon amber dashicons dashicons-calendar-alt"></div>
			<div class="ojat-stat-info">
				<h3><?php echo esc_html( $counts['interview'] ); ?></h3>
				<p><?php esc_html_e( 'Interviews', 'obydullah-job-application-tracker' ); ?></p>
			</div>
		</div>
		<div class="ojat-stat-card">
			<div class="ojat-stat-icon green dashicons dashicons-awards"></div>
			<div class="ojat-stat-info">
				<h3><?php echo esc_html( $counts['offer'] ); ?></h3>
				<p><?php esc_html_e( 'Offers', 'obydullah-job-application-tracker' ); ?></p>
			</div>
		</div>
		<div class="ojat-stat-card">
			<div class="ojat-stat-icon red dashicons dashicons-dismiss"></div>
			<div class="ojat-stat-info">
				<h3><?php echo esc_html( $counts['rejected'] ); ?></h3>
				<p><?php esc_html_e( 'Rejected', 'obydullah-job-application-tracker' ); ?></p>
			</div>
		</div>
	</div>

	<!-- Tabs -->
	<div class="ojat-tabs">
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=ojat-dashboard&tab=all' ) ); ?>"
			class="ojat-tab <?php echo 'all' === $current_tab ? 'active' : ''; ?>">
			<?php esc_html_e( 'All Jobs', 'obydullah-job-application-tracker' ); ?>
		</a>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=ojat-dashboard&tab=saved' ) ); ?>"
			class="ojat-tab <?php echo 'saved' === $current_tab ? 'active' : ''; ?>">
			<?php esc_html_e( 'Saved', 'obydullah-job-application-tracker' ); ?>
		</a>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=ojat-dashboard&tab=interview' ) ); ?>"
			class="ojat-tab <?php echo 'interview' === $current_tab ? 'active' : ''; ?>">
			<?php esc_html_e( 'Interviews', 'obydullah-job-application-tracker' ); ?>
		</a>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=ojat-dashboard&tab=offers' ) ); ?>"
			class="ojat-tab <?php echo 'offers' === $current_tab ? 'active' : ''; ?>">
			<?php esc_html_e( 'Offers', 'obydullah-job-application-tracker' ); ?>
		</a>
	</div>

	<!-- Toolbar -->
	<div class="ojat-toolbar">
		<div class="ojat-toolbar-left">
			<div class="ojat-search-box">
				<span class="ojat-search-icon dashicons dashicons-search"></span>
				<input type="text" class="ojat-input" placeholder="<?php esc_attr_e( 'Search by company or role...', 'obydullah-job-application-tracker' ); ?>" id="ojat-search">
			</div>
			<select class="ojat-select ojat-filter-select" id="ojat-status-filter">
				<option value=""><?php esc_html_e( 'All Status', 'obydullah-job-application-tracker' ); ?></option>
				<?php foreach ( $ojat_status_labels as $ojat_key => $ojat_label ) : ?>
					<option value="<?php echo esc_attr( $ojat_key ); ?>"><?php echo esc_html( $ojat_label ); ?></option>
				<?php endforeach; ?>
			</select>
			<select class="ojat-select ojat-filter-select" id="ojat-priority-filter">
				<option value=""><?php esc_html_e( 'All Priority', 'obydullah-job-application-tracker' ); ?></option>
				<option value="high"><?php esc_html_e( 'High', 'obydullah-job-application-tracker' ); ?></option>
				<option value="medium"><?php esc_html_e( 'Medium', 'obydullah-job-application-tracker' ); ?></option>
				<option value="low"><?php esc_html_e( 'Low', 'obydullah-job-application-tracker' ); ?></option>
			</select>
			<button type="button" class="ojat-btn ojat-btn-primary ojat-btn-sm" id="ojat-apply-filter">
				<span class="dashicons dashicons-filter"></span> <?php esc_html_e( 'Filter', 'obydullah-job-application-tracker' ); ?>
			</button>
			<button type="button" class="ojat-btn ojat-btn-secondary ojat-btn-sm" id="ojat-reset-filter">
				<span class="dashicons dashicons-undo"></span> <?php esc_html_e( 'Reset', 'obydullah-job-application-tracker' ); ?>
			</button>
		</div>
	</div>

	<!-- Table -->
	<div class="ojat-table-wrapper" id="ojat-table-wrapper">
		<table class="ojat-table">
			<thead>
				<tr>
					<th data-sort="company"><?php esc_html_e( 'Company', 'obydullah-job-application-tracker' ); ?> <span class="sort-icon dashicons dashicons-arrow-up-alt2"></span></th>
					<th data-sort="role_title"><?php esc_html_e( 'Role', 'obydullah-job-application-tracker' ); ?> <span class="sort-icon dashicons dashicons-arrow-up-alt2"></span></th>
					<th data-sort="location"><?php esc_html_e( 'Location', 'obydullah-job-application-tracker' ); ?> <span class="sort-icon dashicons dashicons-arrow-up-alt2"></span></th>
					<th><?php esc_html_e( 'Status', 'obydullah-job-application-tracker' ); ?></th>
					<th><?php esc_html_e( 'Priority', 'obydullah-job-application-tracker' ); ?></th>
					<th data-sort="date_applied"><?php esc_html_e( 'Date Applied', 'obydullah-job-application-tracker' ); ?> <span class="sort-icon dashicons dashicons-arrow-up-alt2"></span></th>
					<th><?php esc_html_e( 'Actions', 'obydullah-job-application-tracker' ); ?></th>
				</tr>
			</thead>
			<tbody id="ojat-table-body">
				<?php
				$ojat_tab_status   = isset( $ojat_tab_status_map[ $current_tab ] ) ? $ojat_tab_status_map[ $current_tab ] : '';
				$ojat_query_args   = array(
					'status'   => $ojat_tab_status,
					'per_page' => 20,
					'page'     => isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1, // phpcs:ignore
				);
				$ojat_applications = $db->get_applications( $ojat_query_args );

				if ( empty( $ojat_applications['items'] ) ) :
					?>
				<tr class="ojat-empty-row">
					<td colspan="7">
						<div class="ojat-empty-state">
							<div class="ojat-empty-state-icon dashicons dashicons-clipboard"></div>
							<h3><?php esc_html_e( 'No applications found', 'obydullah-job-application-tracker' ); ?></h3>
							<p><?php esc_html_e( 'Start tracking your job applications by adding your first one.', 'obydullah-job-application-tracker' ); ?></p>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=ojat-add' ) ); ?>" class="ojat-btn ojat-btn-primary">
								<span>+</span> <?php esc_html_e( 'Add Application', 'obydullah-job-application-tracker' ); ?>
							</a>
						</div>
					</td>
				</tr>
				<?php else : ?>
					<?php foreach ( $ojat_applications['items'] as $ojat_item ) : ?>
					<tr data-id="<?php echo esc_attr( $ojat_item->id ); ?>">
						<td class="font-semibold"><?php echo esc_html( $ojat_item->company ); ?></td>
						<td><?php echo esc_html( $ojat_item->role_title ); ?></td>
						<td><?php echo esc_html( $ojat_item->location ); ?></td>
						<td>
							<span class="ojat-status ojat-status-<?php echo esc_attr( $ojat_item->status ); ?>">
								<span class="ojat-status-dot"></span>
								<?php echo esc_html( $ojat_status_labels[ $ojat_item->status ] ?? $ojat_item->status ); ?>
							</span>
						</td>
						<td>
							<span class="ojat-priority ojat-priority-<?php echo esc_attr( $ojat_item->priority ); ?>">
								<span class="ojat-priority-dot"></span>
								<?php echo esc_html( ucfirst( $ojat_item->priority ) ); ?>
							</span>
						</td>
						<td class="text-muted">
							<?php echo $ojat_item->date_applied ? esc_html( date_i18n( 'M d, Y', strtotime( $ojat_item->date_applied ) ) ) : '--'; ?>
						</td>
						<td>
							<div class="ojat-table-actions">
								<button class="ojat-btn-icon ojat-btn-sm ojat-view-btn dashicons dashicons-visibility" data-id="<?php echo esc_attr( $ojat_item->id ); ?>" title="<?php esc_attr_e( 'View', 'obydullah-job-application-tracker' ); ?>"></button>
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=ojat-add&id=' . $ojat_item->id ) ); ?>" class="ojat-btn-icon ojat-btn-sm dashicons dashicons-edit" title="<?php esc_attr_e( 'Edit', 'obydullah-job-application-tracker' ); ?>"></a>
								<button class="ojat-btn-icon ojat-btn-sm ojat-delete-btn dashicons dashicons-trash" data-id="<?php echo esc_attr( $ojat_item->id ); ?>" title="<?php esc_attr_e( 'Delete', 'obydullah-job-application-tracker' ); ?>"></button>
							</div>
						</td>
					</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>

		<?php if ( $ojat_applications['total_pages'] > 1 ) : ?>
		<div class="ojat-pagination">
			<span>
				<?php
				$ojat_from = ( (int) $ojat_applications['page'] - 1 ) * (int) $ojat_applications['per_page'] + 1;
				$ojat_to   = min( (int) $ojat_applications['page'] * (int) $ojat_applications['per_page'], (int) $ojat_applications['total'] );
				echo esc_html(
					sprintf(
						/* translators: 1: from count, 2: to count, 3: total count */
						__( 'Showing %1$d - %2$d of %3$d applications', 'obydullah-job-application-tracker' ),
						$ojat_from,
						$ojat_to,
						(int) $ojat_applications['total']
					)
				);
				?>
			</span>
			<div class="ojat-pagination-pages">
				<?php for ( $ojat_i = 1; $ojat_i <= (int) $ojat_applications['total_pages']; $ojat_i++ ) : ?>
					<a href="<?php echo esc_url( add_query_arg( array( 'paged' => $ojat_i ), admin_url( 'admin.php?page=ojat-dashboard' ) ) ); ?>"
						data-page="<?php echo esc_attr( $ojat_i ); ?>"
						class="ojat-page-btn <?php echo (int) $ojat_applications['page'] === $ojat_i ? 'active' : ''; ?>">
						<?php echo esc_html( $ojat_i ); ?>
					</a>
				<?php endfor; ?>
			</div>
		</div>
		<?php endif; ?>
	</div>

</div><!-- /.ojat-wrapper -->

<!-- View Detail Modal -->
<div class="ojat-modal-overlay" id="ojat-detail-modal">
	<div class="ojat-modal">
		<div class="ojat-modal-header">
			<h2><?php esc_html_e( 'Application Details', 'obydullah-job-application-tracker' ); ?></h2>
			<button class="ojat-modal-close ojat-close-detail">&times;</button>
		</div>
		<div class="ojat-modal-body" id="ojat-detail-content">
			<!-- Populated via AJAX -->
		</div>
		<div class="ojat-modal-footer">
			<button class="ojat-btn ojat-btn-danger" id="ojat-detail-delete"><?php esc_html_e( 'Delete', 'obydullah-job-application-tracker' ); ?></button>
			<a href="#" class="ojat-btn ojat-btn-secondary" id="ojat-detail-edit"><?php esc_html_e( 'Edit', 'obydullah-job-application-tracker' ); ?></a>
			<button class="ojat-btn ojat-btn-primary ojat-close-detail"><?php esc_html_e( 'Close', 'obydullah-job-application-tracker' ); ?></button>
		</div>
	</div>
</div>
</div><!-- /.ojat-app -->
