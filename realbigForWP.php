<?php

include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
include_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
include ( ABSPATH . "wp-content/plugins/realbigForWP/update.php");
include ( ABSPATH . "wp-content/plugins/realbigForWP/synchronising.php");
include ( ABSPATH . "wp-content/plugins/realbigForWP/textEditing.php");

/**
 * Created by PhpStorm.
 * User: user
 * Date: 2018-07-03
 * Time: 10:34
 */

/*
Plugin name:  Realbig For WordPress
Description:  Реалбиговский плагин для вордпреса. Для полного описания перейдите по ссылке: <a href="https://github.com/Gildor17/realbigFoWP/blob/master/README.MD" target="_blank">https://github.com/Gildor17/realbigFoWP/blob/master/README.MD</a>
Version:      0.1.23a
Author:       Gildor
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
*/

try
{
	/** **************************************************************************************************************** **/
	global $wpdb;
	global $table_prefix;
	$GLOBALS['realbigForWP_version'] = '0.1.23a';
	$lastSuccessVersionGatherer = get_option('realbig_status_gatherer_version');

	$statusGatherer = statusGathererConstructor(true);
	/***************** updater code ***************************************************************************************/
	require 'plugin-update-checker/plugin-update-checker.php';
	$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
		'https://github.com/Gildor17/realbigForWP',
		__FILE__,
		'realbigForWP'
	);
	/****************** end of updater code *******************************************************************************/
	/********** checking and creating tables ******************************************************************************/
	$wpPrefix = $table_prefix;
	if ( empty( $wpPrefix ) )
	{
		$wpPrefix = $wpdb->base_prefix;
	}
	$GLOBALS['wpPrefix'] = $wpPrefix;

	if ($statusGatherer['realbig_plugin_settings_table']==false||$statusGatherer['realbig_settings_table']==false||$lastSuccessVersionGatherer!=$GLOBALS['realbigForWP_version'])
	{
		$tableForCurrentPluginChecker = $wpdb->get_var('SHOW TABLES LIKE "'.$wpPrefix.'realbig_plugin_settings"');   //settings for block table checking
		$tableForToken = $wpdb->get_var('SHOW TABLES LIKE "'.$wpPrefix.'realbig_settings"');      //settings for token and other

		$statusGatherer = dbTablesCreateFunction( $tableForCurrentPluginChecker, $tableForToken, $wpPrefix, $statusGatherer);
	}
	if ($statusGatherer['realbig_plugin_settings_table']==true&&$statusGatherer['realbig_settings_table']==true&&$statusGatherer['old_tables_removed']==false)
    {
		$statusGatherer = dbOldTablesRemoveFunction( $wpPrefix, $statusGatherer);
	}
	if ($statusGatherer['realbig_plugin_settings_columns']==false||$lastSuccessVersionGatherer!=$GLOBALS['realbigForWP_version'])
	{
		$colCheck = $wpdb->get_col('SHOW COLUMNS FROM '.$wpPrefix.'realbig_plugin_settings');
		if (!empty($colCheck))
		{
			$statusGatherer = wpRealbigPluginSettingsColomnUpdateFunction($wpPrefix, $colCheck, $statusGatherer);
		}
		else
		{
			$statusGatherer['realbig_plugin_settings_columns'] = false;
		}
    }
	/********** end of checking and creating tables ***********************************************************************/
	/********** token gathering and adding "timeUpdate" field in wp_realbig_settings **************************************/
	$token                 = tokenChecking( $wpPrefix );
	$lastSyncTimeTransient = get_transient('realbigPluginSyncAttempt');

