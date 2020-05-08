<?php

if (!defined("ABSPATH")) { exit;}

//require_once (dirname(__FILE__)."/../../../wp-admin/includes/plugin.php" );
require_once (ABSPATH."/wp-admin/includes/plugin.php");

/** Rename plugin folder */
try {
	//if (empty(apply_filters('wp_doing_cron',defined('DOING_CRON')&&DOING_CRON))&&empty(apply_filters('wp_doing_ajax',defined('DOING_AJAX')&&DOING_AJAX))&&!empty($_POST['folderRename'])) {
//	if (empty(apply_filters('wp_doing_cron',defined('DOING_CRON')&&DOING_CRON))&&empty(apply_filters('wp_doing_ajax',defined('DOING_AJAX')&&DOING_AJAX))) {
	$checkDirName = basename(dirname(__FILE__));
	$pluginDirname = dirname(__DIR__);
	$curFileName = basename(plugin_basename( __FILE__ ));
	$plBaseName = plugin_basename(__FILE__);
	$renameResult = false;
	$activatablePluginCheck = false;
	if (!empty($_GET)&&!empty($_GET['action'])&&!empty($_GET['plugin'])&&in_array($_GET['action'], ['activate','deactivate'])) {
		$activatablePlugin = strtolower($_GET['plugin']);
	}

	if (!empty($pluginDirname)&&!empty($curFileName)&&!empty($checkDirName)&&strpos(strtolower($checkDirName),'realbigforwp')!==false) {
		if (!empty($activatablePlugin)) {
			$activatablePluginCheck = strpos($activatablePlugin, strtolower($checkDirName));
		}
		if ($activatablePluginCheck===false) {
			require_once (ABSPATH."/wp-includes/pluggable.php");
			$rndIntval = rand(1000,9999);
			$newDirName = 'rb-'.$rndIntval.'-git';
			deactivate_plugins($plBaseName);
			$renameResult = rename(dirname(__FILE__),$pluginDirname.'/'.$newDirName);
			if (!empty($renameResult)) {
				activate_plugin($newDirName.'/'.$curFileName, admin_url('plugins.php'));
			} else {
				activate_plugin($plBaseName, admin_url('plugins.php'));
			}
		}
	}
//	}
} catch (Exception $ex1) {
//	$messageFLog = 'rename folder error : '.$ex1->getMessage();
//	error_log(PHP_EOL.current_time('mysql').': '.$messageFLog.PHP_EOL, 3, $rb_logFile);
} catch (Error $er1) {
//	$messageFLog = 'rename folder error : '.$er1->getMessage();
//	error_log(PHP_EOL.current_time('mysql').': '.$messageFLog.PHP_EOL, 3, $rb_logFile);
}
/** End of rename plugin folder */

include_once (dirname(__FILE__)."/update.php");
include_once (dirname(__FILE__)."/synchronising.php");
include_once (dirname(__FILE__)."/textEditing.php");
//include_once (dirname(__FILE__).'/rssGenerator.php');
include_once (dirname(__FILE__)."/syncApi.php");

/*
Plugin name:  Realbig Media Git version
Description:  Плагин для монетизации от RealBig.media
Version:      0.3.2
Author:       Realbig Team
Author URI:   https://realbig.media
License:      GPLv2 or later
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
*/

