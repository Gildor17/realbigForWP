<?php

//include_once( ABSPATH . 'wp-load.php' );
//include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
//include_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
//include ( ABSPATH . "wp-content/plugins/realbigForWP/update.php");
//include ( ABSPATH . "wp-content/plugins/realbigForWP/synchronising.php");
//include ( ABSPATH . "wp-content/plugins/realbigForWP/textEditing.php");

//include ( dirname(__FILE__).'/../../../wp-load.php' );
include_once ( dirname(__FILE__)."/../../../wp-admin/includes/plugin.php" );
include_once ( dirname(__FILE__)."/../../../wp-admin/includes/upgrade.php" );
include ( dirname(__FILE__)."/update.php");
include ( dirname(__FILE__)."/synchronising.php");
include ( dirname(__FILE__)."/textEditing.php");

/*
Plugin name:  Realbig For WordPress
Description:  Плагин для монетизации от RealBig.media
Version:      0.1.26.19
Author:       Realbig Team
License:      GPLv2 or later
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
*/

try {
	if (!defined("ABSPATH")) { exit;}
	/** **************************************************************************************************************** **/
	global $wpdb;
	global $table_prefix;

	$wpPrefix = $table_prefix;
	if ( empty( $wpPrefix ) ) {
		$wpPrefix = $wpdb->base_prefix;
	}
	$GLOBALS['wpPrefix'] = $wpPrefix;

	$tableForCurrentPluginChecker = $wpdb->get_var( 'SHOW TABLES LIKE "' . $wpPrefix . 'realbig_plugin_settings"' );   //settings for block table checking
	$tableForToken                = $wpdb->get_var( 'SHOW TABLES LIKE "' . $wpPrefix . 'realbig_settings"' );      //settings for token and other

	if (!empty($_POST['statusRefresher'])||empty($tableForToken)||empty($tableForCurrentPluginChecker)) {
		delete_option( 'realbig_status_gatherer_version' );
	}

	$pluginData = get_plugin_data( __FILE__ );
	if ( ! empty( $pluginData['Version'] ) ) {
		$GLOBALS['realbigForWP_version'] = $pluginData['Version'];
	} else {
		$GLOBALS['realbigForWP_version'] = '0.1.26.19';
	}
	$lastSuccessVersionGatherer = get_option( 'realbig_status_gatherer_version' );
//	require_once( 'synchronising.php' );
	$statusGatherer             = RFWP_statusGathererConstructor( true );
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

	if ( $statusGatherer['realbig_plugin_settings_table'] == false || $statusGatherer['realbig_settings_table'] == false || $lastSuccessVersionGatherer != $GLOBALS['realbigForWP_version'] ) {
//		$tableForCurrentPluginChecker = $wpdb->get_var( 'SHOW TABLES LIKE "' . $wpPrefix . 'realbig_plugin_settings"' );   //settings for block table checking
//		$tableForToken                = $wpdb->get_var( 'SHOW TABLES LIKE "' . $wpPrefix . 'realbig_settings"' );      //settings for token and other
//        $GLOBALS['problematic_table_status'] = $tableForCurrentPluginChecker;
		$statusGatherer = RFWP_dbTablesCreateFunction( $tableForCurrentPluginChecker, $tableForToken, $wpPrefix, $statusGatherer );

		$resultingTableCheck = $wpdb->get_var( 'SHOW TABLES LIKE "' . $wpPrefix . 'realbig_plugin_settings"' );
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
	$lastSyncTimeTransient = get_transient( 'realbigPluginSyncAttempt' );

	$unmarkSuccessfulUpdate      = $wpdb->get_var( 'SELECT optionValue FROM ' . $wpPrefix . 'realbig_settings WHERE optionName = "successUpdateMark"' );
	$jsAutoSynchronizationStatus = $wpdb->get_var( 'SELECT optionValue FROM ' . $wpPrefix . 'realbig_settings WHERE optionName = "jsAutoSyncFails"' );
	if ( isset( $jsAutoSynchronizationStatus ) && $jsAutoSynchronizationStatus > 4 && ! empty( $token ) && $token != 'no token' && $lastSyncTimeTransient == false ) {
		$wpOptionsCheckerSyncTime = $wpdb->get_row( $wpdb->prepare( 'SELECT optionValue FROM ' . $wpPrefix . 'realbig_settings WHERE optionName = %s', [ "token_sync_time" ] ) );
		RFWP_synchronize( $token, ( empty( $wpOptionsCheckerSyncTime ) ? null : $wpOptionsCheckerSyncTime ), true, $GLOBALS['table_prefix'], 'manual' );
	}
//	/*** enumUpdate */ $resultEnumUpdate = RFWP_updateElementEnumValuesFunction(); /** enumUpdateEnd */
	if ( $statusGatherer['realbig_plugin_settings_table'] == true && ( $statusGatherer['element_column_values'] == false || $lastSuccessVersionGatherer != $GLOBALS['realbigForWP_version'] ) ) {
		/** enumUpdate */
		$statusGatherer = RFWP_updateElementEnumValuesFunction( $wpPrefix, $statusGatherer );
		/** enumUpdateEnd */
	}
	if ( ! empty( $statusGatherer ) ) {
		if ( ! in_array( false, $statusGatherer ) ) {
			if ( ! empty( $lastSuccessVersionGatherer ) ) {
				update_option( 'realbig_status_gatherer_version', $GLOBALS['realbigForWP_version'], 'no' );
			} else {
				add_option( 'realbig_status_gatherer_version', $GLOBALS['realbigForWP_version'], '', 'no' );
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
	try {
		$excludedPagesCheck = $wpdb->get_var($wpdb->prepare("SELECT optionValue FROM " . $wpPrefix . "realbig_settings WHERE optionName = %s", ['excludedPages']));
		$excludedPage = false;
		if (!empty($_SERVER["REDIRECT_URL"])) {
			$usedUrl = $_SERVER["REDIRECT_URL"];
		} else {
			if (!empty($_SERVER["REQUEST_URI"])) {
				$usedUrl = $_SERVER["REQUEST_URI"];
			}
		}

		if (!empty($excludedPagesCheck)&&!is_admin()&&!empty($usedUrl)) {
			$excludedPagesCheckArray = explode(",", $excludedPagesCheck);
			if (!empty($excludedPagesCheckArray)) {
				foreach ($excludedPagesCheckArray AS $item) {
					$item = trim($item);
					if (!empty($item)) {
						preg_match("~".$item."~", $usedUrl, $m);
						if (count($m) > 0) {
							$excludedPage = true;
						}
					}
				}
			}
		} elseif (is_admin()) {
			$excludedPage = true;
		}
	} catch (Exception $excludedE) {
		$excludedPage = false;
	}
	/********** end of checking requested page for excluding **************************************************************/
	/********** autosync and JS text edit *********************************************************************************/
//	$GLOBALS['wpOptionsCheckerSyncTime'] = $wpOptionsCheckerSyncTime;
	function RFWP_syncFunctionAdd() {
		wp_enqueue_script( 'synchronizationJS',
			plugins_url() . '/realbigForWP/synchronizationJS.js',
			array( 'jquery' ),
			$GLOBALS['realbigForWP_version'],
			true );
	}

	function RFWP_syncFunctionAdd1() {
		wp_enqueue_script( 'asyncBlockInserting',
			plugins_url() . '/realbigForWP/asyncBlockInserting.js',
			array( 'jquery' ),
			$GLOBALS['realbigForWP_version'],
			false );
	}

	add_action( 'wp_enqueue_scripts', 'RFWP_syncFunctionAdd1', 100 );
	$GLOBALS['stepCounter'] = 'zero';
	if ( ! empty( $token ) && $token != 'no token' && $lastSyncTimeTransient == false ) {
		add_action( 'wp_enqueue_scripts', 'RFWP_syncFunctionAdd', 101 );
		$activeSyncChecker      = get_transient( 'realbigSyncChecker' );
		$GLOBALS['stepCounter'] = '1st';
		if ( isset( $jsAutoSynchronizationStatus ) && empty( $activeSyncChecker ) ) {
			$GLOBALS['stepCounter'] = '2nd_1';
			set_transient( 'realbigSyncChecker', 'active', 10 );
			if ( ! empty( $unmarkSuccessfulUpdate ) && $unmarkSuccessfulUpdate == 'error' ) {
				$GLOBALS['stepCounter'] = '3rd_1';
				$wpdb->update( $wpPrefix . 'realbig_settings', [
					'optionName'  => 'jsAutoSyncFails',
					'optionValue' => intval( $jsAutoSynchronizationStatus ) + 1
				], [ 'optionName' => 'jsAutoSyncFails' ] );
			} elseif ( ! empty( $unmarkSuccessfulUpdate ) && $unmarkSuccessfulUpdate == 'success' ) {
				$GLOBALS['stepCounter'] = '3rd_2';
				$wpdb->update( $wpPrefix . 'realbig_settings', [
					'optionName'  => 'jsAutoSyncFails',
					'optionValue' => 0
				], [ 'optionName' => 'jsAutoSyncFails' ] );
				$wpdb->update( $wpPrefix . 'realbig_settings', [ 'optionValue' => 'error' ], [ 'optionName' => 'successUpdateMark' ] );
			} elseif ( empty( $unmarkSuccessfulUpdate ) ) {
				$GLOBALS['stepCounter'] = '3rd_3';
				$wpdb->update( $wpPrefix . 'realbig_settings', [
					'optionName'  => 'jsAutoSyncFails',
					'optionValue' => 0
				], [ 'optionName' => 'jsAutoSyncFails' ] );
				$wpdb->insert( $wpPrefix . 'realbig_settings', [
					'optionName'  => 'successUpdateMark',
					'optionValue' => 'error'
				] );
			}
		} elseif ( ! isset( $jsAutoSynchronizationStatus ) ) {
			$GLOBALS['stepCounter'] = '2nd_2';
			$wpdb->insert( $wpPrefix . 'realbig_settings', [ 'optionName' => 'jsAutoSyncFails', 'optionValue' => 0 ] );
		}
	}
	/********** end autosync and JS text edit *****************************************************************************/
	/********** adding AD code in head area *******************************************************************************/
	add_action( 'wp_head', 'RFWP_AD_header_add', 0 );

	function RFWP_AD_header_add() {
		global $wpdb;
		$getDomain = $wpdb->get_var( 'SELECT optionValue FROM ' . $GLOBALS['wpPrefix'] . 'realbig_settings WHERE optionName = "domain"' );
		require_once( 'textEditing.php' );
		$headerParsingResult = RFWP_headerADInsertor();
		if ( $headerParsingResult == true ) {
			if ( ! empty( $getDomain ) && $getDomain != '' ) {
				?>
                <script type="text/javascript"> rbConfig = {start: performance.now()}; </script>
                <script async="async" type="text/javascript" src="//<?php echo $getDomain ?>/rotator.min.js"></script>
				<?php
			} else {
				?>
                <script type="text/javascript"> rbConfig = {start: performance.now()}; </script>
                <script async="async" type="text/javascript" src="//any.realbig.media/rotator.min.js"></script>
				<?php
			}
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

	$pushStatus = $wpdb->get_results( $wpdb->prepare( 'SELECT optionName, optionValue FROM ' . $wpPrefix . 'realbig_settings WHERE optionName IN (%s, %s)', [
		"pushCode",
		"pushStatus"
	] ), ARRAY_A );
	if ( ! empty( $pushStatus ) ) {
		if ( $pushStatus[0]['optionName'] == 'pushStatus' ) {
			$pushStatusValue = $pushStatus[0]['optionValue'];
			$pushCode        = $pushStatus[1]['optionValue'];
		} else {
			$pushStatusValue = $pushStatus[1]['optionValue'];
			$pushCode        = $pushStatus[0]['optionValue'];
		}
	}
	if ( ! empty( $pushStatus ) && ! empty( $pushStatusValue ) && ! empty( $pushCode ) && count( $pushStatus ) == 2 && $pushStatusValue == 1 ) {
		add_action( 'wp_head', 'RFWP_push_head_add', 0 );
		$GLOBALS['pushCode'] = $pushCode;
	}
	/********** end of adding AD code in head area ************************************************************************/
	/********** manual sync ***********************************************************************************************/
//$blocksSettingsTableChecking = $wpdb->query('SELECT id FROM '.$wpPrefix.'realbig_plugin_settings');
	if ( strpos( $GLOBALS['PHP_SELF'], 'wp-admin' ) != false ) {
		$wpOptionsCheckerSyncTime = $wpdb->get_row( $wpdb->prepare( 'SELECT optionValue FROM ' . $wpPrefix . 'realbig_settings WHERE optionName = %s', [ "token_sync_time" ] ) );
		if ( ! empty( $_POST['tokenInput'] ) ) {
			$sameTokenResult = false;
			RFWP_synchronize( $_POST['tokenInput'], ( empty( $wpOptionsCheckerSyncTime ) ? null : $wpOptionsCheckerSyncTime ), $sameTokenResult, $wpPrefix, 'manual' );
//			deactivate_plugins(plugin_basename( __FILE__ ));
		} elseif ( $GLOBALS['token'] == 'no token' ) {
			$GLOBALS['tokenStatusMessage'] = 'Введите токен';
		}
		RFWP_tokenTimeUpdateChecking( $GLOBALS['token'], $wpPrefix );
	}
	/********** end of manual sync ****************************************************************************************/
	/************* blocks for text ****************************************************************************************/
	if (empty($excludedPage)) {
		add_filter( 'the_content', 'RFWP_adBlocksToContentInsertingFunction', 5000 );
	}
	/************* end blocks for text ************************************************************************************/
	/********** using settings in texts ***********************************************************************************/
	function RFWP_adBlocksToContentInsertingFunction($content) {
	    if (!empty($content)) {
		    if ( is_page() || is_single() || is_singular() || is_archive() ) {
			    global $wpdb;

			    $fromDb = $wpdb->get_results( 'SELECT * FROM ' . $GLOBALS['wpPrefix'] . 'realbig_plugin_settings WGPS' );
			    require_once( 'textEditing.php' );
			    $content = RFWP_addIcons( $fromDb, $content, 'content' );

			    return $content;
		    } else {
			    return $content;
		    }
        } else {
		    return $content;
        }
	}
	/*********** end of using settings in texts ***************************************************************************/
	/*********** begin of token input area ********************************************************************************/
	function RFWP_my_plugin_action_links( $links ) {
		$links = array_merge( array( '<a href="' . esc_url( admin_url( '/admin.php?page=realbigForWP%2FrealbigForWP.php' ) ) . '">' . __( 'Settings', 'textdomain' ) . '</a>' ), $links );

		return $links;
	}

	add_action( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'RFWP_my_plugin_action_links' );
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	if ( is_admin() ) {
		add_action( 'admin_menu', 'RFWP_my_pl_settings_menu_create' );
	}
	function RFWP_my_pl_settings_menu_create() {
		if ( strpos( $_SERVER['REQUEST_URI'], 'page=realbigForWP' ) ) {
			add_menu_page( 'Your code sending configuration', 'realBIG', 'administrator', __FILE__, 'RFWP_TokenSync', get_site_url() . '/wp-content/plugins/realbigForWP/assets/realbig_plugin_hover.png' );
		} else {
			add_menu_page( 'Your code sending configuration', 'realBIG', 'administrator', __FILE__, 'RFWP_TokenSync', get_site_url() . '/wp-content/plugins/realbigForWP/assets/realbig_plugin_standart.png' );
		}
//		add_menu_page( 'Your code sending configuration', 'realBIG', 'administrator', __FILE__, 'RFWP_TokenSync', get_site_url().'/wp-content/plugins/realbigForWP/assets/realbig_plugin_hover.png' );
		add_action( 'admin_init', 'RFWP_register_mysettings' );
	}

	function RFWP_register_mysettings() {
		register_setting( 'sending_zone', 'token_value_input' );
		register_setting( 'sending_zone', 'token_value_send' );
	}

	function RFWP_TokenSync() {
	    $sign = 0;
	    try {
		    $deacErrorGather = $GLOBALS['wpdb']->get_row('SELECT optionValue, timeUpdate FROM ' . $GLOBALS["wpPrefix"] . 'realbig_settings WHERE optionName = "deactError"', ARRAY_A);

		    if (!empty($deacErrorGather)) {
			    $deacError = $deacErrorGather["optionValue"];
			    $deacTime = $deacErrorGather["timeUpdate"];
            }
	    } catch (Exception $e) {
	        $deacError = "error gathering error";
	        $deacTime = "error gathering error";
        }
		?>
        <div class="wrap col-md-12">
            <form method="post" name="tokenForm" id="tokenFormId">
                <label><span style="font-size: 16px">Токен</span><br/>
                    <input name="tokenInput" id="tokenInputId" value="<?php echo $GLOBALS['token'] ?>"
                           style="min-width: 280px"
                           required>
                    <label style="font-size: 16px; margin-left: 10px; color: <?php echo $GLOBALS['statusColor'] ?> ">Время
                        последней синхронизации: <?php echo $GLOBALS['tokenTimeUpdate'] ?></label>
                </label>
                <br>
                <label for="statusRefresher">обновить проверку</label>
                <input type="checkbox" name="statusRefresher" id="statusRefresher">
                <br>
<!--	            --><?// if (empty($GLOBALS['problematic_table_status'])): ?>
	            <? if (!empty($GLOBALS['problematic_table_status'])): ?>
                    <label for="manuallyTableCreating">создать таблицу вручную</label>
                    <input type="checkbox" name="manuallyTableCreating" id="manuallyTableCreatingId">
	            <? endif; ?>
				<?php submit_button( 'Синхронизировать', 'primary', 'saveTokenButton' ) ?>
				<?php if ( ! empty( $GLOBALS['tokenStatusMessage'] ) ): ?>
                    <div name="rezultDiv" style="font-size: 16px"><?php echo $GLOBALS['tokenStatusMessage'] ?></div>
				<?php endif; ?>
            </form>
            <br>

            <div>Надписи ниже нужны для тестировки</div>
            <div>Статус соединения
                1: <?php echo( ! empty( $GLOBALS['connection_request_rezult_1'] ) ? $GLOBALS['connection_request_rezult_1'] : 'empty' ) ?></div>
            <div>Статус соединения
                общий: <?php echo( ! empty( $GLOBALS['connection_request_rezult'] ) ? $GLOBALS['connection_request_rezult'] : 'empty' ) ?></div>
            <? if (!empty($GLOBALS['manuallyTableCreatingResult'])): ?>
                <div>Table creating: <?php echo $GLOBALS['manuallyTableCreatingResult']; ?></div>
            <? endif; ?>
            <? if (!empty($deacErrorGather)): ?>
                <div style="border: 1px solid grey; width: max-content; margin-top: 20px; padding: 5px">
                    Инфо о последней деактивации:
                    <div>
                        Update Time: <?= $deacTime?> <br>
                        Error: <?= $deacError?> <br>
                    </div>
                </div>
            <? endif; ?>
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

	deactivate_plugins(plugin_basename( __FILE__ ));
    ?><div style="margin-left: 200px; border: 3px solid red"><?php echo $er; ?></div><?php
}