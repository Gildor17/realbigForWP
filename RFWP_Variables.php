<?php

if (!defined("ABSPATH")) {exit;}

if (!class_exists('RFWP_Variables')) {
    class RFWP_Variables {
       const MOBILE_CACHE = "rb_mobile_cache_timeout";
       const TABLET_CACHE = "rb_tablet_cache_timeout";
       const DESKTOP_CACHE = "rb_desktop_cache_timeout";

       const CACHE = "rb_cache_timeout";
       const ACTIVE_CACHE = "rb_active_cache";
       const LONG_CACHE = "rb_longCacheDeploy";

       const SYNC_ATTEMPT = "realbigPluginSyncAttempt";
       const SYNC_PROCESS = "realbigPluginSyncProcess";

       const LOCAL_ROTATOR_GATHER = "localRotatorGatherTimeout";

       const GATHER_CONTENT_LONG = "gatherContentContainerLong";
       const GATHER_CONTENT_SHORT = "gatherContentContainerShort";

       const CUSTOM_SYNC = "rb_customSyncUsed";
    }
}