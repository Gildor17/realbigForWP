<?php
/**
 * Created by PhpStorm.
 * User: furio
 * Date: 2018-11-06
 * Time: 11:10
 */

if (!defined("ABSPATH")) { exit;}

include_once ( dirname(__FILE__)."/../../../wp-admin/includes/plugin.php" );
include_once ( dirname(__FILE__)."/../../../wp-admin/includes/upgrade.php" );
include_once ( dirname(__FILE__).'/../../../wp-includes/wp-db.php');

if(defined('WP_UNINSTALL_PLUGIN') ) {
	global $wpdb;

	if (!empty(wp_next_scheduled('bl_cron_hook'))) {
		RFWP_cronAutoSyncDelete();
	}

	$wpPrefix = $table_prefix;
	if ( empty( $wpPrefix ) ) {
		$wpPrefix = $wpdb->base_prefix;
	}
	$GLOBALS['wpPrefix'] = $wpPrefix;

	delete_option( 'realbig_status_gatherer' );
	delete_option( 'realbig_status_gatherer_version' );

	$tableName = $wpPrefix . 'realbig_plugin_settings';
	$wpdb->query("DROP TABLE IF EXISTS ". $tableName);
	$tableName = $wpPrefix . 'realbig_settings';
	$wpdb->query("DROP TABLE IF EXISTS ". $tableName);
}