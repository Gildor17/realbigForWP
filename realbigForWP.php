<?php

include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
include_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
include ( ABSPATH . "wp-content/plugins/realbigForWP/update.php");
include ( ABSPATH . "wp-content/plugins/realbigForWP/synchronising.php");

/**
 * Created by PhpStorm.
 * User: user
 * Date: 2018-07-03
 * Time: 10:34
 */

/*
Plugin name:  Realbig For WordPress
Description:  Реалбиговский плагин для вордпреса. Для полного описания перейдите по ссылке: <a href="https://github.com/Gildor17/realbigFoWP/blob/master/README.MD" target="_blank">https://github.com/Gildor17/realbigFoWP/blob/master/README.MD</a>
Version:      0.1.16a
Author:       Gildor
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
*/

try
{
	/** **************************************************************************************************************** **/
	global $wpdb;
	global $table_prefix;
	/***************** updater code ***************************************************************************************/
	require 'plugin-update-checker/plugin-update-checker.php';
	$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
		'https://github.com/Gildor17/realbigForWP',
		__FILE__,
		'realbigForWP'
	);
	/****************** end of updater code *******************************************************************************/
	$GLOBALS['realbigForWP_version'] = '0.1.16a';
	/********** checking and creating tables ******************************************************************************/
	$wpPrefix = $wpdb->base_prefix;
	if ( empty( $wpPrefix ) ) {
		$wpPrefix = $table_prefix;
	}

	try {
		$tableForCurrentPluginChecker = $wpdb->get_var( 'SHOW TABLES LIKE "' . $wpPrefix . 'realbig_plugin_settings"' );   //settings for block table checking
		$tableForToken                = $wpdb->get_var( 'SHOW TABLES LIKE "' . $wpPrefix . 'realbig_settings"' );      //settings for token and other
//	$pluginActivityChecker        = is_plugin_active( 'realbigForWP/realbigForWP.php' );     //plugin status (active or not)
	} catch ( Exception $e ) {
	}

	dbTablesCreateFunction( $tableForCurrentPluginChecker, $tableForToken, $wpPrefix );
	dbOldTablesRemoveFunction( $wpPrefix );
	/********** end of checking and creating tables ***********************************************************************/

	$token = tokenChecking( $wpPrefix );
	/****************** autosync ******************************************************************************************/
	if ( ! empty( $token ) && $token != 'no token' ) {
		try {
			$wpOptionsCheckerSyncTime = $wpdb->get_row( $wpdb->prepare( 'SELECT optionValue FROM ' . $wpPrefix . 'realbig_settings WHERE optionName = %s', [ "token_sync_time" ] ) );
//	    $syncIterations = $wpdb->get_var('SELECT optionValue FROM '.$wpPrefix.'realbig_settings WHERE optionName = "syncRequest"');
//	    $wpdb->update($wpPrefix.'realbig_settings', ['optionValue'=> $syncIterations + 1], ['optionName'=>'syncRequest']);
			if ( ! empty( $wpOptionsCheckerSyncTime ) ) {
				$lastSyncTime = get_object_vars( $wpOptionsCheckerSyncTime );
			} else {
				$lastSyncTime = null;
			}

			if ( ! empty( $lastSyncTime ) ) {
				$timeDif = time() - intval( $lastSyncTime['optionValue'] );
				if ( $timeDif > 300 ) {
					$sameTokenResult = true;
					synchronize( $token, $wpOptionsCheckerSyncTime, $sameTokenResult, $wpPrefix );
					tokenTimeUpdateChecking( $GLOBALS['token'], $wpPrefix );
				}
			}
		} catch ( Exception $e ) {
		}
	}
	/****************** end autosync **************************************************************************************/

	/********** adding AD code in head area *******************************************************************************/
	add_action( 'wp_head', 'AD_func_add', 1 );
	function AD_func_add() {
		require_once( 'textEditing.php' );
		$headerParsingResult = headerADInsertor();
		if ( $headerParsingResult == true ) {
			?>
            <script type="text/javascript"> rbConfig = {start: performance.now()}; </script>
            <script async="async" type="text/javascript" src="//any.realbig.media/rotator.min.js"></script>
			<?php
		}
	}

	/********** end of adding AD code in head area ************************************************************************/

