<?php

if (!defined("ABSPATH")) { exit;}

/**
 * Created by PhpStorm.
 * User: user
 * Date: 2018-07-03
 * Time: 17:07
 */

try {
    function RFWP_gatheringContentLength($content, $isRepeated=null) {
        try {
	        $contentForLength = '';
	        $contentLength = 0;
	        $cuttedContent = $content;
	        $listOfTags = [];
	        $listOfTags['unavailable'] = ['ins','script','style'];
	        $listOfTags['available'] = ['p','div','span','blockquote','table','ul','ol','h1','h2','h3','h4','h5','h6','strong',];
	        $listOfSymbolsForEcranising = '(\/|\$|\^|\.|\,|\&|\||\(|\)|\+|\-|\*|\?|\!|\[|\]|\{|\}|\<|\>|\\\|\~){1}';
	        if (empty($isRepeated)) {
		        foreach ($listOfTags AS $affiliation => $listItems) {
			        for ($lc = 0; $lc < count($listItems); $lc++) {
				        $cycler = 1;
				        $tg1 = $listItems[$lc];
				        $pattern1 = '~(<'.$tg1.'>|<'.$tg1.'\s[^>]*?>)(((?!<'.$tg1.'>)(?!<'.$tg1.'\s[^>]*?>))[\s\S]*?)(<\/'.$tg1.'>)~';

				        while (!empty($cycler)) {
					        preg_match($pattern1, $cuttedContent, $clMatch);
					        if (!empty($clMatch[0])) {
						        if ($affiliation == 'available') {
							        $contentForLength .= $clMatch[0];
						        }
						        // if nothing help, change system to array with loop type
//                                $resItem = preg_replace('~'.$listOfSymbolsForEcranising.'~', '\\\$1', $clMatch[0], -1, $crc);
//                                $cuttedContent = preg_replace('~'.$resItem.'~', '', $cuttedContent, 1,$repCount);
						        $resItem = preg_replace_callback('~'.$listOfSymbolsForEcranising.'~', function ($matches) {return '\\'.$matches[1];}, $clMatch[0], -1, $crc);
						        $cuttedContent = preg_replace_callback('~'.$resItem.'~', function () {return '';}, $cuttedContent, 1,$repCount);
						        $cycler = 1;
					        } else {
						        $cycler = 0;
					        }
				        }
			        }
		        }

		        $contentLength = mb_strlen(strip_tags($contentForLength), 'utf-8');
		        return $contentLength;
	        } else {
		        return $contentLength;
	        }
        } catch (Exception $ex1) {
	        return 0;
        } catch (Error $er1) {
	        return 0;
        }
    }

	function RFWP_addIcons($fromDb, $content, $contentType, $cachedBlocks, $inserts=null, $shortcodes) {
		try {

			global $wp_query;
			global $post;

			if (!empty($GLOBALS['dev_mode'])) {
//				global $wpdb;
//				$curUserCan = current_user_can('activate_plugins');
//				if (!empty($curUserCan)) {
//					if (!empty($GLOBALS['rb_mobile_check'])) {
//						$localMobileString = 'mobile';
//					} else {
//						$localMobileString = 'desktop';
//					}
//
//					$content = '<script>console.log("accessed")</script>'.$content;
//
//					$testPostGather = $wpdb->get_results('SELECT * FROM '.$GLOBALS['wpPrefix'].'posts WHERE post_type = "rb_block_desktop_new" AND post_title IN (51421,39127)');
//					if (!empty($testPostGather)&&!empty($testPostGather[0]->post_content)) {
//						$checkScriptText = preg_match_all('~script type~', $testPostGather[0]->post_content,$cm);
////					$testPostGatherContent = htmlspecialchars_decode($testPostGather[0]->post_content);
//						$testPostGatherContent = $testPostGather[0]->post_content;
//						$testPostGatherContent = preg_replace('~corner_open;~', '<', $testPostGatherContent);
//						$testPostGatherContent = preg_replace('~corner_close;~', '>', $testPostGatherContent);
//						$testPostGatherContent = preg_replace('~\<scr_pt_open;~', '<script', $testPostGatherContent);
//						$testPostGatherContent = preg_replace('~\/scr_pt_close;~', '/script', $testPostGatherContent);
//						$testPostGatherContent = '<div id="cacheTest_id">'.$testPostGatherContent.'</div>';
//						if (!empty($cm[0])) {
//							$testPostGatherContent = '<script>console.log("scripted")</script>'.$testPostGatherContent;
//						}
//
//						$content = $testPostGatherContent.$content;
//					}
//				}
			}

			$editedContent         = $content;
			$contentLength         = 0;

			$getPageTags = get_the_tags(get_the_ID());
			$getPageCategories = get_the_category(get_the_ID());

			$previousEditedContent = $editedContent;
			$usedBlocksCounter     = 0;
			$usedBlocks            = [];
			$objArray              = [];
			$onCategoriesArray = [];
			$offCategoriesArray = [];
			$onTagsArray = [];
			$offTagsArray = [];
			$pageCategories = [];
			$pageTags = [];

			if (!empty($fromDb)) {
			    /** New system for content length checking **/
				$contentLength = RFWP_gatheringContentLength($content);
				/** End of new system for content length checking **/
				if ($contentLength < 1) {
					$contentLength = mb_strlen(strip_tags($content), 'utf-8');
                }
				$contentLengthOld = mb_strlen(strip_tags($content), 'utf-8');

				if (!empty($getPageCategories)) {
					$ctCounter = 0;

					foreach ($getPageCategories AS $k1 => $item1) {
						$pageCategories[$ctCounter] = $item1->name;
						$pageCategories[$ctCounter] = trim($pageCategories[$ctCounter]);
						$pageCategories[$ctCounter] = strtolower($pageCategories[$ctCounter]);
						$ctCounter++;
					}
					unset($k1,$item1);
				}
				if (!empty($getPageTags)) {
					$ctCounter = 0;

					foreach ($getPageTags AS $k1 => $item1) {
						$pageTags[$ctCounter] = $item1->name;
						$pageTags[$ctCounter] = trim($pageTags[$ctCounter]);
						$pageTags[$ctCounter] = strtolower($pageTags[$ctCounter]);
						$ctCounter++;
					}
					unset($k1,$item1);
				}

//				$contentLengthOld = mb_strlen(strip_tags($content), 'utf-8');
/*              ?><script>console.log('new content:'+<?php echo $contentLength ?>);console.log('old content:'+<?php echo $contentLengthOld ?>);</script><?php  */
 				foreach ($fromDb AS $k => $item) {
					$countReplaces = 0;

				    if ( is_object( $item ) ) {
						$item = get_object_vars( $item );
					}
					if (empty($item['setting_type'])) {
						$usedBlocks[$usedBlocksCounter] = $item['id'];
						$usedBlocksCounter ++;
						continue;
					}
					if (!empty($item['minHeaders']) && $item['minHeaders'] > 0) {
						$headersMatchesResult = preg_match_all('~<(h1|h2|h3|h4|h5|h6)~', $content, $headM);
						$headersMatchesResult = count($headM[0]);
						$headersMatchesResult += 1;
					}
					if (!empty($item['minHeaders']) && ! empty($headersMatchesResult) && $item['minHeaders'] > 0 && $item['minHeaders'] > $headersMatchesResult) {
						$usedBlocks[$usedBlocksCounter] = $item['id'];
						$usedBlocksCounter ++;
						continue;
					} elseif (!empty($item['minSymbols']) && $item['minSymbols'] > 0 && $item['minSymbols'] > $contentLength) {
						$usedBlocks[$usedBlocksCounter] = $item['id'];
						$usedBlocksCounter ++;
						continue;
					}

				    /************************************* */
                    $passAllowed = false;
                    $passRejected = false;

                    if (!empty($item['onCategories'])) {
                        if (empty($pageCategories)) {
	                        $usedBlocks[$usedBlocksCounter] = $item['id'];
	                        $usedBlocksCounter ++;
	                        continue;
                        }
                        $onCategoriesArray = explode(':',trim($item['onCategories']));
                        if (!empty($onCategoriesArray)&&count($onCategoriesArray) > 0) {
                            foreach ($onCategoriesArray AS $k1 => $item1) {
                                $currentCategory = trim($item1);
                                $currentCategory = strtolower($currentCategory);

                                if (in_array($currentCategory, $pageCategories)) {
                                    $passAllowed = true;
                                    break;
                                }
                            }
                            unset($k1,$item1);
                            if (empty($passAllowed)) {
	                            $usedBlocks[$usedBlocksCounter] = $item['id'];
	                            $usedBlocksCounter ++;
	                            continue;
                            }
                        }
                    } elseif (!empty($item['offCategories'])&&!empty($pageCategories)) {
	                    $offCategoriesArray = explode(':',trim($item['offCategories']));
                        if (!empty($offCategoriesArray)&&count($offCategoriesArray) > 0) {
                            foreach ($offCategoriesArray AS $k1 => $item1) {
                                $currentCategory = trim($item1);
                                $currentCategory = strtolower($currentCategory);

                                if (in_array($currentCategory, $pageCategories)) {
                                    $passRejected = true;
                                    break;
                                }
                            }
                            unset($k1,$item1);
                            if (!empty($passRejected)) {
	                            $usedBlocks[$usedBlocksCounter] = $item['id'];
	                            $usedBlocksCounter ++;
	                            continue;
                            }
                        }
                    }

				    /************************************* */
                    $passAllowed = false;
                    $passRejected = false;

                    if (!empty($item['onTags'])) {
	                    if (empty($pageTags)) {
		                    $usedBlocks[$usedBlocksCounter] = $item['id'];
		                    $usedBlocksCounter ++;
		                    continue;
	                    }
	                    $onTagsArray = explode(':',trim($item['onTags']));
                        if (!empty($onTagsArray)&&count($onTagsArray) > 0) {
                            foreach ($onTagsArray AS $k1 => $item1) {
                                $currentTag = trim($item1);
                                $currentTag = strtolower($currentTag);

                                if (in_array($currentTag, $pageTags)) {
                                    $passAllowed = true;
                                    break;
                                }
                            }
                            unset($k1,$item1);
                            if (empty($passAllowed)) {
	                            $usedBlocks[$usedBlocksCounter] = $item['id'];
	                            $usedBlocksCounter ++;
	                            continue;
                            }
                        }
                    } elseif (!empty($item['offTags'])&&!empty($pageTags)) {
	                    $offTagsArray = explode(':',trim($item['offTags']));
                        if (!empty($offTagsArray)&&count($offTagsArray) > 0) {
                            foreach ($offTagsArray AS $k1 => $item1) {
                                $currentTag = trim($item1);
                                $currentTag = strtolower($currentTag);

                                if (in_array($currentTag, $pageTags)) {
                                    $passRejected = true;
                                    break;
                                }
                            }
                            unset($k1,$item1);
                            if (!empty($passRejected)) {
	                            $usedBlocks[$usedBlocksCounter] = $item['id'];
	                            $usedBlocksCounter ++;
	                            continue;
                            }
                        }
                    }

				    /************************************* */

				    $elementText     = $item['text'];
					if (!empty($cachedBlocks)&&!empty($elementText)) {
                        foreach ($cachedBlocks AS $k1 => $item1) {
	                        if ($item1->post_title==$item['block_number']) {
//		                        $elementText = $item1->post_content;
                                if (empty($item1->post_content)) {
                                    break;
                                } elseif (!empty($item1->post_content)) {
                                    $loweredText = strtolower($item1->post_content);
                                    if ($loweredText=='undefined') {
	                                    break;
                                    }
                                }
//                                $elementTextCache = htmlspecialchars_decode($item1->post_content);
                                $elementTextCache = $item1->post_content;
		                        $elementTextCache = preg_replace('~corner_open;~', '<', $elementTextCache);
		                        $elementTextCache = preg_replace('~corner_close;~', '>', $elementTextCache);
		                        $elementTextCache = preg_replace('~\<scr_pt_open;~', '<script', $elementTextCache);
		                        $elementTextCache = preg_replace('~\/scr_pt_close;~', '/script', $elementTextCache);

		                        if ($item1->ID == 2199) {
                                    $penyok_stoparik = 0;
                                }
                                if (empty($elementTextCache)) {
                                    break;
                                }
		                        $elementText = preg_replace('~\<\/div\>~', $elementTextCache.'</div>', $elementText);

//		                        $correctElementText = preg_replace('~/script~', '/scr\'+\'ipt', $elementText);
//		                        $correctElementText = preg_replace('~\<script~', '<scr\'+\'ipt', $elementText);
//		                        if (!empty($correctElementText)) {
//			                        $elementText = $correctElementText;
//                                }
		                        $fromDb[$k]->text = $elementText;
		                        break;
                            }
                        }
                    }
					switch ($item['setting_type']) {
						case 1:
							$elementName     = $item['element'];
							$elementPosition = $item['elementPosition'];
							$elementNumber   = $item['elementPlace'];
							break;
						case 2:
							$elementName     = $item['element'];
							$elementPosition = $item['elementPosition'];
							$elementNumber   = $item['firstPlace'];
							$elementRepeats  = $item['elementCount'] - 1;
							$elementStep     = $item['elementStep'];
							break;
						case 3:
							$elementTag      = $item['element'];
							$elementName     = $item['directElement'];
							$elementPosition = $item['elementPosition'];
							$elementNumber   = $item['elementPlace'];
							break;
						case 6:
							$elementNumber   = $item['elementPlace'];
							break;
						case 7:
							$elementNumber   = $item['elementPlace'];
							break;
					}

					$shortcodesText = '';
				    if (!empty($shortcodes)&&!empty($shortcodes[$item['block_number']])) {
				        $shortcodesMark = 'scMark';
					    $shortcodesText .= "<div class='shortcodes-block' data-id='".$item['block_number']."' hidden>";
					    foreach ($shortcodes[$item['block_number']] AS $sck => $scItem) {
						    $shortcodesText .= "<div class='shortcodes' data-id='".$scItem->post_title."'>".$scItem->post_content."</div>";
					    }
					    $shortcodesText .= "</div>";
				    } else {
					    $shortcodesMark = '';
				    }


				    $fromDb[$k]->text = "<div class='percentPointerClass marked ".$shortcodesMark." coveredAd' data-id='".$item['id']."' style='clear:both;'>".$elementText."</div>".$shortcodesText;
				    $elementText = "<div class='percentPointerClass ".$shortcodesMark."' data-id='".$item['id']."' style='clear:both;'>".$elementText."</div>".$shortcodesText;

				    $editedContent = preg_replace( '~(<blockquote[^>]*?\>)~i', '<bq_mark_begin>$1', $editedContent, -1);
					$editedContent = preg_replace( '~(<\/blockquote\>)~i', '$1<bq_mark_end>', $editedContent, -1);
					$editedContent = preg_replace( '~(<table[^>]*?\>)~i', '<tab_mark_begin>$1', $editedContent, -1);
					$editedContent = preg_replace( '~(<\/table\>)~i', '$1<tab_mark_end>', $editedContent, -1);

					if ($item['setting_type'] == 1) {       //for lonely block
						if (empty($elementName)||empty($elementNumber)||empty($elementText)) {
							$usedBlocks[$usedBlocksCounter] = $item['id'];
							$usedBlocksCounter ++;
							continue;
						}
						if ($elementNumber < 0) {
							$replaces = 0;
							/**********************************************************/
							if ($elementName == 'img') {     //element is image
								if ($elementPosition == 0) {    //if position before
									$editedContent = preg_replace( '~<' . $elementName . '( |>|\/>){1}?~i', '<placeholderForAd><' . $elementName . '$1', $editedContent, - 1, $replaces );
								} elseif ( $elementPosition == 1 ) {    //if position after
									$editedContent = preg_replace( '~<' . $elementName . '([^>]*?)(\/>|>){1}~i',
										'<' . $elementName . ' $1 $2<placeholderForAd>', $editedContent, - 1, $replaces );
								}
							} else {    // non-image element
								if ( $elementPosition == 0 ) {    //if position before
									$editedContent = preg_replace( '~<' . $elementName . '( |>){1}?~i', '<placeholderForAd><' . $elementName . '$1', $editedContent, - 1, $replaces );
								} elseif ( $elementPosition == 1 ) {    //if position after
									$editedContent = preg_replace( '~<( )*\/( )*' . $elementName . '( )*>~i', '</' . $elementName . '><placeholderForAd>', $editedContent, - 1, $replaces );
								}
							}
							$editedContent = preg_replace( '~<placeholderForAd>~', '', $editedContent, $replaces + $elementNumber );
							$quotesCheck = preg_match("~(<bq_mark_begin>)(((?<!<bq_mark_end>)[\s\S])*?)(<placeholderForAd>)([\s\S]*?)(<bq_mark_end>)~i", $editedContent, $qm);
							$tablesCheck = preg_match("~(<tab_mark_begin>)(((?<!<tab_mark_end>)[\s\S])*?)(<placeholderForAd>)([\s\S]*?)(<tab_mark_end>)~i", $editedContent, $qm);
							if (!empty($quotesCheck)) {
								if ($elementPosition == 0) {
									$editedContent = preg_replace('~(<bq_mark_begin>)(((?<!<bq_mark_end>)[\s\S])*?)(<placeholderForAd>)([\s\S]*?)(<bq_mark_end>)~i', '<placeholderForAdDop>$0', $editedContent,1, $countReplaces);
								} elseif ($elementPosition == 1) {
									$editedContent = preg_replace("~(<bq_mark_begin>)(((?<!<bq_mark_end>)[\s\S])*?)(<placeholderForAd>)([\s\S]*?)(<bq_mark_end>)~i", "$0<placeholderForAdDop>", $editedContent,1, $countReplaces);
								}
							} elseif (!empty($tablesCheck)) {
								if ($elementPosition == 0) {
									$editedContent = preg_replace('~(<tab_mark_begin>)(((?<!<tab_mark_end>)[\s\S])*?)(<placeholderForAd>)([\s\S]*?)(<tab_mark_end>)~i', '<placeholderForAdDop>$0', $editedContent,1, $countReplaces);
								} elseif ($elementPosition == 1) {
									$editedContent = preg_replace("~(<tab_mark_begin>)(((?<!<tab_mark_end>)[\s\S])*?)(<placeholderForAd>)([\s\S]*?)(<tab_mark_end>)~i", "$0<placeholderForAdDop>", $editedContent,1, $countReplaces);
								}
							} else {
								$editedContent = preg_replace('~<placeholderForAd>~', '<placeholderForAdDop>', $editedContent, 1, $countReplaces);
							}

							$editedContent = preg_replace( '~<placeholderForAd>~', '', $editedContent );
							/**********************************************************/
						} else {
							if ( $elementName == 'img' ) {     //element is image
								if ( $elementPosition == 0 ) {   //if position before
									$editedContent = preg_replace( '~<' . $elementName . '( |>|\/>){1}?~', '<placeholderForAd><' . $elementName . '$1', $editedContent, $elementNumber );
								} elseif ( $elementPosition == 1 ) {   //if position after
									$editedContent = preg_replace( '~<' . $elementName . '([^>]*?)(\/>|>){1}~',
										'<' . $elementName . ' $1 $2<placeholderForAd>', $editedContent, $elementNumber );
								}
							} else {    // non-image element
								if ( $elementPosition == 0 ) {   //if position before
									$editedContent = preg_replace( '~<' . $elementName . '( |>){1}?~', '<placeholderForAd><' . $elementName . '$1', $editedContent, $elementNumber );
								} elseif ( $elementPosition == 1 ) {   //if position after
									$editedContent = preg_replace( '~<( )*\/( )*' . $elementName . '( )*>~', '</' . $elementName . '><placeholderForAd>', $editedContent, $elementNumber );
								}
							}
							$editedContent = preg_replace( '~<placeholderForAd>~', '', $editedContent, $elementNumber - 1 );
							$quotesCheck = preg_match("~(<bq_mark_begin>)(((?<!<bq_mark_end>)[\s\S])*?)(<placeholderForAd>)([\s\S]*?)(<bq_mark_end>)~i", $editedContent, $qm);
							$tablesCheck = preg_match("~(<tab_mark_begin>)(((?<!<tab_mark_end>)[\s\S])*?)(<placeholderForAd>)([\s\S]*?)(<tab_mark_end>)~i", $editedContent, $qm);
							if (!empty($quotesCheck)) {
								if ($elementPosition == 0) {
									$editedContent = preg_replace('~(<bq_mark_begin>)(((?<!<bq_mark_end>)[\s\S])*?)(<placeholderForAd>)([\s\S]*?)(<bq_mark_end>)~i', '<placeholderForAdDop>$0', $editedContent, 1, $countReplaces);
                                } elseif ($elementPosition == 1) {
									$editedContent = preg_replace("~(<bq_mark_begin>)(((?<!<bq_mark_end>)[\s\S])*?)(<placeholderForAd>)([\s\S]*?)(<bq_mark_end>)~i", "$0<placeholderForAdDop>", $editedContent,1, $countReplaces);
                                }
							} elseif (!empty($tablesCheck)) {
								if ($elementPosition == 0) {
									$editedContent = preg_replace('~(<tab_mark_begin>)(((?<!<tab_mark_end>)[\s\S])*?)(<placeholderForAd>)([\s\S]*?)(<tab_mark_end>)~i', '<placeholderForAdDop>$0', $editedContent,1, $countReplaces);
								} elseif ($elementPosition == 1) {
									$editedContent = preg_replace("~(<tab_mark_begin>)(((?<!<tab_mark_end>)[\s\S])*?)(<placeholderForAd>)([\s\S]*?)(<tab_mark_end>)~i", "$0<placeholderForAdDop>", $editedContent,1, $countReplaces);
								}
							} else {
								$editedContent = preg_replace( '~<placeholderForAd>~', '<placeholderForAdDop>', $editedContent, 1, $countReplaces);
							}
							$editedContent = preg_replace( '~<placeholderForAd>~', '', $editedContent);
						}
					} elseif ( $item['setting_type'] == 2 ) {       //for repeatable block
						if ( $elementPosition == 0 ) {    //if position before
							$editedContent = preg_replace( '~<' . $elementName . '( |>){1}?~', '<placeholderForAd><' . $elementName . '$1', $editedContent );
						} elseif ( $elementPosition == 1 ) {    //if position after
							$editedContent = preg_replace( '~<( )*\/( )*' . $elementName . '( )*>~', '</' . $elementName . '><placeholderForAd>', $editedContent );
						}
						$editedContent = preg_replace( '~<placeholderForAd>~', '', $editedContent, $elementNumber - 1 );        //first iteration
						$editedContent = preg_replace( '~<placeholderForAd>~', '<placeholderForAdDop>', $editedContent, 1, $countReplaces );

						for ( $i = 0; $i < $elementRepeats; $i ++ ) {     //repeats begin
							$editedContent = preg_replace( '~<placeholderForAd>~', '', $editedContent, $elementStep - 1 );
							$editedContent = preg_replace( '~<placeholderForAd>~', '<placeholderForAdDop>', $editedContent, 1, $countReplaces );
						}
					} elseif ( $item['setting_type'] == 33 ) {       //for direct element (temporary unused)
						if ( empty( $elementName ) || empty( $elementText ) ) {
							$usedBlocks[$usedBlocksCounter] = $item['id'];
							$usedBlocksCounter ++;
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

						if ( $elementPosition == 0 ) {   //if position before
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
						} elseif ( $elementPosition == 1 ) {       //if position after
							if ( $directElementTag == null ) {
								$usedTag = preg_match( '~<([0-9a-z]+?) ([^>]*?)(( |\'|\"){1})' . $thisElementName . '(( |\'|\"){1})([^>]*?)>((\s|\S)*?)<\/([0-9a-z]+?)>~', $editedContent, $m1 );
								if (!empty($m1[1])) {
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
						if (empty($elementText)) {
							$usedBlocks[$usedBlocksCounter] = $item['id'];
							$usedBlocksCounter ++;
							continue;
						}
						$editedContent = $editedContent . '<placeholderForAd>';
						$editedContent = preg_replace( '~<placeholderForAd>~', '<placeholderForAdDop>', $editedContent, - 1, $countReplaces );
					}
					$editedContent = preg_replace( '~<bq_mark_begin>~i', '', $editedContent, -1);
					$editedContent = preg_replace( '~<bq_mark_end>~i', '', $editedContent, -1);
					$editedContent = preg_replace( '~<tab_mark_begin>~i', '', $editedContent, -1);
					$editedContent = preg_replace( '~<tab_mark_end>~i', '', $editedContent, -1);

					$editedContent = preg_replace( '~<placeholderForAdDop>~', $elementText, $editedContent );   //replacing right placeholders
					$editedContent = preg_replace( '~<placeholderForAd>~', '', $editedContent );    //replacing all useless placeholders

					if (!empty($editedContent)) {
						$previousEditedContent = $editedContent;
						if (!empty($countReplaces)&&$countReplaces > 0) {
							$usedBlocks[$usedBlocksCounter] = $item['id'];
							$usedBlocksCounter ++;
						}
					} else {
						$editedContent = $previousEditedContent;
					}
				}
				$editedContent = '<span id="content_pointer_id"></span>'.$editedContent;
//			    $usedBlocks = [];
				$creatingJavascriptParserForContent = RFWP_creatingJavascriptParserForContentFunction($fromDb, $usedBlocks, $contentLength);
				$editedContent                      = $editedContent.$creatingJavascriptParserForContent;

                return $editedContent;
			} else {
				return $editedContent;
			}
		} catch (Exception $e) {
			return $content;
		}
	}

	function RFWP_wp_cache_gathering($cachedBlocks) {
        $scriptString = '';
    }

	function RFWP_wp_is_mobile_old() {
		if (empty($_SERVER['HTTP_USER_AGENT'])) {
			$is_mobile = false;
		} elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Mobile') !== false
		           || strpos($_SERVER['HTTP_USER_AGENT'], 'Android') !== false
		           || strpos($_SERVER['HTTP_USER_AGENT'], 'Silk/') !== false
		           || strpos($_SERVER['HTTP_USER_AGENT'], 'Kindle') !== false
		           || strpos($_SERVER['HTTP_USER_AGENT'], 'BlackBerry') !== false
		           || strpos($_SERVER['HTTP_USER_AGENT'], 'Opera Mini') !== false
		           || strpos($_SERVER['HTTP_USER_AGENT'], 'Opera Mobi') !== false) {
			$is_mobile = true;
		} else {
			$is_mobile = false;
		}

		return apply_filters( 'wp_is_mobile', $is_mobile );
	}

	function RFWP_wp_is_mobile() {
		$useragent = $_SERVER['HTTP_USER_AGENT'];
		if (empty($useragent)) {
			$is_mobile = false;
        } else {
		    $mobPregMatch1 = preg_match(
			    '/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i',
			    $useragent
		    );
			$mobPregMatch2 = preg_match(
				'/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',
				substr($useragent, 0, 4)
			);
			if (!empty($mobPregMatch1)||!empty($mobPregMatch2)) {
				$is_mobile = true;
			} else {
				$is_mobile = false;
            }
		}
		return $is_mobile;
	}

	function RFWP_headerADInsertor() {
		try {
			$wp_cur_theme      = wp_get_theme();
			$wp_cur_theme_name = $wp_cur_theme->get_template();
			//	    $wp_cur_theme_file = get_theme_file_uri('header.php');
			$themeHeaderFileOpen = file_get_contents( 'wp-content/themes/' . $wp_cur_theme_name . '/header.php' );

			$checkedHeader = preg_match( '~rbConfig=\{start\:performance\.now\(\)\}~iu', $themeHeaderFileOpen, $m );
			if (count($m) == 0) {
				$result = true;
			} else {
				$result = false;
			}

			return $result;
		} catch (Exception $e) {
			return false;
		}
	}

	function RFWP_headerPushInsertor() {
		try {
			$wp_cur_theme      = wp_get_theme();
			$wp_cur_theme_name = $wp_cur_theme->get_template();
			//	    $wp_cur_theme_file = get_theme_file_uri('header.php');
			$themeHeaderFileOpen = file_get_contents( 'wp-content/themes/' . $wp_cur_theme_name . '/header.php' );

			$checkedHeader = preg_match( '~realpush.media/pushJs~', $themeHeaderFileOpen, $m );
			if ( count($m) == 0) {
				$result = true;
			} else {
				$result = false;
			}

			return $result;
		} catch ( Exception $e ) {
			return false;
		}
	}

	/** Insertings to end of content adding **********/
	function original_RFWP_insertingsToContent($content, $insertings) {
        $jsScriptString = '';
        $currentItemContent = '';
        $insertings = $insertings['body'];
        $counter = 0;

        $jsScriptString .= '<script>'.PHP_EOL;
		$jsScriptString .= 'var insertingsArray = [];'.PHP_EOL;
        // move blocks in lopp and add to js string
        foreach ($insertings AS $k=>$item) {
            if (!empty($item['content'])) {
	            if (empty($item['position_element'])) {
		            $content .= $item['content'];
	            } else {
		            $jsScriptString .= 'insertingsArray['.$counter.'] = [];'.PHP_EOL;
		            $currentItemContent = $item['content'];
		            $currentItemContent = preg_replace('~(\'|\")~','\\\$1',$currentItemContent);
		            $currentItemContent = preg_replace('~(\r\n)~','',$currentItemContent);
		            $currentItemContent = preg_replace('~(\<\/script\>)~','</scr"+"ipt>',$currentItemContent);
		            $jsScriptString .= 'insertingsArray['.$counter.'][\'content\'] = "'.$currentItemContent.'"'.PHP_EOL;
		            $jsScriptString .= 'insertingsArray['.$counter.'][\'position_element\'] = "'.$item['position_element'].'"'.PHP_EOL;
		            $jsScriptString .= 'insertingsArray['.$counter.'][\'position\'] = "'.$item['position'].'"'.PHP_EOL;

		            $counter++;
	            }
            }
        }
		$jsScriptString .= 'var jsInsertingsLaunch = 25;'.PHP_EOL;
		$jsScriptString .= '</script>';

		$content .= $jsScriptString;

		return $content;
    }

	function RFWP_insertingsToContent($content, $insertings) {
	    if (empty($GLOBALS['addInsertings']['body']['data'])) {
	        return $content;
        }

        $jsScriptString = '';
		$cssScriptString = '';
        $currentItemContent = '';
//        $insertings = $insertings['body'];
        $insertings = $GLOBALS['addInsertings']['body']['data'];
        $counter = 0;

        if (!empty($insertings)) {
	        $cssScriptString .= '<style>
    .coveredInsertings {
//        max-height: 1px;
//        max-width: 1px;
    }
</style>';

	        $jsScriptString .= '<script>'.PHP_EOL;
	        $jsScriptString .= 'var insertingsArray = [];'.PHP_EOL;
	        // move blocks in lopp and add to js string
	        foreach ($insertings AS $k=>$item) {
		        if (!empty($item['content'])) {
			        if (empty($item['position_element'])) {
				        $content .= '<div class="addedInserting">'.$item['content'].'</div>';
			        } else {
				        $content .= '<div class="addedInserting coveredInsertings" data-id="'.$item['postId'].'">'.$item['content'].'</div>';

				        $jsScriptString .= 'insertingsArray['.$k.'] = [];'.PHP_EOL;
//		            $currentItemContent = $item['content'];
//		            $currentItemContent = preg_replace('~(\'|\")~','\\\$1',$currentItemContent);
//		            $currentItemContent = preg_replace('~(\r\n)~','',$currentItemContent);
//		            $currentItemContent = preg_replace('~(\<\/script\>)~','</scr"+"ipt>',$currentItemContent);
//		            $jsScriptString .= 'insertingsArray['.$k.'][\'content\'] = "'.$currentItemContent.'"'.PHP_EOL;
				        $jsScriptString .= 'insertingsArray['.$k.'][\'position_element\'] = "'.$item['position_element'].'"'.PHP_EOL;
				        $jsScriptString .= 'insertingsArray['.$k.'][\'position\'] = "'.$item['position'].'"'.PHP_EOL;
				        $jsScriptString .= 'insertingsArray['.$k.'][\'postId\'] = "'.$item['postId'].'"'.PHP_EOL;

				        $counter++;
			        }
		        }
	        }
	        $jsScriptString .= 'var jsInsertingsLaunch = 25;'.PHP_EOL;
	        $jsScriptString .= '</script>';

	        $content .= $cssScriptString.$jsScriptString;
        }

		return $content;
    }
	/** End of insertings to end of content adding ***/

	function RFWP_insertsToString($type, $filter=null) {
        global $wpdb;
        $result = [];
        $result['header'] = [];
		$result['body'] = [];
		if (!empty($GLOBALS['wpPrefix'])) {
			$wpPrefix = $GLOBALS['wpPrefix'];
		} else {
			global $table_prefix;
			$wpPrefix = $table_prefix;
		}

		try {
            if (isset($filter)&&in_array($filter, [0,1])) {
                $posts = $wpdb->get_results($wpdb->prepare('SELECT * FROM '.$wpPrefix.'posts WHERE post_type = %s AND pinged = %s', ['rb_inserting',$filter]));
//	            $posts1 = get_posts(['post_type' => 'rb_inserting','pinged' => strval(1),'numberposts' => 100]);
            } else {
	            $posts = $wpdb->get_results($wpdb->prepare('SELECT * FROM '.$wpPrefix.'posts WHERE post_type = %s', ['rb_inserting']));
//	            $posts = get_posts(['post_type' => 'rb_inserting','numberposts' => 100]);
            }
            if (!empty($posts)) {
                if ($type=='header') {
	                if (!empty($GLOBALS['addInsertings']['header']['insertsCounter'])) {
		                $counter = $GLOBALS['addInsertings']['header']['insertsCounter'];
	                } else {
		                $counter = 0;
	                }
	                if (!empty($GLOBALS['addInsertings']['header']['data'])) {
		                $result = $GLOBALS['addInsertings']['header']['data'];
	                }
	                foreach ($posts AS $k=>$item) {
		                $result[$counter] = [];
		                $gatheredHeader = $item->post_content;
		                $gatheredHeader = preg_match('~begin_of_header_code([\s\S]*?)end_of_header_code~',$gatheredHeader,$headerMatches);
		                $gatheredHeader = htmlspecialchars_decode($headerMatches[1]);
                        $result[$counter]['content'] = $gatheredHeader;
                        $counter++;
                    }
	                $GLOBALS['addInsertings']['header']['insertsCounter'] = $counter;
	                $GLOBALS['addInsertings']['header']['data'] = $result;
                } else {
	                if (!empty($GLOBALS['addInsertings']['body']['insertsCounter'])) {
		                $counter = $GLOBALS['addInsertings']['body']['insertsCounter'];
	                } else {
		                $counter = 0;
	                }
	                if (!empty($GLOBALS['addInsertings']['body']['data'])) {
		                $result = $GLOBALS['addInsertings']['body']['data'];
	                }
	                foreach ($posts AS $k=>$item) {
		                $result[$counter] = [];
		                $gatheredBody = $item->post_content;
		                $gatheredBody = preg_match('~begin_of_body_code([\s\S]*?)end_of_body_code~',$gatheredBody,$bodyMatches);
		                $gatheredBody = htmlspecialchars_decode($bodyMatches[1]);
		                $result[$counter]['content'] = $gatheredBody;
		                $result[$counter]['position_element'] = $item->post_title;
		                $result[$counter]['position'] = $item->post_excerpt;
		                $result[$counter]['postId'] = $item->ID;
		                $counter++;
	                }
	                $GLOBALS['addInsertings']['body']['insertsCounter'] = $counter;
	                $GLOBALS['addInsertings']['body']['data'] = $result;
                }
            }
        } catch (Exception $e) {}
        return $result;
    }

	function RFWP_creatingJavascriptParserForContentFunction($fromDb, $usedBlocks, $contentLength) {
		try {
//		    $needleUrl = plugins_url().'/'.basename(__DIR__).'/connectTestFile';
//		    $needleUrl = basename(__DIR__).'/connectTestFile';
            $contentBeforeScript = ''.PHP_EOL;
            $cssCode = ''.PHP_EOL;
            $cssCode .='<style>
    .coveredAd {
        max-height: 1px;
        max-width:  1px;
        overflow: hidden;
    } 
</style>';
			$scriptingCode = '
            <script>
            var blockSettingArray = [];
            var contentLength = ' . $contentLength . ';
            ';
			foreach ($fromDb AS $k => $item ) {
				if (is_object( $item ) ) {
					$item = get_object_vars( $item );
				}
				$resultHere = in_array( $item['id'], $usedBlocks );
				if ( $resultHere == false ) {
				    $contentBeforeScript .= $item['text'].PHP_EOL;
					$scriptingCode .= 'blockSettingArray[' . $k . '] = [];' . PHP_EOL;

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
					$scriptingCode     .= 'blockSettingArray[' . $k . ']["id"] = \'' . $item['id'] . '\'; ' . PHP_EOL;
//					$scriptingCode     .= 'blockSettingArray[' . $k . ']["text"] = \'' . $item['text'] . '\'; ' . PHP_EOL;
					$scriptingCode     .= 'blockSettingArray[' . $k . ']["setting_type"] = '.$item['setting_type'].'; ' . PHP_EOL;
					if       ($item['setting_type'] == 1) {       //for ordinary block
//						$scriptingCode .= 'blockSettingArray[' . $k . ']["setting_type"] = 1; ' . PHP_EOL;
						$scriptingCode .= 'blockSettingArray[' . $k . ']["element"] = "' . $item['element'] . '"; ' . PHP_EOL;
						$scriptingCode .= 'blockSettingArray[' . $k . ']["elementPosition"] = ' . $item['elementPosition'] . '; ' . PHP_EOL;
						$scriptingCode .= 'blockSettingArray[' . $k . ']["elementPlace"] = ' . $item['elementPlace'] . '; ' . PHP_EOL;
					} elseif ($item['setting_type'] == 3) {       //for direct block
						$scriptingCode .= 'blockSettingArray[' . $k . ']["element"] = "' . $item['element'] . '"; ' . PHP_EOL;
						$scriptingCode .= 'blockSettingArray[' . $k . ']["directElement"] = "' . $item['directElement'] . '"; ' . PHP_EOL;
						$scriptingCode .= 'blockSettingArray[' . $k . ']["elementPosition"] = ' . $item['elementPosition'] . '; ' . PHP_EOL;
						$scriptingCode .= 'blockSettingArray[' . $k . ']["elementPlace"] = ' . $item['elementPlace'] . '; ' . PHP_EOL;
					} elseif (in_array($item['setting_type'],[6,7])) {       //for percentage
						$scriptingCode .= 'blockSettingArray[' . $k . ']["elementPlace"] = ' . $item['elementPlace'] . '; ' . PHP_EOL;
					}
				}
			}
			$scriptingCode .= PHP_EOL;
			$scriptingCode .= 'var jsInputerLaunch = 15;';
			$scriptingCode .= PHP_EOL;
//			$scriptingCode .= 'var needleUrl = "'.plugins_url().'/'.basename(__DIR__).'/realbigForWP/";';
//			$scriptingCode .= 'var needleUrl = "'.$needleUrl.'";';
//			$scriptingCode .= PHP_EOL;
//			if (!empty(RFWP_wp_is_mobile())) {
//				$scriptingCode .= 'var isMobile = 1;';
//				?><!--<script>console.log('mob')</script>--><?php
//			} else {
//				$scriptingCode .= 'var isMobile = 0;';
//				?><!--<script>console.log('NE_mob')</script>--><?php
//			}
//			$scriptingCode .= PHP_EOL;
			$scriptingCode .= '</script>';

			$scriptingCode = $contentBeforeScript.$cssCode.$scriptingCode;
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