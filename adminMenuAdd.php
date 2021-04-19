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
            $arrayForArray = [
                'longCacheUse'=>get_transient('rb_longCacheDeploy'),
                'activeCache' =>get_transient('rb_active_cache'),
                'syncAttempt' =>get_transient('realbigPluginSyncAttempt'),
                'syncProcess' =>get_transient('realbigPluginSyncProcess'),
                'cache'       =>get_transient('rb_cache_timeout'),
                'mobileCache' =>get_transient('rb_mobile_cache_timeout'),
                'desktopCache'=>get_transient('rb_desktop_cache_timeout'),
            ];
			$cachesArray = [];
			$cou = 0;
			foreach ($arrayForArray AS $k => $item) {
				$cachesArray[$cou] = [];
				$cachesArray[$cou]['name'] = $k;
				$cachesArray[$cou]['time'] = $item;
				$cou++;
            }
			unset($k,$item,$cou);

			$admin_bar->add_menu(array(
				'id'    => 'rb_item_1',
				'title' => '<span class="ab-icon dashicons dashicons-admin-site"></span> Realbig',
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

            $admin_bar->add_menu(array(
                'id'     => 'rb_sub_item_2',
                'parent' => 'rb_item_1',
                'title'  => 'Cache plugins status:',
                'meta'   => array(
                    'title' => __('My Sub Menu Item'),
                    'target' => '_blank',
                    'class' => 'my_menu_item_class'
                ),
            ));
			$cachePluginsStatus = RFWP_Caches::checkCachePlugins();
			if (!empty($cachePluginsStatus)) {
			    $cpCou = 0;
                foreach ($cachePluginsStatus AS $k => $item) {
                    $cpCou++;
                    $admin_bar->add_menu(array(
                        'id'     => 'rb_sub_item_2_'.$cpCou,
                        'parent' => 'rb_sub_item_2',
                        'title'  => $k.': '.$item,
                    ));
                }
                unset($k, $item, $cpCou);
            }
		}
	}
	add_action('admin_bar_menu', 'RFWP_add_toolbar_items', 20000);
}
catch (Exception $ex) {
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
}
catch (Error $er) {
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
