<?php

if (!defined("ABSPATH")) { exit;}

//include ( dirname(__FILE__).'/../../../wp-load.php' );
require_once ( dirname(__FILE__)."/../../../wp-admin/includes/plugin.php" );
//include_once ( dirname(__FILE__)."/../../../wp-admin/includes/upgrade.php" );
//include_once ( dirname(__FILE__).'/../../../wp-includes/wp-db.php');
include ( dirname(__FILE__)."/update.php");
include ( dirname(__FILE__)."/synchronising.php");
include ( dirname(__FILE__)."/textEditing.php");

/*
Plugin name:  Realbig Media
Description:  Плагин для монетизации от RealBig.media
Version:      0.1.26.43
Author:       Realbig Team
License:      GPLv2 or later
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
*/

try {
	/** **************************************************************************************************************** **/
	global $wpdb;
	global $table_prefix;

	$wpPrefix = $table_prefix;
	if (empty($wpPrefix)) {
		$wpPrefix = $wpdb->base_prefix;
	}
	$GLOBALS['wpPrefix'] = $wpPrefix;

	$GLOBALS['excludedPagesChecked'] = false;

	/***************** Cached AD blocks saving ***************************************************************************************/
	if (empty(get_transient('rb_cache_timeout'))&&!empty($_POST)&&!empty($_POST['type'])) {
	    $sanitisedPostType = sanitize_text_field($_POST['type']);
	    if (!empty($sanitisedPostType)&&$sanitisedPostType=="blocksGethering") {
		    include_once (dirname(__FILE__).'/connectTestFile.php');
        }
    }
	/***************** End of cached AD blocks saving ***************************************************************************************/
	$tableForCurrentPluginChecker = $wpdb->get_var('SHOW TABLES LIKE "' . $wpPrefix . 'realbig_plugin_settings"');   //settings for block table checking
	$tableForToken                = $wpdb->get_var('SHOW TABLES LIKE "' . $wpPrefix . 'realbig_settings"');      //settings for token and other

	if (!empty($_POST['statusRefresher'])||empty($tableForToken)||empty($tableForCurrentPluginChecker)) {
		delete_option('realbig_status_gatherer_version');
	}

	$pluginData = get_plugin_data(__FILE__);
	if (!empty($pluginData['Version'])) {
		$GLOBALS['realbigForWP_version'] = $pluginData['Version'];
	} else {
		$GLOBALS['realbigForWP_version'] = '0.1.26.43';
	}
	$lastSuccessVersionGatherer = get_option('realbig_status_gatherer_version');
//	require_once( 'synchronising.php' );
	$statusGatherer             = RFWP_statusGathererConstructor(true);
	/***************** updater code ***************************************************************************************/
	require 'plugin-update-checker/plugin-update-checker.php';
	$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
		'https://github.com/Gildor17/realbigForWP',
		__FILE__,
		'realbigForWP'
	);
	/****************** end of updater code *******************************************************************************/
	/********** checking and creating tables ******************************************************************************/
	if (!empty($_POST['manuallyTableCreating'])) {
//		RFWP_dbTablesCreateFunction( false, true, $wpPrefix, $statusGatherer );
		$GLOBALS['manuallyTableCreatingResult'] = RFWP_manuallyTablesCreation($wpPrefix);
    }

	if ($statusGatherer['realbig_plugin_settings_table'] == false || $statusGatherer['realbig_settings_table'] == false || $lastSuccessVersionGatherer != $GLOBALS['realbigForWP_version']) {
//		$tableForCurrentPluginChecker = $wpdb->get_var( 'SHOW TABLES LIKE "' . $wpPrefix . 'realbig_plugin_settings"' );   //settings for block table checking
//		$tableForToken                = $wpdb->get_var( 'SHOW TABLES LIKE "' . $wpPrefix . 'realbig_settings"' );      //settings for token and other
//        $GLOBALS['problematic_table_status'] = $tableForCurrentPluginChecker;
		$statusGatherer = RFWP_dbTablesCreateFunction($tableForCurrentPluginChecker, $tableForToken, $wpPrefix, $statusGatherer);

		$resultingTableCheck = $wpdb->get_var('SHOW TABLES LIKE "' . $wpPrefix . 'realbig_plugin_settings"');
		if (empty($resultingTableCheck)) {
			$GLOBALS['problematic_table_status'] = true;
		}
	}
	if ( $statusGatherer['realbig_plugin_settings_table'] == true && $statusGatherer['realbig_settings_table'] == true && $statusGatherer['old_tables_removed'] == false ) {
		$statusGatherer = RFWP_dbOldTablesRemoveFunction( $wpPrefix, $statusGatherer );
	}
	if ( $statusGatherer['realbig_plugin_settings_table'] == true && ( $statusGatherer['realbig_plugin_settings_columns'] == false || $lastSuccessVersionGatherer != $GLOBALS['realbigForWP_version'] ) ) {
		$colCheck = $wpdb->get_col( 'SHOW COLUMNS FROM ' . $wpPrefix . 'realbig_plugin_settings' );
		if ( ! empty( $colCheck ) ) {
			$statusGatherer = RFWP_wpRealbigPluginSettingsColomnUpdateFunction( $wpPrefix, $colCheck, $statusGatherer );
		} else {
			$statusGatherer['realbig_plugin_settings_columns'] = false;
		}
	}
	/********** end of checking and creating tables ***********************************************************************/
	/********** token gathering and adding "timeUpdate" field in wp_realbig_settings **************************************/
	$token                 = RFWP_tokenChecking( $wpPrefix );

	$unmarkSuccessfulUpdate      = $wpdb->get_var( 'SELECT optionValue FROM ' . $wpPrefix . 'realbig_settings WHERE optionName = "successUpdateMark"' );
	$jsAutoSynchronizationStatus = $wpdb->get_var( 'SELECT optionValue FROM ' . $wpPrefix . 'realbig_settings WHERE optionName = "jsAutoSyncFails"' );

