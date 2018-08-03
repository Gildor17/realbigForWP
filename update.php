<?php

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

	function dbTablesCreateFunction($tableForCurrentPluginChecker, $tableForToken, $pluginActivityChecker)
	{
		if ($pluginActivityChecker && empty($tableForCurrentPluginChecker))
		{
			try
			{
				dbDelta("
CREATE TABLE `WpRealbigPluginSettings` 
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
CREATE TABLE `realbigSettings` (
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




