<?php

if (!defined("ABSPATH")) { exit;}

//require_once (dirname(__FILE__)."/../../../wp-admin/includes/plugin.php" );
require_once (ABSPATH."/wp-admin/includes/plugin.php");
include_once (dirname(__FILE__)."/update.php");
include_once (dirname(__FILE__)."/synchronising.php");
include_once (dirname(__FILE__)."/textEditing.php");

/*
Plugin name:  Realbig Media Git version
Description:  Плагин для монетизации от RealBig.media
Version:      0.2.1
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

	if (empty(apply_filters('wp_doing_cron', defined('DOING_CRON')&&DOING_CRON))) {
		require_once (ABSPATH."/wp-includes/pluggable.php");
		$curUserCan = current_user_can('activate_plugins');
	}

	if (!empty($GLOBALS['dev_mode'])) {
		if (empty($GLOBALS['rb_admin_menu_loaded'])) {
			require_once (dirname(__FILE__)."/adminMenuAdd.php");
			$GLOBALS['rb_admin_menu_loaded'] = true;
		}
    }

	if (!isset($GLOBALS['wpPrefix'])) {
		$wpPrefix = $table_prefix;
		if (empty($wpPrefix)) {
			$wpPrefix = $wpdb->base_prefix;
		}
		$GLOBALS['wpPrefix'] = $wpPrefix;
    }

    if (!isset($GLOBALS['excludedPagesChecked'])) {
	    $GLOBALS['excludedPagesChecked'] = false;
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
	/***************** End of test zone ***********************************************************************************/
	/***************** Cached AD blocks saving ***************************************************************************************/
    function saveAdBlocks($tunnelData) {
	    if (!empty($_POST['type'])&&$_POST['type']=='blocksGethering') {
		    include_once (dirname(__FILE__).'/connectTestFile.php');
	    }
	    return $tunnelData;
    }

    function setLongCache($tunnelData) {
        if (!empty($_POST['type'])&&$_POST['type']=='longCatching') {
	        set_transient('rb_longCacheDeploy', time()+300, 300);
        }
	    return $tunnelData;
    }
	/***************** End of cached AD blocks saving *********************************************************************************/
	$tableForCurrentPluginChecker = $wpdb->get_var('SHOW TABLES LIKE "' . $wpPrefix . 'realbig_plugin_settings"');   //settings for block table checking
	$tableForToken                = $wpdb->get_var('SHOW TABLES LIKE "' . $wpPrefix . 'realbig_settings"');      //settings for token and other

    if (empty(apply_filters('wp_doing_cron', defined('DOING_CRON') && DOING_CRON))) {
	    if ((!empty($curUserCan)&&!empty($_POST['statusRefresher']))||empty($tableForToken)||empty($tableForCurrentPluginChecker)) {
	        $wpdb->query('DELETE FROM '.$wpPrefix.'posts WHERE post_type IN ("rb_block_mobile","rb_block_desktop","rb_block_mobile_new","rb_block_desktop_new") AND post_author = 0');
	        delete_transient('rb_cache_timeout');
	        delete_transient('rb_longCacheDeploy');
	        delete_transient('rb_mobile_cache_timeout');
	        delete_transient('rb_desktop_cache_timeout');
		    delete_option('realbig_status_gatherer_version');

		    $messageFLog = 'clear cached ads';
		    error_log(PHP_EOL.current_time('mysql').': '.$messageFLog.PHP_EOL, 3, $rb_logFile);
	    }
    }

    if (!isset($GLOBALS['realbigForWP_version'])) {
	    $pluginData = get_plugin_data(__FILE__);
	    if (!empty($pluginData['Version'])) {
		    $GLOBALS['realbigForWP_version'] = $pluginData['Version'];
	    } else {
		    $GLOBALS['realbigForWP_version'] = '0.1.26.78';
	    }
    }

    if (!isset($lastSuccessVersionGatherer)||!isset($statusGatherer)) {
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
	}

	if ($statusGatherer['realbig_plugin_settings_table'] == false || $statusGatherer['realbig_settings_table'] == false || $lastSuccessVersionGatherer != $GLOBALS['realbigForWP_version']) {
		$statusGatherer = RFWP_dbTablesCreateFunction($tableForCurrentPluginChecker, $tableForToken, $wpPrefix, $statusGatherer);
	}
	if ($statusGatherer['realbig_plugin_settings_table'] == true && ($statusGatherer['realbig_plugin_settings_columns'] == false || $lastSuccessVersionGatherer != $GLOBALS['realbigForWP_version'])) {
		$colCheck = $wpdb->get_col('SHOW COLUMNS FROM '.$wpPrefix.'realbig_plugin_settings');
		if (!empty($colCheck)) {
			$statusGatherer = RFWP_wpRealbigPluginSettingsColomnUpdateFunction($wpPrefix, $colCheck, $statusGatherer);
		} else {
			$statusGatherer['realbig_plugin_settings_columns'] = false;
		}
	}
	/********** end of checking and creating tables ***********************************************************************/
	/********** token gathering and adding "timeUpdate" field in wp_realbig_settings **************************************/
	if (empty($GLOBALS['token'])||(!empty($GLOBALS['token'])&&$GLOBALS['token']=='no token')) {
		RFWP_tokenChecking($wpPrefix);
	}

	$unmarkSuccessfulUpdate      = $wpdb->get_var('SELECT optionValue FROM '.$wpPrefix.'realbig_settings WHERE optionName = "successUpdateMark"');
	$jsAutoSynchronizationStatus = $wpdb->get_var('SELECT optionValue FROM '.$wpPrefix.'realbig_settings WHERE optionName = "jsAutoSyncFails"');

	if ($statusGatherer['realbig_plugin_settings_table'] == true && ($statusGatherer['element_column_values'] == false || $lastSuccessVersionGatherer != $GLOBALS['realbigForWP_version'])) {
		/** enumUpdate */
		$statusGatherer = RFWP_updateElementEnumValuesFunction($wpPrefix, $statusGatherer);
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
        if (empty($GLOBALS['excludedPagesChecked'])) {
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
                preg_match_all("~(\/|\\\)([^\/^\\\]+)~", get_home_url(), $m);

                foreach ($usedUrl1 AS $usedUrl) {
                    if (!empty($usedUrl)&&!empty($m)) {
                        if ($usedUrl=="/"||$usedUrl==get_home_url()."/") {
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
        }
    } catch (Exception $excludedE) {
        $excludedPage = false;
    }
	/********** end of checking requested page for excluding **************************************************************/
	/********** autosync and JS text edit *********************************************************************************/
	function RFWP_syncFunctionAdd() {
		wp_enqueue_script( 'synchronizationJS',
			dirname(__FILE__).'/synchronizationJS.js',
			array('jquery'),
			$GLOBALS['realbigForWP_version'],
			true );
	}

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
	}

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
	}

	add_action('wp_ajax_saveAdBlocks', 'saveAdBlocks');
	add_action('wp_ajax_nopriv_saveAdBlocks', 'saveAdBlocks');
	add_action('wp_ajax_setLongCache', 'setLongCache');
	add_action('wp_ajax_nopriv_setLongCache', 'setLongCache');

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
	}
	if (!empty($GLOBALS['token'])&&$GLOBALS['token']!='no token'&&!empty(apply_filters('wp_doing_cron', defined('DOING_CRON')&&DOING_CRON))&&empty($activeSyncTransient)&&empty($lastSyncTimeTransient)) {
		RFWP_autoSync();
	}
	/********** end autosync and JS text edit *****************************************************************************/
	/********** adding AD code in head area *******************************************************************************/
	function RFWP_AD_header_add() {
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

		$longCache = get_transient('rb_longCacheDeploy');
		if (!empty($GLOBALS['dev_mode'])) {
            $longCache = false;
		}
		$GLOBALS['rb_longCache'] = $longCache;

		if ($headerParsingResult == true&&empty($longCache)) {
            ?><script type="text/javascript"> rbConfig = {start: performance.now(),rotator:'<?php echo $getRotator ?>'}; </script>
            <?php /* <script>
                var xhr = new XMLHttpRequest();
                xhr.open('GET',"//<?php echo $getDomain ?>/<?php echo $getRotator ?>.min.js",true);
                xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                xhr.onreadystatechange = function() {
                    if (xhr.status != 200) {
                        if (xhr.statusText != 'abort') {
                            onErrorPlacing();
                        }
                    }
                };
                xhr.send();
            </script> */ ?>
            <script type="text/javascript">
                function onErrorPlacing() {
                    if (typeof cachePlacing !== 'undefined' && typeof cachePlacing === 'function') {
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
<?php
		}
	}

	function RFWP_push_head_add() {
		require_once (dirname(__FILE__)."/textEditing.php");
//		$headerParsingResult = RFWP_headerPushInsertor();
		$headerParsingResult = RFWP_headerInsertor('push');
		if ($headerParsingResult == true) {
		    global $wpdb;

			$pushDomain = $wpdb->get_var('SELECT optionValue FROM '.$GLOBALS['wpPrefix'].'realbig_settings WHERE optionName = "pushDomain"');
		    if (empty($pushDomain)) {
			    $pushDomain = 'bigreal.org';
            }

			?><script charset="utf-8" async
                src="https://<?php echo $pushDomain ?>/pushJs/<?php echo $GLOBALS['pushCode'] ?>.js"></script><?php
		}
	}

    function RFWP_inserts_head_add() {
	    $contentToAdd = RFWP_insertsToString('header');
	    $stringToAdd = '';
	    foreach ($contentToAdd['header'] AS $k=>$item) {
	        $stringToAdd .= $item['content'];
        }
        ?><?php echo $stringToAdd ?><?php
    }

	// new
	if (!is_admin()&&empty(apply_filters('wp_doing_cron', defined('DOING_CRON')&&DOING_CRON))) {
		add_action('wp_head', 'RFWP_AD_header_add', 0);
		$separatedStatuses = [];
		$statuses = $wpdb->get_results($wpdb->prepare('SELECT optionName, optionValue FROM '.$wpPrefix.'realbig_settings WHERE optionName IN (%s, %s)', [
			"pushCode",
			"pushStatus"
		]), ARRAY_A);
		if (!empty($statuses)) {
		    foreach ($statuses AS $k => $item) {
			    $separatedStatuses[$item['optionName']] = $item['optionValue'];
            }
			if (!empty($separatedStatuses)&&!empty($separatedStatuses['pushCode'])&&isset($separatedStatuses['pushStatus'])&&$separatedStatuses['pushStatus']==1) {
				add_action('wp_head', 'RFWP_push_head_add', 0);
				$GLOBALS['pushCode'] = $separatedStatuses['pushCode'];
            }
		}
		add_action('wp_head', 'RFWP_inserts_head_add', 0);
	}
	/********** end of adding AD code in head area ************************************************************************/
	/********** manual sync ***********************************************************************************************/
    if (empty(apply_filters('wp_doing_cron', defined('DOING_CRON')&&DOING_CRON ))) {
	    if (!empty($curUserCan)&&strpos($GLOBALS['PHP_SELF'], 'wp-admin')!= false) {
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
	if (empty(apply_filters('wp_doing_cron', defined('DOING_CRON')&&DOING_CRON))&&!is_admin()) {
        if (empty($excludedPage)) {
	        add_filter('the_content', 'RFWP_adBlocksToContentInsertingFunction', 5000);
        }

		//	insertings body add
		RFWP_js_add();
		add_filter('the_content', 'RFWP_insertingsToContentAddingFunction', 5001);

		if (!function_exists('test_sc_oval_exec')) {
			function test_sc_oval_exec() {
				return '<div style="width: 100px; height: 20px; border: 1px solid black; background-color: #0033cc; border-radius: 30%;"></div><script>console.log(\'oval narisoval\');</script>';
			}
		}
		add_shortcode('test_sc_oval', 'test_sc_oval_exec');
		add_filter('the_content', 'RFWP_shortCodesAdd', 4999);
	}
	/************* end blocks for text ************************************************************************************/
	/************* adding insertings in text *****************************************************/
    function RFWP_insertingsToContentAddingFunction($content) {
	    if (empty($GLOBALS['used_ins'])||(!empty($GLOBALS['used_ins'])&&empty($GLOBALS['used_ins']['body_0']))) {
		    $GLOBALS['used_ins']['body_0'] = true;
		    $insertings = RFWP_insertsToString('body', 0);
        }
	    $content = RFWP_insertingsToContent($content);
	    return $content;
    }
	/************* end adding insertings in text *****************************************************/
	/********** using settings in texts ***********************************************************************************/
	function RFWP_adBlocksToContentInsertingFunction($content) {
		global $wp_query;
		global $post;

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
                $mobileCheck = RFWP_wp_is_mobile();
                $GLOBALS['rb_mobile_check'] = $mobileCheck;

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
			    $content = RFWP_addIcons($fromDb, $content, 'content', null, null, $shortcodes, $excIdClass, $blockDuplicate);

			    if (empty($GLOBALS['used_ins'])||(!empty($GLOBALS['used_ins'])&&empty($GLOBALS['used_ins']['body_1']))) {
				    $GLOBALS['used_ins']['body_1'] = true;
				    $inserts = RFWP_insertsToString('body', 1);
			    }

                add_filter('the_content', 'RFWP_rbCacheGatheringLaunch', 5003);

			    return $content;
		    } else {
			    return $content;
		    }
	    } else {
		    return $content;
	    }
	}

	function RFWP_shortCodesAdd($content) {
	    if (empty($GLOBALS['shortcodes'])) {
		    RFWP_shortcodesInGlobal();
	    }
	    if (!empty($GLOBALS['shortcodes'])&&$GLOBALS['shortcodes']!='nun') {
		    $content = RFWP_shortcodesToContent($content);
	    }
	    return $content;
    }

	function RFWP_rbCacheGatheringLaunch($content) {
		global $wpdb;

		$mobileCheck = $GLOBALS['rb_mobile_check'];
		if (!empty($mobileCheck)) {
			$cachedBlocks = get_posts(['post_type' => 'rb_block_mobile_new','numberposts' => 100]);
		} else {
			$cachedBlocks = get_posts(['post_type' => 'rb_block_desktop_new','numberposts' => 100]);
		}

		$longCache = $GLOBALS['rb_longCache'];
        $content = RFWP_rb_cache_gathering($content, $cachedBlocks, $longCache);

		return $content;
	}
	/*********** end of using settings in texts ***************************************************************************/
	/*********** begin of token input area ********************************************************************************/
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	if (is_admin()) {
		add_action('admin_menu', 'RFWP_my_pl_settings_menu_create');
	}
	function RFWP_my_pl_settings_menu_create() {
		if (strpos($_SERVER['REQUEST_URI'], 'page=realbigForWP')) {
			add_menu_page( 'Your code sending configuration', 'realBIG', 'administrator', __FILE__, 'RFWP_TokenSync', plugins_url().'/'.basename(__DIR__).'/assets/realbig_plugin_hover.png' );
		} else {
			add_menu_page( 'Your code sending configuration', 'realBIG', 'administrator', __FILE__, 'RFWP_TokenSync', plugins_url().'/'.basename(__DIR__).'/assets/realbig_plugin_standart.png' );
		}
		add_action('admin_init', 'RFWP_register_mysettings');
	}

	function RFWP_register_mysettings() {
		register_setting('sending_zone', 'token_value_input');
		register_setting('sending_zone', 'token_value_send' );
	}

	function RFWP_TokenSync() {
		global $wpdb;
		global $wpPrefix;

		$blocksCounter = 1;
		if (!empty($GLOBALS['dev_mode'])) {
			$killRbAvailable = true;
		} else {
			$killRbAvailable = false;
		}
		$postsGatherDesktop = $wpdb->get_results('SELECT post_title FROM '.$wpPrefix.'posts WHERE post_type IN ("rb_block_desktop_new")');
		$postsGatherMobile  = $wpdb->get_results('SELECT post_title FROM '.$wpPrefix.'posts WHERE post_type IN ("rb_block_mobile_new" )');

		try {
		    $rbSettings = $wpdb->get_results('SELECT optionName, optionValue, timeUpdate FROM ' . $GLOBALS["wpPrefix"] . 'realbig_settings WHERE optionName IN ("deactError","domain","excludedMainPage","excludedPages","pushStatus","excludedPageTypes","kill_rb")', ARRAY_A);
//			$rbTransients = $wpdb->get_results('SELECT optionName, optionValue, timeUpdate FROM ' . $GLOBALS["wpPrefix"] . 'realbig_settings WHERE optionName IN ("deactError","domain","excludedMainPage","excludedPages","pushStatus","excludedPageTypes","kill_rb")', ARRAY_A);

		    $killRbCheck = '';

		    if (!empty($rbSettings)) {
		        foreach ($rbSettings AS $k=>$item) {
		            if ($item['optionName']=='domain') {
			            $usedDomain = $item["optionValue"];
		            } elseif ($item['optionName']=='deactError') {
			            $deacError = $item["optionValue"];
			            $deacTime = $item["timeUpdate"];
                    } elseif ($item['optionName']=='excludedMainPage') {
		                if (!empty($item["optionValue"])) {
			                $excludedMainPage = 'Да';
                        } else {
			                $excludedMainPage = 'Нет';
		                }
                    } elseif ($item['optionName']=='excludedPages') {
			            $excludedPage = $item["optionValue"];
                    } elseif ($item['optionName']=='excludedPageTypes'&&!empty($item["optionValue"])) {
			            $excludedPageTypes = explode(',',$item["optionValue"]);
                    } elseif ($item['optionName']=='pushStatus') {
			            if (!empty($item["optionValue"])) {
				            $pushStatus = 'Да';
			            } else {
				            $pushStatus = 'Нет';
			            }
                    } elseif ($item['optionName']=='kill_rb') {
		                if (!empty($GLOBALS['dev_mode'])) {
			                if (!empty($item["optionValue"])&&$item["optionValue"]==2) {
				                $killRbCheck = 'checked';
			                }
			                if (!empty($item["optionValue"])) {
				                $killRbAvailable = true;
			                }
		                }
		            }
                }
            }
	    } catch (Exception $e) {
			$usedDomain = "domain gathering error";
			$deacError = "error gathering error";
	        $deacTime = "error gathering error";
			$excludedMainPage = "main page gathering error";
			$excludedPage = "pages gathering error";
			$pushStatus = "error gathering error";
			$excludedPageTypes = "error gathering types";
        }
		?>
        <style>
            .separated-blocks {
                display: inline-table;
                margin-right:10px;
            }
            .element-separator {
                margin: 10px 0;
            }
            .squads-blocks {
                border: 1px solid grey;
                width: max-content;
                margin-top: 20px;
                padding: 5px;
            }
            .o-lists {
                margin: 5px 5px 5px 1em;
            }
        </style>
        <div class="wrap">
            <div class="separated-blocks">
                <form method="post" name="tokenForm" id="tokenFormId">
                    <label><span class="element-separator" style="font-size: 16px">Токен</span><br/>
                        <input class="element-separator" name="tokenInput" id="tokenInputId" value="<?php echo $GLOBALS['token'] ?>"
                               style="min-width: 280px"
                               required>
                        <label class="element-separator" style="font-size: 16px; margin-left: 10px; color: <?php echo $GLOBALS['statusColor'] ?> ">Время
                            последней синхронизации: <?php echo $GLOBALS['tokenTimeUpdate'] ?></label>
                    </label>
                    <br>
                    <div class="element-separator">
                        <label for="statusRefresher">обновить проверку</label>
                        <input type="checkbox" name="statusRefresher" id="statusRefresher">
                    </div>
                    <?php if (!empty($killRbAvailable)): ?>
                        <div class="element-separator">
                            <label for="kill_rb">Kill connection to rotator</label>
                            <input type="checkbox" name="kill_rb" id="kill_rb_id" <?php echo $killRbCheck ?>>
                        </div>
                    <?php endif; ?>
                    <br>
		            <?php submit_button( 'Синхронизировать', 'primary', 'saveTokenButton' ) ?>
		            <?php if (!empty($GLOBALS['tokenStatusMessage'])): ?>
                        <div name="rezultDiv" style="font-size: 16px"><?php echo $GLOBALS['tokenStatusMessage'] ?></div>
		            <?php endif; ?>
                </form>
            </div>
            <div class="separated-blocks">
                <div class="squads-blocks">
                    <div>Надписи ниже нужны для тестировки</div>
                    <div>Статус соединения
                        1: <?php echo(!empty($GLOBALS['connection_request_rezult_1']) ? $GLOBALS['connection_request_rezult_1'] : 'empty') ?></div>
                    <div>Статус соединения
                        общий: <?php echo(!empty($GLOBALS['connection_request_rezult']) ? $GLOBALS['connection_request_rezult'] : 'empty') ?></div>
                </div>
	            <?php if (!empty($rbSettings)): ?>
		            <?php if (!empty($deacError)): ?>
                        <div class="squads-blocks">
                            Инфо о последней деактивации:
                            <div>
                                Update Time: <?php echo $deacTime?> <br>
                                Error: <?php echo $deacError?> <br>
                            </div>
                        </div>
		            <?php endif; ?>
		            <?php if (!empty($usedDomain)): ?>
                        <div class="squads-blocks">
                            Инфо о домене:
                            <div>
                                Используемый домен: <span style="color: green"><?php echo $usedDomain?></span>. <br>
                            </div>
                        </div>
		            <?php endif; ?>
		            <?php if (!empty($postsGatherDesktop)||!empty($postsGatherMobile)):?>
                        <div class="squads-blocks">
                            Количество закешированных блоков: <?php echo count($postsGatherDesktop)+count($postsGatherMobile) ?>.<br>
                            <div class="separated-blocks">
                                ИД десктопных:
					            <?php foreach ($postsGatherDesktop AS $item): ?>
                                    <div>
	                                    <?php echo $blocksCounter++; ?>: <?php echo $item->post_title ?>;
                                    </div>
					            <?php endforeach; ?>
                            </div>
				            <?php $blocksCounter = 1; ?>
                            <div class="separated-blocks">
                                ИД мобильных:
                                <?php foreach ($postsGatherMobile AS $item): ?>
                                    <div>
                                        <?php echo $blocksCounter++; ?>: <?php echo $item->post_title ?>;
                                    </div>
					            <?php endforeach; ?>
                            </div>
                        </div>
		            <?php endif; ?>
		            <?php if (!empty($excludedMainPage)):?>
                        <div class="squads-blocks">
                            Главная страница исключена: <?php echo $excludedMainPage ?>.<br>
                        </div>
		            <?php endif; ?>
		            <?php if (!empty($excludedPage)):?>
                        <div class="squads-blocks">
                            Исключенные страницы: <?php echo $excludedPage ?>.<br>
                        </div>
		            <?php endif; ?>
		            <?php if (!empty($pushStatus)):?>
                        <div class="squads-blocks">
                            Вставлять в хедер PUSH-код: <?php echo $pushStatus ?>.<br>
                        </div>
		            <?php endif; ?>
		            <?php if (!empty($excludedPageTypes)):?>
                    <?php $counter = 1; ?>
                        <div class="squads-blocks">
                            Исключенные типы страниц:
                            <ol class="o-lists">
                            <?php foreach ($excludedPageTypes AS $k => $item): ?>
                                <li>
                                    <?php echo $item ?>;
                                </li>
                            <?php endforeach; ?>
                            </ol>
                        </div>
		            <?php endif; ?>
	            <?php endif; ?>
            </div>
        </div>
		<?php
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