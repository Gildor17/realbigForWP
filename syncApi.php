<?php

if (!function_exists('RFWP_syncNow')) {
	function RFWP_syncNow(WP_REST_Request $request) {
		$result = [];
		$refreshSync = delete_transient('realbigPluginSyncAttempt');
		$nextSchedulerCheck = wp_next_scheduled('rb_cron_hook');
		if (empty($nextSchedulerCheck)) {
			RFWP_cronAutoGatheringLaunch();
		}
		$result['result'] = 'timeout cleared';

		return $result;
	}
}

if (!function_exists('RFWP_syncNowPermission')) {
	function RFWP_syncNowPermission($request) {
		$justUsed = get_transient('rb_customSyncUsed');
		$expiration = 5;
		if (empty($justUsed)) {
			set_transient('rb_customSyncUsed', true, $expiration);
			return true;
		} else {
			return false;
		}
	}
}

add_action('rest_api_init', function () {
	register_rest_route('myplugin/v1', '/rb_4td6_resync/', array(
//		'methods'  => 'POST',
		'methods'  => WP_REST_Server::READABLE,
		'callback' => 'RFWP_syncNow',
		'permission_callback' => 'RFWP_syncNowPermission'
	));
});