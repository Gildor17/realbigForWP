<?php
$position = ['left' => 'Слева', 'right' => 'Справа', 'false' => 'В указанном месте'];
$orders = [
    'call' => ['title' => 'Звонок', 'attr' => 'blockFeedbackButtonContactsCall'],
    'callback' => ['title' => 'Контактная форма', 'attrs' => ['blockFeedbackButtonContactsCallbackEmail' => 'Email',
        'blockFeedbackButtonContactsCallbackOrganizationName' => 'Название организации',
        'blockFeedbackButtonContactsCallbackTermsOfUse' => 'Пользовательское соглашение']],
    'chat' => ['title' => 'Чат', 'attr' => 'blockFeedbackButtonContactsChat'],
    'mail' => ['title' => 'E-mail', 'attr' => 'blockFeedbackButtonContactsMail'],
    'vkontakte' => ['title' => 'VKontakte', 'attr' => 'blockFeedbackButtonContactsVkontakte'],
    'odnoklassniki' => ['title' => 'Odnoklassniki', 'attr' => 'blockFeedbackButtonContactsOdnoklassniki'],
    'twitter' => ['title' => 'Twitter', 'attr' => 'blockFeedbackButtonContactsTwitter'],
    'facebook' => ['title' => 'Facebook', 'attr' => 'blockFeedbackButtonContactsFacebook'],
    'viber' => ['title' => 'Viber', 'attr' => 'blockFeedbackButtonContactsViber'],
    'whatsapp' => ['title' => 'Whatsapp', 'attr' => 'blockFeedbackButtonContactsWhatsapp'],
    'telegram' => ['title' => 'Telegram', 'attr' => 'blockFeedbackButtonContactsTelegram'],
];
?>
<div class="element-separator">Выравнивание блока:
    <b><?php echo !empty($position[$args['blockFeedbackPosition']]) ? $position[$args['blockFeedbackPosition']] : ''; ?></b></div>
<?php if ($args['blockFeedbackPosition'] == 'false'):
    $place = ['begin' => 'В начале записи', 'end' => 'В конце записи'];?>
    <div class="element-separator">Расположить блок:
        <b><?php echo !empty($place[$args['blockFeedbackPositionPlace']]) ? $place[$args['blockFeedbackPositionPlace']] : ''; ?></b></div>
    <div class="element-separator">Заголовок блока: <b><?php echo $args['blockFeedbackPositionTitle']; ?></b></div>
<?php endif; ?>
<?php if (!empty($args['blockFeedbackButtonOrder'])): ?>
    <h3>Порядок кнопок связи</b></h3>
    <?php foreach (explode(';', $args['blockFeedbackButtonOrder']) as $button): ?>
        <?php if (!empty($orders[$button])): ?>
            <?php if (!empty($orders[$button]['attrs'])): ?>
                <div class="element-separator most">
                    <b><?php echo $orders[$button]['title']; ?></b>
                    <?php foreach ($orders[$button]['attrs'] as $attr => $name): ?>
                        <?php if (isset($args[$attr])): ?>
                            <div class="element-separator"><?php echo $name; ?>: <b><?php echo $args[$attr]; ?></b></div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>

            <?php elseif (!empty($orders[$button]['attr']) && isset($args[$orders[$button]['attr']])): ?>
                <div class="element-separator"><?php echo $orders[$button]['title']; ?>:
                    <b><?php echo $args[$orders[$button]['attr']]; ?></b></div>
            <?php else: ?>
                <div class="element-separator"><?php echo $orders[$button]['title']; ?></div>
            <?php endif; ?>
        <?php endif; ?>
    <?php endforeach; ?>
<?php else: ?>
    <div class="element-separator">Порядок кнопок связи: <b><?php echo RFWP_Utils::getYesOrNo(0); ?></b></div>
<?php endif; ?>