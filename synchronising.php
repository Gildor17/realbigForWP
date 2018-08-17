<?php
/**
 * Created by PhpStorm.
 * User: furio
 * Date: 2018-08-02
 * Time: 18:17
 */

include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
include_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
include_once( ABSPATH . '/wp-includes/wp-db.php');

try
{

function synchronize($tokenInput, $wpOptionsCheckerSyncTime, $sameTokenResult, $wpPrefix)
{
	global $wpdb;

	try
	{
		//	$url = 'http://realbigweb/api/wp-get-settings?token='.$tokenInput;     // orig web get
//	$url = 'http://realbigweb/api/wp-get-settings';     // orig web post
//	$url = 'https://realbig.media/api/wp-get-settings?token='.$tokenInput.'&sameToken='.$sameTokenResult;     // orig
		$url = 'https://realbig.media/api/wp-get-settings';     // orig post
//		$url = 'https://realb1ig.media/api/wp-get-settings';     // orig post error


		$dataForSending = [
			'token' => $tokenInput,
			'sameToken' => $sameTokenResult
		];

		try
		{
//		$ch = curl_init('https://realbig.media/api/wp-get-settings?token='.$tokenInput);
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
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

		if (!empty($jsonToken))
		{
			$decodedToken = json_decode($jsonToken, true);
			$GLOBALS['tokenStatusMessage'] = $decodedToken['message'];
		}
		else
		{
			$decodedToken = NULL;
			$GLOBALS['tokenStatusMessage'] = 'ошибка соединения';
			$decodedToken['status'] = 'error';
		}

		if (!empty($decodedToken['data']))
		{
			try
			{
				$counter = 0;
				$wpdb->query( 'DELETE FROM '.$wpPrefix.'realbig_plugin_settings');
				$sqlTokenSave = "INSERT INTO ".$wpPrefix."realbig_plugin_settings (text, block_number, setting_type, element, directElement, elementPosition, elementPlace, firstPlace, elementCount, elementStep) VALUES ";

				foreach ($decodedToken['data'] AS $k => $item)
				{
					$counter++;
					$sqlTokenSave .= ( $counter != 1 ? ", " : "") . "('" . $item['text'] . "', " . (int)$item['block_number'] . ", " . (int)$item['setting_type'] . ", '" . htmlspecialchars($item['element']) . "', '" . htmlspecialchars($item['directElement']) . "', " . (int)$item['elementPosition'] . ", " . (int)$item['elementPlace'] . ", " . (int)$item['firstPlace'] . ", " . (int)$item['elementCount'] . ", " . (int)$item['elementStep'] . ")";
				}
				$sqlTokenSave .= " ON DUPLICATE KEY UPDATE text = values(text), setting_type = values(setting_type), element = values(element), directElement = values(directElement), elementPosition = values(elementPosition), elementPlace = values(elementPlace), firstPlace = values(firstPlace), elementCount = values(elementCount), elementStep = values(elementStep) ";

				$wpdb->query($sqlTokenSave);
				// if no needly note, then create
				$wpOptionsCheckerTokenValue = $wpdb->query($wpdb->prepare( "SELECT optionValue FROM " . $wpPrefix . "realbig_settings WHERE optionName = %s", ['_wpRealbigPluginToken']));
				if (empty($wpOptionsCheckerTokenValue))
				{
					$wpdb->insert($wpPrefix.'realbig_settings', ['optionName'=>'_wpRealbigPluginToken', 'optionValue'=>$tokenInput]);
				}
				else
				{
					$wpdb->update($wpPrefix.'realbig_settings', ['optionName'=>'_wpRealbigPluginToken', 'optionValue'=>$tokenInput], ['optionName'=>'_wpRealbigPluginToken']);
				}

				$GLOBALS['token'] = $tokenInput;
			}
			catch (Exception $e)
			{
				$GLOBALS['tokenStatusMessage'] = $e;
			}
		}

		try
		{
		    set_transient('realbigPluginSyncAttempt', $decodedToken['status'], 300);
//            $gTrans = get_transient('realbigPluginSyncAttempt');
//		    delete_transient('realbigPluginSyncAttempt');
			if ($decodedToken['status']=='success')
			{
				if (empty($wpOptionsCheckerSyncTime))
				{
					$wpdb->insert($wpPrefix.'realbig_settings', ['optionName'=>'token_sync_time', 'optionValue'=> time()]);
				}
				else
				{
					$wpdb->update($wpPrefix.'realbig_settings', ['optionName'=>'token_sync_time', 'optionValue'=> time()], ['optionName'=>'token_sync_time']);
				}
			}
		}
		catch (Exception $e)
		{
			echo $e;
		}
	}
	catch (Exception $e) {
		echo $e;
	}
}

function tokenChecking($wpPrefix)
{
	global $wpdb;

	try
	{
		$GLOBALS['tokenStatusMessage'] = NULL;
		$token = $wpdb->get_results("SELECT optionValue FROM ".$wpPrefix."realbig_settings WHERE optionName = '_wpRealbigPluginToken'");

		if (!empty($token))
		{
			$token = get_object_vars($token[0]);
			$GLOBALS['token'] = $token['optionValue'];
			$token = $token['optionValue'];
		}
		else
		{
			$GLOBALS['token'] = 'no token';
			$token = 'no token';
		}

		return $token;
	}
	catch (Exception $e)
	{
		return 'no token';
	}
}

function tokenTimeUpdateChecking($token, $wpPrefix)
{
	global $wpdb;
	try
	{
//		if ($GLOBALS['tokenStatusMessage']=='success'||empty($GLOBALS['tokenStatusMessage']))
//		{
			$timeUpdate = $wpdb->get_results("SELECT timeUpdate FROM ".$wpPrefix."realbig_settings WHERE optionName = 'token_sync_time'");
			if (empty($timeUpdate))
			{
				$updateResult = wpRealbigSettingsTableUpdateFunction($wpPrefix);
				if ($updateResult = true)
				{
					$timeUpdate = $wpdb->get_results("SELECT timeUpdate FROM ".$wpPrefix."realbig_settings WHERE optionName = 'token_sync_time'");
				}
			}
			if (!empty($token)&&$token != 'no token'&&($GLOBALS['tokenStatusMessage']=='success'||empty($GLOBALS['tokenStatusMessage']))&&!empty($timeUpdate))
			{
				if (!empty($timeUpdate))
				{
					$timeUpdate = get_object_vars($timeUpdate[0]);
					$GLOBALS['tokenTimeUpdate'] = $timeUpdate['timeUpdate'];
					$GLOBALS['statusColor'] = 'green';
				}
				else
				{
					$GLOBALS['tokenTimeUpdate'] = '';
					$GLOBALS['statusColor'] = 'red';
				}
			}
			else
			{
				$GLOBALS['tokenTimeUpdate'] = 'never';
				$GLOBALS['statusColor'] = 'red';
			}
//		}
	}
	catch (Exception $e)
	{
		echo $e;
	}
}

}
catch (Error $er)
{
	deactivate_plugins(plugin_basename( __FILE__ ));
	?><div style="margin-left: 200px; border: 3px solid red"><? echo $er; ?></div><?
}