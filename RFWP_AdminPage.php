<?php

if (!defined("ABSPATH")) {exit;}

if (!class_exists('RFWP_AdminPage')) {
    class RFWP_AdminPage
    {
        public static function settingsMenuCreate() {
            $iconUrl = "";

            try {
                $arrContextOptions=array(
                    "ssl"=>array(
                        "verify_peer"=>false,
                        "verify_peer_name"=>false,
                    ),
                );

                $iconUrl = file_get_contents(plugins_url().'/'.basename(__DIR__).'/assets/realbig_plugin_standart.svg' ,
                    false, stream_context_create($arrContextOptions));
                $iconUrl = 'data:image/svg+xml;base64,' . base64_encode($iconUrl);
            } catch (Exception $ex) {
                RFWP_Logs::saveLogs(RFWP_Logs::ERRORS_LOG, 'Error Load Menu Icon: ' . $ex->getMessage());
            } catch (Error $ex) {
                RFWP_Logs::saveLogs(RFWP_Logs::ERRORS_LOG, 'Error Load Menu Icon: ' . $ex->getMessage());
            }

            add_menu_page( 'Your code sending configuration', 'realBIG', 'administrator', __FILE__, '\RFWP_AdminPage::tokenSync', $iconUrl);
            add_action('admin_init', 'RFWP_AdminPage::registerSettings');
        }

        public static function registerSettings() {
            register_setting('sending_zone', 'token_value_input');
            register_setting('sending_zone', 'token_value_send' );
        }

        public static function tokenSync() {
            global $wpdb;
            global $wpPrefix;
            global $curlResult;
            global $devMode;

            $res = [
                'devMode' => $devMode,
                'curlResult' => $curlResult,
                'cache' => [],
                'workProcess' => '',
                'cache_clear' => '',
                'killRbCheck' => '',
                'deacError' => '',
                'deacTime' => '',
                'enable_logs' => '',
                'rbSettings' => null,
                'turboOptions' => RFWP_generateTurboRssUrls(),
                'tab' => isset($_GET['tab']) ? $_GET['tab'] : null,
            ];

            RFWP_initTestMode();
            RFWP_saveThemeThumbnailSizes();
            self::clickButtons();

            if (!empty($GLOBALS['dev_mode'])) {
                $res['killRbAvailable'] = true;
            } else {
                $res['killRbAvailable'] = false;
            }
            $res['getBlocks'] = $wpdb->get_results('SELECT * FROM '.$wpPrefix.'realbig_plugin_settings', ARRAY_A);

            $cached = $wpdb->get_results('SELECT post_title, post_content, post_type FROM '.$wpPrefix.'posts
                                    WHERE post_type IN ("rb_block_desktop_new", "rb_block_tablet_new", "rb_block_mobile_new")');
            $cacheKeys = ["rb_block_desktop_new" => "desktop", "rb_block_tablet_new" => "tablet", "rb_block_mobile_new" => "mobile"];
            if (!empty($cached)) {
                foreach ($cached as $cache) {
                    $type = isset($cacheKeys[$cache->post_type]) ? $cacheKeys[$cache->post_type] : $cache->post_type;

                    if (!isset($res['cache'][$cache->post_title][$type])) {
                        $res['cache'][$cache->post_title][$type] = $cache->post_content;
                    }
                }
            }

            try {
                $res['rbSettings'] = $wpdb->get_results('SELECT optionName, optionValue, timeUpdate FROM ' . $GLOBALS["wpPrefix"] .
                    'realbig_settings WHERE optionName IN ("deactError","domain","excludedMainPage","excludedPages","pushStatus",' .
                    '"excludedPageTypes","excludedIdAndClasses","kill_rb","pushUniversalStatus","pushUniversalDomain",' .
                    '"statusFor404","blockDuplicate","jsToHead","obligatoryMargin","tagsListForTextLength","usedTaxonomies",' .
                    '"enableLogs")', ARRAY_A);
//			$rbTransients = $wpdb->get_results('SELECT optionName, optionValue, timeUpdate FROM ' . $GLOBALS["wpPrefix"] . 'realbig_settings WHERE optionName IN ("deactError","domain","excludedMainPage","excludedPages","pushStatus","excludedPageTypes","kill_rb")', ARRAY_A);

                if (!empty($res['rbSettings'])) {
                    foreach ($res['rbSettings'] AS $k=>$item) {
                        if ($item['optionName']=='pushUniversalStatus') {
                            $res['pushStatus'] = $item["optionValue"];
                        } elseif ($item['optionName']=='pushUniversalDomain') {
                            $res['pushDomain'] = $item["optionValue"];
                        } elseif ($item['optionName']=='statusFor404') {
                            $res['statusFor404'] = $item["optionValue"] == 'show' ? 1 : 0;
                        } elseif ($item['optionName']=='deactError') {
                            $res['deacError'] = $item["optionValue"];
                            $res['deacTime'] = $item["timeUpdate"];
                        } elseif ($item['optionName']=='excludedPageTypes') {
                            if (!empty($item["optionValue"]) && $item['optionValue'] != 'nun') {
                                $res['excludedPageTypes'] = explode(',',$item["optionValue"]);
                            }
                        } elseif ($item['optionName']=='tagsListForTextLength') {
                            if (!empty($item["optionValue"]) && $item['optionValue'] != 'nun') {
                                $res['tagsListForTextLength'] = explode(';',$item["optionValue"]);
                            }
                        } elseif ($item['optionName']=='usedTaxonomies') {
                            $taxonomies = RFWP_getTaxonomies();
                            if (!empty($taxonomies)) {
                                $res['usedTaxonomies'] = [];
                                $usedTaxonomies = json_decode($item['optionValue'], JSON_UNESCAPED_UNICODE);
                                foreach ($usedTaxonomies as $type => $taxonomyType) {
                                    if (!empty($taxonomyType) && !empty($taxonomies[$type])) {
                                        foreach ($taxonomyType as $taxonomy) {
                                            if (!empty($taxonomies[$type][$taxonomy])) {
                                                $res['usedTaxonomies'][] = $taxonomies[$type][$taxonomy];
                                            }
                                        }
                                    }
                                }
                                $res['usedTaxonomies'] = implode('; ', $res['usedTaxonomies']);
                            }
                        } elseif ($item['optionName']=='kill_rb') {
                            if (!empty($GLOBALS['dev_mode'])) {
                                if (!empty($item["optionValue"])&&$item["optionValue"]==2) {
                                    $res['killRbCheck'] = 'checked';
                                }
                                if (!empty($item["optionValue"])) {
                                    $res['killRbAvailable'] = true;
                                }
                            }
                        } elseif ($item['optionName']=='enableLogs') {
                            if (!empty($item["optionValue"])&&$item["optionValue"]==1) {
                                $res['enable_logs'] = 'checked';
                            }
                        } else {
                            $res[$item['optionName']] = $item['optionValue'];
                        }
                    }
                }

                $res['cache_clear'] = get_option('rb_cacheClearAllow');
                if (!empty($res['cache_clear'])&&$res['cache_clear']=='enabled') {
                    $res['cache_clear'] = 'checked';
                } else {
                    $res['cache_clear'] = '';
                }
            } catch (Exception $e) {
                $res = [];
            }


            load_template(__DIR__ . '/templates/adminPage.php', true, $res);
        }

        public static function clickButtons() {
            if (!empty($_POST['clearLogs'])) {
                RFWP_Logs::clearAllLogs();
            }
            else if (!empty($_POST['clearCache'])) {
                RFWP_Cache::clearCaches();
            }
        }
    }
}