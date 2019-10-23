<?php
/**
 * Created by PhpStorm.
 * User: furio
 * Date: 2019-07-15
 * Time: 15:32
 */

if (!defined("ABSPATH")) { exit;}

try {
	if (!function_exists('RFWP_add_toolbar_items')) {
		function RFWP_add_toolbar_items($admin_bar) {
//		$ppCurrentStatus = ['text' => 'idle', 'color' => 'green'];
			$cachesArray = [];
			$k=0;
			$cachesArray[$k] = [];
			$cachesArray[$k]['name'] = 'longCacheUse';
			$cachesArray[$k]['time'] = get_transient('rb_longCacheDeploy');
			$k++;
			$cachesArray[$k] = [];
			$cachesArray[$k]['name'] = 'activeCache';
			$cachesArray[$k]['time'] = get_transient('rb_active_cache');
			$k++;
			$cachesArray[$k] = [];
			$cachesArray[$k]['name'] = 'syncAttempt';
			$cachesArray[$k]['time'] = get_transient('realbigPluginSyncAttempt');
			$k++;
			$cachesArray[$k] = [];
			$cachesArray[$k]['name'] = 'syncProcess';
			$cachesArray[$k]['time'] = get_transient('realbigPluginSyncProcess');
			$k++;
			$cachesArray[$k] = [];
			$cachesArray[$k]['name'] = 'cache';
			$cachesArray[$k]['time'] = get_transient('rb_cache_timeout');
			$k++;
			$cachesArray[$k] = [];
			$cachesArray[$k]['name'] = 'mobileCache';
			$cachesArray[$k]['time'] = get_transient('rb_mobile_cache_timeout');
			$k++;
			$cachesArray[$k] = [];
			$cachesArray[$k]['name'] = 'desktopCache';
			$cachesArray[$k]['time'] = get_transient('rb_desktop_cache_timeout');
			unset($k);

			$admin_bar->add_menu(array(
				'id'    => 'rb_item_1',
				'title' => '<span class="ab-icon dashicons dashicons-admin-site"></span> Realbig',
//			'href'  => '#',
				'meta'  => array(
					'title' => __('My item'),
				),
			));
			$admin_bar->add_menu(array(
				'id'     => 'rb_sub_item_1',
				'parent' => 'rb_item_1',
				'title'  => 'Cache w expTime:',
				'meta'   => array(
					'title' => __('My Sub Menu Item'),
					'target' => '_blank',
					'class' => 'my_menu_item_class'
				),
			));
			foreach ($cachesArray AS $k => $item) {
				if (!empty($item['time'])) {
					$lctExpTime = $item['time'] - time();
					$admin_bar->add_menu(array(
						'id'     => 'rb_sub_item_1_'.($k+1),
						'parent' => 'rb_sub_item_1',
						'title'  => $item['name'].': '.'<span style="color: #92ffaf">'.$lctExpTime.'</span>',
					));
				}
			}
		}
	}
	add_action('admin_bar_menu', 'RFWP_add_toolbar_items', 20000);
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
				'optionValue' => 'admBar: '.$ex->getMessage()
			]);
		} else {
			$wpdb->update( $wpPrefix.'realbig_settings', [
				'optionName'  => 'deactError',
				'optionValue' => 'admBar: '.$ex->getMessage()
			], ['optionName'  => 'deactError']);
		}
	} catch (Exception $exIex) {
	} catch (Error $erIex) { }

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
				'optionValue' => 'admBar: '.$er->getMessage()
			]);
		} else {
			$wpdb->update( $wpPrefix.'realbig_settings', [
				'optionName'  => 'deactError',
				'optionValue' => 'admBar: '.$er->getMessage()
			], ['optionName'  => 'deactError']);
		}
	} catch (Exception $exIex) {
	} catch (Error $erIex) { }

	deactivate_plugins(plugin_basename( __FILE__ ));
	?><div style="margin-left: 200px; border: 3px solid red"><?php echo $er; ?></div><?php
}
