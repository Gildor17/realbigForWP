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

function synchronize($tokenInput, $wpOptionsCheckerSyncTime, $sameTokenResult)
{
	global $wpdb;

	$wpbdBasePrefix = htmlspecialchars($wpdb->base_prefix);
		
//	$url = 'http://realbigweb/api/wp-get-settings?token='.$tokenInput;     // orig web get
//	$url = 'http://realbigweb/api/wp-get-settings';     // orig web post
//	$url = 'https://realbig.media/api/wp-get-settings?token='.$tokenInput.'&sameToken='.$sameTokenResult;     // orig
	$url = 'https://realbig.media/api/wp-get-settings';     // orig post

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
	}

	if (!empty($decodedToken['data']))
	{
		try
		{
			$counter = 0;
			$wpdb->query( 'DELETE FROM '.$wpbdBasePrefix.'realbig_plugin_settings');
			$sqlTokenSave = "INSERT INTO ".$wpbdBasePrefix."realbig_plugin_settings (text, block_number, setting_type, element, directElement, elementPosition, elementPlace, firstPlace, elementCount, elementStep) VALUES ";

			foreach ($decodedToken['data'] AS $k => $item)
			{
				$counter++;
				$sqlTokenSave .= ( $counter != 1 ? ", " : "") . "('" . $item['text'] . "', " . (int)$item['block_number'] . ", " . (int)$item['setting_type'] . ", '" . htmlspecialchars($item['element']) . "', '" . htmlspecialchars($item['directElement']) . "', " . (int)$item['elementPosition'] . ", " . (int)$item['elementPlace'] . ", " . (int)$item['firstPlace'] . ", " . (int)$item['elementCount'] . ", " . (int)$item['elementStep'] . ")";
			}
			$sqlTokenSave .= " ON DUPLICATE KEY UPDATE text = values(text), setting_type = values(setting_type), element = values(element), directElement = values(directElement), elementPosition = values(elementPosition), elementPlace = values(elementPlace), firstPlace = values(firstPlace), elementCount = values(elementCount), elementStep = values(elementStep) ";

			$wpdb->query($sqlTokenSave);
			// if no needly note, then create
			$wpOptionsCheckerTokenValue = $wpdb->query($wpdb->prepare( "SELECT optionValue FROM " . $wpbdBasePrefix . "realbig_settings WHERE optionName = %s", ['_wpRealbigPluginToken']));
			if (empty($wpOptionsCheckerTokenValue))
			{
				$wpdb->insert($wpbdBasePrefix.'realbig_settings', ['optionName'=>'_wpRealbigPluginToken', 'optionValue'=>$tokenInput]);
			}
			else
			{
				$wpdb->update($wpbdBasePrefix.'realbig_settings', ['optionName'=>'_wpRealbigPluginToken', 'optionValue'=>$tokenInput], ['optionName'=>'_wpRealbigPluginToken']);
			}

//            $wpOptionsCheckerSyncTime = $wpdb->get_row($wpdb->prepare('SELECT optionValue FROM '.$wpbdBasePrefix.'realbig_settings WHERE optionName = "token_sync_time"', []));
			if (empty($wpOptionsCheckerSyncTime))
			{
				$wpdb->insert($wpbdBasePrefix.'realbig_settings', ['optionName'=>'token_sync_time', 'optionValue'=> time()]);
			}
			else
			{
				$wpdb->update($wpbdBasePrefix.'realbig_settings', ['optionName'=>'token_sync_time', 'optionValue'=> time()], ['optionName'=>'token_sync_time']);
			}

			$GLOBALS['token'] = $tokenInput;
		}
		catch (Exception $e)
		{
			$GLOBALS['tokenStatusMessage'] = $e;
		}
	}

//	return true;

}