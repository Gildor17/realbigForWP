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
Version:      0.1.14a
Author:       Gildor
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
*/

/** **************************************************************************************************************** **/
//$test12 = $GLOBALS['PHP_SELF'];
//$rezult = add_action('admin_init', 'adminPagesTest');
//$adminChecker = $wpdb->get_var('SELECT optionValue FROM wp_realbig_settings WHERE optionName = "testAdminRow"');
//$testwrs = wpRealbigSettingsTableUpdateFunction();
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
$GLOBALS['realbigForWP_version'] = '0.1.14a';
/********** checking and creating tables ******************************************************************************/
$wpPrefix = $wpdb->base_prefix;
if (empty($wpPrefix)) {
	$wpPrefix = $table_prefix;
}

try
{
	$tableForCurrentPluginChecker = $wpdb->get_var( 'SHOW TABLES LIKE "' . $wpPrefix . 'realbig_plugin_settings"' );   //settings for block table checking
	$tableForToken                = $wpdb->get_var( 'SHOW TABLES LIKE "' . $wpPrefix . 'realbig_settings"' );      //settings for token and other
//	$pluginActivityChecker        = is_plugin_active( 'realbigForWP/realbigForWP.php' );     //plugin status (active or not)
}
catch (Exception $e) {}

dbTablesCreateFunction($tableForCurrentPluginChecker, $tableForToken, $wpPrefix);
dbOldTablesRemoveFunction($wpPrefix);

/********** end of checking and creating tables ***********************************************************************/

$token = tokenChecking($wpPrefix);
/****************** autosync ******************************************************************************************/
if (!empty($token)&&$token!='no token')
{
	try
    {
	    $wpOptionsCheckerSyncTime = $wpdb->get_row( $wpdb->prepare( 'SELECT optionValue FROM ' . $wpPrefix . 'realbig_settings WHERE optionName = %s', [ "token_sync_time" ] ));
//	    $syncIterations = $wpdb->get_var('SELECT optionValue FROM '.$wpPrefix.'realbig_settings WHERE optionName = "syncRequest"');
//	    $wpdb->update($wpPrefix.'realbig_settings', ['optionValue'=> $syncIterations + 1], ['optionName'=>'syncRequest']);
	    if (!empty( $wpOptionsCheckerSyncTime))
	    {
		    $lastSyncTime = get_object_vars( $wpOptionsCheckerSyncTime );
	    }
	    else
	    {
		    $lastSyncTime = null;
	    }

	    if (!empty($lastSyncTime))
	    {
		    $timeDif = time() - intval( $lastSyncTime['optionValue'] );
		    if ($timeDif > 300)
		    {
			    $sameTokenResult = true;
			    synchronize($token, $wpOptionsCheckerSyncTime, $sameTokenResult, $wpPrefix);
			    tokenTimeUpdateChecking($GLOBALS['token']);
		    }
        }
    }
    catch (Exception $e) {}
}
/****************** end autosync **************************************************************************************/

/********** adding AD code in head area *******************************************************************************/
add_action('wp_head', 'AD_func_add', 1);
function AD_func_add()
{
	require_once('textEditing.php');
	$headerParsingResult = headerADInsertor();
	if ($headerParsingResult==true)
	{
		?>
        <script type="text/javascript"> rbConfig={start:performance.now()}; </script>
        <script async="async" type="text/javascript" src="//any.realbig.media/rotator.min.js"></script>
		<?php
	}
}
/********** end of adding AD code in head area ************************************************************************/

