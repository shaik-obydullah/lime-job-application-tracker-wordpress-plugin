<?php
/**
 * Add/edit application form template.
 *
 * @package obydullah-job-application-tracker
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$ojat_page_title = $edit ? __( 'Edit Application', 'obydullah-job-application-tracker' ) : __( 'Add New Application', 'obydullah-job-application-tracker' );
$ojat_back_url   = admin_url( 'admin.php?page=ojat-dashboard' );

$ojat_status_options = array(
	''          => __( 'Select status', 'obydullah-job-application-tracker' ),
	'saved'     => __( 'Saved', 'obydullah-job-application-tracker' ),
	'applied'   => __( 'Applied', 'obydullah-job-application-tracker' ),
	'interview' => __( 'Interview', 'obydullah-job-application-tracker' ),
	'offer'     => __( 'Offer', 'obydullah-job-application-tracker' ),
	'rejected'  => __( 'Rejected', 'obydullah-job-application-tracker' ),
	'withdrawn' => __( 'Withdrawn', 'obydullah-job-application-tracker' ),
);
?>
<div class="ojat-app">
<div class="ojat-wrapper">

	<div class="ojat-header">
		<h1>
			<a href="<?php echo esc_url( $ojat_back_url ); ?>" class="ojat-btn ojat-btn-secondary ojat-btn-sm" style="margin-right:8px;">&#8592;</a>
			<?php echo esc_html( $ojat_page_title ); ?>
		</h1>
	</div>

	<div class="ojat-table-wrapper">
		<div class="ojat-modal-body">
			<form id="ojat-app-form">
				<?php if ( $edit && $item ) : ?>
					<input type="hidden" name="id" value="<?php echo esc_attr( $item->id ); ?>">
				<?php endif; ?>

				<div class="ojat-form-row">
					<div class="ojat-form-group">
						<label><?php esc_html_e( 'Company', 'obydullah-job-application-tracker' ); ?> <span class="required">*</span></label>
						<input type="text" name="company" class="ojat-input" placeholder="<?php esc_attr_e( 'e.g. Google', 'obydullah-job-application-tracker' ); ?>"
							value="<?php echo $edit ? esc_attr( $item->company ) : ''; ?>" required>
					</div>
					<div class="ojat-form-group">
						<label><?php esc_html_e( 'Role', 'obydullah-job-application-tracker' ); ?> <span class="required">*</span></label>
						<input type="text" name="role_title" class="ojat-input" placeholder="<?php esc_attr_e( 'e.g. Frontend Engineer', 'obydullah-job-application-tracker' ); ?>"
							value="<?php echo $edit ? esc_attr( $item->role_title ) : ''; ?>" required>
					</div>
				</div>

				<div class="ojat-form-row">
					<div class="ojat-form-group">
						<label><?php esc_html_e( 'Location', 'obydullah-job-application-tracker' ); ?></label>
						<input type="text" name="location" class="ojat-input" placeholder="<?php esc_attr_e( 'e.g. Remote / New York, NY', 'obydullah-job-application-tracker' ); ?>"
							value="<?php echo $edit ? esc_attr( $item->location ) : ''; ?>">
					</div>
					<div class="ojat-form-group">
						<label><?php esc_html_e( 'Job URL', 'obydullah-job-application-tracker' ); ?></label>
						<input type="url" name="job_url" class="ojat-input" placeholder="https://..."
							value="<?php echo $edit ? esc_url( $item->job_url ) : ''; ?>">
					</div>
				</div>

				<div class="ojat-form-row">
					<div class="ojat-form-group">
						<label><?php esc_html_e( 'Date Applied', 'obydullah-job-application-tracker' ); ?></label>
						<input type="date" name="date_applied" class="ojat-input"
							value="<?php echo $edit && $item->date_applied ? esc_attr( $item->date_applied ) : ''; ?>">
					</div>
					<div class="ojat-form-group">
						<label><?php esc_html_e( 'Salary Range', 'obydullah-job-application-tracker' ); ?></label>
						<input type="text" name="salary_range" class="ojat-input" placeholder="<?php esc_attr_e( 'e.g. $120k - $160k', 'obydullah-job-application-tracker' ); ?>"
							value="<?php echo $edit ? esc_attr( $item->salary_range ) : ''; ?>">
					</div>
				</div>

				<div class="ojat-form-row">
					<div class="ojat-form-group">
						<label><?php esc_html_e( 'Contact Person', 'obydullah-job-application-tracker' ); ?></label>
						<input type="text" name="contact_name" class="ojat-input" placeholder="<?php esc_attr_e( 'Recruiter / Hiring Manager name', 'obydullah-job-application-tracker' ); ?>"
							value="<?php echo $edit ? esc_attr( $item->contact_name ) : ''; ?>">
					</div>
					<div class="ojat-form-group">
						<label><?php esc_html_e( 'Contact Email', 'obydullah-job-application-tracker' ); ?></label>
						<input type="email" name="contact_email" class="ojat-input" placeholder="<?php esc_attr_e( 'email@company.com', 'obydullah-job-application-tracker' ); ?>"
							value="<?php echo $edit ? esc_attr( $item->contact_email ) : ''; ?>">
					</div>
				</div>

				<div class="ojat-form-group">
					<label><?php esc_html_e( 'Notes', 'obydullah-job-application-tracker' ); ?></label>
					<textarea name="notes" class="ojat-textarea" placeholder="<?php esc_attr_e( 'Interview prep, follow-up dates, etc.', 'obydullah-job-application-tracker' ); ?>"><?php echo $edit ? esc_textarea( $item->notes ) : ''; ?></textarea>
				</div>

				<div style="width:100%; max-width:360px; margin-left:auto; margin-right:0;">
					<div class="ojat-form-group">
						<label><?php esc_html_e( 'Status', 'obydullah-job-application-tracker' ); ?> <span class="required">*</span></label>
						<select name="status" class="ojat-select" required>
							<?php foreach ( $ojat_status_options as $ojat_value => $ojat_label ) : ?>
								<option value="<?php echo esc_attr( $ojat_value ); ?>"
									<?php selected( $edit ? $item->status : '', $ojat_value ); ?>>
									<?php echo esc_html( $ojat_label ); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="ojat-form-group">
						<label><?php esc_html_e( 'Priority', 'obydullah-job-application-tracker' ); ?></label>
						<select name="priority" class="ojat-select">
							<option value="medium" <?php selected( $edit ? $item->priority : '', 'medium' ); ?>><?php esc_html_e( 'Medium', 'obydullah-job-application-tracker' ); ?></option>
							<option value="high" <?php selected( $edit ? $item->priority : '', 'high' ); ?>><?php esc_html_e( 'High', 'obydullah-job-application-tracker' ); ?></option>
							<option value="low" <?php selected( $edit ? $item->priority : '', 'low' ); ?>><?php esc_html_e( 'Low', 'obydullah-job-application-tracker' ); ?></option>
						</select>
					</div>
				</div>

				<div style="display:flex; gap:10px; justify-content:flex-end; margin-top:20px;">
					<a href="<?php echo esc_url( $ojat_back_url ); ?>" class="ojat-btn ojat-btn-secondary">
						<?php esc_html_e( 'Cancel', 'obydullah-job-application-tracker' ); ?>
					</a>
					<button type="submit" class="ojat-btn ojat-btn-primary" id="ojat-save-btn">
						<?php echo $edit ? esc_html__( 'Update Application', 'obydullah-job-application-tracker' ) : esc_html__( 'Save Application', 'obydullah-job-application-tracker' ); ?>
					</button>
				</div>

			</form>
		</div>
	</div>

</div>
</div><!-- /.ojat-app -->
