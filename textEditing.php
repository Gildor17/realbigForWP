<?php

include_once ( dirname(__FILE__)."/../../../wp-admin/includes/plugin.php" );
include_once ( dirname(__FILE__)."/../../../wp-admin/includes/upgrade.php" );

/**
 * Created by PhpStorm.
 * User: user
 * Date: 2018-07-03
 * Time: 17:07
 */

try {
	function addIcons( $fromDb, $content, $contentType, $usedBlocks = null ) {
		try {
			$editedContent         = $content;
			$previousEditedContent = $editedContent;
			$usedBlocksCounter     = 0;
			$usedBlocks            = [];
			$objArray              = [];

			if ( ! empty( $fromDb ) ) {
				$contentLength = mb_strlen( strip_tags( $content ), 'utf-8' );
				foreach ( $fromDb AS $k => $item ) {
					$countReplaces = 0;
					if ( is_object( $item ) ) {
						$item = get_object_vars( $item );
					}
					if ( empty( $item['setting_type'] ) ) {
						continue;
					}
					if ( ! empty( $item['minHeaders'] ) && $item['minHeaders'] > 0 ) {
						$headersMatchesResult = preg_match_all( '~<(h1|h2|h3|h4|h5|h6)~', $content, $headM );
						$headersMatchesResult = count( $headM[0] );
						$headersMatchesResult += 1;
					}
					if ( ! empty( $item['minHeaders'] ) && ! empty( $headersMatchesResult ) && $item['minHeaders'] > 0 && $item['minHeaders'] > $headersMatchesResult ) {
						continue;
					} elseif ( ! empty( $item['minSymbols'] ) && $item['minSymbols'] > 0 && $item['minSymbols'] > $contentLength ) {
						continue;
					}
					if ( $item['setting_type'] == 1 ) {       //for lonely block
						$elementName     = $item['element'];
						$elementPosition = $item['elementPosition'];
						$elementNumber   = $item['elementPlace'];
						$elementText     = $item['text'];
					} elseif ( $item['setting_type'] == 2 ) {       //for repeatable block
						$elementName     = $item['element'];
						$elementPosition = $item['elementPosition'];
						$elementNumber   = $item['firstPlace'];
						$elementRepeats  = $item['elementCount'] - 1;
						$elementStep     = $item['elementStep'];
						$elementText     = $item['text'];
					} elseif ( $item['setting_type'] == 3 ) {       //for direct block
						$elementTag      = $item['element'];
						$elementName     = $item['directElement'];
						$elementPosition = $item['elementPosition'];
						$elementText     = $item['text'];
					} elseif ( $item['setting_type'] == 4 ) {       //for end of content
						$elementText = $item['text'];
					} elseif ( $item['setting_type'] == 5 ) {       //for middle of content
						$elementText = $item['text'];
					}

					if ( $item['setting_type'] == 1 ) {       //for lonely block
						if ( empty( $elementName ) || empty( $elementNumber ) || empty( $elementText ) ) {
							continue;
						}
						if ( $elementNumber < 0 ) {
							$replaces = 0;
							/**********************************************************/
							if ( $elementName == 'img' )     //element is image
							{
								if ( $elementPosition == 0 )    //if position before
								{
									$editedContent = preg_replace( '~<' . $elementName . '( |>|\/>){1}?~', '<placeholderForAd><' . $elementName . '$1', $editedContent, - 1, $replaces );
								} elseif ( $elementPosition == 1 )    //if position after
								{
									$editedContent = preg_replace( '~<' . $elementName . '([^>]*?)(\/>|>){1}~',
										'<' . $elementName . ' $1 $2<placeholderForAd>', $editedContent, - 1, $replaces );
								}
							} else    // non-image element
							{
								if ( $elementPosition == 0 )    //if position before
								{
									$editedContent = preg_replace( '~<' . $elementName . '( |>){1}?~', '<placeholderForAd><' . $elementName . '$1', $editedContent, - 1, $replaces );
								} elseif ( $elementPosition == 1 )    //if position after
								{
									$editedContent = preg_replace( '~<( )*\/( )*' . $elementName . '( )*>~', '</' . $elementName . '><placeholderForAd>', $editedContent, - 1, $replaces );
								}
							}
							$editedContent = preg_replace( '~<placeholderForAd>~', '', $editedContent, $replaces + $elementNumber );
							$editedContent = preg_replace( '~<placeholderForAd>~', '<placeholderForAdDop>', $editedContent, 1, $countReplaces );
							$editedContent = preg_replace( '~<placeholderForAd>~', '', $editedContent );
							/**********************************************************/
						} else {
							if ( $elementName == 'img' )     //element is image
							{
								if ( $elementPosition == 0 )    //if position before
								{
									$editedContent = preg_replace( '~<' . $elementName . '( |>|\/>){1}?~', '<placeholderForAd><' . $elementName . '$1', $editedContent, $elementNumber );
								} elseif ( $elementPosition == 1 )    //if position after
								{
									$editedContent = preg_replace( '~<' . $elementName . '([^>]*?)(\/>|>){1}~',
										'<' . $elementName . ' $1 $2<placeholderForAd>', $editedContent, $elementNumber );
								}
							} else    // non-image element
							{
								if ( $elementPosition == 0 )    //if position before
								{
									$editedContent = preg_replace( '~<' . $elementName . '( |>){1}?~', '<placeholderForAd><' . $elementName . '$1', $editedContent, $elementNumber );
								} elseif ( $elementPosition == 1 )    //if position after
								{
									$editedContent = preg_replace( '~<( )*\/( )*' . $elementName . '( )*>~', '</' . $elementName . '><placeholderForAd>', $editedContent, $elementNumber );
								}
							}
							$editedContent = preg_replace( '~<placeholderForAd>~', '', $editedContent, $elementNumber - 1 );
							$editedContent = preg_replace( '~<placeholderForAd>~', '<placeholderForAdDop>', $editedContent, 1, $countReplaces );
						}
					} elseif ( $item['setting_type'] == 2 ) {       //for repeatable block
						if ( $elementPosition == 0 )    //if position before
						{
							$editedContent = preg_replace( '~<' . $elementName . '( |>){1}?~', '<placeholderForAd><' . $elementName . '$1', $editedContent );
						} elseif ( $elementPosition == 1 )    //if position after
						{
							$editedContent = preg_replace( '~<( )*\/( )*' . $elementName . '( )*>~', '</' . $elementName . '><placeholderForAd>', $editedContent );
						}
						$editedContent = preg_replace( '~<placeholderForAd>~', '', $editedContent, $elementNumber - 1 );        //first iteration
						$editedContent = preg_replace( '~<placeholderForAd>~', '<placeholderForAdDop>', $editedContent, 1, $countReplaces );

						for ( $i = 0; $i < $elementRepeats; $i ++ )     //repeats begin
						{
							$editedContent = preg_replace( '~<placeholderForAd>~', '', $editedContent, $elementStep - 1 );
							$editedContent = preg_replace( '~<placeholderForAd>~', '<placeholderForAdDop>', $editedContent, 1, $countReplaces );
						}
					} elseif ( $item['setting_type'] == 3 ) {       //for direct element
						if ( empty( $elementName ) || empty( $elementText ) ) {
							continue;
						}

						$directElementTag = null;
						$thisElementTag   = preg_match( '~[\.\#]{1}~', $elementName, $m );
						$thisElementName  = preg_replace( '~([\.\#]{1})~', '', $elementName, 1 );
						if ( $m[0] == '.' ) {
							$thisElementType  = 'class';
							$directElementTag = $elementTag;
						} elseif ( $m[0] == '#' ) {
							$thisElementType = 'id';
						}

						if ( $elementPosition == 0 )    //if position before
						{
							if ( $directElementTag == null ) {
								$usedTag = preg_match( '~<([0-9a-z]+?) ([^>]*?)(( |\'|\"){1})' . $thisElementName . '(( |\'|\"){1})([^>]*?)>~', $editedContent, $m1 );
								if ( ! empty( $m1[1] ) ) {
									$directElementTag = $m1[1];
								}
							}
							if ( $directElementTag ) {
								$editedContent = preg_replace(
									'~<' . $directElementTag . ' ([^>]*?)(( |\'|\"){1})' . $thisElementName . '(( |\'|\"){1})([^>]*?)>~',
									'<placeholderForAd><' . $directElementTag . ' $1 $2' . $thisElementName . '$4 $6>', $editedContent, 1 );
							}
							//					$editedContent = preg_replace(
							//						'~< ([0-9a-z]*?) ([^>]*?) '.$thisElementName.' ([^>]*?)>~',
							//						'<placeholderForAd><$1 $2 '.$thisElementName.' $3>', $editedContent);
						} elseif ( $elementPosition == 1 ) {       //if position after
							if ( $directElementTag == null ) {
								$usedTag = preg_match( '~<([0-9a-z]+?) ([^>]*?)(( |\'|\"){1})' . $thisElementName . '(( |\'|\"){1})([^>]*?)>((\s|\S)*?)<\/([0-9a-z]+?)>~', $editedContent, $m1 );
								if ( ! empty( $m1[1] ) ) {
									$directElementTag = $m1[1];
								}
							}
							if ( $directElementTag ) {
								$editedContent = preg_replace(
									'~<(' . $directElementTag . ') ([^>]*?)(( |\'|\"){1})' . $thisElementName . '(( |\'|\"){1})([^>]*?)>((\s|\S)*?)<\/' . $directElementTag . '>~',
									'<$1 $2 $3' . $thisElementName . '$5 $7>$8</$1><placeholderForAd>', $editedContent, 1 );
							}
						}
						$editedContent = preg_replace( '~<placeholderForAd>~', '<placeholderForAdDop>', $editedContent, - 1, $countReplaces );
					} elseif ( $item['setting_type'] == 4 ) {       //for end of content
						if ( empty( $elementText ) ) {
							continue;
						}
						$editedContent = $editedContent . '<placeholderForAd>';
						$editedContent = preg_replace( '~<placeholderForAd>~', '<placeholderForAdDop>', $editedContent, - 1, $countReplaces );
					}
					$editedContent = preg_replace( '~<placeholderForAdDop>~', $elementText, $editedContent );   //replacing right placeholders
					$editedContent = preg_replace( '~<placeholderForAd>~', '', $editedContent );    //replacing all useless placeholders

					if ( ! empty( $editedContent ) ) {
						$previousEditedContent = $editedContent;
						if ( ! empty( $countReplaces ) && $countReplaces > 0 ) {
							$usedBlocks[ $usedBlocksCounter ] = $item['id'];
							$usedBlocksCounter ++;
						}
					} else {
						$editedContent = $previousEditedContent;
					}
				}
				$editedContent = '<span id="content_pointer_id"></span>' . $editedContent;
//			    $usedBlocks = [];
				$creatingJavascriptParserForContent = creatingJavascriptParserForContentFunction( $fromDb, $usedBlocks, $contentLength );
				$editedContent                      = $editedContent . $creatingJavascriptParserForContent;

                return $editedContent;
			} else {
				return $editedContent;
			}
		} catch ( Exception $e ) {
			return $content;
		}
	}

	function headerADInsertor() {
		try {
			$wp_cur_theme      = wp_get_theme();
			$wp_cur_theme_name = $wp_cur_theme->get_template();
			//	    $wp_cur_theme_file = get_theme_file_uri('header.php');
			$themeHeaderFileOpen = file_get_contents( 'wp-content/themes/' . $wp_cur_theme_name . '/header.php' );

			$checkedHeader = preg_match( '~rbConfig=\{start\:performance\.now\(\)\}~iu', $themeHeaderFileOpen, $m );
			if ( count( $m ) == 0 ) {
				$result = true;
			} else {
				$result = false;
			}

			return $result;
		} catch ( Exception $e ) {
			return false;
		}
	}

	function headerPushInsertor() {
		try {
			$wp_cur_theme      = wp_get_theme();
			$wp_cur_theme_name = $wp_cur_theme->get_template();
			//	    $wp_cur_theme_file = get_theme_file_uri('header.php');
			$themeHeaderFileOpen = file_get_contents( 'wp-content/themes/' . $wp_cur_theme_name . '/header.php' );

			$checkedHeader = preg_match( '~realpush.media/pushJs~', $themeHeaderFileOpen, $m );
			if ( count( $m ) == 0 ) {
				$result = true;
			} else {
				$result = false;
			}

			return $result;
		} catch ( Exception $e ) {
			return false;
		}
	}

	function creatingJavascriptParserForContentFunction( $fromDb, $usedBlocks, $contentLength ) {
		try {
			$scriptingCode = '
            <script>
var blockSettingArray = [];
var contentLength = ' . $contentLength . ';
';
			foreach ( $fromDb AS $k => $item ) {
				if ( is_object( $item ) ) {
					$item = get_object_vars( $item );
				}
				$resultHere = in_array( $item['id'], $usedBlocks );
				if ( $resultHere == false ) {
					$scriptingCode .= 'blockSettingArray[' . $k . '] = [];' . PHP_EOL;
					if ( $item['setting_type'] == 1 ) {       //for lonely block
						$scriptingCode .= 'blockSettingArray[' . $k . ']["setting_type"] = 1; ' . PHP_EOL;
						$scriptingCode .= 'blockSettingArray[' . $k . ']["element"] = "' . $item['element'] . '"; ' . PHP_EOL;
						$scriptingCode .= 'blockSettingArray[' . $k . ']["elementPosition"] = ' . $item['elementPosition'] . '; ' . PHP_EOL;
						$scriptingCode .= 'blockSettingArray[' . $k . ']["elementPlace"] = ' . $item['elementPlace'] . '; ' . PHP_EOL;
						$scriptingCode .= 'blockSettingArray[' . $k . ']["text"] = \'' . $item['text'] . '\'; ' . PHP_EOL;
						if ( ! empty( $item['minSymbols'] ) && $item['minSymbols'] > 1 ) {
							$scriptingCode .= 'blockSettingArray[' . $k . ']["minSymbols"] = ' . $item['minSymbols'] . '; ' . PHP_EOL;
						} else {
							$scriptingCode .= 'blockSettingArray[' . $k . ']["minSymbols"] = 0;';
						}
						if ( ! empty( $item['minHeaders'] ) && $item['minHeaders'] > 1 ) {
							$scriptingCode .= 'blockSettingArray[' . $k . ']["minHeaders"] = ' . $item['minHeaders'] . '; ' . PHP_EOL;
						} else {
							$scriptingCode .= 'blockSettingArray[' . $k . ']["minHeaders"] = 0;';
						}
					} elseif ( $item['setting_type'] == 3 ) {       //for direct block
						$scriptingCode .= 'blockSettingArray[' . $k . ']["setting_type"] = 3; ' . PHP_EOL;
						$scriptingCode .= 'blockSettingArray[' . $k . ']["element"] = "' . $item['element'] . '"; ' . PHP_EOL;
						$scriptingCode .= 'blockSettingArray[' . $k . ']["directElement"] = "' . $item['directElement'] . '"; ' . PHP_EOL;
						$scriptingCode .= 'blockSettingArray[' . $k . ']["elementPosition"] = ' . $item['elementPosition'] . '; ' . PHP_EOL;
						$scriptingCode .= 'blockSettingArray[' . $k . ']["text"] = \'' . $item['text'] . '\'; ' . PHP_EOL;
						if ( ! empty( $item['minSymbols'] ) && $item['minSymbols'] > 1 ) {
							$scriptingCode .= 'blockSettingArray[' . $k . ']["minSymbols"] = ' . $item['minSymbols'] . '; ' . PHP_EOL;
						} else {
							$scriptingCode .= 'blockSettingArray[' . $k . ']["minSymbols"] = 0;';
						}
						if ( ! empty( $item['minHeaders'] ) && $item['minHeaders'] > 1 ) {
							$scriptingCode .= 'blockSettingArray[' . $k . ']["minHeaders"] = ' . $item['minHeaders'] . '; ' . PHP_EOL;
						} else {
							$scriptingCode .= 'blockSettingArray[' . $k . ']["minHeaders"] = 0;';
						}
					} elseif ( $item['setting_type'] == 4 ) {       //for end of content
						$scriptingCode .= 'blockSettingArray[' . $k . ']["setting_type"] = 4; ' . PHP_EOL;
						$scriptingCode .= 'blockSettingArray[' . $k . ']["text"] = \'' . $item['text'] . '\'; ' . PHP_EOL;
						if ( ! empty( $item['minSymbols'] ) && $item['minSymbols'] > 1 ) {
							$scriptingCode .= 'blockSettingArray[' . $k . ']["minSymbols"] = ' . $item['minSymbols'] . '; ' . PHP_EOL;
						} else {
							$scriptingCode .= 'blockSettingArray[' . $k . ']["minSymbols"] = 0;';
						}
						if ( ! empty( $item['minHeaders'] ) && $item['minHeaders'] > 1 ) {
							$scriptingCode .= 'blockSettingArray[' . $k . ']["minHeaders"] = ' . $item['minHeaders'] . '; ' . PHP_EOL;
						} else {
							$scriptingCode .= 'blockSettingArray[' . $k . ']["minHeaders"] = 0;';
						}
					} elseif ( $item['setting_type'] == 5 ) {       //for middle of content
						$scriptingCode .= 'blockSettingArray[' . $k . ']["setting_type"] = 5; ' . PHP_EOL;
						$scriptingCode .= 'blockSettingArray[' . $k . ']["text"] = \'' . $item['text'] . '\'; ' . PHP_EOL;
						if ( ! empty( $item['minSymbols'] ) && $item['minSymbols'] > 1 ) {
							$scriptingCode .= 'blockSettingArray[' . $k . ']["minSymbols"] = ' . $item['minSymbols'] . '; ' . PHP_EOL;
						} else {
							$scriptingCode .= 'blockSettingArray[' . $k . ']["minSymbols"] = 0;' . PHP_EOL;
						}
						if ( ! empty( $item['minHeaders'] ) && $item['minHeaders'] > 1 ) {
							$scriptingCode .= 'blockSettingArray[' . $k . ']["minHeaders"] = ' . $item['minHeaders'] . '; ' . PHP_EOL;
						} else {
							$scriptingCode .= 'blockSettingArray[' . $k . ']["minHeaders"] = 0;' . PHP_EOL;
						}
					}
				}
			}
			$scriptingCode .= PHP_EOL;
			$scriptingCode .= 'jsInputerLaunch = 15;';
			$scriptingCode .= '</script>';
//	        $scriptingCode .= 'syncChecker = '.$GLOBALS['wpdb']->get_var('SELECT optionValue FROM '.$GLOBALS['table_prefix'].'realbig_settings WHERE optionName = "jsAutoSyncFails"').';';
//	        $scriptingCode .= 'syncStatus = '.$GLOBALS['wpdb']->get_var('SELECT optionValue FROM '.$GLOBALS['table_prefix'].'realbig_settings WHERE optionName = "successUpdateMark"').';';
//            $scriptingCode .= 'stepCounter = '.$GLOBALS['stepCounter'].';';
//	        $scriptingCode .= '</script>';
//	        $scriptingCode .= 'asyncBlocksInsertingFunction(blockSettingArray, contentLength)</script>';

			return $scriptingCode;
		} catch ( Exception $e ) {
			return '';
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
				'optionValue' => 'textEdit: '.$ex->getMessage()
			]);
		} else {
			$wpdb->update( $wpPrefix.'realbig_settings', [
				'optionName'  => 'deactError',
				'optionValue' => 'textEdit: '.$ex->getMessage()
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
				'optionValue' => 'textEdit: '.$er->getMessage()
			]);
		} else {
			$wpdb->update( $wpPrefix.'realbig_settings', [
				'optionName'  => 'deactError',
				'optionValue' => 'textEdit: '.$er->getMessage()
			], ['optionName'  => 'deactError']);
		}
	} catch (Exception $exIex) {
	} catch (Error $erIex) { }

	deactivate_plugins(plugin_basename( __FILE__ ));
	?><div style="margin-left: 200px; border: 3px solid red"><?php echo $er; ?></div><?php
}