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
Version:      0.1.8a
Author:       Gildor
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
*/

/** **************************************************************************************************************** **/

/***************** updater code ***************************************************************************************/
require 'plugin-update-checker/plugin-update-checker.php';
$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
	'https://github.com/Gildor17/realbigForWP',
	__FILE__,
	'realbigForWP'
);
/****************** end of updater code *******************************************************************************/

$GLOBALS['realbigForWP_version'] = '0.1.8a';

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

/****************** autosync ******************************************************************************************/
if (!empty($token))
{
	$wpOptionsCheckerSyncTime = $wpdb->get_row($wpdb->prepare('SELECT optionValue FROM '.$wpdb->base_prefix.'realbig_settings WHERE optionName = %s', ["token_sync_time"]));
	$lastSyncTime = get_object_vars($wpOptionsCheckerSyncTime);

	$timeDif = time() - intval($lastSyncTime['optionValue']);
	if ($timeDif > 300)
	{
		synchronize($token, $wpOptionsCheckerSyncTime);
    }
}
/****************** end autosync **************************************************************************************/

add_action('wp_head', 'AD_func_add', 1);
function AD_func_add()
{
	?>
    <script type="text/javascript"> rbConfig={start:performance.now()}; </script>
    <script async="async" type="text/javascript" src="//any.realbig.media/rotator.min.js"></script>
	<?php
}

if (!empty($_POST['tokenInput']))
{
    synchronize($_POST['tokenInput'], $wpOptionsCheckerSyncTime);
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

$wpPrefix = $wpdb->base_prefix;
dbTablesCreateFunction($tableForCurrentPluginChecker, $tableForToken, $pluginActivityChecker, $wpPrefix);
dbOldTablesRemoveFunction($wpPrefix);

/********** end of checking and creating tables ***********************************************************************/

/********** adding AD code in head area *******************************************************************************/
//http_head()   //in future

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
        <div>Статус соединения общий: <?= (!empty($GLOBALS['connection_request_rezult']) ? $GLOBALS['connection_request_rezult'] : 'empty') ?></div>
	</div>
<?php
}
/************ end of token input area *********************************************************************************/