//	if ( isset( $jsAutoSynchronizationStatus ) && $jsAutoSynchronizationStatus > 4 && ! empty( $token ) && $token != 'no token' && $lastSyncTimeTransient == false ) {
//		$wpOptionsCheckerSyncTime = $wpdb->get_row( $wpdb->prepare( 'SELECT optionValue FROM ' . $wpPrefix . 'realbig_settings WHERE optionName = %s', [ "token_sync_time" ] ) );
//		RFWP_synchronize( $token, ( empty( $wpOptionsCheckerSyncTime ) ? null : $wpOptionsCheckerSyncTime ), true, $GLOBALS['table_prefix'], 'manual' );
//	}
//	/*** enumUpdate */ $resultEnumUpdate = RFWP_updateElementEnumValuesFunction(); /** enumUpdateEnd */
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
		$statusGathererJson = json_encode( $statusGatherer );
		if ( ! empty( $statusGatherer['update_status_gatherer'] ) && $statusGatherer['update_status_gatherer'] == true ) {
			update_option( 'realbig_status_gatherer', $statusGathererJson, 'no' );
		} else {
			add_option( 'realbig_status_gatherer', $statusGathererJson, '', 'no' );
		}
	}
	/********** end of token gathering and adding "timeUpdate" field in wp_realbig_settings *******************************/
	/********** checking requested page for excluding *********************************************************************/
	function RFWP_excludedPageCheck($args) {
		try {
		    if (empty($GLOBALS['excludedPagesChecked'])) {
			    global $wpdb;
			    global $wpPrefix;

			    $excludedPage = false;
			    $mainPageStatus = 0;
			    if (!empty($_SERVER["REDIRECT_URL"])) {
				    $usedUrl = $_SERVER["REDIRECT_URL"];
			    } else {
				    if (!empty($_SERVER["REQUEST_URI"])) {
					    $usedUrl = $_SERVER["REQUEST_URI"];
				    }
			    }
			    $usedUrl = $_SERVER["HTTP_HOST"].$usedUrl;

			    /** Test zone *********/
//		add_action('parse_query', 'mainPageCheck');

//        do_action('posts_selection');
			    /** End of test zone **/

			    if (is_admin()) {
				    $excludedPage = true;
			    } elseif (!empty($usedUrl)) {
				    $excludedMainPageCheck = $wpdb->get_var($wpdb->prepare("SELECT optionValue FROM " . $wpPrefix . "realbig_settings WHERE optionName = %s", ['excludedMainPage']));

				    $homeStatus = false;
				    if (is_home()||is_front_page()) {
					    $homeStatus = true;
				    } else {
					    preg_match_all("~(\/|\\\)([^\/^\\\]+)~", get_home_url(), $m);

					    if (!empty($usedUrl)&&!empty($m)) {
						    if ($usedUrl=="/"||$usedUrl==get_home_url()."/") {
							    $homeStatus = true;
						    } else {
							    foreach ($m[0] AS $item) {
								    if ($usedUrl==$item."/") {
									    $homeStatus = true;
								    }
							    }
						    }
					    }
				    }

				    if ($homeStatus==true) {
					    if (isset($excludedMainPageCheck)) {
						    if ( $excludedMainPageCheck == 1 ) {
							    $mainPageStatus = 1;
						    } elseif ($excludedMainPageCheck == 0) {
							    $mainPageStatus = 2;
						    }
					    }
				    }

				    if ($mainPageStatus == 1) {
					    $excludedPage = true;
				    } elseif ($mainPageStatus == 0) {
					    $excludedPagesCheck = $wpdb->get_var($wpdb->prepare("SELECT optionValue FROM " . $wpPrefix . "realbig_settings WHERE optionName = %s", ['excludedPages']));

					    if (!empty($excludedPagesCheck)) {
						    $excludedDelimiter = 0;
						    $maxCountDelimiter = 0;
						    $excludedPagesCheckArray[1] = explode(",", $excludedPagesCheck);
						    $excludedPagesCheckArray[2] = explode("\n", $excludedPagesCheck);
						    $excludedPagesCheckArray[3] = explode(";", $excludedPagesCheck);
						    $excludedPagesCheckArray[4] = explode(" ", $excludedPagesCheck);

						    foreach ($excludedPagesCheckArray AS $k => $item) {
							    if (count($item) > $maxCountDelimiter) {
								    $maxCountDelimiter = count($item);
								    $excludedDelimiter = $k;
							    }
						    }
						    if ($excludedDelimiter > 0) {
							    $excludedPagesCheckArray = $excludedPagesCheckArray[$excludedDelimiter];
						    } else {
							    $excludedPagesCheckArray = $excludedPagesCheck;
						    }

						    if (!empty($excludedPagesCheckArray)) {
							    foreach ($excludedPagesCheckArray AS $item) {
								    $item = trim($item);

								    if (!empty($item)) {
									    $m = -1;
									    $m = strpos($usedUrl, $item);
//								preg_match("~".$item."~ius", $usedUrl, $m);
									    if (is_integer($m)&&$m > -1) {
										    $excludedPage = true;
									    }
								    }
							    }
						    }
					    }
				    }
			    }
			    if (empty($excludedPage)) {
				    RFWP_js_add();
				    add_filter('the_content', 'RFWP_adBlocksToContentInsertingFunction', 5000);
			    }
			    $GLOBALS['excludedPagesChecked'] = true;
		    }

			return $args;
		} catch (Exception $excludedE) {
			$excludedPage = false;
			return $args;
		}
	}

	add_action('parse_query', 'RFWP_excludedPageCheck', 100);
	/********** end of checking requested page for excluding **************************************************************/
