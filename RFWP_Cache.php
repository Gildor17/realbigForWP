<?php

if (!defined("ABSPATH")) {exit;}

if (!class_exists('RFWP_Cache')) {
    class RFWP_Cache {
        const MOBILE_CACHE = "rb_mobile_cache_timeout";
        const TABLET_CACHE = "rb_tablet_cache_timeout";
        const DESKTOP_CACHE = "rb_desktop_cache_timeout";

        const CACHE = "rb_cache_timeout";
        const ACTIVE_CACHE = "rb_active_cache";
        const LONG_CACHE = "rb_longCacheDeploy";

        const PROCESS_CACHE = "rb_syncProcess_cache";

        public static function getMobileCache() {
            return get_transient(self::MOBILE_CACHE);
        }
        public static function setMobileCache() {
            set_transient(self::MOBILE_CACHE, time()+(60*60), 60*60);
        }

        public static function getTabletCache() {
            return get_transient(self::TABLET_CACHE);
        }
        public static function setTabletCache() {
            set_transient(self::TABLET_CACHE, time()+(60*60), 60*60);
        }

        public static function getDesktopCache() {
            return get_transient(self::DESKTOP_CACHE);
        }
        public static function setDesktopCache() {
            set_transient(self::DESKTOP_CACHE, time()+(60*60), 60*60);
        }

        public static function getCache() {
            return get_transient(self::CACHE);
        }
        public static function setCache() {
            set_transient(self::CACHE, time()+60, 60);
        }

        public static function getActiveCache() {
            return get_transient(self::ACTIVE_CACHE);
        }
        public static function setActiveCache() {
            set_transient(self::ACTIVE_CACHE, time()+5, 5);
        }
        public static function deleteActiveCache() {
            delete_transient(self::ACTIVE_CACHE);
        }

        public static function getProcessCache() {
            return get_transient(self::PROCESS_CACHE);
        }
        public static function setProcessCache() {
            set_transient(self::PROCESS_CACHE, time()+30, 30);
        }
        public static function deleteProcessCache() {
            delete_transient(self::PROCESS_CACHE);
        }

        public static function getLongCache() {
            if (!empty($GLOBALS['dev_mode'])) {
                $longCache = false;
                $GLOBALS['rb_longCache'] = $longCache;
            } else {
                if (!isset($GLOBALS['rb_longCache'])) {
                    $longCache = get_transient(self::LONG_CACHE);
                    $GLOBALS['rb_longCache'] = $longCache;
                } else {
                    $longCache = $GLOBALS['rb_longCache'];
                }
            }
            return $longCache;
        }

        public static function setLongCache() {
            set_transient(self::LONG_CACHE, time()+300, 300);
        }

        public static function deleteCaches() {
            delete_transient(self::CACHE);
            delete_transient(self::LONG_CACHE);
           self::deleteDeviceCaches();
        }

        public static function deleteDeviceCaches() {
            delete_transient(self::MOBILE_CACHE);
            delete_transient(self::TABLET_CACHE);
            delete_transient(self::DESKTOP_CACHE);
        }

        public static function clearCaches() {
            self::deleteCaches();
            global $wpdb;
            global $wpPrefix;

            $wpdb->query('DELETE FROM '.$wpPrefix.'posts
                                WHERE post_type IN ("rb_block_desktop_new", "rb_block_tablet_new", "rb_block_mobile_new")');
        }
    }
}