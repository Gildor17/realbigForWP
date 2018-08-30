<?php

include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
include_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

/**
 * Created by PhpStorm.
 * User: user
 * Date: 2018-07-03
 * Time: 17:07
 */

try
{

    function addIcons ($fromDb, $content, $contentType, $usedBlocks=null)
    {
    	try
    	{
    		$editedContent = $content;
    		$previousEditedContent = $editedContent;
    		$usedBlocksCounter = 0;
    		$usedBlocks = [];
    		$objArray = [];

//    		if ($contentType!='title'&&!empty($usedBlocks))
//    		{
//    		    for ($i = 0; $i < count($fromDb); $i++)
//    		    {
//    		        $resultHere = in_array($fromDb[$i]['id'], $usedBlocks);
//    		        if ($resultHere==true)
//    		        {
//    			        unset($fromDb[$i]);
//                    }
//                }
//            }

    		if ( ! empty( $fromDb ) )
    		{
    		    $contentLength = strlen(strip_tags($content));
    			foreach ( $fromDb AS $k => $item )
    			{
    				if (is_object($item))
    				{
    					$item = get_object_vars( $item );
    				}
				    if (!empty($item['minHeaders'])&&$item['minHeaders'] > 0)
				    {
                        $headersMatchesResult = preg_match_all('~<(h1|h2|h3|h4|h5|h6)~', $content, $headM);
                        $headersMatchesResult = count($headM[0]);
                        $headersMatchesResult += 1;
                    }
    				if (!empty($item['minHeaders'])&&$item['minHeaders'] > 0&&$item['minHeaders'] > $headersMatchesResult)
    				{
                        continue;
                    }
                    elseif (!empty($item['minSymbols'])&&$item['minSymbols'] > 0&&$item['minSymbols'] > $contentLength)
				    {
					    continue;
				    }
    				if ($item['setting_type'] == 1)       //for lonely block
    				{
    					$elementName     = $item['element'];
    					$elementPosition = $item['elementPosition'];
    					$elementNumber   = $item['elementPlace'];
    					$elementText     = $item['text'];
    				}
    				elseif ( $item['setting_type'] == 2 )  //for repeatable block
    				{
    					$elementName     = $item['element'];
    					$elementPosition = $item['elementPosition'];
    					$elementNumber   = $item['firstPlace'];
    					$elementRepeats  = $item['elementCount'] - 1;
    					$elementStep     = $item['elementStep'];
    					$elementText     = $item['text'];
    				}
    				elseif ( $item['setting_type'] == 3 )  //for direct block
    				{
    					$elementTag      = $item['element'];
    					$elementName     = $item['directElement'];
    					$elementPosition = $item['elementPosition'];
    					$elementText     = $item['text'];
    				}
    				elseif ( $item['setting_type'] == 4 )  //for end of content
    				{
    					$elementText     = $item['text'];
    				}

    				if ( $item['setting_type'] == 1 )   //for lonely block
    				{
    				    if ($elementNumber < 0) {
					        $replaces = 0;
    				/**********************************************************/
					        if ( $elementName == 'img' )     //element is image
					        {
						        if ( $elementPosition == 0 )    //if position before
						        {
							        $editedContent = preg_replace( '~<' . $elementName . '( |>|\/>){1}?~', '<placeholderForAd><' . $elementName . '$1', $editedContent, -1, $replaces);
						        }
						        elseif ( $elementPosition == 1 )    //if position after
						        {
							        $editedContent = preg_replace( '~<' . $elementName . '([^>]*?)(\/>|>){1}~',
								        '<' . $elementName . ' $1 $2<placeholderForAd>', $editedContent, -1, $replaces);
						        }
					        }
					        else    // non-image element
					        {
						        if ( $elementPosition == 0 )    //if position before
						        {
							        $editedContent = preg_replace( '~<' . $elementName . '( |>){1}?~', '<placeholderForAd><' . $elementName . '$1', $editedContent, -1, $replaces);
						        }
						        elseif ( $elementPosition == 1 )    //if position after
						        {
							        $editedContent = preg_replace( '~<( )*\/( )*' . $elementName . '( )*>~', '</' . $elementName . '><placeholderForAd>', $editedContent, -1, $replaces);
						        }
					        }
					        $editedContent = preg_replace( '~<placeholderForAd>~', '', $editedContent, $replaces + $elementNumber );
					        $editedContent = preg_replace( '~<placeholderForAd>~', '<placeholderForAdDop>', $editedContent, 1, $countReplaces );
					        $editedContent = preg_replace( '~<placeholderForAd>~', '', $editedContent);
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
//    					$editedContent = preg_replace( '~<placeholderForAd>~', '', $editedContent, $elementNumber - 1 );
//    					$editedContent = preg_replace( '~<placeholderForAd>~', '<placeholderForAdDop>', $editedContent, 1, $countReplaces );
    				}
    				elseif ( $item['setting_type'] == 2 )  //for repeatable block
    				{
    					if ( $elementPosition == 0 )    //if position before
    					{
    						$editedContent = preg_replace( '~<' . $elementName . '( |>){1}?~', '<placeholderForAd><' . $elementName . '$1', $editedContent );
    					}
    					elseif ( $elementPosition == 1 )    //if position after
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
    				}
    				elseif ( $item['setting_type'] == 3 )  //for direct element
    				{
    					$directElementTag = null;
    					$thisElementTag   = preg_match( '~[\.\#]{1}~', $elementName, $m );
    					$thisElementName  = preg_replace( '~([\.\#]{1})~', '', $elementName, 1 );
    					if ( $m[0] == '.' ) {
    						$thisElementType  = 'class';
    						$directElementTag = $elementTag;
    					}
    					elseif ( $m[0] == '#' )
    					{
    						$thisElementType = 'id';
    					}

    					if ( $elementPosition == 0 )    //if position before
    					{
    						if ( $directElementTag == null )
    						{
    							$usedTag          = preg_match( '~<([0-9a-z]+?) ([^>]*?)(( |\'|\"){1})' . $thisElementName . '(( |\'|\"){1})([^>]*?)>~', $editedContent, $m1 );
    							if (!empty($m1[1]))
    							{
    								$directElementTag = $m1[1];
    							}
    						}
    						if ($directElementTag)
    						{
    							$editedContent = preg_replace(
    								'~<' . $directElementTag . ' ([^>]*?)(( |\'|\"){1})' . $thisElementName . '(( |\'|\"){1})([^>]*?)>~',
    								'<placeholderForAd><' . $directElementTag . ' $1 $2' . $thisElementName . '$4 $6>', $editedContent, 1 );
    						}
    //					$editedContent = preg_replace(
    //						'~< ([0-9a-z]*?) ([^>]*?) '.$thisElementName.' ([^>]*?)>~',
    //						'<placeholderForAd><$1 $2 '.$thisElementName.' $3>', $editedContent);
    					}
    					elseif ( $elementPosition == 1 )    //if position after
    					{
    						if ( $directElementTag == null )
    						{
    							$usedTag          = preg_match( '~<([0-9a-z]+?) ([^>]*?)(( |\'|\"){1})' . $thisElementName . '(( |\'|\"){1})([^>]*?)>((\s|\S)*?)<\/([0-9a-z]+?)>~', $editedContent, $m1 );
    							if (!empty($m1[1]))
    							{
    								$directElementTag = $m1[1];
    							}
    						}
    						if ($directElementTag)
    						{
    							$editedContent = preg_replace(
    								'~<(' . $directElementTag . ') ([^>]*?)(( |\'|\"){1})' . $thisElementName . '(( |\'|\"){1})([^>]*?)>((\s|\S)*?)<\/' . $directElementTag . '>~',
    								'<$1 $2 $3' . $thisElementName . '$5 $7>$8</$1><placeholderForAd>', $editedContent, 1 );
    						}
    					}
    					$editedContent = preg_replace( '~<placeholderForAd>~', '<placeholderForAdDop>', $editedContent, -1, $countReplaces);
    				}
    				elseif ( $item['setting_type'] == 4 )  //for end of content
    				{
    					$editedContent = $editedContent.'<placeholderForAd>';
    					$editedContent = preg_replace( '~<placeholderForAd>~', '<placeholderForAdDop>', $editedContent, -1, $countReplaces);
    				}
    				$editedContent = preg_replace( '~<placeholderForAdDop>~', $elementText, $editedContent );   //replacing right placeholders
    				$editedContent = preg_replace( '~<placeholderForAd>~', '', $editedContent );    //replacing all useless placeholders

    				if (!empty($editedContent))
    				{
    					$previousEditedContent = $editedContent;
    					if (!empty($countReplaces)&&$countReplaces>0)
    					{
    						$usedBlocks[$usedBlocksCounter] = $item['id'];
    						$usedBlocksCounter++;
    					}
    				}
    				else
    				{
    					$editedContent = $previousEditedContent;
    				}
    			}
    			$editedContent = '<span id="content_pointer_id"></span>'.$editedContent;
//			    $usedBlocks = [];
    			$creatingJavascriptParserForContent = creatingJavascriptParserForContentFunction($fromDb, $usedBlocks, $contentLength);
    			$editedContent = $editedContent.$creatingJavascriptParserForContent;

    			if ($contentType=='title')
    			{
    				$objArray[0] = $editedContent;
    				$objArray[1] = $usedBlocks;
    				return $objArray;
                }
                else
                {
    				return $editedContent;
    			}
    		}
    		else
    		{
    		    return $editedContent;
    		}
    	}
    	catch (Exception $e)
    	{
    		return $content;
    	}
    }

    function headerADInsertor()
    {
    	try
    	{
    		$wp_cur_theme = wp_get_theme();
    		$wp_cur_theme_name = $wp_cur_theme->get_template();
    //	    $wp_cur_theme_file = get_theme_file_uri('header.php');
    		$themeHeaderFileOpen = file_get_contents('wp-content/themes/'.$wp_cur_theme_name.'/header.php');

    		$checkedHeader = preg_match('~rbConfig=\{start\:performance\.now\(\)\}\;~', $themeHeaderFileOpen, $m);
    		if (count($m) == 0)
    		{
    			$result = true;
    		}
    		else
    		{
    			$result = false;
    		}

    		return $result;
    	}
    	catch (Exception $e)
    	{
    		return false;
    	}
    }

	function creatingJavascriptParserForContentFunction($fromDb, $usedBlocks, $contentLength)
    {
        try {
//	        $scriptingCode = '
//
//<script>var newElement = document.createElement("div");
//newElement.style.cssText = "height: 20px; width: 100px; border: 1px solid black; background-color: yellow";
//var content_pointer = document.getElementById("content_pointer_id");
//var parent_with_content = content_pointer.parentElement;
//var h1_in_parent = parent_with_content.getElementsByTagName("h1");
//
//if (h1_in_parent.length==0)
//{
//    parent_with_content = parent_with_content.parentElement;
//    h1_in_parent = parent_with_content.getElementsByTagName("h1");
//}
//if (h1_in_parent.length==1)
//{
//    element_h1 = h1_in_parent[0];
//    element_h1.parentNode.insertBefore(newElement, element_h1.nextSibling);
//}
//
//// element_h1 = document.getElementsByTagName("h1");
//var newElement = document.createElement("div");
//newElement.style.cssText = "height: 20px; width: 100px; border: 1px solid black; background-color: yellow";
//// element_h1.parentNode.insertBefore(newElement, element_h1.nextSibling);
//
//var blockSettingArray = [];
//var counter = 0;
//
//';
//
//	        foreach ($fromDb AS $k => $item)
//	        {
//		        if ( is_object( $item ) ) {
//			        $item = get_object_vars( $item );
//		        }
//		        $resultHere = in_array($item['id'], $usedBlocks);
//		        if ($resultHere==false) {
//
//			        $scriptingCode .= 'blockSettingArray[' . $k . '] = [];' . PHP_EOL;
//
//			        if ( $item['setting_type'] == 1 )       //for lonely block
//			        {
////				$scriptingCode .= 'blockSettingArray['.$k.']["element"] = []'. PHP_EOL;
////				$scriptingCode .= 'blockSettingArray['.$k.']["elementPosition"] = []'. PHP_EOL;
////				$scriptingCode .= 'blockSettingArray['.$k.']["elementPlace"] = []'. PHP_EOL;
////				$scriptingCode .= 'blockSettingArray['.$k.']["text"] = []'. PHP_EOL;
//				        $scriptingCode .= 'blockSettingArray[' . $k . ']["setting_type"] = 1; ' . PHP_EOL;
//				        $scriptingCode .= 'blockSettingArray[' . $k . ']["element"] = "' . $item['element'] . '"; ' . PHP_EOL;
//				        $scriptingCode .= 'blockSettingArray[' . $k . ']["elementPosition"] = ' . $item['elementPosition'] . '; ' . PHP_EOL;
//				        $scriptingCode .= 'blockSettingArray[' . $k . ']["elementPlace"] = ' . $item['elementPlace'] . '; ' . PHP_EOL;
//				        $scriptingCode .= 'blockSettingArray[' . $k . ']["text"] = \'' . $item['text'] . '\'; ' . PHP_EOL;
//			        } elseif ( $item['setting_type'] == 3 )  //for direct block
//			        {
//				        $scriptingCode .= 'blockSettingArray[' . $k . ']["setting_type"] = 3; ' . PHP_EOL;
//				        $scriptingCode .= 'blockSettingArray[' . $k . ']["element"] = "' . $item['element'] . '"; ' . PHP_EOL;
//				        $scriptingCode .= 'blockSettingArray[' . $k . ']["directElement"] = "' . $item['directElement'] . '"; ' . PHP_EOL;
//				        $scriptingCode .= 'blockSettingArray[' . $k . ']["elementPosition"] = ' . $item['elementPosition'] . '; ' . PHP_EOL;
//				        $scriptingCode .= 'blockSettingArray[' . $k . ']["text"] = \'' . $item['text'] . '\'; ' . PHP_EOL;
//			        } elseif ( $item['setting_type'] == 4 )  //for end of content
//			        {
//				        $scriptingCode .= 'blockSettingArray[' . $k . ']["setting_type"] = 4; ' . PHP_EOL;
//				        $scriptingCode .= 'blockSettingArray[' . $k . ']["text"] = \'' . $item['text'] . '\'; ' . PHP_EOL;
//			        }
//		        }
//	        }
//	        $scriptingCode .= PHP_EOL;
//
//	        $scriptingCode .= '
//var currentElement;
//';
//
//	        foreach ($fromDb AS $k => $item)
//	        {
//		        if ( is_object( $item ) ) {
//			        $item = get_object_vars( $item );
//		        }
//		        $resultHere = in_array($item['id'], $usedBlocks);
//		        if ($resultHere==false) {
//
//			        $scriptingCode .= 'blockSettingArray[' . $k . '] = [];' . PHP_EOL;
//
//			        if ( $item['setting_type'] == 1 )       //for lonely block
//			        {
//			            $scriptingCode .= '
//currentElement = parent_with_content.getElementsByTagName(blockSettingArray['.$k.']["element"]);
//currentElement = currentElement[blockSettingArray['.$k.']["elementPlace"]-1];'.PHP_EOL;
//				        if ($item['elementPosition']==0)
//				        {
//                            $scriptingCode .= 'currentElement.parentNode.insertBefore(newElement, currentElement.nextSibling);'.PHP_EOL;
//				        }
//				        else
//				        {
//					        $scriptingCode .= 'currentElement.parentNode.insertBefore(newElement, currentElement);'.PHP_EOL;
//				        }
////				        $scriptingCode .= 'blockSettingArray[' . $k . ']["setting_type"] = 1; ' . PHP_EOL;
////				        $scriptingCode .= 'blockSettingArray[' . $k . ']["element"] = "' . $item['element'] . '"; ' . PHP_EOL;
////				        $scriptingCode .= 'blockSettingArray[' . $k . ']["elementPosition"] = ' . $item['elementPosition'] . '; ' . PHP_EOL;
////				        $scriptingCode .= 'blockSettingArray[' . $k . ']["elementPlace"] = ' . $item['elementPlace'] . '; ' . PHP_EOL;
////				        $scriptingCode .= 'blockSettingArray[' . $k . ']["text"] = \'' . $item['text'] . '\'; ' . PHP_EOL;
//			        }
//                    elseif ( $item['setting_type'] == 3 )  //for direct block
//			        {
//				        $elementType = substr($item['directElement'], 0, 1);
//				        $elementName = substr($item['directElement'], 1);
////				        var elementName = blockSettingArray[i]["directElement"].subString(1);
//
//                        if ($elementType=='#') {
//                            $scriptingCode .= 'currentElement = parent_with_content.getElementById('.$elementName.');';
//                        } elseif ($elementType=='.') {
//                            $scriptingCode .= 'currentElement = parent_with_content.getElementsByClassName('.$elementName.');';
//                            $scriptingCode .= 'break;';
//                        }
//
////				        $scriptingCode .= 'blockSettingArray[' . $k . ']["setting_type"] = 3; ' . PHP_EOL;
////				        $scriptingCode .= 'blockSettingArray[' . $k . ']["element"] = "' . $item['element'] . '"; ' . PHP_EOL;
////				        $scriptingCode .= 'blockSettingArray[' . $k . ']["directElement"] = "' . $item['directElement'] . '"; ' . PHP_EOL;
////				        $scriptingCode .= 'blockSettingArray[' . $k . ']["elementPosition"] = ' . $item['elementPosition'] . '; ' . PHP_EOL;
////				        $scriptingCode .= 'blockSettingArray[' . $k . ']["text"] = \'' . $item['text'] . '\'; ' . PHP_EOL;
//			        } elseif ( $item['setting_type'] == 4 )  //for end of content
//			        {
//				        $scriptingCode .= 'blockSettingArray[' . $k . ']["setting_type"] = 4; ' . PHP_EOL;
//				        $scriptingCode .= 'blockSettingArray[' . $k . ']["text"] = \'' . $item['text'] . '\'; ' . PHP_EOL;
//			        }
//		        }
//	        }
//	        $scriptingCode .= PHP_EOL;
//
//	        $scriptingCode .= '</script>';
//
//            $scriptingCode = '<script type="text/javascript">document.addEventListener(\'DOMContentLoaded\', function ()
//{
//    var newElement = document.createElement("div");
//    newElement.style.cssText = "height: 20px; width: 100px; border: 1px solid black; background-color: yellow";
//    var content_pointer = document.getElementById("content_pointer_id");
//    var parent_with_content = content_pointer.parentElement;
//    var h1_in_parent = parent_with_content.getElementsByTagName("h1");
//
//    if (h1_in_parent.length==0)
//    {
//        parent_with_content = parent_with_content.parentElement;
//        h1_in_parent = parent_with_content.getElementsByTagName("h1");
//    }
//    if (h1_in_parent.length==1) {
//        element_h1 = h1_in_parent[0];
//        element_h1.parentNode.insertBefore(newElement, element_h1.nextSibling);
//    }
//
//// element_h1 = document.getElementsByTagName("h1");
//    var newElement = document.createElement("div");
//    newElement.style.cssText = "height: 20px; width: 100px; border: 1px solid black; background-color: yellow";
//// element_h1.parentNode.insertBefore(newElement, element_h1.nextSibling);
//
//    var blockSettingArray = [];
//    var counter = 0;
//
//    var currentElement;
//
//    for(var i = 0; i < blockSettingArray.length; i++)
//    {
//        if (blockSettingArray[i]["setting_type"]==1)
//        {
//            currentElement = parent_with_content.getElementsByTagName(blockSettingArray[i]["element"]);
//            currentElement = currentElement[blockSettingArray[i]["elementPlace"]-1];
//            if (blockSettingArray[i]["elementPosition"]==0)
//            {
//                currentElement.parentNode.insertBefore(newElement, currentElement.nextSibling);
//            }
//            else
//            {
//                currentElement.parentNode.insertBefore(newElement, currentElement);
//            }
//        }
//        else if (blockSettingArray[i]["setting_type"]==3)
//        {
//            var elementType = blockSettingArray[i]["directElement"].charAt(0);
//            var elementName = blockSettingArray[i]["directElement"].subString(1);
//            if (elementType==\'#\')
//            {
//                currentElement = parent_with_content.getElementById(elementName);
//            }
//            else if (elementType==\'.\')
//            {
//                currentElement = parent_with_content.getElementsByClassName(elementName);
//                if (currentElement.length > 0)
//                {
//                    for (var i1 = 0; i1 < currentElement.length; i1++)
//                    {
//                        if (currentElement[i1].tagName.toLowerCase() == blockSettingArray[i]["element"].toLowerCase())
//                        {
//                            currentElement = currentElement[i1];
//                            break;
//                        }
//                    }
//
//                }
//            }
//            if (blockSettingArray[i]["elementPosition"]==0)
//            {
//                currentElement.parentNode.insertBefore(newElement, currentElement.nextSibling);
//            }
//            else
//            {
//                currentElement.parentNode.insertBefore(newElement, currentElement);
//            }
//        }
//        else if (blockSettingArray[i]["setting_type"]==4)
//        {
//            parent_with_content.parentNode.insertBefore(newElement, parent_with_content);
//        }
//    }
//});
//</script>';

//            $scriptingCode = '<script>document.addEventListener("DOMContentLoaded", function() {testFuncInTestFile()});</script>';
            
            $scriptingCode = '
            <script>
var blockSettingArray = [];
var contentLength = '.$contentLength.';
';
	        foreach ($fromDb AS $k => $item)
	        {
		        if ( is_object( $item ) ) {
			        $item = get_object_vars( $item );
		        }
		        $resultHere = in_array($item['id'], $usedBlocks);
		        if ($resultHere==false) {

			        $scriptingCode .= 'blockSettingArray[' . $k . '] = [];' . PHP_EOL;
			        if ( $item['setting_type'] == 1 )       //for lonely block
			        {
				        $scriptingCode .= 'blockSettingArray[' . $k . ']["setting_type"] = 1; ' . PHP_EOL;
				        $scriptingCode .= 'blockSettingArray[' . $k . ']["element"] = "' . $item['element'] . '"; ' . PHP_EOL;
				        $scriptingCode .= 'blockSettingArray[' . $k . ']["elementPosition"] = ' . $item['elementPosition'] . '; ' . PHP_EOL;
				        $scriptingCode .= 'blockSettingArray[' . $k . ']["elementPlace"] = ' . $item['elementPlace'] . '; ' . PHP_EOL;
				        $scriptingCode .= 'blockSettingArray[' . $k . ']["text"] = \'' . $item['text'] . '\'; ' . PHP_EOL;
				        if (!empty($item['minSymbols'])&&$item['minSymbols'] > 1)
				        {
					        $scriptingCode .= 'blockSettingArray[' . $k . ']["minSymbols"] = ' . $item['minSymbols'] . '; ' . PHP_EOL;
				        }
				        else
				        {
					        $scriptingCode .= 'blockSettingArray[' . $k . ']["minSymbols"] = 0;';
                        }
				        if (!empty($item['minHeaders'])&&$item['minHeaders'] > 1)
				        {
					        $scriptingCode .= 'blockSettingArray[' . $k . ']["minHeaders"] = ' . $item['minHeaders'] . '; ' . PHP_EOL;
				        }
				        else
				        {
					        $scriptingCode .= 'blockSettingArray[' . $k . ']["minHeaders"] = 0;';
                        }
			        }
			        elseif ( $item['setting_type'] == 3 )  //for direct block
			        {
				        $scriptingCode .= 'blockSettingArray[' . $k . ']["setting_type"] = 3; ' . PHP_EOL;
				        $scriptingCode .= 'blockSettingArray[' . $k . ']["element"] = "' . $item['element'] . '"; ' . PHP_EOL;
				        $scriptingCode .= 'blockSettingArray[' . $k . ']["directElement"] = "' . $item['directElement'] . '"; ' . PHP_EOL;
				        $scriptingCode .= 'blockSettingArray[' . $k . ']["elementPosition"] = ' . $item['elementPosition'] . '; ' . PHP_EOL;
				        $scriptingCode .= 'blockSettingArray[' . $k . ']["text"] = \'' . $item['text'] . '\'; ' . PHP_EOL;
				        if (!empty($item['minSymbols'])&&$item['minSymbols'] > 1)
				        {
					        $scriptingCode .= 'blockSettingArray[' . $k . ']["minSymbols"] = ' . $item['minSymbols'] . '; ' . PHP_EOL;
				        }
				        else
				        {
					        $scriptingCode .= 'blockSettingArray[' . $k . ']["minSymbols"] = 0;';
				        }
				        if (!empty($item['minHeaders'])&&$item['minHeaders'] > 1)
				        {
					        $scriptingCode .= 'blockSettingArray[' . $k . ']["minHeaders"] = ' . $item['minHeaders'] . '; ' . PHP_EOL;
				        }
				        else
				        {
					        $scriptingCode .= 'blockSettingArray[' . $k . ']["minHeaders"] = 0;';
				        }
			        }
			        elseif ( $item['setting_type'] == 4 )  //for end of content
			        {
				        $scriptingCode .= 'blockSettingArray[' . $k . ']["setting_type"] = 4; ' . PHP_EOL;
				        $scriptingCode .= 'blockSettingArray[' . $k . ']["text"] = \'' . $item['text'] . '\'; ' . PHP_EOL;
				        if (!empty($item['minSymbols'])&&$item['minSymbols'] > 1)
				        {
					        $scriptingCode .= 'blockSettingArray[' . $k . ']["minSymbols"] = ' . $item['minSymbols'] . '; ' . PHP_EOL;
				        }
				        else
				        {
					        $scriptingCode .= 'blockSettingArray[' . $k . ']["minSymbols"] = 0;';
				        }
				        if (!empty($item['minHeaders'])&&$item['minHeaders'] > 1)
				        {
					        $scriptingCode .= 'blockSettingArray[' . $k . ']["minHeaders"] = ' . $item['minHeaders'] . '; ' . PHP_EOL;
				        }
				        else
				        {
					        $scriptingCode .= 'blockSettingArray[' . $k . ']["minHeaders"] = 0;';
				        }
			        }
		        }
	        }
	        $scriptingCode .= PHP_EOL;
	        $scriptingCode .= 'testFuncInTestFile(blockSettingArray, contentLength)</script>';

	        return $scriptingCode;
        }
        catch (Exception $e)
        {
            return '';
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