//$blocksSettingsTableChecking = $wpdb->query('SELECT id FROM '.$wpPrefix.'realbig_plugin_settings');
if (strpos($GLOBALS['PHP_SELF'], 'wp-admin') != false)
{
	if (!empty($_POST['tokenInput']))
	{
		$sameTokenResult = false;
		synchronize($_POST['tokenInput'], (empty($wpOptionsCheckerSyncTime) ? NULL : $wpOptionsCheckerSyncTime), $sameTokenResult);
//    if (!empty($token)&&$_POST['tokenInput']==$token&&$blocksSettingsTableChecking!=0)
//    {
//	    $sameTokenResult = true;
//    }
//    else
//    {
//	    $sameTokenResult = false;
//    }
////	$url = 'http://realbigweb/api/wp-get-settings?token='.$_POST['tokenInput'];     // orig web get
////	$url = 'http://realbigweb/api/wp-get-settings';     // orig web post
//	$url = 'https://realbig.media/api/wp-get-settings?token='.$_POST['tokenInput'];     // orig
//
//    $dataForSending = [
//        'token' => $_POST['tokenInput']
//    ];
//
//	try
//	{
//		$ch = curl_init('https://realbig.media/api/wp-get-settings?token='.$_POST["tokenInput"]);
////	    curl_setopt($ch, CURLOPT_URL, $url);
////		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/535.6 (KHTML, like Gecko) Chrome/16.0.897.0 Safari/535.6');
//		curl_setopt($ch, CURLOPT_POST, 1);
//		curl_setopt($ch, CURLOPT_POSTFIELDS, $dataForSending);
//		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//		curl_setopt($ch, CURLOPT_TIMEOUT, 15);
//		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
//		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
//		curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');
//	    curl_setopt($ch, CURLOPT_COOKIE, '');
//	    curl_setopt($ch, CURLOPT_COOKIEFILE, '');
//		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
////	    curl_setopt($ch, CURLOPT_REFERER, $url);
////		$connectionChecker = curl_getinfo($ch, CURLINFO_OS_ERRNO);
//		$jsonToken = curl_exec($ch);
//		curl_close($ch);
//
////		echo '<script>console.log("'.$jsonToken.'")</script>';
//
//		if (!empty($jsonToken))
//		{
//			$GLOBALS['connection_request_rezult'] = 1;
//			$GLOBALS['connection_request_rezult_1'] = 'success';
//		}
//		else
//		{
//			$error = error_get_last();
//			$GLOBALS['connection_request_rezult'] = 'Connection error: ' . $error['message'];
//			$GLOBALS['connection_request_rezult_1'] = 'Connection error: ' . $error['message'];
//
//        }
//	}
//	catch (Exception $e)
//	{
//		$GLOBALS['tokenStatusMessage'] = $e['message'];
//	}
//
//	if (!empty($jsonToken))
//	{
//		$decodedToken = json_decode($jsonToken, true);
//		$GLOBALS['tokenStatusMessage'] = $decodedToken['message'];
//    }
//    else
//    {
//	    $decodedToken = NULL;
//	    $GLOBALS['tokenStatusMessage'] = 'ошибка соединения';
//    }
//
//    if (!empty($decodedToken['data']))
//    {
//        try
//        {
//	        $counter = 0;
//	        $wpdb->query($wpdb->prepare( 'DELETE FROM '.$wpPrefix.'realbig_plugin_settings', []));
//	        $sqlTokenSave = "INSERT INTO ".$wpPrefix."realbig_plugin_settings (text, block_number, setting_type, element, directElement, elementPosition, elementPlace, firstPlace, elementCount, elementStep) VALUES ";
//
//	        foreach ($decodedToken['data'] AS $k => $item)
//	        {
//		        $counter++;
//		        $sqlTokenSave .= ( $counter != 1 ? ", " : "") . "('" . $item['text'] . "', " . (int)$item['block_number'] . ", " . (int)$item['setting_type'] . ", '" . htmlspecialchars($item['element']) . "', '" . htmlspecialchars($item['directElement']) . "', " . (int)$item['elementPosition'] . ", " . (int)$item['elementPlace'] . ", " . (int)$item['firstPlace'] . ", " . (int)$item['elementCount'] . ", " . (int)$item['elementStep'] . ")";
//	        }
//	        $sqlTokenSave .= " ON DUPLICATE KEY UPDATE text = values(text), setting_type = values(setting_type), element = values(element), directElement = values(directElement), elementPosition = values(elementPosition), elementPlace = values(elementPlace), firstPlace = values(firstPlace), elementCount = values(elementCount), elementStep = values(elementStep) ";
//
//		    $wpdb->query($wpdb->prepare($sqlTokenSave, []));
//		    // if no needly note, then create
//		    $wpOptionsCheckerTokenValue = $wpdb->query($wpdb->prepare( "SELECT optionValue FROM " . $wpPrefix . "realbig_settings WHERE optionName = '_wpRealbigPluginToken'", []));
//		    if (empty($wpOptionsCheckerTokenValue))
//		    {
//			    $wpdb->insert($wpPrefix.'realbig_settings', ['optionName'=>'_wpRealbigPluginToken', 'optionValue'=>$_POST["tokenInput"]]);
//		    }
//		    else
//		    {
//			    $wpdb->update($wpPrefix.'realbig_settings', ['optionName'=>'_wpRealbigPluginToken', 'optionValue'=>$_POST["tokenInput"]], ['optionName'=>'_wpRealbigPluginToken']);
//		    }
//
////            $wpOptionsCheckerSyncTime = $wpdb->get_row($wpdb->prepare('SELECT optionValue FROM '.$wpPrefix.'realbig_settings WHERE optionName = "token_sync_time"', []));
//		    if (empty($wpOptionsCheckerSyncTime)) {
//			    $wpdb->insert($wpPrefix.'realbig_settings', ['optionName'=>'token_sync_time', 'optionValue'=> time()]);
//		    }
//		    else
//		    {
//			    $wpdb->update($wpPrefix.'realbig_settings', ['optionName'=>'token_sync_time', 'optionValue'=> time()], ['optionName'=>'token_sync_time']);
//		    }
//
//		    $GLOBALS['token'] = $_POST["tokenInput"];
//	    }
//	    catch (Exception $e)
//	    {
//		    $GLOBALS['tokenStatusMessage'] = $e;
//	    }
//    }
	}
    elseif ($GLOBALS['token'] == 'no token')
	{
		$GLOBALS['tokenStatusMessage'] = 'Введите токен';
	}
	tokenTimeUpdateChecking($GLOBALS['token'], $wpPrefix);
}

