<?php

if (!defined("ABSPATH")) { exit;}

try {
    global $wpdb;
    global $wpPrefix;
	$dev_mode = $GLOBALS['dev_mode'];

	$returnData = [];
    $returnData['errors'] = [];
    $errorsGather = '';
	if (!empty($dev_mode)) {
		$adTestOptionStatus = ["testAd_initialData" => 1,"testAd_iteration1Data" => 1,"testAd_iteration2Data" => 1,"testAd_iteration3Data" => 1,"testAd_iteration4Data" => 1,"testAd_finalIterationData" => 1];
		$adTestData = [];
	}

    $penyok_stoparik = 0;
	$mobileCheck = RFWP_wp_is_mobile();
	if (!empty($mobileCheck)) {
        $checkTran1 = set_transient('rb_mobile_cache_timeout' , '25', 60);
        $blockType = "rb_block_mobile";
    } else {
	    $checkTran2 = set_transient('rb_desktop_cache_timeout', '25', 60);
        $blockType = "rb_block_desktop";
    }
    $data = '';
    if (!empty($_POST['data'])) {
//        $data = '{\"data\":{\"0\":{\"id\":\"13251\",\"code\":\"<div style=\'width: 100px; height: 20px; border: 1px solid black; background - color: #33cc33\'></div>\"}}}';
//        $data = '{\"data\":{"0":{"id":"13251","code":"<div style=\'width: 100px; height: 20px; border: 1px solid black; background - color: #33cc33\'></div>"}}}';
        $data = $_POST['data'];
        if (!empty($dev_mode)) {
	        $adTestData['testAd_initialData'] = $data;
	        $adTestData['testAd_iteration1Data'] = $data;
	        $adTestData['testAd_iteration2Data'] = $data;
	        $adTestData['testAd_iteration3Data'] = $data;
//	        $adTestData['testAd_iteration4Data'] = $data;
	        $adTestData['testAd_finalIterationData'] = json_encode($data);

	        foreach ($adTestData as $k => $item) {
		        if (empty($item)) {
			        $adTestData[$k] = 'nun';
		        }
	        }
        }

	    $data = preg_replace("~\\\'~", "'", $data);
	    $data = preg_replace('~\\\"~', '"', $data);
//	    $data = preg_replace('~\\\&~', '&', $data);
	    $data = preg_replace('~a_m_p~', '&', $data);
//	    $data = preg_replace('~(\\\){2}([a-z])"~', '$1$2', $data);
//	    $data = preg_replace('~\\\"~', '"', $data);
	    $data = json_decode($data);

	    if (!empty($dev_mode)) {
		    $initialAdData_db = $wpdb->get_results( 'SELECT id,optionName FROM '.$wpPrefix.'realbig_settings WHERE optionName IN ("testAd_initialData","testAd_iteration1Data","testAd_iteration2Data","testAd_finalIterationData")',ARRAY_A);
		    $penyok_stoparik = 1;

		    if (count($initialAdData_db) > 0) {
			    foreach ($initialAdData_db AS $k => $item) {
				    $wpdb->update($wpPrefix.'realbig_settings',['optionValue' => $adTestData[$item['optionName']]],['id'=>$item['id']]);
				    $adTestOptionStatus[$item['optionName']] = 2;
			    }
		    }
		    foreach ($adTestOptionStatus AS $k => $item) {
			    if ($item==1) {
				    $wpdb->insert($wpPrefix.'realbig_settings',['optionValue' => $adTestData[$k],'optionName' => $k]);
			    }
		    }
		    $penyok_stoparik = 1;
	    }

//	    if (!empty($initialAdData_db)) {
//		    $wpdb->update($wpPrefix.'realbig_settings',['optionValue' => $data],['id'=>$initialAdData_db]);
//	    } else {
//		    $wpdb->insert($wpPrefix.'realbig_settings',['optionValue' => $data,'optionName' => 'testAd_initialData']);
//	    }
//        $data = json_decode($_POST['data']);
    }
    foreach ($data->data AS $k => $item) {
        try {
            if (!empty($item->code)) {
	            $quotedCode = preg_replace("~\'~", '"', $item->code);
	            if (!empty($quotedCode)) {
		            $item->code = $quotedCode;
                }
            }

            $postCheck = $wpdb->get_var($wpdb->prepare('SELECT id FROM '.$wpPrefix.'posts WHERE post_type = %s AND post_title = %s',[$blockType,$item->id]));
            if (!empty($postCheck)) {
                $postarr = ['ID' => $postCheck, 'post_content' => $item->code];
                $updateBlockResult = wp_update_post($postarr, true);
            } else {
                $postarr = [
                    'post_content' => $item->code,
                    'post_title'   => $item->id,
                    'post_status'  => "publish",
                    'post_type'    => $blockType,
                    'post_author'  => 0
                ];
                require_once(dirname(__FILE__ ) . "/../../../wp-includes/pluggable.php");
                $saveBlockResult = wp_insert_post($postarr, true);
            }
            $penyok_stoparik = 0;
        } catch (Exception $ex1) {
	        $errorsGather .= $ex1->getMessage().'; ';
            array_push($returnData['errors'],$ex1->getMessage());
            continue;
        } catch (Error     $er1) {
	        $errorsGather .= $er1->getMessage().'; ';
	        array_push($returnData['errors'],$er1->getMessage());
	        continue;
        }
    }
    $penyok_stoparik = 0;

	if (!empty($dev_mode)) {
		if (!empty($errorsGather)) {
			$errorsInDb = $wpdb->get_var('SELECT id FROM '.$wpPrefix.'realbig_settings WHERE optionName = "adGatherErrors"');
			if (!empty($errorsInDb)) {
				$wpdb->update($wpPrefix.'realbig_settings',['optionValue' => $errorsGather],['id'=>$errorsInDb]);
			} else {
				$wpdb->insert($wpPrefix.'realbig_settings',['optionValue' => $errorsGather,'optionName' => 'adGatherErrors']);
			}
		}

		$returnData['penyok'] = 'stoparik';
	}

//    $tunnelData['adGather'] = $returnData;
//    return json_encode($returnData);
//    return json_encode($returnData);
} catch (Exception $ex) {
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
				'optionValue' => 'realbigForWP: '.$ex->getMessage()
			]);
		} else {
			$wpdb->update( $wpPrefix.'realbig_settings', [
				'optionName'  => 'deactError',
				'optionValue' => 'realbigForWP: '.$ex->getMessage()
			], ['optionName'  => 'deactError']);
		}
	} catch (Exception $exIex) {
	} catch (Error $erIex) { }

//	include_once ( dirname(__FILE__)."/../../../wp-admin/includes/plugin.php" );
	deactivate_plugins(plugin_basename( __FILE__ ));
	?><div style="margin-left: 200px; border: 3px solid red"><?php echo $ex; ?></div><?php
} catch (Error $er) {
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
				'optionValue' => 'realbigForWP: '.$er->getMessage()
			]);
		} else {
			$wpdb->update( $wpPrefix.'realbig_settings', [
				'optionName'  => 'deactError',
				'optionValue' => 'realbigForWP: '.$er->getMessage()
			], ['optionName'  => 'deactError']);
		}
	} catch (Exception $exIex) {
	} catch (Error $erIex) { }

//	include_once ( dirname(__FILE__)."/../../../wp-admin/includes/plugin.php" );
	deactivate_plugins(plugin_basename( __FILE__ ));
	?><div style="margin-left: 200px; border: 3px solid red"><?php echo $er; ?></div><?php
}