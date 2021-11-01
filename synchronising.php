<?php

if (!defined("ABSPATH")) { exit;}

try {
	if (!function_exists('RFWP_synchronize')) {
		function RFWP_synchronize($tokenInput, $wpOptionsCheckerSyncTime, $sameTokenResult, $wpPrefix, $requestType) {
			global $wpdb;
			global $rb_logFile;
			global $shortcode_tags;
			$shortcodesToSend = array_keys($shortcode_tags);
			$menuItemList = RFWP_getMenuList();
			$permalinkStatus = RFWP_checkPermalink();
            $pluginVersion = RFWP_plugin_version();
            $turboRssUrls = RFWP_generateTurboRssUrls();
            $unsuccessfullAjaxSyncAttempt = 0;

			if (!empty(apply_filters('wp_doing_cron',defined('DOING_CRON')&&DOING_CRON))&&empty(apply_filters('wp_doing_ajax',defined('DOING_AJAX')&&DOING_AJAX))) {
				RFWP_cronCheckLog('cron in sync passed');
			}

//			RFWP_checkModules();

			if (!empty($_SERVER['HTTP_HOST'])) {
				$urlData = $_SERVER['HTTP_HOST'];
			} elseif (!empty($_SERVER['SERVER_NAME'])) {
				$urlData = $_SERVER['SERVER_NAME'];
			} else {
				$urlData = 'empty';
            }

			$getCategoriesTags = RFWP_getTagsCategories();
			if (!empty($getCategoriesTags)) {
				$getCategoriesTags = json_encode($getCategoriesTags);
			}

			$otherInfo = RFWP_otherInfoGather();

			try {
//    			$url = 'https://realbig.web/api/wp-get-settings';     // orig web post
//    			$url = 'https://beta.realbig.media/api/wp-get-settings';     // beta post
                $url = 'https://realbig.media/api/wp-get-settings';     // orig post

                /** for WP request **/
				$dataForSending = [
				    'body'  => [
                        'token'     => $tokenInput,
                        'sameToken' => $sameTokenResult,
                        'urlData'   => $urlData,
                        'getCategoriesTags' => $getCategoriesTags,
                        'getShortcodes' => json_encode($shortcodesToSend),
                        'getMenuList' => json_encode($menuItemList),
                        'otherInfo' => $otherInfo,
                        'pluginVersion' => $pluginVersion,
                        'turboRssUrls' => $turboRssUrls
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
						$messageFLog = 'Synchronisation request error: '.$error.';';
                        error_log(PHP_EOL.current_time('mysql').': '.$messageFLog.PHP_EOL, 3, $rb_logFile);
					}
				}
				catch (Exception $e) {
					$GLOBALS['tokenStatusMessage'] = $e['message'];
					if ( $requestType == 'ajax' ) {
						$ajaxResult = $e['message'];
					}
					$unsuccessfullAjaxSyncAttempt = 1;
					$messageFLog = 'Synchronisation request system error: '.$e['message'].';';
					error_log(PHP_EOL.current_time('mysql').': '.$messageFLog.PHP_EOL, 3, $rb_logFile);
				}
				catch (Error $e) {
					$GLOBALS['tokenStatusMessage'] = $e['message'];
					if ( $requestType == 'ajax' ) {
						$ajaxResult = $e['message'];
					}
					$unsuccessfullAjaxSyncAttempt = 1;
					$messageFLog = 'Synchronisation request system error: '.$e['message'].';';
					error_log(PHP_EOL.current_time('mysql').': '.$messageFLog.PHP_EOL, 3, $rb_logFile);
				}
				if (!empty($jsonToken)&&!is_wp_error($jsonToken)) {
					$decodedToken                  = json_decode($jsonToken, true);
					$GLOBALS['tokenStatusMessage'] = $decodedToken['message'];
					if ($requestType == 'ajax') {
						$ajaxResult = $decodedToken['message'];
					}
					if (!empty($decodedToken)) {
					    $sanitisedStatus = sanitize_text_field($decodedToken['status']);
					    if ($sanitisedStatus=='success'||$sanitisedStatus=='empty_success') {
						    try {
							    if (!empty($decodedToken['data'])) {
								    if (!empty($decodedToken['excludedPages'])) {
								        $sanitisedExcludedPages = sanitize_text_field($decodedToken['excludedPages']);
                                    } else {
									    $sanitisedExcludedPages = '';
                                    }
								    $excludedPagesCheck = $wpdb->query( $wpdb->prepare( "SELECT optionValue FROM " . $wpPrefix . "realbig_settings WHERE optionName = %s", [ 'excludedPages' ]));
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
								    $excludedMainPageCheck = $wpdb->query($wpdb->prepare("SELECT optionValue FROM ".$wpPrefix."realbig_settings WHERE optionName = %s", ['excludedMainPage']));
								    if (isset($decodedToken['excludedMainPage'])) {
									    if (empty($excludedMainPageCheck)) {
										    $wpdb->insert($wpPrefix.'realbig_settings', ['optionName'  => 'excludedMainPage', 'optionValue' => $sanitisedExcludedMainPages]);
									    } else {
										    $wpdb->update($wpPrefix.'realbig_settings', ['optionName'  => 'excludedMainPage', 'optionValue' => $sanitisedExcludedMainPages],
											    ['optionName'  => 'excludedMainPage']);
									    }
								    }

								    $counter = 0;
								    $wpdb->query('DELETE FROM '.$wpPrefix.'realbig_plugin_settings');
								    $sqlTokenSave = "INSERT INTO ".$wpPrefix."realbig_plugin_settings (text, block_number, setting_type, element, directElement, elementPosition, elementPlace, firstPlace, elementCount, elementStep, minSymbols, maxSymbols, minHeaders, maxHeaders, onCategories, offCategories, onTags, offTags, elementCss) VALUES ";
								    foreach ($decodedToken['data'] AS $k => $item) {
									    $counter ++;
									    $sqlTokenSave .= ($counter != 1 ?", ":"")."('".$item['text']."',".(int) sanitize_text_field($item['block_number']).", ".(int) sanitize_text_field($item['setting_type']).", '".sanitize_text_field($item['element'])."', '".sanitize_text_field( $item['directElement'] ) . "', " . (int) sanitize_text_field($item['elementPosition']) . ", " . (int) sanitize_text_field($item['elementPlace']) . ", " . (int) sanitize_text_field($item['firstPlace']) . ", " . (int) sanitize_text_field($item['elementCount']) . ", " . (int) sanitize_text_field($item['elementStep']) . ", " . (int) sanitize_text_field($item['minSymbols']) . ", " . (int) sanitize_text_field($item['maxSymbols']) . ", " . (int) sanitize_text_field($item['minHeaders']).", " . (int) sanitize_text_field($item['maxHeaders']).", '".sanitize_text_field($item['onCategories'])."', '".sanitize_text_field($item['offCategories'])."', '".sanitize_text_field($item['onTags'])."', '".sanitize_text_field($item['offTags'])."', '".sanitize_text_field($item['elementCss'])."')";
								    }
								    unset($k, $item);
								    $sqlTokenSave .= " ON DUPLICATE KEY UPDATE text = values(text), setting_type = values(setting_type), element = values(element), directElement = values(directElement), elementPosition = values(elementPosition), elementPlace = values(elementPlace), firstPlace = values(firstPlace), elementCount = values(elementCount), elementStep = values(elementStep), minSymbols = values(minSymbols), maxSymbols = values(maxSymbols), minHeaders = values(minHeaders), maxHeaders = values(maxHeaders), onCategories = values(onCategories), offCategories = values(offCategories), onTags = values(onTags), offTags = values(offTags), elementCss = values(elementCss) ";
								    $wpdb->query($sqlTokenSave);
							    } elseif (empty($decodedToken['data'])&&sanitize_text_field($decodedToken['status']) == "empty_success") {
								    $wpdb->query('DELETE FROM '.$wpPrefix.'realbig_plugin_settings');
							    }

							    // if no needly note, then create
							    $wpOptionsCheckerTokenValue = $wpdb->query($wpdb->prepare("SELECT optionValue FROM ".$wpPrefix."realbig_settings WHERE optionName = %s",['_wpRealbigPluginToken']));
							    if (empty($wpOptionsCheckerTokenValue)) {
								    $wpdb->insert( $wpPrefix.'realbig_settings', ['optionName' => '_wpRealbigPluginToken', 'optionValue' => $tokenInput]);
							    } else {
								    $wpdb->update( $wpPrefix.'realbig_settings', ['optionName' => '_wpRealbigPluginToken', 'optionValue' => $tokenInput],
									    ['optionName' => '_wpRealbigPluginToken']);
							    }
							    if (!empty($decodedToken['dataUniversalPush'])) {
							        $sanitisedPushUniversalStatus = sanitize_text_field($decodedToken['dataUniversalPush']['pushStatus']);
							        $sanitisedPushUniversalData = sanitize_text_field($decodedToken['dataUniversalPush']['pushCode']);
							        $sanitisedPushUniversalDomain = sanitize_text_field($decodedToken['dataUniversalPush']['pushDomain']);
								    RFWP_saveToRealbigSettings($sanitisedPushUniversalStatus, 'pushUniversalStatus');
								    RFWP_saveToRealbigSettings($sanitisedPushUniversalData, 'pushUniversalCode');
								    RFWP_saveToRealbigSettings($sanitisedPushUniversalDomain, 'pushUniversalDomain');
							    }
							    if (!empty($decodedToken['domain'])) {
							        $sanitisedDomain = sanitize_text_field($decodedToken['domain']);
								    RFWP_saveToRealbigSettings($sanitisedDomain, 'domain');
							    }
							    if (!empty($decodedToken['rotator'])) {
							        $sanitisedRotator = sanitize_text_field($decodedToken['rotator']);
								    RFWP_saveToRealbigSettings($sanitisedRotator, 'rotator');
							    }
							    /** Excluded page types */
							    if (isset($decodedToken['excludedPageTypes'])) {
							        $excludedPageTypes = sanitize_text_field($decodedToken['excludedPageTypes']);
								    RFWP_saveToRealbigSettings($excludedPageTypes, 'excludedPageTypes');
							    }
							    /** End of excluded page types */
							    /** Excluded id and classes */
							    if (isset($decodedToken['excludedIdAndClasses'])) {
							        $excludedIdAndClasses = sanitize_text_field($decodedToken['excludedIdAndClasses']);
								    RFWP_saveToRealbigSettings($excludedIdAndClasses, 'excludedIdAndClasses');
							    }
							    /** End of excluded id and classes */
							    /** Blocks duplicate denying option */
							    if (isset($decodedToken['blockDuplicate'])) {
								    $blockDuplicate = sanitize_text_field($decodedToken['blockDuplicate']);
								    RFWP_saveToRealbigSettings($blockDuplicate, 'blockDuplicate');
							    }
							    /** End of blocks duplicate denying option */
							    /** Create it for compatibility with some plugins */
							    if (empty($GLOBALS['wp_rewrite'])) {
								    $GLOBALS['wp_rewrite'] = new WP_Rewrite();
							    }
//							    $GLOBALS['wp_rewrite']->flush_rules(false);
							    /** End of creating of that for compatibility with some plugins */
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
								            ];
								            require_once(ABSPATH."/wp-includes/pluggable.php");
								            if (empty($GLOBALS['wp_rewrite'])) {
									            $GLOBALS['wp_rewrite'] = new WP_Rewrite();
                                            }
								            $saveInsertResult = wp_insert_post($postarr, true);
                                        }
								        unset($k, $item);
							        }
                                }
							    /** End of insertings */
							    /** Shortcodes */
							    $oldShortcodes = get_posts(['post_type' => 'rb_shortcodes','numberposts' => 100]);
							    if (!empty($oldShortcodes)) {
								    foreach ($oldShortcodes AS $k => $item) {
									    wp_delete_post($item->ID);
								    }
								    unset($k, $item);
							    }
							    if (!empty($decodedToken['shortcodes'])) {
							        $shortcodes = $decodedToken['shortcodes'];

                                    foreach ($shortcodes AS $k=>$item) {
								        if (!empty($item)) {
									        $postarr = [
										        'post_content' => $item['code'],
										        'post_title'   => $item['id'],
										        'post_excerpt' => $item['blockId'],
										        'post_name'    => 'shortcode',
										        'post_status'  => "publish",
										        'post_type'    => 'rb_shortcodes',
										        'post_author'  => 0,
									        ];
									        require_once(ABSPATH."/wp-includes/pluggable.php");
//                                            remove_all_filters("pre_post_content");
                                            remove_all_filters("content_save_pre");
									        $saveInsertResult = wp_insert_post($postarr, true);
                                        }
                                    }
                                    unset($k, $item);
                                }
							    /** End of shortcodes */
                                /** Turbo rss */
                                if (!empty($decodedToken['turboSettings'])) {
                                    $turboSettings = json_encode($decodedToken['turboSettings'], JSON_UNESCAPED_UNICODE);
                                    update_option('rb_TurboRssOptions', $turboSettings, false);
                                }
                                /** End of Turbo rss */
							    /** Turbo rss ads */
							    if (!empty($decodedToken['turboAdSettings'])) {
							    	$listOfColums = ['blockId', 'adNetwork', 'adNetworkYandex', 'adNetworkAdfox', 'settingType', 'element', 'elementPosition', 'elementPlace'];
								    $counter = 0;
								    $wpdb->query('DELETE FROM '.$wpPrefix.'realbig_turbo_ads');
//								    $sqlTokenSave = "INSERT INTO ".$wpPrefix."realbig_turbo_ads (blockId, adNetwork, adNetworkYandex, adNetworkAdfox, settingType, element, elementPosition, elementPlace) VALUES ";
								    $sqlTokenSave = "INSERT INTO ".$wpPrefix."realbig_turbo_ads (";
								    foreach ($listOfColums AS $k => $item) {
									    $sqlTokenSave .= ($k != 0 ?", ":"").$item;
								    }
								    unset($k, $item);
								    $sqlTokenSave .= ") VALUES ";
								    foreach ($decodedToken['turboAdSettings'] AS $k => $item) {
									    $counter ++;
									    $sqlTokenSave .= ($counter != 1 ?", ":"")."(".(int) sanitize_text_field($item['blockId']).",'".sanitize_text_field($item['adNetwork'])."','".sanitize_text_field($item['adNetworkYandex'])."','".$item['adNetworkAdfox']."','".sanitize_text_field($item['settingType'])."','".sanitize_text_field($item['element'])."',".(int) sanitize_text_field($item['elementPosition']).",".(int) sanitize_text_field($item['elementPlace']).")";
								    }
								    unset($k, $item, $counter);
								    $sqlTokenSave .= " ON DUPLICATE KEY UPDATE blockId = values(blockId), adNetwork = values(adNetwork), adNetworkYandex = values(adNetworkYandex), adNetworkAdfox = values(adNetworkAdfox), settingType = values(settingType), element = values(element), elementPosition = values(elementPosition), elementPlace = values(elementPlace) ";
								    $wpdb->query($sqlTokenSave);
							    }
							    /** End of Turbo rss ads */
							    /** Amp */
                                if (!empty($decodedToken['ampSettings'])) {
	                                $turboSettings = json_encode($decodedToken['ampSettings'], JSON_UNESCAPED_UNICODE);
	                                update_option('rb_ampSettings', $turboSettings, false);
                                }
							    /** End of Amp */
                                /** Amp ads */
                                if (!empty($decodedToken['ampAdSettings'])) {
                                    $listOfColums = ['blockId', 'adField', 'settingType', 'element', 'elementPosition', 'elementPlace'];
                                    $counter = 0;
                                    $wpdb->query('DELETE FROM '.$wpPrefix.'realbig_amp_ads');
                                    $sqlTokenSave = "INSERT INTO ".$wpPrefix."realbig_amp_ads (";
                                    foreach ($listOfColums AS $k => $item) {
                                        if ($k != 0) {
                                            $sqlTokenSave .= ", ";
                                        }
                                        $sqlTokenSave .= $item;
                                    }
                                    unset($k, $item);
                                    $sqlTokenSave .= ") VALUES ";
                                    foreach ($decodedToken['ampAdSettings'] AS $k => $item) {
                                        $counter ++;
                                        if ($counter != 1) {
                                            $sqlTokenSave .= ", ";
                                        }
                                        $sqlTokenSave .= "(".(int) sanitize_text_field($item['blockId']).",'".sanitize_text_field($item['adField'])."','".sanitize_text_field($item['settingType'])."','".sanitize_text_field($item['element'])."',".(int) sanitize_text_field($item['elementPosition']).",".(int) sanitize_text_field($item['elementPlace']).")";
                                    }
                                    unset($k, $item, $counter);
                                    $sqlTokenSave .= " ON DUPLICATE KEY UPDATE blockId = values(blockId), adField = values(adField), settingType = values(settingType), element = values(element), elementPosition = values(elementPosition), elementPlace = values(elementPlace) ";
                                    $wpdb->query($sqlTokenSave);
                                }
                                /** End of Amp ads */
                                /** 404 pages status */
							    if (!empty($decodedToken['statusFor404'])) {
								    $statusFor404 = sanitize_text_field($decodedToken['statusFor404']);
								    RFWP_saveToRealbigSettings($statusFor404, 'statusFor404');
                                }
                                /** End of 404 pages status */
                                /** Test Mode */
							    if (isset($decodedToken['testMode'])) {
								    $testMode = intval($decodedToken['testMode']);
								    $oldTestOption = get_option('rb_testMode');
								    update_option('rb_testMode', $testMode, false);
								    RFWP_initTestMode(true);
								    if (!empty($oldTestOption)&&empty($testMode)) {
									    RFWP_cleanWorkProcessFile();
                                    }
							    }
							    /** End of Test Mode */
                                if (isset($decodedToken['jsToHead'])) {
                                    $jsToHead = sanitize_text_field($decodedToken['jsToHead']);
	                                RFWP_saveToRealbigSettings($jsToHead, 'jsToHead');
                                }
                                if (isset($decodedToken['obligatoryMargin'])) {
                                    $obligatoryMargin = sanitize_text_field($decodedToken['obligatoryMargin']);
	                                RFWP_saveToRealbigSettings($obligatoryMargin, 'obligatoryMargin');
                                }
                                if (isset($decodedToken['tagsListForTextLength'])) {
                                    $tagsListForTextLength = sanitize_text_field($decodedToken['tagsListForTextLength']);
	                                RFWP_saveToRealbigSettings($tagsListForTextLength, 'tagsListForTextLength');
                                }

							    $GLOBALS['token'] = $tokenInput;

							    wp_cache_flush();
							    if (class_exists('RFWP_Caches')&&!empty($_POST)&&!empty($_POST['cache_clear'])&&$_POST['cache_clear']=='on') {
								    RFWP_Caches::cacheClear();
                                }

							    delete_transient('rb_mobile_cache_timeout' );
							    delete_transient('rb_desktop_cache_timeout');
						    } catch ( Exception $e ) {
							    $GLOBALS['tokenStatusMessage'] = $e->getMessage();
							    $unsuccessfullAjaxSyncAttempt  = 1;
							    $messageFLog = 'Some error in synchronize: '.$e->getMessage().';';
							    error_log(PHP_EOL.current_time('mysql').': '.$messageFLog.PHP_EOL, 3, $rb_logFile);
						    }
                        }
					}
				} else {
					$decodedToken                  = null;
					$GLOBALS['tokenStatusMessage'] = 'ошибка соединения';
					$decodedToken['status']        = 'error';
					if ($requestType == 'ajax') {
						$ajaxResult = 'connection error';
					}
					$unsuccessfullAjaxSyncAttempt = 1;
					$messageFLog = 'Connection error;';
					error_log(PHP_EOL.current_time('mysql').': '.$messageFLog.PHP_EOL, 3, $rb_logFile);
				}

				$unmarkSuccessfulUpdate = $wpdb->get_var( 'SELECT optionValue FROM ' . $wpPrefix . 'realbig_settings WHERE optionName = "successUpdateMark"' );
				if (!empty($unmarkSuccessfulUpdate)) {
					$wpdb->update( $wpPrefix . 'realbig_settings', ['optionValue' => 'success'], ['optionName' => 'successUpdateMark']);
				} else {
					$wpdb->insert( $wpPrefix . 'realbig_settings', ['optionName'  => 'successUpdateMark', 'optionValue' => 'success']);
				}

				try {
					delete_transient('realbigPluginSyncAttempt');
					set_transient('realbigPluginSyncAttempt', time()+300, 300);
					if ($decodedToken['status'] == 'success') {
						if (empty($wpOptionsCheckerSyncTime)) {
							$wpdb->insert($wpPrefix.'realbig_settings', ['optionName'  => 'token_sync_time', 'optionValue' => time()]);
						} else {
							$wpdb->update($wpPrefix.'realbig_settings', ['optionName'  => 'token_sync_time', 'optionValue' => time()],
                            ['optionName' => 'token_sync_time']);
						}
					}
				} catch (Exception $e) {
//					echo $e->getMessage();
					$messageFLog = 'Some error in synchronize: '.$e->getMessage().';';
					error_log(PHP_EOL.current_time('mysql').': '.$messageFLog.PHP_EOL, 3, $rb_logFile);
				}
				if ($requestType == 'ajax') {
					if (empty($ajaxResult)) {
						$messageFLog = 'Ajax result error;';
						error_log(PHP_EOL.current_time('mysql').': '.$messageFLog.PHP_EOL, 3, $rb_logFile);
						return 'error';
					} else {
						return $ajaxResult;
					}
				} else {
					wp_cache_flush();
                }
				delete_transient('realbigPluginSyncProcess');
			}
			catch (Exception $e) {
				$messageFLog = 'Some error in synchronize: '.$e->getMessage().';';
				error_log(PHP_EOL.current_time('mysql').': '.$messageFLog.PHP_EOL, 3, $rb_logFile);

				if ($requestType == 'ajax') {
					if (empty($ajaxResult)) {
						return 'error';
					} else {
						return $ajaxResult;
					}
				}
			}
			catch (Error $e) {
				$messageFLog = 'Some error in synchronize: '.$e->getMessage().';';
				error_log(PHP_EOL.current_time('mysql').': '.$messageFLog.PHP_EOL, 3, $rb_logFile);

				if ($requestType == 'ajax') {
					if (empty($ajaxResult)) {
						return 'error';
					} else {
						return $ajaxResult;
					}
				}
			}
			return false;
		}
	}
	if (empty(apply_filters('wp_doing_cron', defined('DOING_CRON') && DOING_CRON))) {
		if (!function_exists('RFWP_savingCodeForCache')) {
			function RFWP_savingCodeForCache($blocksAd=null) {
				global $wpdb;
				global $wpPrefix;
				global $rb_logFile;
				$resultTypes = [];

				try {
//                    $url = 'https://realbig.web/api/wp-get-ads';     // orig web post
//                    $url = 'https://beta.realbig.media/api/wp-get-ads';     // beta post
                    $url = 'https://realbig.media/api/wp-get-ads';     // orig post

					$dataForSending = [
						'body'  => [
							'blocksAd' => $blocksAd
						]
					];

					$jsonResult = wp_safe_remote_post($url, $dataForSending);
//	            $jsonResult = wp_remote_post($url, $dataForSending);

					if (!empty($jsonResult)&&!is_wp_error($jsonResult)) {
						$decodedResult = json_decode($jsonResult['body'], true);
						if (!empty($decodedResult)) {
							$sanitisedStatus = sanitize_text_field($decodedResult['status']);
							if ($sanitisedStatus=='success') {
								$resultData = $decodedResult['data'];

								$resultTypes['mobile'] = false;
								$resultTypes['desktop'] = false;
								$resultTypes['universal'] = false;

								require_once(ABSPATH."/wp-includes/pluggable.php");
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

									$postContent = $ritem['code'];
									$postContent = htmlspecialchars_decode($postContent);
									$postContent = json_encode($postContent, JSON_UNESCAPED_UNICODE);
									$postContent = preg_replace('~\<script~', '<scr_pt_open;', $postContent);
									$postContent = preg_replace('~\/script~', '/scr_pt_close;', $postContent);
									$postContent = preg_replace('~\<~', 'corner_open;', $postContent);
									$postContent = preg_replace('~\>~', 'corner_close;', $postContent);

									if (in_array($ritem['type'], ['mobile','universal'])) {
										if (!empty($postCheckMobile)) {
											$postarr = ['ID' => $postCheckMobile, 'post_content' => $postContent];
											$updateBlockResultMobile = wp_update_post($postarr, true);
										} else {
											$postarr = [
												'post_content' => $postContent,
												'post_title'   => $ritem['blockId'],
												'post_status'  => "publish",
												'post_type'    => 'rb_block_mobile_new',
												'post_author'  => 0
											];
											$saveBlockResultMobile = wp_insert_post($postarr, true);
										}
									}
									if (in_array($ritem['type'], ['desktop','universal'])) {
										if (!empty($postCheckDesktop)) {
											$postarr = ['ID' => $postCheckDesktop, 'post_content' => $postContent];
											$updateBlockResultDesktop = wp_update_post($postarr, true);
										} else {
											$postarr = [
												'post_content' => $postContent,
												'post_title'   => $ritem['blockId'],
												'post_status'  => "publish",
												'post_type'    => 'rb_block_desktop_new',
												'post_author'  => 0
											];
											$saveBlockResultDesktop = wp_insert_post($postarr, true);
										}
									}
								}
								unset($rk,$ritem);

								set_transient('rb_cache_timeout', time()+60, 60);
								if (!empty($resultTypes['mobile'])&&empty($resultTypes['desktop'])) {
									set_transient('rb_mobile_cache_timeout', time()+(60*60), 60*60);
								} elseif (empty($resultTypes['mobile'])&&!empty($resultTypes['desktop'])) {
									set_transient('rb_desktop_cache_timeout', time()+(60*60), 60*60);
								} elseif (empty($resultTypes['mobile'])&&empty($resultTypes['desktop'])&&!empty($resultTypes['universal'])) {
									set_transient('rb_mobile_cache_timeout', time()+(60*60), 60*60);
									set_transient('rb_desktop_cache_timeout', time()+(60*60), 60*60);
								}
								delete_transient('rb_active_cache');
							}
						}
					} elseif(is_wp_error($jsonResult)) {
						$error                                  = $jsonResult->get_error_message();
						$messageFLog                            = 'Saving code for cache error: '.$error.';';
						error_log(PHP_EOL.current_time('mysql').': '.$messageFLog.PHP_EOL,3,$rb_logFile);
					}

					return true;
				} catch (Exception $e) {
					$messageFLog = 'Some error in saving code for cache: '.$e->getMessage().';';
					error_log(PHP_EOL.current_time('mysql').': '.$messageFLog.PHP_EOL, 3, $rb_logFile);
					delete_transient('rb_active_cache');
					return false;
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
				global $rb_logFile;
				try {
					$timeUpdate = $wpdb->get_results("SELECT timeUpdate FROM ".$wpPrefix."realbig_settings WHERE optionName = 'token_sync_time'");
					if (empty($timeUpdate)) {
						$updateResult = RFWP_wpRealbigSettingsTableUpdateFunction($wpPrefix);
						if ($updateResult == true) {
							$timeUpdate = $wpdb->get_results("SELECT timeUpdate FROM ".$wpPrefix."realbig_settings WHERE optionName = 'token_sync_time'");
						}
					}
					if (!empty($token)&&$token != 'no token'&&((!empty($GLOBALS['tokenStatusMessage'])&&($GLOBALS['tokenStatusMessage'] == 'Синхронизация прошла успешно' || $GLOBALS['tokenStatusMessage'] == 'Не нашло позиций для блоков на указанном сайте, добавьте позиции для сайтов на странице настроек плагина')) || empty($GLOBALS['tokenStatusMessage'])) && !empty($timeUpdate)) {
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
//				echo $e;
					$messageFLog = 'Some error in token time update check: '.$e->getMessage().';';
					error_log(PHP_EOL.current_time('mysql').': '.$messageFLog.PHP_EOL, 3, $rb_logFile);
				}
			}
		}
	}
	if (!function_exists('RFWP_tokenChecking')) {
		function RFWP_tokenChecking($wpPrefix) {
			global $rb_logFile;

			try {
			    if (!empty($GLOBALS['token'])&&$GLOBALS['token']!='no token') {
				    $token = $GLOBALS['token'];
                } else {
				    global $wpdb;
				    $GLOBALS['tokenStatusMessage'] = null;
				    $token                         = $wpdb->get_results("SELECT optionValue FROM ".$wpPrefix."realbig_settings WHERE optionName = '_wpRealbigPluginToken'");

				    if (!empty($token)) {
					    $token            = get_object_vars($token[0]);
					    $GLOBALS['token'] = $token['optionValue'];
					    $token            = $token['optionValue'];
				    } else {
					    $GLOBALS['token'] = 'no token';
					    $token            = 'no token';
					    $messageFLog = 'Token check: '.$token.';';
					    error_log(PHP_EOL.current_time('mysql').': '.$messageFLog.PHP_EOL, 3, $rb_logFile);
				    }
                }

				return $token;
			} catch (Exception $e) {
				$messageFLog = 'Some error in token check: '.$e->getMessage().';';
				error_log(PHP_EOL.current_time('mysql').': '.$messageFLog.PHP_EOL, 3, $rb_logFile);
				return 'no token';
			}
		}
	}
	if (!function_exists('RFWP_statusGathererConstructorOld')) {
		function RFWP_statusGathererConstructorOld($pointer) {
			global $wpdb;
			global $rb_logFile;

			try {
				$statusGatherer        = [];
				$realbigStatusGatherer = get_option('realbig_status_gatherer');

				if ( $pointer == false ) {
					$statusGatherer['element_column_values']           = false;
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
						$realbigStatusGatherer                             = json_decode($realbigStatusGatherer, true);
						$statusGatherer['element_column_values']           = $realbigStatusGatherer['element_column_values'];
						$statusGatherer['realbig_plugin_settings_table']   = $realbigStatusGatherer['realbig_plugin_settings_table'];
						$statusGatherer['realbig_settings_table']          = $realbigStatusGatherer['realbig_settings_table'];
						$statusGatherer['realbig_plugin_settings_columns'] = $realbigStatusGatherer['realbig_plugin_settings_columns'];
						$statusGatherer['update_status_gatherer']          = true;

						return $statusGatherer;
					} else {
						$statusGatherer['element_column_values']           = false;
						$statusGatherer['realbig_plugin_settings_table']   = false;
						$statusGatherer['realbig_settings_table']          = false;
						$statusGatherer['realbig_plugin_settings_columns'] = false;
						$statusGatherer['update_status_gatherer']          = false;

						return $statusGatherer;
					}
				}
			} catch (Exception $exception) {
				$messageFLog = 'Some error in token time update check: '.$exception->getMessage().';';
				error_log(PHP_EOL.current_time('mysql').': '.$messageFLog.PHP_EOL, 3, $rb_logFile);
				return $statusGatherer = [];
			} catch (Error $error) {
				$messageFLog = 'Some error in token time update check: '.$error->getMessage().';';
				error_log(PHP_EOL.current_time('mysql').': '.$messageFLog.PHP_EOL, 3, $rb_logFile);
				return $statusGatherer = [];
			}
		}
	}
	if (!function_exists('RFWP_statusGathererConstructor')) {
		function RFWP_statusGathererConstructor($pointer) {
			global $wpdb;
			global $rb_logFile;

			try {
				$statusGatherer        = [];
				$realbigStatusGatherer = get_option('realbig_status_gatherer');

				if ($pointer == false) {
					if (!empty($realbigStatusGatherer)) {
						$statusGatherer['update_status_gatherer'] = true;
					}
				} else {
					if (!empty($realbigStatusGatherer)) {
						$realbigStatusGatherer                             = json_decode($realbigStatusGatherer, true);
						foreach ($realbigStatusGatherer AS $k => $item) {
							$statusGatherer[$k] = $item;
						}
						unset($k, $item);
						$statusGatherer['update_status_gatherer']          = true;
					}
				}
				return $statusGatherer;
			} catch (Exception $exception) {
				$messageFLog = 'Some error in token time update check: '.$exception->getMessage().';';
				error_log(PHP_EOL.current_time('mysql').': '.$messageFLog.PHP_EOL, 3, $rb_logFile);
				return $statusGatherer = [];
			} catch (Error $error) {
				$messageFLog = 'Some error in token time update check: '.$error->getMessage().';';
				error_log(PHP_EOL.current_time('mysql').': '.$messageFLog.PHP_EOL, 3, $rb_logFile);
				return $statusGatherer = [];
			}
		}
	}
	if (!function_exists('RFWP_getPageTypes')) {
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
    /** Auto Sync */
	if (!function_exists('RFWP_autoSync')) {
		function RFWP_autoSync() {
			set_transient('realbigPluginSyncProcess', time()+30, 30);
			global $wpdb;
			$wpOptionsCheckerSyncTime = $wpdb->get_row($wpdb->prepare('SELECT optionValue FROM '.$GLOBALS['table_prefix'].'realbig_settings WHERE optionName = %s',[ "token_sync_time" ]));
			if (!empty($wpOptionsCheckerSyncTime)) {
				$wpOptionsCheckerSyncTime = get_object_vars($wpOptionsCheckerSyncTime);
			}
			$token      = RFWP_tokenChecking($GLOBALS['table_prefix']);
			RFWP_cronCheckLog('cron going to sync 2');
			$ajaxResult = RFWP_synchronize($token, $wpOptionsCheckerSyncTime, true, $GLOBALS['table_prefix'], 'ajax');
		}
	}
	/** End of auto Sync */
	/** Creating Cron RB auto sync */
	if (!function_exists('RFWP_cronAutoGatheringLaunch')) {
		function RFWP_cronAutoGatheringLaunch() {
			add_filter('cron_schedules', 'rb_addCronAutosync');
//			add_action('rb_cron_hook', 'rb_cron_exec');
			if (!($checkIt = wp_next_scheduled('rb_cron_hook'))) {
				wp_schedule_event(time(), 'autoSync', 'rb_cron_hook');
//				$spawnResult = spawn_cron();
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
	if (!function_exists('RFWP_getMenuList')) {
		function RFWP_getMenuList() {
			$menuMap = [];
			try {
				$menuTerms = get_terms('nav_menu', array('hide_empty' => true));
				if (!empty($menuTerms)) {
					foreach ($menuTerms AS $k => $item) {
						$menuMap[$item->term_id] = $item->name;
					}
					unset($k,$item);
				}
			} catch (Exception $ex) {} catch (Error $er) {}
			return $menuMap;
		}
	}
	if (!function_exists('RFWP_otherInfoGather')) {
		function RFWP_otherInfoGather() {
			$result = [];
			$result['permalinkStatus'] = RFWP_checkPermalink();

			return $result;
		}
	}
	if (!function_exists('RFWP_checkPermalink')) {
		function RFWP_checkPermalink() {
			$result = false;
			if (get_option('permalink_structure')) {
				$result = true;
			}
			return $result;
		}
	}
	if (!function_exists('RFWP_checkModules')) {
		function RFWP_checkModules() {
//			error_log(PHP_EOL.current_time('mysql').': '.$messageFLog.PHP_EOL, 3, $rb_logFile);
		}
	}
    if (!function_exists('RFWP_plugin_version')) {
        function RFWP_plugin_version() {
            $plugin_version = null;
            $plugin_data = get_plugin_data(dirname(__FILE__).'/realbigForWP.php');
            if (!empty($plugin_data)&&!empty($plugin_data['Version'])) {
                $plugin_version = $plugin_data['Version'];
            }

            return $plugin_version;
        }
    }
    if (!function_exists('RFWP_fillRotatorFileInfo')) {
        function RFWP_fillRotatorFileInfo($rotatorFileInfo) {
            $partsArray = [];
            if (!empty(WP_CONTENT_DIR)&&!empty(WP_CONTENT_URL)) {
                $parts = [
                    'path' => WP_CONTENT_DIR,
                    'pathAdditional' => '/',
                    'url' => WP_CONTENT_URL,
                    'urlAdditional' => '/',
                ];
                array_push($partsArray, $parts);
            }
            if (!empty(WP_PLUGIN_DIR)&&!empty(WP_PLUGIN_URL)) {
                $parts = [
                    'path' => WP_PLUGIN_DIR,
                    'pathAdditional' => '/',
                    'url' => WP_PLUGIN_URL,
                    'urlAdditional' => '/',
                ];
                array_push($partsArray, $parts);
            }
            $parts = [
                'path' => dirname(__FILE__),
                'pathAdditional' => '/',
                'url' => plugin_dir_url(__FILE__),
                'urlAdditional' => '',
            ];
            array_push($partsArray, $parts);
            $rotatorFileInfo['pathUrlToFolderParts'] = $partsArray;

            return $rotatorFileInfo;
        }
    }
    if (!function_exists('RFWP_checkRotatorFile')) {
        function RFWP_checkRotatorFile($rotatorFileInfo) {
            foreach ($rotatorFileInfo['pathUrlToFolderParts'] as $k => $item) {
                $pathToFile = $item['path'].$item['pathAdditional'].$GLOBALS['rb_variables']['rotator'].'.js';
	            $urlToFile = $item['url'].$item['urlAdditional'].$GLOBALS['rb_variables']['rotator'].'.js';
	            $checkCurrentRotator = RFWP_checkRotatorFileSingle($pathToFile, $urlToFile);
	            if (!empty($checkCurrentRotator)) {
		            $clearedUrl = RFWP_clearUrl($item['url']);
		            $urlToFile = $clearedUrl.$item['urlAdditional'].$GLOBALS['rb_variables']['rotator'].'.js';
		            $rotatorFileInfo['urlToFile'] = $urlToFile;
		            break;
                }
            }
            unset($k,$item);

            return $rotatorFileInfo;
        }
    }
    if (!function_exists('RFWP_checkRotatorFileSingle')) {
        function RFWP_checkRotatorFileSingle($pathToFile, $urlToFile) {
            if (file_exists($pathToFile)) {
                $checkLocalRotatorAccessibility = RFWP_checkLocalRotatorAccessibility($urlToFile);
                if (!empty($checkLocalRotatorAccessibility)) {
                    return true;
                }
            }
            unset($k,$item);

            return false;
        }
    }
	if (!function_exists('RFWP_createAndFillLocalRotator')) {
		function RFWP_createAndFillLocalRotator($rotatorFileInfo) {
			global $rb_logFile;
			try {
                $rotatorFileInfo['checkFileExists'] = false;
                foreach ($rotatorFileInfo['pathUrlToFolderParts'] as $k => $item) {
                    $pathToFile = $item['path'].$item['pathAdditional'].$GLOBALS['rb_variables']['rotator'].'.js';
                    $urlToFile = $item['url'].$item['urlAdditional'].$GLOBALS['rb_variables']['rotator'].'.js';
                    try {
                        $rotatorFileInfo['fileRotatorContent'] = file_get_contents($rotatorFileInfo['urlToRotator']);
                    } catch (Exception $ex) {
                        $fileGetContentError = true;
                    } catch (Error $er) {
                        $fileGetContentError = true;
                    }

                    if (empty($rotatorFileInfo['fileRotatorContent'])) {
                        if (!empty($fileGetContentError)&&function_exists('curl_init')) {
                            $ch = curl_init();
                            curl_setopt($ch, CURLOPT_URL, $rotatorFileInfo['urlToRotator']);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            $rotatorFileInfo['fileRotatorContent'] = curl_exec($ch);
                            curl_close($ch);
                        }
                    }

                    if (!empty($rotatorFileInfo['fileRotatorContent'])) {
                        $rotatorFile = fopen($pathToFile, 'w');
                        if ($rotatorFile!==false) {
                            file_put_contents($pathToFile, $rotatorFileInfo['fileRotatorContent']);
                            fclose($rotatorFile);
                        }
                        unset($rotatorFile);
                    }

	                $checkResult = RFWP_checkRotatorFileSingle($pathToFile,$urlToFile);
                    if (!empty($checkResult)) {
	                    $rotatorFileInfo['pathToFile'] = $pathToFile;
	                    $urlToFile = RFWP_clearUrl($urlToFile);
	                    $rotatorFileInfo['urlToFile'] = $urlToFile;
	                    global $wpdb;
	                    $wpPrefix = RFWP_getTablePrefix();
	                    $getLocalRotatorUrl = $wpdb->get_var( 'SELECT optionValue FROM ' . $wpPrefix . 'realbig_settings WHERE optionName = "localRotatorUrl"' );
	                    if (!empty($getLocalRotatorUrl)) {
		                    $wpdb->update( $wpPrefix.'realbig_settings', ['optionValue' => $urlToFile], ['optionName' => 'localRotatorUrl']);
	                    } else {
		                    $wpdb->insert( $wpPrefix.'realbig_settings', ['optionName'  => 'localRotatorUrl', 'optionValue' => $urlToFile]);
	                    }
	                    $GLOBALS['rb_variables']['localRotatorUrl'] = $urlToFile;
	                    set_transient('localRotatorGatherTimeout', true, 15*60);
	                    $GLOBALS['rb_variables']['localRotatorGatherTimeout'] = true;
	                    break;
                    }
                }
                unset($k,$item);
			} catch (Exception $ex) {
				$messageFLog = 'Some error in RFWP_createAndFillLocalRotator: '.$ex->getMessage().';';
				error_log(PHP_EOL.current_time('mysql').': '.$messageFLog.PHP_EOL, 3, $rb_logFile);
			} catch (Error $er) {
				$messageFLog = 'Some error in RFWP_createAndFillLocalRotator: '.$er->getMessage().';';
				error_log(PHP_EOL.current_time('mysql').': '.$messageFLog.PHP_EOL, 3, $rb_logFile);
			}
		    return $rotatorFileInfo;
		}
	}
	if (!function_exists('RFWP_generateTurboRssUrls')) {
		function RFWP_generateTurboRssUrls() {
			$result = [];
		    if (function_exists('RFWP_rssOptionsGet')) {
			    $turboOptions = RFWP_rssOptionsGet();
			    $turboUrl = $turboOptions['name'];
			    if (get_option('permalink_structure')) {
				    $url = home_url().'/feed/'.$turboUrl.'/';
				    $trashUrl = $url.'?rb_rss_trash=1';
			    } else {
				    $url = home_url().'/?feed='.$turboUrl;
				    $trashUrl = $url.'&rb_rss_trash=1';
			    }
			    $result['mainRss'] = $url;
			    $result['trashRss'] = $trashUrl;
            }

			return $result;
        }
    }
	if (!function_exists('RFWP_getDomain')) {
		function RFWP_getDomain() {
			$urlData = '';
			if (!empty($_SERVER['HTTP_HOST'])) {
				$urlData = $_SERVER['HTTP_HOST'];
			} elseif (!empty($_SERVER['SERVER_NAME'])) {
				$urlData = $_SERVER['SERVER_NAME'];
			}

			return $urlData;
        }
    }
	if (!function_exists('RFWP_checkLocalRotatorAccessibility')) {
	    function RFWP_checkLocalRotatorAccessibility($urlToCheck) {
		    $checkResult = false;
		    try {
			    $checkResult = wp_get_http_headers($urlToCheck);
		    }
		    catch (Exception $ex) {
			    $errorText = __FUNCTION__." error: ".$ex->getMessage();
			    RFWP_Logs::saveLogs('errorsLog', $errorText);
		    }
		    catch (Error $ex) {
			    $errorText = __FUNCTION__." error: ".$ex->getMessage();
			    RFWP_Logs::saveLogs('errorsLog', $errorText);
		    }

	        return $checkResult;
        }
    }
	if (!function_exists('RFWP_pluginActivation')) {
	    function RFWP_pluginActivation() {
	        //here
        }
    }
	if (!function_exists('RFWP_createLocalRotator')) {
	    function RFWP_createLocalRotator() {
		    try {
                $rotatorFileInfo = [];
                $rotatorFileInfo['pathToFile'] = '';
                $rotatorFileInfo['urlToFile'] = '';
                $rotatorFileInfo = RFWP_fillRotatorFileInfo($rotatorFileInfo);
                $rotatorFileInfo['urlToRotator'] = 'https://'.$GLOBALS['rb_variables']['adDomain'].'/'.$GLOBALS['rb_variables']['rotator'].'.min.js';
                $rotatorFileInfo = RFWP_createAndFillLocalRotator($rotatorFileInfo);
		    }
		    catch (Exception $ex) {
			    $errorText = __FUNCTION__." error: ".$ex->getMessage();
			    RFWP_Logs::saveLogs('errorsLog', $errorText);
		    }
		    catch (Error $ex) {
			    $errorText = __FUNCTION__." error: ".$ex->getMessage();
			    RFWP_Logs::saveLogs('errorsLog', $errorText);
		    }

            return false;
        }
    }
	if (!function_exists('RFWP_clearUrl')) {
	    function RFWP_clearUrl($url) {
		    $clearedUrl = $url;
		    try {
			    $clearedUrl = preg_replace('~^http[s]?\:~ius', '', $url);
			    if (empty($clearedUrl)) {
				    $clearedUrl = $url;
			    }
		    }
		    catch (Exception $ex) {
			    $errorText = __FUNCTION__." error: ".$ex->getMessage();
			    RFWP_Logs::saveLogs('errorsLog', $errorText);
		    }
		    catch (Error $ex) {
			    $errorText = __FUNCTION__." error: ".$ex->getMessage();
			    RFWP_Logs::saveLogs('errorsLog', $errorText);
		    }

		    return $clearedUrl;
        }
    }
	if (!function_exists('RFWP_saveToRealbigSettings')) {
		function RFWP_saveToRealbigSettings($value, $optionName) {
			try {
				global $wpdb;
				$wpPrefix = RFWP_getWpPrefix();

				$getOption = $wpdb->query($wpdb->prepare("SELECT id FROM ".$wpPrefix."realbig_settings WHERE optionName = %s",[$optionName]));
				if (empty($getOption)) {
					$wpdb->insert($wpPrefix.'realbig_settings', ['optionName' => $optionName, 'optionValue' => $value]);
				} else {
					$wpdb->update($wpPrefix.'realbig_settings', ['optionValue' => $value], ['optionName' => $optionName]);
				}
			}
			catch (Exception $ex) {
				$errorText = __FUNCTION__." error: ".$ex->getMessage();
				RFWP_Logs::saveLogs('errorsLog', $errorText);
			}
			catch (Error $ex) {
				$errorText = __FUNCTION__." error: ".$ex->getMessage();
				RFWP_Logs::saveLogs('errorsLog', $errorText);
			}

			return false;
        }
    }

	if (!function_exists('RFWP_getWpPrefix')) {
		function RFWP_getWpPrefix() {
			$wpPrefix = '';
			try {
				if (!empty($GLOBALS['wpPrefix'])) {
					$wpPrefix = $GLOBALS['wpPrefix'];
				} else {
					if (!empty($GLOBALS['table_prefix'])) {
						$wpPrefix = $GLOBALS['table_prefix'];
					} else {
						global $wpdb;
						$wpPrefix = $wpdb->base_prefix;
					}
					if (!empty($wpPrefix)) {
						$GLOBALS['wpPrefix'] = $wpPrefix;
                    }
                }

				if (empty($wpPrefix)) {
					$errorText = "wpdb prefix missing";
					RFWP_Logs::saveLogs('errorsLog', $errorText);
				}
			}
			catch (Exception $ex) {
				$errorText = __FUNCTION__." error: ".$ex->getMessage();
				RFWP_Logs::saveLogs('errorsLog', $errorText);
			}
			catch (Error $ex) {
				$errorText = __FUNCTION__." error: ".$ex->getMessage();
				RFWP_Logs::saveLogs('errorsLog', $errorText);
			}

			return $wpPrefix;
		}
    }
}
catch (Exception $ex)
{
	try {
		global $wpdb;
		global $rb_logFile;

		$messageFLog = 'Deactivation error: '.$ex->getMessage().';';
		error_log(PHP_EOL.current_time('mysql').': '.$messageFLog.PHP_EOL, 3, $rb_logFile);

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

	deactivate_plugins(plugin_basename(__FILE__));
	?><div style="margin-left: 200px; border: 3px solid red"><?php echo $ex; ?></div><?php
}
catch (Error $er)
{
	try {
		global $wpdb;
		global $rb_logFile;

		$messageFLog = 'Deactivation error: '.$er->getMessage().';';
		error_log(PHP_EOL.current_time('mysql').': '.$messageFLog.PHP_EOL, 3, $rb_logFile);

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