<?php
///**
// * Created by PhpStorm.
// * User: furio
// * Date: 2018-08-02
// * Time: 18:17
// */
//
//include( ABSPATH . 'wp-admin/includes/plugin.php' );
//include( ABSPATH . 'wp-admin/includes/upgrade.php' );
//
//function synchronize($wpdb, $tokenInput )
//{
////	$url = 'http://realbigweb/api/wp-get-settings?token='.$tokenInput;     // orig web get
////	$url = 'http://realbigweb/api/wp-get-settings';     // orig web post
//	$url = 'https://realbig.media/api/wp-get-settings?token='.$tokenInput;     // orig
////	$url = 'https://realbig1.media/api/wp-get-settings?token='.$tokenInput;     // wrong
//	$dataForSending = [
//		'token' => $tokenInput
//	];
//
//	try
//	{
//		$ch = curl_init('https://realbig.media/api/wp-get-settings?token='.$tokenInput);
////	    curl_setopt($ch, CURLOPT_URL, $url);
//		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/535.6 (KHTML, like Gecko) Chrome/16.0.897.0 Safari/535.6');
//		curl_setopt($ch, CURLOPT_POST, 1);
//		curl_setopt($ch, CURLOPT_POSTFIELDS, $dataForSending);
//		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//		curl_setopt($ch, CURLOPT_TIMEOUT, 15);
//		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
//		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
//		curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');
//		curl_setopt($ch, CURLOPT_COOKIE, '');
//		curl_setopt($ch, CURLOPT_COOKIEFILE, '');
//		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
////	    curl_setopt($ch, CURLOPT_REFERER, $url);
//		$connectionChecker = curl_getinfo($ch, CURLINFO_OS_ERRNO);
//		$jsonToken = curl_exec($ch);
//		curl_close($ch);
//
//		if (!empty($jsonToken))
//		{
//			$connection_request_rezult = 1;
//			$connection_request_rezult_1 = 'success';
//		}
//		else
//		{
//			$error = error_get_last();
//			$connection_request_rezult = 'Connection error: ' . $error['message'];
//			$connection_request_rezult_1 = 'Connection error: ' . $error['message'];
//
//		}
//	}
//	catch (Exception $e)
//	{
//		$tokenStatusMessage = $e['message'];
//	}
//
//	if (empty($jsonToken))
//	{
//		try
//		{
//			$jsonToken = wp_remote_get($url);
//			$jsonToken = $jsonToken['body'];
//
//			if (!empty($jsonToken))
//			{
//				$connection_request_rezult = 2;
//				$connection_request_rezult_2 = 'success';
//			}
//			else
//			{
//				$error = error_get_last();
//				$connection_request_rezult = 'Connection error: ' . $error['message'];
//				$connection_request_rezult_2 = 'Connection error: ' . $error['message'];
//			}
//		}
//		catch (Exception $e)
//		{
//			$tokenStatusMessage = $e['message'];
//		}
//	}
//
////    if (empty($jsonToken))        //doesn't work as it should
////    {
////	    try
////	    {
////		    $jsonToken = wp_remote_post($url, $dataForSending);
//////		    $jsonToken = $jsonToken['body'];
////
////		    if (!empty($jsonToken))
////		    {
////			    $connection_request_rezult = 4;
////		    }
////	    }
////	    catch (Exception $e)
////	    {
////		    $tokenStatusMessage = $e['message'];
////	    }
////    }
//
//	if (empty($jsonToken))
//	{
//		try
//		{
//			ini_set('max_execution_time', 300);
//			if (!$jsonToken = @file_get_contents($url, false, null, 0))
//			{
//				$error = error_get_last();
//				$connection_request_rezult = 'Connection error: ' . $error['message'];
//				$connection_request_rezult_3 = 'Connection error: ' . $error['message'];
//			}
//			else
//			{
//				$connection_request_rezult = 3;
//				$connection_request_rezult_3 = 'success';
//			}
//		}
//		catch (Exception $e)
//		{
//			$tokenStatusMessage = $e['message'];
//		}
//	}
//
//	if (!empty($jsonToken))
//	{
//		$decodedToken = json_decode($jsonToken, true);
//		$tokenStatusMessage = $decodedToken['message'];
//	}
//	else
//	{
//		$decodedToken = NULL;
//		$tokenStatusMessage = 'ошибка соединения';
//	}
//
//	if (!empty($decodedToken['data']))
//	{
//		try
//		{
//			$counter = 0;
//			$wpdb->query( 'DELETE FROM WpRealbigPluginSettings' );
//			$sqlTokenSave = "INSERT INTO WpRealbigPluginSettings (text, block_number, setting_type, element, directElement, elementPosition, elementPlace, firstPlace, elementCount, elementStep) VALUES ";
//
//			foreach ($decodedToken['data'] AS $k => $item)
//			{
//				$counter++;
//				$sqlTokenSave .= ( $counter != 1 ? ", " : "") . "('" . $item['text'] . "', " . (int)$item['block_number'] . ", " . (int)$item['setting_type'] . ", '" . htmlspecialchars($item['element']) . "', '" . htmlspecialchars($item['directElement']) . "', " . (int)$item['elementPosition'] . ", " . (int)$item['elementPlace'] . ", " . (int)$item['firstPlace'] . ", " . (int)$item['elementCount'] . ", " . (int)$item['elementStep'] . ")";
//			}
//			$sqlTokenSave .= " ON DUPLICATE KEY UPDATE text = values(text), setting_type = values(setting_type), element = values(element), directElement = values(directElement), elementPosition = values(elementPosition), elementPlace = values(elementPlace), firstPlace = values(firstPlace), elementCount = values(elementCount), elementStep = values(elementStep) ";
//
//			$wpdb->query($sqlTokenSave);
//			// if no needly note, then create
//			$wpOptionsChecker = $wpdb->query("SELECT optionValue FROM realbigSettings WHERE optionName = '_wpRealbigPluginToken'");
//			if (empty($wpOptionsChecker))
//			{
//				$wpdb->insert('realbigSettings', ['optionName'=>'_wpRealbigPluginToken', 'optionValue'=>$_POST["tokenInput"]]);
//			}
//			else
//			{
//				$wpdb->update('realbigSettings', ['optionName'=>'_wpRealbigPluginToken', 'optionValue'=>$_POST["tokenInput"]], ['optionName'=>'_wpRealbigPluginToken']);
//			}
//			$GLOBALS['token'] = $_POST["tokenInput"];
//		}
//		catch (Exception $e)
//		{
//			$tokenStatusMessage = $e;
//		}
//	}
//
//	$returnArray = [
//
//	];
//
//	return $returnArray;
//
//}