//	add_filter('the_content', 'RFWP_wof', 5001);
//
//    function RFWP_wof($content) {
//        $penyok_stoparik = 0;
//
//        return $content;
//
//    }

	/********** autosync and JS text edit *********************************************************************************/
//	$GLOBALS['wpOptionsCheckerSyncTime'] = $wpOptionsCheckerSyncTime;
	function RFWP_syncFunctionAdd() {
		wp_enqueue_script( 'synchronizationJS',
			dirname(__FILE__).'/synchronizationJS.js',
			array( 'jquery' ),
			$GLOBALS['realbigForWP_version'],
			true );
	}

	function RFWP_syncFunctionAdd1() {
		wp_enqueue_script( 'asyncBlockInserting',
			plugins_url().'/'.basename(__DIR__).'/asyncBlockInserting.js',
			array( 'jquery' ),
			$GLOBALS['realbigForWP_version'],
			false );
	}

	function RFWP_syncFunctionAdd2() {
		wp_enqueue_script( 'readyAdGather',
			plugins_url().'/'.basename(__DIR__).'/readyAdGather.js',
			array( 'jquery' ),
			$GLOBALS['realbigForWP_version'],
			true );
	}

    function RFWP_js_add() {
        add_action('wp_enqueue_scripts', 'RFWP_syncFunctionAdd1', 10);
        if (!empty(RFWP_wp_is_mobile())) {
            $cacheTimeout = get_transient('rb_mobile_cache_timeout');
        } else {
            $cacheTimeout = get_transient('rb_desktop_cache_timeout');
        }
        if (empty($cacheTimeout)) {
            add_action('wp_enqueue_scripts', 'RFWP_syncFunctionAdd2', 11);
        }
    }

	$GLOBALS['stepCounter'] = 'zero';
	$lastSyncTimeTransient = get_transient('realbigPluginSyncAttempt');
	$activeSyncTransient   = get_transient('realbigPluginSyncProcess');
	if (!empty($token)&&$token!='no token'&&empty($activeSyncTransient)&&empty($lastSyncTimeTransient)) {
		if (empty(wp_next_scheduled('rb_cron_hook'))) {
			RFWP_cronAutoGatheringLaunch();
		}
//		else {
//			if (!empty(wp_doing_cron())) {
//				RFWP_cronAutoSyncDelete();
//				RFWP_cronAutoGatheringLaunch();
//            }
//		}
        else {
//            if (!empty(wp_doing_cron())) {
            if (!empty(apply_filters( 'wp_doing_cron', defined( 'DOING_CRON' ) && DOING_CRON ))) {
	            RFWP_cronAutoSyncDelete();
            }
        }
	}
	if (!empty(apply_filters('wp_doing_cron', defined('DOING_CRON')&&DOING_CRON))&&empty($activeSyncTransient)&&empty($lastSyncTimeTransient)) {
//	if (!empty(wp_doing_cron())&&empty($activeSyncTransient)&&empty($lastSyncTimeTransient)) {
		RFWP_autoSync();
	}
	/********** end autosync and JS text edit *****************************************************************************/
	/********** adding AD code in head area *******************************************************************************/
	function RFWP_AD_header_add() {
		global $wpdb;
		$getDomain = 'any.realbig.media';
		$getRotator = 'rotator';

		$getOV = $wpdb->get_results( 'SELECT optionName, optionValue FROM ' . $GLOBALS['wpPrefix'] . 'realbig_settings WHERE optionName IN ("domain","rotator")');
		foreach ($getOV AS $k => $item) {
			if (!empty($item->optionValue)) {
				if ($item->optionName == 'domain') {
					$getDomain = $item->optionValue;
				} else {
					$getRotator = $item->optionValue;
				}
			}
		}

//        $httpsCheck = is_ssl();
//		if (!empty($httpsCheck)) {
//		    $prefix = "HTTPS";
//        } else {
//			$prefix = "HTTP";
//		}
//		$rotatorUrl = $prefix."://".$getDomain."/".$getRotator.".min.js";
		$rotatorUrl = "https://".$getDomain."/".$getRotator.".min.js";
//		$rotatorUrl = "HTTPS://ex.ua";
		$GLOBALS['rotatorUrl'] = $rotatorUrl;

		require_once( 'textEditing.php' );
		$headerParsingResult = RFWP_headerADInsertor();
		if ( $headerParsingResult == true ) {
			?><script type="text/javascript"> rbConfig = {start: performance.now(),rotator:'<?php echo $getRotator ?>'}; </script>
            <script async="async" type="text/javascript" src="//<?php echo $getDomain ?>/<?php echo $getRotator ?>.min.js"></script><?php
		}
	}

	function RFWP_push_head_add() {
		require_once( 'textEditing.php' );
		$headerParsingResult = RFWP_headerPushInsertor();
		if ( $headerParsingResult == true ) {
			?>
            <script charset="utf-8" async
                    src="https://realpush.media/pushJs/<?php echo $GLOBALS['pushCode'] ?>.js"></script>
			<?php
		}
	}

	if (!is_admin()) {
		add_action( 'wp_head', 'RFWP_AD_header_add', 0 );
		$pushStatus = $wpdb->get_results( $wpdb->prepare( 'SELECT optionName, optionValue FROM ' . $wpPrefix . 'realbig_settings WHERE optionName IN (%s, %s)', [
			"pushCode",
			"pushStatus"
		] ), ARRAY_A );
		if (!empty($pushStatus)) {
			if ( $pushStatus[0]['optionName'] == 'pushStatus' ) {
				$pushStatusValue = $pushStatus[0]['optionValue'];
				$pushCode        = $pushStatus[1]['optionValue'];
			} else {
				$pushStatusValue = $pushStatus[1]['optionValue'];
				$pushCode        = $pushStatus[0]['optionValue'];
			}
		}
		if (!empty($pushStatus) && ! empty($pushStatusValue)&& ! empty( $pushCode ) && count( $pushStatus ) == 2 && $pushStatusValue == 1 ) {
			add_action( 'wp_head', 'RFWP_push_head_add', 0 );
			$GLOBALS['pushCode'] = $pushCode;
		}
    }

	/********** end of adding AD code in head area ************************************************************************/
	/********** manual sync ***********************************************************************************************/
