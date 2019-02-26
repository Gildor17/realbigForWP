<?php

if (!defined("ABSPATH")) { exit;}

try {
    $penyok_stoparik = 0;
    if (!empty(RFWP_wp_is_mobile())) {
        set_transient('rb_mobile_cache_timeout' , '', 60);
        $blockType = "rb_block_mobile";
    } else {
        set_transient('rb_desktop_cache_timeout', '', 60);
        $blockType = "rb_block_desktop";
    }
    $data = '';
    if (!empty($_POST['data'])) {
        $data = json_decode($_POST['data']);
    }
    foreach ($data->data AS $k => $item) {
        try {
            $postCheck = $wpdb->get_var($wpdb->prepare('SELECT id FROM '.$wpPrefix.'posts WHERE post_type = %s AND post_title = %s',[$blockType,$item->id]));
            if (!empty($postCheck)) {
                $postarr = ['ID' => $postCheck, 'post_content' => $item->code];
                $updateBlockResult = wp_update_post($postarr, true);
            } else {
                $postarr = [
                    'post_content' => $item->code,
                    'post_title'   => $item->id,
                    'post_status'  => "publish",
                    'post_type'    => $blockType,
                    'post_author'  => 0
                ];
                require_once(dirname(__FILE__ ) . "/../../../wp-includes/pluggable.php");
                $saveBlockResult = wp_insert_post($postarr, true);
            }
            $penyok_stoparik = 0;
        } catch (Exception $ex1) {continue;}
        catch   (Error $er1)     {continue;}
    }
    $penyok_stoparik = 0;
} catch (Exception $ex) {
	try {
		global $wpdb;
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