try {
	/** **************************************************************************************************************** **/
	global $wpdb;
	global $table_prefix;
	if (!isset($GLOBALS['dev_mode'])) {
//        $devMode = true;
		$devMode = false;
		$GLOBALS['dev_mode'] = $devMode;
    }

	$rb_logFile = plugin_dir_path(__FILE__).'wpPluginErrors.log';
	global $rb_logFile;
	$rb_processlogFile = plugin_dir_path(__FILE__).'workProcess.log';
	global $rb_processlogFile;
	$rb_testCheckLog = plugin_dir_path(__FILE__).'testCheckLog.log';
	global $rb_testCheckLog;
    if (!is_admin()&&empty(apply_filters('wp_doing_cron',defined('DOING_CRON')&&DOING_CRON))&&empty(apply_filters('wp_doing_ajax',defined('DOING_AJAX')&&DOING_AJAX))) {
	    RFWP_WorkProgressLog(false,'begin of process');
    }

	if (empty(apply_filters('wp_doing_cron', defined('DOING_CRON')&&DOING_CRON))) {
		require_once (ABSPATH."/wp-includes/pluggable.php");
		$curUserCan = current_user_can('activate_plugins');
	}

	if (!empty($GLOBALS['dev_mode'])) {
		if (empty($GLOBALS['rb_admin_menu_loaded'])) {
			require_once(dirname(__FILE__)."/adminMenuAdd.php");
			$GLOBALS['rb_admin_menu_loaded'] = true;
		}
	}

	if (!isset($GLOBALS['wpPrefix'])) {
//		if (!is_admin()&&empty(apply_filters('wp_doing_cron',defined('DOING_CRON')&&DOING_CRON))&&empty(apply_filters('wp_doing_ajax',defined('DOING_AJAX')&&DOING_AJAX))) {
//			RFWP_WorkProgressLog(false,'gather table prefix');
//		}
		$wpPrefix = $table_prefix;
		if (empty($wpPrefix)) {
			$wpPrefix = $wpdb->base_prefix;
		}
		$GLOBALS['wpPrefix'] = $wpPrefix;
	}

    if (!isset($GLOBALS['excludedPagesChecked'])) {
	    $GLOBALS['excludedPagesChecked'] = false;
    }

    if (empty($GLOBALS['workProgressLogs'])) {
	    $workProgressLogs = $wpdb->get_var($wpdb->prepare('SELECT optionValue FROM '.$wpPrefix.'realbig_settings WHERE optionName = %s', ["work_process_status"]));
	    if (!empty($workProgressLogs)&&$workProgressLogs=='enabled') {
		    $GLOBALS['workProgressLogs'] = 'enabled';
        } else {
		    $GLOBALS['workProgressLogs'] = 'disabled';
	    }
    }
	/***************** Test zone ******************************************************************************************/
	/** Kill rb connection emulation */
    // 1 - ok connection; 2 - error connection;
    if (!empty($GLOBALS['dev_mode'])) {
	    $kill_rb_db = $wpdb->get_results('SELECT id,optionValue FROM '.$wpPrefix.'realbig_settings WHERE optionName = "kill_rb"', ARRAY_A);

	    if (empty(apply_filters('wp_doing_cron', defined('DOING_CRON') && DOING_CRON))&&!empty(is_admin())) {
		    if (!empty($curUserCan)&&!empty($_POST['saveTokenButton'])) {
			    if (!empty($_POST['kill_rb'])) {
				    $saveVal = 2;
			    } else {
				    $saveVal = 1;
			    }
			    if (!empty($kill_rb_db)&&count($kill_rb_db) > 0) {
				    $wpdb->update($wpPrefix.'realbig_settings',['optionValue'=>$saveVal],['optionName'=>'kill_rb']);
			    } else {
				    $wpdb->insert($wpPrefix.'realbig_settings',['optionValue'=>$saveVal,'optionName'=>'kill_rb']);
			    }
			    $kill_rb_db = $saveVal;
		    } else {
			    if (!empty($kill_rb_db)&&count($kill_rb_db) > 0) {
				    $kill_rb_db = $kill_rb_db[0]['optionValue'];
			    } else {
				    $kill_rb_db = 1;
			    }
		    }
	    } else {
		    if (!empty($kill_rb_db)&&count($kill_rb_db) > 0) {
			    $kill_rb_db = $kill_rb_db[0]['optionValue'];
		    } else {
			    $kill_rb_db = 1;
		    }
	    }
	    $kill_rb = $kill_rb_db;
    }

    if (!isset($kill_rb)) {
	    $kill_rb = 0;
    }

	$GLOBALS['kill_rb'] = $kill_rb;
	/** End of kill rb connection emulation */
	/** Cron check */
//	if (!empty(apply_filters('wp_doing_cron',defined('DOING_CRON')&&DOING_CRON))&&empty(apply_filters('wp_doing_ajax',defined('DOING_AJAX')&&DOING_AJAX))) {
//		RFWP_cronCheckLog('cron passed');
//    }
	/** End of cron check */
	/***/
	if (!empty($devMode)) {
		if (!empty($_POST['checkIp'])&&is_admin()&&empty(apply_filters('wp_doing_cron', defined('DOING_CRON')&&DOING_CRON))) {
			$thisUrl = 'http://ifconfig.co/ip';
			$checkIpLogFile = plugin_dir_path(__FILE__).'checkIpLog.log';
//		    $thisResultSaved = get_option('checkIpResult');
//		    if (empty($thisResultSaved)) {
//			    $thisResult = wp_remote_get($thisUrl);
//			    if (!empty($thisResult)&&!empty($thisResult['body'])) {
//				    error_log(PHP_EOL.current_time('mysql').':'.$thisResult['body'].PHP_EOL, 3, $checkIpLogFile);
//			    }
//		    }
			$curl = curl_init();
			curl_setopt($curl,CURLOPT_URL, $thisUrl);
			curl_setopt($curl,CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($curl,CURLOPT_IPRESOLVE,CURL_IPRESOLVE_V4);
			$curlResult = curl_exec($curl);
			if (!empty($curlResult)) {
			    global $curlResult;
				error_log(PHP_EOL.current_time('mysql').':'.PHP_EOL.$curlResult.PHP_EOL, 3, $checkIpLogFile);
			}
			curl_close($curl);
		}

		if (!empty($curUserCan)) {
			$testServerName = 'placeholder';
			$testHttpOrigin = 'placeholder';
			$testHttpHost = 'placeholder';

			if (!empty($_SERVER['SERVER_NAME'])) {
				$testServerName = $_SERVER['SERVER_NAME'];
			}
			if (!empty($_SERVER['HTTP_ORIGIN'])) {
				$testHttpOrigin = $_SERVER['HTTP_ORIGIN'];
			}
			if (!empty($_SERVER['HTTP_HOST'])) {
				$testHttpHost = $_SERVER['HTTP_HOST'];
			}
			/*?><script>console.log('SERVER_NAME:<?php echo $testServerName.';';?>');console.log('HTTP_ORIGIN:<?php echo $testHttpOrigin.';';?>');console.log('HTTP_HOST:<?php echo $testHttpHost.';';?>')</script><?php*/
		}
	}

	/***/
    /** Rss test */
	if (!function_exists('rssTestTemplate')) {
		function rssTestTemplate() {
//			include_once (dirname(__FILE__).'/rssGenerator.php');
			$posts = [];
			$rb_rssFeedUrls = [];
			$rssPartsCount = 1;
			$rssOptions = RFWP_rssOptionsGet();
			$postTypes = $rssOptions['typesPost'];
			$feedTrashName = 'rb_turbo_trash_rss';
			add_feed($feedTrashName, 'RFWP_rssCreate');
			$feedName = 'rb_turbo_rss';
			add_feed($feedName, 'RFWP_rssCreate');
			array_push($rb_rssFeedUrls, $feedName);
			if (!empty($postTypes)) {
				$tax_query = RFWP_rss_taxonomy_get($rssOptions);
			    $postTypes = explode(';', $postTypes);
				$posts = get_posts([
					'numberposts' => $rssOptions['pagesCount'],
                    'post_type' => $postTypes,
					'tax_query' => $tax_query,
					'fields' => ['ID'],
				]);
            }

			if (!empty($posts)) {
				$rssDividedPosts = RFWP_rssDivine($posts, $rssOptions);
				$GLOBALS['rb_rssDivideOptions'] = [];
				$GLOBALS['rb_rssDivideOptions']['posts'] = $rssDividedPosts;
				$GLOBALS['rb_rssDivideOptions']['iteration'] = 0;
				$rssOptions['rssPartsSeparated'] = intval($rssOptions['rssPartsSeparated']);
				if ($rssOptions['rssPartsSeparated']  < 1) {
					$rssOptions['rssPartsSeparated'] = 1;
				}
				if (!empty($rssOptions['divide'])&&!($rssOptions['rssPartsSeparated'] >= count($posts))) {
					$rssPartsCount = count($posts)/$rssOptions['rssPartsSeparated'];
					$rssPartsCount = ceil($rssPartsCount);
					$feed = [];
					for ($cou = 0; $cou < $rssPartsCount; $cou++) {
						if ($cou > 0) {
							$feedName = 'rb_turbo_rss';
							if (get_option('permalink_structure')) {
								$feedPage = '/?paged='.($cou+1);
							} else {
								$feedPage = '&paged='.($cou+1);
							}
							$feedName = $feedName.$feedPage;
							add_feed($feedName, 'RFWP_rssCreate');
							array_push($rb_rssFeedUrls, $feedName);
						}
					}
				}
			}
			if (!empty($rb_rssFeedUrls)) {
				$GLOBALS['rb_rssFeedUrls'] = $rb_rssFeedUrls;
			}

//			global $wp_rewrite;
//			$wp_rewrite->flush_rules(false);
		}
    }
	if (!empty($devMode)) {
		add_action('init', 'rssTestTemplate');
    }
	/** End of Rss test */
	/***************** End of test zone ***********************************************************************************/
	/***************** Cached AD blocks saving ***************************************************************************************/
	if (!function_exists('saveAdBlocks')) {
		function saveAdBlocks($tunnelData) {
			if (!empty($_POST['type'])&&$_POST['type']=='blocksGethering') {
				include_once (dirname(__FILE__).'/connectTestFile.php');
			}
			return $tunnelData;
		}
    }

	if (!function_exists('setLongCache')) {
		function setLongCache($tunnelData) {
			if (!empty($_POST['type'])&&$_POST['type']=='longCatching') {
				set_transient('rb_longCacheDeploy', time()+300, 300);
			}
			return $tunnelData;
		}
    }
	/***************** End of cached AD blocks saving *********************************************************************************/
	$tableForCurrentPluginChecker = $wpdb->get_var('SHOW TABLES LIKE "' . $wpPrefix . 'realbig_plugin_settings"');   //settings for block table checking
	$tableForToken                = $wpdb->get_var('SHOW TABLES LIKE "' . $wpPrefix . 'realbig_settings"');      //settings for token and other
	if (!is_admin()&&empty(apply_filters('wp_doing_cron',defined('DOING_CRON')&&DOING_CRON))&&empty(apply_filters('wp_doing_ajax',defined('DOING_AJAX')&&DOING_AJAX))) {
		RFWP_WorkProgressLog(false,'tables check');
	}

	if (empty(apply_filters('wp_doing_cron', defined('DOING_CRON') && DOING_CRON))) {
	    if ((!empty($curUserCan)&&!empty($_POST['statusRefresher']))||empty($tableForToken)||empty($tableForCurrentPluginChecker)) {
	        $wpdb->query('DELETE FROM '.$wpPrefix.'posts WHERE post_type IN ("rb_block_mobile","rb_block_desktop","rb_block_mobile_new","rb_block_desktop_new") AND post_author = 0');
	        delete_transient('rb_cache_timeout');
	        delete_transient('rb_longCacheDeploy');
	        delete_transient('rb_mobile_cache_timeout');
	        delete_transient('rb_desktop_cache_timeout');
		    delete_option('realbig_status_gatherer_version');

		    if (empty($GLOBALS['wp_rewrite'])) {
			    $GLOBALS['wp_rewrite'] = new WP_Rewrite();
		    }
		    $oldShortcodes = get_posts(['post_type' => 'rb_shortcodes','numberposts' => 100]);
		    if (!empty($oldShortcodes)) {
			    foreach ($oldShortcodes AS $k => $item) {
				    wp_delete_post($item->ID);
			    }
			    unset($k, $item);
		    }

		    $messageFLog = 'clear cached ads';
		    error_log(PHP_EOL.current_time('mysql').': '.$messageFLog.PHP_EOL, 3, $rb_logFile);
	    }
    }

    if (!isset($GLOBALS['realbigForWP_version'])) {
	    $pluginData = get_plugin_data(__FILE__);
	    if (!empty($pluginData['Version'])) {
		    $GLOBALS['realbigForWP_version'] = $pluginData['Version'];
	    } else {
		    $GLOBALS['realbigForWP_version'] = '0.3.0';
	    }
    }

    if (!isset($lastSuccessVersionGatherer)||!isset($statusGatherer)) {
	    if (!is_admin()&&empty(apply_filters('wp_doing_cron',defined('DOING_CRON')&&DOING_CRON))&&empty(apply_filters('wp_doing_ajax',defined('DOING_AJAX')&&DOING_AJAX))) {
		    RFWP_WorkProgressLog(false,'gather some statuses from options');
	    }
	    $lastSuccessVersionGatherer = get_option('realbig_status_gatherer_version');
	    $statusGatherer             = RFWP_statusGathererConstructor(true);
    }
	/***************** updater code ***************************************************************************************/
	require 'plugin-update-checker/plugin-update-checker.php';
	$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
		'https://github.com/Gildor17/realbigForWP',
		__FILE__,
		'realbigForWP'
	);
	/****************** end of updater code *******************************************************************************/
	/********** checking and creating tables ******************************************************************************/
	if ((!empty($lastSuccessVersionGatherer)&&$lastSuccessVersionGatherer != $GLOBALS['realbigForWP_version'])||empty($lastSuccessVersionGatherer)) {
		$wpdb->query('DELETE FROM '.$wpPrefix.'posts WHERE post_type IN ("rb_block_mobile","rb_block_desktop","rb_block_mobile_new","rb_block_desktop_new") AND post_author = 0');
		delete_transient('rb_cache_timeout');
		delete_transient('rb_longCacheDeploy');
		delete_transient('rb_mobile_cache_timeout');
		delete_transient('rb_desktop_cache_timeout');

		$messageFLog = 'clear cached ads';
        error_log(PHP_EOL.current_time('mysql').': '.$messageFLog.PHP_EOL, 3, $rb_logFile);

		if (!is_admin()&&empty(apply_filters('wp_doing_cron',defined('DOING_CRON')&&DOING_CRON))&&empty(apply_filters('wp_doing_ajax',defined('DOING_AJAX')&&DOING_AJAX))) {
			RFWP_WorkProgressLog(false,'old cache clean');
		}
	}

	if ($statusGatherer['realbig_plugin_settings_table'] == false || $statusGatherer['realbig_settings_table'] == false || $lastSuccessVersionGatherer != $GLOBALS['realbigForWP_version']) {
		if (!is_admin()&&empty(apply_filters('wp_doing_cron',defined('DOING_CRON')&&DOING_CRON))&&empty(apply_filters('wp_doing_ajax',defined('DOING_AJAX')&&DOING_AJAX))) {
			RFWP_WorkProgressLog(false,'create tables begin');
		}
		$statusGatherer = RFWP_dbTablesCreateFunction($tableForCurrentPluginChecker, $tableForToken, $wpPrefix, $statusGatherer);
		if (!is_admin()&&empty(apply_filters('wp_doing_cron',defined('DOING_CRON')&&DOING_CRON))&&empty(apply_filters('wp_doing_ajax',defined('DOING_AJAX')&&DOING_AJAX))) {
			RFWP_WorkProgressLog(false,'create tables end');
		}
	}
	if ($statusGatherer['realbig_plugin_settings_table'] == true && ($statusGatherer['realbig_plugin_settings_columns'] == false || $lastSuccessVersionGatherer != $GLOBALS['realbigForWP_version'])) {
		$colCheck = $wpdb->get_col('SHOW COLUMNS FROM '.$wpPrefix.'realbig_plugin_settings');
		if (!empty($colCheck)) {
			$statusGatherer = RFWP_wpRealbigPluginSettingsColomnUpdateFunction($wpPrefix, $colCheck, $statusGatherer);
			if (!is_admin()&&empty(apply_filters('wp_doing_cron',defined('DOING_CRON')&&DOING_CRON))&&empty(apply_filters('wp_doing_ajax',defined('DOING_AJAX')&&DOING_AJAX))) {
				RFWP_WorkProgressLog(false,'db table column update');
			}
		} else {
			$statusGatherer['realbig_plugin_settings_columns'] = false;
		}
	}
	/********** end of checking and creating tables ***********************************************************************/
	/********** token gathering and adding "timeUpdate" field in wp_realbig_settings **************************************/
	if (empty($GLOBALS['token'])||(!empty($GLOBALS['token'])&&$GLOBALS['token']=='no token')) {
		RFWP_tokenChecking($wpPrefix);
		if (!is_admin()&&empty(apply_filters('wp_doing_cron',defined('DOING_CRON')&&DOING_CRON))&&empty(apply_filters('wp_doing_ajax',defined('DOING_AJAX')&&DOING_AJAX))) {
			RFWP_WorkProgressLog(false,'token checking');
		}
	}

	$unmarkSuccessfulUpdate      = $wpdb->get_var('SELECT optionValue FROM '.$wpPrefix.'realbig_settings WHERE optionName = "successUpdateMark"');
	$jsAutoSynchronizationStatus = $wpdb->get_var('SELECT optionValue FROM '.$wpPrefix.'realbig_settings WHERE optionName = "jsAutoSyncFails"');

	if ($statusGatherer['realbig_plugin_settings_table'] == true && ($statusGatherer['element_column_values'] == false || $lastSuccessVersionGatherer != $GLOBALS['realbigForWP_version'])) {
		/** enumUpdate */
		$statusGatherer = RFWP_updateElementEnumValuesFunction($wpPrefix, $statusGatherer);
		if (!is_admin()&&empty(apply_filters('wp_doing_cron',defined('DOING_CRON')&&DOING_CRON))&&empty(apply_filters('wp_doing_ajax',defined('DOING_AJAX')&&DOING_AJAX))) {
			RFWP_WorkProgressLog(false,'enum values updated');
		}
		/** enumUpdateEnd */
	}
	if (!empty($statusGatherer)) {
		if (!in_array(false, $statusGatherer)) {
			if (!empty($lastSuccessVersionGatherer)) {
				update_option('realbig_status_gatherer_version', $GLOBALS['realbigForWP_version'], 'no');
			} else {
				add_option('realbig_status_gatherer_version', $GLOBALS['realbigForWP_version'], '', 'no');
			}
		}
		$statusGathererJson = json_encode($statusGatherer);
		if (!empty($statusGatherer['update_status_gatherer']) && $statusGatherer['update_status_gatherer'] == true) {
			update_option('realbig_status_gatherer', $statusGathererJson, 'no');
		} else {
			add_option('realbig_status_gatherer', $statusGathererJson, '', 'no');
		}
	}
	/********** end of token gathering and adding "timeUpdate" field in wp_realbig_settings *******************************/
	/********** checking requested page for excluding *********************************************************************/
    try {
        if (empty($GLOBALS['excludedPagesChecked'])&&empty(apply_filters('wp_doing_cron',defined('DOING_CRON')&&DOING_CRON))) {
	        if (!is_admin()&&empty(apply_filters('wp_doing_cron',defined('DOING_CRON')&&DOING_CRON))&&empty(apply_filters('wp_doing_ajax',defined('DOING_AJAX')&&DOING_AJAX))) {
		        RFWP_WorkProgressLog(false,'excluded page check begin');
	        }
	        $excludedPage = false;
            $mainPageStatus = 0;

	        $usedUrl = '';
	        $usedUrl2 = '';
	        if (!empty($_SERVER["REDIRECT_URL"])) {
                $usedUrl = $_SERVER["REDIRECT_URL"];
            }
            if (!empty($_SERVER["REQUEST_URI"])) {
                $usedUrl2 = $_SERVER["REQUEST_URI"];
            }
            $usedUrl1[0] = $_SERVER["HTTP_HOST"].$usedUrl;
            $usedUrl1[1] = $_SERVER["HTTP_HOST"].$usedUrl2;

            /** Test zone *********/
            /** End of test zone **/

            if (is_admin()) {
                $excludedPage = true;
            } elseif (!empty($usedUrl)||!empty($usedUrl2)) {
                $pageChecksDb = $wpdb->get_results($wpdb->prepare("SELECT optionValue, optionName FROM ".$wpPrefix."realbig_settings WHERE optionName IN (%s,%s,%s)", ['excludedMainPage','excludedPages','excludedPageTypes']), ARRAY_A);
                $pageChecks = [];
                foreach ($pageChecksDb AS $k => $item) {
                    $pageChecks[$item['optionName']] = $item['optionValue'];
                }
                $GLOBALS['pageChecks'] = $pageChecks;

                $homeStatus = false;
	            $getHomeUrl = get_home_url();
	            $getHomeUrl = preg_replace('~^http[s]*?\:\/\/~', '', $getHomeUrl);

	            preg_match_all("~(\/|\\\)([^\/^\\\]+)~", $getHomeUrl, $m);

                foreach ($usedUrl1 AS $usedUrl) {
                    if (!empty($usedUrl)&&!empty($m)) {
                        if ($usedUrl=="/"||$usedUrl==$getHomeUrl."/") {
                            $homeStatus = true;
                            break;
                        } else {
                            foreach ($m[0] AS $item) {
                                if ($usedUrl==$item."/") {
                                    $homeStatus = true;
                                    break;
                                }
                            }
                        }
                    }
                }

                if ($homeStatus==true) {
                    if (isset($pageChecks['excludedMainPage'])) {
                        if ($pageChecks['excludedMainPage'] == 1) {
                            $mainPageStatus = 1;
                        } elseif ($pageChecks['excludedMainPage'] == 0) {
                            $mainPageStatus = 2;
                        }
                    }
                }

                if ($mainPageStatus == 1) {
                    $excludedPage = true;
                } elseif ($mainPageStatus == 0) {
                    if (!empty($pageChecks['excludedPages'])) {
                        $excludedDelimiter = 0;
                        $maxCountDelimiter = 0;
                        $excludedPagesCheckArray[1] = explode(",", $pageChecks['excludedPages']);
                        $excludedPagesCheckArray[2] = explode("\n", $pageChecks['excludedPages']);
                        $excludedPagesCheckArray[3] = explode(";", $pageChecks['excludedPages']);
                        $excludedPagesCheckArray[4] = explode(" ", $pageChecks['excludedPages']);

                        foreach ($excludedPagesCheckArray AS $k => $item) {
                            if (count($item) > $maxCountDelimiter) {
                                $maxCountDelimiter = count($item);
                                $excludedDelimiter = $k;
                            }
                        }
                        if ($excludedDelimiter > 0) {
                            $excludedPagesCheckArray = $excludedPagesCheckArray[$excludedDelimiter];
                        } else {
                            $excludedPagesCheckArray = $pageChecks['excludedPages'];
                        }

                        if (!empty($excludedPagesCheckArray)) {
                            foreach ($excludedPagesCheckArray AS $item) {
                                $item = trim($item);
                                $item1 = preg_replace('~\\\~','\/', $item);
                                $item2 = preg_replace('~\/~','\\', $item);

                                if (!empty($item)) {
                                    $m = -1;
                                    foreach ($usedUrl1 AS $usedUrl) {
                                        $m1 = strpos($usedUrl, $item1);
                                        if (is_integer($m1)&&$m1 > -1) {
                                            $excludedPage = true;
                                            break;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            $GLOBALS['excludedPagesChecked'] = true;
	        if (!is_admin()&&empty(apply_filters('wp_doing_cron',defined('DOING_CRON')&&DOING_CRON))&&empty(apply_filters('wp_doing_ajax',defined('DOING_AJAX')&&DOING_AJAX))) {
		        RFWP_WorkProgressLog(false,'exc excluded check end');
	        }
        }
    } catch (Exception $excludedE) {
        $excludedPage = false;
    }
//    if (!is_admin()&&!empty($curUserCan)&&isset($excludedPage)) {
//        if ($excludedPage==true) {
//	        $excludedPageFprint = 'true';
//        } elseif ($excludedPage==false) {
//	        $excludedPageFprint = 'false';
//        }
/*        <script>console.log('Excluded page: <?php echo $excludedPage; ?>;\n');</script><?php*/
//    }
	/********** end of checking requested page for excluding **************************************************************/
	/********** new working system ****************************************************************************************/
	if (!function_exists('RFWP_blocks_in_head_add')) {
		function RFWP_blocks_in_head_add() {
			global $rb_logFile;
			try {
				if (!is_admin()&&empty(apply_filters('wp_doing_cron',defined('DOING_CRON')&&DOING_CRON))&&empty(apply_filters('wp_doing_ajax',defined('DOING_AJAX')&&DOING_AJAX))) {
					RFWP_WorkProgressLog(false,'blocks_in_head_add begin');
				}
				$content = RFWP_shortCodesAdd('');
				$fromDb = RFWP_gatherBlocksFromDb();
				$GLOBALS['fromDb'] = $fromDb;
				$contentBlocks = RFWP_creatingJavascriptParserForContentFunction_test($fromDb['adBlocks'], $fromDb['excIdClass'], $fromDb['blockDuplicate']);
				$content = $contentBlocks['before'].$content.$contentBlocks['after'];
				if (!is_admin()&&empty(apply_filters('wp_doing_cron',defined('DOING_CRON')&&DOING_CRON))&&empty(apply_filters('wp_doing_ajax',defined('DOING_AJAX')&&DOING_AJAX))) {
					RFWP_WorkProgressLog(false,'blocks_in_head_add end');
				}

				?><?php echo $content ?><?php
			} catch (Exception $ex) {
				$messageFLog = 'RFWP_blocks_in_head_add errors: '.$ex->getMessage().';';
				error_log(PHP_EOL.current_time('mysql').': '.$messageFLog.PHP_EOL, 3, $rb_logFile);
			} catch (Error $er) {
				$messageFLog = 'RFWP_blocks_in_head_add errors: '.$er->getMessage().';';
				error_log(PHP_EOL.current_time('mysql').': '.$messageFLog.PHP_EOL, 3, $rb_logFile);
			}
		}
	}

	if (!function_exists('RFWP_launch_without_content')) {
		function RFWP_launch_without_content() {
			global $rb_logFile;
			try {
				if (!is_admin()&&empty(apply_filters('wp_doing_cron',defined('DOING_CRON')&&DOING_CRON))&&empty(apply_filters('wp_doing_ajax',defined('DOING_AJAX')&&DOING_AJAX))) {
					RFWP_WorkProgressLog(false,'launch_without_content begin');
				}
				$content = '';
				$content = RFWP_launch_without_content_function($content);

				?><?php echo $content ?><?php
			} catch (Exception $ex) {
				$messageFLog = 'RFWP_launch_without_content errors: '.$ex->getMessage().';';
				error_log(PHP_EOL.current_time('mysql').': '.$messageFLog.PHP_EOL, 3, $rb_logFile);
			} catch (Error $er) {
				$messageFLog = 'RFWP_launch_without_content errors: '.$er->getMessage().';';
				error_log(PHP_EOL.current_time('mysql').': '.$messageFLog.PHP_EOL, 3, $rb_logFile);
			}
		}
	}

    if (isset($excludedPage)&&$excludedPage==false&&empty(apply_filters('wp_doing_cron',defined('DOING_CRON')&&DOING_CRON))) {
	    add_shortcode('test_sc_oval', 'test_sc_oval_exec');

//	    RFWP_shortcodesInGlobal();

	    add_action('wp_head', 'RFWP_blocks_in_head_add', 101);
	    add_action('wp_head', 'RFWP_launch_without_content', 1001);
    }
	/********** end of new working system *********************************************************************************/
	/********** autosync and JS text edit *********************************************************************************/
	if (!function_exists('RFWP_syncFunctionAdd1')) {
		function RFWP_syncFunctionAdd1() {
			wp_enqueue_script(
				'asyncBlockInserting',
				plugins_url().'/'.basename(__DIR__).'/asyncBlockInserting.js',
				array('jquery'),
				$GLOBALS['realbigForWP_version'],
				false
			);

			wp_localize_script(
				'asyncBlockInserting',
				'adg_object_ad',
				array('ajax_url' => admin_url('admin-ajax.php'))
			);

			if (!is_admin()&&empty(apply_filters('wp_doing_cron',defined('DOING_CRON')&&DOING_CRON))&&empty(apply_filters('wp_doing_ajax',defined('DOING_AJAX')&&DOING_AJAX))) {
				RFWP_WorkProgressLog(false,'asyncBlockInserting file add');
			}
		}
	}

	if (!function_exists('RFWP_syncFunctionAdd2')) {
		function RFWP_syncFunctionAdd2() {
			wp_enqueue_script(
				'readyAdGather',
				plugins_url().'/'.basename(__DIR__).'/readyAdGather.js',
				array('jquery'),
				$GLOBALS['realbigForWP_version'],
				false
			);

			wp_localize_script(
				'readyAdGather',
				'adg_object',
				array('ajax_url' => admin_url('admin-ajax.php'))
			);

			if (!is_admin()&&empty(apply_filters('wp_doing_cron',defined('DOING_CRON')&&DOING_CRON))&&empty(apply_filters('wp_doing_ajax',defined('DOING_AJAX')&&DOING_AJAX))) {
				RFWP_WorkProgressLog(false,'readyAdGather file add');
			}
		}
	}

	if (empty(apply_filters('wp_doing_cron',defined('DOING_CRON')&&DOING_CRON))) {
		add_action('wp_ajax_saveAdBlocks', 'saveAdBlocks');
		add_action('wp_ajax_nopriv_saveAdBlocks', 'saveAdBlocks');
		add_action('wp_ajax_setLongCache', 'setLongCache');
		add_action('wp_ajax_nopriv_setLongCache', 'setLongCache');
    }

	if (!function_exists('RFWP_js_add')) {
		function RFWP_js_add() {
			add_action('wp_enqueue_scripts', 'RFWP_syncFunctionAdd1', 10);
			$cacheTimeoutMobile = get_transient('rb_mobile_cache_timeout');
			$cacheTimeoutDesktop = get_transient('rb_desktop_cache_timeout');
			if (!empty($GLOBALS['dev_mode'])) {
				$cacheTimeoutMobile = 0;
				$cacheTimeoutDesktop = 0;
			}

			if (empty($cacheTimeoutDesktop)||empty($cacheTimeoutMobile)) {
				$cacheTimeout = get_transient('rb_cache_timeout');

				if (!empty($GLOBALS['dev_mode'])) {
					$cacheTimeout = 0;
				}

				if (empty($cacheTimeout)) {
					add_action('wp_enqueue_scripts', 'RFWP_syncFunctionAdd2', 11);
				}
			}
			if (!is_admin()&&empty(apply_filters('wp_doing_cron',defined('DOING_CRON')&&DOING_CRON))&&empty(apply_filters('wp_doing_ajax',defined('DOING_AJAX')&&DOING_AJAX))) {
				RFWP_WorkProgressLog(false,'js_add end');
			}
		}
	}

	$lastSyncTimeTransient = get_transient('realbigPluginSyncAttempt');
	$activeSyncTransient   = get_transient('realbigPluginSyncProcess');
	if (!empty($GLOBALS['token'])&&$GLOBALS['token']!='no token'&&empty($activeSyncTransient)&&empty($lastSyncTimeTransient)) {
		$nextSchedulerCheck = wp_next_scheduled('rb_cron_hook');
		if (empty($nextSchedulerCheck)) {
			RFWP_cronAutoGatheringLaunch();
		} else {
            if (!empty(apply_filters('wp_doing_cron', defined('DOING_CRON')&&DOING_CRON))) {
	            RFWP_cronAutoSyncDelete();
            }
        }
		if (!is_admin()&&empty(apply_filters('wp_doing_cron',defined('DOING_CRON')&&DOING_CRON))&&empty(apply_filters('wp_doing_ajax',defined('DOING_AJAX')&&DOING_AJAX))) {
			RFWP_WorkProgressLog(false,'auto sync cron create');
		}
	}
	if (!empty($GLOBALS['token'])&&$GLOBALS['token']!='no token'&&!empty(apply_filters('wp_doing_cron', defined('DOING_CRON')&&DOING_CRON))&&empty($activeSyncTransient)&&empty($lastSyncTimeTransient)) {
		RFWP_autoSync();
	}
	/********** end autosync and JS text edit *****************************************************************************/
	/********** adding AD code in head area *******************************************************************************/
	if (!function_exists('RFWP_AD_header_add')) {
		function RFWP_AD_header_add() {
//		if (!is_admin()&&empty(apply_filters('wp_doing_cron',defined('DOING_CRON')&&DOING_CRON))&&empty(apply_filters('wp_doing_ajax',defined('DOING_AJAX')&&DOING_AJAX))) {
//			RFWP_WorkProgressLog(false,'ad header add begin');
//		}
			global $wpdb;
			$getDomain = 'any.realbig.media';
			$getRotator = 'rotator';

			$getOV = $wpdb->get_results('SELECT optionName, optionValue FROM '.$GLOBALS['wpPrefix'].'realbig_settings WHERE optionName IN ("domain","rotator")');
			foreach ($getOV AS $k => $item) {
				if (!empty($item->optionValue)) {
					if ($item->optionName == 'domain') {
						$getDomain = $item->optionValue;
					} else {
						$getRotator = $item->optionValue;
					}
				}
			}
			unset($k, $item);

			if (!empty($GLOBALS['dev_mode'])&&!empty($GLOBALS['kill_rb'])&&$GLOBALS['kill_rb']==2) {
				$getDomain  = "ex.ua";
			}
			$rotatorUrl = "https://".$getDomain."/".$getRotator.".min.js";

			$GLOBALS['rotatorUrl'] = $rotatorUrl;

			require_once (dirname(__FILE__)."/textEditing.php");
//		$headerParsingResult = RFWP_headerADInsertor();
			$headerParsingResult = RFWP_headerInsertor('ad');

			$longCache = RFWP_getLongCache();

			/*if ($headerParsingResult == true&&empty($longCache)) {
				?><!--rb_ad_header_placeholder--><?php
			}*/

			if ($headerParsingResult == true&&empty($longCache)) {
				?><script type="text/javascript"> rbConfig = {start: performance.now(),rotator:'<?php echo $getRotator ?>'}; </script>
                <script type="text/javascript">
                    function onErrorPlacing() {
                        if (typeof cachePlacing !== 'undefined' && typeof cachePlacing === 'function' && typeof jsInputerLaunch !== 'undefined' && jsInputerLaunch == 15) {
                            let errorInfo = [];
                            cachePlacing('low',errorInfo);
                        } else {
                            setTimeout(function () {
                                onErrorPlacing();
                            }, 100)
                        }
                    }
                    let rotatorScript = document.createElement('script');
                    rotatorScript.src = "//<?php echo $getDomain ?>/<?php echo $getRotator ?>.min.js";
                    rotatorScript.type = "text/javascript";
                    rotatorScript.async = true;

                    document.head.append(rotatorScript);
                </script>
                <script>
                    var xhr = new XMLHttpRequest();
                    xhr.open('GET',"//<?php echo $getDomain ?>/<?php echo $getRotator ?>.min.js",true);
                    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                    xhr.onreadystatechange = function() {
                        console.log('xhr_status: '+xhr.status);
                        console.log('xhr_status_text: '+xhr.statusText);
                        if (xhr.status != 200) {
                            if (xhr.statusText != 'abort') {
                                onErrorPlacing();
                            }
                        }
                    };
                    xhr.send();
                </script><?php
			}
			if (!is_admin()&&empty(apply_filters('wp_doing_cron',defined('DOING_CRON')&&DOING_CRON))&&empty(apply_filters('wp_doing_ajax',defined('DOING_AJAX')&&DOING_AJAX))) {
				RFWP_WorkProgressLog(false,'AD_header_add end');
			}
		}
    }

	if (!function_exists('RFWP_push_head_add')) {
		function RFWP_push_head_add() {
			require_once (dirname(__FILE__)."/textEditing.php");
//		if (!is_admin()&&empty(apply_filters('wp_doing_cron',defined('DOING_CRON')&&DOING_CRON))&&empty(apply_filters('wp_doing_ajax',defined('DOING_AJAX')&&DOING_AJAX))) {
//			RFWP_WorkProgressLog(false,'push header add begin');
//		}
//		$headerParsingResult = RFWP_headerPushInsertor();
			$headerParsingResult = RFWP_headerInsertor('push');
			if ($headerParsingResult == true) {
				global $wpdb;

				$pushDomain = $wpdb->get_var('SELECT optionValue FROM '.$GLOBALS['wpPrefix'].'realbig_settings WHERE optionName = "pushDomain"');
				if (empty($pushDomain)) {
					$pushDomain = 'bigreal.org';
				}

				?><script charset="utf-8" async
                          src="https://<?php echo $pushDomain ?>/pushJs/<?php echo $GLOBALS['rb_push']['code'] ?>.js"></script><?php
			}
			if (!is_admin()&&empty(apply_filters('wp_doing_cron',defined('DOING_CRON')&&DOING_CRON))&&empty(apply_filters('wp_doing_ajax',defined('DOING_AJAX')&&DOING_AJAX))) {
				RFWP_WorkProgressLog(false,'push_head_add end');
			}
		}
    }

	if (!function_exists('RFWP_push_native_head_add')) {
		function RFWP_push_native_head_add() {
			require_once (dirname(__FILE__)."/textEditing.php");
			$headerParsingResult = RFWP_headerInsertor('pushNative');
			if ($headerParsingResult == true) {
				global $wpdb;

				$pushDomain = $wpdb->get_var('SELECT optionValue FROM '.$GLOBALS['wpPrefix'].'realbig_settings WHERE optionName = "pushNativeDomain"');
				if (empty($pushDomain)) {
					$pushDomain = 'truenat.bid';
				}

				?><script charset="utf-8" async
                          src="https://<?php echo $pushDomain ?>/nat/<?php echo $GLOBALS['rb_push']['nativeCode'] ?>.js"></script><?php
			}
			if (!is_admin()&&empty(apply_filters('wp_doing_cron',defined('DOING_CRON')&&DOING_CRON))&&empty(apply_filters('wp_doing_ajax',defined('DOING_AJAX')&&DOING_AJAX))) {
				RFWP_WorkProgressLog(false,'push_native_head_add end');
			}
		}
    }

	if (!function_exists('RFWP_inserts_head_add')) {
		function RFWP_inserts_head_add() {
			$contentToAdd = RFWP_insertsToString('header');
			$stringToAdd = '';
			foreach ($contentToAdd['header'] AS $k=>$item) {
				$stringToAdd .= $item['content'];
			}
			?><?php echo $stringToAdd ?><?php
			if (!is_admin()&&empty(apply_filters('wp_doing_cron',defined('DOING_CRON')&&DOING_CRON))&&empty(apply_filters('wp_doing_ajax',defined('DOING_AJAX')&&DOING_AJAX))) {
				RFWP_WorkProgressLog(false,'inserts_head_add end');
			}
		}
    }

	// new
	if (!is_admin()&&empty(apply_filters('wp_doing_cron', defined('DOING_CRON')&&DOING_CRON))) {
		add_action('wp_head', 'RFWP_AD_header_add', 0);
		$separatedStatuses = [];
		$statuses = $wpdb->get_results($wpdb->prepare('SELECT optionName, optionValue FROM '.$wpPrefix.'realbig_settings WHERE optionName IN (%s, %s,%s, %s,%s, %s)', [
			"pushCode",
			"pushStatus",
			"pushDomain",
			"pushNativeCode",
			"pushNativeStatus",
            "pushNativeDomain"
		]), ARRAY_A);
		if (!empty($statuses)) {
		    foreach ($statuses AS $k => $item) {
			    $separatedStatuses[$item['optionName']] = $item['optionValue'];
            }
			if (!empty($separatedStatuses)&&!empty($separatedStatuses['pushCode'])&&isset($separatedStatuses['pushStatus'])&&$separatedStatuses['pushStatus']==1) {
				add_action('wp_head', 'RFWP_push_head_add', 0);
//				$GLOBALS['pushCode'] = $separatedStatuses['pushCode'];
				$GLOBALS['rb_push']['code'] = $separatedStatuses['pushCode'];
				if (empty($separatedStatuses['pushDomain'])) {
					$GLOBALS['rb_push']['domain'] = 'bigreal.org';
				} else {
					$GLOBALS['rb_push']['domain'] = $separatedStatuses['pushDomain'];
                }
			}
			if (!empty($separatedStatuses)&&!empty($separatedStatuses['pushNativeCode'])&&isset($separatedStatuses['pushNativeStatus'])&&$separatedStatuses['pushNativeStatus']==1) {
				add_action('wp_head', 'RFWP_push_native_head_add', 0);
				$GLOBALS['rb_push']['nativeCode'] = $separatedStatuses['pushNativeCode'];
				if (empty($separatedStatuses['pushNativeDomain'])) {
					$GLOBALS['rb_push']['nativeDomain'] = 'truenat.bid';
				} else {
					$GLOBALS['rb_push']['nativeDomain'] = $separatedStatuses['pushNativeDomain'];
				}
			}
		}
		add_action('wp_head', 'RFWP_inserts_head_add', 0);
		if (!is_admin()&&empty(apply_filters('wp_doing_cron',defined('DOING_CRON')&&DOING_CRON))&&empty(apply_filters('wp_doing_ajax',defined('DOING_AJAX')&&DOING_AJAX))) {
			RFWP_WorkProgressLog(false,'all inserts in header end');
		}
	}
	/********** end of adding AD code in head area ************************************************************************/
	/********** manual sync ***********************************************************************************************/
    if (empty(apply_filters('wp_doing_cron', defined('DOING_CRON')&&DOING_CRON))) {
	    if (!empty($curUserCan)&&strpos($GLOBALS['PHP_SELF'], 'wp-admin')!= false) {
		    if (!empty($curUserCan)&&!empty($_POST['saveTokenButton'])) {
		        $workProcess = null;
		        $getWorkProcess = $wpdb->get_var($wpdb->prepare('SELECT id FROM '.$wpPrefix.'realbig_settings WHERE optionName = %s', ["work_process_status"]));
		        if (!empty($_POST['process_log'])) {
			        $workProcess = 'enabled';
                } else {
			        $workProcess = 'disabled';
                }
		        if (!empty($getWorkProcess)) {
			        $wpdb->update( $wpPrefix.'realbig_settings', ['optionValue' => $workProcess], ['id' => $getWorkProcess]);
                } else {
			        $wpdb->insert( $wpPrefix.'realbig_settings', ['optionName' => 'work_process_status', 'optionValue' => $workProcess]);
		        }
            }
		    $wpOptionsCheckerSyncTime = $wpdb->get_row($wpdb->prepare('SELECT optionValue FROM '.$wpPrefix.'realbig_settings WHERE optionName = %s', ["token_sync_time"]));
		    if (!empty($_POST['tokenInput'])) {
			    $sanitized_token = sanitize_text_field($_POST['tokenInput']);
			    if (RFWP_tokenMDValidate($sanitized_token)==true) {
				    $sameTokenResult = false;
				    RFWP_synchronize($sanitized_token, (empty($wpOptionsCheckerSyncTime) ? null : $wpOptionsCheckerSyncTime), $sameTokenResult, $wpPrefix, 'manual');
			    } else {
				    $GLOBALS['tokenStatusMessage'] = 'Неверный формат токена';
				    $messageFLog = 'wrong token format';
			    }
		    } elseif ($GLOBALS['token'] == 'no token') {
			    $GLOBALS['tokenStatusMessage'] = 'Введите токен';
			    $messageFLog = 'no token';
		    }
		    if (!empty($messageFLog)) {
			    error_log(PHP_EOL.current_time('mysql').': '.$messageFLog.PHP_EOL, 3, $rb_logFile);
		    }
		    RFWP_tokenTimeUpdateChecking($GLOBALS['token'], $wpPrefix);
	    }
    }
	/********** end of manual sync ****************************************************************************************/
	/************* blocks for text ****************************************************************************************/
	if (empty(apply_filters('wp_doing_cron',defined('DOING_CRON')&&DOING_CRON))&&!is_admin()) {
        if (empty($excludedPage)) {
	        add_filter('the_content', 'RFWP_adBlocksToContentInsertingFunction', 5000);
        }

		//	insertings body add
		RFWP_js_add();
		add_filter('the_content', 'RFWP_insertingsToContentAddingFunction', 5001);

//		add_shortcode('test_sc_oval', 'test_sc_oval_exec');
//		add_filter('the_content', 'RFWP_shortCodesAdd', 4999);
		if (!is_admin()&&empty(apply_filters('wp_doing_cron',defined('DOING_CRON')&&DOING_CRON))&&empty(apply_filters('wp_doing_ajax',defined('DOING_AJAX')&&DOING_AJAX))) {
			RFWP_WorkProgressLog(false,'add content filter end');
		}
	}
	/************* end blocks for text ************************************************************************************/
	/************* adding insertings in text *****************************************************/
	if (!function_exists('RFWP_insertingsToContentAddingFunction')) {
		function RFWP_insertingsToContentAddingFunction($content) {
			if (empty($GLOBALS['used_ins'])||(!empty($GLOBALS['used_ins'])&&empty($GLOBALS['used_ins']['body_0']))) {
				$GLOBALS['used_ins']['body_0'] = true;
				$insertings = RFWP_insertsToString('body', 0);
			}
			$content = RFWP_insertingsToContent($content);
			if (!is_admin()&&empty(apply_filters('wp_doing_cron',defined('DOING_CRON')&&DOING_CRON))&&empty(apply_filters('wp_doing_ajax',defined('DOING_AJAX')&&DOING_AJAX))) {
				RFWP_WorkProgressLog(false,'RFWP_insertingsToContentAddingFunction end');
			}
			return $content;
		}
	}
	/************* end adding insertings in text *****************************************************/
	/********** using settings in texts ***********************************************************************************/
	if (!function_exists('RFWP_adBlocksToContentInsertingFunction')) {
		function RFWP_adBlocksToContentInsertingFunction($content) {
			global $wp_query;
			global $post;

			if (!is_admin()&&empty(apply_filters('wp_doing_cron',defined('DOING_CRON')&&DOING_CRON))&&empty(apply_filters('wp_doing_ajax',defined('DOING_AJAX')&&DOING_AJAX))) {
				RFWP_WorkProgressLog(false,'adBlocksToContentInsertingFunction begin');
			}

			$pasingAllowed = true;
			$arrayOfCheckedTypes = [
				'is_home' => is_home(),
				'is_front_page' => is_front_page(),
				'is_page' => is_page(),
				'is_single' => is_single(),
				'is_singular' => is_singular(),
				'is_archive' => is_archive(),
				'is_category' => is_category(),
			];

			if ((!empty($arrayOfCheckedTypes['is_home'])||!empty($arrayOfCheckedTypes['is_front_page']))&&!empty($GLOBALS['pageChecks']['excludedMainPage'])) {
				return $content;
			} elseif (in_array(true, $arrayOfCheckedTypes)) {
				if (!empty($GLOBALS['pageChecks']['excludedPageTypes'])) {
					$excludedPageTypesString = $GLOBALS['pageChecks']['excludedPageTypes'];
					$excludedPageTypes = explode(',', $excludedPageTypesString);
					foreach ($excludedPageTypes AS $k => $item) {
						if (!empty($arrayOfCheckedTypes[$item])) {
							$pasingAllowed = false;
							break;
						}
					}
				}

				if (!empty($pasingAllowed)) {
					global $wpdb;

//			    $excIdClass = $wpdb->get_var('SELECT optionValue FROM '.$GLOBALS['wpPrefix'].'realbig_settings WGPS WHERE optionName = "excludedIdAndClasses"');
					$excIdClass = null;
					$blockDuplicate = 'yes';
					$realbig_settings_info = $wpdb->get_results('SELECT optionName, optionValue FROM '.$GLOBALS['wpPrefix'].'realbig_settings WGPS WHERE optionName IN ("excludedIdAndClasses","blockDuplicate")');
					if (!empty($realbig_settings_info)) {
						foreach ($realbig_settings_info AS $k => $item) {
							if (isset($item->optionValue)) {
								if ($item->optionName == 'excludedIdAndClasses') {
									$excIdClass = $item->optionValue;
								} elseif ($item->optionName == 'blockDuplicate') {
									if ($item->optionValue==0) {
										$blockDuplicate = 'no';
									}
								}
							}
						}
						unset($k,$item);
					}

					$cachedBlocks = '';
					if (!isset($GLOBALS['rb_mobile_check'])) {
						$GLOBALS['rb_mobile_check'] = RFWP_wp_is_mobile();
					}

					$shortcodesGathered = get_posts(['post_type'=>'rb_shortcodes','numberposts'=>-1]);
					$shortcodes = [];
					foreach ($shortcodesGathered AS $k=>$item) {
						if (empty($shortcodes[$item->post_excerpt])) {
							$shortcodes[$item->post_excerpt] = [];
						}
						$shortcodes[$item->post_excerpt][$item->post_title] = $item;
					}

					if (!empty($content)) {
						$fromDb = $wpdb->get_results('SELECT * FROM '.$GLOBALS['wpPrefix'].'realbig_plugin_settings WGPS');
					} else {
						$fromDb = $wpdb->get_results('SELECT * FROM '.$GLOBALS['wpPrefix'].'realbig_plugin_settings WGPS WHERE setting_type = 3');
					}
					require_once (dirname(__FILE__)."/textEditing.php");
//			    $content = RFWP_addIcons($fromDb, $content, 'content', null, null, $shortcodes, $excIdClass, $blockDuplicate);
					$content = RFWP_addIcons_test($fromDb, $content);

					if (empty($GLOBALS['used_ins'])||(!empty($GLOBALS['used_ins'])&&empty($GLOBALS['used_ins']['body_1']))) {
						$GLOBALS['used_ins']['body_1'] = true;
						$inserts = RFWP_insertsToString('body', 1);
					}

					add_filter('the_content', 'RFWP_rbCacheGatheringLaunch', 5003);
					if (!is_admin()&&empty(apply_filters('wp_doing_cron',defined('DOING_CRON')&&DOING_CRON))&&empty(apply_filters('wp_doing_ajax',defined('DOING_AJAX')&&DOING_AJAX))) {
						RFWP_WorkProgressLog(false,'adBlocksToContentInsertingFunction end');
					}

					return $content;
				} else {
					if (!is_admin()&&empty(apply_filters('wp_doing_cron',defined('DOING_CRON')&&DOING_CRON))&&empty(apply_filters('wp_doing_ajax',defined('DOING_AJAX')&&DOING_AJAX))) {
						RFWP_WorkProgressLog(false,'adBlocksToContentInsertingFunction empty content end');
					}
				}
			} else {
				if (!is_admin()&&empty(apply_filters('wp_doing_cron',defined('DOING_CRON')&&DOING_CRON))&&empty(apply_filters('wp_doing_ajax',defined('DOING_AJAX')&&DOING_AJAX))) {
					RFWP_WorkProgressLog(false,'adBlocksToContentInsertingFunction forbidden page type end');
				}
			}
			return $content;
		}
	}
	/*********** end of using settings in texts ***************************************************************************/
	/*********** begin of token input area ********************************************************************************/
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	if (is_admin()) {
		include_once (dirname(__FILE__)."/adminPage.php");
		add_action('admin_menu', 'RFWP_my_pl_settings_menu_create');
	}
	/************ end of token input area *********************************************************************************/
}
catch (Exception $ex)
{
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
		    $wpdb->update($wpPrefix.'realbig_settings', [
			    'optionName'  => 'deactError',
			    'optionValue' => 'realbigForWP: '.$ex->getMessage()
		    ], ['optionName'  => 'deactError']);
	    }
    } catch (Exception $exIex) {
    } catch (Error $erIex) { }

//	include_once ( dirname(__FILE__)."/../../../wp-admin/includes/plugin.php" );
	deactivate_plugins(plugin_basename( __FILE__ ));
	?><div style="margin-left: 200px; border: 3px solid red"><?php echo $ex; ?></div><?php
}
catch (Error $er)
{
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