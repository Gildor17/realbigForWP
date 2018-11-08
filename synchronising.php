<?php
/**
 * Created by PhpStorm.
 * User: furio
 * Date: 2018-08-02
 * Time: 18:17
 */

include_once ( dirname(__FILE__).'/../../../wp-load.php' );
include_once ( dirname(__FILE__)."/../../../wp-admin/includes/plugin.php" );
include_once ( dirname(__FILE__)."/../../../wp-admin/includes/upgrade.php" );
include_once ( dirname(__FILE__).'/../../../wp-includes/wp-db.php');

try {
	global $wpdb;

	if ( ! function_exists( 'synchronize' ) ) {
		function synchronize( $tokenInput, $wpOptionsCheckerSyncTime, $sameTokenResult, $wpPrefix, $requestType ) {
			global $wpdb;
			$unsuccessfullAjaxSyncAttempt = 0;

			try {
//			$url = 'http://realbigweb/api/wp-get-settings';     // orig web post
			$url = 'https://realbig.media/api/wp-get-settings';     // orig post

				$dataForSending = [
					'token'     => $tokenInput,
					'sameToken' => $sameTokenResult
				];

				try {
//		$ch = curl_init('https://realbig.media/api/wp-get-settings?token='.$tokenInput);
					$ch = curl_init();
					curl_setopt( $ch, CURLOPT_URL, $url );
					curl_setopt( $ch, CURLOPT_POST, 1 );
					curl_setopt( $ch, CURLOPT_POSTFIELDS, $dataForSending );
					curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
					curl_setopt( $ch, CURLOPT_TIMEOUT, 15 );
					curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 30 );
					curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, false );
					curl_setopt( $ch, CURLOPT_ENCODING, 'gzip, deflate' );
					curl_setopt( $ch, CURLOPT_COOKIE, '' );
					curl_setopt( $ch, CURLOPT_COOKIEFILE, '' );
					curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
					curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );
					$jsonToken = curl_exec( $ch );
					curl_close( $ch );

					if ( ! empty( $jsonToken ) ) {
						$GLOBALS['connection_request_rezult']   = 1;
						$GLOBALS['connection_request_rezult_1'] = 'success';
					} else {
						$error                                  = error_get_last();
						$GLOBALS['connection_request_rezult']   = 'Connection error: ' . $error['message'];
						$GLOBALS['connection_request_rezult_1'] = 'Connection error: ' . $error['message'];
						$unsuccessfullAjaxSyncAttempt           = 1;
					}
				} catch ( Exception $e ) {
					$GLOBALS['tokenStatusMessage'] = $e['message'];
					if ( $requestType == 'ajax' ) {
						$ajaxResult = $e['message'];
					}
					$unsuccessfullAjaxSyncAttempt = 1;
				}
				if ( ! empty( $jsonToken ) ) {
					$decodedToken                  = json_decode( $jsonToken, true );
					$GLOBALS['tokenStatusMessage'] = $decodedToken['message'];
					if ( $requestType == 'ajax' ) {
						$ajaxResult = $decodedToken['message'];
					}
					if (!empty($decodedToken)&&($decodedToken['status']=='success'||$decodedToken['status']=='empty_success')) {
						try {
							if (!empty($decodedToken['data'])) {
								wp_cache_flush();
								$excludedPagesCheck = $wpdb->query( $wpdb->prepare( "SELECT optionValue FROM " . $wpPrefix . "realbig_settings WHERE optionName = %s", [ 'excludedPages' ]));
								if (!isset($decodedToken['excludedPages'])) {
									$decodedToken['excludedPages'] = "";
								}
								if (empty($excludedPagesCheck)) {
									$wpdb->insert($wpPrefix.'realbig_settings', [
										'optionName'  => 'excludedPages',
										'optionValue' => $decodedToken['excludedPages']
									]);
                                } else {
									$wpdb->update( $wpPrefix.'realbig_settings', [
										'optionName'  => 'excludedPages',
										'optionValue' => $decodedToken['excludedPages']
									], ['optionName'  => 'excludedPages']);
                                }
							    $counter = 0;
							    $wpdb->query( 'DELETE FROM ' . $wpPrefix . 'realbig_plugin_settings');
								$sqlTokenSave = "INSERT INTO " . $wpPrefix . "realbig_plugin_settings (text, block_number, setting_type, element, directElement, elementPosition, elementPlace, firstPlace, elementCount, elementStep, minSymbols, minHeaders) VALUES ";
								foreach ( $decodedToken['data'] AS $k => $item ) {
								    $counter ++;
								    $sqlTokenSave .= ( $counter != 1 ? ", " : "" ) . "('" . $item['text'] . "', " . (int) $item['block_number'] . ", " . (int) $item['setting_type'] . ", '" . htmlspecialchars( $item['element'] ) . "', '" . htmlspecialchars( $item['directElement'] ) . "', " . (int) $item['elementPosition'] . ", " . (int) $item['elementPlace'] . ", " . (int) $item['firstPlace'] . ", " . (int) $item['elementCount'] . ", " . (int) $item['elementStep'] . ", " . (int) $item['minSymbols'] . ", " . (int) $item['minHeaders'] . ")";
							    }
							    $sqlTokenSave .= " ON DUPLICATE KEY UPDATE text = values(text), setting_type = values(setting_type), element = values(element), directElement = values(directElement), elementPosition = values(elementPosition), elementPlace = values(elementPlace), firstPlace = values(firstPlace), elementCount = values(elementCount), elementStep = values(elementStep), minSymbols = values(minSymbols), minHeaders = values(minHeaders) ";
								$wpdb->query( $sqlTokenSave );
							} elseif (empty($decodedToken['data'])&&$decodedToken['status'] == "empty_success") {
								$wpdb->query( 'DELETE FROM ' . $wpPrefix . 'realbig_plugin_settings');
							}

							// if no needly note, then create
							$wpOptionsCheckerTokenValue = $wpdb->query( $wpdb->prepare( "SELECT optionValue FROM " . $wpPrefix . "realbig_settings WHERE optionName = %s", [ '_wpRealbigPluginToken' ] ) );
							if ( empty( $wpOptionsCheckerTokenValue ) ) {
								$wpdb->insert( $wpPrefix . 'realbig_settings', [
									'optionName'  => '_wpRealbigPluginToken',
									'optionValue' => $tokenInput
								] );
							} else {
								$wpdb->update( $wpPrefix . 'realbig_settings', [
									'optionName'  => '_wpRealbigPluginToken',
									'optionValue' => $tokenInput
								], [ 'optionName' => '_wpRealbigPluginToken' ] );
							}
							if ( ! empty( $decodedToken['dataPush'] ) ) {
								$wpOptionsCheckerPushStatus = $wpdb->query( $wpdb->prepare( "SELECT optionValue FROM " . $wpPrefix . "realbig_settings WHERE optionName = %s", [ 'pushStatus' ] ) );
								if ( empty( $wpOptionsCheckerPushStatus ) ) {
									$wpdb->insert( $wpPrefix . 'realbig_settings', [
										'optionName'  => 'pushStatus',
										'optionValue' => $decodedToken['dataPush']['pushStatus']
									] );
								} else {
									$wpdb->update( $wpPrefix . 'realbig_settings', [
										'optionName'  => 'pushStatus',
										'optionValue' => $decodedToken['dataPush']['pushStatus']
									], [ 'optionName' => 'pushStatus' ] );
								}
								$wpOptionsCheckerPushCode = $wpdb->query( $wpdb->prepare( "SELECT optionValue FROM " . $wpPrefix . "realbig_settings WHERE optionName = %s", [ 'pushCode' ] ) );
								if ( empty( $wpOptionsCheckerPushCode ) ) {
									$wpdb->insert( $wpPrefix . 'realbig_settings', [
										'optionName'  => 'pushCode',
										'optionValue' => $decodedToken['dataPush']['pushCode']
									] );
								} else {
									$wpdb->update( $wpPrefix . 'realbig_settings', [
										'optionName'  => 'pushCode',
										'optionValue' => $decodedToken['dataPush']['pushCode']
									], [ 'optionName' => 'pushCode' ] );
								}
							}
							if ( ! empty( $decodedToken['domain'] ) ) {
								$getDomain = $wpdb->get_var( 'SELECT optionValue FROM ' . $wpPrefix . 'realbig_settings WHERE optionName = "domain"' );
								if ( ! empty( $getDomain ) ) {
									$wpdb->update( $wpPrefix . 'realbig_settings', [
										'optionName'  => 'domain',
										'optionValue' => $decodedToken['domain']
									], [ 'optionName' => 'domain' ] );
								} else {
									$wpdb->insert( $wpPrefix . 'realbig_settings', [
										'optionName'  => 'domain',
										'optionValue' => $decodedToken['domain']
									] );
								}
							}
							$GLOBALS['token'] = $tokenInput;
						} catch ( Exception $e ) {
							$GLOBALS['tokenStatusMessage'] = $e;
							$unsuccessfullAjaxSyncAttempt  = 1;
						}
					}
				} else {
					$decodedToken                  = null;
					$GLOBALS['tokenStatusMessage'] = 'ошибка соединения';
					$decodedToken['status']        = 'error';
					if ( $requestType == 'ajax' ) {
						$ajaxResult = 'connection error';
					}
					$unsuccessfullAjaxSyncAttempt = 1;
				}

				$unmarkSuccessfulUpdate = $wpdb->get_var( 'SELECT optionValue FROM ' . $wpPrefix . 'realbig_settings WHERE optionName = "successUpdateMark"' );
				if ( ! empty( $unmarkSuccessfulUpdate ) ) {
					$wpdb->update( $wpPrefix . 'realbig_settings', [ 'optionValue' => 'success' ], [ 'optionName' => 'successUpdateMark' ] );
				} else {
					$wpdb->insert( $wpPrefix . 'realbig_settings', [ 'optionName'  => 'successUpdateMark',
					                                                 'optionValue' => 'success'
					] );
				}

				try {
					set_transient( 'realbigPluginSyncAttempt', $decodedToken['status'], 300 );
					if ( $decodedToken['status'] == 'success' ) {
						if ( empty( $wpOptionsCheckerSyncTime ) ) {
							$wpdb->insert( $wpPrefix . 'realbig_settings', [
								'optionName'  => 'token_sync_time',
								'optionValue' => time()
							] );
						} else {
							$wpdb->update( $wpPrefix . 'realbig_settings', [
								'optionName'  => 'token_sync_time',
								'optionValue' => time()
							], [ 'optionName' => 'token_sync_time' ] );
						}
					}
				} catch ( Exception $e ) {
					echo $e;
				}
				if ( $requestType == 'ajax' ) {
					if ( empty( $ajaxResult ) ) {
						return 'error';
					} else {
						return $ajaxResult;
					}
				}
			} catch ( Exception $e ) {
				echo $e;
				if ( $requestType == 'ajax' ) {
					if ( empty( $ajaxResult ) ) {
						return 'error';
					} else {
						return $ajaxResult;
					}
				}
			}
		}
	}

	if ( ! function_exists( 'tokenChecking' ) ) {
		function tokenChecking( $wpPrefix ) {
			global $wpdb;

			try {
				$GLOBALS['tokenStatusMessage'] = null;
				$token                         = $wpdb->get_results( "SELECT optionValue FROM " . $wpPrefix . "realbig_settings WHERE optionName = '_wpRealbigPluginToken'" );

				if ( ! empty( $token ) ) {
					$token            = get_object_vars( $token[0] );
					$GLOBALS['token'] = $token['optionValue'];
					$token            = $token['optionValue'];
				} else {
					$GLOBALS['token'] = 'no token';
					$token            = 'no token';
				}

				return $token;
			} catch ( Exception $e ) {
				return 'no token';
			}
		}
	}

	if ( ! function_exists( 'tokenTimeUpdateChecking' ) ) {
		function tokenTimeUpdateChecking( $token, $wpPrefix ) {
			global $wpdb;
			try {
				$timeUpdate = $wpdb->get_results( "SELECT timeUpdate FROM " . $wpPrefix . "realbig_settings WHERE optionName = 'token_sync_time'" );
				if ( empty( $timeUpdate ) ) {
					$updateResult = wpRealbigSettingsTableUpdateFunction( $wpPrefix );
					if ( $updateResult == true ) {
						$timeUpdate = $wpdb->get_results( "SELECT timeUpdate FROM " . $wpPrefix . "realbig_settings WHERE optionName = 'token_sync_time'" );
					}
				}
				if (!empty($token)&&$token != 'no token'&&((!empty($GLOBALS['tokenStatusMessage'])&&($GLOBALS['tokenStatusMessage'] == 'Синхронизация прошла успешно' || $GLOBALS['tokenStatusMessage'] == 'Не нашло позиций для блоков на указанном сайте, добавьте позиции для сайтов на странице настроек плагина')) || empty($GLOBALS['tokenStatusMessage'])) && !empty($timeUpdate)) {
//				if (!empty($token)&&$token!='no token'&&!empty($timeUpdate)) {
					if ( ! empty( $timeUpdate ) ) {
						$timeUpdate                 = get_object_vars( $timeUpdate[0] );
						$GLOBALS['tokenTimeUpdate'] = $timeUpdate['timeUpdate'];
						$GLOBALS['statusColor']     = 'green';
					} else {
						$GLOBALS['tokenTimeUpdate'] = '';
						$GLOBALS['statusColor']     = 'red';
					}
				} else {
					$GLOBALS['tokenTimeUpdate'] = 'never';
					$GLOBALS['statusColor']     = 'red';
				}
			} catch ( Exception $e ) {
				echo $e;
			}
		}
	}

	if ( ! function_exists( 'statusGathererConstructor' ) ) {
		function statusGathererConstructor( $pointer ) {
			global $wpdb;
			try {
				$statusGatherer        = [];
				$realbigStatusGatherer = get_option( 'realbig_status_gatherer' );

				if ( $pointer == false ) {
					$statusGatherer['element_column_values']           = false;
					$statusGatherer['old_tables_removed']              = false;
					$statusGatherer['realbig_plugin_settings_table']   = false;
					$statusGatherer['realbig_settings_table']          = false;
					$statusGatherer['realbig_plugin_settings_columns'] = false;
					if ( ! empty( $realbigStatusGatherer ) ) {
						$statusGatherer['update_status_gatherer'] = true;
					} else {
						$statusGatherer['update_status_gatherer'] = false;
					}

					return $statusGatherer;
				} else {
					if ( ! empty( $realbigStatusGatherer ) ) {
//	        $realbigStatusGatherer = $errorVariable;
						$realbigStatusGatherer                             = json_decode( $realbigStatusGatherer, true );
						$statusGatherer['element_column_values']           = $realbigStatusGatherer['element_column_values'];
						$statusGatherer['old_tables_removed']              = $realbigStatusGatherer['old_tables_removed'];
						$statusGatherer['realbig_plugin_settings_table']   = $realbigStatusGatherer['realbig_plugin_settings_table'];
						$statusGatherer['realbig_settings_table']          = $realbigStatusGatherer['realbig_settings_table'];
						$statusGatherer['realbig_plugin_settings_columns'] = $realbigStatusGatherer['realbig_plugin_settings_columns'];
						$statusGatherer['update_status_gatherer']          = true;

						return $statusGatherer;
					} else {
//	        $realbigStatusGatherer = $errorVariable;
//	            throw new Error();
						$statusGatherer['element_column_values']           = false;
						$statusGatherer['old_tables_removed']              = false;
						$statusGatherer['realbig_plugin_settings_table']   = false;
						$statusGatherer['realbig_settings_table']          = false;
						$statusGatherer['realbig_plugin_settings_columns'] = false;
						$statusGatherer['update_status_gatherer']          = false;

						return $statusGatherer;
					}
				}
			} catch ( Exception $exception ) {
				return $statusGatherer = [];
//        $catchedException = true;
			} catch ( Error $error ) {
				return $statusGatherer = [];
//		$catchedError = true;
			}
		}
	}

	$jsAutoSynchronizationStatus = intval( $wpdb->get_var( 'SELECT optionValue FROM ' . $GLOBALS['table_prefix'] . 'realbig_settings WHERE optionName = "jsAutoSyncFails"' ) );
	if ( ! empty( $jsAutoSynchronizationStatus ) && $jsAutoSynchronizationStatus < 5 && ! empty( $_POST['funcActivator'] ) && $_POST['funcActivator'] == 'ready' ) {
		$activeSyncTransient = get_transient( 'realbigPluginSyncProcess' );
		if ( $activeSyncTransient == false ) {
			set_transient( 'realbigPluginSyncProcess', 'true', 30 );
			$wpOptionsCheckerSyncTime = $wpdb->get_row( $wpdb->prepare( 'SELECT optionValue FROM ' . $GLOBALS['table_prefix'] . 'realbig_settings WHERE optionName = %s', [ "token_sync_time" ] ) );
			if ( ! empty( $wpOptionsCheckerSyncTime ) ) {
				$wpOptionsCheckerSyncTime = get_object_vars( $wpOptionsCheckerSyncTime );
			}
			$token      = tokenChecking( $GLOBALS['table_prefix'] );
			$ajaxResult = synchronize( $token, $wpOptionsCheckerSyncTime, true, $GLOBALS['table_prefix'], 'ajax' );
//	    echo $ajaxResult;
		}
	}

}
catch (Exception $ex)
{
	deactivate_plugins(plugin_basename( __FILE__ ));
	?><div style="margin-left: 200px; border: 3px solid red"><?php echo $ex; ?></div><?php
}
catch (Error $er)
{
	deactivate_plugins(plugin_basename( __FILE__ ));
	?><div style="margin-left: 200px; border: 3px solid red"><?php echo $er; ?></div><?php
}