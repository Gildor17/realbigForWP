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
Version:      0.1.7a
Author:       Gildor
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
*/

/** **************************************************************************************************************** **/

/***************** updater code ***************************************************************************************/
require 'plugin-update-checker/plugin-update-checker.php';
$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
	'https://github.com/Gildor17/realbigFoWP',
	__FILE__,
	'realbigFoWP'
);

/****************** end of updater code *******************************************************************************/

$GLOBALS['realbigForWP_version'] = '0.1.7a';

$GLOBALS['tokenStatusMessage'] = NULL;
$serv = $_SERVER["HTTP_HOST"];

$token = $wpdb->get_results("SELECT optionValue FROM ".$wpdb->base_prefix."realbig_settings WHERE optionName = '_wpRealbigPluginToken'");
if (!empty($token))
{
	$token = get_object_vars($token[0]);
	$GLOBALS['token'] = $token['optionValue'];
}
else
{
	$GLOBALS['token'] = 'no token';
}

if (!empty($_POST['tokenInput']))
{
//	$url = 'http://realbigweb/api/wp-get-settings?token='.$_POST['tokenInput'];     // orig web get
//	$url = 'http://realbigweb/api/wp-get-settings';     // orig web post
	$url = 'https://realbig.media/api/wp-get-settings?token='.$_POST['tokenInput'];     // orig

    $dataForSending = [
        'token' => $_POST['tokenInput']
    ];

	try
	{
		$ch = curl_init('https://realbig.media/api/wp-get-settings?token='.$_POST["tokenInput"]);
//	    curl_setopt($ch, CURLOPT_URL, $url);
//		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/535.6 (KHTML, like Gecko) Chrome/16.0.897.0 Safari/535.6');
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $dataForSending);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 15);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
		curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');
	    curl_setopt($ch, CURLOPT_COOKIE, '');
	    curl_setopt($ch, CURLOPT_COOKIEFILE, '');
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
//	    curl_setopt($ch, CURLOPT_REFERER, $url);
//		$connectionChecker = curl_getinfo($ch, CURLINFO_OS_ERRNO);
		$jsonToken = curl_exec($ch);
		curl_close($ch);

//		echo '<script>console.log("'.$jsonToken.'")</script>';

		if (!empty($jsonToken))
		{
			$GLOBALS['connection_request_rezult'] = 1;
			$GLOBALS['connection_request_rezult_1'] = 'success';
		}
		else
		{
			$error = error_get_last();
			$GLOBALS['connection_request_rezult'] = 'Connection error: ' . $error['message'];
			$GLOBALS['connection_request_rezult_1'] = 'Connection error: ' . $error['message'];

        }
	}
	catch (Exception $e)
	{
		$GLOBALS['tokenStatusMessage'] = $e['message'];
	}

//    if (empty($jsonToken))
//    {
//	    try
//	    {
//		    $jsonToken = wp_remote_get($url);
//		    $jsonToken = $jsonToken['body'];
//
//		    if (!empty($jsonToken))
//		    {
//			    $GLOBALS['connection_request_rezult'] = 2;
//			    $GLOBALS['connection_request_rezult_2'] = 'success';
//		    }
//		    else
//		    {
//			    $error = error_get_last();
//			    $GLOBALS['connection_request_rezult'] = 'Connection error: ' . $error['message'];
//			    $GLOBALS['connection_request_rezult_2'] = 'Connection error: ' . $error['message'];
//		    }
//	    }
//	    catch (Exception $e)
//	    {
//		    $GLOBALS['tokenStatusMessage'] = $e['message'];
//	    }
//    }

//    if (empty($jsonToken))        //doesn't work as it should
//    {
//	    try
//	    {
//		    $jsonToken = wp_remote_post($url, $dataForSending);
////		    $jsonToken = $jsonToken['body'];
//
//		    if (!empty($jsonToken))
//		    {
//			    $GLOBALS['connection_request_rezult'] = 4;
//		    }
//	    }
//	    catch (Exception $e)
//	    {
//		    $GLOBALS['tokenStatusMessage'] = $e['message'];
//	    }
//    }

