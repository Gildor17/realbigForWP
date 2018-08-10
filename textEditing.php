<?php

include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
include_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

/**
 * Created by PhpStorm.
 * User: user
 * Date: 2018-07-03
 * Time: 17:07
 */

function addIcons ($fromDb, $content)
{
	$editedContent = $content;

	if (!empty($fromDb))
	{
		foreach ($fromDb AS $k => $item)
		{
			if (is_object($item))
			{
				$item = get_object_vars($item);
			}
			if ( $item['setting_type'] == 1 )       //for lonely block
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
				$elementName     = $item['directElement'];
				$elementPosition = $item['elementPosition'];
				$elementText     = $item['text'];
			}


			if ( $item['setting_type'] == 1 )   //for lonely block
			{
				if ( $elementPosition == 0 )    //if position before
				{
					$editedContent = preg_replace( '~<' . $elementName . '( |>){1}?~', '<placeholderForAd><' . $elementName . '$1', $editedContent, $elementNumber );
				}
				elseif ( $elementPosition == 1 )    //if position after
				{
					$editedContent = preg_replace( '~<( )*/( )*' . $elementName . '( )*>~', '</' . $elementName . '><placeholderForAd>', $editedContent, $elementNumber );
				}
				$editedContent = preg_replace( '~<placeholderForAd>~', '', $editedContent, $elementNumber - 1 );
				$editedContent = preg_replace( '~<placeholderForAd>~', '<placeholderForAdDop>', $editedContent, 1 );
			}
			elseif ( $item['setting_type'] == 2 )  //for repeatable block
			{
				if ( $elementPosition == 0 )    //if position before
				{
					$editedContent = preg_replace( '~<' . $elementName . '( |>){1}?~', '<placeholderForAd><' . $elementName . '$1', $editedContent );
				}
				elseif ( $elementPosition == 1 )    //if position after
				{
					$editedContent = preg_replace( '~<( )*/( )*' . $elementName . '( )*>~', '</' . $elementName . '><placeholderForAd>', $editedContent );
				}
				$editedContent = preg_replace( '~<placeholderForAd>~', '', $editedContent, $elementNumber - 1 );        //first iteration
				$editedContent = preg_replace( '~<placeholderForAd>~', '<placeholderForAdDop>', $editedContent, 1 );

				for ( $i = 0; $i < $elementRepeats; $i ++ )     //repeats begin
				{
					$editedContent = preg_replace( '~<placeholderForAd>~', '', $editedContent, $elementStep - 1 );
					$editedContent = preg_replace( '~<placeholderForAd>~', '<placeholderForAdDop>', $editedContent, 1 );
				}
			}
			elseif ( $item['setting_type'] == 3 )  //for repeatable block
			{
				$thisElementTag = preg_match('~[\.\#]{1}~', $elementName, $m);
				$thisElementName = preg_replace('~([\.\#]{1})~', '', $elementName, 1);
				if ($m[0]=='.')
				{
					$thisElementType = 'class';
				}
				elseif ($m[0]=='#')
				{
					$thisElementType = 'id';
				}

				if ( $elementPosition == 0 )    //if position before
				{
					$editedContent = preg_replace(
						'~<([0-9a-z]*?) ([^>]*?) '.$thisElementName.' ([^>]*?)>~',
						'( "$1", "$2", "$3" )<placeholderForAd><$1 $2 '.$thisElementName.' $3>', $editedContent);
				}
				elseif ( $elementPosition == 1 )    //if position after
				{
					$usedTag = preg_match('~<([0-9a-z]*?) ([^>]*?) '.$thisElementName.' ([^>]*?)>((\s|\S)*?)<\/p>~', $editedContent,$m1);
					$editedContent = preg_replace(
						'~<('.$m1[1].') ([^>]*?) '.$thisElementName.' ([^>]*?)>((\s|\S)*?)<\/'.$m1[1].'>~',
						'<$1 $2 '.$thisElementName.' $3>$4</$1><placeholderForAd>', $editedContent);
				}
				$editedContent = preg_replace( '~<placeholderForAd>~', '<placeholderForAdDop>', $editedContent);
			}
			$editedContent = preg_replace( '~<placeholderForAdDop>~', $elementText, $editedContent );   //replacing right placeholders
			$editedContent = preg_replace( '~<placeholderForAd>~', '', $editedContent );    //replacing all useless placeholders
		}
		return $editedContent;
	}
	else
	{
		return $editedContent;
	}
}

function headerADInsertor()
{
	$wp_cur_theme = wp_get_theme();
	$wp_cur_theme_name = $wp_cur_theme->get_template();
//	$wp_cur_theme_file = get_theme_file_uri('header.php');
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