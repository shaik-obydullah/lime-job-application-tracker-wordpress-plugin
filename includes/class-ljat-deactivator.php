<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LJAT_Deactivator {

	public static function deactivate() {
		flush_rewrite_rules();
	}
}
