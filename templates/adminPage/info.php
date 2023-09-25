<?php
$args = !empty($GLOBALS['rb_adminPage_args']) ? $GLOBALS['rb_adminPage_args'] : [];
?>

<?php if (!empty($args['deacError'])): ?>
    <div class="squads-blocks">
        Причина последней деактивации:
        <div>
            <span style="color: red">Ошибка: <?php echo $args['deacError']?></span><br>
            Время: <?php echo $args['deacTime']?> <br>
        </div>
    </div>
<?php endif; ?>
<?php if (!empty($args['domain']) || !empty($args['pushStatus']) && !empty($args['pushDomain'])): ?>
    <div class="squads-blocks">
        Инфо о доменах:
        <div>
            <?php if (!empty($args['domain'])): ?>
                Домен для рекламы: <span style="color: green"><?php echo $args['domain']?></span>. <br>
            <?php endif; ?>
            <?php if (!empty($args['pushStatus']) && !empty($args['pushDomain'])): ?>
                Домен для push: <span style="color: green"><?php echo $args['pushDomain']?></span>. <br>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

    <h2>Настройки плагина из кабинета РБ</h2>
    <div>
        <div class="element-separator more">Вставлять в head PUSH-код:
            <b><?php echo RFWP_Utils::getYesOrNo(!empty($args['pushStatus']) ? $args['pushStatus'] : 0) ?></b></div>
        <div class="element-separator more">Исключенные страницы:
            <b><?php echo !empty($args['excludedPages']) ? $args['excludedPages'] : RFWP_Utils::getYesOrNo(0) ?></b></div>
        <div class="element-separator more">Исключенные ид и классы:
            <b><?php echo !empty($args['excludedIdAndClasses']) ? $args['excludedIdAndClasses'] : RFWP_Utils::getYesOrNo(0) ?>.</b></div>
        <div class="element-separator more">Главная страница исключена:
            <b><?php echo RFWP_Utils::getYesOrNo(!empty($args['excludedMainPage']) ? $args['excludedMainPage'] : 0) ?></b></div>
        <div class="element-separator more">
            Исключенные типы страниц:
            <?php if (!empty($args['excludedPageTypes'])):?>
                <?php $counter = 1; ?>
                <ol class="element-separator">
                    <?php foreach ($args['excludedPageTypes'] AS $k => $item): ?>
                        <li><?php echo $item ?></li>
                    <?php endforeach; ?>
                </ol>
            <?php else: ?>
                <b><?= RFWP_Utils::getYesOrNo(0) ?></b>
            <?php endif; ?>
        </div>
        <div class="element-separator more">Показывать рекламу на 404:
            <b><?php echo RFWP_Utils::getYesOrNo(!empty($args['statusFor404']) ? $args['statusFor404'] : 0) ?></b></div>
        <div class="element-separator more">Дублирование рекламных блоков:
            <b><?php echo RFWP_Utils::getYesOrNo(!empty($args['blockDuplicate']) ? $args['blockDuplicate'] : 0) ?></b></div>
        <div class="element-separator more">Обязательный отступ:
            <b><?php echo RFWP_Utils::getYesOrNo(!empty($args['obligatoryMargin']) ? $args['obligatoryMargin'] : 0) ?></b></div>
        <div class="element-separator more">Теги для длины текста:
            <b><?php echo RFWP_Utils::getYesOrNo(!empty($args['tagsListForTextLength']) ?
                    implode(', ', $args['tagsListForTextLength']) : 0) ?></b></div>
        <div class="element-separator more">Таксономии:
            <b><?php echo RFWP_Utils::getYesOrNo(!empty($args['usedTaxonomies']) ? $args['usedTaxonomies'] : 0) ?></b></div>
        <div class="element-separator more">Все скрипты в хедере:
            <b><?php echo RFWP_Utils::getYesOrNo(!empty($args['jsToHead']) ? $args['jsToHead'] : 0) ?></b></div>
    </div>

<?php if (!empty($args['getBlocks'])): ?>
    <h2>Настройки рекламных мест из кабинета РБ</h2>
    <?php foreach ($args['getBlocks'] AS $item): ?>
        <?php $GLOBALS['rb_adminPage_adTemplate'] = $item; ?>
        <?php load_template(__DIR__ . '/ad_template.php', false); ?>
    <?php endforeach; ?>
<?php endif; ?>