//    if (empty($jsonToken))
//    {
//	    try
//	    {
//		    ini_set('max_execution_time', 300);
//		    if (!$jsonToken = @file_get_contents($url, false, null, 0))
//		    {
//			    $error = error_get_last();
//			    $GLOBALS['connection_request_rezult'] = 'Connection error: ' . $error['message'];
//			    $GLOBALS['connection_request_rezult_3'] = 'Connection error: ' . $error['message'];
//		    }
//		    else
//		    {
//			    $GLOBALS['connection_request_rezult'] = 3;
//			    $GLOBALS['connection_request_rezult_3'] = 'success';
//            }
//	    }
//	    catch (Exception $e)
//	    {
//		    $GLOBALS['tokenStatusMessage'] = $e['message'];
//	    }
//    }

	if (!empty($jsonToken))
	{
		$decodedToken = json_decode($jsonToken, true);
		$GLOBALS['tokenStatusMessage'] = $decodedToken['message'];
    }
    else
    {
	    $decodedToken = NULL;
	    $GLOBALS['tokenStatusMessage'] = 'ошибка соединения';
    }

    if (!empty($decodedToken['data']))
    {
        try
        {
	        $counter = 0;
	        $wpdb->query($wpdb->prepare( 'DELETE FROM '.$wpdb->base_prefix.'realbig_plugin_settings', ''));
	        $sqlTokenSave = "INSERT INTO ".$wpdb->base_prefix."realbig_plugin_settings (text, block_number, setting_type, element, directElement, elementPosition, elementPlace, firstPlace, elementCount, elementStep) VALUES ";

	        foreach ($decodedToken['data'] AS $k => $item)
	        {
		        $counter++;
		        $sqlTokenSave .= ( $counter != 1 ? ", " : "") . "('" . $item['text'] . "', " . (int)$item['block_number'] . ", " . (int)$item['setting_type'] . ", '" . htmlspecialchars($item['element']) . "', '" . htmlspecialchars($item['directElement']) . "', " . (int)$item['elementPosition'] . ", " . (int)$item['elementPlace'] . ", " . (int)$item['firstPlace'] . ", " . (int)$item['elementCount'] . ", " . (int)$item['elementStep'] . ")";
	        }
	        $sqlTokenSave .= " ON DUPLICATE KEY UPDATE text = values(text), setting_type = values(setting_type), element = values(element), directElement = values(directElement), elementPosition = values(elementPosition), elementPlace = values(elementPlace), firstPlace = values(firstPlace), elementCount = values(elementCount), elementStep = values(elementStep) ";

		    $wpdb->query($wpdb->prepare($sqlTokenSave, ''));
		    // if no needly note, then create
		    $wpOptionsChecker = $wpdb->query($wpdb->prepare("SELECT optionValue FROM ".$wpdb->base_prefix."realbig_settings WHERE optionName = '_wpRealbigPluginToken'", ''));
		    if (empty($wpOptionsChecker))
		    {
			    $wpdb->insert($wpdb->base_prefix.'realbig_settings', ['optionName'=>'_wpRealbigPluginToken', 'optionValue'=>$_POST["tokenInput"]]);
		    }
		    else
		    {
			    $wpdb->update($wpdb->base_prefix.'realbig_settings', ['optionName'=>'_wpRealbigPluginToken', 'optionValue'=>$_POST["tokenInput"]], ['optionName'=>'_wpRealbigPluginToken']);
		    }
		    $GLOBALS['token'] = $_POST["tokenInput"];
	    }
	    catch (Exception $e)
	    {
		    $GLOBALS['tokenStatusMessage'] = $e;
	    }
    }
}
elseif ($GLOBALS['token'] == 'no token')
{
	$GLOBALS['tokenStatusMessage'] = 'Введите токен';
}

/************* blocks for text ****************************************************************************************/
$fromDb = $wpdb->get_results('SELECT setting_type, `text`, element, directElement, elementPosition, elementPlace, firstPlace, elementCount, elementStep FROM '.$wpdb->base_prefix.'realbig_plugin_settings WGPS');
/************* end blocks for text ************************************************************************************/

add_filter('the_content', 'pathToIcons', 102);

/********** checking and creating tables ******************************************************************************/
$tableForCurrentPluginChecker = $wpdb->get_var('SHOW TABLES LIKE "'.$wpdb->base_prefix.'realbig_plugin_settings"');   //settings for block table checking
$tableForToken = $wpdb->get_var('SHOW TABLES LIKE "'.$wpdb->base_prefix.'realbig_settings"');      //settings for token and other
$pluginActivityChecker = is_plugin_active('realbigForWP/realbigForWP.php');     //plugin status (active or not)

$updating = new UpdateClass();
$wpPrefix = $wpdb->base_prefix;
$updating->dbTablesCreateFunction($tableForCurrentPluginChecker, $tableForToken, $pluginActivityChecker, $wpPrefix);
$updating->dbOldTablesRemoveFunction($wpdb, $wpPrefix);

/********** end of checking and creating tables ***********************************************************************/

/********** adding AD code in head area *******************************************************************************/
//http_head()

/********** end of adding AD code in head area ************************************************************************/

/********** using settings in texts ***********************************************************************************/
function pathToIcons($content)
{
    $fromDb = $GLOBALS['fromDb'];
	require('textEditing.php');
    $setNum = 1;
	$content = addIcons($fromDb, $content);
    return $content;
}
/*********** end of using settings in texts ***************************************************************************/

/*********** begin of token input area ********************************************************************************/
function my_plugin_action_links($links)
{
	$links = array_merge( array('<a href="' . esc_url( admin_url( '/admin.php?page=realbigForWP%2FrealbigForWP.php' ) ) . '">' . __( 'Settings', 'textdomain' ) . '</a>'), $links );
	return $links;
}
add_action( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'my_plugin_action_links' );

//////////////////////////////////////////////////////////////////////////////////////////////////////

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
            </label>
            <?php submit_button('Синхронизировать', 'primary', 'saveTokenButton') ?>
            <?php if (!empty($GLOBALS['tokenStatusMessage'])): ?>
            <div name="rezultDiv" style="font-size: 16px"><?= $GLOBALS['tokenStatusMessage'] ?></div>
            <?php endif; ?>
		</form>
        <br>
        <div>Надписи ниже нужны для тестировки</div>
        <div>Статус соединения 1: <?= (!empty($GLOBALS['connection_request_rezult_1']) ? $GLOBALS['connection_request_rezult_1'] : 'empty') ?></div>
        <div>Статус соединения 2: <?//= (!empty($GLOBALS['connection_request_rezult_2']) ? $GLOBALS['connection_request_rezult_2'] : 'empty') ?></div>
        <div>Статус соединения 3: <?//= (!empty($GLOBALS['connection_request_rezult_3']) ? $GLOBALS['connection_request_rezult_3'] : 'empty') ?></div>
        <div>Статус соединения общий: <?= $GLOBALS['connection_request_rezult'] ?></div>
	</div>
<?php
}
/************ end of token input area *********************************************************************************/
