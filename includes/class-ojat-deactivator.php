<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class OJAT_Deactivator {

	public static function deactivate() {
		flush_rewrite_rules();
	}
}
