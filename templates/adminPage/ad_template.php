<?php
$tagCategories = RFWP_getTagsCategories();
$tagString = $categoryString = "";

if (!empty($args['onTags'])) {
    $tags = explode(',', $args['onTags']);
    foreach ($tags as $tag) {
        $tagString .= (!empty($tagString) ? ',' : '') . " " .
            (isset($tagCategories['tags'][$tag]) ? '"' . $tagCategories['tags'][$tag] . '"' : $tag);
    }
    $tagString = "Выводить в" . $tagString;

} elseif (!empty($args['offTags'])) {
    $tags = explode(',', $args['offTags']);
    foreach ($tags as $tag) {
        $tagString .= (!empty($tagString) ? ',' : '') . " " .
            (isset($tagCategories['tags'][$tag]) ? '"' . $tagCategories['tags'][$tag] . '"' : $tag);
    }
    $tagString = "Не выводить в" . $tagString;
} else {
    $tagString = RFWP_Utils::getYesOrNo(0);

}

if (!empty($args['onCategories'])) {
    $tags = explode(',', $args['onCategories']);
    foreach ($tags as $tag) {
        $categoryString .= (!empty($categoryString) ? ',' : '') . " " .
            (isset($tagCategories['categories'][$tag]) ? '"' . $tagCategories['categories'][$tag] . '"' : $tag);
    }
    $categoryString = "Выводить в" . $categoryString;

} elseif (!empty($args['offCategories'])) {
    $tags = explode(',', $args['offCategories']);
    foreach ($tags as $tag) {
        $categoryString .= (!empty($categoryString) ? ',' : '') . " " .
            (isset($tagCategories['categories'][$tag]) ? '"' . $tagCategories['categories'][$tag] . '"' : $tag);
    }
    $categoryString = "Не выводить в" . $categoryString;
} else {
    $categoryString = RFWP_Utils::getYesOrNo(0);

}
?>

<div class="squads-blocks width-whole">
    <div class="element-separator">ID: <b><?php echo $args['block_number']; ?></b></div>
    <div class="element-separator">Тип отображения:
        <b><?php echo RFWP_AdUtils::getSettingsType($args['setting_type']);
        if (in_array($args['setting_type'], [6, 7])) echo ": " . $args['elementPlace'] . " от начала текста" ?></b>
    </div>
    <div class="element-separator">Минимум символов: <b><?php echo $args['minSymbols']; ?></b></div>
    <div class="element-separator">Максимум символов: <b><?php echo $args['maxSymbols']; ?></b></div>
    <div class="element-separator">Минимум заголовков: <b><?php echo $args['minHeaders']; ?></b></div>
    <div class="element-separator">Максимум заголовков: <b><?php echo $args['maxHeaders']; ?></b></div>
    <div class="element-separator">Теги: <b><?php echo $tagString; ?></b></div>
    <div class="element-separator">Категории: <b><?php echo $categoryString; ?></b></div>
    <div class="element-separator">Расположение: <b><?php echo ucfirst($args['elementCss']); ?></b></div>
</div>