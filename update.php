<?php

include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
include_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

/**
 * Created by PhpStorm.
 * User: furio
 * Date: 2018-07-31
 * Time: 18:33
 *
 * @param $tableForCurrentPluginChecker
 * @param $tableForToken
 * @param $pluginActivityChecker
 *
 * @return Exception
 */


class UpdateClass
{
	function dbOldTablesRemoveFunction($wpdb, $wpPrefix)
	{
//		require_once $wpdb;
		try
		{
			$blocksTable = $wpdb->get_var('SHOW TABLES LIKE "WpRealbigPluginSettings"');
			$blocksTable1 = $wpdb->get_var('SHOW TABLES LIKE "Wp1RealbigPluginSettings"');
			$settingsTable = $wpdb->get_var('SHOW TABLES LIKE "realbigSettings"');
			$newBlocksTable = $wpdb->get_var('SHOW TABLES LIKE "'.$wpPrefix.'realbig_plugin_settings"');
			$newSettingsTable = $wpdb->get_var('SHOW TABLES LIKE "'.$wpPrefix.'realbig_settings"');

			if (!empty($blocksTable)&&!empty($newBlocksTable))
			{
				$wpdb->query($wpdb->prepare('DROP TABLE `WpRealbigPluginSettings`', []));
			}

			if (!empty($settingsTable)&&!empty($newSettingsTable))
			{
				$oldSettingTableData = $wpdb->get_results('SELECT * FROM realbigSettings');
				if (!empty($oldSettingTableData[0]))
				{
					$oldSettingTableData = get_object_vars($oldSettingTableData[0]);
				}
				$newSettingTableData = $wpdb->get_results('SELECT * FROM '.$wpPrefix.'realbig_settings');
				if (!empty($newSettingTableData[0]))
				{
					$newSettingTableData = get_object_vars($newSettingTableData[0]);
				}

				if (!empty($oldSettingTableData)&&empty($newSettingTableData))
				{
					$newSettingsSql = 'INSERT INTO '.$wpPrefix.'realbig_settings (optionName, optionValue) VALUES ("'.$oldSettingTableData['optionName'].'", "'.$oldSettingTableData['optionValue'].'")';
					$wpdb->query($wpdb->prepare($newSettingsSql, ''));
				}
				$wpdb->query($wpdb->prepare('DROP TABLE `realbigSettings`', []));
			}
		}
		catch (Exception $e)
		{
			echo $e;
		}
	}

	function dbTablesCreateFunction($tableForCurrentPluginChecker, $tableForToken, $pluginActivityChecker, $wpPrefix)
	{
		$old_tables = "WpRealbigPluginSettings, realbigSettings";
		if ($pluginActivityChecker && empty($tableForCurrentPluginChecker))
		{
			try
			{
				dbDelta("
CREATE TABLE `".$wpPrefix."realbig_plugin_settings` 
(
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`block_number` INT(11) NOT NULL,
	`text` TEXT NOT NULL,
	`setting_type` INT(11) NOT NULL,
	`element` ENUM('p','li','ul','ol','h1','h2','h3','h4','h5','h6') NOT NULL,
	`directElement` TEXT NOT NULL,
	`elementPosition` INT(11) NOT NULL,
	`elementPlace` INT(11) NOT NULL,
	`firstPlace` INT(11) NOT NULL,
	`elementCount` INT(11) NOT NULL,
	`elementStep` INT(11) NOT NULL,
	`time_create` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`time_update` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
    ", true);
				add_option('realbigForWP_version', $GLOBALS['realbigForWP_version']);
			}
			catch (Exception $e)
			{
//			return $e;
			}
		}

		if ($pluginActivityChecker && empty($tableForToken))
		{
			try
			{
				dbDelta("
CREATE TABLE `".$wpPrefix."realbig_settings` (
`id` INT(11) NOT NULL AUTO_INCREMENT,
`optionName` VARCHAR(50) NOT NULL,
`optionValue` TEXT NOT NULL,
PRIMARY KEY (`id`),
UNIQUE INDEX `optionName` (`optionName`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
    ", true);
			}
			catch (Exception $e)
			{
//			return $e;
			}
		}
	}
}




