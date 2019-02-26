<?php

if (!defined("ABSPATH")) { exit;}

//include ( dirname(__FILE__).'/../../../wp-load.php' );
include_once ( dirname(__FILE__)."/../../../wp-admin/includes/plugin.php" );
include_once ( dirname(__FILE__)."/../../../wp-admin/includes/upgrade.php" );
include_once ( dirname(__FILE__).'/../../../wp-includes/wp-db.php');

/**
 * Created by PhpStorm.
 * User: furio
 * Date: 2018-07-31
 * Time: 18:33
 */

try {
    function RFWP_manuallyTablesCreation ($wpPrefix) {
	    global $wpdb;
	    try {
		    $checkTable = $wpdb->get_var( 'SHOW TABLES LIKE "' . $wpPrefix . 'realbig_plugin_settings"' );
		    if (empty($checkTable)) {
			    $sql = "
CREATE TABLE `" . $wpPrefix . "realbig_plugin_settings` 
(
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`block_number` INT(11) NOT NULL,
	`text` TEXT NOT NULL,
	`setting_type` INT(11) NOT NULL,
	`element` ENUM('p','li','ul','ol','blockquote','img','video','h1','h2','h3','h4','h5','h6') NOT NULL,
	`directElement` TEXT NOT NULL,
	`elementPosition` INT(11) NOT NULL,
	`elementPlace` INT(11) NOT NULL,
	`firstPlace` INT(11) NOT NULL,
	`elementCount` INT(11) NOT NULL,
	`elementStep` INT(11) NOT NULL,
	`minSymbols` INT(11) NULL DEFAULT NULL,
	`minHeaders` INT(11) NULL DEFAULT NULL,
	`time_update` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB   
";
			    dbDelta($sql);
            }
            if (empty($wpdb->last_error)) {
	            $checkTable = $wpdb->get_var( 'SHOW TABLES LIKE "' . $wpPrefix . 'realbig_plugin_settings"' );
	            if (empty($checkTable)) {
		            return "fail";
	            } else {
		            return "success";
	            }
            } else {
	            return $wpdb->last_error;
            }
	    } catch (Exception $e) {
	        return $e->getMessage();
        }
    }

	function RFWP_dbOldTablesRemoveFunction( $wpPrefix, $statusGatherer ) {
		global $wpdb;
		try {
			$blocksTable      = $wpdb->get_var( 'SHOW TABLES LIKE "WpRealbigPluginSettings"' );
			$settingsTable    = $wpdb->get_var( 'SHOW TABLES LIKE "realbigSettings"' );
			$newBlocksTable   = $wpdb->get_var( 'SHOW TABLES LIKE "' . $wpPrefix . 'realbig_plugin_settings"' );
			$newSettingsTable = $wpdb->get_var( 'SHOW TABLES LIKE "' . $wpPrefix . 'realbig_settings"' );

			if ( ! empty( $blocksTable ) && ! empty( $newBlocksTable ) ) {
				$wpdb->query( 'DROP TABLE `WpRealbigPluginSettings`' );
			}
			if ( ! empty( $settingsTable ) && ! empty( $newSettingsTable ) ) {
				$oldSettingTableData = $wpdb->get_results( 'SELECT * FROM realbigSettings' );
				if ( ! empty( $oldSettingTableData[0] ) ) {
					$oldSettingTableData = get_object_vars( $oldSettingTableData[0] );
				}
				$newSettingTableData = $wpdb->get_results( 'SELECT * FROM ' . $wpPrefix . 'realbig_settings' );
				if ( ! empty( $newSettingTableData[0] ) ) {
					$newSettingTableData = get_object_vars( $newSettingTableData[0] );
				}
				if ( ! empty( $oldSettingTableData ) && empty( $newSettingTableData ) ) {
					$newSettingsSql = 'INSERT INTO ' . $wpPrefix . 'realbig_settings (optionName, optionValue) VALUES (%s, %s)';
					$wpdb->query( $wpdb->prepare( $newSettingsSql, [
						$oldSettingTableData['optionName'],
						$oldSettingTableData['optionValue']
					] ) );
				}
				$wpdb->query( 'DROP TABLE `realbigSettings`' );
			}
			if ( empty( $blocksTable ) && empty( $settingsTable ) ) {
				$statusGatherer['old_tables_removed'] = true;
			}

			return $statusGatherer;
		} catch ( Exception $e ) {
			echo $e;
			$statusGatherer['old_tables_removed'] = false;

			return $statusGatherer;
		}
	}

	function RFWP_dbTablesCreateFunction( $tableForCurrentPluginChecker, $tableForToken, $wpPrefix, $statusGatherer ) {
	    global $wpdb;
		try {
			if (empty($tableForCurrentPluginChecker)) {

			    $sql = "
CREATE TABLE `" . $wpPrefix . "realbig_plugin_settings` 
(
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`block_number` INT(11) NOT NULL,
	`text` TEXT NOT NULL,
	`setting_type` INT(11) NOT NULL,
	`element` ENUM('p','li','ul','ol','blockquote','img','video','h1','h2','h3','h4','h5','h6') NOT NULL,
	`directElement` TEXT NOT NULL,
	`elementPosition` INT(11) NOT NULL,
	`elementPlace` INT(11) NOT NULL,
	`firstPlace` INT(11) NOT NULL,
	`elementCount` INT(11) NOT NULL,
	`elementStep` INT(11) NOT NULL,
	`minSymbols` INT(11) NULL DEFAULT NULL,
	`minHeaders` INT(11) NULL DEFAULT NULL,
	`time_update` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB   
";
				dbDelta($sql, true);
				add_option( 'realbigForWP_version', $GLOBALS['realbigForWP_version'] );
//				if (!empty($wpdb->get_var( 'SHOW TABLES LIKE "' . $wpPrefix . 'realbig_plugin_settings"' ))) {
//					$statusGatherer['realbig_plugin_settings_table'] = true;
//                }
			} else {
				$statusGatherer['realbig_plugin_settings_table'] = true;
			}

			if (empty($tableForToken)) {

				$sql = "
CREATE TABLE `" . $wpPrefix . "realbig_settings` (
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
			} else {
				$statusGatherer['realbig_settings_table'] = true;
			}

			return $statusGatherer;
		} catch (Exception $e) {
			echo $e;
			$statusGatherer['realbig_plugin_settings_table'] = false;
			$statusGatherer['realbig_settings_table']        = false;

			return $statusGatherer;
		}
	}

	function RFWP_updateElementEnumValuesFunction( $wpPrefix, $statusGatherer ) {
		global $wpdb;
		$requiredElementColumnValues = "enum('p','li','ul','ol','blockquote','img','video','h1','h2','h3','h4','h5','h6')";
		try {
			$enumTypeQuery = $wpdb->get_results( 'SHOW FIELDS FROM ' . $wpPrefix . 'realbig_plugin_settings WHERE Field = "element"' );
			if ( ! empty( $enumTypeQuery ) ) {
				$enumTypeQuery = get_object_vars( $enumTypeQuery[0] );
				if ( $enumTypeQuery['Type'] != $requiredElementColumnValues ) {
					$wpdb->query( "ALTER TABLE " . $wpPrefix . "realbig_plugin_settings MODIFY `element` ENUM('p','li','ul','ol','blockquote','img','video','h1','h2','h3','h4','h5','h6') NULL DEFAULT NULL" );
					$statusGatherer['element_column_values'] = false;
					return $statusGatherer;
				} else {
					$statusGatherer['element_column_values'] = true;
					return $statusGatherer;
				}
			} else {
				$statusGatherer['element_column_values'] = false;
				return $statusGatherer;
			}
		} catch ( Exception $e ) {
			$statusGatherer['element_column_values'] = false;
			return $statusGatherer;
		}
	}

	function RFWP_wpRealbigSettingsTableUpdateFunction( $wpPrefix ) {
		global $wpdb;

		try {
			$rez = $wpdb->query( 'SHOW FIELDS FROM ' . $wpPrefix . 'realbig_settings' );

			if ( $rez != 4 ) {
				$wpdb->query( 'ALTER TABLE ' . $wpPrefix . 'realbig_settings ADD `timeUpdate` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP AFTER optionValue' );
			}
			return true;
		} catch ( Exception $e ) {
			return false;
		}
	}

	function RFWP_wpRealbigPluginSettingsColomnUpdateFunction( $wpPrefix, $colCheck, $statusGatherer ) {
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
			'minHeaders'
		];

		try {
			foreach ( $requiredColumnsInRealbigPluginSettingsTable as $item ) {
				if ( ! in_array( $item, $colCheck ) ) {
					$atLeastOneMissedColumn = true;
					$wpdb->query( 'ALTER TABLE ' . $wpPrefix . 'realbig_plugin_settings ADD COLUMN ' . $item . ' INT(11) NULL DEFAULT NULL' );
				}
			}
			if ( $atLeastOneMissedColumn == false ) {
				$statusGatherer['realbig_plugin_settings_columns'] = true;
			} else {
				$statusGatherer['realbig_plugin_settings_columns'] = false;
			}

			return $statusGatherer;
		} catch ( Exception $e ) {
			$statusGatherer['realbig_plugin_settings_columns'] = false;

			return $statusGatherer;
		}
	}

}
catch (Exception $ex)
{
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