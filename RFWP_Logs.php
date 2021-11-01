<?php

if (!defined("ABSPATH")) {exit;}

if (!class_exists('RFWP_Logs')) {
    class RFWP_Logs {
        private static $missingLogFile = false;

        private static $errorsLog;
        private static $workProcessLog;
        private static $testCheckLog;
        private static $rssCheckLog;
        private static $modulesLog;
        private static $ampTestLog;

//	public function __construct() {
//		$this->generateFilePaths();
//	}

        public static function generateFilePaths() {
            try {
                $logsList = self::getLogsList();

                if (!empty($logsList)) {
                    foreach ($logsList as $k => $item) {
                        try {
                            if (file_exists(dirname(__FILE__).'/'.$item)) {
                                self::$$k = plugin_dir_path(__FILE__).$item;
                            } else {
                                self::$missingLogFile = true;
                            }
                        } catch (Exception $ex) {} catch (Error $er) {}
                    }
                    unset($k,$item);
                }

                $GLOBALS['rb_logFile'] = plugin_dir_path(__FILE__).'wpPluginErrors.log';
                $GLOBALS['rb_processlogFile'] = plugin_dir_path(__FILE__).'workProcess.log';
                $GLOBALS['rb_testCheckLog'] = plugin_dir_path(__FILE__).'testCheckLog.log';
                $GLOBALS['rb_rssCheckLog'] = plugin_dir_path(__FILE__).'rssCheckLog.log';
                $GLOBALS['rb_modulesLog'] = plugin_dir_path(__FILE__).'modulesLog.log';
                $GLOBALS['rb_ampTestLog'] = plugin_dir_path(__FILE__).'ampTestLog.log';

                return true;
            } catch (Exception $ex) {} catch (Error $er) {}
            return false;
        }

        private static function getLogsList() {
            // var name - file name
            $list = [
                'errorsLog' => 'wpPluginErrors.log',
                'workProcessLog' => 'workProcess.log',
                'testCheckLog' => 'testCheckLog.log',
                'rssCheckLog' => 'rssCheckLog.log',
                'modulesLog' => 'modulesLog.log',
                'ampTestLog' => 'ampTestLog.log',
            ];

            return $list;
        }

        public static function saveLogs($logAttributeName, $text, $useDateBefore = true) {
            try {
                if (!empty(self::$$logAttributeName)) {
                    $message = PHP_EOL;
                    if (!empty($useDateBefore)) {
                        $message .= current_time('mysql');
                    }

                    error_log($message.': '.$text.PHP_EOL, 3, self::$$logAttributeName);
                }
            } catch (Exception $ex) {} catch (Error $er) {}
        }

        public static function test1() {
            $testVal = self::$errorsLog;
//            throw new Exception('jk');
            $penyok_stoparik = 0;
        }
    }
}