//$blocksSettingsTableChecking = $wpdb->query('SELECT id FROM '.$wpPrefix.'realbig_plugin_settings');
	if ( strpos( $GLOBALS['PHP_SELF'], 'wp-admin' ) != false ) {
		if ( ! empty( $_POST['tokenInput'] ) ) {
			$sameTokenResult = false;
			synchronize( $_POST['tokenInput'], ( empty( $wpOptionsCheckerSyncTime ) ? null : $wpOptionsCheckerSyncTime ), $sameTokenResult, $wpPrefix );
		} elseif ( $GLOBALS['token'] == 'no token' ) {
			$GLOBALS['tokenStatusMessage'] = 'Введите токен';
		}
		tokenTimeUpdateChecking( $GLOBALS['token'], $wpPrefix );
	}
	/************* blocks for text ****************************************************************************************/
	$fromDb = $wpdb->get_results( 'SELECT setting_type, `text`, element, directElement, elementPosition, elementPlace, firstPlace, elementCount, elementStep FROM ' . $wpPrefix . 'realbig_plugin_settings WGPS' );
	/************* end blocks for text ************************************************************************************/
	add_filter( 'the_content', 'pathToIcons', 102 );
	/********** using settings in texts ***********************************************************************************/
	function pathToIcons( $content ) {
		$fromDb = $GLOBALS['fromDb'];
		require_once( 'textEditing.php' );
		$setNum  = 1;
		$content = addIcons( $fromDb, $content );
		return $content;
	}
	/*********** end of using settings in texts ***************************************************************************/
//function adminPagesTest() {
//    global $wpdb;
//    $adminChecker = $wpdb->get_var('SELECT optionValue FROM '.$wpPrefix.'realbig_settings WHERE optionName = "testAdminRow"');
//	$adminChecker = $adminChecker + 1;
//    $wpdb->update($wpPrefix.'realbig_settings', ['optionValue'=> $adminChecker], ['optionName'=>'testAdminRow']);
//}
	/*********** begin of token input area ********************************************************************************/
	function my_plugin_action_links( $links ) {
		$links = array_merge( array( '<a href="' . esc_url( admin_url( '/admin.php?page=realbigForWP%2FrealbigForWP.php' ) ) . '">' . __( 'Settings', 'textdomain' ) . '</a>' ), $links );

		return $links;
	}

	add_action( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'my_plugin_action_links' );

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	if ( is_admin() ) {
		add_action( 'admin_menu', 'my_pl_settings_menu_create' );
	}
	function my_pl_settings_menu_create() {
		add_menu_page( 'Your code sending configuration', 'Реалбиг плагин настройки', 'administrator', __FILE__, 'TokenSync' );
		add_action( 'admin_init', 'register_mysettings' );
	}

	function register_mysettings() {
		register_setting( 'sending_zone', 'token_value_input' );
		register_setting( 'sending_zone', 'token_value_send' );
	}

	function TokenSync() {
		?>
        <div class="wrap col-md-12">
            <form method="post" name="tokenForm" id="tokenFormId">
                <label><span style="font-size: 16px">Токен</span><br/>
                    <input name="tokenInput" id="tokenInputId" value="<?= $GLOBALS['token'] ?>" style="min-width: 280px"
                           required>
                    <label style="font-size: 16px; margin-left: 10px; color: <?= $GLOBALS['statusColor'] ?> ">Время
                        последней синхронизации: <?= $GLOBALS['tokenTimeUpdate'] ?></label>
                </label>
				<?php submit_button( 'Синхронизировать', 'primary', 'saveTokenButton' ) ?>
				<?php if ( ! empty( $GLOBALS['tokenStatusMessage'] ) ): ?>
                    <div name="rezultDiv" style="font-size: 16px"><?= $GLOBALS['tokenStatusMessage'] ?></div>
				<?php endif; ?>
            </form>
            <br>
            <div>Надписи ниже нужны для тестировки</div>
            <div>Статус соединения
                1: <?= ( ! empty( $GLOBALS['connection_request_rezult_1'] ) ? $GLOBALS['connection_request_rezult_1'] : 'empty' ) ?></div>
            <div>Статус соединения
                общий: <?= ( ! empty( $GLOBALS['connection_request_rezult'] ) ? $GLOBALS['connection_request_rezult'] : 'empty' ) ?></div>
        </div>
		<?php
	}
	/************ end of token input area *********************************************************************************/
}
catch (Exception $e)
{
    deactivate_plugins('realbigForWP');
}