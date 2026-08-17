<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$status_labels = array(
	'saved'     => __( 'Saved', 'lime-job-tracker' ),
	'applied'   => __( 'Applied', 'lime-job-tracker' ),
	'interview' => __( 'Interview', 'lime-job-tracker' ),
	'offer'     => __( 'Offer', 'lime-job-tracker' ),
	'rejected'  => __( 'Rejected', 'lime-job-tracker' ),
	'withdrawn' => __( 'Withdrawn', 'lime-job-tracker' ),
);

$tab_status_map = array(
	'all'       => '',
	'saved'     => 'saved',
	'interview' => 'interview',
	'offers'    => 'offer',
);
?>
<div class="ljat-app">
<div class="jat-wrapper">

	<!-- Header -->
	<div class="jat-header">
		<h1>
			<span class="jat-icon dashicons dashicons-portfolio"></span>
			<?php esc_html_e( 'Lime Job Application Tracker', 'lime-job-tracker' ); ?>
		</h1>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=ljat-add' ) ); ?>" class="jat-btn jat-btn-primary">
			<span>+</span> <?php esc_html_e( 'Add Application', 'lime-job-tracker' ); ?>
		</a>
	</div>

	<!-- Stats -->
	<div class="jat-stats">
		<div class="jat-stat-card">
			<div class="jat-stat-icon blue dashicons dashicons-portfolio"></div>
			<div class="jat-stat-info">
				<h3><?php echo esc_html( $counts['total'] ); ?></h3>
				<p><?php esc_html_e( 'Total Applications', 'lime-job-tracker' ); ?></p>
			</div>
		</div>
		<div class="jat-stat-card">
			<div class="jat-stat-icon amber dashicons dashicons-calendar-alt"></div>
			<div class="jat-stat-info">
				<h3><?php echo esc_html( $counts['interview'] ); ?></h3>
				<p><?php esc_html_e( 'Interviews', 'lime-job-tracker' ); ?></p>
			</div>
		</div>
		<div class="jat-stat-card">
			<div class="jat-stat-icon green dashicons dashicons-awards"></div>
			<div class="jat-stat-info">
				<h3><?php echo esc_html( $counts['offer'] ); ?></h3>
				<p><?php esc_html_e( 'Offers', 'lime-job-tracker' ); ?></p>
			</div>
		</div>
		<div class="jat-stat-card">
			<div class="jat-stat-icon red dashicons dashicons-dismiss"></div>
			<div class="jat-stat-info">
				<h3><?php echo esc_html( $counts['rejected'] ); ?></h3>
				<p><?php esc_html_e( 'Rejected', 'lime-job-tracker' ); ?></p>
			</div>
		</div>
	</div>

	<!-- Tabs -->
	<div class="jat-tabs">
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=ljat-dashboard&tab=all' ) ); ?>"
		   class="jat-tab <?php echo 'all' === $current_tab ? 'active' : ''; ?>">
			<?php esc_html_e( 'All Jobs', 'lime-job-tracker' ); ?>
		</a>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=ljat-dashboard&tab=saved' ) ); ?>"
		   class="jat-tab <?php echo 'saved' === $current_tab ? 'active' : ''; ?>">
			<?php esc_html_e( 'Saved', 'lime-job-tracker' ); ?>
		</a>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=ljat-dashboard&tab=interview' ) ); ?>"
		   class="jat-tab <?php echo 'interview' === $current_tab ? 'active' : ''; ?>">
			<?php esc_html_e( 'Interviews', 'lime-job-tracker' ); ?>
		</a>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=ljat-dashboard&tab=offers' ) ); ?>"
		   class="jat-tab <?php echo 'offers' === $current_tab ? 'active' : ''; ?>">
			<?php esc_html_e( 'Offers', 'lime-job-tracker' ); ?>
		</a>
	</div>

	<!-- Toolbar -->
	<div class="jat-toolbar">
		<div class="jat-toolbar-left">
			<div class="jat-search-box">
				<span class="jat-search-icon dashicons dashicons-search"></span>
				<input type="text" class="jat-input" placeholder="<?php esc_attr_e( 'Search by company or role...', 'lime-job-tracker' ); ?>" id="ljat-search">
			</div>
			<select class="jat-select jat-filter-select" id="ljat-status-filter">
				<option value=""><?php esc_html_e( 'All Status', 'lime-job-tracker' ); ?></option>
				<?php foreach ( $status_labels as $key => $label ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
			<select class="jat-select jat-filter-select" id="ljat-priority-filter">
				<option value=""><?php esc_html_e( 'All Priority', 'lime-job-tracker' ); ?></option>
				<option value="high"><?php esc_html_e( 'High', 'lime-job-tracker' ); ?></option>
				<option value="medium"><?php esc_html_e( 'Medium', 'lime-job-tracker' ); ?></option>
				<option value="low"><?php esc_html_e( 'Low', 'lime-job-tracker' ); ?></option>
			</select>
			<button type="button" class="jat-btn jat-btn-primary jat-btn-sm" id="ljat-apply-filter">
				<span class="dashicons dashicons-filter"></span> <?php esc_html_e( 'Filter', 'lime-job-tracker' ); ?>
			</button>
			<button type="button" class="jat-btn jat-btn-secondary jat-btn-sm" id="ljat-reset-filter">
				<span class="dashicons dashicons-undo"></span> <?php esc_html_e( 'Reset', 'lime-job-tracker' ); ?>
			</button>
		</div>
	</div>

	<!-- Table -->
	<div class="jat-table-wrapper" id="ljat-table-wrapper">
		<table class="jat-table">
			<thead>
				<tr>
					<th data-sort="company"><?php esc_html_e( 'Company', 'lime-job-tracker' ); ?> <span class="sort-icon dashicons dashicons-arrow-up-alt2"></span></th>
					<th data-sort="role_title"><?php esc_html_e( 'Role', 'lime-job-tracker' ); ?> <span class="sort-icon dashicons dashicons-arrow-up-alt2"></span></th>
					<th data-sort="location"><?php esc_html_e( 'Location', 'lime-job-tracker' ); ?> <span class="sort-icon dashicons dashicons-arrow-up-alt2"></span></th>
					<th><?php esc_html_e( 'Status', 'lime-job-tracker' ); ?></th>
					<th><?php esc_html_e( 'Priority', 'lime-job-tracker' ); ?></th>
					<th data-sort="date_applied"><?php esc_html_e( 'Date Applied', 'lime-job-tracker' ); ?> <span class="sort-icon dashicons dashicons-arrow-up-alt2"></span></th>
					<th><?php esc_html_e( 'Actions', 'lime-job-tracker' ); ?></th>
				</tr>
			</thead>
			<tbody id="ljat-table-body">
				<?php
				$tab_status   = isset( $tab_status_map[ $current_tab ] ) ? $tab_status_map[ $current_tab ] : '';
				$query_args   = array(
					'status'   => $tab_status,
					'per_page' => 20,
					'page'     => isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1, // phpcs:ignore
				);
				$applications = $db->get_applications( $query_args );

				if ( empty( $applications['items'] ) ) :
				?>
				<tr class="ljat-empty-row">
					<td colspan="7">
						<div class="jat-empty-state">
							<div class="jat-empty-state-icon dashicons dashicons-clipboard"></div>
							<h3><?php esc_html_e( 'No applications found', 'lime-job-tracker' ); ?></h3>
							<p><?php esc_html_e( 'Start tracking your job applications by adding your first one.', 'lime-job-tracker' ); ?></p>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=ljat-add' ) ); ?>" class="jat-btn jat-btn-primary">
								<span>+</span> <?php esc_html_e( 'Add Application', 'lime-job-tracker' ); ?>
							</a>
						</div>
					</td>
				</tr>
				<?php else : ?>
					<?php foreach ( $applications['items'] as $item ) : ?>
					<tr data-id="<?php echo esc_attr( $item->id ); ?>">
						<td class="font-semibold"><?php echo esc_html( $item->company ); ?></td>
						<td><?php echo esc_html( $item->role_title ); ?></td>
						<td><?php echo esc_html( $item->location ); ?></td>
						<td>
							<span class="jat-status jat-status-<?php echo esc_attr( $item->status ); ?>">
								<span class="jat-status-dot"></span>
								<?php echo esc_html( $status_labels[ $item->status ] ?? $item->status ); ?>
							</span>
						</td>
						<td>
							<span class="jat-priority jat-priority-<?php echo esc_attr( $item->priority ); ?>">
								<span class="jat-priority-dot"></span>
								<?php echo esc_html( ucfirst( $item->priority ) ); ?>
							</span>
						</td>
						<td class="text-muted">
							<?php echo $item->date_applied ? esc_html( date_i18n( 'M d, Y', strtotime( $item->date_applied ) ) ) : '--'; ?>
						</td>
						<td>
							<div class="jat-table-actions">
								<button class="jat-btn-icon jat-btn-sm ljat-view-btn dashicons dashicons-visibility" data-id="<?php echo esc_attr( $item->id ); ?>" title="<?php esc_attr_e( 'View', 'lime-job-tracker' ); ?>"></button>
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=ljat-add&id=' . $item->id ) ); ?>" class="jat-btn-icon jat-btn-sm dashicons dashicons-edit" title="<?php esc_attr_e( 'Edit', 'lime-job-tracker' ); ?>"></a>
								<button class="jat-btn-icon jat-btn-sm ljat-delete-btn dashicons dashicons-trash" data-id="<?php echo esc_attr( $item->id ); ?>" title="<?php esc_attr_e( 'Delete', 'lime-job-tracker' ); ?>"></button>
							</div>
						</td>
					</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>

		<?php if ( $applications['total_pages'] > 1 ) : ?>
		<div class="jat-pagination">
			<span>
				<?php
				$from = ( (int) $applications['page'] - 1 ) * (int) $applications['per_page'] + 1;
				$to   = min( (int) $applications['page'] * (int) $applications['per_page'], (int) $applications['total'] );
				printf(
					/* translators: 1: from count, 2: to count, 3: total count */
					esc_html__( 'Showing %1$d - %2$d of %3$d applications', 'lime-job-tracker' ),
					$from,
					$to,
					(int) $applications['total']
				);
				?>
			</span>
			<div class="jat-pagination-pages">
				<?php for ( $i = 1; $i <= (int) $applications['total_pages']; $i++ ) : ?>
					<a href="<?php echo esc_url( add_query_arg( array( 'paged' => $i ), admin_url( 'admin.php?page=ljat-dashboard' ) ) ); ?>"
					   data-page="<?php echo esc_attr( $i ); ?>"
					   class="jat-page-btn <?php echo (int) $applications['page'] === $i ? 'active' : ''; ?>">
						<?php echo esc_html( $i ); ?>
					</a>
				<?php endfor; ?>
			</div>
		</div>
		<?php endif; ?>
	</div>

</div><!-- /.jat-wrapper -->

<!-- View Detail Modal -->
<div class="jat-modal-overlay" id="ljat-detail-modal">
	<div class="jat-modal">
		<div class="jat-modal-header">
			<h2><?php esc_html_e( 'Application Details', 'lime-job-tracker' ); ?></h2>
			<button class="jat-modal-close ljat-close-detail">&times;</button>
		</div>
		<div class="jat-modal-body" id="ljat-detail-content">
			<!-- Populated via AJAX -->
		</div>
		<div class="jat-modal-footer">
			<button class="jat-btn jat-btn-danger" id="ljat-detail-delete"><?php esc_html_e( 'Delete', 'lime-job-tracker' ); ?></button>
			<a href="#" class="jat-btn jat-btn-secondary" id="ljat-detail-edit"><?php esc_html_e( 'Edit', 'lime-job-tracker' ); ?></a>
			<button class="jat-btn jat-btn-primary ljat-close-detail"><?php esc_html_e( 'Close', 'lime-job-tracker' ); ?></button>
		</div>
	</div>
</div>
</div><!-- /.ljat-app -->
