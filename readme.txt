=== Obydullah Job Application Tracker ===
Contributors: obydullah
Tags: job application tracker, application tracker, job tracker, job search, career
Text Domain: obydullah-job-application-tracker
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 8.0
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Track and manage your job applications from the WordPress admin dashboard.

== Description ==

Obydullah Job Application Tracker turns your WordPress admin dashboard into a personal job-search command center. Log every application you send, follow up on interviews, track offers and rejections, and filter your pipeline in real time — all without leaving WordPress.

The plugin is built exclusively with native WordPress APIs (a custom database table, admin menus, and AJAX), has zero third-party dependencies, and ships with a modern, full-width, responsive interface.

= Features =

* Dashboard with live stats — total applications, interviews, offers, and rejections at a glance.
* Full application tracker — company, role, location, job URL, status, priority, dates, salary, contact info, and notes.
* Real-time filtering — search by company/role/location and filter by status and priority with a dedicated Filter button.
* AJAX pagination — browse your pipeline without full page reloads.
* Quick actions — view details in a modal, edit, and delete with confirmation.
* Status pipeline — Saved, Applied, Interview, Offer, Rejected, Withdrawn.
* Priority flags — High / Medium / Low.
* Full-width responsive layout — works great on any screen size.
* Secure by default — nonce-verified AJAX, capability checks, prepared SQL statements, and sanitized inputs.

== Installation ==

1. Upload the `obydullah-job-application-tracker` folder to the `/wp-content/plugins/` directory, or install the plugin through the WordPress **Plugins → Add New** screen.
2. Activate the plugin through the **Plugins** screen in WordPress.
3. A **Job Tracker** menu item appears in the admin sidebar — start adding your applications.

The plugin creates its own table (`wp_ojat_applications`) on activation. No configuration is required.

== Frequently Asked Questions ==

= Do I need a developer to set this up? =

No. It is a standard WordPress plugin — activate it and start tracking.

= Is my data safe? =

Yes. All AJAX requests are protected with nonces, all inputs are sanitized, all output is escaped, and every database query uses prepared statements.

== Screenshots ==

1. Dashboard — KPI stat cards, a searchable/filterable table, inline actions, and AJAX pagination.
2. Add / Edit Application — a clean two-column form for logging a new application or editing an existing one.
3. Application Details — the modal detail view showing the full record for any application.

== Changelog ==

= 1.0.0 =
* Initial release.