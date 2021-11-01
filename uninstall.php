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

		if (!empty(wp_next_scheduled('bl_cron_hook'))) {
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
		global $rb_logFile;

		$messageFLog = 'Deactivation error: '.$ex->getMessage().';';
		error_log(PHP_EOL.current_time('mysql').': '.$messageFLog.PHP_EOL, 3, $rb_logFile);

		if (!empty($GLOBALS['wpPrefix'])) {
			$wpPrefix = $GLOBALS['wpPrefix'];
		} else {
			global $table_prefix;
			$wpPrefix = $table_prefix;
		}

		$errorInDB = $wpdb->query("SELECT * FROM ".$wpPrefix."realbig_settings WHERE optionName = 'deactError'");
		if (empty($errorInDB)) {
			$wpdb->insert($wpPrefix.'realbig_settings', [
				'optionName'  => 'deactError',
				'optionValue' => 'textEdit: '.$ex->getMessage()
			]);
		} else {
			$wpdb->update( $wpPrefix.'realbig_settings', [
				'optionName'  => 'deactError',
				'optionValue' => 'textEdit: '.$ex->getMessage()
			], ['optionName'  => 'deactError']);
		}
	} catch (Exception $exIex) { } catch (Error $erIex) { }

	deactivate_plugins(plugin_basename( __FILE__ ));
	?><div style="margin-left: 200px; border: 3px solid red"><?php echo $ex; ?></div><?php
} catch (Error $er) {
	try {
		global $wpdb;
		global $rb_logFile;

		$messageFLog = 'Deactivation error: '.$er->getMessage().';';
		error_log(PHP_EOL.current_time('mysql').': '.$messageFLog.PHP_EOL, 3, $rb_logFile);

		if (!empty($GLOBALS['wpPrefix'])) {
			$wpPrefix = $GLOBALS['wpPrefix'];
		} else {
			global $table_prefix;
			$wpPrefix = $table_prefix;
		}

		$errorInDB = $wpdb->query("SELECT * FROM ".$wpPrefix."realbig_settings WHERE optionName = 'deactError'");
		if (empty($errorInDB)) {
			$wpdb->insert($wpPrefix.'realbig_settings', [
				'optionName'  => 'deactError',
				'optionValue' => 'textEdit: '.$er->getMessage()
			]);
		} else {
			$wpdb->update( $wpPrefix.'realbig_settings', [
				'optionName'  => 'deactError',
				'optionValue' => 'textEdit: '.$er->getMessage()
			], ['optionName'  => 'deactError']);
		}
	} catch (Exception $exIex) { } catch (Error $erIex) { }

	deactivate_plugins(plugin_basename( __FILE__ ));
	?><div style="margin-left: 200px; border: 3px solid red"><?php echo $er; ?></div><?php
}
