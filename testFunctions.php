<?php

/** Kill rb connection emulation */
// 1 - ok connection; 2 - error connection;
$kill_rb_db = $wpdb->get_results('SELECT id,optionValue FROM '.$wpPrefix.'realbig_settings WHERE optionName = "kill_rb"', ARRAY_A);
if (empty(apply_filters('wp_doing_cron', defined('DOING_CRON') && DOING_CRON))&&!empty(is_admin())) {
	if (!empty($curUserCan)&&!empty($_POST['saveTokenButton'])) {
		if (!empty($_POST['kill_rb'])) {
			$saveVal = 2;
		} else {
			$saveVal = 1;
		}
		if (!empty($kill_rb_db)&&count($kill_rb_db) > 0) {
			$wpdb->update($wpPrefix.'realbig_settings',['optionValue'=>$saveVal],['optionName'=>'kill_rb']);
		} else {
			$wpdb->insert($wpPrefix.'realbig_settings',['optionValue'=>$saveVal,'optionName'=>'kill_rb']);
		}
		$kill_rb_db = $saveVal;
	} else {
		if (!empty($kill_rb_db)&&count($kill_rb_db) > 0) {
			$kill_rb_db = $kill_rb_db[0]['optionValue'];
		} else {
			$kill_rb_db = 1;
		}
	}
} else {
	if (!empty($kill_rb_db)&&count($kill_rb_db) > 0) {
		$kill_rb_db = $kill_rb_db[0]['optionValue'];
	} else {
		$kill_rb_db = 1;
	}
}
$kill_rb = $kill_rb_db;
if (!isset($kill_rb)) {
	$kill_rb = 0;
}
$GLOBALS['kill_rb'] = $kill_rb;
/** End of kill rb connection emulation */
/** Check IP */
if (!empty($_POST['checkIp'])&&is_admin()&&empty(apply_filters('wp_doing_cron', defined('DOING_CRON')&&DOING_CRON))) {
	$thisUrl = 'http://ifconfig.co/ip';
//		    $thisResultSaved = get_option('checkIpResult');
//		    if (empty($thisResultSaved)) {
//			    $thisResult = wp_remote_get($thisUrl);
//			    if (!empty($thisResult)&&!empty($thisResult['body'])) {
//				    error_log(PHP_EOL.current_time('mysql').':'.$thisResult['body'].PHP_EOL, 3, $checkIpLogFile);
//			    }
//		    }
	$curl = curl_init();
	curl_setopt($curl,CURLOPT_URL, $thisUrl);
	curl_setopt($curl,CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curl,CURLOPT_IPRESOLVE,CURL_IPRESOLVE_V4);
	$curlResult = curl_exec($curl);
	if (!empty($curlResult)) {
		global $curlResult;
        RFWP_Logs::saveLogs(RFWP_Logs::IP_LOG, PHP_EOL.$curlResult);
	}
	curl_close($curl);
}

if (!empty($curUserCan)) {
	$testServerName = 'placeholder';
	$testHttpOrigin = 'placeholder';
	$testHttpHost = 'placeholder';

	if (!empty($_SERVER['SERVER_NAME'])) {
		$testServerName = $_SERVER['SERVER_NAME'];
	}
	if (!empty($_SERVER['HTTP_ORIGIN'])) {
		$testHttpOrigin = $_SERVER['HTTP_ORIGIN'];
	}
	if (!empty($_SERVER['HTTP_HOST'])) {
		$testHttpHost = $_SERVER['HTTP_HOST'];
	}
	/*?><script>console.log('SERVER_NAME:<?php echo $testServerName.';';?>');console.log('HTTP_ORIGIN:<?php echo $testHttpOrigin.';';?>');console.log('HTTP_HOST:<?php echo $testHttpHost.';';?>')</script><?php*/
}
/** End of Check IP */
/** Check in header inserting */
if (!function_exists('RFWP_checkHeader')) {
	function RFWP_checkHeader($content) {
//        $content .= '<!-- RFWP inserting detected -->';
		$content .= '<script>console.log("header passed 1");</script>';

		return $content;
	}
}
/** End of Check in header inserting */