//	/*** enumUpdate */ $resultEnumUpdate = updateElementEnumValuesFunction(); /** enumUpdateEnd */
    if ($statusGatherer['element_column_values']==false||$lastSuccessVersionGatherer!=$GLOBALS['realbigForWP_version'])
    {
	    /** enumUpdate */ $statusGatherer = updateElementEnumValuesFunction($wpPrefix, $statusGatherer); /** enumUpdateEnd */
    }
    if (!empty($statusGatherer))
    {
        if (!in_array(false, $statusGatherer)) {
            if (!empty($lastSuccessVersionGatherer)) {
                update_option('realbig_status_gatherer_version', $GLOBALS['realbigForWP_version'], 'no');
            } else {
                add_option('realbig_status_gatherer_version', $GLOBALS['realbigForWP_version'], '', 'no');
            }
        }
	    $statusGathererJson = json_encode($statusGatherer);
	    if (!empty($statusGatherer['update_status_gatherer'])&&$statusGatherer['update_status_gatherer']==true) {
		    update_option('realbig_status_gatherer', $statusGathererJson, 'no');
	    }
	    else
	    {
		    add_option('realbig_status_gatherer', $statusGathererJson, '', 'no');
	    }
    }
	/********** end of token gathering and adding "timeUpdate" field in wp_realbig_settings *******************************/
	/****************** autosync ******************************************************************************************/
//	$GLOBALS['wpOptionsCheckerSyncTime'] = $wpOptionsCheckerSyncTime;
	function syncFunctionAdd($sc)
	{
	    $testVar = $sc;
		wp_enqueue_script( 'synchronizationJS',
			plugins_url(). '/realbigForWP/synchronizationJS.js',
			array('jquery'),
			false,
			true);
	}
	function syncFunctionAdd1($sc)
	{
		wp_enqueue_script( 'jsFileForTestPurposes1',
			plugins_url(). '/realbigForWP/jsFileForTestPurposes1.js',
			array('jquery'),
			false,
			false);
	}
	add_action('wp_enqueue_scripts', 'syncFunctionAdd1', 200);
	if ( ! empty( $token ) && $token != 'no token' && $lastSyncTimeTransient == false)
	{
		add_action('wp_enqueue_scripts', 'syncFunctionAdd', 201);
	}
	/****************** end autosync **************************************************************************************/
	/********** adding AD code in head area *******************************************************************************/
	add_action( 'wp_head', 'AD_func_add', 1 );

	function AD_func_add()
    {
		require_once( 'textEditing.php' );
		$headerParsingResult = headerADInsertor();
		if ( $headerParsingResult == true )
		{
			?>
            <script type="text/javascript"> rbConfig = {start: performance.now()}; </script>
            <script async="async" type="text/javascript" src="//any.realbig.media/rotator.min.js"></script>
			<?php
		}
	}
	function push_head_add()
    {
	    require_once( 'textEditing.php' );
	    $headerParsingResult = headerPushInsertor();
	    if ( $headerParsingResult == true )
	    {
		    ?>
            <script charset="utf-8" async
                    src="https://realpush.media/pushJs/<?php echo $GLOBALS['pushCode'] ?>.js"></script>
		    <?php
	    }
	}
	$pushStatus = $wpdb->get_results($wpdb->prepare('SELECT optionName, optionValue FROM ' . $wpPrefix . 'realbig_settings WHERE optionName IN (%s, %s)', ["pushCode","pushStatus"]),ARRAY_A);
	if (!empty($pushStatus)&&$pushStatus[0]['optionName']=='pushStatus') {
	    $pushStatusValue = $pushStatus[0]['optionValue'];
	    $pushCode = $pushStatus[1]['optionValue'];
    } else {
		$pushStatusValue = $pushStatus[1]['optionValue'];
		$pushCode = $pushStatus[0]['optionValue'];
    }
	if (!empty($pushStatus)&&count($pushStatus)==2&&$pushStatusValue==1) {
		add_action('wp_head', 'push_head_add', 2);
		$GLOBALS['pushCode'] = $pushCode;
	}

	/********** end of adding AD code in head area ************************************************************************/
	/********** manual sync ***********************************************************************************************/
