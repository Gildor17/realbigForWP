<?php

if (!defined("ABSPATH")) {exit;}

try {
    if (!class_exists('RFWP_Caches')) {
        class RFWP_Caches {
            private static $pluginList;

            public static function cacheClear() {
                $allowCacheClear = get_option('rb_cacheClearAllow');
                if (!empty($allowCacheClear)&&$allowCacheClear=='enabled') {
                    self::$pluginList = self::getCachePluginList();
                    if (!empty(self::$pluginList)) {
                        foreach (self::$pluginList as $item) {
                            try {
                                self::$item();
                            } catch (Exception $ex) {
                                $messageFLog = 'Some error in RFWP_Caches->cacheClear : '.$ex->getMessage().';';
                                RFWP_Logs::saveLogs('errorsLog', $messageFLog);
                            } catch (Error $er) {
                                $messageFLog = 'Some error in RFWP_Caches->cacheClear : '.$er->getMessage().';';
                                RFWP_Logs::saveLogs('errorsLog', $messageFLog);
                            }
                        }
                        unset($item);
                    }
                }
            }

            private static function getCachePluginList() {
                $list = [
                    'autoptimizeCacheClear',
                    'wpSuperCacheCacheClear',
                    'wpFastestCacheCacheClear',
                    'w3TotalCacheCacheClear',
                    'liteSpeedCacheCacheClear',
                ];
                return $list;
            }

            /** Function for cache plugins */
            private static function autoptimizeCacheClear() {
                if (class_exists('autoptimizeCache')&&method_exists(autoptimizeCache::class, 'clearall')) {
                    autoptimizeCache::clearall();
                    if (empty(apply_filters('wp_doing_cron',defined('DOING_CRON')&&DOING_CRON))) {
                        header("Refresh:0");  # Refresh the page so that autoptimize can create new cache files and it does breaks the page after clearall.
                    }
                }
            }

            private static function wpSuperCacheCacheClear() {
                if (function_exists('wp_cache_clean_cache')) {
                    wp_cache_clear_cache();
                }
            }

            private static function wpFastestCacheCacheClear() {
                do_action('wpfc_delete_cache');
            }

            private static function w3TotalCacheCacheClear() {
                if (function_exists('w3tc_flush_all')) {
                    w3tc_flush_all();
                }
            }

            private static function liteSpeedCacheCacheClear() {
                do_action('litespeed_purge_all');
            }
            /** End of Function for cache plugins */
        }
    }
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
				'optionValue' => 'caches: '.$ex->getMessage()
			]);
		} else {
			$wpdb->update( $wpPrefix.'realbig_settings', [
				'optionName'  => 'deactError',
				'optionValue' => 'caches: '.$ex->getMessage()
			], ['optionName'  => 'deactError']);
		}
	} catch (Exception $exIex) {} catch (Error $erIex) {}

	deactivate_plugins(plugin_basename(__FILE__));
	?><div style="margin-left: 200px; border: 3px solid red"><?php echo $ex; ?></div><?php
}
catch (Error $ex) {
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
				'optionValue' => 'caches: '.$ex->getMessage()
			]);
		} else {
			$wpdb->update( $wpPrefix.'realbig_settings', [
				'optionName'  => 'deactError',
				'optionValue' => 'caches: '.$ex->getMessage()
			], ['optionName'  => 'deactError']);
		}
	} catch (Exception $exIex) {} catch (Error $erIex) {}

	deactivate_plugins(plugin_basename(__FILE__));
	?><div style="margin-left: 200px; border: 3px solid red"><?php echo $ex; ?></div><?php
}