<?php

if (!defined("ABSPATH")) { exit;}

try {
    global $wpdb;
    global $wpPrefix;
	$dev_mode = $GLOBALS['dev_mode'];

	$returnData = [];
    $returnData['errors'] = [];
    $errorsGather = '';

    $checkCacheTimeoutMobile = get_transient('rb_mobile_cache_timeout');
    $checkCacheTimeoutTablet = get_transient('rb_tablet_cache_timeout');
    $checkCacheTimeoutDesktop = get_transient('rb_desktop_cache_timeout');

    if (!empty($checkCacheTimeoutMobile)&&!empty($checkCacheTimeoutTablet)&&!empty($checkCacheTimeoutDesktop)) {
        return true;
    }

	$stopIt = false;
    while (empty($stopIt)) {
	    $checkCacheTimeout = get_transient('rb_cache_timeout');
	    if (!empty($checkCacheTimeout)) {
		    return true;
	    }
	    $checkActiveCaching = get_transient('rb_active_cache');
	    if (!empty($checkActiveCaching)) {
		    sleep(6);
	    } else {
		    set_transient('rb_active_cache', time()+5, 5);
	        $stopIt = true;
        }
    }

    $data = '';
    if (!empty($_POST['data'])) {
        $data = $_POST['data'];

	    $data = preg_replace("~\\\'~", "'", $data);
	    $data = preg_replace('~\\\"~', '"', $data);

	    $savingResult = RFWP_savingCodeForCache($data);
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
				'optionValue' => 'realbigForWP: '.$ex->getMessage()
			]);
		} else {
			$wpdb->update( $wpPrefix.'realbig_settings', [
				'optionName'  => 'deactError',
				'optionValue' => 'realbigForWP: '.$ex->getMessage()
			], ['optionName'  => 'deactError']);
		}
	} catch (Exception $exIex) {
	} catch (Error $erIex) { }

//	include_once ( dirname(__FILE__)."/../../../wp-admin/includes/plugin.php" );
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
				'optionValue' => 'realbigForWP: '.$er->getMessage()
			]);
		} else {
			$wpdb->update( $wpPrefix.'realbig_settings', [
				'optionName'  => 'deactError',
				'optionValue' => 'realbigForWP: '.$er->getMessage()
			], ['optionName'  => 'deactError']);
		}
	} catch (Exception $exIex) {
	} catch (Error $erIex) { }

//	include_once ( dirname(__FILE__)."/../../../wp-admin/includes/plugin.php" );
	deactivate_plugins(plugin_basename( __FILE__ ));
	?><div style="margin-left: 200px; border: 3px solid red"><?php echo $er; ?></div><?php
}