//$blocksSettingsTableChecking = $wpdb->query('SELECT id FROM '.$wpPrefix.'realbig_plugin_settings');
	if ( strpos( $GLOBALS['PHP_SELF'], 'wp-admin' ) != false )
	{
		$wpOptionsCheckerSyncTime = $wpdb->get_row( $wpdb->prepare( 'SELECT optionValue FROM ' . $wpPrefix . 'realbig_settings WHERE optionName = %s', [ "token_sync_time" ] ) );
		if ( ! empty( $_POST['tokenInput'] ) )
		{
			$sameTokenResult = false;
			synchronize( $_POST['tokenInput'], ( empty( $wpOptionsCheckerSyncTime ) ? null : $wpOptionsCheckerSyncTime ), $sameTokenResult, $wpPrefix );
//			deactivate_plugins(plugin_basename( __FILE__ ));
		}
		elseif ( $GLOBALS['token'] == 'no token' )
        {
	        $GLOBALS['tokenStatusMessage'] = 'Введите токен';
		}
		tokenTimeUpdateChecking( $GLOBALS['token'], $wpPrefix );
	}
	/********** end of manual sync ****************************************************************************************/
	/************* blocks for text ****************************************************************************************/
//	if (is_page()||is_single()||is_singular())
//	{
//		$fromDb = $wpdb->get_results( 'SELECT * FROM ' . $wpPrefix . 'realbig_plugin_settings WGPS' );
		add_filter('the_content', 'pathToIcons', 5000);
//	}
	/************* end blocks for text ************************************************************************************/
//	add_filter( 'the_content', 'pathToIcons', 5000 );
	/********** using settings in texts ***********************************************************************************/
	function pathToIcons($content)
    {
        if (is_page()||is_single()||is_singular()) {
            global $wpdb;

	        $fromDb = $wpdb->get_results('SELECT * FROM ' . $GLOBALS['wpPrefix'] . 'realbig_plugin_settings WGPS');
//	        $fromDb = $GLOBALS['fromDb'];
	        require_once( 'textEditing.php' );
	        $setNum  = 1;
	        $content = addIcons($fromDb, $content, 'content');
	        return $content;
        } else {
            return $content;
        }
//        $post_page1 = is_page();
//        $post_page2 = is_single();
//        $post_page3 = is_singular();
	}
	/*********** end of using settings in texts ***************************************************************************/
	/*********** begin of token input area ********************************************************************************/
	function my_plugin_action_links( $links )
    {
		$links = array_merge( array( '<a href="' . esc_url( admin_url( '/admin.php?page=realbigForWP%2FrealbigForWP.php' ) ) . '">' . __( 'Settings', 'textdomain' ) . '</a>' ), $links );

		return $links;
	}

	add_action( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'my_plugin_action_links' );

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	if ( is_admin() )
	{
		add_action( 'admin_menu', 'my_pl_settings_menu_create' );
	}
	function my_pl_settings_menu_create() {
	    if (strpos($_SERVER['REQUEST_URI'], 'page=realbigForWP'))
	    {
		    add_menu_page( 'Your code sending configuration', 'realBIG', 'administrator', __FILE__, 'TokenSync', get_site_url().'/wp-content/plugins/realbigForWP/assets/realbig_plugin_hover.png' );
        }
        else
        {
	        add_menu_page( 'Your code sending configuration', 'realBIG', 'administrator', __FILE__, 'TokenSync', get_site_url().'/wp-content/plugins/realbigForWP/assets/realbig_plugin_standart.png' );
        }
//		add_menu_page( 'Your code sending configuration', 'realBIG', 'administrator', __FILE__, 'TokenSync', get_site_url().'/wp-content/plugins/realbigForWP/assets/realbig_plugin_hover.png' );
		add_action( 'admin_init', 'register_mysettings' );
	}

	function register_mysettings()
    {
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
<!--        <div style="width: 100px; height: 20px; border: 1px solid black; background-color: royalblue"></div>-->
		<?php
	}
	/************ end of token input area *********************************************************************************/
}
catch (Exception $ex)
{
//	deactivate_plugins('realbigForWP');
	deactivate_plugins(plugin_basename( __FILE__ ));
	?><div style="margin-left: 200px; border: 3px solid red"><? echo $ex; ?></div><?

//	wp_die( 'test' );
}
catch (Error $er)
{
	deactivate_plugins(plugin_basename( __FILE__ ));
    ?><div style="margin-left: 200px; border: 3px solid red"><? echo $er; ?></div><?

//    $urlForRedirection = get_site_url().'/wp-admin/index.php';
//	wp_safe_redirect($urlForRedirection, 302);  // this thing calling error
//    exit;
}