<?php
?>

<h2>Удалить указанные шорткоды: <?php echo RFWP_Utils::getYesOrNo(!empty($args['filterSc']) ? 1 : 0); ?></h2>
<?php if (!empty($args['filterSc'])): ?>
    <div class="element-separator">Шорткоды для удаления: <b><?php echo str_replace([';', ';;'], ', ', $args['filterScField']); ?></b></div>
<?php endif; ?>

<h2>Фильтр тегов (без контента): <?php echo RFWP_Utils::getYesOrNo(!empty($args['filterTagsWithoutContent']) ? 1 : 0); ?></h2>
<?php if (!empty($args['filterTagsWithoutContent'])): ?>
    <div class="element-separator">Теги для удаления: <b><?php echo str_replace([';', ';;'], ', ', $args['filterTagsWithoutContentField']); ?></b></div>
<?php endif; ?>

<h2>Фильтр тегов (с контентом): <?php echo RFWP_Utils::getYesOrNo(!empty($args['filterTagsWithContent']) ? 1 : 0); ?></h2>
<?php if (!empty($args['filterTagsWithContent'])): ?>
    <div class="element-separator">Теги для удаления: <b><?php echo str_replace([';', ';;'], ', ', $args['filterTagsWithContentField']); ?></b></div>
<?php endif; ?>

<h2>Контент для удаления: <?php echo RFWP_Utils::getYesOrNo(!empty($args['filterContent']) ? 1 : 0); ?></h2>
<?php if (!empty($args['filterContent'])): ?>
    <div class="element-separator">Список удаляемого контента: <b><?php echo $args['filterContentField']; ?></b></div>
<?php endif; ?>