<?php

if (!defined("ABSPATH")) { exit;}

/**
 * Created by PhpStorm.
 * User: furio
 * Date: 2018-07-31
 * Time: 18:33
 */

try {
	if (!function_exists('RFWP_dbTablesCreateFunction')) {
		function RFWP_dbTablesCreateFunction($tableForCurrentPluginChecker, $tableForToken, $tableForTurboRssAds, $wpPrefix, $statusGatherer) {
			global $wpdb;
			global $rb_logFile;
			try {
				require_once (ABSPATH."/wp-admin/includes/upgrade.php");
				if (empty($tableForCurrentPluginChecker)) {
					$sql = "
CREATE TABLE `".$wpPrefix."realbig_plugin_settings` 
(
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`block_number` INT(11) NOT NULL,
	`text` TEXT NOT NULL,
	`setting_type` INT(11) NOT NULL,
	`element` ENUM('p','li','ul','ol','blockquote','img','video','iframe','h1','h2','h3','h4','h5','h6','h2-4','article') NOT NULL,
	`directElement` TEXT NOT NULL,
	`elementPosition` INT(11) NOT NULL,
	`elementPlace` INT(11) NOT NULL,
	`firstPlace` INT(11) NOT NULL,
	`elementCount` INT(11) NOT NULL,
	`elementStep` INT(11) NOT NULL,
	`minSymbols` INT(11) NULL DEFAULT NULL,
	`maxSymbols` INT(11) NULL DEFAULT NULL,
	`minHeaders` INT(11) NULL DEFAULT NULL,
	`maxHeaders` INT(11) NULL DEFAULT NULL,
	`elementCss` ENUM('default','center','left','right') NOT NULL DEFAULT 'default',
	`time_update` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB   
";
					$tableCreateResult = dbDelta($sql, true);
					if (!is_admin()&&empty(apply_filters('wp_doing_cron',defined('DOING_CRON')&&DOING_CRON))&&empty(apply_filters('wp_doing_ajax',defined('DOING_AJAX')&&DOING_AJAX))) {
						RFWP_WorkProgressLog(false,'create realbig_plugin_settings tables');
					}
					add_option('realbigForWP_version', $GLOBALS['realbigForWP_version']);
//				if (!empty($wpdb->get_var( 'SHOW TABLES LIKE "' . $wpPrefix . 'realbig_plugin_settings"' ))) {
//					$statusGatherer['realbig_plugin_settings_table'] = true;
//              }
				} else {
					$statusGatherer['realbig_plugin_settings_table'] = true;
					$messageFLog = 'realbig_plugin_settings exists;';
					if (!empty($messageFLog)) {
						error_log(PHP_EOL.current_time('mysql').': '.$messageFLog.PHP_EOL,3,$rb_logFile);
					}
				}

				if (empty($tableForToken)) {
					$sql = "
CREATE TABLE `".$wpPrefix."realbig_settings` (
`id` INT(11) NOT NULL AUTO_INCREMENT,
`optionName` VARCHAR(50) NOT NULL,
`optionValue` TEXT NOT NULL,
`timeUpdate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
PRIMARY KEY (`id`),
UNIQUE INDEX `optionName` (`optionName`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
";
					dbDelta($sql, true);
					if (!is_admin()&&empty(apply_filters('wp_doing_cron',defined('DOING_CRON')&&DOING_CRON))&&empty(apply_filters('wp_doing_ajax',defined('DOING_AJAX')&&DOING_AJAX))) {
						RFWP_WorkProgressLog(false,'create realbig_settings tables');
					}
				} else {
					$statusGatherer['realbig_settings_table'] = true;
					$messageFLog = 'realbig_settings exists;';
                    error_log(PHP_EOL.current_time('mysql').': '.$messageFLog.PHP_EOL,3,$rb_logFile);
				}

				if (empty($tableForTurboRssAds)) {
					$sql = "
CREATE TABLE `".$wpPrefix."realbig_turbo_ads` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`blockId` INT(11) NOT NULL,
	`adNetwork` ENUM('rsya','adfox') NOT NULL DEFAULT 'rsya' COLLATE 'utf8_bin',
	`adNetworkYandex` TEXT NULL COLLATE 'utf8_bin',
	`adNetworkAdfox` TEXT NULL COLLATE 'utf8_bin',
	`settingType` ENUM('single','begin','middle','end') NOT NULL DEFAULT 'single' COLLATE 'utf8_bin',
	`element` ENUM('p','li','ul','ol','blockquote','img','video','iframe','h1','h2','h3','h4','h5','h6','h2-4','article') NOT NULL DEFAULT 'p' COLLATE 'utf8_bin',
	`elementPosition` TINYINT(4) NOT NULL DEFAULT '0',
	`elementPlace` INT(11) NOT NULL DEFAULT '1',
	`timeUpdate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`)
)
COMMENT='Ads for turbo RSS'
COLLATE='utf8_bin'
ENGINE=InnoDB
";
					dbDelta($sql, true);
					if (!is_admin()&&empty(apply_filters('wp_doing_cron',defined('DOING_CRON')&&DOING_CRON))&&empty(apply_filters('wp_doing_ajax',defined('DOING_AJAX')&&DOING_AJAX))) {
						RFWP_WorkProgressLog(false,'create realbig_turbo_ads tables');
					}
				} else {
					$statusGatherer['realbig_turbo_ads_table'] = true;
					$messageFLog = 'realbig_turbo_ads exists;';
                    error_log(PHP_EOL.current_time('mysql').': '.$messageFLog.PHP_EOL,3,$rb_logFile);
				}

				return $statusGatherer;
			} catch (Exception $e) {
//				echo $e;
				$messageFLog = 'some error in table create: '.$e->getMessage().';';
				error_log(PHP_EOL.current_time('mysql').': '.$messageFLog.PHP_EOL,3,$rb_logFile);

				$statusGatherer['realbig_plugin_settings_table'] = false;
				$statusGatherer['realbig_settings_table']        = false;
				return $statusGatherer;
			}
		}
	}
	if (!function_exists('RFWP_updateElementEnumValuesFunction')) {
		function RFWP_updateElementEnumValuesFunction($wpPrefix, $statusGatherer) {
			global $rb_logFile;
			$requiredElementColumnValues = "enum('p','li','ul','ol','blockquote','img','video','iframe','h1','h2','h3','h4','h5','h6','h2-4','article')";
			try {
				function RFWP_checkElementColumnValues($wpPrefix, $requiredElementColumnValues) {
					global $wpdb;
					$localReturnValue = false;

					$enumTypeQuery = $wpdb->get_results('SHOW FIELDS FROM '.$wpPrefix.'realbig_plugin_settings WHERE Field = "element"');
					if (!empty($enumTypeQuery)) {
						$enumTypeQuery = get_object_vars($enumTypeQuery[0]);
						if ($enumTypeQuery['Type'] != $requiredElementColumnValues) {
							$alterResult = $wpdb->query("ALTER TABLE ".$wpPrefix."realbig_plugin_settings MODIFY `element` ENUM('p','li','ul','ol','blockquote','img','video','iframe','h1','h2','h3','h4','h5','h6','h2-4','article') NULL DEFAULT NULL");
							if (!empty($alterResult)&&is_int($alterResult)&&$alterResult == 1) {
								$localReturnValue = RFWP_checkElementColumnValues($wpPrefix, $requiredElementColumnValues);
							}
						} else {
							$localReturnValue = true;
						}
					}
					return $localReturnValue;
				}
				$statusGatherer['element_column_values'] = RFWP_checkElementColumnValues($wpPrefix, $requiredElementColumnValues);
				return $statusGatherer;
			} catch (Exception $ex) {
				$messageFLog = 'some error in update Element Enum Values: '.$ex->getMessage().';';
				error_log(PHP_EOL.current_time('mysql').': '.$messageFLog.PHP_EOL,3,$rb_logFile);

				$statusGatherer['element_column_values'] = false;
				return $statusGatherer;
			} catch (Error $er) {
				$messageFLog = 'some error in update Element Enum Values: '.$er->getMessage().';';
				error_log(PHP_EOL.current_time('mysql').': '.$messageFLog.PHP_EOL,3,$rb_logFile);

				$statusGatherer['element_column_values'] = false;
				return $statusGatherer;
			}
		}
	}
	if (!function_exists('RFWP_wpRealbigSettingsTableUpdateFunction')) {
		function RFWP_wpRealbigSettingsTableUpdateFunction($wpPrefix) {
			global $wpdb;
			global $rb_logFile;
			try {
				$rez = $wpdb->query('SHOW FIELDS FROM ' . $wpPrefix . 'realbig_settings');

				if ($rez != 4) {
					$wpdb->query('ALTER TABLE ' . $wpPrefix . 'realbig_settings ADD `timeUpdate` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP AFTER optionValue');
				}
				return true;
			} catch (Exception $ex) {
				$messageFLog = 'some error in wpRealbigSettingsTableUpdate: '.$ex->getMessage().';';
				error_log(PHP_EOL.current_time('mysql').': '.$messageFLog.PHP_EOL,3,$rb_logFile);

				return false;
			} catch (Error $er) {
				$messageFLog = 'some error in wpRealbigSettingsTableUpdate: '.$er->getMessage().';';
				error_log(PHP_EOL.current_time('mysql').': '.$messageFLog.PHP_EOL,3,$rb_logFile);

				return false;
			}
		}
	}
	if (!function_exists('RFWP_wpRealbigPluginSettingsColomnUpdateFunction')) {
		function RFWP_wpRealbigPluginSettingsColomnUpdateFunction($wpPrefix, $colCheck, $statusGatherer) {
			global $wpdb;
			$atLeastOneMissedColumn                      = false;
			$requiredColumnsInRealbigPluginSettingsTable = [
				'block_number',
				'text',
				'setting_type',
				'element',
				'directElement',
				'elementPosition',
				'elementPlace',
				'firstPlace',
				'elementCount',
				'elementStep',
				'time_update',
				'minSymbols',
				'maxSymbols',
				'minHeaders',
				'maxHeaders',
				'onCategories',
				'offCategories',
				'onTags',
				'offTags',
                'elementCss',
			];
			global $rb_logFile;
			try {
			    // !!! not ready yet!!!

				foreach ($requiredColumnsInRealbigPluginSettingsTable as $item) {
					if (!in_array($item, $colCheck)) {
						$atLeastOneMissedColumn = true;
						if (in_array($item, ['text','directElement','onCategories','offCategories','onTags','offTags','elementCss'])) {
							$wpdb->query('ALTER TABLE '.$wpPrefix.'realbig_plugin_settings ADD COLUMN '.$item.' TEXT NULL DEFAULT NULL');
						} else {
							$wpdb->query('ALTER TABLE '.$wpPrefix.'realbig_plugin_settings ADD COLUMN '.$item.' INT(11) NULL DEFAULT NULL');
						}
					}
				}
				if ($atLeastOneMissedColumn == false) {
					$statusGatherer['realbig_plugin_settings_columns'] = true;
				} else {
					$statusGatherer['realbig_plugin_settings_columns'] = false;
				}

				return $statusGatherer;
			} catch (Exception $ex) {
				$messageFLog = 'some error in wpRealbigSettingsTableUpdate: '.$ex->getMessage().';';
				error_log(PHP_EOL.current_time('mysql').': '.$messageFLog.PHP_EOL,3,$rb_logFile);

				$statusGatherer['realbig_plugin_settings_columns'] = false;

				return $statusGatherer;
			} catch (Error $er) {
				$messageFLog = 'some error in wpRealbigSettingsTableUpdate: '.$er->getMessage().';';
				error_log(PHP_EOL.current_time('mysql').': '.$messageFLog.PHP_EOL,3,$rb_logFile);

				$statusGatherer['realbig_plugin_settings_columns'] = false;

				return $statusGatherer;
			}
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
				'optionValue' => 'update: '.$ex->getMessage()
			]);
		} else {
			$wpdb->update( $wpPrefix.'realbig_settings', [
				'optionName'  => 'deactError',
				'optionValue' => 'update: '.$ex->getMessage()
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
				'optionValue' => 'update: '.$er->getMessage()
			]);
		} else {
			$wpdb->update( $wpPrefix.'realbig_settings', [
				'optionName'  => 'deactError',
				'optionValue' => 'update: '.$er->getMessage()
			], ['optionName'  => 'deactError']);
		}
	} catch (Exception $exIex) {
	} catch (Error $erIex) { }

	deactivate_plugins(plugin_basename( __FILE__ ));
	?><div style="margin-left: 200px; border: 3px solid red"><?php echo $er; ?></div><?php
}