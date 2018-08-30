<?php

include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
include_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
//require_once( ABSPATH . '/wp-includes/wp-db.php');

/**
 * Created by PhpStorm.
 * User: furio
 * Date: 2018-07-31
 * Time: 18:33
 */

try
{

function dbOldTablesRemoveFunction($wpPrefix)
{
//		require_once $wpdb;
//	require_once( ABSPATH . '/wp-includes/wp-db.php');
	global $wpdb;

	try
	{
		$blocksTable = $wpdb->get_var('SHOW TABLES LIKE "WpRealbigPluginSettings"');
		$settingsTable = $wpdb->get_var('SHOW TABLES LIKE "realbigSettings"');
		$newBlocksTable = $wpdb->get_var('SHOW TABLES LIKE "'.$wpPrefix.'realbig_plugin_settings"');
		$newSettingsTable = $wpdb->get_var('SHOW TABLES LIKE "'.$wpPrefix.'realbig_settings"');

		if (!empty($blocksTable)&&!empty($newBlocksTable))
		{
			$wpdb->query('DROP TABLE `WpRealbigPluginSettings`');
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
				$newSettingsSql = 'INSERT INTO '.$wpPrefix.'realbig_settings (optionName, optionValue) VALUES (%s, %s)';
				$wpdb->query($wpdb->prepare($newSettingsSql, [$oldSettingTableData['optionName'],$oldSettingTableData['optionValue']]));
			}
			$wpdb->query('DROP TABLE `realbigSettings`');
		}
	}
	catch (Exception $e)
	{
		echo $e;
	}
}

function dbTablesCreateFunction($tableForCurrentPluginChecker, $tableForToken, $wpPrefix)
{
//	$old_tables = "WpRealbigPluginSettings, realbigSettings";
	try
	{
		if (empty($tableForCurrentPluginChecker))
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
	`minSymbols` INT(11) NULL DEFAULT NULL,
	`time_create` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`time_update` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
    ", true);
			add_option('realbigForWP_version', $GLOBALS['realbigForWP_version']);
		}

		if (empty($tableForToken))
		{
			dbDelta("
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
    ", true);
		}
	}
	catch (Exception $e)
	{
		echo $e;
	}
}

function updateElementEnumValuesFunction()
{
    global $wpdb;
    try
    {
	    $enumTypeQuery = $wpdb->get_results('SHOW FIELDS FROM wp_realbig_plugin_settings WHERE Field = "element"');
	    if (!empty($enumTypeQuery))
	    {
		    $enumTypeQuery = get_object_vars($enumTypeQuery[0]);
//	    $enu1 = ;
		    if (!strpos($enumTypeQuery['Type'], 'blockquote'))
		    {
			    $wpdb->query("ALTER TABLE wp_realbig_plugin_settings MODIFY `element` ENUM('p','li','ul','ol','blockquote','h1','h2','h3','h4','h5','h6') NULL DEFAULT NULL");
		    }
		    else
		    {
		        return false;
		    }
		    $enumTypeQuery = $wpdb->get_results('SHOW FIELDS FROM wp_realbig_plugin_settings WHERE Field = "element"');
		    if (!empty($enumTypeQuery))
		    {
			    $enumTypeQuery = get_object_vars($enumTypeQuery[0]);
//	    $enu1 = ;
			    if (strpos($enumTypeQuery['Type'], 'blockquote'))
			    {
			        return true;
                }
                else
                {
			        return false;
                }
            } 
            else 
            {
		        return false;
            }
	    }
	    else
	    {
	        return false;
        }
    }
    catch (Exception $e)
    {
        return false;
    }
}

function wpRealbigSettingsTableUpdateFunction($wpPrefix)
{
	global $wpdb;

	try
	{
		$rez = $wpdb->query('SHOW FIELDS FROM '.$wpPrefix.'realbig_settings');

		if ($rez!=4)
		{
			$wpdb->query('ALTER TABLE '.$wpPrefix.'realbig_settings ADD `timeUpdate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER optionValue');
		}
		return true;
	}
	catch (Exception $e)
	{
		return false;
	}
}

function wpRealbigPluginSettingsColomnUpdateFunction($wpPrefix, $minSymbolsColumnStatus, $minHeadersColumnStatus)
{
    global $wpdb;

    try
    {
        if ($minSymbolsColumnStatus==false) {
	        $wpdb->query('ALTER TABLE '.$wpPrefix.'realbig_plugin_settings ADD COLUMN `minSymbols` INT(11) NULL DEFAULT NULL');
        }
        if ($minHeadersColumnStatus==false) {
	        $wpdb->query('ALTER TABLE '.$wpPrefix.'realbig_plugin_settings ADD COLUMN `minHeaders` INT(11) NULL DEFAULT NULL');
        }

        return true;
    }
    catch (Exception $e)
    {
        return false;
    }


}

}
catch (Exception $ex)
{
	deactivate_plugins(plugin_basename( __FILE__ ));
	?><div style="margin-left: 200px; border: 3px solid red"><? echo $ex; ?></div><?
}
catch (Error $er)
{
	deactivate_plugins(plugin_basename( __FILE__ ));
	?><div style="margin-left: 200px; border: 3px solid red"><? echo $er; ?></div><?
}