<?php

if (!defined("ABSPATH")) {exit;}

if (!class_exists('RFWP_Variables')) {
    class RFWP_Variables {
       const LOCAL_ROTATOR_GATHER = "localRotatorGatherTimeout";

       const GATHER_CONTENT_LONG = "gatherContentContainerLong";
       const GATHER_CONTENT_SHORT = "gatherContentContainerShort";

       const CUSTOM_SYNC = "rb_customSyncUsed";
    }
}