//$blocksSettingsTableChecking = $wpdb->query('SELECT id FROM '.$wpPrefix.'realbig_plugin_settings');
	if (strpos($GLOBALS['PHP_SELF'], 'wp-admin')!= false) {
		$wpOptionsCheckerSyncTime = $wpdb->get_row($wpdb->prepare('SELECT optionValue FROM ' . $wpPrefix . 'realbig_settings WHERE optionName = %s', [ "token_sync_time" ]));
		if (!empty($_POST['tokenInput'])) {
			$sanitized_token = sanitize_text_field($_POST['tokenInput']);
		    if (RFWP_tokenMDValidate($sanitized_token)==true) {
			    $sameTokenResult = false;
			    RFWP_synchronize($sanitized_token, (empty($wpOptionsCheckerSyncTime) ? null : $wpOptionsCheckerSyncTime), $sameTokenResult, $wpPrefix, 'manual');
		    } else {
			    $GLOBALS['tokenStatusMessage'] = 'Неверный формат токена';
		    }
//			deactivate_plugins(plugin_basename( __FILE__ ));
		} elseif ( $GLOBALS['token'] == 'no token' ) {
			$GLOBALS['tokenStatusMessage'] = 'Введите токен';
		}
		RFWP_tokenTimeUpdateChecking($GLOBALS['token'], $wpPrefix);
	}
	/********** end of manual sync ****************************************************************************************/
	/************* blocks for text ****************************************************************************************/