/************* blocks for text ****************************************************************************************/
$fromDb = $wpdb->get_results('SELECT setting_type, `text`, element, directElement, elementPosition, elementPlace, firstPlace, elementCount, elementStep FROM '.$wpPrefix.'realbig_plugin_settings WGPS');
/************* end blocks for text ************************************************************************************/

add_filter('the_content', 'pathToIcons', 102);

/********** using settings in texts ***********************************************************************************/
function pathToIcons($content)
{
    $fromDb = $GLOBALS['fromDb'];
	require_once('textEditing.php');
    $setNum = 1;
	$content = addIcons($fromDb, $content);
    return $content;
}
/*********** end of using settings in texts ***************************************************************************/

//function adminPagesTest()
//{
//    global $wpdb;
//
//    $adminChecker = $wpdb->get_var('SELECT optionValue FROM '.$wpPrefix.'realbig_settings WHERE optionName = "testAdminRow"');
//	$adminChecker = $adminChecker + 1;
//    $wpdb->update($wpPrefix.'realbig_settings', ['optionValue'=> $adminChecker], ['optionName'=>'testAdminRow']);
//}

/*********** begin of token input area ********************************************************************************/
function my_plugin_action_links($links)
{
	$links = array_merge( array('<a href="' . esc_url( admin_url( '/admin.php?page=realbigForWP%2FrealbigForWP.php' ) ) . '">' . __( 'Settings', 'textdomain' ) . '</a>'), $links );
	return $links;
}
add_action( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'my_plugin_action_links' );

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if (is_admin())
{
	add_action( 'admin_menu', 'my_pl_settings_menu_create' );
}
function my_pl_settings_menu_create()
{
	add_menu_page('Your code sending configuration', 'Реалбиг плагин настройки', 'administrator', __FILE__, 'TokenSync' );
	add_action( 'admin_init', 'register_mysettings' );
}

function register_mysettings ()
{
	register_setting('sending_zone', 'token_value_input');
	register_setting('sending_zone', 'token_value_send');
}

function TokenSync()
{
?>
	<div class="wrap col-md-12">
		<form method="post" name="tokenForm" id="tokenFormId">
            <label><span style="font-size: 16px">Токен</span><br/>
                <input name="tokenInput" id="tokenInputId" value="<?= $GLOBALS['token'] ?>" style="min-width: 280px" required>
                <label style="font-size: 16px; margin-left: 10px; color: <?= $GLOBALS['statusColor'] ?> ">Время последней синхронизации: <?= $GLOBALS['tokenTimeUpdate'] ?></label>
            </label>
            <?php submit_button('Синхронизировать', 'primary', 'saveTokenButton') ?>
            <?php if (!empty($GLOBALS['tokenStatusMessage'])): ?>
            <div name="rezultDiv" style="font-size: 16px"><?= $GLOBALS['tokenStatusMessage'] ?></div>
            <?php endif; ?>
		</form>
        <br>
        <div>Надписи ниже нужны для тестировки</div>
        <div>Статус соединения 1: <?= (!empty($GLOBALS['connection_request_rezult_1']) ? $GLOBALS['connection_request_rezult_1'] : 'empty') ?></div>
        <div>Статус соединения общий: <?= (!empty($GLOBALS['connection_request_rezult']) ? $GLOBALS['connection_request_rezult'] : 'empty') ?></div>
	</div>
<?php
}
/************ end of token input area *********************************************************************************/
