<?php
?>

<?php if (!empty($args['couYandexMetrics']) | !empty($args['couLiveInternet']) || !empty($args['couGoogleAnalytics'])): ?>
    <?php if (!empty($args['couYandexMetrics'])): ?>
        <div class="element-separator more">Яндекс.Метрика: <b><?php echo $args['couYandexMetrics']; ?></b></div>
    <?php endif; ?>
    <?php if (!empty($args['couLiveInternet'])): ?>
        <div class="element-separator more">LiveInternet: <b><?php echo $args['couLiveInternet']; ?></b></div>
    <?php endif; ?>
    <?php if (!empty($args['couGoogleAnalytics'])): ?>
        <div class="element-separator more">Google Analytics: <b><?php echo $args['couGoogleAnalytics']; ?></b></div>
    <?php endif; ?>
<?php else: ?>
    <div>Не указано счетчиков</div>
<?php endif; ?>