//	if ($mainPageStatus == 2||empty($excludedPage)) {
//	if (empty($excludedPage)) {
//		add_filter( 'the_content', 'RFWP_adBlocksToContentInsertingFunction', 5000 );
//	}
	/************* end blocks for text ************************************************************************************/
	/********** using settings in texts ***********************************************************************************/
	function RFWP_adBlocksToContentInsertingFunction($content) {
        if (is_home()||is_front_page()||is_page() || is_single() || is_singular() || is_archive()) {
	        global $wpdb;

	        $rotatorUrl = $GLOBALS['rotatorUrl'];
	        $rotatorResponce = wp_safe_remote_head($rotatorUrl, ['timeout' => 1]);

	        $cachedBlocks = '';
	        if (!is_array($rotatorResponce)||(!empty($rotatorResponce['response']['code'])&&$rotatorResponce['response']['code']!=200)) {
	            ?><script>console.log('using cache')</script><?php
		        if (!empty(RFWP_wp_is_mobile())) {
			        $cachedBlocks = get_posts(['post_type' => 'rb_block_mobile']);
		        } else {
			        $cachedBlocks = get_posts(['post_type' => 'rb_block_desktop']);
		        }
	        }

	        if (!empty($content)) {
		        $fromDb = $wpdb->get_results('SELECT * FROM '.$GLOBALS['wpPrefix'].'realbig_plugin_settings WGPS');
            } else {
		        $fromDb = $wpdb->get_results('SELECT * FROM '.$GLOBALS['wpPrefix'].'realbig_plugin_settings WGPS WHERE setting_type = 3');
	        }
            require_once('textEditing.php');
            $content = RFWP_addIcons($fromDb, $content, 'content', $cachedBlocks);

            return $content;
        } else {
            return $content;
        }
	}
	/*********** end of using settings in texts ***************************************************************************/
	/*********** begin of token input area ********************************************************************************/
	function RFWP_my_plugin_action_links($links) {
		$links = array_merge( array( '<a href="' . esc_url( admin_url( '/admin.php?page=realbigForWP%2FrealbigForWP.php' ) ) . '">' . __( 'Settings', 'textdomain' ) . '</a>' ), $links );
		return $links;
	}

	add_action('plugin_action_links_' . plugin_basename( __FILE__ ), 'RFWP_my_plugin_action_links');
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
//		add_menu_page( 'Your code sending configuration', 'realBIG', 'administrator', __FILE__, 'RFWP_TokenSync', get_site_url().'/wp-content/plugins/realbigForWP/assets/realbig_plugin_hover.png' );
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
//		$postsGather = $wpdb->get_results('SELECT post_title FROM '.$wpPrefix.'posts WHERE post_type IN ("rb_block_desktop","rb_block_mobile")');
		$postsGatherDesktop = $wpdb->get_results('SELECT post_title FROM '.$wpPrefix.'posts WHERE post_type IN ("rb_block_desktop")');
		$postsGatherMobile  = $wpdb->get_results('SELECT post_title FROM '.$wpPrefix.'posts WHERE post_type IN ("rb_block_mobile" )');

		try {
		    $rbSettings = $wpdb->get_results('SELECT optionName, optionValue, timeUpdate FROM ' . $GLOBALS["wpPrefix"] . 'realbig_settings WHERE optionName IN ("deactError","domain","excludedMainPage","excludedPages","pushStatus")', ARRAY_A);

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
                    } elseif ($item['optionName']=='pushStatus') {
			            if (!empty($item["optionValue"])) {
				            $pushStatus = 'Да';
			            } else {
				            $pushStatus = 'Нет';
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
                    <br>
		            <?php if (!empty($GLOBALS['problematic_table_status'])): ?>
                        <label for="manuallyTableCreating">создать таблицу вручную</label>
                        <input type="checkbox" name="manuallyTableCreating" id="manuallyTableCreatingId">
		            <?php endif; ?>
		            <?php submit_button( 'Синхронизировать', 'primary', 'saveTokenButton' ) ?>
		            <?php if ( ! empty( $GLOBALS['tokenStatusMessage'] ) ): ?>
                        <div name="rezultDiv" style="font-size: 16px"><?php echo $GLOBALS['tokenStatusMessage'] ?></div>
		            <?php endif; ?>
                </form>
            </div>
            <div class="separated-blocks">
                <div class="squads-blocks">
                    <div>Надписи ниже нужны для тестировки</div>
                    <div>Статус соединения
                        1: <?php echo( ! empty( $GLOBALS['connection_request_rezult_1'] ) ? $GLOBALS['connection_request_rezult_1'] : 'empty' ) ?></div>
                    <div>Статус соединения
                        общий: <?php echo( ! empty( $GLOBALS['connection_request_rezult'] ) ? $GLOBALS['connection_request_rezult'] : 'empty' ) ?></div>
	                <?php if (!empty($GLOBALS['manuallyTableCreatingResult'])): ?>
                        <div>Table creating: <?php echo $GLOBALS['manuallyTableCreatingResult']; ?></div>
	                <?php endif; ?>
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
	            <?php endif; ?>
            </div>
        </div>
        <!--        <div style="width: 100px; height: 20px; border: 1px solid black; background-color: royalblue"></div>-->
		<?php
	}
	/************ end of token input area *********************************************************************************/
}
catch (Exception $ex)
{
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
}
catch (Error $er)
{
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