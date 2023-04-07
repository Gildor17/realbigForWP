<?php
$args = !empty($GLOBALS['rb_adminPage_args']) && !empty($GLOBALS['rb_adminPage_args']['turboOptions']) ? $GLOBALS['rb_adminPage_args']['turboOptions'] : [];
?>
<?php if (!empty($args['template-post']) || !empty($args['template-page'])): ?>
    <?php if (!empty($args['template-post'])): ?>
        <h2>Записи</h2>
        <div class="element-separator"><?php echo $args['template-post']; ?></div>
    <?php endif; ?>

    <?php if (!empty($args['template-page'])): ?>
        <h2>Страницы</h2>
        <div class="element-separator"><?php echo $args['template-page']; ?></div>
    <?php endif; ?>
<?php else: ?>
    Нет заполненных шаблонов.
<?php endif; ?>