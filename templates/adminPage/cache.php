<?php
$args = !empty($GLOBALS['rb_adminPage_args']) && !empty($GLOBALS['rb_adminPage_args']['cache']) ? $GLOBALS['rb_adminPage_args']['cache'] : [];
?>

<?php if (!empty($args) && is_array($args)):?>
    Количество закешированных блоков: <b><?php echo count($args) ?></b>
    <div class="element-separator more overflow-auto">
        <table class="table">
            <thead>
                <tr>
                    <th>ID блока</th>
                    <th>Кеш десктопа</th>
                    <th>Кеш планшета</th>
                    <th>Кеш мобільного</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($args as $blockId => $caches): ?>
                    <tr>
                        <td><b><?php echo $blockId ?></b></td>
                        <td><?php echo isset($caches['desktop']) ? RFWP_rb_cache_gathering_content($caches['desktop']) : "—"; ?></td>
                        <td><?php echo isset($caches['tablet']) ? RFWP_rb_cache_gathering_content($caches['tablet']) : "—"; ?></td>
                        <td><?php echo isset($caches['mobile']) ? RFWP_rb_cache_gathering_content($caches['mobile']) : "—"; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <form method="post" class="ml-auto" name="cacheForm" id="cacheFormId">
        <?php submit_button( 'Очистить кеш', 'primary', 'clearCache') ?></form>
<?php else: ?>
    Нет закешированных блоков
<?php endif; ?>