<?php
/**
 * Created by PhpStorm.
 * User: furio
 * Date: 2018-11-06
 * Time: 11:10
 */

if (!defined("ABSPATH")) { exit;}

try {
	if(defined('WP_UNINSTALL_PLUGIN')) {
		global $wpdb;
        global $table_prefix;

		if (!empty(wp_next_scheduled('rb_cron_hook'))) {
			RFWP_cronAutoSyncDelete();
		}

		$wpPrefix = $table_prefix;
		if ( empty( $wpPrefix ) ) {
			$wpPrefix = $wpdb->base_prefix;
		}
		$GLOBALS['wpPrefix'] = $wpPrefix;

		$wpdb->query('DELETE FROM '.$wpPrefix.'posts WHERE post_type IN ("rb_block_mobile","rb_block_desktop","rb_block_mobile_new","rb_block_desktop_new","rb_inserting") AND post_author = 0');

		delete_option( 'realbig_status_gatherer' );
		delete_option( 'realbig_status_gatherer_version' );

		$tableName = $wpPrefix . 'realbig_plugin_settings';
		$wpdb->query("DROP TABLE IF EXISTS ". $tableName);
		$tableName = $wpPrefix . 'realbig_settings';
		$wpdb->query("DROP TABLE IF EXISTS ". $tableName);
	}
} catch (Exception $ex) {
	try {
		global $wpdb;

		$messageFLog = 'Deactivation error: '.$ex->getMessage().';';
        RFWP_Logs::saveLogs(RFWP_Logs::ERRORS_LOG, $messageFLog);

		if (!empty($GLOBALS['wpPrefix'])) {
			$wpPrefix = $GLOBALS['wpPrefix'];
		} else {
			global $table_prefix;
			$wpPrefix = $table_prefix;
		}

        RFWP_Utils::saveToRbSettings('uninstall: ' . $ex->getMessage(), 'deactError');
	} catch (Exception $exIex) { } catch (Error $erIex) { }

	deactivate_plugins(plugin_basename( __FILE__ ));
	?><div style="margin-left: 200px; border: 3px solid red"><?php echo $ex; ?></div><?php
} catch (Error $er) {
	try {
		global $wpdb;

		$messageFLog = 'Deactivation error: '.$er->getMessage().';';
        RFWP_Logs::saveLogs(RFWP_Logs::ERRORS_LOG, $messageFLog);

		if (!empty($GLOBALS['wpPrefix'])) {
			$wpPrefix = $GLOBALS['wpPrefix'];
		} else {
			global $table_prefix;
			$wpPrefix = $table_prefix;
		}

        RFWP_Utils::saveToRbSettings('uninstall: ' . $er->getMessage(), 'deactError');
	} catch (Exception $exIex) { } catch (Error $erIex) { }

	deactivate_plugins(plugin_basename( __FILE__ ));
	?><div style="margin-left: 200px; border: 3px solid red"><?php echo $er; ?></div><?php
}
