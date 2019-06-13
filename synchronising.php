<?php
/**
 * Created by PhpStorm.
 * User: furio
 * Date: 2018-08-02
 * Time: 18:17
 */

if (!defined("ABSPATH")) { exit;}

try {
	global $wpdb;

	if (!function_exists('RFWP_synchronize')) {
		function RFWP_synchronize($tokenInput, $wpOptionsCheckerSyncTime, $sameTokenResult, $wpPrefix, $requestType) {
			global $wpdb;
			$unsuccessfullAjaxSyncAttempt = 0;

			try {
//			$url = 'https://realbigweb/api/wp-get-settings';     // orig web post
//			$url = 'https://beta.realbig.media/api/wp-get-settings';     // beta post
			$url = 'https://realbig.media/api/wp-get-settings';     // orig post

                /** for WP request **/
				$dataForSending = [
				    'body'  => [
                        'token'     => $tokenInput,
                        'sameToken' => $sameTokenResult
                    ]
				];
				try {
					$jsonToken = wp_safe_remote_post($url, $dataForSending);
//					$jsonToken = wp_remote_post($url, $dataForSending);
					if (!is_wp_error($jsonToken)) {
						$jsonToken = $jsonToken['body'];
						if (!empty($jsonToken)) {
							$GLOBALS['connection_request_rezult']   = 1;
							$GLOBALS['connection_request_rezult_1'] = 'success';
						}
                    } else {
						$error                                  = $jsonToken->get_error_message();
						$GLOBALS['connection_request_rezult']   = 'Connection error: ' . $error;
						$GLOBALS['connection_request_rezult_1'] = 'Connection error: ' . $error;
						$unsuccessfullAjaxSyncAttempt           = 1;
                    }
				} catch (Exception $e) {
					$GLOBALS['tokenStatusMessage'] = $e['message'];
					if ( $requestType == 'ajax' ) {
						$ajaxResult = $e['message'];
					}
					$unsuccessfullAjaxSyncAttempt = 1;
				}
				if (!empty($jsonToken)&&!is_wp_error($jsonToken)) {
					$decodedToken                  = json_decode( $jsonToken, true );
					$GLOBALS['tokenStatusMessage'] = $decodedToken['message'];
					if ( $requestType == 'ajax' ) {
						$ajaxResult = $decodedToken['message'];
					}
					if (!empty($decodedToken)) {
					    $sanitisedStatus = sanitize_text_field($decodedToken['status']);
					    if ($sanitisedStatus=='success'||$sanitisedStatus=='empty_success') {
						    try {
							    if (!empty($decodedToken['data'])) {
								    wp_cache_flush();
								    if (!empty($decodedToken['excludedPages'])) {
								        $sanitisedExcludedPages = sanitize_text_field($decodedToken['excludedPages']);
                                    } else {
									    $sanitisedExcludedPages = '';
                                    }
								    $excludedPagesCheck = $wpdb->query( $wpdb->prepare( "SELECT optionValue FROM " . $wpPrefix . "realbig_settings WHERE optionName = %s", [ 'excludedPages' ]));
//								    if (!isset($decodedToken['excludedPages'])) {
//									    $decodedToken['excludedPages'] = "";
//								    }
								    if (empty($excludedPagesCheck)) {
									    $wpdb->insert($wpPrefix.'realbig_settings', ['optionName'  => 'excludedPages', 'optionValue' => $sanitisedExcludedPages]);
								    } else {
									    $wpdb->update( $wpPrefix.'realbig_settings', ['optionName'  => 'excludedPages', 'optionValue' => $sanitisedExcludedPages],
										    ['optionName'  => 'excludedPages']);
								    }

								    if (!empty($decodedToken['excludedMainPage'])) {
									    $sanitisedExcludedMainPages = sanitize_text_field($decodedToken['excludedMainPage']);
									    if (intval($sanitisedExcludedMainPages)) {
									        if (strlen($sanitisedExcludedMainPages) > 1) {
										        $sanitisedExcludedMainPages = '';
									        }
                                        } else {
										    $sanitisedExcludedMainPages = '';
                                        }
								    } else {
									    $sanitisedExcludedMainPages = '';
								    }
								    $excludedMainPageCheck = $wpdb->query( $wpdb->prepare( "SELECT optionValue FROM " . $wpPrefix . "realbig_settings WHERE optionName = %s", [ 'excludedMainPage' ]));
								    if (isset($decodedToken['excludedMainPage'])) {
									    if (empty($excludedMainPageCheck)) {
										    $wpdb->insert($wpPrefix.'realbig_settings', ['optionName'  => 'excludedMainPage', 'optionValue' => $sanitisedExcludedMainPages]);
									    } else {
										    $wpdb->update($wpPrefix.'realbig_settings', ['optionName'  => 'excludedMainPage', 'optionValue' => $sanitisedExcludedMainPages],
											    ['optionName'  => 'excludedMainPage']);
									    }
								    }

								    $counter = 0;
								    $wpdb->query( 'DELETE FROM ' . $wpPrefix . 'realbig_plugin_settings');
								    $sqlTokenSave = "INSERT INTO " . $wpPrefix . "realbig_plugin_settings (text, block_number, setting_type, element, directElement, elementPosition, elementPlace, firstPlace, elementCount, elementStep, minSymbols, minHeaders, onCategories, offCategories, onTags, offTags) VALUES ";
								    foreach ( $decodedToken['data'] AS $k => $item ) {
//								        foreach ($item AS $k1 => $item1) {
//								            if (in_array($k1, ['onCategories', 'offCategories', 'onTags', 'offTags'])) {
//								                if (empty($item1)) {
//								                    $item[$k1] = 'null';
//                                                }
//                                            }
//                                        }
//									    unset($k1, $item1);
//
									    $penyok_stoparik = 0;

									    $counter ++;
									    $sqlTokenSave .= ($counter != 1 ? ", " : "") . "('" . $item['text'] . "', " . (int) sanitize_text_field($item['block_number']) . ", " . (int) sanitize_text_field($item['setting_type']) . ", '" . sanitize_text_field($item['element']) . "', '" . sanitize_text_field( $item['directElement'] ) . "', " . (int) sanitize_text_field($item['elementPosition']) . ", " . (int) sanitize_text_field($item['elementPlace']) . ", " . (int) sanitize_text_field($item['firstPlace']) . ", " . (int) sanitize_text_field($item['elementCount']) . ", " . (int) sanitize_text_field($item['elementStep']) . ", " . (int) sanitize_text_field($item['minSymbols']) . ", " . (int) sanitize_text_field($item['minHeaders']).", '".sanitize_text_field($item['onCategories'])."', '".sanitize_text_field($item['offCategories'])."', '".sanitize_text_field($item['onTags'])."', '".sanitize_text_field($item['offTags'])."')";
								    }
								    unset($k, $item);
								    $sqlTokenSave .= " ON DUPLICATE KEY UPDATE text = values(text), setting_type = values(setting_type), element = values(element), directElement = values(directElement), elementPosition = values(elementPosition), elementPlace = values(elementPlace), firstPlace = values(firstPlace), elementCount = values(elementCount), elementStep = values(elementStep), minSymbols = values(minSymbols), minHeaders = values(minHeaders), minHeaders = values(onCategories), minHeaders = values(offCategories), minHeaders = values(onTags), minHeaders = values(offTags) ";
								    $wpdb->query($sqlTokenSave);
							    } elseif (empty($decodedToken['data'])&&sanitize_text_field($decodedToken['status']) == "empty_success") {
								    $wpdb->query('DELETE FROM '.$wpPrefix.'realbig_plugin_settings');
							    }

							    // if no needly note, then create
							    $wpOptionsCheckerTokenValue = $wpdb->query( $wpdb->prepare( "SELECT optionValue FROM " . $wpPrefix . "realbig_settings WHERE optionName = %s", [ '_wpRealbigPluginToken' ] ) );
							    if (empty($wpOptionsCheckerTokenValue)) {
								    $wpdb->insert( $wpPrefix . 'realbig_settings', ['optionName'  => '_wpRealbigPluginToken', 'optionValue' => $tokenInput]);
							    } else {
								    $wpdb->update( $wpPrefix . 'realbig_settings', ['optionName'  => '_wpRealbigPluginToken', 'optionValue' => $tokenInput],
									    ['optionName' => '_wpRealbigPluginToken']);
							    }
							    if (!empty($decodedToken['dataPush'])) {
							        $sanitisedPushStatus = sanitize_text_field($decodedToken['dataPush']['pushStatus']);
							        $sanitisedPushData = sanitize_text_field($decodedToken['dataPush']['pushCode']);
								    $wpOptionsCheckerPushStatus = $wpdb->query( $wpdb->prepare( "SELECT optionValue FROM " . $wpPrefix . "realbig_settings WHERE optionName = %s", [ 'pushStatus' ] ) );
								    if (empty($wpOptionsCheckerPushStatus)) {
									    $wpdb->insert( $wpPrefix . 'realbig_settings', ['optionName'  => 'pushStatus', 'optionValue' => $sanitisedPushStatus]);
								    } else {
									    $wpdb->update( $wpPrefix . 'realbig_settings', ['optionName'  => 'pushStatus', 'optionValue' => $sanitisedPushStatus],
										    ['optionName' => 'pushStatus']);
								    }
								    $wpOptionsCheckerPushCode = $wpdb->query( $wpdb->prepare( "SELECT optionValue FROM " . $wpPrefix . "realbig_settings WHERE optionName = %s", [ 'pushCode' ] ) );
								    if (empty($wpOptionsCheckerPushCode)) {
									    $wpdb->insert( $wpPrefix . 'realbig_settings', ['optionName'  => 'pushCode', 'optionValue' => $sanitisedPushData]);
								    } else {
									    $wpdb->update( $wpPrefix . 'realbig_settings', ['optionName'  => 'pushCode', 'optionValue' => $sanitisedPushData],
										    ['optionName' => 'pushCode']);
								    }
							    }
							    if (!empty($decodedToken['domain'])) {
							        $sanitisedDomain = sanitize_text_field($decodedToken['domain']);
								    $getDomain = $wpdb->get_var( 'SELECT optionValue FROM ' . $wpPrefix . 'realbig_settings WHERE optionName = "domain"' );
								    if (!empty($getDomain)) {
									    $wpdb->update( $wpPrefix . 'realbig_settings', ['optionName'  => 'domain', 'optionValue' => $sanitisedDomain],
										    ['optionName' => 'domain']);
								    } else {
									    $wpdb->insert( $wpPrefix . 'realbig_settings', ['optionName'  => 'domain', 'optionValue' => $sanitisedDomain]);
								    }
							    }
							    if (!empty($decodedToken['rotator'])) {
							        $sanitisedRotator = sanitize_text_field($decodedToken['rotator']);
								    $getRotator = $wpdb->get_var( 'SELECT optionValue FROM ' . $wpPrefix . 'realbig_settings WHERE optionName = "rotator"' );
								    if (!empty($getRotator)) {
									    $wpdb->update( $wpPrefix.'realbig_settings', ['optionName'  => 'rotator', 'optionValue' => $decodedToken['rotator']],
										    ['optionName' => 'rotator']);
								    } else {
									    $wpdb->insert( $wpPrefix.'realbig_settings', ['optionName'  => 'rotator', 'optionValue' => $decodedToken['rotator']]);
								    }
							    }
							    /** Excluded page types */
							    if (isset($decodedToken['excludedPageTypes'])) {
							        $excludedPageTypes = sanitize_text_field($decodedToken['excludedPageTypes']);
								    $getExcludedPageTypes = $wpdb->get_var('SELECT id FROM '.$wpPrefix.'realbig_settings WHERE optionName = "excludedPageTypes"');
								    if (!empty($getExcludedPageTypes)) {
									    $updateResult = $wpdb->update($wpPrefix.'realbig_settings', ['optionName'=>'excludedPageTypes', 'optionValue'=>$excludedPageTypes],
										    ['optionName' => 'excludedPageTypes']);
                                    } else {
									    $wpdb->insert($wpPrefix.'realbig_settings', ['optionName'=>'excludedPageTypes', 'optionValue'=>$excludedPageTypes]);
								    }
							    }
							    /** End of excluded page types */
							    /** Live internet code */
//							    if (isset($decodedToken['liveInternetCode'])) {
//							        $liveInternetCode = sanitize_text_field($decodedToken['liveInternetCode']);
//								    $getLiveInternetCode = $wpdb->get_var('SELECT id FROM '.$wpPrefix.'realbig_settings WHERE optionName = "liveInternetCode"');
//								    if (!empty($getLiveInternetCode)) {
//									    $updateResult = $wpdb->update($wpPrefix.'realbig_settings', ['optionName'=>'liveInternetCode', 'optionValue'=>$liveInternetCode],
//										    ['optionName' => 'liveInternetCode']);
//                                    } else {
//									    $wpdb->insert($wpPrefix.'realbig_settings', ['optionName'=>'liveInternetCode', 'optionValue'=>$liveInternetCode]);
//								    }
//							    }
							    /** End of live internet code */
							    /** Live internet active check */
//							    if (isset($decodedToken['activeLiveInterner'])) {
//							        $activeLiveInternet = sanitize_text_field($decodedToken['activeLiveInterner']);
//								    $getActiveLiveInternet = $wpdb->get_var('SELECT id FROM '.$wpPrefix.'realbig_settings WHERE optionName = "activeLiveInterner"');
//								    if (!empty($getLiveInternetCode)) {
//									    $updateResult = $wpdb->update($wpPrefix.'realbig_settings', ['optionName'=>'activeLiveInterner', 'optionValue'=>$activeLiveInternet],
//										    ['optionName' => 'activeLiveInterner']);
//                                    } else {
//									    $wpdb->insert($wpPrefix.'realbig_settings', ['optionName'=>'activeLiveInterner', 'optionValue'=>$activeLiveInternet]);
//								    }
//							    }
							    /** End of live internet active check */
							    /** Insertings */
							    if (!empty($decodedToken['insertings'])) {
								    $insertings = $decodedToken['insertings'];
                                    $oldInserts = get_posts(['post_type' => 'rb_inserting','numberposts' => 100]);
                                    if (!empty($oldInserts)&&in_array($insertings['status'],['ok','empty'])) {
	                                    foreach ($oldInserts AS $k => $item) {
		                                    wp_delete_post($item->ID);
                                        }
	                                    unset($k, $item);
                                    }

							        if ($insertings['status']='ok') {
							            foreach ($insertings['data'] AS $k=>$item) {
							                $content_for_post = 'begin_of_header_code'.$item['headerField'].'end_of_header_code&begin_of_body_code'.$item['bodyField'].'end_of_body_code';

								            $postarr = [
									            'post_content' => $content_for_post,
									            'post_title'   => $item['position_element'],
									            'post_excerpt' => $item['position'],
									            'post_name'    => $item['name'],
									            'post_status'  => "publish",
									            'post_type'    => 'rb_inserting',
									            'post_author'  => 0,
									            'pinged'       => $item['limitationUse'],
//									            'ping_status'  => $item['limitationUse'],
//									            'post_content_filtered' => 12,
								            ];
								            require_once(dirname(__FILE__ ) . "/../../../wp-includes/pluggable.php");
								            $saveInsertResult = wp_insert_post($postarr, true);
                                        }
								        unset($k, $item);
							        }
                                }
							    /** End of insertings */

							    $GLOBALS['token'] = $tokenInput;

							    delete_transient('rb_mobile_cache_timeout' );
							    delete_transient('rb_desktop_cache_timeout');

						    } catch ( Exception $e ) {
							    $GLOBALS['tokenStatusMessage'] = $e;
							    $unsuccessfullAjaxSyncAttempt  = 1;
						    }
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
					$wpdb->update( $wpPrefix . 'realbig_settings', ['optionValue' => 'success'], ['optionName' => 'successUpdateMark']);
				} else {
					$wpdb->insert( $wpPrefix . 'realbig_settings', ['optionName'  => 'successUpdateMark', 'optionValue' => 'success']);
				}

				try {
					set_transient('realbigPluginSyncAttempt', $decodedToken['status'], 300);
					if ($decodedToken['status'] == 'success') {
						if (empty($wpOptionsCheckerSyncTime)) {
							$wpdb->insert( $wpPrefix . 'realbig_settings', ['optionName'  => 'token_sync_time', 'optionValue' => time()]);
						} else {
							$wpdb->update( $wpPrefix . 'realbig_settings', ['optionName'  => 'token_sync_time', 'optionValue' => time()],
                            ['optionName' => 'token_sync_time']);
						}
					}
				} catch (Exception $e) {
					echo $e;
				}
				if ($requestType == 'ajax') {
					if (empty($ajaxResult)) {
						return 'error';
					} else {
						return $ajaxResult;
					}
				} else {
					wp_cache_flush();
                }
			} catch (Exception $e) {
				echo $e;
				if ($requestType == 'ajax') {
					if (empty($ajaxResult)) {
						return 'error';
					} else {
						return $ajaxResult;
					}
				}
			}
		}
	}

	if (!function_exists('RFWP_savingCodeForCache')) {
		function RFWP_savingCodeForCache($blocksAd=null) {
			global $wpdb;
			global $wpPrefix;
//            global $token;
            $resultTypes = [];

            try {
//    			$url = 'https://realbigweb/api/wp-get-ads';     // orig web post
//                $url = 'https://beta.realbig.media/api/wp-get-ads';     // beta post
    			$url = 'https://realbig.media/api/wp-get-ads';     // orig post

	            $dataForSending = [
		            'body'  => [
//			            'token'    => $token,
			            'blocksAd' => $blocksAd
		            ]
	            ];

	            $jsonResult = wp_safe_remote_post($url, $dataForSending);
//	            $jsonResult = wp_remote_post($url, $dataForSending);

                if (!empty($jsonResult)&&!is_wp_error($jsonResult)) {
//                    $decodedResult = json_decode($jsonResult, true);
                    $decodedResult = json_decode($jsonResult['body'], true);
                    if (!empty($decodedResult)) {
	                    $sanitisedStatus = sanitize_text_field($decodedResult['status']);
	                    if ($sanitisedStatus=='success') {
	                        $resultData = $decodedResult['data'];

		                    $resultTypes['mobile'] = false;
		                    $resultTypes['desktop'] = false;
		                    $resultTypes['universal'] = false;

		                    foreach ($resultData AS $rk => $ritem) {
			                    $postCheckMobile = null;
			                    $postCheckDesktop = null;

			                    switch ($ritem['type']) {
                                    case 'mobile':
	                                    $postCheckMobile  = $wpdb->get_var($wpdb->prepare('SELECT id FROM '.$wpPrefix.'posts WHERE post_type = %s AND post_title = %s',['rb_block_mobile_new',$ritem['blockId']]));
	                                    $resultTypes['mobile'] = true;
	                                    break;
                                    case 'desktop':
	                                    $postCheckDesktop = $wpdb->get_var($wpdb->prepare('SELECT id FROM '.$wpPrefix.'posts WHERE post_type = %s AND post_title = %s',['rb_block_desktop_new',$ritem['blockId']]));
	                                    $resultTypes['desktop'] = true;
	                                    break;
                                    case 'universal':
	                                    $postCheckMobile  = $wpdb->get_var($wpdb->prepare('SELECT id FROM '.$wpPrefix.'posts WHERE post_type = %s AND post_title = %s',['rb_block_mobile_new',$ritem['blockId']]));
	                                    $postCheckDesktop = $wpdb->get_var($wpdb->prepare('SELECT id FROM '.$wpPrefix.'posts WHERE post_type = %s AND post_title = %s',['rb_block_desktop_new',$ritem['blockId']]));
	                                    $resultTypes['universal'] = true;
	                                    break;
                                }

                                if (in_array($ritem['type'], ['mobile','universal'])) {
	                                if (!empty($postCheckMobile)) {
		                                $postarr = ['ID' => $postCheckMobile, 'post_content' => $ritem['code']];
		                                $updateBlockResultMobile = wp_update_post($postarr, true);
	                                } else {
		                                $postarr = [
			                                'post_content' => $ritem['code'],
			                                'post_title'   => $ritem['blockId'],
			                                'post_status'  => "publish",
			                                'post_type'    => 'rb_block_mobile_new',
			                                'post_author'  => 0
		                                ];
		                                require_once(dirname(__FILE__ )."/../../../wp-includes/pluggable.php");
		                                $saveBlockResultMobile = wp_insert_post($postarr, true);
	                                }
                                }
                                if (in_array($ritem['type'], ['desktop','universal'])) {
	                                if (!empty($postCheckDesktop)) {
		                                $postarr = ['ID' => $postCheckDesktop, 'post_content' => $ritem['code']];
		                                $updateBlockResultDesktop = wp_update_post($postarr, true);
	                                } else {
		                                $postarr = [
			                                'post_content' => $ritem['code'],
			                                'post_title'   => $ritem['blockId'],
			                                'post_status'  => "publish",
			                                'post_type'    => 'rb_block_desktop_new',
			                                'post_author'  => 0
		                                ];
		                                require_once(dirname(__FILE__ )."/../../../wp-includes/pluggable.php");
		                                $saveBlockResultDesktop = wp_insert_post($postarr, true);
	                                }
                                }
	                        }
                            unset($rk,$ritem);

		                    set_transient('rb_cache_timeout', '60', 60);
		                    if (!empty($resultTypes['mobile'])&&empty($resultTypes['desktop'])) {
			                    set_transient('rb_mobile_cache_timeout', '60', 60*60);
                            } elseif (empty($resultTypes['mobile'])&&!empty($resultTypes['desktop'])) {
			                    set_transient('rb_desktop_cache_timeout', '60', 60*60);
                            } elseif (empty($resultTypes['mobile'])&&empty($resultTypes['desktop'])&&!empty($resultTypes['universal'])) {
			                    set_transient('rb_mobile_cache_timeout', '60', 60*60);
			                    set_transient('rb_desktop_cache_timeout', '60', 60*60);
                            }
		                    delete_transient('rb_active_cache');
	                    }
                    }
                }
	            return true;
            } catch (Exception $e) {
                delete_transient('rb_active_cache');
	            return false;
            }
		}
	}

	if (!function_exists('RFWP_tokenChecking')) {
		function RFWP_tokenChecking($wpPrefix) {
			global $wpdb;

			try {
				$GLOBALS['tokenStatusMessage'] = null;
				$token                         = $wpdb->get_results("SELECT optionValue FROM ".$wpPrefix."realbig_settings WHERE optionName = '_wpRealbigPluginToken'");

				if (!empty($token)) {
					$token            = get_object_vars($token[0]);
					$GLOBALS['token'] = $token['optionValue'];
					$token            = $token['optionValue'];
				} else {
					$GLOBALS['token'] = 'no token';
					$token            = 'no token';
				}

				return $token;
			} catch (Exception $e) {
				return 'no token';
			}
		}
	}

	if (!function_exists('RFWP_tokenMDValidate')) {
	    function RFWP_tokenMDValidate($token) {
	        if (strlen($token) != 32) {
		        return false;
            }
            preg_match('~[^0-9a-z]+~', $token, $validateMatch);
	        if (!empty($validateMatch)) {
		        return false;
	        }

            return true;
        }
    }

	if (!function_exists('RFWP_tokenTimeUpdateChecking')) {
		function RFWP_tokenTimeUpdateChecking($token, $wpPrefix) {
			global $wpdb;
			try {
				$timeUpdate = $wpdb->get_results("SELECT timeUpdate FROM ".$wpPrefix."realbig_settings WHERE optionName = 'token_sync_time'");
				if (empty($timeUpdate)) {
					$updateResult = RFWP_wpRealbigSettingsTableUpdateFunction($wpPrefix);
					if ($updateResult == true) {
						$timeUpdate = $wpdb->get_results("SELECT timeUpdate FROM ".$wpPrefix."realbig_settings WHERE optionName = 'token_sync_time'");
					}
				}
				if (!empty($token)&&$token != 'no token'&&((!empty($GLOBALS['tokenStatusMessage'])&&($GLOBALS['tokenStatusMessage'] == 'Синхронизация прошла успешно' || $GLOBALS['tokenStatusMessage'] == 'Не нашло позиций для блоков на указанном сайте, добавьте позиции для сайтов на странице настроек плагина')) || empty($GLOBALS['tokenStatusMessage'])) && !empty($timeUpdate)) {
//				if (!empty($token)&&$token!='no token'&&!empty($timeUpdate)) {
					if (!empty($timeUpdate)) {
						$timeUpdate                 = get_object_vars($timeUpdate[0]);
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
			} catch (Exception $e) {
				echo $e;
			}
		}
	}

	if (!function_exists('RFWP_statusGathererConstructor')) {
		function RFWP_statusGathererConstructor($pointer) {
			global $wpdb;
			try {
				$statusGatherer        = [];
				$realbigStatusGatherer = get_option('realbig_status_gatherer');

				if ( $pointer == false ) {
					$statusGatherer['element_column_values']           = false;
					$statusGatherer['old_tables_removed']              = false;
					$statusGatherer['realbig_plugin_settings_table']   = false;
					$statusGatherer['realbig_settings_table']          = false;
					$statusGatherer['realbig_plugin_settings_columns'] = false;
					if (!empty($realbigStatusGatherer)) {
						$statusGatherer['update_status_gatherer'] = true;
					} else {
						$statusGatherer['update_status_gatherer'] = false;
					}

					return $statusGatherer;
				} else {
					if (!empty($realbigStatusGatherer)) {
//	        $realbigStatusGatherer = $errorVariable;
						$realbigStatusGatherer                             = json_decode($realbigStatusGatherer, true);
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
			} catch (Exception $exception) {
				return $statusGatherer = [];
//        $catchedException = true;
			} catch (Error $error) {
				return $statusGatherer = [];
//		$catchedError = true;
			}
		}
	}

	if (!function_exists('RFWP_autoSync')) {
		function RFWP_getPageTypes() {
			return [
				'is_home' => 'is_home',
				'is_front_page' => 'is_front_page',
				'is_page' => 'is_page',
				'is_single' => 'is_single',
				'is_singular' => 'is_singular',
				'is_archive' => 'is_archive',
				'is_category' => 'is_category',
			];
		}
	}

//	if ( ! empty( $jsAutoSynchronizationStatus ) && $jsAutoSynchronizationStatus < 5 && ! empty( $_POST['funcActivator'] ) && $_POST['funcActivator'] == 'ready' ) {
//	if (!empty($_POST["action"])&&$_POST["action"]=="heartbeat") {

    /** Auto Sync */
	if (!function_exists('RFWP_autoSync')) {
		function RFWP_autoSync() {
			set_transient('realbigPluginSyncProcess', 'true', 30);
			global $wpdb;
			$wpOptionsCheckerSyncTime = $wpdb->get_row($wpdb->prepare('SELECT optionValue FROM '.$GLOBALS['table_prefix'].'realbig_settings WHERE optionName = %s',[ "token_sync_time" ]));
			if (!empty($wpOptionsCheckerSyncTime)) {
				$wpOptionsCheckerSyncTime = get_object_vars($wpOptionsCheckerSyncTime);
			}
			$token      = RFWP_tokenChecking($GLOBALS['table_prefix']);
			$ajaxResult = RFWP_synchronize($token, $wpOptionsCheckerSyncTime, true, $GLOBALS['table_prefix'], 'ajax');
		}
	}
	/** End of auto Sync */

	/** Creating Cron RB auto sync */
	if (!function_exists('RFWP_cronAutoGatheringLaunch')) {
		function RFWP_cronAutoGatheringLaunch() {
			add_filter('cron_schedules', 'rb_addCronAutosync');
			add_action( 'rb_cron_hook', 'rb_cron_exec' );
			if (!($checkIt = wp_next_scheduled( 'rb_cron_hook' ))) {
				wp_schedule_event( time(), 'autoSync', 'rb_cron_hook' );
			}
		}
	}
	if (!function_exists('rb_addCronAutosync')) {
		function rb_addCronAutosync($schedules) {
			$schedules['autoSync'] = array(
				'interval' => 20,
				'display'  => esc_html__( 'autoSync' ),
			);
			return $schedules;
		}
	}
	if (!function_exists('RFWP_cronAutoSyncDelete')) {
		function RFWP_cronAutoSyncDelete() {
			$checkIt = wp_next_scheduled('rb_cron_hook');
			wp_unschedule_event( $checkIt, 'rb_cron_hook' );
		}
	}
	/** End of Creating Cron RB auto sync */
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
				'optionValue' => 'synchro: '.$ex->getMessage()
			]);
		} else {
			$wpdb->update( $wpPrefix.'realbig_settings', [
				'optionName'  => 'deactError',
				'optionValue' => 'synchro: '.$ex->getMessage()
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
				'optionValue' => 'synchro: '.$er->getMessage()
			]);
		} else {
			$wpdb->update( $wpPrefix.'realbig_settings', [
				'optionName'  => 'deactError',
				'optionValue' => 'synchro: '.$er->getMessage()
			], ['optionName'  => 'deactError']);
		}
	} catch (Exception $exIex) {
	} catch (Error $erIex) { }

	deactivate_plugins(plugin_basename( __FILE__ ));
	?><div style="margin-left: 200px; border: 3px solid red"><?php echo $er; ?></div><?php
}