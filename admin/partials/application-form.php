<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$page_title = $edit ? __( 'Edit Application', 'lime-job-tracker' ) : __( 'Add New Application', 'lime-job-tracker' );
$back_url   = admin_url( 'admin.php?page=ljat-dashboard' );

$status_options = array(
	''          => __( 'Select status', 'lime-job-tracker' ),
	'saved'     => __( 'Saved', 'lime-job-tracker' ),
	'applied'   => __( 'Applied', 'lime-job-tracker' ),
	'interview' => __( 'Interview', 'lime-job-tracker' ),
	'offer'     => __( 'Offer', 'lime-job-tracker' ),
	'rejected'  => __( 'Rejected', 'lime-job-tracker' ),
	'withdrawn' => __( 'Withdrawn', 'lime-job-tracker' ),
);
?>
<div class="ljat-app">
<div class="jat-wrapper">

	<div class="jat-header">
		<h1>
			<a href="<?php echo esc_url( $back_url ); ?>" class="jat-btn jat-btn-secondary jat-btn-sm" style="margin-right:8px;">&#8592;</a>
			<?php echo esc_html( $page_title ); ?>
		</h1>
	</div>

	<div class="jat-table-wrapper">
		<div class="jat-modal-body">
			<form id="ljat-app-form">
				<?php if ( $edit && $item ) : ?>
					<input type="hidden" name="id" value="<?php echo esc_attr( $item->id ); ?>">
				<?php endif; ?>

				<div class="jat-form-row">
					<div class="jat-form-group">
						<label><?php esc_html_e( 'Company', 'lime-job-tracker' ); ?> <span class="required">*</span></label>
						<input type="text" name="company" class="jat-input" placeholder="<?php esc_attr_e( 'e.g. Google', 'lime-job-tracker' ); ?>"
							value="<?php echo $edit ? esc_attr( $item->company ) : ''; ?>" required>
					</div>
					<div class="jat-form-group">
						<label><?php esc_html_e( 'Role', 'lime-job-tracker' ); ?> <span class="required">*</span></label>
						<input type="text" name="role_title" class="jat-input" placeholder="<?php esc_attr_e( 'e.g. Frontend Engineer', 'lime-job-tracker' ); ?>"
							value="<?php echo $edit ? esc_attr( $item->role_title ) : ''; ?>" required>
					</div>
				</div>

				<div class="jat-form-row">
					<div class="jat-form-group">
						<label><?php esc_html_e( 'Location', 'lime-job-tracker' ); ?></label>
						<input type="text" name="location" class="jat-input" placeholder="<?php esc_attr_e( 'e.g. Remote / New York, NY', 'lime-job-tracker' ); ?>"
							value="<?php echo $edit ? esc_attr( $item->location ) : ''; ?>">
					</div>
					<div class="jat-form-group">
						<label><?php esc_html_e( 'Job URL', 'lime-job-tracker' ); ?></label>
						<input type="url" name="job_url" class="jat-input" placeholder="https://..."
							value="<?php echo $edit ? esc_url( $item->job_url ) : ''; ?>">
					</div>
				</div>

				<div class="jat-form-row">
					<div class="jat-form-group">
						<label><?php esc_html_e( 'Date Applied', 'lime-job-tracker' ); ?></label>
						<input type="date" name="date_applied" class="jat-input"
							value="<?php echo $edit && $item->date_applied ? esc_attr( $item->date_applied ) : ''; ?>">
					</div>
					<div class="jat-form-group">
						<label><?php esc_html_e( 'Salary Range', 'lime-job-tracker' ); ?></label>
						<input type="text" name="salary_range" class="jat-input" placeholder="<?php esc_attr_e( 'e.g. $120k - $160k', 'lime-job-tracker' ); ?>"
							value="<?php echo $edit ? esc_attr( $item->salary_range ) : ''; ?>">
					</div>
				</div>

				<div class="jat-form-row">
					<div class="jat-form-group">
						<label><?php esc_html_e( 'Contact Person', 'lime-job-tracker' ); ?></label>
						<input type="text" name="contact_name" class="jat-input" placeholder="<?php esc_attr_e( 'Recruiter / Hiring Manager name', 'lime-job-tracker' ); ?>"
							value="<?php echo $edit ? esc_attr( $item->contact_name ) : ''; ?>">
					</div>
					<div class="jat-form-group">
						<label><?php esc_html_e( 'Contact Email', 'lime-job-tracker' ); ?></label>
						<input type="email" name="contact_email" class="jat-input" placeholder="<?php esc_attr_e( 'email@company.com', 'lime-job-tracker' ); ?>"
							value="<?php echo $edit ? esc_attr( $item->contact_email ) : ''; ?>">
					</div>
				</div>

				<div class="jat-form-group">
					<label><?php esc_html_e( 'Notes', 'lime-job-tracker' ); ?></label>
					<textarea name="notes" class="jat-textarea" placeholder="<?php esc_attr_e( 'Interview prep, follow-up dates, etc.', 'lime-job-tracker' ); ?>"><?php echo $edit ? esc_textarea( $item->notes ) : ''; ?></textarea>
				</div>

				<div style="width:100%; max-width:360px; margin-left:auto; margin-right:0;">
					<div class="jat-form-group">
						<label><?php esc_html_e( 'Status', 'lime-job-tracker' ); ?> <span class="required">*</span></label>
						<select name="status" class="jat-select" required>
							<?php foreach ( $status_options as $value => $label ) : ?>
								<option value="<?php echo esc_attr( $value ); ?>"
									<?php selected( $edit ? $item->status : '', $value ); ?>>
									<?php echo esc_html( $label ); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="jat-form-group">
						<label><?php esc_html_e( 'Priority', 'lime-job-tracker' ); ?></label>
						<select name="priority" class="jat-select">
							<option value="medium" <?php selected( $edit ? $item->priority : '', 'medium' ); ?>><?php esc_html_e( 'Medium', 'lime-job-tracker' ); ?></option>
							<option value="high" <?php selected( $edit ? $item->priority : '', 'high' ); ?>><?php esc_html_e( 'High', 'lime-job-tracker' ); ?></option>
							<option value="low" <?php selected( $edit ? $item->priority : '', 'low' ); ?>><?php esc_html_e( 'Low', 'lime-job-tracker' ); ?></option>
						</select>
					</div>
				</div>

				<div style="display:flex; gap:10px; justify-content:flex-end; margin-top:20px;">
					<a href="<?php echo esc_url( $back_url ); ?>" class="jat-btn jat-btn-secondary">
						<?php esc_html_e( 'Cancel', 'lime-job-tracker' ); ?>
					</a>
					<button type="submit" class="jat-btn jat-btn-primary" id="ljat-save-btn">
						<?php echo $edit ? esc_html__( 'Update Application', 'lime-job-tracker' ) : esc_html__( 'Save Application', 'lime-job-tracker' ); ?>
					</button>
				</div>

			</form>
		</div>
	</div>

</div>
</div><!-- /.ljat-app -->
