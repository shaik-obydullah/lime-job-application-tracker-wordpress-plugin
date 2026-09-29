<?php
/**
 * Shared field definitions and value sanitizers.
 *
 * The status and priority vocabularies live here so the database layer, the
 * AJAX handlers, and both admin templates all read from the same list.
 *
 * @package obydullah-job-application-tracker
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The valid application statuses, in pipeline order.
 *
 * @return string[]
 */
function ojat_get_statuses() {
	return array( 'saved', 'applied', 'interview', 'offer', 'rejected', 'withdrawn' );
}

/**
 * The valid priorities, most urgent first.
 *
 * @return string[]
 */
function ojat_get_priorities() {
	return array( 'high', 'medium', 'low' );
}

/**
 * Translated labels for each status, keyed by status slug.
 *
 * @return array<string, string>
 */
function ojat_get_status_labels() {
	return array(
		'saved'     => __( 'Saved', 'obydullah-job-application-tracker' ),
		'applied'   => __( 'Applied', 'obydullah-job-application-tracker' ),
		'interview' => __( 'Interview', 'obydullah-job-application-tracker' ),
		'offer'     => __( 'Offer', 'obydullah-job-application-tracker' ),
		'rejected'  => __( 'Rejected', 'obydullah-job-application-tracker' ),
		'withdrawn' => __( 'Withdrawn', 'obydullah-job-application-tracker' ),
	);
}

/**
 * Translated labels for each priority, keyed by priority slug.
 *
 * @return array<string, string>
 */
function ojat_get_priority_labels() {
	return array(
		'high'   => __( 'High', 'obydullah-job-application-tracker' ),
		'medium' => __( 'Medium', 'obydullah-job-application-tracker' ),
		'low'    => __( 'Low', 'obydullah-job-application-tracker' ),
	);
}

/**
 * Dashboard tab slugs mapped to the status each one filters on.
 *
 * Every value is either a real status slug or an empty string meaning "no
 * filter". Using the status slugs directly keeps the tabs from drifting away
 * from the status vocabulary.
 *
 * @return array<string, string>
 */
function ojat_get_dashboard_tabs() {
	return array(
		'all'       => '',
		'saved'     => 'saved',
		'applied'   => 'applied',
		'interview' => 'interview',
		'offer'     => 'offer',
	);
}

/**
 * Constrain a value to the known status vocabulary.
 *
 * Anything unrecognized becomes an empty string, which callers read as "no
 * status filter" rather than as a value to store.
 *
 * @param mixed $value Raw status.
 * @return string
 */
function ojat_sanitize_status( $value ) {
	$value = sanitize_key( $value );

	return in_array( $value, ojat_get_statuses(), true ) ? $value : '';
}

/**
 * Constrain a value to the known priority vocabulary.
 *
 * @param mixed $value Raw priority.
 * @return string
 */
function ojat_sanitize_priority( $value ) {
	$value = sanitize_key( $value );

	return in_array( $value, ojat_get_priorities(), true ) ? $value : '';
}

/**
 * Validate a Y-m-d date string.
 *
 * Returns null for anything that is not a real calendar date. The null is
 * deliberate: MySQL coerces an empty string written to a DATE column into
 * '0000-00-00', which then formats as a date thousands of years in the past.
 * Writing NULL keeps "no date" distinguishable from a real one.
 *
 * @param mixed $value Raw date.
 * @return string|null
 */
function ojat_sanitize_date( $value ) {
	$value = sanitize_text_field( $value );

	if ( '' === $value ) {
		return null;
	}

	$date = DateTime::createFromFormat( 'Y-m-d', $value );

	// createFromFormat() tolerates overflow ("2026-13-45" rolls over), so
	// compare the round-tripped output against the input to confirm a real date.
	if ( ! $date || $value !== $date->format( 'Y-m-d' ) ) {
		return null;
	}

	return $value;
}
